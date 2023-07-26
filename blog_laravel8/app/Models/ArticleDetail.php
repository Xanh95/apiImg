<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ArticleDetail extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'status',
        'type',
        'description',
        'user_id',
        'slug',
    ];
    function article()
    {
        return $this->belongsTo(Article::class);
    }
}
