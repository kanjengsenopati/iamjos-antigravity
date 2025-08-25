<?php

namespace App\Models;

use App\Traits\UuidTrait;
use App\Traits\RequestLocale;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;

class HonoraryCouncil extends Model
{
    use SoftDeletes, UuidTrait, RequestLocale;

    protected $fillable = [
        'name',
        'position',
        'position_en',
        'image',
        'order',
    ];

    protected function position(): Attribute
    {
        return Attribute::make(
            get: fn($value, array $attr) => $this->localizeAttr('position', $value, $attr)
        );
    }
}
