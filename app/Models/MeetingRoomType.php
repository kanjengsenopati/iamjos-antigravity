<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MeetingRoomType extends Model
{
    use SoftDeletes, UuidTrait;

    protected $fillable = [
        'name',
        'image',
        'is_active',
    ];

    public function meetingRoomLayouts()
    {
        return $this->hasMany(MeetingRoomLayout::class);
    }
}
