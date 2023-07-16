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

class PostController extends ResponseApiController
{

    public function index(Request $request)
    {
        $status = $request->search('status');
        $layout_status = ['0', '1'];
        $sort = $request->search('sort');
        $sort_types = ['desc', 'asc'];
        $sort_option = ['title', 'created_at', 'updated_at'];
        $sort_by = $request->search('sort_by');
        $status = in_array($status, $layout_status) ? $status : '1';
        $sort = in_array($sort, $sort_types) ? $sort : 'desc';
        $sort_by = in_array($sort_by, $sort_option) ? $sort_by : 'created_at';
        $search = $request->search('query');
        $limit = request()->search('limit') ?? config('app.paginate');

        $query = Post::select('*');

        if ($status) {
            $query = $query->where('status', $status);
        }
        if ($search) {
            $query = $query->where('title', 'LIKE', '%' . $search . '%');
        }
        $posts = $query->orderBy($sort_by, $sort)->paginate($limit);

        return $this->handleSuccess($posts, 'Posts data');
    }

    public function store(Request $request)
    {

        $request->validate([
            'name' => 'required',
            'status' => 'required|numeric',
            'type' => 'required',
            'description' => 'required',
            'post_metas.image' =>  'image|mimes:png,jpg,jpeg,svg|max:10240',
            'category_id' => 'required|array',
        ], [
            'name.required' => 'A name is required',
            'status.required' => 'A status is required',
            'status.numeric' => 'A status is numeric',
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
    public function edit(Post $post)
    {
        $data = $post->load(['category', 'postMeta']);

        return $this->handleSuccess($data, 'success');
    }

    public function update(Request $request, Post $post)
    {
        $request->validate([
            'name' => 'required',
            'status' => 'required|numeric',
            'type' => 'required',
            'description' => 'required',
            'post_metas.image' =>  'image|mimes:png,jpg,jpeg,svg|max:10240',
            'category_id' => 'required|array',
        ], [
            'name.required' => 'A name is required',
            'status.required' => 'A status is required',
            'status.numeric' => 'A status is numeric',
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
                $oldImg = $post->postMeta->where('meta_key', 'image');
                $path = str_replace(url('/') . '/storage', 'public', $oldImg->meta_value);
                if ($path) {
                    Storage::delete($path);
                }
                $oldImg->forceDelete();
                $imageName = $title . '.' . $image->extension();
                $image->storeAs($dirUpload, $imageName);
                $imageUrl = asset(Storage::url($dirUpload . '/' . $imageName));
            }
            $post->postMeta->whereNot('meta_key', 'image')->forceDelete();
            $post_meta->meta_key = 'image';
            $post_meta->meta_value = $imageUrl;
            $post_meta->post_id = $post->id;
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
        $request->validate([
            'ids' => 'required',
        ]);

        $ids = $request->restore('ids');

        $ids = is_array($ids) ? $ids : [$ids];
        Post::onlyTrashed()->whereIn('id', $ids)->restore();
        foreach ($ids as $id) {
            $post = Post::find($id);
            $post->status = '1';
            $post->save();
        }

        return $this->handleSuccess([], 'Post restored successfully!');
    }

    public function destroy(Request $request)
    {
        $request->validate([
            'ids' => 'required',
            'type' => 'required|in:delete,force_delete',
        ]);

        $ids = $request->delete('ids');
        $type = $request->delete('type');
        $ids = is_array($ids) ? $ids : [$ids];
        $posts = Post::withTrashed()->whereIn('id', $ids)->get();

        foreach ($posts as $post) {
            $post->status = '1';
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
                $post->forceDelete();
            } else {
                $post->delete();
            }
        }

        if ($type === 'force_delete') {
            return $this->handleResponse([], 'Post force delete successfully!');
        } else {
            return $this->handleResponse([], 'Post delete successfully!');
        }
    }
}
