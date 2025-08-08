<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HomeSector extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'name',
        'name_en',
        'description',
        'description_en',
        'image',
        'order',
        'link',
    ];
}
