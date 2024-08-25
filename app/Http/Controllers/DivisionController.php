<?php


namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Game;
use App\Models\Team;
use App\Models\Season;
use App\Models\Division;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Services\RankingService;

class DivisionController extends Controller
{
    protected $rankingService;
    public function __construct(RankingService $rankingService)
    {
        $this->rankingService = $rankingService;
    }
    public function index()
    {
        Log::info('DivisionController@index reached');

        try {
            $divisions = Division::with('teams')->get();

            if ($divisions->isEmpty()) {
                Log::warning('No divisions found');
                return view('divisions.index', ['divisions' => collect()]);
            }

            Log::info('Divisions retrieved', ['divisions_count' => $divisions->count()]);
            return view('divisions.index', compact('divisions'));

        } catch (\Exception $e) {
            Log::error('Error retrieving divisions: ' . $e->getMessage());
            return response()->view('errors.500', [], 500);
        }
    }

    public function show(Request $request, Division $division)
{
    Log::info('DivisionController@show reached', ['division_id' => $division->id]);

    try {
        $latestSeason = Season::latest()->first();
        if (!$latestSeason) {
            Log::warning('No active season found');
            $currentSeasonId = null;
            $seasons = collect();
            $gamesByDate = collect();
            $standings = collect();
            session()->flash('error', 'Geen actief seizoen gevonden.');
            return view('divisions.show', compact('division', 'gamesByDate', 'standings', 'seasons', 'currentSeasonId'));
        }

        $currentSeasonId = $request->query('season_id', $latestSeason->id);
        $seasons = Season::all();

        $games = Game::with(['homeTeam', 'awayTeam'])
                     ->where('division_id', $division->id)
                     ->where('season_id', $currentSeasonId)
                     ->orderBy('date', 'asc')
                     ->get();

        $gamesByDate = $games->groupBy('date');

        // Gebruik de RankingService om de standings op te halen
        $rankingService = new RankingService();
        $standings = $rankingService->calculateDivisionStandings($division, $currentSeasonId);

        Log::info('Division data retrieved', [
            'games_count' => $games->count(),
            'standings_count' => count($standings)
        ]);

        return view('divisions.show', compact('division', 'gamesByDate', 'standings', 'seasons', 'currentSeasonId'));

    } catch (\Exception $e) {
        Log::error('Error retrieving division data: ' . $e->getMessage());
        return response()->view('errors.500', [], 500);
    }
}


    public function getTeamsByDivision($divisionId)
    {
        $division = Division::find($divisionId);
        if (!$division) {
            return response()->json(['message' => 'Division not found'], 404);
        }

        $teams = $division->teams;

        return response()->json($teams);
    }


public function create()
{
    $teams = Team::all();
    return view('divisions.create', compact('teams'));
}

public function store(Request $request)
{
    $request->validate([
        'name' => 'required|string|max:255',
    ]);

    $division = Division::create($request->all());

    if ($request->has('teams')) {
        $division->teams()->sync($request->teams);
    }

    return redirect()->route('divisions.index')->with('success', 'Divisie succesvol toegevoegd.');
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

