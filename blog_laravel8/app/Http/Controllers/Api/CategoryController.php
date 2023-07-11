<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CategoryRequest;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\ResponseApiController;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class CategoryController extends ResponseApiController
{

    public function index()
    {
        $category = Category::all();
        return $this->handleSuccess($category, 'get all success');
    }
    public function store(Request $request)
    {
        //
        $request->validate([
            'name' => 'required',
            'status' => 'required|numeric',
            'type' => 'required',
            'description' => 'required',
            'image' =>  'image|mimes:png,jpg,jpeg,svg|max:10240',
        ], [
            'name.required' => 'A name is required',
            'status.required' => 'A status is required',
            'status.numeric' => 'A status is numeric',
            'description.numeric' => 'A status is numeric',
            'type.required' => 'A type is required',
        ]);

        $dirUpload = 'public/upload/Category/' . date('Y/m/d');
        $image = $request->image;
        $title = Str::random(10) . '.';
        $slug =  Str::slug($request->name);
        $user = Auth::user();

        if (!Storage::exists($dirUpload)) {
            Storage::makeDirectory($dirUpload, 0755, true);
        }
        $category = new Category;
        if ($image) {
            $imageName = $title . $image->extension();
            $image->storeAs($dirUpload, $imageName);
            $imageUrl = asset(Storage::url($dirUpload . '/' . $imageName));
            $category->link = $imageUrl;
            $category->path = $dirUpload . '/' . $imageName;
        }
        $category->slug = $slug;
        $category->createByUser = $user->email;
        $category->fill($request->all());

        if ($category->save()) {
            return $this->handleSuccess($category, 'save success');
        }
        return $this->handleError('upload fail', 404);
    }
    public function edit(Category $category)
    {
        $data = $category;
        if ($data) {
            return $this->handleSuccess($data, 'get success');
        }
        return $this->handleError('get error', 404);
    }

    public function update(Request $request, Category $category)
    {
        $request->validate([
            'image' =>  'image|mimes:png,jpg,jpeg,svg|max:10240',
        ]);
        $image = $request->image;
        $dirUpload = 'public/upload/Category/' . date('Y/m/d');
        $title =  Str::random(10) . '.';
        $imageUrl = $category->link;
        $path = $category->path;
        $name = $request->name ? $request->name : $category->name;
        $status = $request->status ? $request->status : $category->status;
        $type = $request->type ? $request->type : $category->type;
        $description = $request->description ?? $category->description;
        $slug =  Str::slug($name);
        $user = Auth::user();
        if ($image) {
            if (!Storage::exists($dirUpload)) {
                Storage::makeDirectory($dirUpload, 0755, true);
            }
            $imageName = $title . $image->extension();
            $image->storeAs($dirUpload, $imageName);
            Storage::delete($category->path);
            $imageUrl = asset(Storage::url($dirUpload . '/' . $imageName));
            $path = $dirUpload . '/' . $imageName;
        }
        if ($category->update([
            'slug' => $slug,
            'status' => $status,
            'type' => $type,
            'name' => $name,
            'link' => $imageUrl,
            'path' => $path,
            'createByUser' => $user->email,
            'description' => $description
        ])) {
            return $this->handleSuccess($category, 'update success');
        }
        return $this->handleError('error update', 404);
    }
    public function destroy(Category $category)
    {
        //
        $path = $category->path;
        if ($path) {
            Storage::delete($path);
        }

        if ($category->delete()) {

            return $this->handleSuccess($category, 'delete success');
        }
        return $this->handleError('delete error', 404);
    }
}
