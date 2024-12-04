<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\Game;
use App\Models\CupGame;
use App\Models\LiveScore;
use App\Models\Player;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GameService
{
    public function canStartGame($game, $isAdmin = false)
    {
        $user = auth()->user();

        // Controleer of de gebruiker is ingelogd
        if (!$user) {
            Log::info('Unauthenticated user cannot start the game');
            return false; // Niet-ingelogde gebruikers kunnen de game niet starten
        }

        // Log details van de gebruiker en de wedstrijd
        Log::info('Checking if user can start game', [
            'user_id' => $user->id,
            'user_team_id' => $user->team_id,
            'game_id' => $game->id,
            'game_home_team_id' => $game->home_team_id,
            'game_date' => $game->date,
            'isAdmin' => $isAdmin
        ]);

        // Admins kunnen de game altijd starten
        if ($isAdmin || $user->role === 'admin') {
            Log::info('User is admin or has admin privileges');
            return true;
        }

        // Alleen het thuisteam kan de wedstrijd starten
        if ($user->team_id == $game->home_team_id) {
            // Stel de tijdsperiode in: vanaf 00:00 uur van de wedstrijddag tot 30 uur na de wedstrijdtijd
            $gameDate = Carbon::parse($game->date)->setTimezone('Europe/Amsterdam');

            // Start om 00:00 op de dag van de wedstrijd
            $allowedStart = $gameDate->copy()->startOfDay(); // Begin van de wedstrijddag
            // Tot 30 uur na de wedstrijdtijd
            $allowedEnd = $gameDate->copy()->addHours(30); // 30 uur na de geplande wedstrijdtijd
            $currentDateTime = Carbon::now('Europe/Amsterdam');

            // Log de tijdsberekeningen
            Log::info('Time window for starting game', [
                'current_time' => $currentDateTime->toDateTimeString(),
                'allowed_start' => $allowedStart->toDateTimeString(),
                'allowed_end' => $allowedEnd->toDateTimeString()
            ]);

            // Controleer of de huidige tijd binnen het toegestane venster valt
            if ($currentDateTime->between($allowedStart, $allowedEnd)) {
                Log::info('User can start the game');
                return true;
            } else {
                Log::info('User cannot start the game, out of allowed time range');
            }
        } else {
            Log::info('User is not part of the home team, cannot start the game');
        }

        // Geen toegang voor het uitteam of andere teams om de wedstrijd te starten
        return false;
    }

    public function canViewGame($game)
    {
        // Iedereen kan de wedstrijd bekijken (alle teams en admin)
        return true;
    }

    public function updateLiveScore(Request $request, $game)
    {
        Log::info('Updating LiveScore for game or cup game.', ['game_id' => $game->id]);

        // Validatie van de request data
        $validatedData = $request->validate([
            'home_score' => 'required|integer',
            'away_score' => 'required|integer',
            'home_captain' => 'nullable|integer',
            'away_captain' => 'nullable|integer',
            'home_reserve' => 'nullable|integer',
            'away_reserve' => 'nullable|integer',
            'scores' => 'nullable|array',
            'forfeit_team' => 'nullable|string|in:home,away',
        ]);

        // Logica voor CupGame, indien van toepassing
        if ($game instanceof CupGame && isset($validatedData['forfeit_team'])) {
            Log::info('Forfeit not allowed for cup games', ['game_id' => $game->id]);
            unset($validatedData['forfeit_team']);
        }

        // Verwerk de spelers
        $playerIds = array_map('intval', array_filter(array_merge(
            [$validatedData['home_captain'], $validatedData['away_captain'], $validatedData['home_reserve'], $validatedData['away_reserve']],
            array_column($validatedData['scores'] ?? [], 'home_player'),
            array_column($validatedData['scores'] ?? [], 'away_player')
        )));

        $players = Player::with('team')->whereIn('id', $playerIds)->get()->keyBy('id')->toArray();

        // Verwerk de captains en reserves
        $homeCaptainName = $players[$validatedData['home_captain']]['first_name'] . ' ' . $players[$validatedData['home_captain']]['last_name'] ?? '';
        $awayCaptainName = $players[$validatedData['away_captain']]['first_name'] . ' ' . $players[$validatedData['away_captain']]['last_name'] ?? '';

        // Logica om de scores te verwerken
        $scores = [];
        foreach ($validatedData['scores'] as $index => $score) {
            $homePlayerName = $players[$score['home_player']]['first_name'] . ' ' . $players[$score['home_player']]['last_name'] ?? 'Nog niet gestart';
            $awayPlayerName = $players[$score['away_player']]['first_name'] . ' ' . $players[$score['away_player']]['last_name'] ?? 'Nog niet gestart';

            $scores[] = [
                'home_player_name' => $homePlayerName,
                'away_player_name' => $awayPlayerName,
                '1M' => $score['1M'] ?? '',
                '2M' => $score['2M'] ?? '',
                'Belle' => $score['Belle'] ?? '',
                'WinnerId' => null,
            ];
        }

        // Opslaan van de LiveScore
        LiveScore::updateOrCreate(
            ['game_id' => $game->id],
            ['data' => json_encode([
                'home_team_name' => $game->homeTeam->name ?? 'Unknown',
                'away_team_name' => $game->awayTeam->name ?? 'Unknown',
                'home_score' => $validatedData['home_score'],
                'away_score' => $validatedData['away_score'],
                'scores' => $scores,
            ])]
        );

        Log::info('Live score updated successfully for game.', ['game_id' => $game->id]);
    }
}

