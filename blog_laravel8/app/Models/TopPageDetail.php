<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TopPageDetail extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'area',
        'about',
        'summary',

    ];

    public function topPage()
    {
        return $this->belongsTo(Toppage::class);
    }
}
