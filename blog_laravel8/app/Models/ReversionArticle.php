<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class ReversionArticle extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'title',
        'status',
        'type',
        'description',
        'article_id',
        'seo_description',
        'seo_title',
        'seo_content',
        'user_id',
        'slug',
        'category_ids',
    ];

    public function ReversionArticleMeta()
    {
        return $this->hasMany(ReversionArticleMeta::class);
    }
    public function article()
    {
        return $this->belongsTo(Article::class);
    }
    public function ReversionArticleDetail()
    {
        return $this->hasMany(ReversionArticleDetail::class);
    }
}
