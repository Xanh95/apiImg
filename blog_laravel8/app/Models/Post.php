<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasPermissionsTrait;


class Post extends Model
{
    use HasFactory, SoftDeletes, HasPermissionsTrait;
    protected $fillable = [
        'name',
        'status',
        'type',
        'description',
        'user_id',
        'slug',
    ];

    public function category()
    {
        return $this->belongsToMany(Category::class);
    }

    public function postMeta()
    {
        return $this->hasMany(PostMeta::class);
    }

    public function postDetail()
    {
        return $this->hasMany(PostDetail::class);
    }
}
