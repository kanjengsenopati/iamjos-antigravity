<?php

namespace App\Enums;

enum ReviewFormElementType: string
{
    case TEXT = 'text';
    case TEXTAREA = 'textarea';
    case CHECKBOX = 'checkbox';
    case RADIO = 'radio';
    case SELECT = 'select';
    case RATING = 'rating';

    /**
     * Get human-readable label for the element type.
     */
    public function label(): string
    {
        return match ($this) {
            self::TEXT => 'Text Input',
            self::TEXTAREA => 'Text Area',
            self::CHECKBOX => 'Checkboxes',
            self::RADIO => 'Radio Buttons',
            self::SELECT => 'Dropdown',
            self::RATING => 'Rating Scale',
        };
    }

    /**
     * Get description for the element type.
     */
    public function description(): string
    {
        return match ($this) {
            self::TEXT => 'Single line text input',
            self::TEXTAREA => 'Multi-line text area',
            self::CHECKBOX => 'Multiple selection checkboxes',
            self::RADIO => 'Single selection radio buttons',
            self::SELECT => 'Dropdown selection menu',
            self::RATING => 'Rating scale (1-5 stars)',
        };
    }

    /**
     * Check if element type supports options.
     */
    public function hasOptions(): bool
    {
        return in_array($this, [
            self::CHECKBOX,
            self::RADIO,
            self::SELECT,
        ]);
    }

    /**
     * Check if element type is rating.
     */
    public function isRating(): bool
    {
        return $this === self::RATING;
    }

    /**
     * Get all element types as array.
     */
    public static function toArray(): array
    {
        return array_map(fn($case) => [
            'value' => $case->value,
            'label' => $case->label(),
            'description' => $case->description(),
        ], self::cases());
    }
}
