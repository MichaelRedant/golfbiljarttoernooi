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
        $latestSeason = Season::latest('id')->first();
        $currentSeasonId = $latestSeason ? $latestSeason->id : null;
        $divisions = Division::all();

        return view('rankings.index', compact('divisions', 'seasons', 'currentSeasonId'));
    }

    public function teamRankings(Request $request, Division $division)
    {
        $seasonId = $request->input('season_id', Season::latest()->first()->id);
        $seasons = Season::all();
        
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

        return view('rankings.teams', compact('division', 'standings', 'seasonId', 'seasons'));
    }

    public function playerRankings(Request $request, Division $division)
    {
        $seasonId = $request->input('season_id', Season::latest()->first()->id);
        $seasons = Season::all();

        $players = Player::where('division_id', $division->id)->with(['team.gamesHome' => function($query) use ($seasonId) {
            $query->where('season_id', $seasonId)->whereNotNull('home_score')->whereNotNull('away_score');
        }, 'team.gamesAway' => function($query) use ($seasonId) {
            $query->where('season_id', $seasonId)->whereNotNull('home_score')->whereNotNull('away_score');
        }])->get();

        $standings = $players->map(function ($player) {
            $teamGames = $player->team->gamesHome->merge($player->team->gamesAway);

            $gamesWon = $teamGames->reduce(function ($carry, $game) use ($player) {
                return $carry + (($game->winner_id == $player->team_id) ? 1 : 0);
            }, 0);

            $gamesLost = $teamGames->reduce(function ($carry, $game) use ($player) {
                return $carry + (($game->loser_id == $player->team_id) ? 1 : 0);
            }, 0);

            $gamesDrawn = $teamGames->reduce(function ($carry, $game) {
                return $carry + (($game->home_score === $game->away_score) ? 1 : 0);
            }, 0);

            $matchesWon = 0;
            $matchesLost = 0;
            foreach ($teamGames as $game) {
                foreach ($game->manches as $manche) {
                    if ($manche->winner_id == $player->id) {
                        $matchesWon++;
                    } else {
                        $matchesLost++;
                    }
                }
            }

            $points = $matchesWon * 3;

            return [
                'player_id' => $player->id,
                'player_name' => $player->first_name . ' ' . $player->last_name,
                'team_id' => $player->team->id,
                'team_name' => $player->team->name,
                'games_won' => $gamesWon,
                'games_lost' => $gamesLost,
                'games_drawn' => $gamesDrawn,
                'matches_won' => $matchesWon,
                'matches_lost' => $matchesLost,
                'points' => $points,
            ];
        });

        $standings = $standings->sortByDesc('points')->values();

        return view('rankings.players', compact('division', 'players', 'seasonId', 'seasons', 'standings'));
    }
}
