<?php

namespace App\Http\Controllers;

use Exception;
use Carbon\Carbon;
use App\Models\Game;
use App\Models\Team;
use App\Models\Manche;
use App\Models\Player;
use App\Models\Season;
use App\Models\Division;
use App\Models\LiveScore;
use App\Events\ScoreUpdated;
use Illuminate\Http\Request;
use App\Models\TeamSeasonStat;
use App\Models\PlayerSeasonStat;
use App\Services\RankingService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Notifications\GameApprovalNotification;

class GameController extends Controller
{
    
    protected $rankingService;

    public function __construct(RankingService $rankingService)
    {
        $this->rankingService = $rankingService;
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

    $matchDate = Carbon::parse($validatedData['date'])->startOfDay();
    $currentDate = Carbon::now()->startOfDay();

    if ($currentDate->greaterThan($matchDate->addDay())) {
        return back()->withErrors(['msg' => 'U kunt geen scores meer invoeren voor deze wedstrijd.']);
    }

    if ($validatedData['bye_team_id']) {
        $validatedData['home_team_id'] = null;
        $validatedData['away_team_id'] = null;
    } else {
        $request->validate([
            'home_team_id' => 'required|exists:teams,id',
            'away_team_id' => 'required|exists:teams,id',
        ]);

        $homeTeam = Team::find($validatedData['home_team_id']);
        $awayTeam = Team::find($validatedData['away_team_id']);
        $divisionId = $validatedData['division_id'];

        if (!$homeTeam->divisions->contains($divisionId) || !$awayTeam->divisions->contains($divisionId)) {
            Log::error('Teams are not in the selected division');
            return back()->withErrors(['msg' => 'Teams must be in the selected division']);
        }
    }

    $game = Game::create($validatedData);
    Log::info('Game created: ', ['game_id' => $game->id]);

    if (isset($validatedData['scores'])) {
        $homeWins = 0;
        $awayWins = 0;

        foreach ($validatedData['scores'] as $index => $score) {
            if (!empty($score['1M']) && !empty($score['2M']) && (!isset($score['Belle']) || !empty($score['Belle']))) {
                $matchResult = $this->calculateMatchResult($score);

                if ($matchResult == 1) {
                    $homeWins++;
                } elseif ($matchResult == 2) {
                    $awayWins++;
                }

                Manche::create([
                    'game_id' => $game->id,
                    'player1_id' => $score['home_player'],
                    'player2_id' => $score['away_player'],
                    'number' => $index + 1,
                    'score1' => $score['1M'],
                    'score2' => $score['2M'],
                    'belle_score' => $score['Belle'] ?? null,
                    'winner_id' => $matchResult == 1 ? $score['home_player'] : ($matchResult == 2 ? $score['away_player'] : null),
                ]);
            }
        }

        $game->update([
            'home_score' => $homeWins,
            'away_score' => $awayWins,
        ]);

        $awayTeamCaptain = $game->awayTeam->players()->where('role', 'captain')->first();
        if ($awayTeamCaptain) {
            $awayTeamCaptain->notify(new GameApprovalNotification($game));
        }
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
                '1M' => $manche->score1 ?? 'N/A',
                '2M' => $manche->score2 ?? 'N/A',
                'Belle' => $manche->belle_score ?? 'N/A',
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
                    '1M' => $score['1M'] ?? 'N/A',
                    '2M' => $score['2M'] ?? 'N/A',
                    'Belle' => $score['Belle'] ?? 'N/A',
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

    // Verwerk bye team
    if ($request->filled('is_bye')) {
        $validatedData['home_team_id'] = null;
        $validatedData['away_team_id'] = null;
        $validatedData['bye_team_id'] = $validatedData['bye_team_id'];
    } else {
        $validatedData['bye_team_id'] = null;
    }

    // Verwerk forfeit
    if ($request->filled('forfeit_by')) {
        if ($validatedData['forfeit_by'] === 'home') {
            $validatedData['home_score'] = 0;
            $validatedData['away_score'] = 6; // Forfeit score
        } elseif ($validatedData['forfeit_by'] === 'away') {
            $validatedData['home_score'] = 6;
            $validatedData['away_score'] = 0; // Forfeit score
        }

        // Verwijder alle bestaande manches als het een forfeit is
        Manche::where('game_id', $game->id)->delete();
    } else {
        // Verwerk reguliere wedstrijd zonder forfeit
        if (isset($validatedData['manches'])) {
            foreach ($validatedData['manches'] as $index => $mancheData) {
                $manche = Manche::updateOrCreate(
                    [
                        'game_id' => $game->id,
                        'number' => $index + 1,
                    ],
                    [
                        'player1_id' => $mancheData['player1_id'],
                        'player2_id' => $mancheData['player2_id'],
                        'score1' => $mancheData['score1'],
                        'score2' => $mancheData['score2'],
                        'belle_score' => $mancheData['belle_score'] ?? null,
                        'winner_id' => $this->determineWinner($mancheData, $mancheData['player1_id'], $mancheData['player2_id']),
                    ]
                );

                // Log updated manche details
                Log::info('Manche updated or created', ['manche_id' => $manche->id, 'winner_id' => $manche->winner_id]);
            }
        }
    }

    // Update game data
    $game->update($validatedData);

    // Als de gebruiker admin is, zet away_team_approved direct op true
    $user = auth()->user();
    if ($user->role === 'admin') {
        $game->away_team_approved = true;
        $game->save();
    }

    // Update de statistieken van spelers en teams na de update van de wedstrijd
    $this->updatePlayerStats($game);
    $this->updateGameStats($game);

    // Update de LiveScore data
    $this->updateLiveScore(null, $game);

    return redirect()->route('games.for-division-season', ['division_id' => $game->division_id, 'season_id' => $game->season_id])->with('success', 'Wedstrijd succesvol bijgewerkt!');
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
            $seasonId = $game->season_id;
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
    $homeWins = $game->home_score;
    $awayWins = $game->away_score;

    $seasonId = $game->season_id;

    // Ophalen of initialiseren van teamstatistieken
    $homeTeamStats = TeamSeasonStat::firstOrNew([
        'team_id' => $game->home_team_id,
        'season_id' => $seasonId
    ]);

    $awayTeamStats = TeamSeasonStat::firstOrNew([
        'team_id' => $game->away_team_id,
        'season_id' => $seasonId
    ]);

    // Reset waarden om fouten te vermijden
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

    // Log bijgewerkte teamstatistieken
    Log::info('Updated team season stats', [
        'home_team_id' => $game->home_team_id,
        'away_team_id' => $game->away_team_id,
        'home_team_stats' => $homeTeamStats->toArray(),
        'away_team_stats' => $awayTeamStats->toArray(),
    ]);

    // Update van spelerstatistieken op basis van de manches
    foreach ($game->manches as $manche) {
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
    }
}




protected function getPlayerIdByName($name)
{
    Log::info('getPlayerIdByName called', ['name' => $name]);

    // Controleer of de naam een numerieke waarde is (speler-ID)
    if (is_numeric($name)) {
        $player = Player::find($name);
        if ($player) {
            Log::info('Player found by ID in getPlayerIdByName', [
                'player_id' => $player->id,
                'first_name' => $player->first_name,
                'last_name' => $player->last_name
            ]);
            return $player->id;
        } else {
            Log::warning('Player not found by ID in getPlayerIdByName', ['player_id' => $name]);
            return null;  // Return null if no player is found with that ID
        }
    }

    // Als de naam geen ID is, verwerk deze dan als een volledige naam
    $parts = explode(' ', $name);
    if (count($parts) < 2) {
        Log::warning('Name format is invalid for getPlayerIdByName', ['name' => $name]);
        return null;  // Return null if the name cannot be split properly
    }

    $firstName = $parts[0];
    $lastName = implode(' ', array_slice($parts, 1));

    Log::info('Searching for player by name', ['first_name' => $firstName, 'last_name' => $lastName]);

    // Speler zoeken op basis van voornaam en achternaam
    $player = Player::where('first_name', $firstName)
                    ->where('last_name', $lastName)
                    ->first();

    if (!$player) {
        Log::warning('Player not found in getPlayerIdByName by name', [
            'first_name' => $firstName,
            'last_name' => $lastName
        ]);
        return null;  // Return null if no player is found
    }

    Log::info('Player found in getPlayerIdByName by name', [
        'player_id' => $player->id,
        'first_name' => $player->first_name,
        'last_name' => $player->last_name
    ]);

    return $player->id;
}



protected function determineWinner($score, $homePlayerId = null, $awayPlayerId = null)
{
    Log::info('Determine winner called', [
        'score' => $score,
        'homePlayerId' => $homePlayerId,
        'awayPlayerId' => $awayPlayerId
    ]);

    // Als één van de speler-ID's niet is doorgegeven, probeer ze op te halen
    if ($homePlayerId === null && isset($score['home_player'])) {
        $homePlayerId = $this->getPlayerIdByName($score['home_player']);
        Log::info('Home player ID set', ['homePlayerId' => $homePlayerId]);
    }

    if ($awayPlayerId === null && isset($score['away_player'])) {
        $awayPlayerId = $this->getPlayerIdByName($score['away_player']);
        Log::info('Away player ID set', ['awayPlayerId' => $awayPlayerId]);
    }

    // Als beide spelers-ID's niet bekend zijn, geef geen winnaar terug
    if ($homePlayerId === null && $awayPlayerId === null) {
        // Beide spelers geven forfait, geen winnaar
        Log::info('Both player IDs are null, no winner');
        return null;
    }

    // Controleer of we beide spelers-ID's hebben
    if ($homePlayerId === null || $awayPlayerId === null) {
        Log::error('Missing player ID for determineWinner', [
            'homePlayerId' => $homePlayerId,
            'awayPlayerId' => $awayPlayerId,
            'score' => $score
        ]);
        return null;
    }

    // Bestaande logica om de winnaar te bepalen
    $homePoints = 0;
    $awayPoints = 0;

    if (isset($score['1M'])) {
        if ($score['1M'] == 1) $homePoints++;
        elseif ($score['1M'] == 2) $awayPoints++;
    }

    if (isset($score['2M'])) {
        if ($score['2M'] == 1) $homePoints++;
        elseif ($score['2M'] == 2) $awayPoints++;
    }

    if ($homePoints == $awayPoints && isset($score['Belle'])) {
        if ($score['Belle'] == 1) $homePoints++;
        elseif ($score['Belle'] == 2) $awayPoints++;
    }

    Log::info('Calculated points', [
        'homePoints' => $homePoints,
        'awayPoints' => $awayPoints
    ]);

    // Winnaar bepalen
    if ($homePoints > $awayPoints) {
        Log::info('Home player is winner', ['winnerId' => $homePlayerId]);
        return $homePlayerId;
    } elseif ($awayPoints > $homePoints) {
        Log::info('Away player is winner', ['winnerId' => $awayPlayerId]);
        return $awayPlayerId;
    }

    Log::info('Match is a draw, no winner');
    return null; // Gelijkspel of geen winnaar
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

    // Controleer 1M en 2M scores voor elke speler
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

    // Als beide spelers één manche winnen, kijk dan naar de Belle om de winnaar te bepalen
    if ($homePoints == $awayPoints && isset($scoreData['Belle'])) {
        if ($scoreData['Belle'] == 1) {
            $homePoints++;
        } elseif ($scoreData['Belle'] == 2) {
            $awayPoints++;
        }
    }

    // Bepaal de winnaar op basis van punten
    if ($homePoints > $awayPoints) {
        return 1; // Thuis team wint
    } elseif ($awayPoints > $homePoints) {
        return 2; // Uit team wint
    }

    return 0; // Gelijkspel of geen winnaar
}


public function updatePlayerStats(Game $game)
{
    Log::info('Starting updatePlayerStats', ['game_id' => $game->id]);

    $currentSeasonId = $game->season_id;
    $divisionId = $game->division_id; // Haal division_id op uit de game
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
        $currentSeasonId = $request->query('season_id', Season::latest('id')->value('id'));
        $seasons = Season::all();
        $season = Season::find($currentSeasonId);
        $game->load(['homeTeam', 'awayTeam', 'manches.player1', 'manches.player2']);

        Log::info('Game data:', $game->toArray());
        Log::info('Manches data:', $game->manches->toArray());

        if (!$currentSeasonId) {
            return back()->withErrors('Geen actief seizoen gevonden.');
        }

        // Fetch all games for the current division and selected season
        $games = Game::with(['homeTeam', 'awayTeam'])
                     ->where('division_id', $division->id)
                     ->where('season_id', $currentSeasonId)
                     ->orderBy('date', 'asc')
                     ->get()
                     ->groupBy(function($game) {
                         return \Carbon\Carbon::parse($game->date)->format('d-m-Y');
                     });

        // Fetch standings
        $standings = $this->calculateDivisionStandings($division, $currentSeasonId);

        // Fetch past games with pagination
        $pastGames = Game::with(['homeTeam', 'awayTeam'])
                         ->where('division_id', $division->id)
                         ->where('season_id', $currentSeasonId)
                         ->where('date', '<', now())
                         ->orderBy('date', 'desc')
                         ->paginate(5);

        return view('games.show', compact('division', 'games', 'game', 'standings', 'seasons', 'currentSeasonId', 'season', 'pastGames'));
    }

    public function showGame(Game $game)
{
    // Laad de nodige relaties: thuisteam, uitteam en manches met spelers
    $game->load(['homeTeam', 'awayTeam', 'manches.player1', 'manches.player2']);
    
    // Zoek naar de meest recente update van de manches
    $mostRecentManche = $game->manches()->orderBy('updated_at', 'desc')->first();
    
    $scores = [];

    // Als er recente wijzigingen zijn in de manches, gebruik die gegevens
    if ($mostRecentManche && $mostRecentManche->updated_at > ($game->updated_at ?? now())) {
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
                '1M' => $manche->score1 ?? 'N/A',
                '2M' => $manche->score2 ?? 'N/A',
                'Belle' => $manche->belle_score ?? 'N/A',
            ];
        }
    } else {
        // Anders gebruik de LiveScore gegevens
        $liveScore = LiveScore::where('game_id', $game->id)->first();
        $liveData = $liveScore ? json_decode($liveScore->data, true) : null;

        if ($liveData && isset($liveData['scores'])) {
            foreach ($liveData['scores'] as $score) {
                // Zoek de juiste teamnaam voor de speler
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

    return view('games.show', compact('game', 'scores', 'liveData'));
}


public function updateLiveScore(Request $request = null, Game $game)
{
    Log::info('Updating LiveScore for game.', ['game_id' => $game->id]);

    // Bepaal het toegestane tijdsbestek op basis van de wedstrijdtijd
    $matchDate = Carbon::parse($game->date)->startOfDay();
    $allowedStart = $matchDate->copy()->subHours(2); // Start 2 uur voor de wedstrijddag
    $allowedEnd = $matchDate->copy()->addHours(30); // Eindigt 30 uur na de wedstrijddag

    $currentTime = Carbon::now();

    // Controleer of de huidige tijd binnen het toegestane tijdsbestek valt
    if (!$currentTime->between($allowedStart, $allowedEnd)) {
        Log::warning('Live score update attempted outside of allowed timeframe.', [
            'game_id' => $game->id,
            'current_time' => $currentTime,
            'allowed_start' => $allowedStart,
            'allowed_end' => $allowedEnd
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

        $playerIds = array_map('intval', array_filter(array_merge(
            [$validatedData['home_captain'], $validatedData['away_captain'], $validatedData['home_reserve'], $validatedData['away_reserve']],
            array_column($validatedData['scores'], 'home_player'),
            array_column($validatedData['scores'], 'away_player')
        )));

        Log::info('Player IDs to search:', $playerIds);

        $players = Player::with('team')->whereIn('id', $playerIds)->get()->keyBy('id')->toArray();

        Log::info('Players found:', $players);

        $homeCaptainName = isset($validatedData['home_captain']) && isset($players[$validatedData['home_captain']]) 
            ? $players[$validatedData['home_captain']]['first_name'] . ' ' . $players[$validatedData['home_captain']]['last_name'] 
            : '';

        $awayCaptainName = isset($validatedData['away_captain']) && isset($players[$validatedData['away_captain']]) 
            ? $players[$validatedData['away_captain']]['first_name'] . ' ' . $players[$validatedData['away_captain']]['last_name'] 
            : '';

        $homeReserveName = isset($validatedData['home_reserve']) && isset($players[$validatedData['home_reserve']]) 
            ? $players[$validatedData['home_reserve']]['first_name'] . ' ' . $players[$validatedData['home_reserve']]['last_name'] 
            : '';

        $awayReserveName = isset($validatedData['away_reserve']) && isset($players[$validatedData['away_reserve']]) 
            ? $players[$validatedData['away_reserve']]['first_name'] . ' ' . $players[$validatedData['away_reserve']]['last_name'] 
            : '';

        Log::info('Resolved names:', [
            'homeCaptain' => $homeCaptainName,
            'awayCaptain' => $awayCaptainName,
            'homeReserve' => $homeReserveName,
            'awayReserve' => $awayReserveName,
        ]);

        $scores = [];
        if (isset($validatedData['scores'])) {
            foreach ($validatedData['scores'] as $index => $score) {
                $winnerId = null;

                $homePlayerName = 'Nog niet gestart';
                $homePlayerTeamName = $game->homeTeam->name ?? '';
                $awayPlayerName = 'Nog niet gestart';
                $awayPlayerTeamName = $game->awayTeam->name ?? '';

                if ($score['home_player'] === 'forfeit') {
                    $homePlayerName = 'Forfait';
                    $homePlayerTeamName = $game->homeTeam->name ?? '';

                    if (isset($players[$score['away_player']])) {
                        $awayPlayerName = $players[$score['away_player']]['first_name'] . ' ' . $players[$score['away_player']]['last_name'];
                        $awayPlayerTeamName = $players[$score['away_player']]['team']['name'];
                    }

                    $score['1M'] = 2;
                    $score['2M'] = 2;
                    $score['Belle'] = null;

                    $winnerId = isset($players[$score['away_player']]) ? $score['away_player'] : null;
                } elseif ($score['away_player'] === 'forfeit') {
                    if (isset($players[$score['home_player']])) {
                        $homePlayerName = $players[$score['home_player']]['first_name'] . ' ' . $players[$score['home_player']]['last_name'];
                        $homePlayerTeamName = $players[$score['home_player']]['team']['name'];
                    }

                    $awayPlayerName = 'Forfait';
                    $awayPlayerTeamName = $game->awayTeam->name ?? '';

                    $score['1M'] = 1;
                    $score['2M'] = 1;
                    $score['Belle'] = null;

                    $winnerId = isset($players[$score['home_player']]) ? $score['home_player'] : null;
                } else {
                    if (isset($players[$score['home_player']])) {
                        $homePlayerName = $players[$score['home_player']]['first_name'] . ' ' . $players[$score['home_player']]['last_name'];
                        $homePlayerTeamName = $players[$score['home_player']]['team']['name'];
                    }

                    if (isset($players[$score['away_player']])) {
                        $awayPlayerName = $players[$score['away_player']]['first_name'] . ' ' . $players[$score['away_player']]['last_name'];
                        $awayPlayerTeamName = $players[$score['away_player']]['team']['name'];
                    }

                    $score['1M'] = is_numeric($score['1M']) ? intval($score['1M']) : null;
                    $score['2M'] = is_numeric($score['2M']) ? intval($score['2M']) : null;
                    $score['Belle'] = is_numeric($score['Belle']) ? intval($score['Belle']) : null;

                    $winnerId = $this->determineWinner($score);
                }

                $score['home_player_name'] = $homePlayerName;
                $score['away_player_name'] = $awayPlayerName;

                Log::info('Score entry resolved:', [
                    'homePlayer' => $homePlayerName,
                    'homeTeam' => $homePlayerTeamName,
                    'awayPlayer' => $awayPlayerName,
                    'awayTeam' => $awayPlayerTeamName,
                    '1M' => $score['1M'],
                    '2M' => $score['2M'],
                    'Belle' => $score['Belle'],
                    'WinnerId' => $winnerId,
                ]);

                $scores[] = [
                    'home_player_name' => $homePlayerName,
                    'home_player_team' => $homePlayerTeamName,
                    'away_player_name' => $awayPlayerName,
                    'away_player_team' => $awayPlayerTeamName,
                    '1M' => $score['1M'] !== null ? $score['1M'] : '',
                    '2M' => $score['2M'] !== null ? $score['2M'] : '',
                    'Belle' => $score['Belle'] !== null ? $score['Belle'] : '',
                    'WinnerId' => $winnerId,
                ];

                Manche::updateOrCreate(
                    ['game_id' => $game->id, 'number' => $index + 1],
                    [
                        'player1_id' => isset($players[$score['home_player']]) ? $score['home_player'] : null,
                        'player2_id' => isset($players[$score['away_player']]) ? $score['away_player'] : null,
                        'score1' => $score['1M'],
                        'score2' => $score['2M'],
                        'belle_score' => $score['Belle'],
                        'winner_id' => $winnerId,
                    ]
                );
            }
        }

        $dataToStore = [
            'home_team_name' => $game->homeTeam->name ?? 'Nog niet gestart',
            'away_team_name' => $game->awayTeam->name ?? 'Nog niet gestart',
            'home_score' => $validatedData['home_score'],
            'away_score' => $validatedData['away_score'],
            'home_captain_name' => $homeCaptainName,
            'away_captain_name' => $awayCaptainName,
            'home_reserve_name' => $homeReserveName,
            'away_reserve_name' => $awayReserveName,
            'scores' => $scores,
            'forfeit_team' => $validatedData['forfeit_team'] ?? null,
            'division_name' => $game->division->name ?? '',
            'game_date' => $game->date ? $game->date->format('Y-m-d') : '',
        ];

        Log::info('Final data to be stored in LiveScore:', $dataToStore);

        LiveScore::updateOrCreate(
            ['game_id' => $game->id],
            ['data' => json_encode($dataToStore)]
        );

        Log::info('Live score updated successfully for game.', ['game_id' => $game->id]);

        return response()->json(['success' => true]);

    } else {
        // Fetch existing data and update live score without request
        $game->load(['homeTeam', 'awayTeam', 'division', 'manches.player1.team', 'manches.player2.team']);
        
        $scores = [];

        foreach ($game->manches as $manche) {
            $homePlayerName = $manche->player1 ? $manche->player1->first_name . ' ' . $manche->player1->last_name : 'Nog niet gestart';
            $homePlayerTeam = $manche->player1 && $manche->player1->team ? $manche->player1->team->name : '';

            $awayPlayerName = $manche->player2 ? $manche->player2->first_name . ' ' . $manche->player2->last_name : 'Nog niet gestart';
            $awayPlayerTeam = $manche->player2 && $manche->player2->team ? $manche->player2->team->name : '';

            $scores[] = [
                'home_player_name' => $homePlayerName,
                'home_player_team' => $homePlayerTeam,
                'away_player_name' => $awayPlayerName,
                'away_player_team' => $awayPlayerTeam,
                '1M' => $manche->score1 !== null ? $manche->score1 : '',
                '2M' => $manche->score2 !== null ? $manche->score2 : '',
                'Belle' => $manche->belle_score !== null ? $manche->belle_score : '',
            ];
        }

        $dataToStore = [
            'home_team_name' => $game->homeTeam->name ?? 'Nog niet gestart',
            'away_team_name' => $game->awayTeam->name ?? 'Nog niet gestart',
            'home_score' => $game->home_score ?? 0,
            'away_score' => $game->away_score ?? 0,
            'home_captain_name' => '',
            'away_captain_name' => '',
            'home_reserve_name' => '',
            'away_reserve_name' => '',
            'scores' => $scores,
            'forfeit_team' => $game->forfeit_by,
            'division_name' => $game->division->name ?? '',
            'game_date' => $game->date ? $game->date->format('Y-m-d') : '',
        ];

        Log::info('Final data to be stored in LiveScore:', $dataToStore);

        LiveScore::updateOrCreate(
            ['game_id' => $game->id],
            ['data' => json_encode($dataToStore)]
        );

        Log::info('Live score updated successfully for game.', ['game_id' => $game->id]);
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


public function showLiveScores()
{
    Log::info('Entering showLiveScores method');

    // Haal de huidige datum en de datum van twee dagen geleden op
    $twoDaysAgo = Carbon::now()->subDays(2)->format('Y-m-d');
    $currentDate = Carbon::now()->format('Y-m-d');
    Log::info('Current date:', ['date' => $currentDate]);
    Log::info('Two days ago:', ['date' => $twoDaysAgo]);

    // Haal de wedstrijden op die binnen de laatste 48 uur plaatsvinden
    $games = Game::with(['homeTeam', 'awayTeam', 'division'])
                 ->whereBetween('date', [$twoDaysAgo, $currentDate])
                 ->get();
    Log::info('Games found:', ['games' => $games->toArray()]);

    // Haal de live scores op voor de gevonden wedstrijden
    $liveScores = LiveScore::whereIn('game_id', $games->pluck('id'))->get()->keyBy('game_id');
    Log::info('Live scores found:', ['live_scores' => $liveScores->toArray()]);

    // Verwerk de wedstrijden en koppel de live scores aan de bijbehorende wedstrijd
    $liveData = $games->map(function ($game) use ($liveScores) {
        // Zorg ervoor dat de live score gegevens beschikbaar zijn of anders een leeg array teruggeven
        $data = $liveScores->get($game->id) ? json_decode($liveScores->get($game->id)->data, true) : [];

        // Voeg basisgegevens over de wedstrijd toe, controleer op null waarden
        $data['home_team_name'] = $game->homeTeam->name ?? 'Onbekend';
        $data['away_team_name'] = $game->awayTeam->name ?? 'Onbekend';
        $data['division_name'] = $game->division->name ?? 'Geen divisie';
        $data['game_date'] = $game->date ? $game->date->format('Y-m-d') : 'Onbekende datum';

        // De teamnamen zijn al aanwezig in $data['scores'], dus we hoeven ze niet opnieuw op te halen
        // Controleer of 'home_player_team' en 'away_player_team' aanwezig zijn in elke score
        if (isset($data['scores'])) {
            foreach ($data['scores'] as &$score) {
                // Als de teamnaam niet is ingesteld, stel deze dan in op basis van de wedstrijdgegevens
                if (!isset($score['home_player_team']) || empty($score['home_player_team'])) {
                    $score['home_player_team'] = $game->homeTeam->name ?? 'Onbekend';
                }
                if (!isset($score['away_player_team']) || empty($score['away_player_team'])) {
                    $score['away_player_team'] = $game->awayTeam->name ?? 'Onbekend';
                }
            }
        } else {
            $data['message'] = 'Live scores zijn nog niet beschikbaar.';
        }

        Log::info('Live data for game:', ['game_id' => $game->id, 'live_data' => $data]);

        return $data;
    });

    // Als er geen live wedstrijden zijn, toon een bericht aan de gebruiker
    if ($liveData->isEmpty()) {
        return view('live-scores', ['message' => 'Er zijn geen live wedstrijden beschikbaar voor vandaag.']);
    }

    Log::info('Displaying live scores', ['liveData' => $liveData->toArray()]);

    // Toon de live scores
    return view('live-scores', ['liveData' => $liveData]);
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
        $this->updateLiveScore(null, $game);

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
        $this->updateLiveScore(null, $game);

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