<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReversionArticleDetail extends Model
{
    use HasFactory;
    protected $fillable = [
        'title',
        'seo_title',
        'reversion_article_id',
        'description',
        'seo_description',
        'content',
        'seo_content',
        'slug',
        'language',
    ];
    function ReversionArticle()
    {
        return $this->belongsTo(ReversionArticle::class);
    }
}
