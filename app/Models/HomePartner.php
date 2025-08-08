<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HomePartner extends Model
{
    use UuidTrait, SoftDeletes;

    protected $fillable = [
        'image',
        'order',
        'link',
        'is_active',
    ];
}
