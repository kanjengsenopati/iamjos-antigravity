<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ContactUs extends Model
{
    use UuidTrait, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'message',
        'is_read',
    ];
}
