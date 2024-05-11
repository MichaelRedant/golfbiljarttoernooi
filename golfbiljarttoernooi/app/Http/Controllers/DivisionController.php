<?php


namespace App\Http\Controllers;

use App\Models\Season;
use App\Models\Division;
use Illuminate\Http\Request;

class DivisionController extends Controller
{
    public function index()
{
    $divisions = Division::with('teams')->get(); // Laadt alleen teams zonder seizoensfilter

    return view('divisions.index', compact('divisions'));
}

    public function getDivisions() {
    $divisions = Division::all();
    return response()->json($divisions);
}

    
public function show(Division $division)
{
    $currentSeason = Season::latest()->first();
    if (!$currentSeason) {
        return back()->withErrors('No active season found.');
    }

    $nextGames = $division->games()
        ->where('season_id', $currentSeason->id)
        ->where('date', '>=', now())
        ->orderBy('date')
        ->limit(5)
        ->get();

    $pastGames = $division->games()
        ->where('season_id', $currentSeason->id)
        ->where('date', '<', now())
        ->orderBy('date', 'desc')
        ->limit(5)
        ->get();

    // Assuming calculateStandings is defined to calculate the standings based on games
    $standings = $this->calculateStandings($division->id);

    return view('divisions.show', compact('division', 'nextGames', 'pastGames', 'standings'));
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
        return view('divisions.edit', ['division' => $division]);
    }

    public function update(Request $request, Division $division)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $division->update($request->all());
        return redirect()->route('divisions.index');
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

