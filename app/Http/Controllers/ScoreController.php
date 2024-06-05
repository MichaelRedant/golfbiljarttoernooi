<?php
namespace App\Http\Controllers;

use App\Models\Game;
use App\Events\ScoreUpdated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ScoreController extends Controller
{
    
    public function updateScore(Request $request)
    {
        $validatedData = $request->validate([
            'match_id' => 'required|exists:games,id',
            'home_score' => 'required|integer',
            'away_score' => 'required|integer',
        ]);

        $score = [
            'match_id' => $validatedData['match_id'],
            'home_score' => $validatedData['home_score'],
            'away_score' => $validatedData['away_score'],
        ];

        Log::info('Broadcasting ScoreUpdated event with data: ', $score); // Added log

        event(new ScoreUpdated($score));

        return response()->json(['message' => 'Score updated!']);
    }

    public function updateLiveScore(Request $request, Game $game)
    {
        Log::info('updateLiveScore called for game ID: ' . $game->id);
        $validatedData = $request->validate([
            'home_score' => 'required|integer',
            'away_score' => 'required|integer',
        ]);

        Log::info('Validated data: ', $validatedData);

        $game->update([
            'home_score' => $validatedData['home_score'],
            'away_score' => $validatedData['away_score'],
        ]);

        Log::info('Game updated: ', $game->toArray());

        $scoreData = [
            'match_id' => $game->id,
            'home_score' => $game->home_score,
            'away_score' => $game->away_score,
        ];

        Log::info('Broadcasting ScoreUpdated event with data: ', $scoreData); // Added log

        event(new ScoreUpdated($scoreData));

        return response()->json(['success' => true]);
    }

}