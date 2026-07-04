<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReviewFormResponse extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'review_assignment_id',
        'review_form_element_id',
        'response_value',
    ];

    // =====================================================
    // RELATIONSHIPS
    // =====================================================

    /**
     * Get the review assignment that owns this response.
     */
    public function reviewAssignment(): BelongsTo
    {
        return $this->belongsTo(ReviewAssignment::class, 'review_assignment_id');
    }

    /**
     * Get the form element this response belongs to.
     */
    public function element(): BelongsTo
    {
        return $this->belongsTo(ReviewFormElement::class, 'review_form_element_id');
    }

    // =====================================================
    // HELPERS
    // =====================================================

    /**
     * Get formatted response value based on element type.
     */
    public function getFormattedValue(): mixed
    {
        $element = $this->element;

        if (!$element) {
            return $this->response_value;
        }

        switch ($element->element_type->value) {
            case 'checkbox':
                // Decode JSON array for checkboxes
                return json_decode($this->response_value, true) ?? [];

            case 'rating':
                // Return numeric rating
                return (int) $this->response_value;

            default:
                return $this->response_value;
        }
    }

    /**
     * Get display label for response (useful for select/radio/checkbox).
     */
    public function getDisplayLabel(): string
    {
        $element = $this->element;

        if (!$element || !$element->hasOptions()) {
            return $this->response_value;
        }

        $options = $element->options ?? [];
        $value = $this->response_value;

        // Handle checkbox (multiple values)
        if ($element->element_type->value === 'checkbox') {
            $values = json_decode($value, true) ?? [];
            $labels = [];

            foreach ($values as $val) {
                $option = collect($options)->firstWhere('value', $val);
                if ($option) {
                    $labels[] = $option['label'] ?? $val;
                }
            }

            return implode(', ', $labels);
        }

        // Handle radio/select (single value)
        $option = collect($options)->firstWhere('value', $value);
        return $option['label'] ?? $value;
    }
}
