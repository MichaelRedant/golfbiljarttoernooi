<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Division;
use App\Models\Player;
use App\Models\Season;

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

    // Bereken standings
    $standings = $teams->map(function ($team) use ($seasonId) {
        $games = $team->games->where('season_id', $seasonId);
        $won = 0;
        $lost = 0;
        $draw = 0;
        $points = 0;
        
        foreach ($games as $game) {
            if ($game->home_team_id == $team->id) {
                if ($game->home_score > $game->away_score) {
                    $won++;
                    $points += 3; // Voorbeeld: 3 punten voor een win
                } elseif ($game->home_score == $game->away_score) {
                    $draw++;
                    $points += 1; // 1 punt voor een draw
                } else {
                    $lost++;
                }
            } else if ($game->away_team_id == $team->id) {
                if ($game->away_score > $game->home_score) {
                    $won++;
                    $points += 3;
                } elseif ($game->away_score == $game->home_score) {
                    $draw++;
                    $points += 1;
                } else {
                    $lost++;
                }
            }
        }

        return [
            'team_name' => $team->name,
            'games_won' => $won,
            'games_lost' => $lost,
            'games_draw' => $draw,
            'points' => $points,
            'team_id' => $team->id
        ];
    });

    $standings = collect($standings);

    return view('rankings.teams', compact('division', 'standings', 'seasonId'));
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
