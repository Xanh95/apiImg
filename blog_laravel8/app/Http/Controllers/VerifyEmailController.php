<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Api\ResponseApiController;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Models\User;

class VerifyEmailController extends ResponseApiController
{

    public function __invoke(Request $request)
    {
        $user = User::find($request->route('id'));

        if ($user->hasVerifiedEmail()) {
            return $this->handleSuccess([], 'already-success');
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        return $this->handleSuccess([], 'verify-success');
    }
}
