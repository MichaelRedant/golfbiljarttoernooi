<?php

namespace App\Policies;

use App\Models\Game;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class GamePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any games.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        return true; // Adjust according to your logic
    }

    /**
     * Determine whether the user can view the game.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Game  $game
     * @return mixed
     */
    public function view(User $user, Game $game)
    {
        return true; // Adjust according to your logic
    }

    /**
     * Determine whether the user can create games.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        return true; // Adjust according to your logic
    }

    /**
     * Determine whether the user can update the game.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Game  $game
     * @return mixed
     */
   
    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Game $game)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Game $game)
    {
        //
    }
}
