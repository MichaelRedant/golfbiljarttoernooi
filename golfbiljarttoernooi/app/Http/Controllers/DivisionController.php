<?php


namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Game;
use App\Models\Team;
use App\Models\Season;
use App\Models\Division;
use Illuminate\Http\Request;

class DivisionController extends Controller
{
    public function index()
{
    $divisions = Division::with('teams')->get(); // Load divisions with teams
    return view('divisions.index', compact('divisions'));
}

    public function getDivisions() {
    $divisions = Division::all();
    return response()->json($divisions);
}

    
public function show(Request $request, Division $division)
{
    $currentSeasonId = $request->query('season_id', Season::latest('id')->value('id'));
    $seasons = Season::all();

    if (!$currentSeasonId) {
        return back()->withErrors('Geen actief seizoen gevonden.');
    }

    // Ophalen van alle wedstrijden voor de huidige divisie en het geselecteerde seizoen
    $games = Game::with(['homeTeam', 'awayTeam'])
                 ->where('division_id', $division->id)
                 ->where('season_id', $currentSeasonId)
                 ->orderBy('date', 'asc')
                 ->get();

                 $gamesByDate = collect();
                 if ($currentSeasonId) {
                     $gamesByDate = Game::with(['homeTeam', 'awayTeam'])
                                        ->where('season_id', $currentSeasonId)
                                        ->orderBy('date', 'asc')
                                        ->get()
                                        ->groupBy('date');
                 }
    // Fetch standings
    $standings = $this->calculateDivisionStandings($division, $currentSeasonId);

    return view('divisions.show', compact('division', 'gamesByDate', 'games', 'standings', 'seasons', 'currentSeasonId'));
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

    public function create()
    {
        return view('divisions.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        Division::create($request->all());
        return redirect()->route('divisions.index');
    }
    

    public function edit(Division $division)
    {
        $teams = Team::with('club')->get(); // Load all teams with their clubs
        return view('divisions.edit', compact('division', 'teams'));
    }


    public function update(Request $request, Division $division)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'teams' => 'array', // Validate that 'teams' is an array
            'teams.*' => 'exists:teams,id', // Validate that each 'team' exists in the teams table
        ]);

        $division->update([
            'name' => $request->input('name'),
        ]);

        $division->teams()->sync($request->input('teams', [])); // Sync the selected teams with the division

        return redirect()->route('divisions.index')->with('success', 'Divisie succesvol bijgewerkt.');
    }

    public function delete(Division $division)
    {
        return view('divisions.delete', ['division' => $division]);
    }

    public function destroy(Division $division)
    {
        $division->delete();
        return redirect()->route('divisions.index');
    }
}

