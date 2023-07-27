<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\ArticleDetail;
use Illuminate\Http\Request;
use App\Models\Upload;
use Illuminate\Support\Str;
use App\Models\ArticleMeta;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use App\Mail\ArticleStatus;


class ArticleController extends Controller
{
    //
    public function index(Request $request)
    {
        if (!$request->user()->hasPermission('view')) {
            return $this->handleError('Unauthorized', 403);
        }

        $status = $request->input('status');
        $layout_status = ['unpublished', 'published', 'draft', 'pending'];
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
        $query = Article::select('*');

        if ($status) {
            $query = $query->where('status', $status);
        }
        if ($search) {
            $query = $query->where('name', 'LIKE', '%' . $search . '%');
        }
        if ($language) {
            $query = $query->whereHas('articleDetail', function ($q) use ($language) {
                $q->where('language', $language);
            });
            $query = $query->with(['articleDetail' => function ($q) use ($language) {
                $q->where('language', $language);
            }]);
        }
        $articles = $query->orderBy($sort_by, $sort)->paginate($limit);
        foreach ($articles as $article) {
            $url_ids = $article->articleMeta()->where('meta_key', 'url_id')->pluck('meta_value');
            if (!$url_ids->isEmpty()) {
                $url_ids = explode('-', $url_ids[0]);
                foreach ($url_ids as $url_id) {
                    $image[] = Upload::find($url_id)->url;
                }
                $article->image = $image;
            }
        }

        return $this->handleSuccess($articles, 'Posts data');
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
        $article = new Article;
        $category_ids = $request->category_id;
        $data_article_meta = $request->article_metas;
        $data_article_meta['url_id'] = $request->url_id;
        $content = $request->content;

        $article->user_id = $user;
        $article->slug = $slug;
        $article->name = $name;
        $article->status = 'pending';
        $article->type = $request->type;
        $article->description = $description;
        $article->content = $content;
        $article->save();
        foreach ($languages as $language) {
            $article_detail = new ArticleDetail();
            $article_detail->name = translate($language, $name);
            $article_detail->slug = str_replace(' ', '-', $article_detail->name);
            $article_detail->description = translate($language, $description);
            $article_detail->content = translate($language, $content);
            $article_detail->article_id = $article->id;
            $article_detail->language = $language;
            $article_detail->save();
        }
        if ($data_article_meta) {
            if ($data_article_meta['url_id']) {
                $article_meta = new Article();
                $article_meta->meta_key = 'url_id';
                $article_meta->meta_value = implode('-', $data_article_meta['url_id']);
                CheckUsed($data_article_meta['url_id']);
                $article_meta->article_id = $article->id;
                $article_meta->save();
            }
            $article_meta = new Article;
            foreach ($data_article_meta as $key => $value) {
                if ($key != 'url_id') {
                    $article_meta = new ArticleMeta();
                    $article_meta->meta_key = $key;
                    $article_meta->meta_value = $value;
                    $article_meta->article_id = $article->id;
                    $article_meta->save();
                }
            }
        }
        $article->Category()->sync($category_ids);


        return $this->handleSuccess($article, 'save success');
    }
    public function edit(Request $request, Article $article)
    {
        if (!$request->user()->hasPermission('view')) {
            return $this->handleError('Unauthorized', 403);
        }

        $language = $request->language;

        if ($language) {
            $article->article_detail = $article->articleDetail()->where('language', $language)->get();
        }
        $article->categories = $article->category()->where('status', 'active')->pluck('name');
        $article->article_meta = $article->articleMeta()->get();
        $url_ids = $article->articleMeta()->where('meta_key', 'url_id')->pluck('meta_value');
        if (!$url_ids->isEmpty()) {
            $url_ids = explode('-', $url_ids[0]);
            foreach ($url_ids as $url_id) {
                $image[] = Upload::find($url_id)->url;
            }
            $article->image = $image;
        }

        return $this->handleSuccess($article, 'success');
    }

    public function update(Request $request, Article $article)
    {
        if (!$request->user()->hasPermission('update')) {
            return $this->handleError('Unauthorized', 403);
        }

        $request->validate([
            'name' => 'required',
            'status' => 'required|string',
            'type' => 'required',
            'description' => 'required',
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
        $content = $request->content;
        $languages = config('app.languages');
        $slug =  Str::slug($name);
        $user = Auth::id();
        $category_ids = $request->category_id;
        $data_article_meta = $request->article_metas;
        $data_article_meta['url_id'] = $request->url_id;

        $article->user_id = $user;
        $article->slug = $slug;
        $article->name = $name;
        $article->type = $request->type;
        $article->description = $description;
        $article->content = $content;
        $article->save();
        $article->articleDetail()->delete();
        foreach ($languages as $language) {
            $article_detail = new ArticleDetail;
            $article_detail->name = translate($language, $name);
            $article_detail->slug = str_replace(' ', '-', $article_detail->name);
            $article_detail->description = translate($language, $description);
            $article_detail->content = translate($language, $content);
            $article_detail->article_id = $article->id;
            $article_detail->language = $language;
            $article_detail->save();
        }
        if ($data_article_meta) {
            $article->articleMeta()->where('meta_key', '!=', 'url_id')->delete();
            if (isset($data_article_meta['url_id'])) {
                $current_url_ids = $article->articleMeta()->where('meta_key', 'url_id')->pluck('meta_value');
                $current_url_ids = explode('-', $current_url_ids[0]);
                foreach ($current_url_ids as $current_url_id) {
                    $image = Upload::find($current_url_id);
                    $path = str_replace(url('/') . '/storage', 'public', $image->url);
                    Storage::delete($path);
                    $image->delete();
                }
                $article->articleMeta()->delete();
                CheckUsed($data_article_meta['url_id']);
                $url_ids = implode('-', $data_article_meta['url_id']);
                $article_meta = new ArticleMeta();
                $article_meta->meta_key = 'url_id';
                $article_meta->meta_value = $url_ids;
                $article_meta->article_id = $article->id;
                $article_meta->save();
            }

            foreach ($data_article_meta as $key => $value) {
                if ($key != 'url_id') {
                    $article_meta = new ArticleMeta();
                    $article_meta->meta_key = $key;
                    $article_meta->meta_value = $value;
                    $article_meta->article_id = $article->id;
                    $article_meta->save();
                }
            }
        }
        $article->Category()->sync($category_ids);


        return $this->handleSuccess($article, 'update success');
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
        Article::onlyTrashed()->whereIn('id', $ids)->restore();
        foreach ($ids as $id) {
            $article = Article::find($id);
            $article->status = 'active';
            $article->save();
        }

        return $this->handleSuccess([], 'Article restored successfully!');
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
        $articles = Article::withTrashed()->whereIn('id', $ids)->get();

        foreach ($articles as $article) {
            $article->status = 'unpublished';
            $article->save();
            if ($type === 'force_delete') {
                $current_url_ids = $article->articleMeta()->where('meta_key', 'url_id')->pluck('meta_value');
                $current_url_ids = explode('-', $current_url_ids[0]);
                foreach ($current_url_ids as $current_url_id) {
                    $image = Upload::find($current_url_id);
                    $path = str_replace(url('/') . '/storage', 'public', $image->url);
                    Storage::delete($path);
                    $image->delete();
                }

                $article->forceDelete();
            } else {
                $article->delete();
            }
        }

        if ($type === 'force_delete') {
            return $this->handleSuccess([], 'article force delete successfully!');
        } else {
            return $this->handleSuccess([], 'article delete successfully!');
        }
    }
    public function updateDetails(Request $request, Article $article)
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
        $slug =  Str::slug($name);

        if (!($language && in_array($language, config('app.languages')))) {
            return $this->handleError('Not Found Language', 404);
        }
        $article_detail = $article->articleDetail()->where('language', $language)->first();
        $article_detail->name = $name;
        $article_detail->slug = $slug;
        $article_detail->description = $request->description;
        $article_detail->save();
        return $this->handleSuccess($article_detail, 'Post detail updated successfully');
    }
    public function status(Request $request, Article $article)
    {
        if (!$request->user()->hasPermission('update')) {
            return  $this->handleError('Unauthorized', 403);
        }

        $request->validate([
            'status' => 'required|string|in:published,reject',
            'reason' => 'string',
        ]);

        $user_id =  $article->user_id;
        $email = User::find($user_id)->email;

        if ($request->status === 'published') {
            $article->status = 'published';
            $article->save();
            Mail::to($email)->send(new ArticleStatus($article, 'published'));
        }
        if ($request->status === 'reject') {
            Mail::to($email)->send(new ArticleStatus($article, 'reject', $request->reason));

            $article->status = 'pending';
            $article->save();
        }

        return $this->handleResponse($article, 'article status updated successfully');
    }
}
