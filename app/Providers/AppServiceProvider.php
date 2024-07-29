<?php

namespace App\Providers;

use Carbon\Carbon;
use App\Models\Game;
use App\Models\Sponsor;
use App\Models\Division;
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
        View::composer(['partials.navbar', 'other.view'], function ($view) {
            $divisions = Division::all(); // Haal alle divisies op
            $view->with('divisions', $divisions);
        });

        View::composer('*', function ($view) {
            $today = Carbon::today();
            $hasLiveMatches = Game::whereDate('date', $today)->exists();
            $view->with('hasLiveMatches', $hasLiveMatches);
            $sponsors = Sponsor::all();
            $view->with('sponsors', $sponsors);
        });
    }
}
