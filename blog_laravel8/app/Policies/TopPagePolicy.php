<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Http\Request;

class TopPagePolicy
{
    use HandlesAuthorization;

    public function before(User $user)
    {
        if ($user->hasRole('admin')) {
            return true;
        }
    }

    public function create(Request $request, User $user)
    {
        return $user->hasPermission('create') || $request->user()->id == $user->id;
    }


    public function update(Request $request, User $user)
    {
        return $user->hasPermission('update') || $request->user()->id == $user->id;
    }
}
