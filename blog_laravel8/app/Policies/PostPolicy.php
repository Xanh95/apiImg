<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use App\Traits\HasPermissionsTrait;


class PostPolicy
{
    use HandlesAuthorization, HasPermissionsTrait;

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

    public function update(User $user)
    {

        return $user->hasRole('edit');
    }

    public function delete(User $user)
    {
    }
}
