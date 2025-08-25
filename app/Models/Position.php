<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Position extends Model
{
    use SoftDeletes, UuidTrait;

    protected $fillable = [
        'parent_id',
        'member_id',
        'name',
        'name_en',
        'order',
    ];

    public function member()
    {
        return $this->belongsTo(Member::class)->withTrashed();
    }

    public function children()
    {
        return $this->hasMany(Position::class, 'parent_id');
    }

    public function parent()
    {
        return $this->belongsTo(Position::class, 'parent_id');
    }

    // eager load rekursif
    public function childrenRecursive()
    {
        return $this->children()->with(['member', 'childrenRecursive']);
    }

    // root node saja
    public function scopeRoots($q)
    {
        return $q->whereNull('parent_id');
    }
}
