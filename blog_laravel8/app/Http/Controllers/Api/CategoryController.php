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
        $category = Category::all()->where('status', '1');

        return $this->handleSuccess($category, 'get all success');
    }
    public function store(Request $request)
    {

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
            'description.required' => 'A status is required',
            'type.required' => 'A type is required',
        ]);

        $image = $request->image;
        $title = Str::random(10);
        $slug =  Str::slug($request->name);
        $user = Auth::id();
        $category = new Category;

        if ($image) {
            $dirUpload = 'public/upload/category/' . date('Y/m/d');
            if (!Storage::exists($dirUpload)) {
                Storage::makeDirectory($dirUpload, 0755, true);
            }
            $imageName = $title . '.' . $image->extension();
            $image->storeAs($dirUpload, $imageName);
            $imageUrl = asset(Storage::url($dirUpload . '/' . $imageName));
            $category->link = $imageUrl;
        }
        $category->slug = $slug;
        $category->user_id = $user;
        $category->name = $request->name;
        $category->status = $request->status;
        $category->type = $request->type;
        $category->description = $request->description;
        $category->save();

        return $this->handleSuccess($category, 'save success');
    }
    public function edit(Category $category)
    {
        $data = $category;


        return $this->handleSuccess($data, 'get success');
    }

    public function update(Request $request, Category $category)
    {
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

        $image = $request->image;
        $path = str_replace(url('/') . '/storage', 'public', $category->link);
        $user = Auth::id();
        $slug = Str::slug($request->name);

        if ($image) {
            $dirUpload = 'public/upload/category/' . date('Y/m/d');
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
            $category->link = $imageUrl;
        }
        $category->slug = $slug;
        $category->user_id = $user;
        $category->name = $request->name;
        $category->status = $request->status;
        $category->type = $request->type;
        $category->description = $request->description;
        $category->save();

        return $this->handleSuccess($category, 'update success');
    }
    public function destroy(Category $category)
    {

        $path = str_replace(url('/') . '/storage', 'public', $category->link);

        if ($path) {
            Storage::delete($path);
        }

        $category->forceDelete();
        return $this->handleSuccess([], 'delete success');
    }
}
