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
    // Haal alle divisies op behalve de huidige
    $divisions = Division::where('id', '!=', $division->id)->get();

    // Stuur een lege collectie naar de view als er geen andere divisies zijn
    if ($divisions->isEmpty()) {
        $noOtherDivisions = 'Geen andere divisies beschikbaar.';
        return view('divisions.show', compact('division', 'divisions', 'noOtherDivisions'));
    }

    return view('divisions.show', compact('division', 'divisions'));
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

