<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contact extends Model
{
    use SoftDeletes, UuidTrait;

    protected $fillable = [
        'name',
        'type',
        'value',
        'image',
    ];
}
