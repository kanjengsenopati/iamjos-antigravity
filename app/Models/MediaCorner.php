<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MediaCorner extends Model
{
    use UuidTrait;
    protected $fillable = [
        'video_id',
        'title',
        'description',
        'channel',
        'published_at',
        'url',
        'thumbnails',
        'is_active'
    ];
    protected $casts = ['published_at' => 'datetime', 'thumbnails' => 'array'];
}
