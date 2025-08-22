<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BookingIna extends Model
{
    use UuidTrait, SoftDeletes;

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
}
