<?php

namespace App\Models;

use App\Traits\RequestLocale;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class HomeSector extends Model
{
    use HasUuids, SoftDeletes, RequestLocale;

    protected $fillable = [
        'name',
        'name_en',
        'description',
        'description_en',
        'image',
        'order',
        // 'link',
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
