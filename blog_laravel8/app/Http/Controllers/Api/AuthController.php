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
            'image' =>  'image|mimes:png,jpg,jpeg,svg|max:10240',
        ], [
            'email.required' => 'A email is required',
            'email.email' => 'The email must be in email format',
            'email.unique' => 'This email has already been used',
            'password.required' => 'A password is required',
            'password.min' => 'A password with a minimum of 8 characters',
            'password.confirmed' => 'The password and password confirmation are not correct',
            'name.required' => 'A name is required',
            'name.max' => 'A name with a maximum of 150 characters',
            'image.image' => 'A image is not a image',
            'image.mimes' => 'A format of image not in png,jpg,jpeg,svg',
            'image.max' => 'Maximum file size to upload is 10MB'
        ]);

        $user = new User;
        $url_id = $request->url_id;
        $pin = random_int(100000, 999999);

        if ($url_id) {
            $user->avatar = implode('-', $url_id);
        }
        $user->email = $request->email;
        $user->name = $request->name;
        $user->password = Hash::make($request->password);
        $user->pin = $pin;
        $user->save();
        $user->roles()->sync(2);
        if ($url_id) {
            foreach ($url_id as $id) {
                $avatar[] = Upload::find($id)->url;
            }
            $user->avatar = $avatar;
        }
        Mail::to($user->email)->send(new VerifyPin($pin));

        return $this->handleSuccess($user, 'success');
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

            return $this->handleSuccess($user, 'success');
        }

        return $this->handleError('wrong password or email', 401);
    }
}
