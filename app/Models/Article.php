<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Article extends Model
{
    use UuidTrait, SoftDeletes;
    protected $fillable = [
        'external_id',
        'title',
        'slug',
        'source',
        'image',
        'summary',
        'body',
        'published_at',
        'is_active',
        'estimated_reading_time',
    ];
}
