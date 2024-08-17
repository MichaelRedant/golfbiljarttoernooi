<?php

namespace App\Http\Controllers;

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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Notifications\GameApprovalNotification;

class GameController extends Controller
{
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

        // Check if the home team and away team belong to the selected division
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

            Log::info('Manche created:', [
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

        Log::info('Home Wins: ' . $homeWins);
        Log::info('Away Wins: ' . $awayWins);

        $game->update([
            'home_score' => $homeWins,
            'away_score' => $awayWins,
        ]);

        // Notify the away team for approval
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

    // Laad de nodige relaties: thuisteam, uitteam en manches met spelers
    $game->load(['homeTeam', 'awayTeam', 'manches.player1', 'manches.player2']);

    // Haal alle spelers van het thuis- en uitteam op
    $homeTeamPlayers = $game->homeTeam->players;
    $awayTeamPlayers = $game->awayTeam->players;

    // Voeg spelers toe die niet bij het thuis- of uitteam horen, maar wel in de manches voorkomen
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

    return view('games.edit', compact('game', 'homeTeamPlayers', 'awayTeamPlayers', 'scores'));
}




public function update(Request $request, Game $game)
{
    Log::info('Update method called');
    Log::info('Request data:', $request->all());

    $validatedData = $request->validate([
        'date' => 'required|date',
        'home_team_id' => 'nullable|exists:teams,id',
        'away_team_id' => 'nullable|exists:teams,id',
        'bye_team_id' => 'nullable|exists:teams,id',
        'home_score' => 'nullable|integer',
        'away_score' => 'nullable|integer',
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

    $game->update($validatedData);

    if (isset($validatedData['manches'])) {
        foreach ($validatedData['manches'] as $index => $mancheData) {
            Manche::updateOrCreate(
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
                ]
            );
        }
    }

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

        return view('games.approval', compact('game', 'liveData', 'scores'));
    }

    return redirect()->route('games.index')->withErrors(['msg' => 'You are not authorized to approve this game.']);
}



public function approve(Request $request, Game $game)
{
    $user = auth()->user();

    if ($user->team_id == $game->away_team_id || $user->role == 'admin') {

        $liveScore = LiveScore::where('game_id', $game->id)->first();

        if (!$liveScore) {
            return redirect()->route('dashboard')->withErrors(['msg' => 'Geen live score gegevens gevonden voor deze wedstrijd.']);
        }

        $liveData = json_decode($liveScore->data, true);

        DB::beginTransaction();
        try {
            Log::info('LiveScore Data:', $liveData);

            // Update de game met de scores
            $game->update([
                'home_score' => $liveData['home_score'],
                'away_score' => $liveData['away_score'],
                'away_team_approved' => true,
            ]);

            // Update of maak de manches aan op basis van de live scores
            foreach ($liveData['scores'] as $index => $score) {
                Manche::updateOrCreate(
                    [
                        'game_id' => $game->id,
                        'number' => $index + 1,
                    ],
                    [
                        'player1_id' => $this->getPlayerIdByName($score['home_player_name']),
                        'player2_id' => $this->getPlayerIdByName($score['away_player_name']),
                        'score1' => $score['1M'],
                        'score2' => $score['2M'],
                        'belle_score' => $score['Belle'] !== 'N/A' ? $score['Belle'] : null,
                        'winner_id' => $this->determineWinnerId($score),
                    ]
                );

                Log::info('Manche Data:', [
                    'game_id' => $game->id,
                    'player1_id' => $this->getPlayerIdByName($score['home_player_name']),
                    'player2_id' => $this->getPlayerIdByName($score['away_player_name']),
                    'score1' => $score['1M'],
                    'score2' => $score['2M'],
                    'belle_score' => $score['Belle'] !== 'N/A' ? $score['Belle'] : null,
                    'winner_id' => $this->determineWinnerId($score),
                ]);
            }

            DB::commit();

            return redirect()->route('dashboard')->with('success', 'Wedstrijd succesvol goedgekeurd!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error tijdens goedkeuring van de wedstrijd:', ['error' => $e->getMessage()]);
            return redirect()->route('dashboard')->withErrors(['msg' => $e->getMessage()]);
        }
    }

    return redirect()->route('games.index')->withErrors(['msg' => 'You are not authorized to approve or reject this game.']);
}



protected function getPlayerIdByName($name)
{
    $parts = explode(' ', $name);
    if (count($parts) < 2) {
        return null;  // Return null if the name cannot be split properly
    }

    return Player::where('first_name', $parts[0])
                 ->where('last_name', $parts[1])
                 ->value('id');
}

protected function determineWinnerId($score)
{
    $homePoints = $score['1M'] ?? 0;
    $awayPoints = $score['2M'] ?? 0;

    if ($homePoints > $awayPoints) {
        return $this->getPlayerIdByName($score['home_player_name']);
    } elseif ($awayPoints > $homePoints) {
        return $this->getPlayerIdByName($score['away_player_name']);
    } elseif (isset($score['Belle'])) {
        return $score['Belle'] == 1 
            ? $this->getPlayerIdByName($score['home_player_name']) 
            : $this->getPlayerIdByName($score['away_player_name']);
    }

    return null;
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
        $homePoints = $scoreData['1M'];
        $awayPoints = $scoreData['2M'];

        if ($homePoints > $awayPoints) {
            return 1;
        } elseif ($awayPoints > $homePoints) {
            return 2;
        } elseif (isset($scoreData['Belle'])) {
            return $scoreData['Belle'] == 1 ? 1 : 2;
        }

        return 0;
    }

    protected function updatePlayerStats(Game $game)
    {
        $manches = Manche::where('game_id', $game->id)->get();

        foreach ($manches as $manche) {
            $player1 = Player::find($manche->player1_id);
            $player2 = Player::find($manche->player2_id);

            // Update statistics for player1
            if ($manche->winner_id == $manche->player1_id) {
                $player1->increment('matches_won');
            } else {
                $player1->increment('matches_lost');
            }

            $player1->increment('manches_won', ($manche->score1 > $manche->score2) ? 1 : 0);
            $player1->increment('manches_lost', ($manche->score1 < $manche->score2) ? 1 : 0);

            // Update statistics for player2
            if ($manche->winner_id == $manche->player2_id) {
                $player2->increment('matches_won');
            } else {
                $player2->increment('matches_lost');
            }

            $player2->increment('manches_won', ($manche->score2 > $manche->score1) ? 1 : 0);
            $player2->increment('manches_lost', ($manche->score2 < $manche->score1) ? 1 : 0);
        }
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

    return view('games.show', compact('game', 'scores'));
}




public function updateLiveScore(Request $request, Game $game)
{
    Log::info('Received request to update live score.', $request->all());

    $validatedData = $request->validate([
        'home_score' => 'required|integer',
        'away_score' => 'required|integer',
        'home_captain' => 'nullable|integer',
        'away_captain' => 'nullable|integer',
        'home_reserve' => 'nullable|integer',
        'away_reserve' => 'nullable|integer',
        'scores' => 'nullable|array',
    ]);

    $playerIds = array_map('intval', array_filter(array_merge(
        [$validatedData['home_captain'], $validatedData['away_captain'], $validatedData['home_reserve'], $validatedData['away_reserve']],
        array_column($validatedData['scores'], 'home_player'),
        array_column($validatedData['scores'], 'away_player')
    )));

    Log::info('Player IDs to search:', $playerIds);

    // Haal spelers op en sleutel ze op ID
    $players = Player::with('team')->whereIn('id', $playerIds)->get()->keyBy('id')->toArray();

    Log::info('Players found:', $players);

    $homeCaptainName = isset($validatedData['home_captain']) && isset($players[$validatedData['home_captain']]) 
        ? $players[$validatedData['home_captain']]['first_name'] . ' ' . $players[$validatedData['home_captain']]['last_name'] 
        : 'Onbekend';

    $awayCaptainName = isset($validatedData['away_captain']) && isset($players[$validatedData['away_captain']]) 
        ? $players[$validatedData['away_captain']]['first_name'] . ' ' . $players[$validatedData['away_captain']]['last_name'] 
        : 'Onbekend';

    $homeReserveName = isset($validatedData['home_reserve']) && isset($players[$validatedData['home_reserve']]) 
        ? $players[$validatedData['home_reserve']]['first_name'] . ' ' . $players[$validatedData['home_reserve']]['last_name'] 
        : 'Onbekend';

    $awayReserveName = isset($validatedData['away_reserve']) && isset($players[$validatedData['away_reserve']]) 
        ? $players[$validatedData['away_reserve']]['first_name'] . ' ' . $players[$validatedData['away_reserve']]['last_name'] 
        : 'Onbekend';

    Log::info('Resolved names:', [
        'homeCaptain' => $homeCaptainName,
        'awayCaptain' => $awayCaptainName,
        'homeReserve' => $homeReserveName,
        'awayReserve' => $awayReserveName,
    ]);

    $scores = [];
    if (isset($validatedData['scores'])) {
        foreach ($validatedData['scores'] as $score) {
            $homePlayerName = isset($players[$score['home_player']]) 
                ? $players[$score['home_player']]['first_name'] . ' ' . $players[$score['home_player']]['last_name'] 
                : 'Onbekend';

            $homePlayerTeamName = isset($players[$score['home_player']])
                ? $players[$score['home_player']]['team']['name']
                : 'Onbekend';

            $awayPlayerName = isset($players[$score['away_player']]) 
                ? $players[$score['away_player']]['first_name'] . ' ' . $players[$score['away_player']]['last_name'] 
                : 'Onbekend';

            $awayPlayerTeamName = isset($players[$score['away_player']])
                ? $players[$score['away_player']]['team']['name']
                : 'Onbekend';

            Log::info('Score entry resolved:', [
                'homePlayer' => $homePlayerName,
                'homeTeam' => $homePlayerTeamName,
                'awayPlayer' => $awayPlayerName,
                'awayTeam' => $awayPlayerTeamName,
                '1M' => $score['1M'],
                '2M' => $score['2M'],
                'Belle' => $score['Belle'],
            ]);

            $scores[] = [
                'home_player_name' => $homePlayerName,
                'home_player_team' => $homePlayerTeamName,
                'away_player_name' => $awayPlayerName,
                'away_player_team' => $awayPlayerTeamName,
                '1M' => $score['1M'] ?? 'N/A',
                '2M' => $score['2M'] ?? 'N/A',
                'Belle' => $score['Belle'] ?? 'N/A',
            ];
        }
    }

    $dataToStore = [
        'home_team_name' => $game->homeTeam->name ?? 'Onbekend',
        'away_team_name' => $game->awayTeam->name ?? 'Onbekend',
        'home_score' => $validatedData['home_score'],
        'away_score' => $validatedData['away_score'],
        'home_captain_name' => $homeCaptainName,
        'away_captain_name' => $awayCaptainName,
        'home_reserve_name' => $homeReserveName,
        'away_reserve_name' => $awayReserveName,
        'scores' => $scores,
    ];

    Log::info('Final data to be stored in LiveScore:', $dataToStore);

    $liveScore = LiveScore::updateOrCreate(
        ['game_id' => $game->id],
        ['data' => json_encode($dataToStore)]
    );

    Log::info('Live score updated/created successfully.', $liveScore->toArray());

    return response()->json(['success' => true]);
}




public function showLiveScores()
{
    $liveScores = LiveScore::all();

    if ($liveScores->isEmpty()) {
        return view('live-scores', ['message' => 'Er zijn momenteel geen live gegevens beschikbaar.']);
    }

    $liveData = $liveScores->map(function($liveScore) {
        $data = json_decode($liveScore->data, true);

        $game = Game::with(['homeTeam', 'awayTeam'])->find($liveScore->game_id);
        if ($game) {
            foreach ($data['scores'] as &$score) {
                // Zoek de juiste teamnaam voor de speler
                $score['home_player_team'] = $this->getPlayerActualTeamName($score['home_player_name']);
                $score['away_player_team'] = $this->getPlayerActualTeamName($score['away_player_name']);
            }
            $data['division_name'] = $game->division->name;
        } else {
            Log::error('Game niet gevonden voor live score', ['game_id' => $liveScore->game_id]);
        }

        Log::info('Live data for game', ['game_id' => $liveScore->game_id, 'data' => $data]);

        return $data;
    });

    return view('live-scores', ['liveData' => $liveData]);
}


protected function getPlayerActualTeamName($playerName)
{
    $parts = explode(' ', $playerName);
    if (count($parts) < 2) {
        Log::warning('Ongeldige spelernaam', ['player_name' => $playerName]);
        return 'Onbekend Team';
    }

    // Zoek de speler op basis van voor- en achternaam zonder teambeperking
    $player = Player::where('first_name', $parts[0])
                    ->where('last_name', $parts[1])
                    ->with('team')
                    ->first();

    if ($player && $player->team) {
        Log::info('Speler gevonden', ['player_name' => $playerName, 'team_name' => $player->team->name]);
        return $player->team->name;
    } else {
        Log::error('Speler niet gevonden of geen team gekoppeld', ['player_name' => $playerName]);
        return 'Onbekend Team';
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
                    'away_score' => $game->away_score,
                ]);
            } elseif ($teamId === $game->away_team_id) {
                $game->update([
                    'forfeit_by' => 'away',
                    'forfeit_confirmed' => false,
                ]);
                // Notify home team for confirmation
            }
            return redirect()->route('games.show', $game->id)->with('success', 'Forfeit request submitted.');
        }
        return redirect()->route('games.show', $game->id)->withErrors(['msg' => 'You are not authorized to forfeit this game.']);
    }

    public function confirmForfeit(Request $request, Game $game)
    {
        $user = auth()->user();
        if ($user->role === 'admin' || $user->team_id === $game->home_team_id) {
            $game->update(['forfeit_confirmed' => true, 'away_score' => 0]);
            return redirect()->route('games.show', $game->id)->with('success', 'Forfeit confirmed.');
        }
        return redirect()->route('games.show', $game->id)->withErrors(['msg' => 'You are not authorized to confirm this forfeit.']);
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

    return $teams->map(function ($team) {
        $gamesWon = 0;
        $gamesLost = 0;
        $gamesDraw = 0;

        // Iterate over home games
        foreach ($team->gamesHome as $game) {
            if ($game->home_score > $game->away_score) {
                $gamesWon++;
            } elseif ($game->home_score == $game->away_score) {
                $gamesDraw++;
            } else {
                $gamesLost++;
            }
        }

        // Iterate over away games
        foreach ($team->gamesAway as $game) {
            if ($game->away_score > $game->home_score) {
                $gamesWon++;
            } elseif ($game->away_score == $game->home_score) {
                $gamesDraw++;
            } else {
                $gamesLost++;
            }
        }

        // Points: 2 for a win, 1 for a draw, 0 for a loss
        return [
            'team_id' => $team->id,
            'team_name' => $team->name,
            'games_won' => $gamesWon,
            'games_lost' => $gamesLost,
            'games_draw' => $gamesDraw,
            'points' => $gamesWon * 2 + $gamesDraw
        ];
    })->sortByDesc('points')->values()->all();
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

        $homeTeamPlayers = $game->homeTeam->club->teams->flatMap(function ($team) {
            return $team->players;
        })->unique('id');

        $awayTeamPlayers = $game->awayTeam->club->teams->flatMap(function ($team) {
            return $team->players;
        })->unique('id');

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
