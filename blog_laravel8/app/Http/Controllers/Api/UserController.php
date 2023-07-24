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
use App\Models\Role;
use App\Models\UserMeta;
use App\Models\Post;

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
        $user->name = $request->name;
        $user->password = Hash::make($request->password);
        $user->pin = $pin;
        $user->save();
        $user->roles()->sync(2);
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

        return $this->handleError('wrong password or email', 401);
    }
    public function userInfo(Request $request)
    {
        $user = $request->user('api');

        return $this->handleSuccess($user, 'success');
    }
    public function create(Request $request)
    {
        if (!$request->user()->hasPermission('create')) {
            return $this->handleError('Unauthorized', 403);
        }

        $request->validate([
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8',
            'name' => 'required|max:150',
            'image' =>  'image|mimes:png,jpg,jpeg,svg|max:10240',
        ]);
        $user = new User;
        $role = $request->role ?? 2;
        $image = $request->image;
        $path = str_replace(url('/') . '/storage', 'public', $user->avatar);


        if (!$request->user()->hasRole('admin')) {
            if ($role == 1) {
                return $this->handleError('Unauthorized', 403);
            }
        }
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
        $user->password = $request->password;
        $user->password = Hash::make($request->password);
        $user->email_verified_at = Carbon::now();
        $user->status = 'active';
        $user->save();
        $user->roles()->sync($role);

        return $this->handleSuccess($user, 'success');
    }
    public function index(Request $request)
    {
        if (!$request->user()->hasPermission('update')) {
            return $this->handleError('Unauthorized', 403);
        }

        $limit = request()->input('limit') ?? config('app.paginate');

        $data = User::latest()->with('roles')->paginate($limit);

        return $this->handleSuccess($data, 'get data user success');
    }
    public function edit(Request $request, User $user)
    {
        if (!$request->user()->hasPermission('update') && (Auth::id() != $user->id)) {
            return $this->handleError('Unauthorized', 403);
        }

        $data = $user;

        return $this->handleSuccess($data, 'success');
    }
    public function update(Request $request, User $user)
    {
        if (!$request->user()->hasPermission('update')) {
            return $this->handleError('Unauthorized', 403);
        }

        $request->validate([
            'email' => 'email|unique:users',
            'password' => 'min:8',
            'name' => 'required|max:150',
            'role' => 'required',
            'image' =>  'image|mimes:png,jpg,jpeg,svg|max:10240',
        ]);

        $role = $request->role;
        $image = $request->image;
        $password = $request->password;
        $name = $request->name;
        $email = $request->email;

        if (!$request->user()->hasRole('admin')) {
            if ($role == 1) {
                $this->handleError('Unauthorized', 403);
            }
        }
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
            $user->roles()->sync($role);
        }
        $user->name = $name;
        $user->save();
        $user->roles()->sync($role);

        return $this->handleSuccess($user, 'update success');
    }

    public function destroy(Request $request)
    {
        if (!$request->user()->hasPermission('delete')) {
            return $this->handleError('Unauthorized', 403);
        }

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
        if (!$request->user()->hasPermission('delete')) {
            return $this->handleError('Unauthorized', 403);
        }

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

    public function addFavorite(Request $request)
    {
        if (!$request->user()->hasPermission('view')) {
            return $this->handleError('Unauthorized', 403);
        }

        $request->validate([
            'favorite' => 'required|array',
            'type' => 'required'
        ]);

        $favorites = $request->favorite;
        $type = $request->type;
        $user_meta = $request->user()->userMeta();

        if ($type == 'add') {
            if ($user_meta->where('meta_key', 'favorite_post')->exists()) {
                $user_meta = $request->user()->userMeta()->where('meta_key', 'favorite_post')->first();
                $favorited = explode('-', $user_meta->meta_value);
                $favorite = array_unique(array_merge($favorited, $favorites));
                $user_meta->meta_value = implode('-', $favorite);
                $user_meta->save();

                return $this->handleSuccess([], 'favorite success');
            }
            $user_meta = new UserMeta;
            $user_meta->meta_key = 'favorite_post';
            $user_meta->meta_value = implode('-', $favorites);
            $user_meta->user_id = Auth::id();
            $user_meta->save();

            return $this->handleSuccess([], 'favorite success');
        }
        if ($type == 'sub') {
            $user_meta = $user_meta->where('meta_key', 'favorite_post')->first();

            $favorited = explode('-', $user_meta->meta_value);
            $favorite = array_diff($favorited, $favorites);
            $user_meta->meta_value = implode('-', $favorite);
            $user_meta->save();

            return $this->handleSuccess($user_meta, 'subfavorite success');
        }
    }

    public function showFavorite(Request $request)
    {
        if (!$request->user()->hasPermission('view')) {
            return $this->handleError('Unauthorized', 403);
        }
        $user_meta = $request->user()->userMeta()->where('meta_key', 'favorite_post')->first();
        $post_id = explode('-', $user_meta->meta_value);
        $data = Post::find($post_id);

        return $this->handleSuccess($data, 'get success');
    }
    public function editMyPassWord(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::find(Auth::id());
        $current_password = $request->current_password;
        $password = $request->password;


        if (!Hash::check($current_password, $user->password)) {
            return $this->handleError('Current password is incorrect', 401);
        }

        $user->password = Hash::make($password);
        $user->save();

        return $this->handleSuccess([], 'change password success');
    }
}
