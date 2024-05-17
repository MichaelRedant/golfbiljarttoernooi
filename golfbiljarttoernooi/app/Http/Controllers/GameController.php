<?php

namespace App\Http\Controllers;

use App\Models\Division;
use App\Models\Game;
use App\Models\Manche;
use App\Models\Player;
use App\Models\Season;
use App\Models\Team;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GameController extends Controller
{
    public function index(Request $request)
{
    $seasons = Season::all();
    $divisions = Division::all();
    $currentSeasonId = $request->input('season_id', Season::latest('id')->first()->id);
    $divisionId = $request->input('division_id', $divisions->first()->id ?? null);

    if (!$divisionId) {
        return redirect()->route('home')->withErrors('Divisie niet gevonden');
    }

    $upcomingGames = Game::with(['homeTeam', 'byeTeam'])
        ->where('division_id', $divisionId)
        ->where('season_id', $currentSeasonId)
        ->where('date', '>=', Carbon::now())
        ->orderBy('date', 'asc')
        ->get();

    return view('games.index', compact('seasons', 'divisions', 'upcomingGames', 'currentSeasonId'));
}


public function create(Request $request, $division_id = null, $season_id = null)
{
    $divisions = Division::all();
    $teams = Team::all();
    $seasons = Season::all();

    $selectedDivisionId = $division_id ?? $divisions->first()->id ?? null;
    $selectedSeasonId = $season_id ?? Season::latest('id')->first()->id;

    return view('games.create', compact('divisions', 'teams', 'seasons', 'selectedDivisionId', 'selectedSeasonId'));
}


public function store(Request $request)
{
    Log::info('Store method called');
    Log::info('Request data: ', $request->all());

    $validatedData = $request->validate([
        'home_team_id' => 'nullable|exists:teams,id',
        'away_team_id' => 'nullable|exists:teams,id',
        'bye_team_id' => 'nullable|exists:teams,id',
        'date' => 'required|date',
        'season_id' => 'required|exists:seasons,id',
        'scores' => 'required|array',
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

    $homeTeam = Team::find($validatedData['home_team_id']);
    $awayTeam = Team::find($validatedData['away_team_id']);

    if ($homeTeam && $awayTeam) {
        $divisionId = $homeTeam->division_id == $awayTeam->division_id ? $homeTeam->division_id : null;
        if (!$divisionId) {
            return back()->withErrors(['msg' => 'Teams must be in the same division']);
        }
    } else {
        return back()->withErrors(['msg' => 'Invalid teams']);
    }

    $validatedData['division_id'] = $divisionId;

    $game = Game::create($validatedData);

    if (!$validatedData['bye_team_id']) {
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
        }

        Log::info('Home Wins: ' . $homeWins);
        Log::info('Away Wins: ' . $awayWins);

        $game->update([
            'home_score' => $homeWins,
            'away_score' => $awayWins,
        ]);
    }

    return redirect()->route('games.index')->with('success', 'Wedstrijd succesvol aangemaakt!');
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
    Log::info('Request data: ', $request->all());

    $validatedData = $request->validate([
        'home_team_id' => 'required|exists:teams,id',
        'away_team_id' => 'required|exists:teams,id',
        'date' => 'required|date',
        'season_id' => 'required|exists:seasons,id',
        'home_score' => 'required|integer',
        'away_score' => 'required|integer',
        'home_captain' => 'nullable|exists:players,id',
        'away_captain' => 'nullable|exists:players,id',
        'home_reserve' => 'nullable|exists:players,id',
        'away_reserve' => 'nullable|exists:players,id',
    ]);

    $homeTeam = Team::find($validatedData['home_team_id']);
    $awayTeam = Team::find($validatedData['away_team_id']);

    if ($homeTeam && $awayTeam) {
        $divisionId = $homeTeam->division_id == $awayTeam->division_id ? $homeTeam->division_id : null;
        if (!$divisionId) {
            return back()->withErrors(['msg' => 'Teams must be in the same division']);
        }
    } else {
        return back()->withErrors(['msg' => 'Invalid teams']);
    }

    $validatedData['division_id'] = $divisionId;

    $game->update($validatedData);

    return redirect()->route('games.index')->with('success', 'Wedstrijd succesvol bijgewerkt!');
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


public function show(Game $game)
{
    $game->load('homeTeam', 'awayTeam', 'manches');
    return view('games.show', compact('game'));
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


public function showGamesForDivisionAndSeason(Request $request, $division_id, $season_id = null)
{
    $division = Division::findOrFail($division_id);
    $seasons = Season::all();
    $season_id = $season_id ?? Season::latest('id')->first()->id;
    $season = Season::findOrFail($season_id);

    $games = Game::where('division_id', $division_id)
        ->where('season_id', $season_id)
        ->where('date', '>=', Carbon::now())
        ->orderBy('date', 'asc')
        ->get();

    return view('games.list', compact('games', 'division', 'season', 'seasons'));
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
        for ($j = 0; $j < $matchesPerRound; $j++) {
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

    
    public function destroy(Game $game)
    {
        $game->delete();
        return redirect()->route('games.index');
    }
    
}