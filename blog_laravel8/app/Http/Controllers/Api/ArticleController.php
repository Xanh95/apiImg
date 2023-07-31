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


class ArticleController extends ResponseApiController
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
        $sort_option = ['title', 'created_at', 'updated_at'];
        $sort_by = $request->input('sort_by');
        $status = in_array($status, $layout_status) ? $status : 'published';
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
            $query = $query->where('title', 'LIKE', '%' . $search . '%');
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
            $article->category = implode('-', $article->category()->pluck('category_id')->toArray());
            $url_ids = $article->thumbnail;
            if ($url_ids) {
                $url_ids = explode('-', $url_ids);
                foreach ($url_ids as $url_id) {
                    $image[] = Upload::find($url_id)->url;
                }
                $article->image = $image;
            }
        }

        return $this->handleSuccess($articles, 'article data');
    }
    public function store(Request $request)
    {
        if (!$request->user()->hasPermission('create')) {
            return $this->handleError('Unauthorized', 403);
        }

        $request->validate([
            'title' => 'required',
            'type' => 'required',
            'description' => 'required',
            'content' => 'required',
            'seo_title' => 'required',
            'seo_description' => 'required',
            'seo_content' => 'required',
            'category_id' => 'required|array',
        ], [
            'name.required' => 'A name is required',
            'status.required' => 'A status is required',
            'status.string' => 'A status is string',
            'description.required' => 'A status is required',
            'type.required' => 'A type is required',
        ]);

        $title = $request->title;
        $description = $request->description;
        $languages = config('app.languages');
        $slug =  Str::slug($title);
        $user = Auth::id();
        $article = new Article;
        $category_ids = $request->category_id;
        $data_article_meta = $request->article_metas;
        $url_ids = $request->url_id;
        $content = $request->content;
        $seo_title = $request->seo_title;
        $seo_content = $request->content;
        $seo_description = $request->description;

        $article->user_id = $user;
        $article->seo_title = $seo_title;
        $article->seo_content = $seo_content;
        $article->seo_description = $seo_description;
        $article->slug = $slug;
        $article->title = $title;
        $article->status = 'pending';
        $article->type = $request->type;
        $article->description = $description;
        $article->content = $content;
        if ($url_ids) {
            $article->thumbnail = implode('-', $url_ids);
            CheckUsed($url_ids);
        }
        $article->save();
        foreach ($languages as $language) {
            $article_detail = new ArticleDetail();
            $article_detail->title = translate($language, $title);
            $article_detail->slug = str_replace(' ', '-', $article_detail->title);
            $article_detail->description = translate($language, $description);
            $article_detail->content = translate($language, $content);
            $article_detail->seo_content = translate($language, $seo_content);
            $article_detail->seo_description = translate($language, $seo_description);
            $article_detail->seo_title = translate($language, $seo_title);
            $article_detail->article_id = $article->id;
            $article_detail->language = $language;
            $article_detail->save();
        }
        if ($data_article_meta) {
            $article_meta = new Article;
            foreach ($data_article_meta as $key => $value) {
                $article_meta = new ArticleMeta();
                $article_meta->meta_key = $key;
                $article_meta->meta_value = $value;
                $article_meta->article_id = $article->id;
                $article_meta->save();
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
        $url_ids = $article->thumbnail;
        if ($url_ids) {
            $url_ids = explode('-', $url_ids);
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
        if ($article->status == 'published' && !$request->user()->hasRole('admin')) {
            return $this->handleError('Unauthorized', 403);
        }

        $request->validate([
            'title' => 'required',
            'type' => 'required',
            'description' => 'required',
            'content' => 'required',
            'seo_title' => 'required',
            'seo_description' => 'required',
            'seo_content' => 'required',
            'category_id' => 'required|array',
        ], [
            'title.required' => 'A title is required',
            'description.required' => 'A status is required',
            'type.required' => 'A type is required',
        ]);

        $title = $request->title;
        $description = $request->description;
        $content = $request->content;
        $languages = config('app.languages');
        $slug =  Str::slug($title);
        $user = Auth::id();
        $category_ids = $request->category_id;
        $data_article_meta = $request->article_metas;
        $url_ids = $request->url_id;
        $seo_title = $request->seo_title;
        $seo_content = $request->seo_content;
        $seo_description = $request->seo_description;
        $current_url = $article->thumbnail;

        $article->seo_title = $seo_title;
        $article->seo_content = $seo_content;
        $article->seo_description = $seo_description;
        $article->user_id = $user;
        $article->slug = $slug;
        $article->title = $title;
        $article->type = $request->type;
        $article->description = $description;
        $article->content = $content;
        $article->status = 'pending';
        if ($request->has('url_id')) {
            if ($current_url) {
                $current_url_ids = explode('-', $current_url);
                foreach ($current_url_ids as $current_url_id) {
                    $image = Upload::find($current_url_id);
                    $path = str_replace(url('/') . '/storage', 'public', $image->url);
                    Storage::delete($path);
                    $image->delete();
                }
            }
            $article->thumbnail = implode('-', $url_ids);
            CheckUsed($url_ids);
        }
        $article->save();
        $article->articleDetail()->delete();
        foreach ($languages as $language) {
            $article_detail = new ArticleDetail;
            $article_detail->title = translate($language, $title);
            $article_detail->slug = str_replace(' ', '-', $article_detail->title);
            $article_detail->description = translate($language, $description);
            $article_detail->content = translate($language, $content);
            $article_detail->seo_content = translate($language, $seo_content);
            $article_detail->seo_description = translate($language, $seo_description);
            $article_detail->seo_title = translate($language, $seo_title);
            $article_detail->article_id = $article->id;
            $article_detail->language = $language;
            $article_detail->save();
        }
        if ($data_article_meta) {
            foreach ($data_article_meta as $key => $value) {
                $article_meta = new ArticleMeta();
                $article_meta->meta_key = $key;
                $article_meta->meta_value = $value;
                $article_meta->article_id = $article->id;
                $article_meta->save();
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
                $current_url = $article->thumbnail;
                if ($current_url) {
                    $current_url_ids = explode('-', $current_url);
                    foreach ($current_url_ids as $current_url_id) {
                        $image = Upload::find($current_url_id);
                        $path = str_replace(url('/') . '/storage', 'public', $image->url);
                        Storage::delete($path);
                        $image->delete();
                    }
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
            'title' => 'required|string|max: 255',
            'description' => 'string',
        ]);

        $language = $request->language;
        $title = $request->title;
        $description = $request->description;
        $content = $request->content;
        $slug =  Str::slug($title);

        if (!($language && in_array($language, config('app.languages')))) {
            return $this->handleError('Not Found Language', 404);
        }
        $article_detail = $article->articleDetail()->where('language', $language)->first();
        $article_detail->title = $title;
        $article_detail->slug = $slug;
        $article_detail->description = $description;
        $article_detail->content = $content;
        $article_detail->save();
        $article->status = 'pending';
        $article->save();
        return $this->handleSuccess($article_detail, 'article detail updated successfully');
    }
}
