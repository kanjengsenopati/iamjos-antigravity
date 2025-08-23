<?php

namespace App\Models;

use App\Traits\UuidTrait;
use App\Traits\RequestLocale;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Article extends Model
{
    use UuidTrait, SoftDeletes, RequestLocale;
    protected $fillable = [
        'external_id',
        'title',
        'title_en',
        'slug',
        'source',
        'image',
        'summary',
        'summary_en',
        'body',
        'body_en',
        'published_at',
        'is_active',
        'estimated_reading_time',
    ];

    protected function title(): Attribute
    {
        return Attribute::make(
            get: fn($value, array $attr) => $this->localizeAttr('title', $value, $attr)
        );
    }

    protected function summary(): Attribute
    {
        return Attribute::make(
            get: fn($value, array $attr) => $this->localizeAttr('summary', $value, $attr)
        );
    }

    protected function body(): Attribute
    {
        return Attribute::make(
            get: fn($value, array $attr) => $this->localizeAttr('body', $value, $attr)
        );
    }
}
