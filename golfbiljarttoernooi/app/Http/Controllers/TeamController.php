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
        $team->load('division', 'players', 'gamesHome', 'gamesAway');
    
        // Verzamel alle games thuis en uit
        $allGames = $team->gamesHome->merge($team->gamesAway);
    
        $gamesWon = $allGames->filter(function ($game) use ($team) {
            return ($game->home_team_id == $team->id && $game->home_score > $game->away_score) ||
                   ($game->away_team_id == $team->id && $game->away_score > $game->home_score);
        })->count();
    
        $gamesLost = $allGames->filter(function ($game) use ($team) {
            return ($game->home_team_id == $team->id && $game->home_score < $game->away_score) ||
                   ($game->away_team_id == $team->id && $game->away_score < $game->home_score);
        })->count();
    
        $gamesDraw = $allGames->filter(function ($game) use ($team) {
            return $game->home_score == $game->away_score;
        })->count();
    
        // Veronderstellen dat je 3 punten voor een winst en 1 punt voor een gelijkspel telt
        $points = $gamesWon * 3 + $gamesDraw * 1;
    
        $teamStats = [
            'games_won' => $gamesWon,
            'games_lost' => $gamesLost,
            'games_draw' => $gamesDraw,
            'points' => $points,
            // De berekening van 'rank' zou moeten worden geïmplementeerd op een manier die overeenkomt met de ranking logica
            'rank' => $this->calculateRank($team)
        ];
    
        return view('teams.show', compact('team', 'teamStats'));
    }
    

    protected function calculateRank(Team $team)
{
    $teams = $team->division->teams()->with(['gamesHome', 'gamesAway'])->get();

    $rankings = $teams->map(function ($team) {
        $gamesWon = $team->gamesHome->where('home_score', '>', 'away_score')->count() +
                    $team->gamesAway->where('away_score', '>', 'home_score')->count();

        $gamesDraw = $team->gamesHome->where('home_score', '=', 'away_score')->count() +
                     $team->gamesAway->where('away_score', '=', 'home_score')->count();

        $points = $gamesWon * 3 + $gamesDraw;  // Points from wins and draws

        return [
            'team_id' => $team->id,
            'points' => $points,
            'games_won' => $gamesWon,  // Additional tiebreaker
        ];
    })->sortByDesc(function ($team) {
        return [$team['points'], $team['games_won']];  // Primary sort by points, secondary by wins
    });

    // Finding the rank
    $rank = $rankings->pluck('team_id')->search($team->id) + 1;

    return $rank;
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

