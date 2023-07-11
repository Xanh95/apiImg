<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'slug',
        'status',
        'type',
        'category_id',
    ];
    public function Category()
    {
        return $this->belongsToMany(Category::class);
    }
    public function postMeta()
    {
        return $this->hasOne(PostMeta::class);
    }
}
