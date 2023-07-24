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

    public function index(Request $request)
    {
        if (!$request->user()->hasPermission('view')) {
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

        $query = Category::select('*');

        if ($status) {
            $query = $query->where('status', $status);
        }
        if ($search) {
            $query = $query->where('name', 'LIKE', '%' . $search . '%');
        }
        $categories = $query->orderBy($sort_by, $sort)->paginate($limit);

        return $this->handleSuccess($categories, 'Categories data');
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
            'image' =>  'image|mimes:png,jpg,jpeg,svg|max:10240',
        ], [
            'name.required' => 'A name is required',
            'status.required' => 'A status is required',
            'status.string' => 'A status is string',
            'description.required' => 'A status is required',
            'type.required' => 'A type is required',
        ]);

        $image = $request->image;
        $title = Str::random(10);
        $slug =  Str::slug($request->name);
        $user = Auth::id();
        $category = new Category;
        $image = $request->image;
        $title = Str::random(10);
        $slug =  Str::slug($request->name);
        $user = Auth::id();
        $category = new Category;
        $post_ids = $request->post_ids;

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
        if ($post_ids) {
            $category->posts()->sync($post_ids);
        }

        return $this->handleSuccess($category, 'save success');
    }
    public function edit(Category $category, Request $request)
    {
        if (!$request->user()->hasPermission('view')) {
            return $this->handleError('Unauthorized', 403);
        }
        $category->posts = $category->posts()->where('status', 'active')->pluck('name');

        return $this->handleSuccess($category, 'success');
    }

    public function update(Request $request, Category $category)
    {
        if (!$request->user()->hasPermission('update')) {
            return $this->handleError('Unauthorized', 403);
        }

        $request->validate([
            'name' => 'required',
            'status' => 'required|string',
            'type' => 'required',
            'description' => 'required',
            'image' =>  'image|mimes:png,jpg,jpeg,svg|max:10240',
        ], [
            'name.required' => 'A name is required',
            'status.required' => 'A status is required',
            'status.string' => 'A status is string',
            'description.string' => 'A status is string',
            'type.required' => 'A type is required',
        ]);

        $image = $request->image;
        $path = str_replace(url('/') . '/storage', 'public', $category->link);
        $user = Auth::id();
        $slug = Str::slug($request->name);
        $post_ids = $request->post_ids;


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
        if ($request->user()->hasRole('admin') || $user == $category->user_id) {
            $category->status = $request->status;
        }
        $category->type = $request->type;
        $category->description = $request->description;
        $category->save();
        if ($post_ids) {
            $category->posts()->sync($post_ids);
        }

        return $this->handleSuccess($category, 'update success');
    }

    public function restore(Request $request)
    {
        if (!$request->user()->hasPermission('delete')) {
            return $this->handleError('Unauthorized', 403);
        }

        $request->validate([
            'ids' => 'required',
        ]);

        $ids = $request->category('ids');
        $ids = is_array($ids) ? $ids : [$ids];
        Category::onlyTrashed()->whereIn('id', $ids)->restore();

        foreach ($ids as $id) {
            $category = Category::find($id);
            $category->status = '1';
            $category->save();
        }

        return $this->handleSuccess([], 'Category restored successfully!');
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
        $categories = Category::withTrashed()->whereIn('id', $ids)->get();

        foreach ($categories as $category) {
            $category->status = 'inactive';
            $category->save();
            if ($type === 'force_delete') {
                if ($category->url) {
                    $path = 'public' . Str::after($category->link, 'storage');
                    Storage::delete($path);
                }
                $category->forceDelete();
            } else {
                $category->delete();
            }
        }
        if ($type === 'force_delete') {
            return $this->handleSuccess([], 'Category force delete successfully!');
        } else {
            return $this->handleSuccess([], 'Category delete successfully!');
        }
    }
}
