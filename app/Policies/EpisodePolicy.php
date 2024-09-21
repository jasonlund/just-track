<?php

namespace App\Policies;

use App\Models\Episode;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class EpisodePolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    public function attachUser(User $user, Episode $episode)
    {
        return $user->shows()->find($episode->season->show_id)
            ? Response::allow()
            : Response::deny('You do not have this show in your library.');
    }
}
