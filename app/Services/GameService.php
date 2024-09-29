<?php

namespace App\Services;

use Carbon\Carbon;

class GameService
{
    public function canStartGame($game, $isAdmin = false)
    {
        // Admins kunnen de game altijd starten/bewerken
        if ($isAdmin) {
            return true;
        }

        // Voor niet-admins: controleer het tijdsbestek
        $gameDate = Carbon::parse($game->date);
        $allowedStart = $gameDate->subHours(2); // 2 uur voor de wedstrijdtijd
        $allowedEnd = $gameDate->addHours(30);  // 30 uur na de wedstrijdtijd
        $currentDateTime = Carbon::now();

        return $currentDateTime->between($allowedStart, $allowedEnd);
    }
}

