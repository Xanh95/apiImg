<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Models\Upload;
use Illuminate\Support\Facades\Mail;
use App\Mail\VerifyPin;
use Illuminate\Support\Facades\Auth;


class AuthController extends ResponseApiController
{
    //
    public function register(Request $request)
    {
        $request->validate([
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8|confirmed',
            'name' => 'required|max:150',
        ], [
            'email.required' => 'A email is required',
            'email.email' => 'The email must be in email format',
            'email.unique' => 'This email has already been used',
            'password.required' => 'A password is required',
            'password.min' => 'A password with a minimum of 8 characters',
            'password.confirmed' => 'The password and password confirmation are not correct',
            'name.required' => 'A name is required',
            'name.max' => 'A name with a maximum of 150 characters',
        ]);

        $user = new User;
        $url_id = $request->url_id;
        $pin = random_int(100000, 999999);


        $user->email = $request->email;
        $user->name = $request->name;
        $user->password = Hash::make($request->password);
        $user->pin = $pin;
        $user->save();
        $user->roles()->sync(2);
        if ($url_id) {
            $avatar = Upload::find($url_id)->url;
            $user->avatar = $avatar;
        }
        Mail::to($user->email)->send(new VerifyPin($pin));
        $user->role = $user->roles()->pluck('name');

        return $this->handleSuccess($user, 'register success');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:8',
        ], [
            'email.required' => 'A email is required',
            'email.email' => 'The email must be in email format',
            'password.required' => 'A password is required',
            'password.min' => 'A password with a minimum of 8 characters',
        ]);

        if (Auth::attempt([
            'email' => $request->email,
            'password' => $request->password
        ])) {
            $user = User::whereEmail($request->email)->first();
            $user->token = $user->createToken('App')->accessToken;
            $user->role = $user->roles()->pluck('name');
            $url_id = $user->avatar;
            if ($url_id) {
                $user->avatar = Upload::find($url_id)->url;
            }

            return $this->handleSuccess($user, 'get success data');
        }

        return $this->handleError('wrong password or email', 401);
    }
}
