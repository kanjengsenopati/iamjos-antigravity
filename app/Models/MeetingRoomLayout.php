<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MeetingRoomLayout extends Model
{
    use UuidTrait;

    protected $fillable = [
        'meeting_room_id',
        'meeting_room_type_id',
        'capacity'
    ];

    public function meetingRoom()
    {
        return $this->belongsTo(MeetingRoom::class)->withTrashed();
    }

    public function type()
    {
        return $this->belongsTo(MeetingRoomType::class, 'meeting_room_type_id')->withTrashed();
    }
}
