<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Division;
use App\Models\Team;
use App\Models\Season;

class CheckResourcesAvailability
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $divisions = Division::all();
        $teams = Team::all();
        $seasons = Season::all();

        if ($divisions->isEmpty() || $teams->isEmpty() || $seasons->isEmpty()) {
            return redirect()->route('home')->withErrors(['msg' => 'Er zijn geen divisies, teams of seizoenen beschikbaar om een wedstrijd aan te maken.']);
        }

        return $next($request);
    }
}
