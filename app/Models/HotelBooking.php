<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HotelBooking extends Model
{
    use SoftDeletes, UuidTrait;

    protected $fillable = [
        'name',
        'price',
        'rating',
        'url',
        'image',
        'is_active'
    ];
}
