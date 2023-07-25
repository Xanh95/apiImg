<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Post;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\PostDetail;
use App\Models\PostMeta;

class PostTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        for ($i = 2539; $i < 3001; $i++) {
            $post = new Post;
            $category_ids = random_int(46, 95);
            $data_post_meta = [
                "key_post_meta_$i" => "value post meta $i",
            ];
            $name = "Post Number $i";
            $description = "content of post number $i";
            $languages = config('app.languages');


            $post->name = $name;
            $post->user_id = User::whereHas('roles', function ($query) {
                $query->where('name', 'admin')
                    ->orWhere('name', 'editor');
            })->pluck('id')->random();
            $post->slug = Str::slug($name);
            $post->status = "active";
            $post->type = "type";
            $post->description = $description;
            $post->save();
            foreach ($languages as $language) {
                $post_detail = new PostDetail;
                $post_detail->name = translate($language, $name);
                $post_detail->slug = str_replace(' ', '-', $post_detail->name);
                $post_detail->description = translate($language, $description);
                $post_detail->post_id = $post->id;
                $post_detail->language = $language;
                $post_detail->save();
            }
            if ($data_post_meta) {
                foreach ($data_post_meta as $key => $value) {
                    if ($key != 'image') {
                        $post_meta = new PostMeta();
                        $post_meta->meta_key = $key;
                        $post_meta->meta_value = $value;
                        $post_meta->post_id = $post->id;
                        $post_meta->save();
                    }
                }
            }
            $post->Category()->sync($category_ids);
        }
    }
}
