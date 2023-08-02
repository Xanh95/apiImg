<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Toppage extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'area',
        'about',
        'summary',
        'cover_photo',
        'avatar',
        'website',
        'facebook',
        'instagram',
        'status',
        'video',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function TopPageDetail()
    {
        return $this->hasOne(TopPageDetail::class);
    }
}
