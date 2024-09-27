<?php

namespace App\Services;

use Carbon\Carbon;

class GameService
{
    public function canStartGame($game)
    {
        $gameDate = Carbon::parse($game->date)->startOfDay();
        $allowedStart = $gameDate->copy()->subHours(0);
        $allowedEnd = $gameDate->copy()->addHours(30);
        $currentDateTime = Carbon::now();

        return $currentDateTime->between($allowedStart, $allowedEnd);
    }
}
