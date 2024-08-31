<?php

namespace App\Http\Controllers;

use App\Models\Club;
use App\Models\Team;
use Illuminate\Http\Request;

class ClubController extends Controller
{
    public function index(Request $request)
    {
        $sort = $request->query('sort', 'name');
        $order = $request->query('order', 'asc');
        $clubs = Club::orderBy($sort, $order)->get();
        return view('clubs.index', compact('clubs'));
    }


    public function create()
    {
        return view('clubs.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'phone_number' => 'nullable|string|max:20',
        ]);
    
        Club::create($request->all());
        return redirect()->route('clubs.index')->with('success', 'Club successfully created.');
    }

    public function show(Club $club)
    {
        return view('clubs.show', compact('club'));
    }

    

    
    public function edit(Club $club)
    {
        $allTeams = Team::all(); // Retrieves all teams
        $clubTeams = $club->teams; // Ensure that the Club model has a 'teams' relationship defined
    
        return view('clubs.edit', compact('club', 'allTeams', 'clubTeams'));
    }

    public function update(Request $request, Club $club)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'location' => 'nullable|string|max:255',
        'contact_person' => 'nullable|string|max:255',
        'phone_number' => 'nullable|string|max:20',
        'teams' => 'nullable|array',
        'teams.*' => 'exists:teams,id',
    ]);

    // Update club details including location, contact person, and phone number
    $club->update($request->only(['name', 'location', 'contact_person', 'phone_number']));

    // Update teams association
    if ($request->filled('teams')) {
        // Reset the club_id for all teams that are currently associated with this club
        Team::where('club_id', $club->id)->update(['club_id' => null]);
        // Now set the club_id for the selected teams
        Team::whereIn('id', $request->teams)->update(['club_id' => $club->id]);
    } else {
        // If no teams are selected, ensure no team is linked to this club
        Team::where('club_id', $club->id)->update(['club_id' => null]);
    }

    return redirect()->route('clubs.index')->with('success', 'Club successfully updated.');
}

    public function destroy(Club $club)
    {
        $club->delete();
        return redirect()->route('clubs.index')->with('success', 'Club succesvol verwijderd.');
    }
}

