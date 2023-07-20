<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use App\Traits\HasPermissionsTrait;
use App\Models\Post;


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
        return $user->hasPermission('create');
    }

    public function viewAny(User $user)
    {
        return $user->hasPermission('view');
    }

    public function view(User $user)
    {
        return $user->hasPermission('view');
    }


    public function update(User $user)
    {
        return $user->hasPermission('update');
    }

    public function delete(User $user)
    {
        return $user->hasPermission('delete');
    }
}
