<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MeetingVenue extends Model
{
    use UuidTrait, SoftDeletes;

    protected $fillable = [
        'external_id',
        'phri_province_id',
        'phri_regency_id',
        'province_id',
        'regency_id',
        'province_name',
        'city_name',
        'hotel',
        'address',
        'email',
        'phone',
        'max_capacity',
        'photo',
    ];

    public function meeting_rooms()
    {
        return $this->hasMany(MeetingRoom::class, 'meeting_venue_id', 'id');
    }
}
