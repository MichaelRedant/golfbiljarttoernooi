<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureTeamIsAuthorized
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        $game = $request->route('game');

        if ($user->role === 'admin' || $user->team_id === $game->home_team_id) {
            return $next($request);
        }
        return redirect()->route('games.index')->withErrors(['msg' => 'You are not authorized to perform this action.']);
    }
}
