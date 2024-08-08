<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Game;
use App\Models\Team;
use App\Models\Manche;
use App\Models\Player;
use App\Models\Season;
use App\Models\Division;
use App\Events\ScoreUpdated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Notifications\GameApprovalNotification;

class GameController extends Controller
{
    public function index(Request $request)
    {
        $divisionId = $request->query('division_id');
        $seasonId = $request->query('season_id');

        $upcomingGames = Game::with(['homeTeam', 'awayTeam'])
                            ->where('division_id', $divisionId)
                            ->where('season_id', $seasonId)
                            ->where('date', '>=', now())
                            ->orderBy('date', 'asc')
                            ->get();

        $pastGames = Game::with(['homeTeam', 'awayTeam'])
                         ->where('division_id', $divisionId)
                         ->where('season_id', $seasonId)
                         ->where('date', '<', now())
                         ->orderBy('date', 'desc')
                         ->paginate(5);

        $divisions = Division::all();
        $seasons = Season::all();
        $currentSeasonId = $seasonId ?? Season::latest('id')->value('id');

        return view('games.index', compact('upcomingGames', 'pastGames', 'divisions', 'seasons', 'currentSeasonId'));
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
        Log::info('Game data:', $game->toArray());
        $teams = Team::all();
        $game->load('homeTeam', 'awayTeam');

        if (is_string($game->date)) {
            $game->date = \Carbon\Carbon::parse($game->date);
        }

        return view('games.edit', compact('game', 'teams'));
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
        ]);

        if ($request->filled('is_bye')) {
            $validatedData['home_team_id'] = null;
            $validatedData['away_team_id'] = null;
            $validatedData['bye_team_id'] = $validatedData['bye_team_id'];
        } else {
            $validatedData['bye_team_id'] = null;
        }

        $game->update($validatedData);

        return redirect()->route('games.for-division-season', ['division_id' => $game->division_id, 'season_id' => $game->season_id])->with('success', 'Wedstrijd succesvol bijgewerkt!');
    }

    public function requestApproval(Game $game)
    {
        if (auth()->user()->team_id == $game->away_team_id || auth()->user()->role == 'admin') {
            return view('games.approval', compact('game'));
        }
        return redirect()->route('games.index')->withErrors(['msg' => 'You are not authorized to approve this game.']);
    }

    public function approve(Request $request, Game $game)
    {
        $user = auth()->user();
        if ($user->team_id == $game->away_team_id || $user->role == 'admin') {
            $game->update(['away_team_approved' => true]);
            return redirect()->route('dashboard')->with('success', 'Wedstrijd succesvol goedgekeurd!');
        }
        return redirect()->route('games.index')->withErrors(['msg' => 'You are not authorized to approve this game.']);
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
    $game->load(['homeTeam', 'awayTeam', 'manches.player1', 'manches.player2']);
    
    Log::info('Game data:', $game->toArray());
    Log::info('Manches data:', $game->manches->toArray());

    return view('games.show', compact('game'));
}

    public function showLiveScores()
    {
        $today = Carbon::today();
        $matches = Game::with(['homeTeam', 'awayTeam'])
                        ->whereDate('date', $today)
                        ->get();

        return view('live-scores', compact('matches'));
    }

    public function updateLiveScore(Request $request, Game $game)
    {
        Log::info('updateLiveScore called for game ID: ' . $game->id);
        $validatedData = $request->validate([
            'home_score' => 'required|integer',
            'away_score' => 'required|integer',
        ]);

        Log::info('Validated data:', $validatedData);

        $game->update([
            'home_score' => $validatedData['home_score'],
            'away_score' => $validatedData['away_score'],
        ]);

        event(new ScoreUpdated([
            'match_id' => $game->id,
            'home_score' => $game->home_score,
            'away_score' => $game->away_score,
        ]));

        Log::info('Game updated:', $game->toArray());

        return response()->json(['success' => true]);
    }

    public function fetchLiveScores()
    {
        $matches = Game::with(['homeTeam', 'awayTeam'])->get();

        return response()->json([
            'matches' => $matches->map(function ($match) {
                return [
                    'match_id' => $match->id,
                    'home_score' => $match->home_score,
                    'away_score' => $match->away_score,
                    'updated_at' => $match->updated_at->setTimezone('Europe/Brussels')->format('H:i:s'),
                ];
            }),
        ]);
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

    private function calculateDivisionStandings(Division $division, $seasonId)
    {
        $teams = $division->teams()->with([
            'gamesHome' => function ($query) use ($seasonId) {
                $query->where('season_id', $seasonId)->whereNotNull('home_score')->whereNotNull('away_score');
            },
            'gamesAway' => function ($query) use ($seasonId) {
                $query->where('season_id', $seasonId)->whereNotNull('home_score')->whereNotNull('away_score');
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

            return [
                'team_id' => $team->id,
                'team_name' => $team->name,
                'games_won' => $gamesWon,
                'games_lost' => $gamesLost,
                'games_draw' => $gamesDraw,
                'points' => $gamesWon * 3 + $gamesDraw // 3 points for a win, 1 point for a draw
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

    // Fetch upcoming games for the current division and selected season
    $upcomingGames = Game::with(['homeTeam', 'awayTeam'])
                         ->where('division_id', $division_id)
                         ->where('season_id', $season->id)
                         ->where('date', '>=', $currentDateTime)
                         ->whereNull('bye_team_id')
                         ->orderBy('date', 'asc')
                         ->get()
                         ->groupBy(function($game) {
                             return \Carbon\Carbon::parse($game->date)->format('Y-m-d');
                         });

    // Fetch past games
    $pastGames = Game::with(['homeTeam', 'awayTeam'])
                     ->where('division_id', $division_id)
                     ->where('season_id', $season->id)
                     ->where('date', '<', $currentDateTime)
                     ->whereNull('bye_team_id')
                     ->orderBy('date', 'desc')
                     ->get();

    return view('games.list', compact('division', 'upcomingGames', 'pastGames', 'seasons', 'season', 'currentDateTime'));
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
        $game->delete();
        return redirect($request->headers->get('referer'))->with('success', 'Wedstrijd succesvol verwijderd.');
    }
}
