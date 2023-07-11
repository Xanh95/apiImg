<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\ApiLoginrequest;
use Illuminate\Http\Request;
use App\Http\Requests\Apirequest;
use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class UserController extends ResponseApiController
{
    //
    public function register(Apirequest $request)
    {
        $request->validate([
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8',
            'name' => 'required|max:150'
        ]);
        $user = new User;
        $user->fill($request->all());
        $user->password = Hash::make($request->password);
        $user->save();
        return $this->handleSuccess($user, 'success');
    }
    public function login(ApiLoginrequest $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:8',
        ]);
        if (Auth::attempt([
            'email' => $request->email,
            'password' => $request->password
        ])) {
            $user = User::whereEmail($request->email)->first();
            $user->token = $user->createToken('App')->accessToken;
            return $this->handleSuccess($user, 'success');
        }
        return $this->handleError('wrong passsword or email', 401);
    }
    public function userInfo(Request $request)
    {
        $user = $request->user('api');
        return $this->handleSuccess($user, 'success');
    }
}
