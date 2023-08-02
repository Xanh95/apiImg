<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ArticleDetail extends Model
{
    use HasFactory;
    protected $fillable = [
        'title',
        'seo_title',
        'article_id',
        'description',
        'seo_description',
        'content',
        'seo_content',
        'slug',
        'language',
    ];

    function article()
    {
        return $this->belongsTo(Article::class);
    }
}
