<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\User;
use Illuminate\Support\Str;

class CategoryTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        for ($i = 1; $i < 51; $i++) {
            $category = new Category;
            $name = "Category number $i";
            $description = "content of Category number $i";
            $post_ids = random_int(52, 55);

            $category->user_id = User::whereHas('roles', function ($query) {
                $query->where('name', 'admin')
                    ->orWhere('name', 'editor');
            })->pluck('id')->random();
            $category->name = $name;
            $category->slug = Str::slug($name);
            $category->status = 'active';
            $category->type = 'type';
            $category->description = $description;
            $category->save();
            $category->posts()->sync($post_ids);
        }
    }
}
