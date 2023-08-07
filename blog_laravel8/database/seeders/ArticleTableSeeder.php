<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\ArticleDetail;
use App\Models\ArticleMeta;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Str;

class ArticleTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        for ($i = 1; $i < 301; $i++) {
            $article = new Article();
            $category_ids = random_int(46, 95);
            $data_article_meta = [
                "key_article_meta_$i" => "value article meta $i",
            ];
            $title = "article Number $i";
            $description = "description of article number $i";
            $content = "content of article number $i";
            $seo_title = "seo_title article Number $i";
            $seo_description = "seo_description of article number $i";
            $seo_content = "seo_content of article number $i";
            $languages = config('app.languages');


            $article->title = $title;
            $article->content = $content;
            $article->seo_content = $seo_content;
            $article->seo_title = $seo_title;
            $article->user_id = User::whereHas('roles', function ($query) {
                $query->where('name', 'admin')
                    ->orWhere('name', 'editor');
            })->pluck('id')->random();
            $article->slug = Str::slug($title);
            $article->status = "published";
            $article->type = "type";
            $article->description = $description;
            $article->seo_description = $seo_description;
            $article->save();
            foreach ($languages as $language) {
                $article_detail = new ArticleDetail();
                $article_detail->title = translate($language, $title);
                $article_detail->seo_title = translate($language, $seo_title);
                $article_detail->slug = str_replace(' ', '-', $article_detail->title);
                $article_detail->description = translate($language, $description);
                $article_detail->seo_description = translate($language, $seo_description);
                $article_detail->content = translate($language, $content);
                $article_detail->seo_content = translate($language, $seo_content);
                $article_detail->article_id = $article->id;
                $article_detail->language = $language;
                $article_detail->save();
            }
            if ($data_article_meta) {
                foreach ($data_article_meta as $key => $value) {
                    if ($key != 'image') {
                        $article_meta = new ArticleMeta;
                        $article_meta->meta_key = $key;
                        $article_meta->meta_value = $value;
                        $article_meta->article_id = $article->id;
                        $article_meta->save();
                    }
                }
            }
            $article->Category()->sync($category_ids);
        }
    }
}
