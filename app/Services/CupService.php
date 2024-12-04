<?php

namespace App\Services;

use App\Models\Cup;
use App\Models\CupGame;
use App\Models\Team;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Exception;

class CupService
{
    /**
     * Genereer bekerwedstrijden voor de opgegeven ronde.
     */
    public function generateCupRound(Cup $cup, array $teams)
    {
        // Check of de huidige ronde beschikbaar is
        $currentRound = $cup->currentRound();
        if (!$currentRound) {
            Log::error('Huidige ronde niet gevonden voor cup ID: ' . $cup->id);
            throw new Exception('Huidige ronde niet gevonden.');
        }

        // Randomiseer of gebruik een logica om wedstrijden te maken
        $pairs = $this->generateTeamPairs($teams);
        
        foreach ($pairs as $pair) {
            try {
                CupGame::create([
                    'cup_round_id' => $currentRound->id,
                    'home_team_id' => $pair[0]->id,
                    'away_team_id' => $pair[1] ? $pair[1]->id : null, // 'null' betekent vrij
                    'date' => now(),
                    'home_team_approved' => 0,
                    'away_team_approved' => 0,
                    'forfeit_confirmed' => 0,
                ]);
            } catch (Exception $e) {
                Log::error('Fout bij het maken van CupGame voor teams: ' . $pair[0]->name . ' vs ' . ($pair[1]->name ?? 'vrij'), ['error' => $e->getMessage()]);
                throw new Exception('Fout bij het genereren van een wedstrijd.');
            }
        }
    }

    /**
     * Genereer teamparen voor de bekerwedstrijden.
     */
    private function generateTeamPairs(array $teams)
    {
        shuffle($teams); // Of gebruik een aangepaste logica voor paring

        $pairs = [];
        while (count($teams) > 1) {
            $home = array_shift($teams);
            $away = count($teams) > 0 ? array_shift($teams) : null; // Team "vrij"
            $pairs[] = [$home, $away];
        }

        return $pairs;
    }

    /**
     * Registreer uitslag voor een bekerwedstrijd.
     */
    public function registerScore(CupGame $game, $homeScore, $awayScore, $forfeit = false)
    {
        if ($forfeit) {
            $game->update([
                'forfeit_confirmed' => true,
                'home_score' => null,
                'away_score' => null,
            ]);
        } else {
            $game->update([
                'home_score' => $homeScore,
                'away_score' => $awayScore,
                'forfeit_confirmed' => false,
            ]);
        }
    }

    /**
     * Controleer of een spel kan worden gestart of aangepast.
     */
    public function canStartGame(CupGame $game, bool $isAdmin = false): bool
    {
        // Admins kunnen de game altijd starten/bewerken
        if ($isAdmin) {
            return true;
        }

        // Voor niet-admins: controleer het tijdsbestek
        $gameDate = Carbon::parse($game->date);
        $allowedTimeStart = (clone $gameDate)->subHours(2); // 2 uur voor de wedstrijdtijd
        $allowedTimeEnd = (clone $gameDate)->addHours(30);  // 30 uur na de wedstrijdtijd
        $currentDateTime = Carbon::now();

        return $currentDateTime->between($allowedTimeStart, $allowedTimeEnd) || $isAdmin;
    }
}
