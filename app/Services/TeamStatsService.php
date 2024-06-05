<?php

namespace App\Services;

use App\Models\Team;

class TeamStatsService
{
    public function calculateTeamStats(Team $team, $seasonId)
    {
        $teams = Team::with(['gamesHome' => function ($query) use ($seasonId) {
            $query->where('season_id', $seasonId)
                  ->whereNotNull('home_score')
                  ->whereNotNull('away_score');
        }, 'gamesAway' => function ($query) use ($seasonId) {
            $query->where('season_id', $seasonId)
                  ->whereNotNull('home_score')
                  ->whereNotNull('away_score');
        }])->get();

        $standings = $teams->map(function ($team) {
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
                'points' => $gamesWon * 3 + $gamesDraw
            ];
        });

        return $standings->sortByDesc('points')->values()->all();
    }
}
