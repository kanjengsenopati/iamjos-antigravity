<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;
use App\Casts\Localized;
use App\Traits\RequestLocale;

class HomeSlider extends Model
{
    use UuidTrait, SoftDeletes, RequestLocale;

    protected $table = 'home_sliders';
    protected $fillable = [
        'id',
        'title',
        'title_en',
        'description',
        'description_en',
        'button_text',
        'button_text_en',
        'button_link',
        'media',
        'media_type',
        'media_processing_status',
        'thumbnail_path',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Get the media URL
     */
    public function getMediaUrlAttribute(): ?string
    {
        return $this->media ? asset('storage/' . $this->media) : null;
    }

    /**
     * Get the thumbnail URL
     */
    public function getThumbnailUrlAttribute(): ?string
    {
        return $this->thumbnail_path ? asset('storage/' . $this->thumbnail_path) : null;
    }

    /**
     * Check if media is an image
     */
    public function isImage(): bool
    {
        return $this->media_type === 'image';
    }

    /**
     * Check if media is a video
     */
    public function isVideo(): bool
    {
        return $this->media_type === 'video';
    }

    /**
     * Check if media processing is completed
     */
    public function isProcessingCompleted(): bool
    {
        return $this->media_processing_status === 'completed';
    }

    /**
     * Check if media processing failed
     */
    public function isProcessingFailed(): bool
    {
        return $this->media_processing_status === 'failed';
    }

    /**
     * Scope for active sliders
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for ordered sliders
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order', 'asc');
    }

    protected function title(): Attribute
    {
        return Attribute::make(
            get: fn($value, array $attr) => $this->localizeAttr('title', $value, $attr)
        );
    }

    protected function description(): Attribute
    {
        return Attribute::make(
            get: fn($value, array $attr) => $this->localizeAttr('description', $value, $attr)
        );
    }

    protected function buttonText(): Attribute
    {
        return Attribute::make(
            get: fn($value, array $attr) => $this->localizeAttr('button_text', $value, $attr)
        );
    }
}
