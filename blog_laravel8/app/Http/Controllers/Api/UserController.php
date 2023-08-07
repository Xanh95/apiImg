<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\DeleteRequest;
use App\Http\Requests\RestoreRequest;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Models\UserMeta;
use App\Models\Post;
use App\Models\Upload;
use App\Mail\ArticleStatus;
use App\Models\Article;
use App\Models\ReversionArticle;
use App\Models\ArticleMeta;

class UserController extends ResponseApiController
{

    public function userInfo(Request $request)
    {
        $user = $request->user('api');
        $url_id = $user->avatar;

        if ($url_id) {
            $avatar = Upload::find($url_id)->url;
            $user->avatar = $avatar;
        }
        $user->role = $user->roles()->pluck('name');

        return $this->handleSuccess($user, 'get success user info');
    }

    public function create(Request $request)
    {
        if (!$request->user()->hasPermission('create')) {
            return $this->handleError('Unauthorized create user', 403);
        }

        $request->validate([
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8',
            'name' => 'required|max:150',
            'url_id' =>  'array',
        ]);

        $user = new User;
        $role = $request->role ?? 2;
        $url_id = $request->url_id;
        $name = $request->name;
        $email = $request->email;
        $password = $request->password;

        if (!$request->user()->hasRole('admin')) {
            if ($role == 1) {
                return $this->handleError('Unauthorized choose role', 403);
            }
        }
        if ($url_id) {
            $user->avatar = $url_id;
            CheckUsed([$url_id]); // kiểm tra ảnh sử dụng và xóa những ảnh không dùng đi
        }
        $user->name = $name;
        $user->email = $email;
        $user->password = $password;
        $user->password = Hash::make($request->password);
        $user->email_verified_at = Carbon::now();
        $user->status = 'active';
        $user->save();
        $user->roles()->sync($role);
        $user->avatar = Upload::find($user->avatar)->first()->url;

        return $this->handleSuccess($user, 'create success user');
    }

    public function index(Request $request)
    {
        if (!$request->user()->hasPermission('update')) {
            return $this->handleError('Unauthorized view all user', 403);
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
                $user->avatar = Upload::find($user->avatar)->url;
            }
        }

        return $this->handleSuccess($users, 'get data user all success');
    }

    public function edit(Request $request, User $user)
    {
        if (!$request->user()->hasPermission('update') && (Auth::id() != $user->id)) {
            return $this->handleError('Unauthorized view this user', 403);
        }

        $url_id = $user->avatar;

        if ($url_id) {
            $user->avatar = Upload::find($url_id)->url;
        }
        $user->role = $user->roles()->pluck('name');
        $data = $user;

        return $this->handleSuccess($data, 'get data success');
    }

    public function update(Request $request, User $user)
    {
        if (!$request->user()->hasPermission('update')) {
            return $this->handleError('Unauthorized update this user', 403);
        }

        $request->validate([
            'email' => 'email|unique:users',
            'password' => 'min:8',
            'name' => 'required|max:150',
            'role' => 'required',
        ]);

        $roles = $request->role;
        $url_id = $request->url_id;
        $password = $request->password;
        $name = $request->name;
        $email = $request->email;
        $roles = is_array($roles) ? $roles : [$roles];

        foreach ($roles as $role) {
            if ($role == 1) {
                if (!$request->user()->hasRole('admin')) {
                    return  $this->handleError('Unauthorized choose role', 403);
                }
            }
        }
        if ($url_id) {
            deleteFile($user->avatar); // xóa ảnh avatar cũ đi
            $user->avatar = $url_id;
            CheckUsed([$url_id]); // check những ảnh tạo ra dùng ảnh nào còn lại ko dùng xóa đi
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
        if ($user->avatar) {
            $user->avatar = Upload::find($user->avatar)->url;
        }
        $user->role = $user->roles()->pluck('name');

        return $this->handleSuccess($user, "update $user->name success");
    }

    public function destroy(DeleteRequest $request)
    {
        if (!$request->user()->hasPermission('delete')) {
            return $this->handleError('Unauthorized delete users', 403);
        }

        $ids = $request->input('ids');
        $type = $request->input('type');
        $ids = is_array($ids) ? $ids : [$ids];
        $users = User::withTrashed()->whereIn('id', $ids)->get();

        foreach ($users as $user) {
            $user->status = 'inactive';
            $user->save();
            if ($type === 'force_delete') {
                deleteFile($user->avatar); // xóa avatar cũ đi
                $user->forceDelete();
            } else {
                $user->delete();
            }
        }
        if ($type === 'force_delete') {
            return $this->handleSuccess([], 'User force delete successfully!');
        } else {
            return $this->handleSuccess([], 'User delete successfully!');
        }
    }

    public function restore(RestoreRequest $request)
    {
        if (!$request->user()->hasPermission('delete')) {
            return $this->handleError('Unauthorized restore users', 403);
        }

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
            return $this->handleError('Unauthorized view favorite', 403);
        }

        $request->validate([
            'favorite' => 'required|array',
            'favorite.*' => 'numeric',
            'type' => 'required|in:add,sub'
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
            return $this->handleError('Unauthorized view show favorite', 403);
        }

        if ($request->user()->userMeta()->where('meta_key', 'favorite_post')->exists()) {
            $user_meta = $request->user()->userMeta()->where('meta_key', 'favorite_post')->first();
            $post_id = explode('-', $user_meta->meta_value);
            $data = Post::find($post_id);

            return $this->handleSuccess($data, 'get favorite success');
        }
        return $this->handleError('do not have favorite', 404);
    }

    public function editMyPassWord(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => 'required|min:8|confirmed',
        ]);

        $user = User::find(Auth::id());
        $current_password = $request->current_password;
        $password = $request->password;

        if (!Hash::check($current_password, $user->password)) {
            return $this->handleError('Current password is incorrect', 401);
        }

        $user->password = Hash::make($password);
        $user->save();

        return $this->handleSuccess([], 'changed password success');
    }

    public function approve(Request $request, Article $article)
    {
        if (!$request->user()->hasRole('admin')) {
            return  $this->handleError('Unauthorized approve article', 403);
        }

        $request->validate([
            'status' => 'required|in:published,reject',
            'reason' => 'string',
        ]);

        $user_id =  $article->user_id;
        $email = User::find($user_id)->email;

        if ($request->status === 'published') {
            $article->status = 'published';
            $article->save();
            Mail::to($email)->send(new ArticleStatus($article->title, 'published'));
        }
        if ($request->status === 'reject') {
            Mail::to($email)->send(new ArticleStatus($article->title, 'reject', $request->reason));
            $article->status = 'unpublished';
            $article->save();
        }

        return $this->handleSuccess($article, 'article status updated successfully');
    }
    public function approveReversion(Request $request, ReversionArticle $reversion)
    {
        if (!$request->user()->hasRole('admin')) {
            return  $this->handleError('Unauthorized approve reversion article', 403);
        }

        $request->validate([
            'status' => 'required|string|in:published,reject',
            'reason' => 'string',
        ]);

        $user_id =  $reversion->user_id;
        $email = User::find($user_id)->email;

        if ($request->status === 'published') {
            $article = Article::find($reversion->article_id);
            $languages = config('app.languages');
            $reversion_detail = $reversion->ReversionArticleDetail();
            $reversion_metas = $reversion->ReversionArticleMeta();
            $current_thumbnail = $article->thumbnail;
            $new_thumbnail = $reversion->new_thumbnail;
            $category_ids = explode('-', $reversion->category_ids);
            $article->title = $reversion->title;
            $article->description = $reversion->description;
            $article->content = $reversion->content;
            $article->seo_content = $reversion->seo_content;
            $article->seo_description = $reversion->seo_description;
            $article->seo_title = $reversion->seo_title;
            $article->user_id = $reversion->user_id;
            $article->title = $reversion->title;
            $article->slug = $reversion->slug;
            $article->status = 'published';
            $article->type = $reversion->type;
            if ($new_thumbnail) {
                $article->thumbnail = $new_thumbnail;
                $current_url = $current_thumbnail;
                if ($current_url) {
                    $current_url_ids = explode('-', $current_url);
                    foreach ($current_url_ids as $current_url_id) {
                        $image = Upload::find($current_url_id);
                        $path = str_replace(url('/') . '/storage', 'public', $image->url);
                        Storage::delete($path);
                        $image->delete();
                    }
                }
            }
            $article->save();
            if ($reversion_detail->exists()) {
                foreach ($languages as $language) {
                    $article_detail = $article->ArticleDetail()->where('language', $language)->first();
                    $reversion_detail = $reversion->ReversionArticleDetail()->where('language', $language)->first();
                    $article_detail->title = $reversion_detail->title;
                    $article_detail->content = $reversion_detail->content;
                    $article_detail->description = $reversion_detail->description;
                    $article_detail->seo_content = $reversion_detail->seo_content;
                    $article_detail->seo_description = $reversion_detail->seo_description;
                    $article_detail->seo_title = $reversion_detail->seo_title;
                    $article_detail->slug = $reversion_detail->slug;
                    $article_detail->save();
                }
            }
            if ($reversion_metas->exists()) {
                $article_metas = $article->ArticleMeta();
                $article_metas->delete();
                foreach ($reversion_metas->get() as $reversion_meta) {
                    $article_meta = new ArticleMeta;
                    $article_meta->meta_key = $reversion_meta->meta_key;
                    $article_meta->meta_value = $reversion_meta->meta_value;
                    $article_meta->article_id = $article->id;
                    $article_meta->save();
                }
            }
            $other_reversions = ReversionArticle::where('article_id', $article->id)->where('id', '!=', $reversion->id);
            foreach ($other_reversions->get() as $other_reversion) {
                $new_thumbnail = $other_reversion->new_thumbnail;
                if ($new_thumbnail) {
                    $article->thumbnail = $new_thumbnail;
                    $current_url = $current_thumbnail;
                    if ($current_url) {
                        $current_url_ids = explode('-', $current_url);
                        foreach ($current_url_ids as $current_url_id) {
                            $image = Upload::find($current_url_id);
                            $path = str_replace(url('/') . '/storage', 'public', $image->url);
                            Storage::delete($path);
                            $image->delete();
                        }
                    }
                }
            }
            $article->Category()->sync($category_ids);
            $other_reversions->delete();
            $reversion->forceDelete();
            Mail::to($email)->send(new ArticleStatus($article->title, 'published'));
        }
        if ($request->status === 'reject') {
            Mail::to($email)->send(new ArticleStatus($reversion->title, 'reject', $request->reason));
            $reversion->status = 'unpublished';
            $reversion->save();
        }

        return $this->handleSuccess($article, 'reversion status updated successfully');
    }
}
