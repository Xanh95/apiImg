<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\ArticleDetailRequest;
use App\Models\Article;
use App\Models\ArticleDetail;
use Illuminate\Http\Request;
use App\Models\Upload;
use Illuminate\Support\Str;
use App\Models\ArticleMeta;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\ArticleRequest;
use App\Http\Requests\DeleteRequest;
use App\Http\Requests\RestoreRequest;

class ArticleController extends ResponseApiController
{
    //
    public $languages;

    public function __construct()
    {
        $this->languages = config('app.languages');
    }
    public function index(Request $request)
    {
        if (!$request->user()->hasPermission('view')) {
            return $this->handleError('Unauthorized view articles', 403);
        }

        $status = $request->input('status');
        $layout_status = ['unpublished', 'published', 'draft', 'pending'];
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
        $language = in_array($language, $this->languages) ? $language : '';
        $query = Article::select('*');
        if ($status) {
            $query = $query->where('status', $status);
        }
        if ($search) {
            $query = $query->where('title', 'LIKE', '%' . $search . '%');
        }
        if ($language) {
            $query = $query->with(['articleDetail' => function ($q) use ($language) {
                $q->where('language', $language);
            }]);
        }
        $articles = $query->orderBy($sort_by, $sort)->paginate($limit);
        foreach ($articles as $article) {
            $article->category_id = implode('-', $article->category()->pluck('category_id')->toArray());
            $article->category_name = $article->category()->pluck('name');
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
    public function store(ArticleRequest $request)
    {
        if (!$request->user()->hasPermission('create')) {
            return $this->handleError('Unauthorized create article', 403);
        }

        $title = $request->title;
        $description = $request->description;
        $slug =  Str::slug($title);
        $user = Auth::id();
        $article = new Article;
        $category_ids = $request->category_ids;
        $data_article_meta = $request->article_metas;
        $url_ids = $request->url_ids;
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
            CheckUsed($url_ids); // kiểm tra những ảnh  tạo ra sử dụng và xóa những ảnh không dùng đi
        }
        $article->save();
        foreach ($this->languages as $language) {
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
        $article->category_id = $article->category()->pluck('category_id');
        $article->category_name = $article->category()->pluck('name');
        $url_ids = $article->thumbnail;
        if ($url_ids) {
            $url_ids = explode('-', $url_ids);
            foreach ($url_ids as $url_id) {
                $image[] = Upload::find($url_id)->url;
            }
            $article->image = $image;
        }


        return $this->handleSuccess($article, 'save success');
    }
    public function edit(Request $request, Article $article)
    {
        if (!$request->user()->hasPermission('view')) {
            return $this->handleError('Unauthorized view article', 403);
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

        return $this->handleSuccess($article, 'get success data article');
    }

    public function update(ArticleRequest $request, Article $article)
    {
        if (!$request->user()->hasPermission('update')) {
            return $this->handleError('Unauthorized edit article', 403);
        }
        if ($article->status == 'published' && !$request->user()->hasRole('admin')) {
            return $this->handleError('Unauthorized edit article', 403);
        }

        $title = $request->title;
        $description = $request->description;
        $content = $request->content;
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
            deleteFile($current_url); // xóa ảnh cũ đi
            $article->thumbnail = implode('-', $url_ids);
            CheckUsed($url_ids); // kiểm tra những ảnh  tạo ra sử dụng và xóa những ảnh không dùng đi
        }
        $article->save();
        $article->articleDetail()->delete();
        foreach ($this->languages as $language) {
            $article_detail = new ArticleDetail;
            $article_detail->title = translate($language, $title);
            $article_detail->slug = str_replace(' ', '-', $title);
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

        return $this->handleSuccess($article, 'update article success');
    }
    public function restore(RestoreRequest $request)
    {
        if (!$request->user()->hasPermission('delete')) {
            return $this->handleError('Unauthorized restore article', 403);
        }

        $ids = $request->input('ids');

        $ids = is_array($ids) ? $ids : [$ids];
        Article::onlyTrashed()->whereIn('id', $ids)->restore();
        foreach ($ids as $id) {
            $article = Article::find($id);
            $article->status = 'pending';
            $article->save();
        }

        return $this->handleSuccess([], 'Article restored successfully!');
    }
    public function destroy(DeleteRequest $request)
    {
        if (!$request->user()->hasPermission('delete')) {
            return  $this->handleError('Unauthorized delete articles', 403);
        }

        $ids = $request->input('ids');
        $type = $request->input('type');
        $ids = is_array($ids) ? $ids : [$ids];
        $articles = Article::withTrashed()->whereIn('id', $ids)->get();

        foreach ($articles as $article) {
            $article->status = 'unpublished';
            $article->save();
            $reversion_articles = $article->ReversionArticle()->get();
            foreach ($reversion_articles as $reversion) {
                deleteFile($reversion->new_thumbnail); // xóa ảnh của của bản reversion article
            }
            if ($type === 'force_delete') {
                deleteFile($article->thumbnail); // xóa ảnh của  article đi
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

    public function updateDetails(ArticleDetailRequest $request, Article $article)
    {
        if (!$request->user()->hasPermission('update')) {
            return  $this->handleError('Unauthorized edit this language of article', 403);
        }

        $language = $request->language;
        $title = $request->title;
        $description = $request->description;
        $content = $request->content;
        $slug =  Str::slug($title);
        $seo_title = $request->seo_title;
        $seo_content = $request->content;
        $seo_description = $request->description;

        if (!($language && in_array($language, $this->languages))) {
            return $this->handleError('Not Found Language', 404);
        }
        $article_detail = $article->articleDetail()->where('language', $language)->first();
        $article_detail->title = $title;
        $article_detail->description = $description;
        $article_detail->content = $content;
        $article_detail->slug = $slug;
        $article_detail->seo_title = $seo_title;
        $article_detail->seo_description = $seo_description;
        $article_detail->seo_content = $seo_content;
        $article_detail->save();
        $article->status = 'pending';
        $article->save();

        return $this->handleSuccess($article_detail, 'article detail updated successfully');
    }
}
