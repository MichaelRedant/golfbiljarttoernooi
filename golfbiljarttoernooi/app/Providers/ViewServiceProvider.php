<?php

namespace App\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use App\Models\Division;

class ViewServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot()
    {
         // De view composer die automatisch de divisies laadt voor elke view
         View::composer(['layouts.app'], function ($view) {
            $divisions = Division::all();
            $view->with('divisions', $divisions);
        });
    }
}
