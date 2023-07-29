<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Models\ReversionArticle;
use App\Models\ReversionArticleDetail;
use App\Models\ReversionArticleMeta;
use Illuminate\Support\Facades\Auth;
use App\Models\Upload;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;


class ReversionArticleController extends ResponseApiController
{
    public function store(Request $request, Article $article)
    {
        if (!$request->user()->hasPermission('create')) {
            return  $this->handleError('Unauthorized', 403);
        }

        $request->validate([
            'title' => 'required',
            'description' => 'required',
            'content' => 'required',
            'seo_content' => 'required',
            'seo_description' => 'required',
            'seo_title' => 'required',
            'type' => 'required',
            'category_ids' => 'required',
        ]);

        $reversion = new ReversionArticle;
        $reversion_metas = $request->reversion_metas;
        $reversion_title = $request->title;
        $reversion_thumbnail = $article->thumbnail;
        $reversion_new_thumbnail = $request->url_id;
        $reversion_description = $request->description;
        $reversion_content = $request->content;
        $reversion_seo_content = $request->seo_content;
        $reversion_seo_title = $request->seo_title;
        $reversion_seo_description = $request->seo_description;
        $reversion_type = $request->type;
        $languages = config('app.languages');
        $reversion_category_ids = $request->category_ids;

        $reversion->title = $reversion_title;
        $reversion->thumbnail = $reversion_thumbnail;
        $reversion->new_thumbnail = implode('-', $reversion_new_thumbnail);
        if ($reversion_new_thumbnail) {
            CheckUsed($reversion_new_thumbnail);
        }
        $reversion->user_id = Auth::id();
        $reversion->article_id = $article->id;
        $reversion->description = $reversion_description;
        $reversion->content = $reversion_content;
        $reversion->seo_content = $reversion_seo_content;
        $reversion->seo_description = $reversion_seo_description;
        $reversion->seo_title = $reversion_seo_title;
        $reversion->slug = Str::slug($reversion_title);
        $reversion->status = 'pending';
        $reversion->type = $reversion_type;
        $reversion->category_ids = $reversion_category_ids;
        $reversion->save();

        foreach ($languages as $language) {
            $reversion_detail = new ReversionArticleDetail;
            $reversion_detail->title = translate($language, $reversion_detail->title);
            $reversion_detail->slug = str_replace(' ', '-', $reversion_detail->title);
            $reversion_detail->description = translate($language, $reversion->description);
            $reversion_detail->content = translate($language, $reversion->content);
            $reversion_detail->seo_content = translate($language, $reversion->seo_content);
            $reversion_detail->seo_description = translate($language, $reversion->seo_description);
            $reversion_detail->seo_title = translate($language, $reversion->seo_title);
            $reversion_detail->reversion_article_id = $reversion->id;
            $reversion_detail->language = $language;
            $reversion_detail->save();
        }
        if ($reversion_metas) {
            foreach ($reversion_metas as $key => $value) {
                $reversion_meta = new ReversionArticleMeta;
                $reversion_meta->reversion_article_id = $reversion->id;
                $reversion_meta->meta_key = $key;
                $reversion_meta->meta_value = $value;
                $reversion_meta->save();
            }
        }
        $reversion->metas = $reversion->ReversionArticleMeta()->get();
        $reversion->detail = $reversion->ReversionArticleDetail()->get();

        return $this->handleSuccess($reversion, "create success reversion $article->title");
    }
    public function index(Request $request)
    {
        if (!$request->user()->hasPermission('update')) {
            return  $this->handleError('Unauthorized', 403);
        }

        $status = $request->input('status');
        $layout_status = ['unpublished', 'published', 'draft', 'pending'];
        $languages = config('app.languages');
        $sort = $request->input('sort');
        $sort_types = ['desc', 'asc'];
        $sort_option = ['title', 'created_at', 'updated_at', 'article_id'];
        $sort_by = $request->input('sort_by');
        $status = in_array($status, $layout_status) ? $status : 'published';
        $sort = in_array($sort, $sort_types) ? $sort : 'desc';
        $sort_by = in_array($sort_by, $sort_option) ? $sort_by : 'created_at';
        $search = $request->input('query');
        $limit = request()->input('limit') ?? config('app.paginate');
        $language = $request->language;
        $language = in_array($language, $languages) ? $language : '';
        $article_id = $request->article_id;
        $query = ReversionArticle::select('*');

        if ($status) {
            $query = $query->where('status', $status);
        }
        if ($article_id) {
            $query = $query->where('article_id', $article_id);
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
        $reversions = $query->orderBy($sort_by, $sort)->paginate($limit);
        foreach ($reversions as $reversion) {
            $url_ids = $reversion->thumbnail;
            if ($url_ids) {
                $url_ids = explode('-', $url_ids);
                foreach ($url_ids as $url_id) {
                    $image[] = Upload::find($url_id)->url;
                }
                $reversion->image = $image;
            }
        }

        return $this->handleSuccess($reversions, 'article data');
    }
    public function edit(Request $request, ReversionArticle $reversion)
    {
        if (!$request->user()->hasPermission('update')) {
            return  $this->handleError('Unauthorized', 403);
        }

        $language = $request->language;

        if ($language) {
            $reversion->reversion_detail = $reversion->ReversionArticleDetail()->where('language', $language)->get();
        }

        $reversion->article_meta = $reversion->ReversionArticleMeta()->get();
        $url_ids = $reversion->thumbnail;
        if ($url_ids) {
            $url_ids = explode('-', $url_ids);
            foreach ($url_ids as $url_id) {
                $image[] = Upload::find($url_id)->url;
            }
            $reversion->image = $image;
        }

        return $this->handleSuccess($reversion, 'success');
    }
    public function update(Request $request, ReversionArticle $reversion)
    {
        if (!$request->user()->hasPermission('update')) {
            return  $this->handleError('Unauthorized', 403);
        }

        $request->validate([
            'title' => 'required',
            'type' => 'required',
            'description' => 'required',
            'content' => 'required',
            'seo_title' => 'required',
            'seo_description' => 'required',
            'seo_content' => 'required',
            'category_ids' => 'required',
        ], [
            'title.required' => 'A title is required',
            'description.required' => 'A status is required',
            'type.required' => 'A type is required',
        ]);

        $reversion_metas = $request->reversion_metas;
        $reversion_title = $request->title;
        $reversion_thumbnail = $reversion->thumbnail;
        $reversion_new_thumbnail = $request->url_id;
        $reversion_description = $request->description;
        $reversion_content = $request->content;
        $reversion_seo_content = $request->seo_content;
        $reversion_seo_title = $request->seo_title;
        $reversion_seo_description = $request->seo_description;
        $reversion_type = $request->type;
        $languages = config('app.languages');
        $reversion_category_ids = $request->category_ids;

        $reversion->title = $reversion_title;
        $reversion->thumbnail = $reversion_thumbnail;
        if ($reversion_new_thumbnail) {
            $current_url = $reversion->new_thumbnail;
            if ($current_url) {
                $current_url_ids = explode('-', $current_url);
                foreach ($current_url_ids as $current_url_id) {
                    $image = Upload::find($current_url_id);
                    $path = str_replace(url('/') . '/storage', 'public', $image->url);
                    Storage::delete($path);
                    $image->delete();
                }
            }
            $reversion->new_thumbnail = implode('-', $reversion_new_thumbnail);
            CheckUsed($reversion_new_thumbnail);
        }
        $reversion->user_id = Auth::id();
        $reversion->description = $reversion_description;
        $reversion->content = $reversion_content;
        $reversion->seo_content = $reversion_seo_content;
        $reversion->seo_description = $reversion_seo_description;
        $reversion->seo_title = $reversion_seo_title;
        $reversion->slug = Str::slug($reversion_title);
        $reversion->status = 'pending';
        $reversion->type = $reversion_type;
        $reversion->category_ids = $reversion_category_ids;
        $reversion->save();
        $reversion->ReversionArticleDetail()->delete();
        foreach ($languages as $language) {
            $reversion_detail = new ReversionArticleDetail;
            $reversion_detail->title = translate($language, $reversion_detail->title);
            $reversion_detail->slug = str_replace(' ', '-', $reversion_detail->title);
            $reversion_detail->description = translate($language, $reversion->description);
            $reversion_detail->content = translate($language, $reversion->content);
            $reversion_detail->seo_content = translate($language, $reversion->seo_content);
            $reversion_detail->seo_description = translate($language, $reversion->seo_description);
            $reversion_detail->seo_title = translate($language, $reversion->seo_title);
            $reversion_detail->reversion_article_id = $reversion->id;
            $reversion_detail->language = $language;
            $reversion_detail->save();
        }
        $reversion->ReversionArticleMeta()->delete();
        if ($reversion_metas) {
            foreach ($reversion_metas as $key => $value) {
                $reversion_meta = new ReversionArticleMeta;
                $reversion_meta->reversion_article_id = $reversion->id;
                $reversion_meta->meta_key = $key;
                $reversion_meta->meta_value = $value;
                $reversion_meta->save();
            }
        }
        $reversion->metas = $reversion->ReversionArticleMeta()->get();
        $reversion->detail = $reversion->ReversionArticleDetail()->get();

        return $this->handleSuccess($reversion, "update success reversion $reversion->title");
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
        $reversion_articles = ReversionArticle::withTrashed()->whereIn('id', $ids)->get();

        foreach ($reversion_articles as $reversion) {
            $reversion->status = 'unpublished';
            $reversion->save();
            if ($type === 'force_delete') {
                $current_url = $reversion->new_thumbnail;
                if ($current_url) {
                    $current_url_ids = explode('-', $current_url);
                    foreach ($current_url_ids as $current_url_id) {
                        $image = Upload::find($current_url_id);
                        $path = str_replace(url('/') . '/storage', 'public', $image->url);
                        Storage::delete($path);
                        $image->delete();
                    }
                }
                $reversion->forceDelete();
            } else {
                $reversion->delete();
            }
        }

        if ($type === 'force_delete') {
            return $this->handleSuccess([], 'reversion article force delete successfully!');
        } else {
            return $this->handleSuccess([], 'reversion article delete successfully!');
        }
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
        ReversionArticle::onlyTrashed()->whereIn('id', $ids)->restore();
        foreach ($ids as $id) {
            $reversion = ReversionArticle::find($id);
            $reversion->status = 'pending';
            $reversion->save();
        }

        return $this->handleSuccess([], 'reversion article restored successfully!');
    }
    public function updateDetails(Request $request, ReversionArticle $reversion)
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
        $reversion_detail = $reversion->ReversionArticleDetail()->where('language', $language)->first();
        $reversion_detail->title = $title;
        $reversion_detail->slug = $slug;
        $reversion_detail->description = $description;
        $reversion_detail->content = $content;
        $reversion_detail->save();
        $reversion->status = 'pending';
        $reversion->save();
        return $this->handleSuccess($reversion_detail, 'reversion detail updated successfully');
    }
}