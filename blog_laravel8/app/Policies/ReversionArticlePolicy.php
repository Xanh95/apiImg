<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use App\Traits\HasPermissionsTrait;
use App\Models\ReversionArticle;

class ReversionArticlePolicy
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

    public function update(User $user, ReversionArticle $reversion)
    {
        return $user->hasPermission('update');
    }

    public function delete(User $user)
    {
        return $user->hasPermission('delete');
    }
}
