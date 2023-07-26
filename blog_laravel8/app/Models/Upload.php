<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Upload extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'urls',
        'category_id',
        'post_id',
        'user_id',
        'article_id'
    ];
    function post()
    {
        return $this->belongsTo(Post::class);
    }
    function category()
    {
        return $this->belongsTo(Category::class);
    }
    function user()
    {
        return $this->belongsTo(User::class);
    }
    function article()
    {
        return $this->belongsTo(Article::class);
    }
}
