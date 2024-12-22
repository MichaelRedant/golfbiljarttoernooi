<?php

namespace App\Http\Controllers;

use App\Models\MancheCup;
use Illuminate\Http\Request;

class MancheCupController extends Controller
{
    public function index()
    {
        $manchesCups = MancheCup::all();
        return view('manche_cups.index', compact('mancheCups'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'game_id' => 'required|exists:cup_games,id', // Gebruik 'game_id' in plaats van 'cup_game_id'
            'player1_id' => 'required|exists:players,id',
            'player2_id' => 'required|exists:players,id',
            'score1' => 'required|integer',
            'score2' => 'required|integer',
            'belle_score' => 'nullable|integer',
        ]);

        MancheCup::create([
            'game_id' => $request->game_id, // Gebruik 'game_id'
            'player1_id' => $request->player1_id,
            'player2_id' => $request->player2_id,
            'score1' => $request->score1,
            'score2' => $request->score2,
            'belle_score' => $request->belle_score,
            'winner_id' => $request->score1 > $request->score2 ? $request->player1_id : $request->player2_id,
            'number' => MancheCup::where('game_id', $request->game_id)->count() + 1, // Gebruik 'game_id'
        ]);

        return redirect()->route('manche_cups.index')->with('success', 'Beker manche succesvol opgeslagen.');
    }
}
