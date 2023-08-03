<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Api\ResponseApiController;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Mail\VerifyPin;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;


class VerifyEmailController extends ResponseApiController
{

    public function verifyPin(Request $request)
    {
        $request->validate([
            'pin' => 'required|size:6',
        ]);

        $user = User::find(Auth::id());
        $pin = $request->pin;
        $userPin = $user->pin;

        $updatedAt = Carbon::parse($user->updated_at);
        $twentyFourHoursAgo = Carbon::now()->subHours(24);
        if ($updatedAt->lt($twentyFourHoursAgo)) {
            $user->pin = '';
            $user->save();
            return $this->handleError("Authentication Timeout", 410);
        }
        if ($user->hasVerifiedEmail()) {
            return $this->handleSuccess([], 'already-success');
        }
        if ($pin == $userPin) {
            $user->pin = '';
            $user->email_verified_at = Carbon::now();
            $user->status = 'active';
            $user->save();
            return $this->handleSuccess([], 'Authentication success');
        }
        return $this->handleError('Authentication fail', 422);
    }
    public function resendPin()
    {

        $user = User::find(Auth::id());
        $pin = random_int(100000, 999999);

        $updatedAt = Carbon::parse($user->updated_at);
        $twentyFourHoursAgo = Carbon::now()->subHours(24);
        if (!($updatedAt->lt($twentyFourHoursAgo))) {
            return $this->handleError("PIN is still valid", 200);
        }
        if ($user->hasVerifiedEmail()) {
            return $this->handleSuccess([], 'already-success');
        }
        $user->pin = $pin;
        $user->save();
        Mail::to($user->email)->send(new VerifyPin($pin));

        return $this->handleSuccess([], 'resend pin success');
    }
}