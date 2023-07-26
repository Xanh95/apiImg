<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ArticleMeta extends Model
{
    use HasFactory;
    protected $fillable = [
        'meta_key',
        'meta_value',
    ];
    function article()
    {
        return $this->belongsTo(Article::class);
    }
}
