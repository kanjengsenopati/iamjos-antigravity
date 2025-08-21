<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DirectionCommitment extends Model
{
    use SoftDeletes, UuidTrait;

    protected $fillable = [
        'image',
        'content',
        'content_en',
        'order',
    ];
}
