<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Post;
use App\Models\PostDetail;
use App\Models\PostMeta;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\UserMeta;
use App\Models\Upload;

class PostController extends ResponseApiController
{

    public function index(Request $request)
    {
        if (!$request->user()->hasPermission('view')) {
            return $this->handleError('Unauthorized', 403);
        }

        $status = $request->input('status');
        $layout_status = ['inactive', 'active'];
        $languages = config('app.languages');
        $sort = $request->input('sort');
        $sort_types = ['desc', 'asc'];
        $sort_option = ['name', 'created_at', 'updated_at'];
        $sort_by = $request->input('sort_by');
        $status = in_array($status, $layout_status) ? $status : 'active';
        $sort = in_array($sort, $sort_types) ? $sort : 'desc';
        $sort_by = in_array($sort_by, $sort_option) ? $sort_by : 'created_at';
        $search = $request->input('query');
        $limit = request()->input('limit') ?? config('app.paginate');
        $language = $request->language;
        $language = in_array($language, $languages) ? $language : '';
        $query = Post::select('*');

        if ($status) {
            $query = $query->where('status', $status);
        }
        if ($search) {
            $query = $query->where('name', 'LIKE', '%' . $search . '%');
        }
        if ($language) {
            $query = $query->whereHas('postDetail', function ($q) use ($language) {
                $q->where('language', $language);
            });
            $query = $query->with(['postDetail' => function ($q) use ($language) {
                $q->where('language', $language);
            }]);
        }
        $posts = $query->orderBy($sort_by, $sort)->paginate($limit);
        foreach ($posts as $post) {
            $url_ids = $post->postMeta()->where('meta_key', 'url_id')->pluck('meta_value');
            if (!$url_ids->isEmpty()) {
                $url_ids = explode('-', $url_ids[0]);
                foreach ($url_ids as $url_id) {
                    $image[] = Upload::find($url_id)->url;
                }
                $post->image = $image;
            }
        }

        return $this->handleSuccess($posts, 'Posts data');
    }

    public function store(Request $request)
    {
        if (!$request->user()->hasPermission('create')) {
            return $this->handleError('Unauthorized', 403);
        }

        $request->validate([
            'name' => 'required',
            'status' => 'required|string',
            'type' => 'required',
            'description' => 'required',
            'post_metas.image' =>  'image|mimes:png,jpg,jpeg,svg|max:10240',
            'category_id' => 'required|array',
        ], [
            'name.required' => 'A name is required',
            'status.required' => 'A status is required',
            'status.string' => 'A status is string',
            'description.required' => 'A status is required',
            'type.required' => 'A type is required',
        ]);

        $name = $request->name;
        $description = $request->description;
        $languages = config('app.languages');
        $slug =  Str::slug($name);
        $user = Auth::id();
        $post = new Post;
        $category_ids = $request->category_id;
        $data_post_meta = $request->post_metas;
        $data_post_meta['url_id'] = $request->url_id;

        $post->user_id = $user;
        $post->slug = $slug;
        $post->name = $name;
        $post->status = $request->status;
        $post->type = $request->type;
        $post->description = $description;
        $post->save();
        foreach ($languages as $language) {
            $post_detail = new PostDetail;
            $post_detail->name = translate($language, $name);
            $post_detail->slug = str_replace(' ', '-', $post_detail->name);
            $post_detail->description = translate($language, $description);
            $post_detail->post_id = $post->id;
            $post_detail->language = $language;
            $post_detail->save();
        }
        if ($data_post_meta) {
            if ($data_post_meta['url_id']) {
                $post_meta = new PostMeta();
                $post_meta->meta_key = 'url_id';
                $post_meta->meta_value = implode('-', $data_post_meta['url_id']);
                CheckUsed($data_post_meta['url_id']);
                $post_meta->post_id = $post->id;
                $post_meta->save();
            }
            $post_meta = new PostMeta;
            foreach ($data_post_meta as $key => $value) {
                if ($key != 'url_id') {
                    $post_meta = new PostMeta();
                    $post_meta->meta_key = $key;
                    $post_meta->meta_value = $value;
                    $post_meta->post_id = $post->id;
                    $post_meta->save();
                }
            }
        }
        $post->Category()->sync($category_ids);


        return $this->handleSuccess($post, 'save success');
    }
    public function edit(Request $request, Post $post)
    {
        if (!$request->user()->hasPermission('view')) {
            return $this->handleError('Unauthorized', 403);
        }

        $language = $request->language;

        if ($language) {
            $post->post_detail = $post->postDetail()->where('language', $language)->get();
        }
        $post->categories = $post->category()->where('status', 'active')->pluck('name');
        $post->post_meta = $post->postMeta()->get();
        $url_ids = $post->postMeta()->where('meta_key', 'url_id')->pluck('meta_value');
        if (!$url_ids->isEmpty()) {
            $url_ids = explode('-', $url_ids[0]);
            foreach ($url_ids as $url_id) {
                $image[] = Upload::find($url_id)->url;
            }
            $post->image = $image;
        }

        return $this->handleSuccess($post, 'success');
    }

    public function update(Request $request, Post $post)
    {
        if (!$request->user()->hasPermission('update')) {
            return $this->handleError('Unauthorized', 403);
        }

        $request->validate([
            'name' => 'required',
            'status' => 'required|string',
            'type' => 'required',
            'description' => 'required',
            'post_metas.image' =>  'image|mimes:png,jpg,jpeg,svg|max:10240',
            'category_id' => 'required|array',
        ], [
            'name.required' => 'A name is required',
            'status.required' => 'A status is required',
            'status.string' => 'A status is string',
            'description.required' => 'A status is required',
            'type.required' => 'A type is required',
        ]);

        $name = $request->name;
        $description = $request->description;
        $languages = config('app.languages');
        $slug =  Str::slug($name);
        $user = Auth::id();
        $category_ids = $request->category_id;
        $data_post_meta = $request->post_metas;
        $data_post_meta['url_id'] = $request->url_id;

        $post->user_id = $user;
        $post->slug = $slug;
        $post->name = $name;
        if ($request->user()->hasRole('admin') || $user == $post->user_id) {
            $post->status = $request->status;
        }
        $post->type = $request->type;
        $post->description = $description;
        $post->save();
        $post->postDetail()->delete();
        foreach ($languages as $language) {
            $post_detail = new PostDetail;
            $post_detail->name = translate($language, $name);
            $post_detail->slug = str_replace(' ', '-', $post_detail->name);
            $post_detail->description = translate($language, $description);
            $post_detail->post_id = $post->id;
            $post_detail->language = $language;
            $post_detail->save();
        }
        if ($data_post_meta) {

            $post->postMeta()->where('meta_key', '!=', 'url_id')->delete();
            if (isset($data_post_meta['url_id'])) {
                $current_url_ids = $post->postMeta()->where('meta_key', 'url_id')->pluck('meta_value');
                $current_url_ids = explode('-', $current_url_ids[0]);
                foreach ($current_url_ids as $current_url_id) {
                    $image = Upload::find($current_url_id);
                    $path = str_replace(url('/') . '/storage', 'public', $image->url);
                    Storage::delete($path);
                    $image->delete();
                }
                $post->postMeta()->delete();
                CheckUsed($data_post_meta['url_id']);
                $url_ids = implode('-', $data_post_meta['url_id']);
                $post_meta = new PostMeta();
                $post_meta->meta_key = 'url_id';
                $post_meta->meta_value = $url_ids;
                $post_meta->post_id = $post->id;
                $post_meta->save();
            }

            foreach ($data_post_meta as $key => $value) {
                if ($key != 'url_id') {
                    $post_meta = new PostMeta();
                    $post_meta->meta_key = $key;
                    $post_meta->meta_value = $value;
                    $post_meta->post_id = $post->id;
                    $post_meta->save();
                }
            }
        }
        $post->Category()->sync($category_ids);


        return $this->handleSuccess($post, 'update success');
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
        Post::onlyTrashed()->whereIn('id', $ids)->restore();
        foreach ($ids as $id) {
            $post = Post::find($id);
            $post->status = 'active';
            $post->save();
        }

        return $this->handleSuccess([], 'Post restored successfully!');
    }

    public function destroy(Request $request)
    {
        if (!$request->user()->hasPermission('delete')) {
            return  $this->handleError('Unauthorized', 403);
        }

        $request->validate([
            'ids' => 'required',
            'type' => 'required|in:delete,force_delete',
        ]);

        $ids = $request->input('ids');
        $type = $request->input('type');
        $ids = is_array($ids) ? $ids : [$ids];
        $posts = Post::withTrashed()->whereIn('id', $ids)->get();

        foreach ($posts as $post) {
            $post->status = 'inactive';
            $post->save();
            if ($type === 'force_delete') {
                $current_url_ids = $post->postMeta()->where('meta_key', 'url_id')->pluck('meta_value');
                $current_url_ids = explode('-', $current_url_ids[0]);
                foreach ($current_url_ids as $current_url_id) {
                    $image = Upload::find($current_url_id);
                    $path = str_replace(url('/') . '/storage', 'public', $image->url);
                    Storage::delete($path);
                    $image->delete();
                }
                foreach ($ids as $id) {
                    $user_metas = UserMeta::where('meta_key', 'favorite_post')
                        ->where('meta_value', 'LIKE', "%$id%")
                        ->get();
                    foreach ($user_metas as $user_meta) {
                        $post_ids = explode('-', $user_meta->meta_value);
                        $post_ids = array_diff($post_ids, [$id]);
                        $user_meta->meta_value = implode('-', $post_ids);
                        $user_meta->save();
                    }
                }
                $post->forceDelete();
            } else {
                $post->delete();
            }
        }

        if ($type === 'force_delete') {
            return $this->handleSuccess([], 'Post force delete successfully!');
        } else {
            return $this->handleSuccess([], 'Post delete successfully!');
        }
    }
    public function updateDetails(Request $request, Post $post)
    {
        if (!$request->user()->hasPermission('update')) {
            return  $this->handleError('Unauthorized', 403);
        }

        $request->validate([
            'name' => 'required|string|max: 255',
            'description' => 'string',
        ]);

        $language = $request->language;
        $name = $request->name;
        $description = $request->description;
        $slug =  Str::slug($name);

        if (!($language && in_array($language, config('app.languages')))) {
            return $this->handleError('Not Found Language', 404);
        }
        $post_detail = $post->postDetail()->where('language', $language)->first();
        $post_detail->name = $name;
        $post_detail->slug = $slug;
        $post_detail->description = $description;
        $post_detail->save();
        $post->status = 'pending';
        $post->save();
        return $this->handleSuccess($post_detail, 'Post detail updated successfully');
    }
}
