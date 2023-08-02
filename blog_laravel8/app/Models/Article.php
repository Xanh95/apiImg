<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasPermissionsTrait;



class Article extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'title',
        'status',
        'type',
        'description',
        'seo_description',
        'seo_title',
        'seo_content',
        'user_id',
        'slug',
    ];

    public function category()
    {
        return $this->belongsToMany(Category::class);
    }

    public function articleMeta()
    {
        return $this->hasMany(ArticleMeta::class);
    }
    public function reversionArticle()
    {
        return $this->hasMany(ReversionArticle::class);
    }
    public function articleDetail()
    {
        return $this->hasMany(ArticleDetail::class);
    }
}
