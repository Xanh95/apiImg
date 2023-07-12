<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'status',
        'type',
        'description',
        'category_id',
    ];
    public function category()
    {
        return $this->belongsToMany(Category::class);
    }
    public function postMeta()
    {
        return $this->hasOne(PostMeta::class);
    }
}
