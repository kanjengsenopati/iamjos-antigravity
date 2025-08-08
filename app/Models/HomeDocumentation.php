<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HomeDocumentation extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'media_type',
        'media_url',
        'thumbnail',
    ];
}
