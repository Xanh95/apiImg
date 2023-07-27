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
use App\Models\Upload;

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
        // event(new Registered($user));

        $url_id = explode('-', $user->avatar);

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
        $url_id = explode('-', $user->avatar);

        if ($url_id) {
            foreach ($url_id as $id) {
                $avatar[] = Upload::find($id)->url;
            }
            $user->avatar = $avatar;
        }

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
        $url_id = $request->url_id;



        if (!$request->user()->hasRole('admin')) {
            if ($role == 1) {
                return $this->handleError('Unauthorized', 403);
            }
        }
        if ($url_id) {
            $user->avatar = implode('-', $url_id);
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

        $status = $request->input('status');
        $layout_status = ['inactive', 'active'];
        $sort = $request->input('sort');
        $sort_types = ['desc', 'asc'];
        $sort_option = ['name', 'created_at', 'updated_at'];
        $sort_by = $request->input('sort_by');
        $status = in_array($status, $layout_status) ? $status : 'active';
        $sort = in_array($sort, $sort_types) ? $sort : 'desc';
        $sort_by = in_array($sort_by, $sort_option) ? $sort_by : 'created_at';
        $search = $request->input('query');
        $limit = request()->input('limit') ?? config('app.paginate');
        $role = $request->input('role');
        $users = User::select('*');

        if ($status) {
            $users = $users->where('status', $status);
        }
        if ($search) {
            $users = $users->where('name', 'LIKE', '%' . $search . '%');
        }
        if ($role) {
            $users = $users->whereHas('roles', function ($q) use ($role) {
                $q->where('name', $role);
            });
        }
        $users = $users->with('roles');
        $users = $users->orderBy($sort_by, $sort)->paginate($limit);
        foreach ($users as $user) {
            if ($user->avatar) {
                $url_id = explode('-', $user->avatar);
                foreach ($url_id as $id) {
                    $avatar[] = Upload::find($id)->url;
                }
                $user->avatar = $avatar;
            }
        }

        return $this->handleSuccess($users, 'get data user success');
    }
    public function edit(Request $request, User $user)
    {
        if (!$request->user()->hasPermission('update') && (Auth::id() != $user->id)) {
            return $this->handleError('Unauthorized', 403);
        }

        $url_id = $user->avatar;

        if ($url_id) {
            $user->avatar = Upload::find($url_id)->url;
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
        ]);

        $role = $request->role;
        $url_id = $request->url_id;
        $password = $request->password;
        $name = $request->name;
        $email = $request->email;

        if (!$request->user()->hasRole('admin')) {
            if ($role == 1) {
                $this->handleError('Unauthorized', 403);
            }
        }
        if ($url_id) {
            $current_url_ids = explode('-', $user->avatar);
            foreach ($current_url_ids as $current_url_id) {
                $image = Upload::find($current_url_id);
                $path = str_replace(url('/') . '/storage', 'public', $image->url);
                Storage::delete($path);
                $image->delete();
            }
            $user->avatar = implode('-', $url_id);
            CheckUsed($url_id);
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
                $current_url_ids = explode('-', $user->avatar);
                foreach ($current_url_ids as $current_url_id) {
                    $image = Upload::find($current_url_id);
                    $path = str_replace(url('/') . '/storage', 'public', $image->url);
                    Storage::delete($path);
                    $image->delete();
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
