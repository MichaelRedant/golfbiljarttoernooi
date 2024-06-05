<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use App\Models\Game;
use App\Models\Team;
use App\Policies\GamePolicy;
use App\Policies\TeamPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Team::class => TeamPolicy::class,
        Game::class => GamePolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot()
{
    $this->registerPolicies();

    Gate::define('delete-team', function ($user, $team) {
        // Hier kun je een check toevoegen om te zien of de gebruiker admin is
        return $user->is_admin;
    });
}
}
