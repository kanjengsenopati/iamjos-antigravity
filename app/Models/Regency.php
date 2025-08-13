<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Regency extends Model
{
    use HasUuids, SoftDeletes;
    protected $fillable = [
        'external_id',
        'phri_province_id',
        'province_id',
        'name',
    ];


    public function province()
    {
        return $this->belongsTo(Province::class)->withTrashed();
    }
}
