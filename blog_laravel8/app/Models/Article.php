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
}
