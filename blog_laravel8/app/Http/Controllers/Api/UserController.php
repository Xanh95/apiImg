<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\ApiLoginrequest;
use Illuminate\Http\Request;
use App\Http\Requests\Apirequest;
use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    //
    public function register(Apirequest $request)
    {
        $user = new User;
        $user->fill($request->all());
        $user->password = Hash::make($request->password);
        $user->save();
        return response()->json($user);
    }
    public function login(ApiLoginrequest $request)
    {
        if (Auth::attempt([
            'email' => $request->email,
            'password' => $request->password
        ])) {
            $user = User::whereEmail($request->email)->first();
            $user->token = $user->createToken('App')->accessToken;
            return response()->json($user);
        }
        return response()->json('sai ten dang nhap hoac mat khau', 401);
    }
    public function userInfo(Request $request)
    {
        return response()->json($request->user('api'));
    }
}
