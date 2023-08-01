<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Post;
use App\Models\Upload;

class DashboardController extends ResponseApiController
{
    //
    public function dashBoard(Request $request)
    {

        $count_category = Category::count();
        $count_post = Post::count();
        $count_article = Article::count();
        $categories = Category::latest()->where('status', 'active')->limit(10)->get();
        foreach ($categories as $category) {
            if ($category->url) {
                $url_ids = explode('-', $category->url);
                foreach ($url_ids as $url_id) {
                    $image[] = Upload::find($url_id)->url;
                }
                $category->image = $image;
            }
        }
        $posts = Post::latest()->where('status', 'active')->limit(10)->get();
        foreach ($posts as $post) {
            $url_ids = $post->postMeta()->where('meta_key', 'url_id')->pluck('meta_value');
            if (!$url_ids->isEmpty()) {
                $url_ids = explode('-', $url_ids[0]);
                foreach ($url_ids as $url_id) {
                    $image[] = Upload::find($url_id)->url;
                }
                $post->image = $image;
            }
        }
        $articles = Article::latest()->where('status', 'published')->limit(10)->get();
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

        $data = [
            'count_category' => $count_category,
            'categories' => $categories,
            'count_post' => $count_post,
            'posts' => $posts,
            'count_article' => $count_article,
            'article' => $article,
        ];
        return $this->handleSuccess($data, 'success');
    }
}
