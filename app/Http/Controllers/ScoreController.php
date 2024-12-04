<?php
namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\LiveScore;
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

    public function getLiveScores()
    {
        // Haal de live scores op voor reguliere wedstrijden
        $liveGameScores = LiveScore::with('game.homeTeam', 'game.awayTeam', 'game.division')
            ->whereHas('game', function ($query) {
                $query->where('status', 'live');
            })
            ->get()
            ->map(function ($liveScore) {
                return array_merge($liveScore->data, ['type' => 'regular']);
            });

        // Haal de live scores op voor bekerwedstrijden
        $liveCupGameScores = LiveScore::with('cupGame.homeTeam', 'cupGame.awayTeam')
            ->whereHas('cupGame', function ($query) {
                $query->where('status', 'live');
            })
            ->get()
            ->map(function ($liveScore) {
                return array_merge($liveScore->data, ['type' => 'cup']);
            });

        // Combineer beide soorten wedstrijden in één collectie
        $liveData = $liveGameScores->merge($liveCupGameScores);

        // Check of er live data is
        if ($liveData->isEmpty()) {
            return view('live-scores', ['message' => 'Er zijn momenteel geen live wedstrijden.']);
        }

        return view('live-scores', compact('liveData'));
    }

}