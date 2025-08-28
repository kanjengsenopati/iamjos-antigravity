<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Model;

class MeetingVenueGallery extends Model
{
    use UuidTrait;

    protected $fillable = [
        'meeting_venue_id',
        'image',
    ];

    public function meeting_venue()
    {
        return $this->belongsTo(MeetingVenue::class, 'meeting_venue_id', 'id');
    }
}
