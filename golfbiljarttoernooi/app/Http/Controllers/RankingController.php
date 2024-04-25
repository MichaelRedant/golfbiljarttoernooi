<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
Use App\Models\Division;
use App\Models\Player;

class RankingController extends Controller
{
    public function index()
    {
        $divisions = Division::all();
        return view('rankings.index', compact('divisions'));
    }
    
    public function teamRankings(Division $division)
    {
        $teams = $division->teams; // Zorg ervoor dat je de relaties correct laadt
        return view('rankings.teams', compact('division', 'teams'));
    }
    
    public function playerRankings(Division $division)
    {
        $players = Player::where('division_id', $division->id)->get();
        return view('rankings.players', compact('division', 'players'));
    }
    
}
