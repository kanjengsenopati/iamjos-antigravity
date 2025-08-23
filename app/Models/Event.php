<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Event extends Model
{
    use SoftDeletes, UuidTrait;

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
}
