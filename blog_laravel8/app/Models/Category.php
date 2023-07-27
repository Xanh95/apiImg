<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasPermissionsTrait;

class Category extends Model
{
    use HasFactory, SoftDeletes, HasPermissionsTrait;
    protected $fillable = [
        'name',
        'slug',
        'status',
        'type',
        'description',
        'link',

    ];
    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class);
    }
    public function articles(): BelongsToMany
    {
        return $this->belongsToMany(Article::class);
    }
}
