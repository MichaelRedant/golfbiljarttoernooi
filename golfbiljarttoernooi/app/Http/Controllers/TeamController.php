<?php

// app/Http/Controllers/TeamController.php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Models\Division;
use Illuminate\Http\Request;

class TeamController extends Controller
{
    public function index(Request $request)
    {
        $divisions = Division::all();
    $selectedDivisionId = $request->input('division');
    $teams = ($selectedDivisionId) ? Division::find($selectedDivisionId)->teams : collect();
    return view('teams.index', compact('divisions', 'teams'));
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
        $team->load('division', 'players');
    
        // Hier simuleren we het ophalen van statistieken, maar je moet je logica implementeren
        $teamStats = [
            'games_won' => $team->gamesHome->where('home_score', '>', 'away_score')->count() + $team->gamesAway->where('away_score', '>', 'home_score')->count(),
            'games_lost' => $team->gamesHome->where('home_score', '<', 'away_score')->count() + $team->gamesAway->where('away_score', '<', 'home_score')->count(),
            'games_draw' => $team->gamesHome->where('home_score', '=', 'away_score')->count() + $team->gamesAway->where('away_score', '=', 'home_score')->count(),
            'points' => $team->gamesHome->sum(function ($game) {
                return $game->home_score > $game->away_score ? 3 : ($game->home_score == $game->away_score ? 1 : 0);
            }) + $team->gamesAway->sum(function ($game) {
                return $game->away_score > $game->home_score ? 3 : ($game->away_score == $game->home_score ? 1 : 0);
            }),
            // Aanname dat je een methode hebt om de rang te bepalen
            'rank' => $this->calculateRank($team)
        ];
    
        return view('teams.show', compact('team', 'teamStats'));
    }
    
    protected function calculateRank(Team $team)
    {
        // Logica om de rang van het team te berekenen
        return 1; // Voorbeeld waarde
    }


    public function edit(Team $team)
{
    $divisions = Division::all();
    $players = $team->players; // Zorg ervoor dat de relatie correct gedefinieerd is.
    $allTeams = Team::all(); // Haal alle teams op voor de dropdown.

    return view('teams.edit', compact('team', 'divisions', 'players', 'allTeams'));
}

    public function update(Request $request, Team $team)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $team->update($request->all());

        return redirect()->route('teams.index')->with('success', 'Team updated successfully.');
    }

    public function calculateTeamStandings()
{
    // Eerst halen we alle teams op met hun thuis- en uitgames
    $teams = Team::with('gamesHome', 'gamesAway')->get();

    // We zullen een array opstellen om het klassement bij te houden
    $standings = [];

    foreach ($teams as $team) {
        // Bereken de punten voor elk team
        $won = 0; // Aantal gewonnen games
        $lost = 0; // Aantal verloren games
        $draw = 0; // Aantal gelijke spelen

        foreach ($team->gamesHome as $game) {
            if ($game->home_score > $game->away_score) {
                $won++;
            } elseif ($game->home_score < $game->away_score) {
                $lost++;
            } else {
                $draw++;
            }
        }

        foreach ($team->gamesAway as $game) {
            if ($game->away_score > $game->home_score) {
                $won++;
            } elseif ($game->away_score < $game->home_score) {
                $lost++;
            } else {
                $draw++;
            }
        }

        // Voeg de teamresultaten toe aan de klassement array
        $standings[] = [
            'team_id' => $team->id,
            'team_name' => $team->name,
            'games_won' => $won,
            'games_lost' => $lost,
            'games_draw' => $draw,
            'points' => $won * 3 + $draw // 3 punten voor een overwinning, 1 punt voor een gelijkspel
        ];
    }

    // Sorteer de array op punten, dan op gewonnen games
    usort($standings, function ($a, $b) {
        if ($a['points'] === $b['points']) {
            return $b['games_won'] <=> $a['games_won'];
        }
        return $b['points'] <=> $a['points'];
    });

    // Stuur de klassement array naar een view
    return view('teams.standings', ['standings' => $standings]);
}

// In TeamController.php
public function getTeamsByDivision(Request $request)
{
    $divisionId = $request->query('division_id');
    $teams = Team::where('division_id', $divisionId)->get();

    return response()->json($teams);
}


    public function destroy(Team $team)
    {
        $team->delete();

        return redirect()->route('teams.index')->with('success', 'Team deleted successfully.');
    }
}

