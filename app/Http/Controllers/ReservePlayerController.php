<?php

// app/Http/Controllers/ReservePlayerController.php

namespace App\Http\Controllers;

use App\Models\ReservePlayer;
use Illuminate\Http\Request;

class ReservePlayerController extends Controller
{
    public function index()
    {
        $reservePlayers = ReservePlayer::all();
        return view('reserve_players.index', compact('reservePlayers'));
    }

    // Andere methoden voor ReservePlayerController
}

