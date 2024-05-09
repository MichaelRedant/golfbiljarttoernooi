<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use App\Models\Team;
use App\Policies\TeamPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Team::class => TeamPolicy::class,
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
