<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IssueGalley extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'journal_id',
        'issue_id',
        'label',
        'locale',
        'file_path',
        'file_type',
        'original_file_name',
        'url_path',
        'seq_id',
        'sort_order',
    ];

    // =====================================================
    // ROUTE MODEL BINDING
    // =====================================================

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'seq_id';
    }

    /**
     * Retrieve the model for a bound value.
     */
    public function resolveRouteBinding($value, $field = null)
    {
        if ($field === 'id' || \Illuminate\Support\Str::isUuid($value)) {
            return $this->where('id', $value)->firstOrFail();
        }

        return $this->where($field ?? $this->getRouteKeyName(), $value)->firstOrFail();
    }

    // =====================================================
    // MODEL EVENTS
    // =====================================================

    protected static function booted(): void
    {
        static::creating(function (IssueGalley $galley) {
            if (empty($galley->seq_id)) {
                $maxSeq = static::max('seq_id') ?? 0;
                $galley->seq_id = $maxSeq + 1;
            }
        });
    }

    // =====================================================
    // RELATIONSHIPS
    // =====================================================

    /**
     * Get the journal that owns this galley.
     */
    public function journal(): BelongsTo
    {
        return $this->belongsTo(Journal::class);
    }

    /**
     * Get the issue that owns this galley.
     */
    public function issue(): BelongsTo
    {
        return $this->belongsTo(Issue::class);
    }
}
