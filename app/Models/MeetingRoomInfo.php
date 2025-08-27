<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MeetingRoomInfo extends Model
{
    use SoftDeletes, UuidTrait;

    protected $fillable = [
        'title',
        'title_en',
        'description',
        'description_en',
        'image',
    ];
}
