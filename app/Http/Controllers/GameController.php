<?php

namespace App\Http\Controllers;

use Exception;
use Carbon\Carbon;
use App\Models\Game;
use App\Models\Team;
use App\Models\Manche;
use App\Models\Player;
use App\Models\Season;
use App\Models\CupGame;
use App\Models\Division;
use App\Models\LiveScore;
use App\Events\ScoreUpdated;
use Illuminate\Http\Request;
use App\Services\GameService;
use App\Models\TeamSeasonStat;
use App\Models\PlayerSeasonStat;
use App\Services\RankingService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Notifications\GameApprovalNotification;
use App\Services\LiveScoreService;

class GameController extends Controller
{
    
    protected $rankingService;
    protected $liveScoreService;

    public function __construct(RankingService $rankingService , LiveScoreService $liveScoreService)
    {
        $this->rankingService = $rankingService;
        $this->liveScoreService = $liveScoreService;
    }

    public function index(Request $request)
    {
        $divisionId = $request->query('division_id');
    $seasonId = $request->query('season_id');
    $today = Carbon::today(); // Haal de huidige datum op

    // Haal de wedstrijden van vandaag op
    $todayGames = Game::with(['homeTeam', 'awayTeam'])
                        ->where('division_id', $divisionId)
                        ->where('season_id', $seasonId)
                        ->whereDate('date', Carbon::today())
                        ->orderBy('date', 'asc')
                        ->get();

    $upcomingGames = Game::with(['homeTeam', 'awayTeam'])
                        ->where('division_id', $divisionId)
                        ->where('season_id', $seasonId)
                        ->where('date', '>', $today)
                        ->orderBy('date', 'asc')
                        ->get();

    $pastGames = Game::with(['homeTeam', 'awayTeam'])
                     ->where('division_id', $divisionId)
                     ->where('season_id', $seasonId)
                     ->where('date', '<', $today)
                     ->orderBy('date', 'desc')
                     ->paginate(5);

    $divisions = Division::all();
    $seasons = Season::all();
    $currentSeasonId = $seasonId ?? Season::latest('id')->value('id');

    return view('games.index', compact('todayGames', 'upcomingGames', 'pastGames', 'divisions', 'seasons', 'currentSeasonId'));
    }

    public function create(Request $request, $division_id = null, $season_id = null)
{
    $divisions = Division::all();
    $teams = Team::all();
    $seasons = Season::all();
    $latestSeason = Season::latest('id')->first();

    if ($divisions->isEmpty() || $teams->isEmpty() || $seasons->isEmpty()) {
        return redirect()->route('home')->withErrors(['msg' => 'Er zijn geen divisies, teams of seizoenen beschikbaar om een wedstrijd aan te maken.']);
    }

    $selectedDivisionId = $division_id ?? '';
    $selectedSeasonId = $season_id ?? $latestSeason->id;

    return view('games.create', compact('divisions', 'teams', 'seasons', 'selectedDivisionId', 'selectedSeasonId', 'latestSeason'));
}
    
public function store(Request $request)
{
    Log::info('Store method called');
    Log::info('Request data: ', $request->all());

    $validatedData = $request->validate([
        'division_id' => 'required|exists:divisions,id',
        'season_id' => 'required|exists:seasons,id',
        'date' => 'required|date',
        'home_team_id' => 'nullable|exists:teams,id',
        'away_team_id' => 'nullable|exists:teams,id',
        'bye_team_id' => 'nullable|exists:teams,id',
        'scores' => 'sometimes|array',
        'scores.*.home_player' => 'nullable|exists:players,id',
        'scores.*.away_player' => 'nullable|exists:players,id',
        'scores.*.1M' => 'nullable|integer',
        'scores.*.2M' => 'nullable|integer',
        'scores.*.Belle' => 'nullable|integer',
        'home_captain' => 'nullable|exists:players,id',
        'away_captain' => 'nullable|exists:players,id',
        'home_reserve' => 'nullable|exists:players,id',
        'away_reserve' => 'nullable|exists:players,id',
    ]);

    // Zorg ervoor dat als het een bye-team is, beide teams leeg zijn
    if ($validatedData['bye_team_id']) {
        $validatedData['home_team_id'] = null;
        $validatedData['away_team_id'] = null;
    } else {
        // Valideer dat beide teams geselecteerd zijn
        $request->validate([
            'home_team_id' => 'required|exists:teams,id',
            'away_team_id' => 'required|exists:teams,id',
        ]);
    }

    // Zorg ervoor dat de teams in de juiste divisie spelen
    $homeTeam = Team::find($validatedData['home_team_id']);
    $awayTeam = Team::find($validatedData['away_team_id']);
    $divisionId = $validatedData['division_id'];

    if (!$homeTeam->divisions->contains($divisionId) || !$awayTeam->divisions->contains($divisionId)) {
        Log::error('Teams are not in the selected division');
        return back()->withErrors(['msg' => 'Teams must be in the selected division']);
    }

    // Maak een nieuwe wedstrijd aan
    $game = Game::create($validatedData);
    Log::info('Game created: ', ['game_id' => $game->id]);

    // Verwerk de scores als ze zijn opgegeven
    if (isset($validatedData['scores'])) {
        $homeWins = 0;
        $awayWins = 0;

        foreach ($validatedData['scores'] as $index => $score) {
            if (!empty($score['1M']) && !empty($score['2M']) && (!isset($score['Belle']) || !empty($score['Belle']))) {
                // Bereken de winnaar van elke manche
                $matchResult = $this->calculateMatchResult($score);

                if ($matchResult == 1) {
                    $homeWins++;
                } elseif ($matchResult == 2) {
                    $awayWins++;
                }

                // Bereken de winnaar op basis van de score
                $winnerId = $this->determineWinner($score, $score['home_player'], $score['away_player']);

                // Maak een nieuwe manche aan
                Manche::create([
                    'game_id' => $game->id,
                    'player1_id' => $score['home_player'],
                    'player2_id' => $score['away_player'],
                    'number' => $index + 1,
                    'score1' => $score['1M'],
                    'score2' => $score['2M'],
                    'belle_score' => $score['Belle'] ?? null,
                    'winner_id' => $winnerId,
                ]);
            }
        }

        // Update de eindscore van de wedstrijd
        $game->update([
            'home_score' => $homeWins,
            'away_score' => $awayWins,
        ]);

        // Update de statistieken van de spelers
        $this->updatePlayerStats($game);
    }

    return redirect()->route('game.create')->with('success', 'Wedstrijd succesvol aangemaakt!');
}



public function edit(Game $game)
{
    $user = auth()->user();

    // Controleer of de gebruiker admin is
    if ($user->role !== 'admin') {
        return redirect()->route('games.show', $game->id)->withErrors(['msg' => 'Je bent niet bevoegd om deze wedstrijd te bewerken.']);
    }

    // Laad de benodigde relaties voor het game object
    $game->load(['homeTeam.club.teams.players', 'awayTeam.club.teams.players', 'manches.player1', 'manches.player2']);

    // Haal alle spelers van het thuis- en uitteam op inclusief spelers van alle teams binnen de clubs
    $homeTeamPlayers = $game->homeTeam->club->teams->flatMap(function ($team) {
        return $team->players;
    })->unique('id');

    $awayTeamPlayers = $game->awayTeam->club->teams->flatMap(function ($team) {
        return $team->players;
    })->unique('id');

    // Voeg spelers toe die in de manches voorkomen maar niet in de huidige teamselectie zitten
    $additionalPlayers = collect();
    foreach ($game->manches as $manche) {
        if ($manche->player1 && !$homeTeamPlayers->contains($manche->player1) && !$awayTeamPlayers->contains($manche->player1)) {
            $additionalPlayers->push($manche->player1);
        }
        if ($manche->player2 && !$homeTeamPlayers->contains($manche->player2) && !$awayTeamPlayers->contains($manche->player2)) {
            $additionalPlayers->push($manche->player2);
        }
    }

    // Voeg de extra spelers toe aan de respectievelijke teams
    $homeTeamPlayers = $homeTeamPlayers->merge($additionalPlayers)->unique('id');
    $awayTeamPlayers = $awayTeamPlayers->merge($additionalPlayers)->unique('id');

    // Zoek naar de meest recente update van de manches
    $mostRecentManche = $game->manches()->orderBy('updated_at', 'desc')->first();

    $scores = [];

    // Controleer of er recente wijzigingen zijn in de manches
    if ($mostRecentManche && $mostRecentManche->updated_at > ($game->updated_at ?? now())) {
        // Gebruik actuele gegevens van de manches
        foreach ($game->manches as $manche) {
            $homePlayerName = optional($manche->player1)->first_name . ' ' . optional($manche->player1)->last_name;
            $awayPlayerName = optional($manche->player2)->first_name . ' ' . optional($manche->player2)->last_name;

            $homePlayerTeamName = $this->getPlayerActualTeamName($homePlayerName);
            $awayPlayerTeamName = $this->getPlayerActualTeamName($awayPlayerName);

            $scores[] = [
                'home_player_name' => $homePlayerName ?? 'Onbekend',
                'home_player_team' => $homePlayerTeamName,
                'away_player_name' => $awayPlayerName ?? 'Onbekend',
                'away_player_team' => $awayPlayerTeamName,
                '1M' => $manche->score1 ?? '', // Lege waarde als score niet beschikbaar is
                '2M' => $manche->score2 ?? '', // Lege waarde als score niet beschikbaar is
                'Belle' => $manche->belle_score ?? '', // Lege waarde als score niet beschikbaar is
            ];
        }
    } else {
        // Gebruik de opgeslagen LiveScore gegevens indien beschikbaar
        $liveScore = LiveScore::where('game_id', $game->id)->first();
        $liveData = $liveScore ? json_decode($liveScore->data, true) : null;

        if ($liveData && isset($liveData['scores'])) {
            foreach ($liveData['scores'] as $score) {
                // Zoek de juiste teamnaam voor de speler op basis van de opgeslagen gegevens
                $homePlayerTeamName = $this->getPlayerActualTeamName($score['home_player_name']);
                $awayPlayerTeamName = $this->getPlayerActualTeamName($score['away_player_name']);

                $scores[] = [
                    'home_player_name' => $score['home_player_name'] ?? 'Onbekend',
                    'home_player_team' => $homePlayerTeamName,
                    'away_player_name' => $score['away_player_name'] ?? 'Onbekend',
                    'away_player_team' => $awayPlayerTeamName,
                    '1M' => $score['1M'] ?? '', // Lege waarde als score niet beschikbaar is
                    '2M' => $score['2M'] ?? '', // Lege waarde als score niet beschikbaar is
                    'Belle' => $score['Belle'] ?? '', // Lege waarde als score niet beschikbaar is
                ];
            }
        }
    }

    // Retourneer de view met de benodigde data om alle velden aan te kunnen passen
    return view('games.edit', compact('game', 'homeTeamPlayers', 'awayTeamPlayers', 'scores'));
}


public function update(Request $request, Game $game)
{
    Log::info('Update method called');
    Log::info('Form submitted via hidden iframe.', $request->all());
    Log::info('Request data:', $request->all());

    $validatedData = $request->validate([
        'date' => 'required|date',
        'home_team_id' => 'nullable|exists:teams,id',
        'away_team_id' => 'nullable|exists:teams,id',
        'bye_team_id' => 'nullable|exists:teams,id',
        'home_score' => 'nullable|integer',
        'away_score' => 'nullable|integer',
        'forfeit_by' => 'nullable|string|in:home,away',
        'manches' => 'nullable|array',
        'manches.*.player1_id' => 'nullable|exists:players,id',
        'manches.*.player2_id' => 'nullable|exists:players,id',
        'manches.*.score1' => 'nullable|integer',
        'manches.*.score2' => 'nullable|integer',
        'manches.*.belle_score' => 'nullable|integer',
    ]);

    if ($request->filled('is_bye')) {
        $validatedData['home_team_id'] = null;
        $validatedData['away_team_id'] = null;
        $validatedData['bye_team_id'] = $validatedData['bye_team_id'];
    } else {
        $validatedData['bye_team_id'] = null;
    }

    // Forfeit verwerkingslogica
    if ($request->filled('forfeit_by')) {
        if ($validatedData['forfeit_by'] === 'home') {
            $validatedData['home_score'] = 0;
            $validatedData['away_score'] = 6;
        } elseif ($validatedData['forfeit_by'] === 'away') {
            $validatedData['home_score'] = 6;
            $validatedData['away_score'] = 0;
        }

        Manche::where('game_id', $game->id)->delete();
    } else {
        // Normale wedstrijd verwerking zonder forfeit
        if (isset($validatedData['manches'])) {
            foreach ($validatedData['manches'] as $index => $mancheData) {
                $score1 = is_numeric($mancheData['score1']) ? intval($mancheData['score1']) : null;
                $score2 = is_numeric($mancheData['score2']) ? intval($mancheData['score2']) : null;
                $belleScore = is_numeric($mancheData['belle_score']) ? intval($mancheData['belle_score']) : null;

                $scoreArray = [
                    '1M' => $score1,
                    '2M' => $score2,
                    'Belle' => $belleScore,
                ];

                $winnerId = $this->determineWinner($scoreArray, $mancheData['player1_id'], $mancheData['player2_id']);

                Log::info("Winner calculated for manche $index: ", ['winner_id' => $winnerId]);

                Manche::updateOrCreate(
                    [
                        'game_id' => $game->id,
                        'number' => $index + 1,
                    ],
                    [
                        'player1_id' => $mancheData['player1_id'],
                        'player2_id' => $mancheData['player2_id'],
                        'score1' => $score1,
                        'score2' => $score2,
                        'belle_score' => $belleScore,
                        'winner_id' => $winnerId,
                    ]
                );
            }
        }
    }

    $game->update($validatedData);

    $user = auth()->user();
    if ($user->role === 'admin') {
        $game->away_team_approved = true;
        $game->save();
    }

    $this->updatePlayerStats($game);
    $this->updateGameStats($game);

    $this->updateLiveScore(new Request(), $game);


    return redirect()->route('games.for-division-season', ['division_id' => $game->division_id, 'season_id' => $game->season_id])
        ->with('success', 'Wedstrijd succesvol bijgewerkt!');
}



public function requestApproval(Game $game)
{
    $user = auth()->user();

    if ($user->team_id == $game->away_team_id || $user->role == 'admin') {
        // Haal de live score gegevens op voor de wedstrijd
        $liveScore = LiveScore::where('game_id', $game->id)->first();

        // Decode de JSON-data naar een array
        if ($liveScore) {
            $liveData = json_decode($liveScore->data, true);
            $scores = $liveData['scores'] ?? [];
        } else {
            $liveData = null;
            $scores = [];
        }

        // Controleer op forfait
        $forfeitTeam = $liveData['forfeit_team'] ?? null;

        if ($forfeitTeam) {
            // Pas de score aan voor de weergave van de goedkeuringspagina
            if ($forfeitTeam === 'home') {
                $game->home_score = 0;
                $game->away_score = 6;
            } elseif ($forfeitTeam === 'away') {
                $game->home_score = 6;
                $game->away_score = 0;
            }
        }

        return view('games.approval', compact('game', 'liveData', 'scores', 'forfeitTeam'));
    }

    return redirect()->route('games.index')->withErrors(['msg' => 'You are not authorized to approve this game.']);
}



public function approve(Request $request, Game $game)
{
    $user = auth()->user();
    Log::info('Approve method called by user:', ['user_id' => $user->id, 'game_id' => $game->id]);

    $isDryRun = $request->has('dry_run');
    Log::info('Dry run:', ['isDryRun' => $isDryRun]);

    if ($user->team_id == $game->away_team_id || $user->role == 'admin') {
        Log::info('User authorized to approve game', ['user_id' => $user->id]);

        $liveScore = LiveScore::where('game_id', $game->id)->first();
        if (!$liveScore) {
            Log::error('No live score data found for game', ['game_id' => $game->id]);
            return redirect()->route('dashboard')->withErrors(['msg' => 'Geen live score gegevens gevonden voor deze wedstrijd.']);
        }

        $liveData = json_decode($liveScore->data, true);
        Log::info('LiveScore Data:', $liveData);

        DB::beginTransaction();
        try {
            if (isset($liveData['forfeit_team'])) {
                $forfeitTeam = $liveData['forfeit_team'];
                Log::info('Forfeit team detected', ['forfeit_team' => $forfeitTeam]);

                if ($forfeitTeam === 'home') {
                    $game->home_score = 0;
                    $game->away_score = 6;
                } elseif ($forfeitTeam === 'away') {
                    $game->home_score = 6;
                    $game->away_score = 0;
                }

                if (!$isDryRun) {
                    Manche::where('game_id', $game->id)->delete();
                    $game->update([
                        'home_score' => $game->home_score,
                        'away_score' => $game->away_score,
                        'away_team_approved' => true,
                    ]);
                    Log::info('Game updated after forfeit', ['game_id' => $game->id]);
                }
            } else {
                // Verwerking van normale wedstrijd
                $game->home_score = $liveData['home_score'];
                $game->away_score = $liveData['away_score'];
                Log::info('Normal game processing:', ['home_score' => $game->home_score, 'away_score' => $game->away_score]);

                if (!$isDryRun) {
                    $game->away_team_approved = true;
                    $game->save();
                
                    // Verifieer dat het veld correct is opgeslagen
                    $updatedGame = Game::find($game->id);
                    if ($updatedGame->away_team_approved) {
                        Log::info('Game successfully approved', ['game_id' => $game->id]);
                    } else {
                        Log::error('Failed to update away_team_approved for game', ['game_id' => $game->id]);
                    }
                }

                foreach ($liveData['scores'] as $index => $score) {
                    $homePlayerName = $score['home_player_name'];
                    $awayPlayerName = $score['away_player_name'];

                    // Controleer op 'Forfait' spelers en stel player IDs in op null indien van toepassing
                    $homePlayerId = ($homePlayerName === 'Forfait') ? null : $this->getPlayerIdByName($homePlayerName);
                    $awayPlayerId = ($awayPlayerName === 'Forfait') ? null : $this->getPlayerIdByName($awayPlayerName);

                    // Bepaal de winnaar met aangepaste methode
                    $winnerId = $this->determineWinner($score, $homePlayerId, $awayPlayerId);

                    Log::info('Processing Manche Data:', [
                        'game_id' => $game->id,
                        'player1_id' => $homePlayerId,
                        'player2_id' => $awayPlayerId,
                        'score1' => $score['1M'],
                        'score2' => $score['2M'],
                        'belle_score' => $score['Belle'] !== '' ? $score['Belle'] : null,
                        'winner_id' => $winnerId,
                    ]);

                    if (!$isDryRun) {
                        Manche::updateOrCreate(
                            [
                                'game_id' => $game->id,
                                'number' => $index + 1,
                            ],
                            [
                                'player1_id' => $homePlayerId,
                                'player2_id' => $awayPlayerId,
                                'score1' => $score['1M'],
                                'score2' => $score['2M'],
                                'belle_score' => $score['Belle'] !== '' ? $score['Belle'] : null,
                                'winner_id' => $winnerId,
                            ]
                        );
                    }
                }

                if (!$isDryRun) {
                    // Update de spelersstatistieken
                    $this->updatePlayerStats($game);
                    Log::info('Player stats updated for game', ['game_id' => $game->id]);
                }
            }

            // Update de teamstatistieken
            if (!$isDryRun) {
                $this->rankingService->updateTeamStats($game);
                Log::info('Team stats updated for game', ['game_id' => $game->id]);
            }

            // Simuleer of herbereken de teamstand
            $division = $game->division;
            $seasonId = $game->season_id ?? null;
if (!$seasonId) {
    Log::error('Season ID is null for the game', ['game_id' => $game->id]);
    return;
}

            $standings = $this->rankingService->calculateDivisionStandings($division, $seasonId);
            Log::info('Updated Division Standings (Simulated):', ['standings' => $standings]);

            if ($isDryRun) {
                DB::rollBack();
                Log::info('Dry run completed, no data saved', ['game_id' => $game->id]);
                return redirect()->route('games.show', $game->id)->with('success', 'Dry Run voltooid: wedstrijdgegevens gesimuleerd en niet opgeslagen.');
            } else {
                DB::commit();
                Log::info('Game successfully approved', ['game_id' => $game->id]);
                return redirect()->route('home')->with('success', 'Wedstrijd succesvol goedgekeurd!');
            }

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error during game approval:', ['error' => $e->getMessage()]);
            return redirect()->route('dashboard')->withErrors(['msg' => $e->getMessage()]);
        }
    }

    Log::warning('Unauthorized user attempted to approve game', ['user_id' => $user->id, 'game_id' => $game->id]);
    return redirect()->route('games.index')->withErrors(['msg' => 'Je bent niet bevoegd om deze wedstrijd goed te keuren of af te wijzen.']);
}



private function updateGameStats(Game $game)
{
    Log::info('Updating game stats for game', ['game_id' => $game->id]);

    $homeWins = $game->home_score;
    $awayWins = $game->away_score;

    $seasonId = $game->season_id ?? null;
    if (!$seasonId) {
        Log::error('Season ID is null for the game', ['game_id' => $game->id]);
        return;
    }

    // Ophalen of initialiseren van teamstatistieken
    $homeTeamStats = TeamSeasonStat::firstOrNew([
        'team_id' => $game->home_team_id,
        'season_id' => $seasonId
    ]);

    $awayTeamStats = TeamSeasonStat::firstOrNew([
        'team_id' => $game->away_team_id,
        'season_id' => $seasonId
    ]);

    // Reset de waarden om fouten te vermijden
    $homeTeamStats->games_draw = $homeTeamStats->games_draw ?? 0;
    $awayTeamStats->games_draw = $awayTeamStats->games_draw ?? 0;
    $homeTeamStats->games_won = $homeTeamStats->games_won ?? 0;
    $awayTeamStats->games_won = $awayTeamStats->games_won ?? 0;
    $homeTeamStats->games_lost = $homeTeamStats->games_lost ?? 0;
    $awayTeamStats->games_lost = $awayTeamStats->games_lost ?? 0;

    // Bepalen of het team heeft gewonnen, verloren of gelijkgespeeld
    if ($homeWins > $awayWins) {
        $homeTeamStats->games_won += 1;
        $homeTeamStats->points += 2;

        $awayTeamStats->games_lost += 1;
    } elseif ($awayWins > $homeWins) {
        $awayTeamStats->games_won += 1;
        $awayTeamStats->points += 2;

        $homeTeamStats->games_lost += 1;
    } else {
        $homeTeamStats->games_draw += 1;
        $awayTeamStats->games_draw += 1;

        $homeTeamStats->points += 1;
        $awayTeamStats->points += 1;
    }

    // Opslaan van de bijgewerkte teamstatistieken
    $homeTeamStats->save();
    $awayTeamStats->save();

    Log::info('Updated team season stats', [
        'home_team_stats' => $homeTeamStats->toArray(),
        'away_team_stats' => $awayTeamStats->toArray(),
    ]);

    // Update van spelerstatistieken op basis van de manches
    foreach ($game->manches as $manche) {
        // Controleer of $manche->score een geldige array is voordat determineWinner wordt aangeroepen
        if (is_array($manche->score)) {
            $winnerId = $this->determineWinner($manche->score, $manche->player1_id, $manche->player2_id);
            $manche->winner_id = $winnerId;
            $manche->save();

            if ($winnerId !== null) {
                $loserId = $winnerId === $manche->player1_id ? $manche->player2_id : $manche->player1_id;

                PlayerSeasonStat::updateOrCreate(
                    ['player_id' => $winnerId, 'season_id' => $seasonId],
                    ['matches_won' => DB::raw('matches_won + 1'), 'points' => DB::raw('points + 1')]
                );

                PlayerSeasonStat::updateOrCreate(
                    ['player_id' => $loserId, 'season_id' => $seasonId],
                    ['matches_lost' => DB::raw('matches_lost + 1')]
                );
            }
        } else {
            Log::warning('Invalid score format for manche', ['manche_id' => $manche->id, 'score' => $manche->score]);
        }
    }
}







protected function getPlayerIdByName($name)
{
    $parts = explode(' ', $name);
    if (count($parts) < 2) {
        return null;  // Return null if the name cannot be split properly
    }

    $firstName = $parts[0];
    $lastName = implode(' ', array_slice($parts, 1));

    $player = Player::where('first_name', $firstName)
                    ->where('last_name', $lastName)
                    ->first();

    return $player ? $player->id : null;
}

protected function determineWinner(array $score, $homePlayerId = null, $awayPlayerId = null)
{
    // Controleer of de score-data een geldig array is
    if (empty($score) || !is_array($score)) {
        Log::warning('Ongeldige of ontbrekende scoredata', [
            'homePlayerId' => $homePlayerId,
            'awayPlayerId' => $awayPlayerId,
            'score' => $score,
        ]);
        return null; // Stop hier als de data niet correct is
    }

    Log::info('Bepalen van winnaar gestart', [
        'homePlayerId' => $homePlayerId,
        'awayPlayerId' => $awayPlayerId,
        'score' => $score,
    ]);

    $homePoints = 0;
    $awayPoints = 0;

    // Tel de punten voor de eerste set
    if (isset($score['1M'])) {
        if ($score['1M'] == 1) {
            $homePoints++;
        } elseif ($score['1M'] == 2) {
            $awayPoints++;
        }
    }

    // Log de tussenstand na de eerste set
    Log::info('Stand na 1M', ['homePoints' => $homePoints, 'awayPoints' => $awayPoints]);

    // Tel de punten voor de tweede set
    if (isset($score['2M'])) {
        if ($score['2M'] == 1) {
            $homePoints++;
        } elseif ($score['2M'] == 2) {
            $awayPoints++;
        }
    }

    // Log de tussenstand na de tweede set
    Log::info('Stand na 2M', ['homePoints' => $homePoints, 'awayPoints' => $awayPoints]);

    // Belle wordt alleen geteld als er een gelijkspel is na 2 sets
    if ($homePoints == $awayPoints && isset($score['Belle'])) {
        if ($score['Belle'] == 1) {
            $homePoints++;
        } elseif ($score['Belle'] == 2) {
            $awayPoints++;
        }
    }

    // Log de uiteindelijke stand
    Log::info('Eindstand na Belle', ['homePoints' => $homePoints, 'awayPoints' => $awayPoints]);

    // Bepaal de winnaar
    if ($homePoints > $awayPoints) {
        return $homePlayerId;
    } elseif ($awayPoints > $homePoints) {
        return $awayPlayerId;
    }

    // Gelijkspel of geen winnaar
    Log::warning('Geen winnaar bepaald, gelijkspel', [
        'homePoints' => $homePoints,
        'awayPoints' => $awayPoints,
    ]);

    return null; // Geen winnaar, het blijft gelijkspel
}





    public function bulkApprove(Request $request)
    {
        $gameIds = $request->input('game_ids', []);

        if (!empty($gameIds)) {
            Game::whereIn('id', $gameIds)->update(['away_team_approved' => true]);
            return redirect()->route('dashboard')->with('success', 'Geselecteerde wedstrijden zijn goedgekeurd.');
        }

        return redirect()->route('dashboard')->with('error', 'Geen wedstrijden geselecteerd voor goedkeuring.');
    }

    protected function calculateMatchResult(array $scoreData)
{
    $homePoints = 0;
    $awayPoints = 0;

    // Controleer 1M en 2M scores
    if (isset($scoreData['1M'])) {
        if ($scoreData['1M'] == 1) {
            $homePoints++;
        } elseif ($scoreData['1M'] == 2) {
            $awayPoints++;
        }
    }

    if (isset($scoreData['2M'])) {
        if ($scoreData['2M'] == 1) {
            $homePoints++;
        } elseif ($scoreData['2M'] == 2) {
            $awayPoints++;
        }
    }

    // Gebruik de Belle als er gelijkspel is
    if ($homePoints == $awayPoints && isset($scoreData['Belle'])) {
        if ($scoreData['Belle'] == 1) {
            $homePoints++;
        } elseif ($scoreData['Belle'] == 2) {
            $awayPoints++;
        }
    }

    // Retourneer de winnaar: 1 voor thuis, 2 voor uit, 0 voor gelijkspel
    if ($homePoints > $awayPoints) {
        return 1; // Home team wint
    } elseif ($awayPoints > $homePoints) {
        return 2; // Away team wint
    }

    return 0; // Gelijkspel
}


public function updatePlayerStats(Game $game)
{
    Log::info('Starting updatePlayerStats', ['game_id' => $game->id]);

    $currentSeasonId = $game->season_id;
    $divisionId = $game->division_id;
    $playerStatsData = []; // Voor tijdelijke opslag van spelerstatistieken

    // Loop door elke manche van de game
    foreach ($game->manches as $manche) {
        $player1Id = $manche->player1_id;
        $player2Id = $manche->player2_id;

        // Sla manches over als beide spelers null zijn
        if ($player1Id === null && $player2Id === null) {
            Log::warning('Both player IDs are null for manche', ['manche_id' => $manche->id]);
            continue;
        }

        $score1 = $manche->score1;
        $score2 = $manche->score2;
        $belleScore = $manche->belle_score;

        // Initialiseer statistieken voor de spelers indien nog niet gedaan
        foreach ([$player1Id, $player2Id] as $playerId) {
            if ($playerId !== null && !isset($playerStatsData[$playerId])) {
                $playerStatsData[$playerId] = [
                    'matches_played' => 0,
                    'matches_won' => 0,
                    'matches_lost' => 0,
                    'manches_won' => 0,
                    'manches_lost' => 0,
                    'points' => 0,
                ];
                Log::info('Initialized stats for player', ['player_id' => $playerId]);
            }
        }

        // Verhoog matches_played voor beide spelers
        if ($player1Id !== null) {
            $playerStatsData[$player1Id]['matches_played'] += 1;
        }
        if ($player2Id !== null) {
            $playerStatsData[$player2Id]['matches_played'] += 1;
        }

        $player1SetsWon = 0;
        $player2SetsWon = 0;

        // Bereken gewonnen sets (manches) op basis van score1 en score2
        if ($score1 == 1) $player1SetsWon++;
        if ($score1 == 2) $player2SetsWon++;

        if ($score2 == 1) $player1SetsWon++;
        if ($score2 == 2) $player2SetsWon++;

        // Als de stand gelijk is, wordt de Belle gebruikt om de winnaar te bepalen
        if ($player1SetsWon == $player2SetsWon && $belleScore !== null) {
            if ($belleScore == 1) $player1SetsWon++;
            if ($belleScore == 2) $player2SetsWon++;
        }

        // Update de stats voor manches (sets) gewonnen en verloren voor beide spelers
        if ($player1Id !== null) {
            $playerStatsData[$player1Id]['manches_won'] += $player1SetsWon;
            $playerStatsData[$player1Id]['manches_lost'] += $player2SetsWon;
        }
        if ($player2Id !== null) {
            $playerStatsData[$player2Id]['manches_won'] += $player2SetsWon;
            $playerStatsData[$player2Id]['manches_lost'] += $player1SetsWon;
        }

        // Bepaal de winnaar en verliezer van de match (wedstrijd tussen twee spelers)
        if ($player1SetsWon > $player2SetsWon) {
            $winnerId = $player1Id;
            $loserId = $player2Id;
        } elseif ($player2SetsWon > $player1SetsWon) {
            $winnerId = $player2Id;
            $loserId = $player1Id;
        } else {
            // Als beide spelers gelijk spelen, is er geen winnaar
            $winnerId = null;
            $loserId = null;
        }

        // Update de statistieken voor matches (individuele wedstrijden) als er een winnaar is
        if ($winnerId !== null && $loserId !== null) {
            $playerStatsData[$winnerId]['matches_won'] += 1;
            $playerStatsData[$winnerId]['points'] += 1; // 1 punt voor het winnen van een individuele match
            $playerStatsData[$loserId]['matches_lost'] += 1;
        }

        Log::info('Updated stats for players', [
            'player1_id' => $player1Id,
            'player1_sets_won' => $player1SetsWon,
            'player1_sets_lost' => $player2SetsWon,
            'player2_id' => $player2Id,
            'player2_sets_won' => $player2SetsWon,
            'player2_sets_lost' => $player1SetsWon,
        ]);
    }

    // Update de statistieken in de database
    foreach ($playerStatsData as $playerId => $stats) {
        $playerStats = PlayerSeasonStat::firstOrNew([
            'player_id' => $playerId,
            'season_id' => $currentSeasonId,
            'division_id' => $divisionId // Voeg division_id toe
        ]);

        Log::info('Before updating player season stats', [
            'player_id' => $playerId,
            'current_matches_played' => $playerStats->matches_played,
            'current_matches_won' => $playerStats->matches_won,
            'current_matches_lost' => $playerStats->matches_lost,
            'current_manches_won' => $playerStats->manches_won,
            'current_manches_lost' => $playerStats->manches_lost,
            'current_points' => $playerStats->points,
            'new_matches_played' => $stats['matches_played'],
            'new_matches_won' => $stats['matches_won'],
            'new_matches_lost' => $stats['matches_lost'],
            'new_manches_won' => $stats['manches_won'],
            'new_manches_lost' => $stats['manches_lost'],
            'new_points' => $stats['points'],
        ]);

        // Update de statistieken door de nieuwe waarden toe te voegen
        $playerStats->matches_played += $stats['matches_played'];
        $playerStats->matches_won += $stats['matches_won'];
        $playerStats->matches_lost += $stats['matches_lost'];
        $playerStats->manches_won += $stats['manches_won'];
        $playerStats->manches_lost += $stats['manches_lost'];
        $playerStats->points += $stats['points'];
        $playerStats->save();

        Log::info('Database updated for player stats', [
            'player_id' => $playerId,
            'matches_played' => $playerStats->matches_played,
            'matches_won' => $playerStats->matches_won,
            'matches_lost' => $playerStats->matches_lost,
            'manches_won' => $playerStats->manches_won,
            'manches_lost' => $playerStats->manches_lost,
            'points' => $playerStats->points,
            'season_id' => $currentSeasonId,
            'division_id' => $divisionId
        ]);
    }

    Log::info('Finished updatePlayerStats', ['game_id' => $game->id]);
}



public function updateAllPlayerStats()
{
    Log::info('Starting updateAllPlayerStats for all games.');

    // Haal alle wedstrijden op
    $games = Game::all();

    foreach ($games as $game) {
        // Update de statistieken voor elke wedstrijd
        $this->updatePlayerStats($game);
    }

    Log::info('Finished updating all player stats.');

    return response()->json(['success' => 'Alle spelersstatistieken zijn bijgewerkt.']);
}



public function show(Request $request, Division $division, Game $game)
{
    Log::info('Entering show method in GameController');

    // Log de aangevraagde URL en de route-parameters
    Log::info('Request URL:', ['url' => $request->fullUrl()]);
    Log::info('Route parameters:', ['division_id' => $division->id, 'game_id' => $game->id]);

    // Seizoeninformatie ophalen
    $currentSeasonId = $request->query('season_id', Season::latest('id')->value('id'));
    Log::info('Current Season ID:', ['season_id' => $currentSeasonId]);

    if (!$currentSeasonId) {
        Log::warning('Geen actief seizoen gevonden');
        return back()->withErrors('Geen actief seizoen gevonden.');
    }

    // Seizoenen ophalen
    $seasons = Season::all();
    Log::info('Fetched Seasons:', ['seasons' => $seasons->pluck('id')->toArray()]);

    $season = Season::find($currentSeasonId);
    if (!$season) {
        Log::error('Season not found with ID:', ['season_id' => $currentSeasonId]);
    } else {
        Log::info('Current Season:', $season->toArray());
    }

    // Game- en manches-informatie ophalen
    try {
        $game->load(['homeTeam', 'awayTeam', 'manches.player1', 'manches.player2']);
        Log::info('Game data:', $game->toArray());
        Log::info('Manches data:', $game->manches->toArray());
    } catch (\Exception $e) {
        Log::error('Failed to load game relationships:', ['error' => $e->getMessage()]);
        return back()->withErrors('Er is een fout opgetreden bij het laden van de wedstrijdgegevens.');
    }

    // Games ophalen voor de huidige divisie en het geselecteerde seizoen
    try {
        $games = Game::with(['homeTeam', 'awayTeam'])
                     ->where('division_id', $division->id)
                     ->where('season_id', $currentSeasonId)
                     ->orderBy('date', 'asc')
                     ->get()
                     ->groupBy(function($game) {
                         return \Carbon\Carbon::parse($game->date)->format('d-m-Y');
                     });
        Log::info('Fetched Games for Division and Season:', ['game_count' => $games->count()]);
    } catch (\Exception $e) {
        Log::error('Error fetching games for division and season:', ['error' => $e->getMessage()]);
    }

    // Standings ophalen
    try {
        $standings = $this->calculateDivisionStandings($division, $currentSeasonId);
        Log::info('Calculated Standings:', ['standings' => $standings]);
    } catch (\Exception $e) {
        Log::error('Error calculating division standings:', ['error' => $e->getMessage()]);
    }

    // Vorige games ophalen met paginering
    try {
        $pastGames = Game::with(['homeTeam', 'awayTeam'])
                         ->where('division_id', $division->id)
                         ->where('season_id', $currentSeasonId)
                         ->where('date', '<', now())
                         ->orderBy('date', 'desc')
                         ->paginate(5);
        Log::info('Fetched Past Games:', ['past_game_count' => $pastGames->total()]);
    } catch (\Exception $e) {
        Log::error('Error fetching past games with pagination:', ['error' => $e->getMessage()]);
    }

    Log::info('Rendering games.show view');
    return view('games.show', compact('division', 'games', 'game', 'standings', 'seasons', 'currentSeasonId', 'season', 'pastGames'));
}

public function showGame(Game $game)
{
    // Log het begin van de functie
    Log::info('Entering showGame function', ['game_id' => $game->id]);

    try {
        // Laad de benodigde relaties
        $game->load(['homeTeam', 'awayTeam', 'manches.player1', 'manches.player2']);
        Log::info('Game relaties geladen', ['game' => $game->toArray()]);
    } catch (\Exception $e) {
        Log::error('Error loading game relationships in showGame', ['error' => $e->getMessage()]);
        return back()->withErrors('Er is een fout opgetreden bij het laden van de game-gegevens.');
    }

    $scores = [];
    $liveData = null;

    try {
        // Zoek naar de meest recente update van de manches
        $mostRecentManche = $game->manches()->orderBy('updated_at', 'desc')->first();
        Log::info('Meest recente manche gevonden', ['mostRecentManche' => $mostRecentManche ? $mostRecentManche->toArray() : 'None found']);

        // Controleer of de manches recent zijn bijgewerkt
        if ($mostRecentManche && $mostRecentManche->updated_at > ($game->updated_at ?? now())) {
            Log::info('Gebruik manche data, recent bijgewerkt');
            foreach ($game->manches as $manche) {
                $homePlayerName = optional($manche->player1)->first_name . ' ' . optional($manche->player1)->last_name;
                $awayPlayerName = optional($manche->player2)->first_name . ' ' . optional($manche->player2)->last_name;

                $homePlayerTeamName = $this->getPlayerActualTeamName($homePlayerName);
                $awayPlayerTeamName = $this->getPlayerActualTeamName($awayPlayerName);

                $scores[] = [
                    'home_player_name' => $homePlayerName ?: 'Onbekend',
                    'home_player_team' => $homePlayerTeamName,
                    'away_player_name' => $awayPlayerName ?: 'Onbekend',
                    'away_player_team' => $awayPlayerTeamName,
                    '1M' => $manche->score1 ?: 'N/A',
                    '2M' => $manche->score2 ?: 'N/A',
                    'Belle' => $manche->belle_score ?: 'N/A',
                ];
            }
        } else {
            Log::info('Geen recente manche updates, gebruik LiveScore gegevens');

            $liveScore = LiveScore::where('game_id', $game->id)->first();

            if (!$liveScore) {
                Log::warning('Geen LiveScore gevonden voor game', ['game_id' => $game->id]);
            } else {
                Log::info('LiveScore gevonden', ['liveScore' => $liveScore->toArray()]);
                $liveData = json_decode($liveScore->data, true);
            }

            if ($liveData && isset($liveData['scores'])) {
                foreach ($liveData['scores'] as $score) {
                    $homePlayerTeamName = $this->getPlayerActualTeamName($score['home_player_name']);
                    $awayPlayerTeamName = $this->getPlayerActualTeamName($score['away_player_name']);

                    $scores[] = [
                        'home_player_name' => $score['home_player_name'] ?? 'Onbekend',
                        'home_player_team' => $homePlayerTeamName,
                        'away_player_name' => $score['away_player_name'] ?? 'Onbekend',
                        'away_player_team' => $awayPlayerTeamName,
                        '1M' => $score['1M'] ?? 'N/A',
                        '2M' => $score['2M'] ?? 'N/A',
                        'Belle' => $score['Belle'] ?? 'N/A',
                    ];
                }
            }
        }
    } catch (\Exception $e) {
        Log::error('Error processing game data in showGame', ['error' => $e->getMessage()]);
        return back()->withErrors('Er is een fout opgetreden bij het verwerken van de wedstrijdgegevens.');
    }

    Log::info('Rendering games.show view', ['game_id' => $game->id, 'scores' => $scores]);

    // Zorg ervoor dat $liveData altijd gedefinieerd is, zelfs als het null is
    $liveData = $liveData ?: [];

    return view('games.show', compact('game', 'scores', 'liveData'));
}



public function updateLiveScore(Request $request = null, Game $game)
{
   Log::info('Updating LiveScore for game.', ['game_id' => $game->id]);

    $user = auth()->user();
    $gameService = new GameService();

    // Controleer of de game kan worden gestart, geef door of de gebruiker admin is
    if (!$gameService->canStartGame($game, $user->role === 'admin')) {
       Log::warning('Live score update attempted outside of allowed timeframe.', [
            'game_id' => $game->id,
            'current_time' => Carbon::now(),
            'allowed_start' => Carbon::parse($game->date)->subHours(2),
            'allowed_end' => Carbon::parse($game->date)->addHours(30),
        ]);

        return response()->json(['error' => 'Live score updates are not allowed outside of the specified time frame.'], 403);
    }

    // Ga verder met het bijwerken van de live score
    if ($request) {
       Log::info('Received request to update live score.', $request->all());

        $validatedData = $request->validate([
            'home_score' => 'required|integer',
            'away_score' => 'required|integer',
            'home_captain' => 'nullable|integer',
            'away_captain' => 'nullable|integer',
            'home_reserve' => 'nullable|integer',
            'away_reserve' => 'nullable|integer',
            'scores' => 'nullable|array',
            'forfeit_team' => 'nullable|string|in:home,away',
        ]);

        // Verzamelen van spelers-ID's
        $playerIds = array_map('intval', array_filter(array_merge(
            [$validatedData['home_captain'], $validatedData['away_captain'], $validatedData['home_reserve'], $validatedData['away_reserve']],
            array_column($validatedData['scores'] ?? [], 'home_player'),
            array_column($validatedData['scores'] ?? [], 'away_player')
        )));

        if (!empty($playerIds)) {
            $players = Player::with('team')->whereIn('id', $playerIds)->get()->keyBy('id')->toArray();
        } else {
            $players = [];
        }

        Log::info('Players found:', $players);

        // Opstellen van kapitein- en reservenaam voor het thuis- en uitteam
        $homeCaptainName = isset($players[$validatedData['home_captain']])
            ? $players[$validatedData['home_captain']]['first_name'] . ' ' . $players[$validatedData['home_captain']]['last_name']
            : '';

        $awayCaptainName = isset($players[$validatedData['away_captain']])
            ? $players[$validatedData['away_captain']]['first_name'] . ' ' . $players[$validatedData['away_captain']]['last_name']
            : '';

        $homeReserveName = isset($players[$validatedData['home_reserve']])
            ? $players[$validatedData['home_reserve']]['first_name'] . ' ' . $players[$validatedData['home_reserve']]['last_name']
            : '';

        $awayReserveName = isset($players[$validatedData['away_reserve']])
            ? $players[$validatedData['away_reserve']]['first_name'] . ' ' . $players[$validatedData['away_reserve']]['last_name']
            : '';

        $scores = [];
        if (isset($validatedData['scores'])) {
            foreach ($validatedData['scores'] as $index => $score) {
                $homePlayerName = isset($players[$score['home_player']])
                    ? $players[$score['home_player']]['first_name'] . ' ' . $players[$score['home_player']]['last_name']
                    : 'Niet gestart';

                $awayPlayerName = isset($players[$score['away_player']])
                    ? $players[$score['away_player']]['first_name'] . ' ' . $players[$score['away_player']]['last_name']
                    : 'Niet gestart';

                // Controleer of de scores numeriek zijn, zo niet, zet ze op null
                $score['1M'] = is_numeric($score['1M']) ? intval($score['1M']) : null;
                $score['2M'] = is_numeric($score['2M']) ? intval($score['2M']) : null;
                $score['Belle'] = is_numeric($score['Belle']) ? intval($score['Belle']) : null;

                $winnerId = $this->determineWinner($score, $score['home_player'], $score['away_player']);

                $scores[] = [
                    'home_player_name' => $homePlayerName,
                    'away_player_name' => $awayPlayerName,
                    '1M' => $score['1M'] ?? '',
                    '2M' => $score['2M'] ?? '',
                    'Belle' => $score['Belle'] ?? '',
                    'WinnerId' => $winnerId,
                ];

                // Update of maak manche aan
                Manche::updateOrCreate(
                    ['game_id' => $game->id, 'number' => $index + 1],
                    [
                        'player1_id' => $score['home_player'] === 'forfeit' ? null : $score['home_player'],
                        'player2_id' => $score['away_player'] === 'forfeit' ? null : $score['away_player'],
                        'score1' => $score['1M'],
                        'score2' => $score['2M'],
                        'belle_score' => $score['Belle'],
                        'winner_id' => $winnerId,
                    ]
                );
            }
        }

        $dataToStore = [
            'home_team_name' => $game->homeTeam->name ?? 'Niet gestart',
            'away_team_name' => $game->awayTeam->name ?? 'Niet gestart',
            'home_score' => $validatedData['home_score'],
            'away_score' => $validatedData['away_score'],
            'home_captain_name' => $homeCaptainName,
            'away_captain_name' => $awayCaptainName,
            'home_reserve_name' => $homeReserveName,
            'away_reserve_name' => $awayReserveName,
            'scores' => $scores,
        ];

        Log::info('Final data to be stored in LiveScore:', $dataToStore);

        // Update of maak de live score aan
        LiveScore::updateOrCreate(
            ['game_id' => $game->id],
            ['data' => json_encode($dataToStore)]
        );

        Log::info('Live score updated successfully for game.', ['game_id' => $game->id]);

        return response()->json(['success' => true]);
    }
}



public function fetchLatestGameData(Game $game)
{
    Log::info('Fetching latest game data for game:', ['game_id' => $game->id]);

    // Haal de live score op uit de database
    $liveScore = LiveScore::where('game_id', $game->id)->first();

    if ($liveScore) {
        $data = json_decode($liveScore->data, true);
        Log::info('Live score data retrieved:', ['data' => $data]);
        return response()->json($data);
    } else {
        Log::warning('No live score data found for game:', ['game_id' => $game->id]);
        return response()->json(['home_score' => 0, 'away_score' => 0, 'scores' => []]);
    }
}
public function fetchLiveScore(Game $game)
{
    // Haal de live score op uit de database voor de opgegeven game
    $liveScore = LiveScore::where('game_id', $game->id)->first();

    if ($liveScore) {
        $data = json_decode($liveScore->data, true);
        
        // Log de data die is opgehaald uit de liveScore
        Log::info('Live score data retrieved:', $data);
        
        return response()->json($data);
    } else {
        // Log dat er geen live score is gevonden
        Log::info('No live score found for game ID:', ['game_id' => $game->id]);
        
        // Haal bestaande spelersinformatie op als er geen live score is
        $homePlayers = $game->homeTeam ? $game->homeTeam->players->map(function($player) {
            return [
                'id' => $player->id,
                'name' => $player->first_name . ' ' . $player->last_name,
                'team' => $player->team->name
            ];
        }) : [];

        $awayPlayers = $game->awayTeam ? $game->awayTeam->players->map(function($player) {
            return [
                'id' => $player->id,
                'name' => $player->first_name . ' ' . $player->last_name,
                'team' => $player->team->name
            ];
        }) : [];

        $response = [
            'home_score' => $game->home_score ?? 0,
            'away_score' => $game->away_score ?? 0,
            'scores' => [
                'home_players' => $homePlayers,
                'away_players' => $awayPlayers
            ]
        ];

        // Log het antwoord wanneer er geen live score is gevonden
        Log::info('Returning default scores:', $response);

        return response()->json($response);
    }
}

protected function processManche(CupGame $game, int $index, array $scoreData)
{
    // Controleer of de spelerdata geldig is en integerwaarden bevat
    if (empty($scoreData['home_player']) || empty($scoreData['away_player'])) {
        Log::warning('Player data missing for manche.', [
            'game_id' => $game->id,
            'manche_number' => $index + 1,
            'home_player' => $scoreData['home_player'] ?? 'None',
            'away_player' => $scoreData['away_player'] ?? 'None'
        ]);
        return; // Sla manche over als er geen spelers zijn
    }

    // Valideer en converteer scores naar numerieke waarden, gebruik null voor "N/A"
    $homeScore = $this->validateScore($scoreData['1M']);
    $awayScore = $this->validateScore($scoreData['2M']);
    
    // Belle score alleen verwerken als beide scores gelijk zijn en niet "N/A" zijn
    $belleScore = null;
    if ($homeScore !== null && $awayScore !== null && $homeScore === $awayScore) {
        $belleScore = $this->validateScore($scoreData['Belle']);
    }

    // Winnaar bepalen op basis van scores
    $winnerId = $this->determineWinner($scoreData, $scoreData['home_player'], $scoreData['away_player']);

    Log::info('Processing manche for game', [
        'game_id' => $game->id,
        'manche_number' => $index + 1,
        'home_player' => $scoreData['home_player'],
        'away_player' => $scoreData['away_player'],
        'home_score' => $homeScore,
        'away_score' => $awayScore,
        'belle_score' => $belleScore,
        'winner_id' => $winnerId
    ]);

    try {
        // Update of creëer de manche
        Manche::updateOrCreate(
            [
                'game_id' => $game->id,
                'number' => $index + 1,
            ],
            [
                'player1_id' => $scoreData['home_player'],
                'player2_id' => $scoreData['away_player'],
                'score1' => $homeScore,
                'score2' => $awayScore,
                'belle_score' => $belleScore,
                'winner_id' => $winnerId,
            ]
        );

        Log::info('Manche successfully processed and saved.', [
            'game_id' => $game->id,
            'manche_number' => $index + 1,
        ]);

    } catch (\Exception $e) {
        Log::error('Failed to process manche for game', [
            'game_id' => $game->id,
            'manche_number' => $index + 1,
            'error' => $e->getMessage()
        ]);
    }
}

protected function validateScore($score)
{
    // Valideer of de score een integer is, anders null
    if ($score === 'N/A' || $score === null || !is_numeric($score)) {
        return null;
    }
    return intval($score);
}

 
public function showLiveScores()
{
    Log::info('Entering showLiveScores method in GameController');

    $liveData = $this->liveScoreService->getLiveScoresForGames();

    // Controleer of er live wedstrijden zijn
    $hasLiveMatches = $liveData->isNotEmpty();
    Log::info('Are there live matches today?', ['has_live_matches' => $hasLiveMatches]);

    if ($liveData->isEmpty()) {
        return view('live-scores', ['message' => 'Er zijn geen live wedstrijden beschikbaar voor vandaag.']);
    }

    Log::info('Displaying live scores', ['liveData' => $liveData->toArray()]);

    return view('live-scores', ['liveData' => $liveData, 'hasLiveMatches' => $hasLiveMatches]);
}




protected function getPlayerActualTeamName($playerName)
{
    // Splits de naam op aan de hand van spaties, maar controleer of er voldoende delen zijn
    $parts = explode(' ', trim($playerName));

    if (count($parts) < 2) {
        Log::warning('Ongeldige spelernaam', ['player_name' => $playerName]);
        return ''; // Retourneer een lege string als de naam niet voldoende informatie bevat
    }

    // Probeer een match te vinden voor een speler met de voor- en achternaam
    $firstName = $parts[0];
    $lastName = implode(' ', array_slice($parts, 1)); // Dit combineert alle delen na de eerste voor de achternaam

    $player = Player::where('first_name', $firstName)
                    ->where('last_name', $lastName)
                    ->with('team')
                    ->first();

    // Controleer of de speler is gevonden en een team heeft, anders een lege string retourneren
    if ($player && $player->team) {
        Log::info('Speler gevonden', ['player_name' => $playerName, 'team_name' => $player->team->name]);
        return $player->team->name;
    } else {
        Log::error('Speler niet gevonden of geen team gekoppeld', ['player_name' => $playerName]);
        return ''; // Retourneer een lege string als er geen team is gevonden
    }
}





public function forfeitRequest(Request $request, Game $game)
{
    $user = auth()->user();
    $teamId = $user->team_id;

    if ($user->role === 'admin' || $teamId === $game->home_team_id || $teamId === $game->away_team_id) {
        if ($teamId === $game->home_team_id || $user->role === 'admin') {
            $game->update([
                'forfeit_by' => 'home',
                'forfeit_confirmed' => true,
                'home_score' => 0,
                'away_score' => 6,  // Losing score
            ]);
        } elseif ($teamId === $game->away_team_id) {
            $game->update([
                'forfeit_by' => 'away',
                'forfeit_confirmed' => false,
                'home_score' => 6,  // Winning score
                'away_score' => 0,
            ]);
            // Notify home team for confirmation (if required)
        }

        // Delete existing manches since the game ended with a forfeit
        Manche::where('game_id', $game->id)->delete();

        // Update the LiveScore data
        $this->updateLiveScore(new Request(), $game);


        return redirect()->route('games.show', $game->id)->with('success', 'Forfaitverzoek ingediend.');
    }

    return redirect()->route('games.show', $game->id)->withErrors(['msg' => 'Je bent niet bevoegd om deze wedstrijd op te geven.']);
}



public function confirmForfeit(Request $request, Game $game)
{
    $user = auth()->user();

    if ($user->role === 'admin' || $user->team_id === $game->home_team_id) {
        $game->update([
            'forfeit_confirmed' => true,
            'away_score' => $game->forfeit_by === 'home' ? 6 : 0,
            'home_score' => $game->forfeit_by === 'away' ? 6 : 0,
        ]);

        // Update the LiveScore data
        $this->updateLiveScore(new Request(), $game);


        return redirect()->route('games.show', $game->id)->with('success', 'Forfait bevestigd.');
    }

    return redirect()->route('games.show', $game->id)->withErrors(['msg' => 'Je bent niet bevoegd om dit forfait te bevestigen.']);
}



    public function calendarData()
    {
        $games = Game::all();
        $events = [];
        foreach ($games as $game) {
            $events[] = [
                'title' => $game->homeTeam->name . ' tegen ' . $game->awayTeam->name,
                'start' => $game->date,
                'url' => route('games.show', $game->id),
            ];
        }
        return response()->json($events);
    }

    public function showDashboard()
    {
        $divisions = Division::all();
        $seasons = Season::all();
        $currentSeason = Season::latest('id')->first();
        return view('dashboard', compact('divisions', 'currentSeason', 'seasons'));
    }

    public function calculateDivisionStandings(Division $division, $seasonId)
{
    Log::info('Calculating division standings', [
        'division_id' => $division->id, 
        'season_id' => $seasonId
    ]);

    $teams = $division->teams()->with([
        'gamesHome' => function ($query) use ($seasonId) {
            $query->where('season_id', $seasonId)
                  ->whereNotNull('home_score')
                  ->whereNotNull('away_score');
        },
        'gamesAway' => function ($query) use ($seasonId) {
            $query->where('season_id', $seasonId)
                  ->whereNotNull('home_score')
                  ->whereNotNull('away_score');
        }
    ])->get();

    $standings = $teams->map(function ($team) {
        $gamesPlayed = 0;
        $gamesWon = 0;
        $gamesLost = 0;
        $gamesDraw = 0;
        $matchesWon = 0;
        $matchesLost = 0;
        $manchesWon = 0;
        $manchesLost = 0;

        Log::info('Processing team', ['team_id' => $team->id, 'team_name' => $team->name]);

        // Verwerk thuiswedstrijden
        foreach ($team->gamesHome as $game) {
            Log::info('Processing home game', ['game_id' => $game->id, 'home_team_id' => $game->home_team_id, 'away_team_id' => $game->away_team_id]);

            $gamesPlayed++;
            $matchesWon += $game->home_score;
            $matchesLost += $game->away_score;

            if ($game->home_score > $game->away_score) {
                $gamesWon++;
            } elseif ($game->home_score < $game->away_score) {
                $gamesLost++;
            } else {
                $gamesDraw++;
            }

            // Verwerk de manches voor thuiswedstrijden
            foreach ($game->manches as $manche) {
                // Thuisteam wint een manche als score1 of score2 == 1
                if ($manche->score1 == 1) {
                    $manchesWon++;
                } elseif ($manche->score1 == 2) {
                    $manchesLost++;
                }

                if ($manche->score2 == 1) {
                    $manchesWon++;
                } elseif ($manche->score2 == 2) {
                    $manchesLost++;
                }

                if ($manche->belle_score !== null) {
                    if ($manche->belle_score == 1) {
                        $manchesWon++;
                    } elseif ($manche->belle_score == 2) {
                        $manchesLost++;
                    }
                }
            }
        }

        // Verwerk uitwedstrijden
        foreach ($team->gamesAway as $game) {
            Log::info('Processing away game', ['game_id' => $game->id, 'home_team_id' => $game->home_team_id, 'away_team_id' => $game->away_team_id]);

            $gamesPlayed++;
            $matchesWon += $game->away_score;
            $matchesLost += $game->home_score;

            if ($game->away_score > $game->home_score) {
                $gamesWon++;
            } elseif ($game->away_score < $game->home_score) {
                $gamesLost++;
            } else {
                $gamesDraw++;
            }

            // Verwerk de manches voor uitwedstrijden
            foreach ($game->manches as $manche) {
                // Uitteam wint een manche als score1 of score2 == 2
                if ($manche->score1 == 2) {
                    $manchesWon++;
                } elseif ($manche->score1 == 1) {
                    $manchesLost++;
                }

                if ($manche->score2 == 2) {
                    $manchesWon++;
                } elseif ($manche->score2 == 1) {
                    $manchesLost++;
                }

                if ($manche->belle_score !== null) {
                    if ($manche->belle_score == 2) {
                        $manchesWon++;
                    } elseif ($manche->belle_score == 1) {
                        $manchesLost++;
                    }
                }
            }
        }

        $points = $gamesWon * 2 + $gamesDraw;

        Log::info('Team standings', [
            'team_id' => $team->id,
            'games_played' => $gamesPlayed,
            'games_won' => $gamesWon,
            'games_lost' => $gamesLost,
            'games_draw' => $gamesDraw,
            'matches_won' => $matchesWon,
            'matches_lost' => $matchesLost,
            'manches_won' => $manchesWon,
            'manches_lost' => $manchesLost,
            'points' => $points
        ]);

        return [
            'team_id' => $team->id,
            'team_name' => $team->name,
            'games_played' => $gamesPlayed,
            'games_won' => $gamesWon,
            'games_lost' => $gamesLost,
            'games_draw' => $gamesDraw,
            'points' => $points,
            'matches_won' => $matchesWon,
            'matches_lost' => $matchesLost,
            'manches_won' => $manchesWon,
            'manches_lost' => $manchesLost
        ];
    })->sort(function ($a, $b) {
        // Sorteer eerst op punten (dalend)
        if ($a['points'] != $b['points']) {
            return $b['points'] - $a['points'];
        }
        // Als punten gelijk zijn, sorteer op gewonnen wedstrijden (dalend)
        if ($a['games_won'] != $b['games_won']) {
            return $b['games_won'] - $a['games_won'];
        }
        // Als nog gelijk, sorteer op gewonnen matches (dalend)
        if ($a['matches_won'] != $b['matches_won']) {
            return $b['matches_won'] - $a['matches_won'];
        }
        // Als nog gelijk, sorteer op gewonnen manches (dalend)
        return $b['manches_won'] - $a['manches_won'];
    })->values()->all();

    Log::info('Completed division standings calculation', ['standings' => $standings]);

    return $standings;
}


    public function showCalendar(Request $request)
{
    $divisions = Division::all();
    $seasons = Season::all();
    $latestSeason = Season::latest('id')->first();
    
    // Get selected division and season or default to the latest season
    $selectedDivisionId = $request->input('division_id', $divisions->first()->id ?? null);
    $selectedSeasonId = $request->input('season_id', $latestSeason->id ?? null);

    // Validate selected division
    $division = Division::find($selectedDivisionId);
    if (!$division) {
        return redirect()->route('games.kalender')->with('error', 'Reeks niet gevonden.');
    }

    // Validate selected season
    $season = Season::find($selectedSeasonId);
    if (!$season) {
        return redirect()->route('games.kalender')->with('error', 'Seizoen niet gevonden.');
    }

    $currentDateTime = Carbon::now();

    // Fetch upcoming games for the selected division and season
    $upcomingGames = Game::with(['homeTeam', 'awayTeam'])
                         ->where('division_id', $division->id)
                         ->where('season_id', $season->id)
                         ->where('date', '>=', $currentDateTime)
                         ->whereNull('bye_team_id')
                         ->orderBy('date', 'asc')
                         ->get()
                         ->groupBy(function($game) {
                             return \Carbon\Carbon::parse($game->date)->format('d-m-Y');
                         });

    // Fetch past games
    $pastGames = Game::with(['homeTeam', 'awayTeam'])
                     ->where('division_id', $division->id)
                     ->where('season_id', $season->id)
                     ->where('date', '<', $currentDateTime)
                     ->whereNull('bye_team_id')
                     ->orderBy('date', 'desc')
                     ->get();

    return view('games.kalender', compact('divisions', 'seasons', 'upcomingGames', 'pastGames', 'selectedDivisionId', 'selectedSeasonId', 'currentDateTime'));
}


public function showGamesForDivisionAndSeason(Request $request, $division_id, $season_id = null)
{
    $division = Division::find($division_id);
    if (!$division) {
        return redirect()->route('dashboard')->with('error', 'Division not found.');
    }

    $seasons = Season::all();
    $season = $season_id ? Season::find($season_id) : Season::latest('id')->first();

    if (!$season) {
        return redirect()->route('dashboard')->with('error', 'Season not found.');
    }

    $currentDateTime = Carbon::now();

    // Fetch games scheduled for today
    $todayGames = Game::with(['homeTeam', 'awayTeam'])
        ->where('division_id', $division_id)
        ->where('season_id', $season->id)
        ->whereDate('date', $currentDateTime->format('Y-m-d'))
        ->get();

    // Fetch upcoming games excluding today's games
    $upcomingGames = Game::with(['homeTeam', 'awayTeam'])
        ->where('division_id', $division_id)
        ->where('season_id', $season->id)
        ->where('date', '>', $currentDateTime)
        ->orderBy('date', 'asc')
        ->get()
        ->groupBy(function($game) {
            return \Carbon\Carbon::parse($game->date)->format('Y-m-d');
        });

    // Fetch past games excluding today's games
    $pastGames = Game::with(['homeTeam', 'awayTeam'])
        ->where('division_id', $division_id)
        ->where('season_id', $season->id)
        ->where('date', '<', $currentDateTime)
        ->orderBy('date', 'desc')
        ->get();

    return view('games.list', compact('division', 'todayGames', 'upcomingGames', 'pastGames', 'seasons', 'season', 'currentDateTime'));
}

    public function generateMatches()
    {
        $teams = Team::all();
        $numTeams = $teams->count();
        $totalRounds = $numTeams - 1;
        $matchesPerRound = intdiv($numTeams, 2);
        $matchDate = Carbon::now()->next('Saturday');
        $currentSeason = Season::latest()->first()->id;

        $schedule = [];

        for ($i = 0; $i < $totalRounds * 2; $i++) {
            for ($j = 0; $matchesPerRound; $j++) {
                $home = ($i + $j) % $numTeams;
                $away = ($i + $numTeams - $j) % $numTeams;
                if ($home != $away) {
                    $schedule[] = [
                        'home_team_id' => $teams[$home]->id,
                        'away_team_id' => $teams[$away]->id,
                        'season_id' => $currentSeason,
                        'date' => $matchDate->copy()->addWeeks($i)->format('Y-m-d')
                    ];
                }
            }
        }

        foreach ($schedule as $gameData) {
            Game::create($gameData);
        }

        return redirect()->route('games.index')->with('success', 'Wedstrijden succesvol gegenereerd!');
    }

    public function clearCalendar()
    {
        Manche::query()->delete(); // Verwijder alle manches
        Game::query()->delete(); // Verwijder alle games

        return redirect()->route('games.index')->with('success', 'Kalender succesvol verwijderd!');
    }

    public function editForm(Game $game)
{
    // Laad de benodigde relaties voor de wedstrijd en spelers
    $game->load([
        'homeTeam' => function ($query) {
            $query->with(['club.teams.players']);
        },
        'awayTeam' => function ($query) {
            $query->with(['club.teams.players']);
        },
        'manches', 
        'belles'
    ]);

    // Haal alle spelers van het thuisteam en teams binnen dezelfde club op
    $homeTeamPlayers = $game->homeTeam->club->teams->flatMap(function ($team) {
        return $team->players;
    })->unique('id');

    // Haal alle spelers van het uitteam en teams binnen dezelfde club op
    $awayTeamPlayers = $game->awayTeam->club->teams->flatMap(function ($team) {
        return $team->players;
    })->unique('id');

    // Toon de bewerkingspagina voor de wedstrijd met alle spelers en gegevens
    return view('games.match_form', compact('game', 'homeTeamPlayers', 'awayTeamPlayers'));
}
    public function play(Game $game)
    {
        $this->authorize('update', $game);
        return view('games.play', compact('game'));
    }
    
    function updateManches(Game $game, array $manchesData)
    {
        $game->players()->detach();

        foreach ($manchesData as $playerId => $scores) {
            $game->players()->attach($playerId, [
                'manche_1_score' => $scores['manche_1_score'] ?? 0,
                'manche_2_score' => $scores['manche_2_score'] ?? 0,
                'belle_score' => $scores['belle_score'] ?? 0,
                'is_belle_winner' => isset($scores['belle_score']) && $scores['belle_score'] > 0
            ]);
        }
    }
    
    function updateBelles(Game $game, array $bellesData)
    {
        $game->belles()->delete();

        foreach ($bellesData as $belle) {
            $game->belles()->create([
                'player_id' => $belle['player_id'],
                'score' => $belle['score'],
                'is_winner' => $belle['is_winner'] ?? false,
            ]);
        }
    }
    
    public function destroy(Game $game, Request $request)
{
    // Verwijder alle manches die aan de wedstrijd zijn gekoppeld
    Manche::where('game_id', $game->id)->delete();

    // Verwijder ook de live score indien deze bestaat
    LiveScore::where('game_id', $game->id)->delete();

    // Verwijder de wedstrijd zelf
    $game->delete();

    return redirect($request->headers->get('referer'))->with('success', 'Wedstrijd en bijbehorende gegevens succesvol verwijderd.');
}
}