<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Discussion extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'submission_id',
        'user_id',
        'subject',
        'stage_id',
        'is_open',
    ];

    protected function casts(): array
    {
        return [
            'is_open' => 'boolean',
            'stage_id' => 'integer',
        ];
    }

    /**
     * Get the submission this discussion belongs to.
     */
    public function submission(): BelongsTo
    {
        return $this->belongsTo(Submission::class);
    }

    /**
     * Get the user who created this discussion.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all messages in this discussion.
     */
    public function messages(): HasMany
    {
        return $this->hasMany(DiscussionMessage::class)->orderBy('created_at');
    }
}
