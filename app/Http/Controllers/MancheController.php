<?php

// app/Http/Controllers/MancheController.php

namespace App\Http\Controllers;

use App\Models\Manche;
use Illuminate\Http\Request;

class MancheController extends Controller
{
    public function index()
    {
        $manches = Manche::all();
        return view('manches.index', compact('manches'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'game_id' => 'required|exists:games,id',
            'player1_id' => 'required|exists:players,id',
            'player2_id' => 'required|exists:players,id',
            'score1' => 'required|integer',
            'score2' => 'required|integer',
            'belle_score' => 'nullable|integer'
        ]);

        Manche::create([
            'game_id' => $request->game_id,
            'player1_id' => $request->player1_id,
            'player2_id' => $request->player2_id,
            'score1' => $request->score1,
            'score2' => $request->score2,
            'belle_score' => $request->belle_score,
            'winner_id' => $request->score1 > $request->score2 ? $request->player1_id : $request->player2_id,
            'number' => Manche::where('game_id', $request->game_id)->count() + 1, // Assign a number to each manche
        ]);

        return redirect()->route('manches.index')->with('success', 'Manche succesvol opgeslagen.');
    }

}

