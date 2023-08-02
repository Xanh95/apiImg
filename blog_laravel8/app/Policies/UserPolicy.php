<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use App\Models\Article;

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
        return $user->hasPermission('create');
    }

    public function viewAny(User $user)
    {
        return $user->hasPermission('update');
    }

    public function update(User $user, User $targetUser)
    {
        return $user->id === $targetUser->id || $user->hasPermission('update');
    }

    public function view(User $user)
    {
        return $user->hasPermission('view');
    }

    public function delete(User $user)
    {
        return $user->hasPermission('delete');
    }

    public function approve(User $user, Article $article)
    {
    }
}
