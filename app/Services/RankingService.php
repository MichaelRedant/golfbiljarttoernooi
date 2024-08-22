<?php
namespace App\Services;

use App\Models\Game;
use App\Models\Team;
use App\Models\Player;
use App\Models\Division;
use App\Models\TeamSeasonStat;
use App\Models\PlayerSeasonStat;
use Illuminate\Support\Facades\Log;

    class RankingService
{
    public function updateTeamStats(Game $game)
    {
        $homeWins = $game->home_score;
        $awayWins = $game->away_score;

        $seasonId = $game->season_id;

        // Update home team stats
        $homeTeamStats = TeamSeasonStat::firstOrNew([
            'team_id' => $game->home_team_id,
            'season_id' => $seasonId,
        ]);

        $awayTeamStats = TeamSeasonStat::firstOrNew([
            'team_id' => $game->away_team_id,
            'season_id' => $seasonId,
        ]);

        if ($homeWins > $awayWins) {
            $homeTeamStats->games_won += 1;
            $homeTeamStats->points += 2;

            $awayTeamStats->games_lost += 1;
        } elseif ($awayWins > $homeWins) {
            $awayTeamStats->games_won += 1;
            $awayTeamStats->points += 2;

            $homeTeamStats->games_lost += 1;
        } else {
            $homeTeamStats->games_draw += 1;
            $awayTeamStats->games_draw += 1;

            $homeTeamStats->points += 1;
            $awayTeamStats->points += 1;
        }

        $homeTeamStats->save();
        $awayTeamStats->save();

        Log::info('Updated team season stats', [
            'home_team_id' => $game->home_team_id,
            'away_team_id' => $game->away_team_id,
            'home_team_stats' => $homeTeamStats->toArray(),
            'away_team_stats' => $awayTeamStats->toArray(),
        ]);
    }

    public function updatePlayerStats(Game $game)
    {
        $seasonId = $game->season_id;
        $playerMatchStats = [];
        $playerMancheStats = [];

        foreach ($game->manches as $manche) {
            $winnerId = $manche->winner_id;
            $loserId = $winnerId === $manche->player1_id ? $manche->player2_id : $manche->player1_id;

            // Update manche stats
            $playerMancheStats[$winnerId]['manches_won'] = ($playerMancheStats[$winnerId]['manches_won'] ?? 0) + 1;
            $playerMancheStats[$loserId]['manches_lost'] = ($playerMancheStats[$loserId]['manches_lost'] ?? 0) + 1;

            // Update match stats
            $playerMatchStats[$winnerId]['matches_won'] = ($playerMatchStats[$winnerId]['matches_won'] ?? 0) + 1;
            $playerMatchStats[$winnerId]['points'] = ($playerMatchStats[$winnerId]['points'] ?? 0) + 1;
            $playerMatchStats[$loserId]['matches_lost'] = ($playerMatchStats[$loserId]['matches_lost'] ?? 0) + 1;
        }

        // Update in database
        foreach ($playerMatchStats as $playerId => $stats) {
            $playerStats = PlayerSeasonStat::firstOrNew([
                'player_id' => $playerId,
                'season_id' => $seasonId,
            ]);

            $playerStats->matches_won += $stats['matches_won'];
            $playerStats->matches_lost += $stats['matches_lost'];
            $playerStats->points += $stats['points'];
            $playerStats->save();

            Log::info('Database updated for matches and points (season)', [
                'player_id' => $playerId,
                'matches_won' => $playerStats->matches_won,
                'matches_lost' => $playerStats->matches_lost,
                'points' => $playerStats->points,
                'season_id' => $seasonId,
            ]);
        }

        foreach ($playerMancheStats as $playerId => $stats) {
            $playerStats = PlayerSeasonStat::firstOrNew([
                'player_id' => $playerId,
                'season_id' => $seasonId,
            ]);

            $playerStats->manches_won += $stats['manches_won'];
            $playerStats->manches_lost += $stats['manches_lost'];
            $playerStats->save();

            Log::info('Database updated for manches (season)', [
                'player_id' => $playerId,
                'manches_won' => $playerStats->manches_won,
                'manches_lost' => $playerStats->manches_lost,
                'season_id' => $seasonId,
            ]);
        }

        Log::info('Finished updatePlayerStats', ['game_id' => $game->id]);
    }

    public function calculateDivisionStandings(Division $division, $seasonId)
    {
        Log::info('Calculating division standings', ['division_id' => $division->id, 'season_id' => $seasonId]);

        // Haal alle teams in de divisie op en laad hun games in het opgegeven seizoen
        $teams = $division->teams()->with([
            'gamesHome' => function ($query) use ($seasonId) {
                $query->where('season_id', $seasonId)
                      ->whereNotNull('home_score')
                      ->whereNotNull('away_score');
            },
            'gamesAway' => function ($query) use ($seasonId) {
                $query->where('season_id', $seasonId)
                      ->whereNotNull('home_score')
                      ->whereNotNull('away_score');
            }
        ])->get();

        $standings = $teams->map(function ($team) {
            $gamesWon = 0;
            $gamesLost = 0;
            $gamesDraw = 0;

            Log::info('Processing team', ['team_id' => $team->id, 'team_name' => $team->name]);

            // Verwerk thuiswedstrijden
            foreach ($team->gamesHome as $game) {
                if ($game->home_score > $game->away_score) {
                    $gamesWon++;
                } elseif ($game->home_score == $game->away_score) {
                    $gamesDraw++;
                } else {
                    $gamesLost++;
                }
                Log::info('Processed home game', [
                    'game_id' => $game->id,
                    'home_score' => $game->home_score,
                    'away_score' => $game->away_score,
                    'games_won' => $gamesWon,
                    'games_lost' => $gamesLost,
                    'games_draw' => $gamesDraw
                ]);
            }

            // Verwerk uitwedstrijden
            foreach ($team->gamesAway as $game) {
                if ($game->away_score > $game->home_score) {
                    $gamesWon++;
                } elseif ($game->away_score == $game->home_score) {
                    $gamesDraw++;
                } else {
                    $gamesLost++;
                }
                Log::info('Processed away game', [
                    'game_id' => $game->id,
                    'home_score' => $game->home_score,
                    'away_score' => $game->away_score,
                    'games_won' => $gamesWon,
                    'games_lost' => $gamesLost,
                    'games_draw' => $gamesDraw
                ]);
            }

            // Punten: 2 voor een overwinning, 1 voor een gelijkspel, 0 voor verlies
            $points = $gamesWon * 2 + $gamesDraw;
            Log::info('Calculated points for team', [
                'team_id' => $team->id,
                'team_name' => $team->name,
                'games_won' => $gamesWon,
                'games_lost' => $gamesLost,
                'games_draw' => $gamesDraw,
                'points' => $points
            ]);

            return [
                'team_id' => $team->id,
                'team_name' => $team->name,
                'games_won' => $gamesWon,
                'games_lost' => $gamesLost,
                'games_draw' => $gamesDraw,
                'points' => $points
            ];
        })->sortByDesc('points')->values()->all();

        Log::info('Completed division standings calculation', ['standings' => $standings]);

        return $standings;
    }

    public function calculatePlayerStandings($divisionId, $seasonId)
{
    Log::info('Calculating player standings.', ['division_id' => $divisionId, 'season_id' => $seasonId]);

    $players = Player::whereHas('team.divisions', function($query) use ($divisionId) {
        $query->where('divisions.id', $divisionId);
    })
    ->with(['team.gamesHome' => function($query) use ($seasonId) {
        $query->where('season_id', $seasonId)->whereNotNull('home_score')->whereNotNull('away_score');
    }, 'team.gamesAway' => function($query) use ($seasonId) {
        $query->where('season_id', $seasonId)->whereNotNull('home_score')->whereNotNull('away_score');
    }])
    ->get();

    $standings = $players->map(function ($player) {
        $teamGames = $player->team->gamesHome->merge($player->team->gamesAway);

        $matchesWon = 0;
        $matchesLost = 0;

        foreach ($teamGames as $game) {
            foreach ($game->manches as $manche) {
                if ($manche->winner_id == $player->id) {
                    $matchesWon++;
                } else if ($manche->player1_id == $player->id || $manche->player2_id == $player->id) {
                    $matchesLost++;
                }
            }
        }

        $points = $matchesWon;

        return [
            'player_id' => $player->id,
            'player_name' => $player->first_name . ' ' . $player->last_name,
            'team_id' => $player->team->id,
            'team_name' => $player->team->name,
            'matches_won' => $matchesWon,
            'matches_lost' => $matchesLost,
            'points' => $points,
        ];
    })->sortByDesc('points')->values()->all();

    Log::info('Player standings calculated.', ['standings' => $standings]);

    return $standings;
}

}
