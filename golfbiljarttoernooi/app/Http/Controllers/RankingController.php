<?php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Models\User;
use App\Models\Player;
use App\Models\Season;
use App\Models\Division;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RankingController extends Controller
{
    public function index(Request $request)
    {
        $seasons = Season::all();
        $currentSeasonId = $request->input('season_id', Season::latest('id')->first()->id);
        $divisions = Division::all();

        return view('rankings.index', compact('divisions', 'seasons', 'currentSeasonId'));
    }
    
    public function teamRankings(Request $request, Division $division)
{
    $seasonId = $request->input('season_id', Season::latest()->first()->id);
    $teams = $division->teams()->with(['games' => function($query) use ($seasonId) {
        $query->where('season_id', $seasonId);
    }])->get();

    // Controleer of er daadwerkelijk games zijn
    $hasGames = $teams->pluck('games')->flatten()->isNotEmpty();

    return view('rankings.teams', compact('division', 'teams', 'seasonId', 'hasGames'));
}

    
public function getRankingsForDivisionAndSeason($divisionId, $seasonId) {
    // Eerst haal je de teams van de divisie op voor het geselecteerde seizoen
    $teams = Team::where('division_id', $divisionId)->with(['games' => function($query) use ($seasonId) {
        $query->where('season_id', $seasonId);
    }])->get();

    $rankings = $teams->map(function ($team) {
        // Bereken totaal gewonnen, verloren, gelijk en punten
        $gamesWon = $team->games->where('result', 'win')->count();
        $gamesLost = $team->games->where('result', 'loss')->count();
        $gamesDraw = $team->games->where('result', 'draw')->count();
        $points = ($gamesWon * 3) + $gamesDraw; // Stel dat een winst 3 punten geeft en een gelijkspel 1 punt

        return [
            'team_name' => $team->name,
            'games_won' => $gamesWon,
            'games_lost' => $gamesLost,
            'games_draw' => $gamesDraw,
            'points' => $points
        ];
    });

    // Sorteer rankings op punten, daarna op gewonnen games
    $sortedRankings = $rankings->sortByDesc('points')->values();

    return response()->json($sortedRankings);
}
    

public function playerRankings(Request $request, Division $division)
{
    $seasonId = $request->input('season_id', Season::latest()->first()->id);
    $players = Player::where('division_id', $division->id)->with(['games' => function($query) use ($seasonId) {
        $query->where('season_id', $seasonId); // Zorg ervoor dat alleen games van het geselecteerde seizoen worden meegenomen
    }])->get();

    return view('rankings.players', compact('division', 'players', 'seasonId'));
}
}
