<?php

namespace App\Models;

use App\Traits\RequestLocale;
use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AboutUsHistory extends Model
{
    use SoftDeletes, UuidTrait, RequestLocale;

    protected $fillable = [
        'title',
        'title_en',
        'content',
        'content_en',
        'image',
    ];
}
