<?php

// app/Http/Controllers/TeamController.php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Models\Division;
use Illuminate\Http\Request;

class TeamController extends Controller
{
    public function index()
    {
        $teams = Team::all();
        return view('teams.index', compact('teams'));
    }

    public function create()
    {
        // Haal alle divisies op uit de database
    $divisions = Division::all();

    return view('teams.create', compact('divisions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'division_id' => 'required|exists:divisions,id', // Zorg ervoor dat division_id bestaat in de divisions tabel
        ]);

        Team::create([
            'name' => $request->name,
            'division_id' => $request->division_id,
        ]);

        return redirect()->route('teams.index')->with('success', 'Team created successfully.');
    }

    public function show(Team $team)
{
    // Ophalen van de divisie van het team en de bijbehorende spelers
    $team->load('division', 'players');

    return view('teams.show', compact('team'));
}


    public function edit(Team $team)
    {
        // Haal alle divisies op uit de database
        $divisions = Division::all();

        return view('teams.edit', compact('team', 'divisions'));
    }

    public function update(Request $request, Team $team)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $team->update($request->all());

        return redirect()->route('teams.index')->with('success', 'Team updated successfully.');
    }

    public function destroy(Team $team)
    {
        $team->delete();

        return redirect()->route('teams.index')->with('success', 'Team deleted successfully.');
    }
}

