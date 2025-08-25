<?php

namespace App\Models;

use App\Traits\UuidTrait;
use App\Traits\RequestLocale;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;

class AboutUsHistory extends Model
{
    use SoftDeletes, UuidTrait, RequestLocale;

    protected $fillable = [
        'title',
        'title_en',
        'content',
        'content_en',
        'image',
    ];

    protected function title(): Attribute
    {
        return Attribute::make(
            get: fn($value, array $attr) => $this->localizeAttr('title', $value, $attr)
        );
    }

    protected function content(): Attribute
    {
        return Attribute::make(
            get: fn($value, array $attr) => $this->localizeAttr('content', $value, $attr)
        );
    }
}
