<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     *
     * @return void
     */
    public function checkRole(User $user, User $targetUser)
    {
        return $user->role === 'admin' || $user->id === $targetUser->id;
    }
}
