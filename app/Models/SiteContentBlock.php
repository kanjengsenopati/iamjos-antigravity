<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;

/**
 * SiteContentBlock Model
 * 
 * Represents a configurable section/block on the portal landing page.
 * Part of the "Page Builder" system that allows admins to customize
 * the portal without code changes.
 */
class SiteContentBlock extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'key',
        'title',
        'description',
        'content',
        'config',
        'is_active',
        'sort_order',
        'icon',
        'category',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'config' => 'array',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Cache key for active blocks
     */
    const CACHE_KEY = 'site_content_blocks_active';
    const CACHE_TTL = 3600; // 1 hour

    /**
     * Get the user who created this block
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated this block
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the user who deleted this block
     */
    public function deleter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    /**
     * Scope: Active blocks only
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Ordered by sort_order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order', 'asc');
    }

    /**
     * Get all active blocks for rendering (with caching)
     */
    public static function getActiveBlocks(): \Illuminate\Support\Collection
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            return self::active()->ordered()->get();
        });
    }

    /**
     * Clear the blocks cache (call after any update)
     */
    public static function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
        
        // Clear data caches used by PortalController
        Cache::forget('featured_journals');
        Cache::forget('portal_stats');
        Cache::forget('all_journals');
        Cache::forget('latest_articles');
    }

    /**
     * Get a specific config value
     */
    public function getConfig(string $key, $default = null)
    {
        return data_get($this->config, $key, $default);
    }

    /**
     * Set a specific config value
     */
    public function setConfig(string $key, $value): self
    {
        $config = $this->config ?? [];
        data_set($config, $key, $value);
        $this->config = $config;
        return $this;
    }

    /**
     * Get the Blade component name for this block
     */
    public function getComponentName(): string
    {
        // Convert key to component name: hero_search -> site.hero-search
        $componentKey = str_replace('_', '-', $this->key);
        return "site.blocks.{$componentKey}";
    }

    /**
     * Check if this block type exists as a component
     */
    public function hasComponent(): bool
    {
        $componentPath = resource_path("views/components/site/blocks/{$this->getComponentPath()}.blade.php");
        return file_exists($componentPath);
    }

    /**
     * Get component path from key
     */
    protected function getComponentPath(): string
    {
        return str_replace('_', '-', $this->key);
    }

    /**
     * Bootstrap: Clear cache on save/delete and set audit fields
     */
    protected static function boot()
    {
        parent::boot();

        // Automatically set created_by and updated_by on creation
        static::creating(function ($model) {
            if (auth()->check()) {
                $model->created_by = $model->created_by ?? auth()->id();
                $model->updated_by = $model->updated_by ?? auth()->id();
            }
        });

        // Automatically set updated_by on update
        static::updating(function ($model) {
            if (auth()->check()) {
                $model->updated_by = auth()->id();
            }
        });

        // Automatically set deleted_by on soft delete
        static::deleting(function ($model) {
            if (auth()->check() && !$model->isForceDeleting()) {
                $model->deleted_by = auth()->id();
                $model->save();
            }
        });

        static::saved(function ($model) {
            self::clearCache();
        });

        static::deleted(function ($model) {
            self::clearCache();
        });
    }
}
