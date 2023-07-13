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

    public function postInCategory(Category $category)
    {
        $data = $category->posts;
        return $this->handleSuccess($data, 'success');
    }

    public function store(Request $request)
    {

        $request->validate([
            'name' => 'required',
            'status' => 'required|numeric',
            'type' => 'required',
            'description' => 'required',
            'image' =>  'image|mimes:png,jpg,jpeg,svg|max:10240',
            'category_id' => 'required|array',
        ], [
            'name.required' => 'A name is required',
            'status.required' => 'A status is required',
            'status.numeric' => 'A status is numeric',
            'description.required' => 'A status is required',
            'type.required' => 'A type is required',
        ]);

        $image = $request->image;
        $title = Str::random(10);
        $slug =  Str::slug($request->name);
        $user = Auth::id();
        $post = new Post;
        $post_meta = new PostMeta;
        $category_ids = $request->category_id;

        if ($image) {
            $dirUpload = 'public/upload/post/' . date('Y/m/d');
            if (!Storage::exists($dirUpload)) {
                Storage::makeDirectory($dirUpload, 0755, true);
            }
            $imageName = $title . '.' . $image->extension();
            $image->storeAs($dirUpload, $imageName);
            $imageUrl = asset(Storage::url($dirUpload . '/' . $imageName));
            $post_meta->link = $imageUrl;
        }
        $post_meta->slug = $slug;
        $post->user_id = $user;
        $post->name = $request->name;
        $post->status = $request->status;
        $post->type = $request->type;
        $post->description = $request->description;
        $post->save();
        $post_meta->post_id = $post->id;
        $post_meta->save();
        $post->Category()->attach($category_ids);


        return $this->handleSuccess($post, 'save success');
    }
    public function edit(Post $post)
    {
        $data = $post->load(['category' => function ($query) {
            $query->where('status', '1');
        }, 'postMeta']);

        return $this->handleSuccess($data, 'success');
    }

    public function update(Request $request, Post $post)
    {
        $request->validate([
            'name' => 'required',
            'status' => 'required|numeric',
            'type' => 'required',
            'description' => 'required',
            'image' =>  'image|mimes:png,jpg,jpeg,svg|max:10240',
            'category_id' => 'required|array',
        ], [
            'name.required' => 'A name is required',
            'status.required' => 'A status is required',
            'status.numeric' => 'A status is numeric',
            'description.required' => 'A status is required',
            'type.required' => 'A type is required',
        ]);

        $image = $request->image;
        $path = str_replace(url('/') . '/storage', 'public', $post->postMeta->link);
        $user = Auth::id();
        $slug = Str::slug($request->name);
        $category_ids = $request->category_id;

        if ($image) {
            $dirUpload = 'public/upload/post/' . date('Y/m/d');
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
            $post->postMeta->link = $imageUrl;
        }

        $post->postMeta->slug = $slug;
        $post->user_id = $user;
        $post->name = $request->name;
        $post->status = $request->status;
        $post->type = $request->type;
        $post->description = $request->description;
        $post->postMeta->post_id = $post->id;
        $post->Category()->sync($category_ids);
        $post->save();
        $post->postMeta->save();
        $data =  $post->load('category', 'postMeta');

        return $this->handleSuccess($data, 'update success');
    }
    public function destroy(Post $post)
    {

        $path = str_replace(url('/') . '/storage', 'public', $post->postMeta->link);

        if ($path) {
            Storage::delete($path);
        }
        $post->forceDelete();

        return $this->handleSuccess([], 'delete success');
    }
}
