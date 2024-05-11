<?php

namespace App\Providers;

use App\Models\Division;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

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
    }

}
