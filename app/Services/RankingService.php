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
    $homeWins = $game->home_score ?? 0; // Voeg standaardwaarden toe om null te vermijden
    $awayWins = $game->away_score ?? 0;
    $seasonId = $game->season_id ?? null;

    if (!$seasonId) {
        Log::error('Season ID is null for the game', ['game_id' => $game->id]);
        return; // Return early if seasonId is missing
    }

    // Zoek of creëer teamstatistieken voor het thuis- en uitteam
    $homeTeamStats = TeamSeasonStat::firstOrNew([
        'team_id' => $game->home_team_id,
        'season_id' => $seasonId,
    ]);

    $awayTeamStats = TeamSeasonStat::firstOrNew([
        'team_id' => $game->away_team_id,
        'season_id' => $seasonId,
    ]);

    // **1. Teamscores bijwerken**
    if ($homeWins > $awayWins) {
        $homeTeamStats->games_won += 1;
        $awayTeamStats->games_lost += 1;
        $homeTeamStats->points += 2;
    } elseif ($homeWins < $awayWins) {
        $awayTeamStats->games_won += 1;
        $homeTeamStats->games_lost += 1;
        $awayTeamStats->points += 2;
    } else {
        $homeTeamStats->games_draw += 1;
        $awayTeamStats->games_draw += 1;
        $homeTeamStats->points += 1;
        $awayTeamStats->points += 1;
    }

    // **2. Match en Manche Statistieken bijwerken**
    $homeMatchesWon = 0;
    $awayMatchesWon = 0;
    $homeManchesWon = 0;
    $awayManchesWon = 0;
    $totalMatches = count($game->live_data['scores'] ?? []); // Controleer of scores bestaan
    $totalManches = $totalMatches * 2; // Standaard twee manches per match (Belle optioneel)

    foreach ($game->live_data['scores'] ?? [] as $score) { // Loop door scores als ze bestaan
        // Controleer op null voor elke waarde voordat je ermee werkt
        if (!empty($score['1M'])) {
            $score['1M'] == '1' ? $homeManchesWon++ : $awayManchesWon++;
        }

        if (!empty($score['2M'])) {
            $score['2M'] == '1' ? $homeManchesWon++ : $awayManchesWon++;
        }

        if (!empty($score['Belle'])) {
            $score['Belle'] == '1' ? $homeManchesWon++ : $awayManchesWon++;
        }

        // Bepaal winnaar van de match
        if (!empty($score['WinnerId'])) {
            if ($score['WinnerId'] == $game->home_team_id) {
                $homeMatchesWon++;
            } elseif ($score['WinnerId'] == $game->away_team_id) {
                $awayMatchesWon++;
            }
        }
    }

    // Update statistieken voor thuis- en uitteam
    $homeTeamStats->matches_won += $homeMatchesWon;
    $homeTeamStats->matches_lost += ($totalMatches - $homeMatchesWon);
    $homeTeamStats->manches_won += $homeManchesWon;
    $homeTeamStats->manches_lost += ($totalManches - $homeManchesWon);

    $awayTeamStats->matches_won += $awayMatchesWon;
    $awayTeamStats->matches_lost += ($totalMatches - $awayMatchesWon);
    $awayTeamStats->manches_won += $awayManchesWon;
    $awayTeamStats->manches_lost += ($totalManches - $awayManchesWon);

    $homeTeamStats->save();
    $awayTeamStats->save();
}



public function updatePlayerStats(Game $game)
{
    Log::info('Starting updatePlayerStats', ['game_id' => $game->id]);

    $seasonId = $game->season_id ?? null;
    $divisionId = $game->division_id ?? null;
    
    if (!$seasonId || !$divisionId) {
        Log::error('Missing season or division ID for game', ['game_id' => $game->id]);
        return;
    }

    $playerMatchStats = [];
    $playerMancheStats = [];
    $processedPlayers = [];

    foreach ($game->manches as $manche) {
        $winnerId = $manche->winner_id ?? null;
        $loserId = $winnerId === $manche->player1_id ? $manche->player2_id : $manche->player1_id;

        if (!$winnerId || !$loserId) {
            Log::warning('Missing winner or loser ID for manche', ['manche_id' => $manche->id]);
            continue; // Skip if there's no winner or loser
        }

        $processedPlayers[] = $manche->player1_id ?? 0;
        $processedPlayers[] = $manche->player2_id ?? 0;

        // Initialiseer de arrays als ze nog niet bestaan
        $playerMancheStats[$winnerId] = $playerMancheStats[$winnerId] ?? ['manches_won' => 0, 'manches_lost' => 0];
        $playerMancheStats[$loserId] = $playerMancheStats[$loserId] ?? ['manches_won' => 0, 'manches_lost' => 0];

        // Werk de manche-statistieken bij
        $playerMancheStats[$winnerId]['manches_won']++;
        $playerMancheStats[$loserId]['manches_lost']++;

        // Initialiseer de match arrays als ze nog niet bestaan
        $playerMatchStats[$winnerId] = $playerMatchStats[$winnerId] ?? ['matches_won' => 0, 'matches_lost' => 0, 'points' => 0];
        $playerMatchStats[$loserId] = $playerMatchStats[$loserId] ?? ['matches_won' => 0, 'matches_lost' => 0, 'points' => 0];

        // Werk de match-statistieken bij
        $playerMatchStats[$winnerId]['matches_won']++;
        $playerMatchStats[$winnerId]['points']++;
        $playerMatchStats[$loserId]['matches_lost']++;
    }

    $uniqueProcessedPlayers = array_unique($processedPlayers);
    if (count($uniqueProcessedPlayers) !== 12) {
        Log::error('Mismatch in the number of processed players.', [
            'expected' => 12,
            'actual' => count($uniqueProcessedPlayers),
            'processed_players' => $uniqueProcessedPlayers
        ]);
        throw new \Exception('Niet alle spelers zijn correct verwerkt.');
    }

    foreach ($playerMatchStats as $playerId => $stats) {
        $playerStats = PlayerSeasonStat::firstOrNew([
            'player_id' => $playerId,
            'season_id' => $seasonId,
            'division_id' => $divisionId
        ]);

        $playerStats->matches_won += $stats['matches_won'];
        $playerStats->matches_lost += $stats['matches_lost'];
        $playerStats->points += $stats['points'];
        $playerStats->save();

        Log::info('Database updated for matches and points', [
            'player_id' => $playerId,
            'matches_won' => $playerStats->matches_won,
            'matches_lost' => $playerStats->matches_lost,
            'points' => $playerStats->points
        ]);
    }

    foreach ($playerMancheStats as $playerId => $stats) {
        $playerStats = PlayerSeasonStat::firstOrNew([
            'player_id' => $playerId,
            'season_id' => $seasonId,
            'division_id' => $divisionId
        ]);

        $playerStats->manches_won += $stats['manches_won'];
        $playerStats->manches_lost += $stats['manches_lost'];
        $playerStats->save();

        Log::info('Database updated for manches', [
            'player_id' => $playerId,
            'manches_won' => $playerStats->manches_won,
            'manches_lost' => $playerStats->manches_lost
        ]);
    }

    Log::info('Finished updatePlayerStats', ['game_id' => $game->id]);
}


public function calculateDivisionStandings(Division $division, $seasonId)
{
    Log::info('Calculating division standings', [
        'division_id' => $division->id, 
        'season_id' => $seasonId
    ]);

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
        $gamesPlayed = 0;
        $gamesWon = 0;
        $gamesLost = 0;
        $gamesDraw = 0;
        $matchesWon = 0;
        $matchesLost = 0;
        $manchesWon = 0;
        $manchesLost = 0;

        Log::info('Processing team', ['team_id' => $team->id, 'team_name' => $team->name]);

        // Verwerk thuiswedstrijden
        foreach ($team->gamesHome as $game) {
            Log::info('Processing home game', ['game_id' => $game->id, 'home_team_id' => $game->home_team_id, 'away_team_id' => $game->away_team_id]);

            $gamesPlayed++;
            $matchesWon += $game->home_score;
            $matchesLost += $game->away_score;

            if ($game->home_score > $game->away_score) {
                $gamesWon++;
            } elseif ($game->home_score < $game->away_score) {
                $gamesLost++;
            } else {
                $gamesDraw++;
            }

            // Verwerk de manches voor thuiswedstrijden
            foreach ($game->manches as $manche) {
                // Thuisteam wint een manche als score1 of score2 == 1
                if ($manche->score1 == 1) {
                    $manchesWon++;
                } elseif ($manche->score1 == 2) {
                    $manchesLost++;
                }

                if ($manche->score2 == 1) {
                    $manchesWon++;
                } elseif ($manche->score2 == 2) {
                    $manchesLost++;
                }

                if ($manche->belle_score !== null) {
                    if ($manche->belle_score == 1) {
                        $manchesWon++;
                    } elseif ($manche->belle_score == 2) {
                        $manchesLost++;
                    }
                }
            }
        }

        // Verwerk uitwedstrijden
        foreach ($team->gamesAway as $game) {
            Log::info('Processing away game', ['game_id' => $game->id, 'home_team_id' => $game->home_team_id, 'away_team_id' => $game->away_team_id]);

            $gamesPlayed++;
            $matchesWon += $game->away_score;
            $matchesLost += $game->home_score;

            if ($game->away_score > $game->home_score) {
                $gamesWon++;
            } elseif ($game->away_score < $game->home_score) {
                $gamesLost++;
            } else {
                $gamesDraw++;
            }

            // Verwerk de manches voor uitwedstrijden
            foreach ($game->manches as $manche) {
                // Uitteam wint een manche als score1 of score2 == 2
                if ($manche->score1 == 2) {
                    $manchesWon++;
                } elseif ($manche->score1 == 1) {
                    $manchesLost++;
                }

                if ($manche->score2 == 2) {
                    $manchesWon++;
                } elseif ($manche->score2 == 1) {
                    $manchesLost++;
                }

                if ($manche->belle_score !== null) {
                    if ($manche->belle_score == 2) {
                        $manchesWon++;
                    } elseif ($manche->belle_score == 1) {
                        $manchesLost++;
                    }
                }
            }
        }

        $points = $gamesWon * 2 + $gamesDraw;

        Log::info('Team standings', [
            'team_id' => $team->id,
            'games_played' => $gamesPlayed,
            'games_won' => $gamesWon,
            'games_lost' => $gamesLost,
            'games_draw' => $gamesDraw,
            'matches_won' => $matchesWon,
            'matches_lost' => $matchesLost,
            'manches_won' => $manchesWon,
            'manches_lost' => $manchesLost,
            'points' => $points
        ]);

        return [
            'team_id' => $team->id,
            'team_name' => $team->name,
            'games_played' => $gamesPlayed,
            'games_won' => $gamesWon,
            'games_lost' => $gamesLost,
            'games_draw' => $gamesDraw,
            'points' => $points,
            'matches_won' => $matchesWon,
            'matches_lost' => $matchesLost,
            'manches_won' => $manchesWon,
            'manches_lost' => $manchesLost
        ];
    })->sortByDesc('points')->values()->all();

    Log::info('Completed division standings calculation', ['standings' => $standings]);

    return $standings;
}


public function calculatePlayerStandings($divisionId, $seasonId)
{
    Log::info('Calculating player standings.', ['division_id' => $divisionId, 'season_id' => $seasonId]);

    // Stap 1: Haal alle spelers op die normaal in deze divisie spelen
    $players = Player::whereHas('team.divisions', function($query) use ($divisionId) {
        $query->where('divisions.id', $divisionId);
    })
    ->with(['team.club.teams.gamesHome' => function($query) use ($seasonId, $divisionId) {
        $query->where('season_id', $seasonId)
              ->where('division_id', $divisionId)
              ->whereNotNull('home_score')
              ->whereNotNull('away_score');
    }, 'team.club.teams.gamesAway' => function($query) use ($seasonId, $divisionId) {
        $query->where('season_id', $seasonId)
              ->where('division_id', $divisionId)
              ->whereNotNull('home_score')
              ->whereNotNull('away_score');
    }])
    ->get();

    // Stap 2: Voeg spelers toe die in deze divisie hebben gespeeld maar er normaal niet in zitten
    $additionalPlayers = Player::whereHas('team.club.teams.gamesHome', function($query) use ($divisionId, $seasonId) {
        $query->where('division_id', $divisionId)
              ->where('season_id', $seasonId)
              ->whereNotNull('home_score')
              ->whereNotNull('away_score');
    })->orWhereHas('team.club.teams.gamesAway', function($query) use ($divisionId, $seasonId) {
        $query->where('division_id', $divisionId)
              ->where('season_id', $seasonId)
              ->whereNotNull('home_score')
              ->whereNotNull('away_score');
    })
    ->with(['team.club.teams.gamesHome' => function($query) use ($seasonId, $divisionId) {
        $query->where('season_id', $seasonId)
              ->where('division_id', $divisionId)
              ->whereNotNull('home_score')
              ->whereNotNull('away_score');
    }, 'team.club.teams.gamesAway' => function($query) use ($seasonId, $divisionId) {
        $query->where('season_id', $seasonId)
              ->where('division_id', $divisionId)
              ->whereNotNull('home_score')
              ->whereNotNull('away_score');
    }])
    ->get();

    // Merge de extra spelers met de originele spelerslijst en verwijder duplicaten
    $players = $players->merge($additionalPlayers)->unique('id');

    // Stap 3: Bereken de standings
    $standings = $players->map(function ($player) use ($divisionId, $seasonId) {
        $teamGames = $player->team->club->teams->flatMap(function($team) use ($seasonId, $divisionId) {
            return $team->gamesHome->merge($team->gamesAway)->filter(function($game) use ($seasonId, $divisionId) {
                return $game->season_id == $seasonId && $game->division_id == $divisionId;
            });
        });

        $matchesWon = 0;
        $matchesLost = 0;
        $manchesWon = 0;
        $manchesLost = 0;

        foreach ($teamGames as $game) {
            foreach ($game->manches as $manche) {
                if ($manche->player1_id == $player->id || $manche->player2_id == $player->id) {
                    // Update matches gewonnen/verloren
                    if ($manche->winner_id == $player->id) {
                        $matchesWon++;
                    } else {
                        $matchesLost++;
                    }

                    // Update manches gewonnen/verloren
                    $scores = [$manche->score1, $manche->score2];
                    if ($manche->belle_score !== null) {
                        $scores[] = $manche->belle_score;
                    }

                    foreach ($scores as $score) {
                        if ($score == 1) {
                            if ($manche->player1_id == $player->id) {
                                $manchesWon++;
                            } else {
                                $manchesLost++;
                            }
                        } elseif ($score == 2) {
                            if ($manche->player2_id == $player->id) {
                                $manchesWon++;
                            } else {
                                $manchesLost++;
                            }
                        }
                    }
                }
            }
        }

        $points = $matchesWon; // Veronderstelling: elke gewonnen match levert 1 punt op
        $matchesPlayed = $matchesWon + $matchesLost;

        return [
            'player_id' => $player->id,
            'player_name' => $player->first_name . ' ' . $player->last_name,
            'team_id' => $player->team->id,
            'team_name' => $player->team->name,
            'matches_played' => $matchesPlayed,
            'matches_won' => $matchesWon,
            'matches_lost' => $matchesLost,
            'manches_won' => $manchesWon,
            'manches_lost' => $manchesLost,
            'points' => $points,
        ];
    })
    ->sort(function($a, $b) {
        // Sorteer eerst op punten (dalend)
        if ($a['points'] != $b['points']) {
            return $b['points'] - $a['points'];
        }
        // Als punten gelijk zijn, sorteer op gewonnen manches (dalend)
        if ($a['manches_won'] != $b['manches_won']) {
            return $b['manches_won'] - $a['manches_won'];
        }
        // Als nog gelijk, sorteer op gespeelde wedstrijden (dalend)
        if ($a['matches_played'] != $b['matches_played']) {
            return $b['matches_played'] - $a['matches_played']; // Inverse logica om teams met 0 gespeelde wedstrijden onderaan te plaatsen
        }
        // Als nog gelijk, sorteer op naam (alfabetisch)
        return strcmp($a['player_name'], $b['player_name']);
    })
    ->values()
    ->all();

    Log::info('Player standings calculated.', ['standings' => $standings]);

    return $standings;
}


    

public function getDivisionsWherePlayerPlayed($playerId, $seasonId)
{
    $divisions = Game::where('season_id', $seasonId)
        ->whereHas('manches', function ($query) use ($playerId) {
            $query->where('player1_id', $playerId)
                  ->orWhere('player2_id', $playerId);
        })
        ->pluck('division_id')
        ->unique()
        ->toArray();

    return $divisions;
}

public function calculatePlayerStandingsForAllDivisions(Team $team, $seasonId)
{
    Log::info('Calculating player standings for all divisions.', ['team_id' => $team->id, 'season_id' => $seasonId]);

    // Stap 1: Haal alle spelers van het team op
    $players = $team->players;

    // Stap 2: Bereken de statistieken voor elk van deze spelers over alle divisies
    $standings = $players->map(function ($player) use ($seasonId) {
        // Haal alle wedstrijden (home en away) van de speler op voor alle divisies in het seizoen
        $teamGames = $player->team->gamesHome()
            ->where('season_id', $seasonId)
            ->whereNotNull('home_score')
            ->whereNotNull('away_score')
            ->get()
            ->merge(
                $player->team->gamesAway()
                    ->where('season_id', $seasonId)
                    ->whereNotNull('home_score')
                    ->whereNotNull('away_score')
                    ->get()
            );

        $matchesWon = 0;
        $matchesLost = 0;
        $manchesWon = 0;
        $manchesLost = 0;

        // Stap 3: Bereken de resultaten van de speler
        foreach ($teamGames as $game) {
            foreach ($game->manches as $manche) {
                if ($manche->player1_id == $player->id || $manche->player2_id == $player->id) {
                    // Update matches gewonnen/verloren
                    if ($manche->winner_id == $player->id) {
                        $matchesWon++;
                    } else {
                        $matchesLost++;
                    }

                    // Update manches gewonnen/verloren
                    $scores = [$manche->score1, $manche->score2];
                    if ($manche->belle_score !== null) {
                        $scores[] = $manche->belle_score;
                    }

                    foreach ($scores as $score) {
                        if ($score == 1) {
                            if ($manche->player1_id == $player->id) {
                                $manchesWon++;
                            } else {
                                $manchesLost++;
                            }
                        } elseif ($score == 2) {
                            if ($manche->player2_id == $player->id) {
                                $manchesWon++;
                            } else {
                                $manchesLost++;
                            }
                        }
                    }
                }
            }
        }

        $points = $matchesWon; // Veronderstelling: elke gewonnen match levert 1 punt op
        $matchesPlayed = $matchesWon + $matchesLost;

        return [
            'player_id' => $player->id,
            'player_name' => $player->first_name . ' ' . $player->last_name,
            'team_id' => $player->team->id,
            'team_name' => $player->team->name,
            'matches_played' => $matchesPlayed,
            'matches_won' => $matchesWon,
            'matches_lost' => $matchesLost,
            'manches_won' => $manchesWon,
            'manches_lost' => $manchesLost,
            'points' => $points,
        ];
    })
    ->sort(function ($a, $b) {
        // Sorteer eerst op punten (dalend)
        if ($a['points'] != $b['points']) {
            return $b['points'] - $a['points'];
        }
        // Als punten gelijk zijn, sorteer op gewonnen manches (dalend)
        if ($a['manches_won'] != $b['manches_won']) {
            return $b['manches_won'] - $a['manches_won'];
        }
        // Als nog gelijk, sorteer op gespeelde wedstrijden (dalend)
        if ($a['matches_played'] != $b['matches_played']) {
            return $b['matches_played'] - $a['matches_played']; // Inverse logica om teams met 0 gespeelde wedstrijden onderaan te plaatsen
        }
        // Als nog gelijk, sorteer op naam (alfabetisch)
        return strcmp($a['player_name'], $b['player_name']);
    })
    ->values()
    ->all();

    Log::info('Player standings calculated.', ['standings' => $standings]);

    return $standings;
}


}