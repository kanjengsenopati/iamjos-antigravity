<?php

namespace App\Models;

use App\Traits\UuidTrait;
use App\Traits\RequestLocale;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;

class BookingIna extends Model
{
    use UuidTrait, SoftDeletes, RequestLocale;

    protected $fillable = [
        'title',
        'title_en',
        'subtitle',
        'subtitle_en',
        'url',
        'button_text',
        'button_text_en',
        'image'
    ];

    protected function title(): Attribute
    {
        return Attribute::make(
            get: fn($value, array $attr) => $this->localizeAttr('title', $value, $attr)
        );
    }

    protected function subtitle(): Attribute
    {
        return Attribute::make(
            get: fn($value, array $attr) => $this->localizeAttr('subtitle', $value, $attr)
        );
    }

    protected function buttonText(): Attribute
    {
        return Attribute::make(
            get: fn($value, array $attr) => $this->localizeAttr('button_text', $value, $attr)
        );
    }
}
