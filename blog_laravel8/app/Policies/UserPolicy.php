<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;


class UserPolicy
{
    use HandlesAuthorization;

    public function before(User $user)
    {
        if ($user->hasRole('admin')) {
            return true;
        }
    }

    public function create(User $user)
    {
        return $user->hasRole('edit');
    }

    public function update(User $user, User $targetUser)
    {

        return $user->id === $targetUser->id || $user->hasRole('edit');
    }

    public function view(User $user, User $targetUser)
    {
        return $user->hasRole('edit') || $user->id === $targetUser->id;
    }


    public function delete(User $user)
    {
    }
}
