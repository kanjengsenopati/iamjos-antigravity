<?php

namespace App\Models;

use App\Enums\ReviewFormElementType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReviewFormElement extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'review_form_id',
        'element_type',
        'question',
        'description',
        'options',
        'required',
        'sequence',
    ];

    protected function casts(): array
    {
        return [
            'element_type' => ReviewFormElementType::class,
            'options' => 'array',
            'required' => 'boolean',
            'sequence' => 'integer',
        ];
    }

    // =====================================================
    // RELATIONSHIPS
    // =====================================================

    /**
     * Get the review form that owns this element.
     */
    public function reviewForm(): BelongsTo
    {
        return $this->belongsTo(ReviewForm::class, 'review_form_id');
    }

    /**
     * Get all responses for this element.
     */
    public function responses(): HasMany
    {
        return $this->hasMany(ReviewFormResponse::class, 'review_form_element_id');
    }

    // =====================================================
    // SCOPES
    // =====================================================

    /**
     * Scope elements by required status.
     */
    public function scopeRequired($query, bool $required = true)
    {
        return $query->where('required', $required);
    }

    /**
     * Scope elements ordered by sequence.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sequence');
    }

    // =====================================================
    // HELPERS
    // =====================================================

    /**
     * Check if element has options (checkbox, radio, select).
     */
    public function hasOptions(): bool
    {
        return $this->element_type->hasOptions();
    }

    /**
     * Check if element is rating type.
     */
    public function isRating(): bool
    {
        return $this->element_type->isRating();
    }

    /**
     * Get formatted options for display.
     */
    public function getFormattedOptions(): array
    {
        if (!$this->hasOptions()) {
            return [];
        }

        return $this->options ?? [];
    }

    /**
     * Get rating configuration.
     */
    public function getRatingConfig(): array
    {
        if (!$this->isRating()) {
            return [];
        }

        return array_merge([
            'min' => 1,
            'max' => 5,
            'labels' => [],
        ], $this->options ?? []);
    }

    /**
     * Validate response value against element rules.
     */
    public function validateResponse($value): bool
    {
        // Required validation
        if ($this->required && empty($value)) {
            return false;
        }

        // Type-specific validation
        switch ($this->element_type) {
            case ReviewFormElementType::CHECKBOX:
                // For checkbox, value should be array
                return is_array($value);

            case ReviewFormElementType::RADIO:
            case ReviewFormElementType::SELECT:
                // Value should be one of the options
                if ($this->options && !empty($value)) {
                    $validOptions = array_map(function($opt) {
                        return is_array($opt) ? ($opt['value'] ?? $opt) : $opt;
                    }, $this->options);
                    return in_array($value, $validOptions);
                }
                return true;

            case ReviewFormElementType::RATING:
                // Value should be between min and max
                $config = $this->getRatingConfig();
                if (!empty($value)) {
                    $numValue = (int) $value;
                    return $numValue >= $config['min'] && $numValue <= $config['max'];
                }
                return !$this->required;

            default:
                return true;
        }
    }

    /**
     * Get response count for this element.
     */
    public function getResponseCount(): int
    {
        return $this->responses()->count();
    }
}
