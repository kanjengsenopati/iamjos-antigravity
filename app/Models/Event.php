<?php

namespace App\Models;

use App\Traits\UuidTrait;
use App\Traits\RequestLocale;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Event extends Model
{
    const TYPE_UPCOMING = 'UPCOMING';
    const TYPE_PAST = 'PAST';
    use SoftDeletes, UuidTrait, RequestLocale;

    protected $fillable = [
        'province_id',
        'external_id',
        'name',
        'name_en',
        'description',
        'description_en',
        'location',
        'organized_by',
        'start_date',
        'end_date',
        'web',
        'is_approved',
        'is_active',
        'image',
    ];

    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn($value, array $attr) => $this->localizeAttr('name', $value, $attr)
        );
    }

    protected function description(): Attribute
    {
        return Attribute::make(
            get: fn($value, array $attr) => $this->localizeAttr('description', $value, $attr)
        );
    }
}
