<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReviewForm extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'journal_id',
        'title',
        'description',
        'elements',
        'is_active',
        'response_count',
    ];

    protected function casts(): array
    {
        return [
            'elements' => 'array',
            'is_active' => 'boolean',
            'response_count' => 'integer',
        ];
    }

    // =====================================================
    // RELATIONSHIPS
    // =====================================================

    public function journal(): BelongsTo
    {
        return $this->belongsTo(Journal::class, 'journal_id');
    }

    /**
     * Get all elements for this form.
     */
    public function elements(): HasMany
    {
        return $this->hasMany(ReviewFormElement::class, 'review_form_id')->ordered();
    }

    /**
     * Get all review assignments using this form.
     */
    public function reviewAssignments(): HasMany
    {
        return $this->hasMany(ReviewAssignment::class, 'review_form_id');
    }

    // =====================================================
    // SCOPES
    // =====================================================

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // =====================================================
    // HELPERS
    // =====================================================

    public function incrementResponseCount(): void
    {
        $this->increment('response_count');
    }

    /**
     * Get element count for this form.
     */
    public function getElementCount(): int
    {
        return $this->elements()->count();
    }

    /**
     * Check if form has elements.
     */
    public function hasElements(): bool
    {
        return $this->getElementCount() > 0;
    }

    /**
     * Check if form is ready to use (has elements).
     */
    public function isReady(): bool
    {
        return $this->is_active && $this->hasElements();
    }

    /**
     * Check if form can be deleted.
     */
    public function canBeDeleted(): bool
    {
        return $this->response_count === 0;
    }

    /**
     * Check if form can be edited.
     */
    public function canBeEdited(): bool
    {
        // Form can be edited even if it has responses
        // But elements with responses cannot be deleted
        return true;
    }

    /**
     * Duplicate this form with all its elements.
     */
    public function duplicate(string $newTitle = null): self
    {
        $newForm = $this->replicate();
        $newForm->title = $newTitle ?? $this->title . ' (Copy)';
        $newForm->response_count = 0;
        $newForm->save();

        // Duplicate all elements
        foreach ($this->elements as $element) {
            $newElement = $element->replicate();
            $newElement->review_form_id = $newForm->id;
            $newElement->save();
        }

        return $newForm;
    }
}
