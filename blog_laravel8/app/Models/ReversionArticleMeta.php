<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReversionArticleMeta extends Model
{
    use HasFactory;
    protected $fillable = [
        'meta_key',
        'meta_value',
    ];

    function ReversionArticle()
    {
        return $this->belongsTo(ReversionArticle::class);
    }
}
