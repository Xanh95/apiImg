<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Article extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'name',
        'status',
        'type',
        'description',
        'user_id',
        'slug',
    ];
    public function categories()
    {

        return $this->belongsToMany(Category::class);
    }
    public function articleMeta()
    {
        return $this->hasMany(ArticleMeta::class);
    }
    public function articleDetail()
    {
        return $this->hasMany(ArticleDetail::class);
    }
    public function upload()
    {
        return $this->hasMany(Upload::class);
    }
}
