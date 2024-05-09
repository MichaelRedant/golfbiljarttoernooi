<?php

// app/Http/Controllers/TeamController.php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Models\Season;
use App\Models\Division;
use Illuminate\Http\Request;
use App\Services\TeamStatsService;

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

    public function show(Team $team, Request $request)
{
    $currentSeasonId = $request->query('season_id', Season::latest()->first()->id); // Verkrijg het seizoen ID van de query of gebruik het laatste seizoen
    $seasons = Season::all(); // Haal alle seizoenen op

    // Bereken of haal team statistieken op
    $teamStats = $team->calculateStatsForSeason($currentSeasonId);

    // Zorg ervoor dat alle statistieken zijn gedefinieerd
    $defaultStats = [
        'games_won' => 0,
        'games_lost' => 0,
        'games_draw' => 0,
        'points' => 0
    ];

    // Combineer default stats met daadwerkelijke stats
    $teamStats = array_merge($defaultStats, $teamStats);

    return view('teams.show', compact('team', 'teamStats', 'seasons', 'currentSeasonId'));
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

    public function calculateTeamStandings(Request $request)
    {
        $currentSeasonId = $request->query('season_id', Season::latest()->first()->id); // Verkrijg seizoen ID uit query parameter of gebruik het laatste seizoen
        $seasons = Season::all(); // Haal alle seizoenen op voor de dropdown
        $teams = Team::with(['gamesHome' => function ($query) use ($currentSeasonId) {
                        $query->where('season_id', $currentSeasonId)
                              ->whereNotNull('home_score')
                              ->whereNotNull('away_score');
                    }, 'gamesAway' => function ($query) use ($currentSeasonId) {
                        $query->where('season_id', $currentSeasonId)
                              ->whereNotNull('home_score')
                              ->whereNotNull('away_score');
                    }])->get();
    
        $standings = $teams->map(function ($team) {
            // Bereken gewonnen, verloren en gelijke games
            $gamesWon = $team->gamesHome->reduce(function ($carry, $game) {
                return $carry + (($game->home_score > $game->away_score) ? 1 : 0);
            }, 0) + $team->gamesAway->reduce(function ($carry, $game) {
                return $carry + (($game->away_score > $game->home_score) ? 1 : 0);
            }, 0);
    
            $gamesLost = $team->gamesHome->reduce(function ($carry, $game) {
                return $carry + (($game->home_score < $game->away_score) ? 1 : 0);
            }, 0) + $team->gamesAway->reduce(function ($carry, $game) {
                return $carry + (($game->away_score < $game->home_score) ? 1 : 0);
            }, 0);
    
            $gamesDraw = $team->gamesHome->reduce(function ($carry, $game) {
                return $carry + (($game->home_score === $game->away_score) ? 1 : 0);
            }, 0) + $team->gamesAway->reduce(function ($carry, $game) {
                return $carry + (($game->away_score === $game->home_score) ? 1 : 0);
            }, 0);
    
            return [
                'team_id' => $team->id,
                'team_name' => $team->name,
                'games_won' => $gamesWon,
                'games_lost' => $gamesLost,
                'games_draw' => $gamesDraw,
                'points' => $gamesWon * 3 + $gamesDraw // 3 punten voor een overwinning, 1 punt voor een gelijkspel
            ];
        })->sortByDesc('points')->values()->all();
    
        return view('teams.standings', ['standings' => $standings, 'seasons' => $seasons, 'currentSeasonId' => $currentSeasonId]);
    }
    
public function getTeamsByDivision(Request $request)
{
    $divisionId = $request->query('division_id');
    $teams = Team::where('division_id', $divisionId)->get();
    return response()->json($teams);
}

public function moveToDivision(Request $request, Team $team)
    {
        $request->validate([
            'new_division_id' => 'required|exists:divisions,id',  // Zorg ervoor dat de nieuwe divisie bestaat
        ]);

        // Update het team met de nieuwe divisie ID
        $team->update(['division_id' => $request->new_division_id]);

        // Redirect terug naar een relevante pagina met een success bericht
        return back()->with('success', 'Team succesvol verplaatst naar een nieuwe divisie.');
    }


public function removeFromDivision(Request $request, Team $team)
{
    // Veronderstellen dat er een 'division_id' attribuut is dat null kan worden gemaakt of aangepast
    $team->division_id = null; // of stel in op een andere divisie ID
    $team->save();

    return redirect()->route('divisions.show', $team->division_id)->with('success', 'Team verwijderd uit divisie.');
}



    public function destroy(Team $team)
    {
        $team->delete();

        return redirect()->route('teams.index')->with('success', 'Team deleted successfully.');
    }
}

