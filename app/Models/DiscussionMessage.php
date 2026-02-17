<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DiscussionMessage extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'discussion_id',
        'user_id',
        'body',
    ];

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::created(function (DiscussionMessage $message) {
            $message->discussion()->touch();
        });
    }

    /**
     * Get the discussion this message belongs to.
     */
    public function discussion(): BelongsTo
    {
        return $this->belongsTo(Discussion::class);
    }

    /**
     * Get the user who sent this message.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function files()
    {
        return $this->hasMany(DiscussionFile::class);
    }
}
