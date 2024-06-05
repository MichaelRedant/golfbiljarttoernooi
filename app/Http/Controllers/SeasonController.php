<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\Season;
use Illuminate\Http\Request;
use App\Services\MatchService;
use Illuminate\Support\Facades\DB;
use App\Services\MatchGeneratorService;

class SeasonController extends Controller
{
    public function index()
    {
        $seasons = Season::all();
        return view('seasons.index', compact('seasons'));
    }
    
    public function create()
    {
        return view('seasons.create');
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date'
        ]);
    
        $season = Season::create($request->all());
    
        MatchService::generateMatchesForSeason($season);
    
        return redirect()->route('seasons.index')->with('success', 'Seizoen aangemaakt en wedstrijden gegenereerd.');
    }


    
    public function edit(Season $season)
    {
        return view('seasons.edit', compact('season'));
    }
    
    public function update(Request $request, Season $season)
    {
        $request->validate(['name' => 'required|string']);

        $season->update($request->all());
        // Optioneel: Update wedstrijden logica hier als dat nodig is

        return redirect()->route('seasons.index')->with('success', 'Seizoen bijgewerkt.');
    }

    protected $matchGenerator;

    public function __construct(MatchGeneratorService $matchGenerator)
    {
        $this->matchGenerator = $matchGenerator;
    }

    
    public function destroy(Season $season)
    {
        DB::transaction(function () use ($season) {
            // Verwijderen van alle games gerelateerd aan het seizoen
            Game::where('season_id', $season->id)->delete();

            $season->delete();
        });

        return redirect()->route('seasons.index')->with('success', 'Seizoen en gerelateerde wedstrijden verwijderd.');
    }

    
}
