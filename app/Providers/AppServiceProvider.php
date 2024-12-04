<?php

namespace App\Providers;

use App\Models\Cup;
use App\Models\CupGame;
use App\Models\Division;
use App\Models\Game;
use App\Models\Season;
use App\Models\Sponsor;
use Carbon\Carbon;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot()
    {
        // Deel de divisies in specifieke views
        View::composer(['partials.navbar', 'other.view'], function ($view) {
            $divisions = Division::all();
            $view->with('divisions', $divisions);
        });

        // Controleer of er vandaag live games of bekerwedstrijden zijn
        View::composer('*', function ($view) {
            $today = Carbon::today();
            
            // Controleer of er een reguliere wedstrijd of bekerwedstrijd op de huidige dag is
            $hasLiveMatchesOrCupGames = Game::whereDate('date', $today)->exists() ||
                                        CupGame::whereDate('date', $today)->exists();

            $view->with('hasLiveMatchesOrCupGames', $hasLiveMatchesOrCupGames);

            // Sponsors ophalen en delen
            $sponsors = Sponsor::all();
            $view->with('sponsors', $sponsors);
        });

        // Huidig seizoen ophalen en cups delen
        $currentSeason = Season::whereDate('start_date', '<=', Carbon::today())
            ->whereDate('end_date', '>=', Carbon::today())
            ->first();

        $cups = $currentSeason ? Cup::where('season_id', $currentSeason->id)->with('division')->get() : collect();
        View::share('cups', $cups);
    }
}
