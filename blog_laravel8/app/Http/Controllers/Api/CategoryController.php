<?php

namespace App\Http\Controllers\Api;

use App\Models\Category;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\ResponseApiController;
use App\Http\Requests\CategoryRequest;
use App\Http\Requests\DeleteRequest;
use App\Http\Requests\RestoreRequest;
use App\Models\Upload;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;


class CategoryController extends ResponseApiController
{

    public function index(Request $request)
    {
        if (!$request->user()->hasPermission('view')) {
            return $this->handleError('Unauthorized view categories', 403);
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
        foreach ($categories as $category) {
            if ($category->url) {
                $url_ids = explode('-', $category->url);
                foreach ($url_ids as $url_id) {
                    $image[] = Upload::find($url_id)->url;
                }
                $category->image = $image;
            }
        }

        return $this->handleSuccess($categories, 'get success Categories data');
    }

    public function store(CategoryRequest $request)
    {
        if (!$request->user()->hasPermission('create')) {
            return $this->handleError('Unauthorized create category', 403);
        }

        $url_ids = $request->url_ids;
        $user = Auth::id();
        $slug =  Str::slug($request->name);
        $category = new Category;
        $post_ids = $request->post_ids;

        $category->slug = $slug;
        $category->user_id = $user;
        $category->name = $request->name;
        $category->status = $request->status;
        $category->type = $request->type;
        $category->description = $request->description;
        if ($url_ids) {
            $category->url = implode('-', $url_ids);
            CheckUsed($url_ids); // kiểm tra những ảnh mà người dùng đó tạo ra đã sử dụng không thì xóa đi
        }
        $category->save();
        if ($post_ids) {
            $category->posts()->sync($post_ids);
        }
        if ($category->url) {
            $url_ids = explode('-', $category->url);
            foreach ($url_ids as $url_id) {
                $image[] = Upload::find($url_id)->url;
            }
            $category->image = $image;
        }


        return $this->handleSuccess($category, 'save success');
    }

    public function edit(Category $category, Request $request)
    {
        if (!$request->user()->hasPermission('view')) {
            return $this->handleError('Unauthorized view this category', 403);
        }

        $category->posts = $category->posts()->where('status', 'active')->pluck('name');
        if ($category->url) {
            $url_ids = explode('-', $category->url);
            foreach ($url_ids as $url_id) {
                $image[] = Upload::find($url_id)->url;
            }
            $category->image = $image;
        }

        return $this->handleSuccess($category, 'success');
    }

    public function update(CategoryRequest $request, Category $category)
    {
        if (!$request->user()->hasPermission('update')) {
            return $this->handleError('Unauthorized update category', 403);
        }

        $user = Auth::id();
        $name = $request->name;
        $slug = Str::slug($name);
        $post_ids = $request->post_ids;
        $url_ids = $request->url_id;
        $type = $request->type;
        $description = $request->description;
        $status = $request->status;
        $current_url = $category->url;

        $category->slug = $slug;
        $category->user_id = $user;
        $category->name = $name;
        if ($request->user()->hasRole('admin') || $user == $category->user_id) {
            $category->status = $status;
        }
        $category->type = $type;
        $category->description = $description;
        if ($post_ids) {
            $category->posts()->sync($post_ids);
        }
        if ($request->has('url_id')) {
            deleteFile($current_url); // xóa những file ảnh cũ đi
            CheckUsed($url_ids); // kiểm tra những ảnh mà người dùng đó tạo ra đã sử dụng không thì xóa đi
            $category->url = implode('-', $url_ids);
        }
        $category->save();

        return $this->handleSuccess($category, 'update success');
    }

    public function restore(RestoreRequest $request)
    {
        if (!$request->user()->hasPermission('delete')) {
            return $this->handleError('Unauthorized restore category', 403);
        }

        $ids = $request->input('ids');
        $ids = is_array($ids) ? $ids : [$ids];
        Category::onlyTrashed()->whereIn('id', $ids)->restore();

        foreach ($ids as $id) {
            $category = Category::find($id);
            $category->status = 'active';
            $category->save();
        }

        return $this->handleSuccess([], 'Category restored successfully!');
    }

    public function destroy(DeleteRequest $request)
    {
        if (!$request->user()->hasPermission('delete')) {
            return $this->handleError('Unauthorized delete categories', 403);
        }

        $ids = $request->input('ids');
        $type = $request->input('type');
        $ids = is_array($ids) ? $ids : [$ids];
        $categories = Category::withTrashed()->whereIn('id', $ids)->get();

        foreach ($categories as $category) {
            $category->status = 'inactive';
            $category->save();
            if ($type === 'force_delete') {
                deleteFile($category->url); // xóa những file ảnh cũ đi
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
