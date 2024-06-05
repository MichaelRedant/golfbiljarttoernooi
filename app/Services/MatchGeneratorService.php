<?php

namespace App\Services;

use App\Models\Team;
use App\Models\Game;
use Carbon\Carbon;

class MatchGeneratorService
{
    public function generateMatchesForSeason($season)
    {
        $teams = Team::all();
        $numTeams = $teams->count();
        $totalRounds = $numTeams - 1;
        $matchesPerRound = intdiv($numTeams, 2);
        $matchDate = Carbon::now()->next('Saturday');

        $schedule = [];

        for ($i = 0; $i < $totalRounds * 2; $i++) {
            for ($j = 0; $j < $matchesPerRound; $j++) {
                $home = ($i + $j) % $numTeams;
                $away = ($i + $numTeams - $j) % $numTeams;

                if ($home != $away) {
                    $schedule[] = [
                        'home_team_id' => $teams[$home]->id,
                        'away_team_id' => $teams[$away]->id,
                        'date' => $matchDate->copy()->addWeeks($i)->format('Y-m-d'),
                        'season_id' => $season->id
                    ];
                }
            }
        }

        foreach ($schedule as $gameData) {
            Game::create($gameData);
        }
    }
}
