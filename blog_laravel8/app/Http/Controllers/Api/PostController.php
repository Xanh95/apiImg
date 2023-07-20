<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreatePostRequest;
use App\Models\Category;
use App\Models\Post;
use App\Models\PostMeta;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\UserMeta;


class PostController extends ResponseApiController
{

    public function index(Request $request)
    {
        if (!$request->user()->hasPermission('view')) {
            $this->handleError('Unauthorized', 403);
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

        $query = Post::select('*');

        if ($status) {
            $query = $query->where('status', $status);
        }
        if ($search) {
            $query = $query->where('name', 'LIKE', '%' . $search . '%');
        }
        $posts = $query->orderBy($sort_by, $sort)->paginate($limit);

        return $this->handleSuccess($posts, 'Posts data');
    }

    public function store(Request $request)
    {
        if (!$request->user()->hasPermission('create')) {
            $this->handleError('Unauthorized', 403);
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

        $title = Str::random(10);
        $slug =  Str::slug($request->name);
        $user = Auth::id();
        $post = new Post;
        $category_ids = $request->category_id;
        $data_post_meta = $request->post_metas;

        $post->user_id = $user;
        $post->slug = $slug;
        $post->name = $request->name;
        $post->status = $request->status;
        $post->type = $request->type;
        $post->description = $request->description;
        $post->save();
        if ($data_post_meta) {
            $post_meta = new PostMeta;
            if (isset($data_post_meta['image'])) {
                $image = $data_post_meta['image'];
                $dirUpload = 'public/upload/post/' . date('Y/m/d');
                if (!Storage::exists($dirUpload)) {
                    Storage::makeDirectory($dirUpload, 0755, true);
                }
                $imageName = $title . '.' . $image->extension();

                $image->storeAs($dirUpload, $imageName);
                $imageUrl = asset(Storage::url($dirUpload . '/' . $imageName));
                $post_meta->meta_key = 'image';
                $post_meta->meta_value = $imageUrl;
                $post_meta->post_id = $post->id;
                $post_meta->save();
            }

            foreach ($data_post_meta as $key => $value) {
                if ($key != 'image') {
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
            $this->handleError('Unauthorized', 403);
        }

        $post->categories = $post->category()->where('status', 'active')->pluck('name');
        $post->post_meta = $post->postMeta()->get();

        return $this->handleSuccess($post, 'success');
    }

    public function update(Request $request, Post $post)
    {
        if (!$request->user()->hasPermission('update')) {
            $this->handleError('Unauthorized', 403);
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

        $title = Str::random(10);
        $slug =  Str::slug($request->name);
        $user = Auth::id();
        $category_ids = $request->category_id;
        $data_post_meta = $request->post_metas;

        $post->user_id = $user;
        $post->slug = $slug;
        $post->name = $request->name;
        if ($request->user()->hasRole('admin') || $user == $post->user_id) {
            $post->status = $request->status;
        }
        $post->type = $request->type;
        $post->description = $request->description;
        $post->save();
        if ($data_post_meta) {
            $post_meta = new PostMeta;
            $oldImg = $post->postMeta->where('meta_key', 'image');
            if (!$oldImg->isEmpty()) {
                $path = str_replace(url('/') . '/storage', 'public', $oldImg->first()->meta_value);
                if ($path) {
                    Storage::delete($path);
                }
                $oldImg->first()->forceDelete();
            }
            $post->postMeta()->delete();
            if (isset($data_post_meta['image'])) {
                $image = $data_post_meta['image'];
                $dirUpload = 'public/upload/post/' . date('Y/m/d');
                if (!Storage::exists($dirUpload)) {
                    Storage::makeDirectory($dirUpload, 0755, true);
                }
                $imageName = $title . '.' . $image->extension();
                $image->storeAs($dirUpload, $imageName);
                $imageUrl = asset(Storage::url($dirUpload . '/' . $imageName));
                $post_meta->meta_key = 'image';
                $post_meta->meta_value = $imageUrl;
                $post_meta->post_id = $post->id;
            }
            $post_meta->save();

            foreach ($data_post_meta as $key => $value) {
                if ($key != 'image') {
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
            $this->handleError('Unauthorized', 403);
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
            $this->handleError('Unauthorized', 403);
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
                $post_metas = $post->postMeta()->get();
                foreach ($post_metas as $post_meta) {
                    $value = $post_meta->value;
                    if (filter_var($value, FILTER_VALIDATE_URL) !== false) {
                        $path = 'public' . Str::after($post_meta->value, 'storage');
                        Storage::delete($path);
                    }
                }
                UserMeta::where('meta_value', $ids)->delete();
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
}
