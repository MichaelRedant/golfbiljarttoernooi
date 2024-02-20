<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\Team;
use Carbon\Carbon;
use Illuminate\Http\Request;

class GameController extends Controller
{
    public function index()
    {
        $games = Game::all();
        return view('games.index', compact('games'));
    }

    public function calendarData()
    {
        $games = Game::all();
        $events = [];
        foreach ($games as $game) {
            $events[] = [
                'title' => $game->homeTeam->name . ' vs ' . $game->awayTeam->name,
                'start' => $game->date,
                'url'   => route('games.show', $game->id),
            ];
        }
        return response()->json($events);
    }

    public function generateMatches()
    {
        $teams = Team::all()->filter(function ($team) {
            return $team != null;
        });
        $numTeams = $teams->count();
        $totalRounds = $numTeams - 1;
        $matchesPerRound = intdiv($numTeams, 2);
        $matchDate = Carbon::now()->next('Saturday');

        if ($numTeams % 2 != 0) {
            $teams->push(null);
            $numTeams++;
        }

        for ($round = 0; $round < $totalRounds; $round++) {
            for ($match = 0; $match < $matchesPerRound; $match++) {
                $homeTeam = $teams->get($match);
                $awayTeam = $teams->get($numTeams - 1 - $match);
        
                // Zorg ervoor dat zowel thuis- als uitteams geen null zijn en daadwerkelijk instanties van Team.
                if (!is_null($homeTeam) && !is_null($awayTeam) && $homeTeam instanceof Team && $awayTeam instanceof Team) {
                    Game::create([
                        'home_team_id' => $homeTeam->id,
                        'away_team_id' => $awayTeam->id,
                        'date' => $matchDate->format('Y-m-d'),
                    ]);
                }
            }
            $teams = $this->rotateTeams($teams);
            $matchDate = $matchDate->addWeek();
        }
        

        return redirect()->route('games.index')->with('success', 'Wedstrijden succesvol gegenereerd!');
    }

    private function rotateTeams($teams)
    {
        $teamsArray = $teams->toArray();
        $firstTeam = array_shift($teamsArray);
        $lastTeam = array_pop($teamsArray);
        array_unshift($teamsArray, $firstTeam);
        array_push($teamsArray, $lastTeam);
        return collect($teamsArray);
    }

    public function clearCalendar()
    {
        Game::truncate();
        return redirect()->route('games.index')->with('success', 'Kalender succesvol verwijderd!');
    }

    public function show(Game $game)
    {
        return view('games.show', compact('game'));
    }

    public function edit(Game $game)
    {
        return view('games.edit', compact('game'));
    }

    public function update(Request $request, Game $game)
    {
        $game->update($request->all());
        return redirect()->route('games.index');
    }

    public function destroy(Game $game)
    {
        $game->delete();
        return redirect()->route('games.index');
    }
}
