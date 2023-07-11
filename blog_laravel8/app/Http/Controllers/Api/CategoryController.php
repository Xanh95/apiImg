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

        $dirUpload = 'public/upload/category/' . date('Y/m/d');
        $image = $request->image;
        $title = Str::random(10);
        $slug =  Str::slug($request->name);
        $user = Auth::user()->id;

        $category = new Category;
        if ($image) {
            if (!Storage::exists($dirUpload)) {
                Storage::makeDirectory($dirUpload, 0755, true);
            }
            $imageName = $title . '.' . $image->extension();
            $image->storeAs($dirUpload, $imageName);
            $imageUrl = asset(Storage::url($dirUpload . '/' . $imageName));
            $category->link = $imageUrl;
        }
        $category->slug = $slug;
        $category->createByUser = $user;
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
        $path = str_replace('http://localhost/storage', 'public', $category->link);
        $user = Auth::user()->id;
        $slug = STR::slug($request->name);
        $category->slug = $slug;
        $category->createByUser = $user;
        $category->fill($request->all());
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
        if ($category->save()) {
            return $this->handleSuccess($category, 'update success');
        }
        return $this->handleError('error update', 404);
    }
    public function destroy(Category $category)
    {
        //
        $path = str_replace('http://localhost/storage', 'public', $category->link);
        if ($path) {
            Storage::delete($path);
        }

        if ($category->delete()) {

            return $this->handleSuccess([], 'delete success');
        }
        return $this->handleError('delete error', 404);
    }
}
