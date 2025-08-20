<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MeetingRoom extends Model
{
    use UuidTrait, SoftDeletes;

    protected $fillable = [
        'meeting_venue_id',
        'name',
        'photo',
    ];

    public function meeting_venue()
    {
        return $this->belongsTo(MeetingVenue::class)->withTrashed();
    }

    public function meeting_room_layouts()
    {
        return $this->hasMany(MeetingRoomLayout::class, 'meeting_room_id', 'id');
    }
}
