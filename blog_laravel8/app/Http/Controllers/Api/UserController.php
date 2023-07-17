<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Auth\Events\Registered;
use App\Mail\VerifyPin;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;



class UserController extends ResponseApiController
{
    //
    public function register(Request $request)
    {
        $request->validate([
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8',
            'name' => 'required|max:150',
            'image' =>  'image|mimes:png,jpg,jpeg,svg|max:10240',
        ]);

        $user = new User;
        $image = $request->image;
        $pin = random_int(100000, 999999);


        if ($image) {
            $dirUpload = 'public/upload/user/' . date('Y/m/d');
            $title =  Str::random(10);
            if (!Storage::exists($dirUpload)) {
                Storage::makeDirectory($dirUpload, 0755, true);
            }
            $imageName = $title . '.' . $image->extension();
            $image->storeAs($dirUpload, $imageName);
            $imageUrl = asset(Storage::url($dirUpload . '/' . $imageName));
            $user->avatar = $imageUrl;
        }
        $user->email = $request->email;
        $user->role = 'user';
        $user->name = $request->name;
        $user->password = Hash::make($request->password);
        $user->pin = $pin;
        $user->save();
        // event(new Registered($user));
        Mail::to($user->email)->send(new VerifyPin($pin));

        return $this->handleSuccess($user, 'success');
    }
    public function login(Request $request)
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
    public function create(Request $request)
    {
        $request->validate([
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8',
            'name' => 'required|max:150',
            'image' =>  'image|mimes:png,jpg,jpeg,svg|max:10240',
        ]);

        $user = new User;
        $role = $request->role ?? 'user';
        $image = $request->image;
        $path = str_replace(url('/') . '/storage', 'public', $user->avatar);


        if ($image) {
            $dirUpload = 'public/upload/user/' . date('Y/m/d');
            $title =  Str::random(10);
            if (!Storage::exists($dirUpload)) {
                Storage::makeDirectory($dirUpload, 0755, true);
            }
            $imageName = $title . '.' . $image->extension();
            $image->storeAs($dirUpload, $imageName);
            if ($path) {
                Storage::delete($path);
            }
            $imageUrl = asset(Storage::url($dirUpload . '/' . $imageName));
            $user->avatar = $imageUrl;
        }
        $user->email = $request->email;
        $user->role = $role;
        $user->password = $request->password;
        $user->name = $request->name;
        $user->password = Hash::make($request->password);
        $user->save();

        return $this->handleSuccess($user, 'success');
    }

    public function edit(User $user)
    {
        $data = $user;

        return $this->handleSuccess($data, 'success');
    }
    public function update(Request $request, User $user)
    {
        $request->validate([
            'password' => 'min:8',
            'name' => 'required|max:150',
            'role' => 'required',
            'image' =>  'image|mimes:png,jpg,jpeg,svg|max:10240',
        ]);

        $role = $request->role ?? 'user';
        $image = $request->image;
        $password = $request->password;
        $name = $request->name;
        $email = $request->email;

        if ($image) {
            $dirUpload = 'public/upload/user/' . date('Y/m/d');
            $title =  Str::random(10);
            if (!Storage::exists($dirUpload)) {
                Storage::makeDirectory($dirUpload, 0755, true);
            }
            $imageName = $title . '.' . $image->extension();
            $image->storeAs($dirUpload, $imageName);
            $path = str_replace(url('/') . '/storage', 'public', $user->avatar);
            if ($path) {
                Storage::delete($path);
            }
            $imageUrl = asset(Storage::url($dirUpload . '/' . $imageName));
            $user->avatar = $imageUrl;
        }
        if ($password) {
            $user->password = Hash::make($password);
        }
        if ($email) {
            $user->email = $email;
        }
        if ($role) {
            $user->role = $role;
        }
        $user->name = $name;
        $user->save();

        return $this->handleSuccess($user, 'update success');
    }

    public function destroy(Request $request)
    {
        $request->validate([
            'ids' => 'required',
            'type' => 'required|in:delete,force_delete',
        ]);

        $ids = $request->input('ids');
        $type = $request->input('type');
        $ids = is_array($ids) ? $ids : [$ids];
        $users = User::withTrashed()->whereIn('id', $ids)->get();

        foreach ($users as $user) {
            $user->status = 'inactive';
            $user->save();
            if ($type === 'force_delete') {
                $path = str_replace(url('/') . '/storage', 'public', $user->avatar);
                if ($path) {
                    Storage::delete($path);
                }
                $user->forceDelete();
            } else {
                $user->delete();
            }
        }

        if ($type === 'force_delete') {
            return $this->handleSuccess([], 'Post force delete successfully!');
        } else {
            return $this->handleSuccess([], 'Post delete successfully!');
        }
    }
    public function restore(Request $request)
    {
        $request->validate([
            'ids' => 'required',
        ]);

        $ids = $request->input('ids');

        $ids = is_array($ids) ? $ids : [$ids];
        User::onlyTrashed()->whereIn('id', $ids)->restore();
        foreach ($ids as $id) {
            $post = User::find($id);
            $post->status = 'active';
            $post->save();
        }

        return $this->handleSuccess([], 'User restored successfully!');
    }

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
        if ($pin == $userPin) {
            $user->email_verified_at = Carbon::now();
            $user->save();
            return $this->handleSuccess([], 'xac thuc thanh cong');
        }
        return $this->handleError('xac thuc that bai', 422);
    }
    public function resendPin()
    {

        $user = User::find(Auth::id());
        $pin = random_int(100000, 999999);

        $user->pin = $pin;
        $user->save();
        Mail::to($user->email)->send(new VerifyPin($pin));

        return $this->handleSuccess([], 'resend pin success');
    }
}
