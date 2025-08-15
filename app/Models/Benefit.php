<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Benefit extends Model
{
    use SoftDeletes, UuidTrait;

    protected $fillable = [
        'title',
        'title_en',
        'subtitle',
        'subtitle_en',
        'url',
        'button_text',
        'button_text_en',
        'image',
        'image_2',
        'image_3',
        'order',
    ];
}
