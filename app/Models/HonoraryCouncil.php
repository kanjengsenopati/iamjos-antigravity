<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HonoraryCouncil extends Model
{
    use SoftDeletes, UuidTrait;

    protected $fillable = [
        'name',
        'position',
        'position_en',
        'image',
        'order',
    ];
}
