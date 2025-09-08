<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Member extends Model
{
    const TYPE_BPP = 'bpp';
    const TYPE_ORGANIZATION = 'organization';
    use SoftDeletes, UuidTrait;

    protected $fillable = [
        'name',
        'image',
        'type'
    ];

    // Scope untuk organization members
    public function scopeOrganization($query)
    {
        return $query->where('type', 'organization');
    }

    // Scope untuk BPP members
    public function scopeBpp($query)
    {
        return $query->where('type', 'bpp');
    }

    public function position()
    {
        return $this->hasOne(Position::class, 'member_id', 'id');
    }

    public function bppOrganization()
    {
        return $this->hasOne(BppOrganization::class, 'member_id', 'id');
    }
}
