<?php

namespace App\Models;

use App\Traits\UuidTrait;
use App\Traits\RequestLocale;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;

class DirectionCommitment extends Model
{
    use SoftDeletes, UuidTrait, RequestLocale;

    protected $fillable = [
        'image',
        'content',
        'content_en',
        'order',
    ];

    protected function content(): Attribute
    {
        return Attribute::make(
            get: fn($value, array $attr) => $this->localizeAttr('content', $value, $attr)
        );
    }
}
