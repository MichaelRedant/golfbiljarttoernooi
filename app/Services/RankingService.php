<?php
namespace App\Services;

use App\Models\Game;
use App\Models\Team;
use App\Models\Player;
use App\Models\CupGame;
use App\Models\Division;
use App\Models\TeamSeasonStat;
use App\Models\PlayerSeasonStat;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

    class RankingService

{
    public function updateTeamStats(Game $game)
    {
        $homeWins = $game->home_score ?? 0;
        $awayWins = $game->away_score ?? 0;
        $seasonId = $game->season_id ?? null;

        if (!$seasonId) {
            Log::error('Season ID is null for the game', ['game_id' => $game->id]);
            return;
        }

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

        $homeMatchesWon = 0;
        $awayMatchesWon = 0;
        $homeManchesWon = 0;
        $awayManchesWon = 0;
        $totalMatches = count($game->manches);
        $totalManches = $totalMatches * 2;

        foreach ($game->manches as $manche) {
            if ($manche->score1 == 1) {
                $homeManchesWon++;
            } elseif ($manche->score1 == 2) {
                $awayManchesWon++;
            }

            if ($manche->score2 == 1) {
                $homeManchesWon++;
            } elseif ($manche->score2 == 2) {
                $awayManchesWon++;
            }

            if ($manche->belle_score !== null) {
                if ($manche->belle_score == 1) {
                    $homeManchesWon++;
                } elseif ($manche->belle_score == 2) {
                    $awayManchesWon++;
                }
            }

            if ($manche->winner_id == $game->home_team_id) {
                $homeMatchesWon++;
            } elseif ($manche->winner_id == $game->away_team_id) {
                $awayMatchesWon++;
            }
        }

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

        Log::info('Team stats updated', [
            'home_team_stats' => $homeTeamStats,
            'away_team_stats' => $awayTeamStats,
        ]);
    }

    public function updatePlayerStats(Game $game)
{
    Log::info('Starting updatePlayerStats', ['game_id' => $game->id]);

    $seasonId = $game->season_id;
    $divisionId = $game->division_id;

    if (!$seasonId || !$divisionId) {
        Log::error('Season ID or Division ID is null for the game', ['game_id' => $game->id]);
        return;
    }

    // Haal alle betrokken spelers op uit de huidige wedstrijd
    $playerIds = $game->manches->pluck('player1_id')
        ->merge($game->manches->pluck('player2_id'))
        ->unique()
        ->filter();

    // Start een database transactie om race conditions te voorkomen
    DB::beginTransaction();

    try {
        // Verwijder de bestaande statistieken voor de betrokken spelers in deze wedstrijd
        PlayerSeasonStat::whereIn('player_id', $playerIds)
            ->where('season_id', $seasonId)
            ->where('division_id', $divisionId)
            ->delete();

        // Herbereken de statistieken voor elke speler
        foreach ($playerIds as $playerId) {
            $playerStat = PlayerSeasonStat::firstOrNew([
                'player_id' => $playerId,
                'season_id' => $seasonId,
                'division_id' => $divisionId
            ]);

            // Reset statistieken naar 0 voordat we ze opnieuw berekenen
            $playerStat->matches_played = 0;
            $playerStat->matches_won = 0;
            $playerStat->matches_lost = 0;
            $playerStat->manches_won = 0;
            $playerStat->manches_lost = 0;
            $playerStat->points = 0;

            // Haal alle wedstrijden op waarin de speler heeft gespeeld
            $games = Game::where('season_id', $seasonId)
                ->where('division_id', $divisionId)
                ->where(function($query) use ($playerId) {
                    $query->whereHas('manches', function ($query) use ($playerId) {
                        $query->where('player1_id', $playerId)
                              ->orWhere('player2_id', $playerId);
                    });
                })
                ->get();

            // Verwerk elke wedstrijd om de statistieken bij te werken
            foreach ($games as $game) {
                foreach ($game->manches as $manche) {
                    Log::info('Processing manche', [
                        'manche_id' => $manche->id,
                        'player1_id' => $manche->player1_id,
                        'player2_id' => $manche->player2_id,
                        'winner_id' => $manche->winner_id,
                        'score1' => $manche->score1,
                        'score2' => $manche->score2,
                        'belle_score' => $manche->belle_score
                    ]);

                    if ($manche->player1_id == $playerId || $manche->player2_id == $playerId) {
                        // Update matches gespeeld
                        $playerStat->matches_played += 1;

                        // Update gewonnen/verloren matches
                        if ($manche->winner_id == $playerId) {
                            $playerStat->matches_won += 1;
                            $playerStat->points += 1; // 1 punt voor het winnen van een match
                        } elseif ($manche->winner_id !== null) {
                            // Alleen verhogen als de manche een winnaar heeft en de speler verloren heeft
                            $playerStat->matches_lost += 1;
                        }

                        // Update manches gewonnen/verloren
                        $scores = [$manche->score1, $manche->score2];
                        if ($manche->belle_score !== null) {
                            $scores[] = $manche->belle_score;
                        }

                        foreach ($scores as $score) {
                            if ($score == 1 && $manche->player1_id == $playerId) {
                                $playerStat->manches_won += 1;
                            } elseif ($score == 2 && $manche->player2_id == $playerId) {
                                $playerStat->manches_won += 1;
                            } elseif ($score == 1 && $manche->player2_id == $playerId) {
                                $playerStat->manches_lost += 1;
                            } elseif ($score == 2 && $manche->player1_id == $playerId) {
                                $playerStat->manches_lost += 1;
                            }
                        }
                    }
                }
            }

            // Log de nieuwe statistieken na de update
            Log::info('Updated player stats after processing games', [
                'player_id' => $playerId,
                'matches_played' => $playerStat->matches_played,
                'matches_won' => $playerStat->matches_won,
                'matches_lost' => $playerStat->matches_lost,
                'manches_won' => $playerStat->manches_won,
                'manches_lost' => $playerStat->manches_lost,
                'points' => $playerStat->points
            ]);

            $playerStat->save();
        }

        // Commit de transactie als alle bewerkingen succesvol zijn
        DB::commit();
    } catch (\Exception $e) {
        // Rollback bij een fout
        DB::rollBack();
        Log::error('Error updating player stats', ['error' => $e->getMessage(), 'game_id' => $game->id]);
    }

    Log::info('Player stats updated for game', [
        'game_id' => $game->id,
        'player_stats' => $playerIds->toArray()
    ]);
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
    $players = Player::whereHas('team.divisions', function ($query) use ($divisionId) {
        $query->where('divisions.id', $divisionId);
    })
    ->with(['team.club.teams.gamesHome' => function ($query) use ($seasonId, $divisionId) {
        $query->where('season_id', $seasonId)
              ->where('division_id', $divisionId)
              ->whereNotNull('home_score')
              ->whereNotNull('away_score');
    }, 'team.club.teams.gamesAway' => function ($query) use ($seasonId, $divisionId) {
        $query->where('season_id', $seasonId)
              ->where('division_id', $divisionId)
              ->whereNotNull('home_score')
              ->whereNotNull('away_score');
    }])
    ->get();

    // Stap 2: Voeg spelers toe die in deze divisie hebben gespeeld maar er normaal niet in zitten
    $additionalPlayers = Player::whereHas('team.club.teams.gamesHome', function ($query) use ($divisionId, $seasonId) {
        $query->where('division_id', $divisionId)
              ->where('season_id', $seasonId)
              ->whereNotNull('home_score')
              ->whereNotNull('away_score');
    })
    ->orWhereHas('team.club.teams.gamesAway', function ($query) use ($divisionId, $seasonId) {
        $query->where('division_id', $divisionId)
              ->where('season_id', $seasonId)
              ->whereNotNull('home_score')
              ->whereNotNull('away_score');
    })
    ->with(['team.club.teams.gamesHome' => function ($query) use ($seasonId, $divisionId) {
        $query->where('season_id', $seasonId)
              ->where('division_id', $divisionId)
              ->whereNotNull('home_score')
              ->whereNotNull('away_score');
    }, 'team.club.teams.gamesAway' => function ($query) use ($seasonId, $divisionId) {
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
        // Haal alle wedstrijden (home en away) van het team van de speler op
        $teamGames = $player->team->club->teams->flatMap(function ($team) use ($seasonId, $divisionId) {
            return $team->gamesHome->merge($team->gamesAway)->filter(function ($game) use ($seasonId, $divisionId) {
                return $game->season_id == $seasonId && $game->division_id == $divisionId;
            });
        })->unique('id'); // Vermijd dubbele wedstrijden

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
                    } elseif (!is_null($manche->winner_id)) {
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

        $matchesPlayed = $matchesWon + $matchesLost;
        $points = $matchesWon; // Elke gewonnen match levert 1 punt op

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
        // Als punten gelijk zijn, sorteer op gewonnen matches (dalend)
        if ($a['matches_won'] != $b['matches_won']) {
            return $b['matches_won'] - $a['matches_won'];
        }
        // Als punten en gewonnen matches gelijk zijn, sorteer op gewonnen manches (dalend)
        if ($a['manches_won'] != $b['manches_won']) {
            return $b['manches_won'] - $a['manches_won'];
        }
        // Als alles gelijk is, sorteer op verloren matches (stijgend)
        if ($a['matches_lost'] != $b['matches_lost']) {
            return $a['matches_lost'] - $b['matches_lost'];
        }
        // Als nog gelijk, sorteer op verloren manches (stijgend)
        return $a['manches_lost'] - $b['manches_lost'];
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

    // Stap 2: Bereken de statistieken voor elke speler over alle divisies
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
            )
            ->unique('id'); // Vermijd dubbele wedstrijden

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
                    } elseif (!is_null($manche->winner_id)) {
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

        $matchesPlayed = $matchesWon + $matchesLost;
        $points = $matchesWon; // Elke gewonnen match levert 1 punt op

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
            return $b['matches_played'] - $a['matches_played'];
        }
        // Als nog gelijk, sorteer op naam (alfabetisch)
        return strcmp($a['player_name'], $b['player_name']);
    })
    ->values()
    ->all();

    Log::info('Player standings calculated.', ['standings' => $standings]);

    return $standings;
}


public function updateCupTeamStats(CupGame $game)
    {
        Log::info('Updating cup team progress for CupGame', ['game_id' => $game->id]);

        $homeScore = $game->home_score;
        $awayScore = $game->away_score;

        // Controleer of de game een geldig seizoen heeft (optioneel)
        if (!$game->cup) {
            Log::error('Cup not found for the game', ['game_id' => $game->id]);
            return;
        }

        // Bepaal welk team doorgaat op basis van de score
        if ($homeScore > $awayScore) {
            $winningTeam = $game->home_team_id;
            Log::info('Home team won the game', ['home_team_id' => $game->home_team_id]);
        } elseif ($awayScore > $homeScore) {
            $winningTeam = $game->away_team_id;
            Log::info('Away team won the game', ['away_team_id' => $game->away_team_id]);
        } else {
            Log::error('The game ended in a tie, but ties should not be possible in cup games.', ['game_id' => $game->id]);
            return;
        }

        // Markeer de game als goedgekeurd
        $game->update([
            'away_team_approved' => true,
        ]);

        $this->advanceTeamToNextRound($winningTeam, $game->cup);


        Log::info('Cup team progress updated successfully', ['winning_team_id' => $winningTeam]);
    }

    /**
 * Bepaal de naam van de volgende ronde op basis van de huidige ronde.
 *
 * @param string $currentRoundName
 * @return string
 */
private function getNextRoundName($currentRoundName)
{
    $roundOrder = ['1/8 Finale', '1/4 Finale', 'Halve Finale', 'Finale'];

    $currentIndex = array_search($currentRoundName, $roundOrder);
    return $roundOrder[$currentIndex + 1] ?? 'Finale'; // Standaard naar 'Finale' als de naam niet in de lijst staat
}


    /**
 * Stuur het winnende team door naar de volgende ronde.
 *
 * @param int $winningTeamId
 * @param Cup $cup
 */
public function advanceTeamToNextRound($winningTeamId, $cup)
{
    Log::info('Advancing team to the next round', ['winning_team_id' => $winningTeamId, 'cup_id' => $cup->id]);

    // Haal de huidige ronde op
    $currentRound = $cup->rounds()->latest('id')->first();

    // Controleer of er een volgende ronde bestaat
    $nextRound = $cup->rounds()->create([
        'cup_id' => $cup->id,
        'round_name' => $this->getNextRoundName($currentRound->round_name), // Gebruik een methode om de volgende ronde naam op te halen
        'is_knockout' => true, // Veronderstel dat alle rondes knock-out rondes zijn
    ]);

    Log::info('Next round created', ['next_round_id' => $nextRound->id]);

    // Maak een bekerwedstrijd aan voor de winnaar in de volgende ronde
    CupGame::create([
        'cup_round_id' => $nextRound->id,
        'home_team_id' => $winningTeamId, // Het winnende team gaat door naar de volgende ronde
        'date' => now(), // Stel de datum in of gebruik logica voor een wedstrijdschema
        'home_team_approved' => 0,
        'away_team_approved' => 0,
        'forfeit_confirmed' => 0,
    ]);

    Log::info('Cup game created for next round', ['next_round_id' => $nextRound->id, 'winning_team_id' => $winningTeamId]);
}

}