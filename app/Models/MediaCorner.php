<?php

namespace App\Models;

use App\Traits\UuidTrait;
use App\Traits\RequestLocale;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;

class MediaCorner extends Model
{
    use UuidTrait, RequestLocale;
    protected $fillable = [
        'video_id',
        'title',
        'title_en',
        'description',
        'description_en',
        'channel',
        'published_at',
        'url',
        'thumbnails',
        'is_active'
    ];
    protected $casts = ['published_at' => 'datetime', 'thumbnails' => 'array'];

    protected function title(): Attribute
    {
        return Attribute::make(
            get: fn($value, array $attr) => $this->localizeAttr('title', $value, $attr)
        );
    }

    protected function description(): Attribute
    {
        return Attribute::make(
            get: fn($value, array $attr) => $this->localizeAttr('description', $value, $attr)
        );
    }
}
