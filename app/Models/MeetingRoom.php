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
    ];

    public function meetingVenue()
    {
        return $this->belongsTo(MeetingVenue::class)->withTrashed();
    }
}
