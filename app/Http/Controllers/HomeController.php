<?php

// app/Http/Controllers/HomeController.php

namespace App\Http\Controllers;

use App\Models\CupGame;
use App\Models\Game;
use App\Models\News;
use Carbon\Carbon;

class HomeController extends Controller
{
    public function index()
    {
        // Haal het laatste nieuws op
        $news = News::latest()->take(3)->get();

        // Check voor live games en bekerwedstrijden
        $hasLiveMatches = Game::where('date', Carbon::today())->exists();
        $hasLiveCupMatches = CupGame::where('date', Carbon::today())->exists();

        // Zet `hasLiveButton` op true als er een van beide wedstrijden live is
        $hasLiveButton = $hasLiveMatches || $hasLiveCupMatches;

        return view('home', compact('news', 'hasLiveButton'));
    }

    public function reglement()
{
    return view('reglement');
}
}
