<?php
namespace App\Services;

use Carbon\Carbon;
use App\Models\Game;
use App\Models\Team;
use App\Models\Season;

class MatchService
{
    public static function generateMatchesForSeason(Season $season)
    {
        $teams = Team::all();
        $numTeams = $teams->count();
        $totalRounds = $numTeams - 1;
        $matchesPerRound = intdiv($numTeams, 2);
        $matchDate = Carbon::parse($season->start_date);

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
                        'season_id' => $season->id  // Zorg ervoor dat de wedstrijden aan het seizoen gekoppeld zijn
                    ];
                }
            }
        }

        // Maak alle geplande games aan in de database
        foreach ($schedule as $gameData) {
            Game::create($gameData);
        }
    }
}
