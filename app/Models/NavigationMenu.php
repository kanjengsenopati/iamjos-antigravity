<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NavigationMenu extends Model
{
    use HasFactory, HasUuids;

    /**
     * Menu location constants - Header Navigation Only
     */
    public const LOCATION_PRIMARY = 'primary';
    public const LOCATION_USER_TOP = 'user_top';
    public const LOCATION_FOOTER = 'footer';

    protected $fillable = [
        'journal_id',
        'name',
        'location',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    // =====================================================
    // RELATIONSHIPS
    // =====================================================

    /**
     * Get the journal this menu belongs to
     */
    public function journal(): BelongsTo
    {
        return $this->belongsTo(Journal::class);
    }

    /**
     * Get all items in this menu
     */
    public function items(): HasMany
    {
        return $this->hasMany(NavigationItem::class, 'menu_id')->orderBy('order');
    }

    /**
     * Get only root-level items (no parent)
     */
    public function rootItems(): HasMany
    {
        return $this->hasMany(NavigationItem::class, 'menu_id')
            ->whereNull('parent_id')
            ->where('is_active', true)
            ->orderBy('order');
    }

    /**
     * Get menu tree structure (nested items)
     */
    public function getTreeAttribute(): \Illuminate\Support\Collection
    {
        return $this->buildTree($this->rootItems()->with('activeChildren')->get());
    }

    /**
     * Build nested tree from flat items
     */
    protected function buildTree($items): \Illuminate\Support\Collection
    {
        return $items->map(function ($item) {
            $item->children = $this->buildTree($item->activeChildren);
            return $item;
        });
    }

    // =====================================================
    // SCOPES
    // =====================================================

    /**
     * Scope to active menus only
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope by location
     */
    public function scopeLocation($query, string $location)
    {
        return $query->where('location', $location);
    }

    /**
     * Scope by journal (or site-wide)
     */
    public function scopeForJournal($query, ?string $journalId)
    {
        if ($journalId) {
            return $query->where(function ($q) use ($journalId) {
                $q->where('journal_id', $journalId)
                    ->orWhereNull('journal_id');
            });
        }
        return $query->whereNull('journal_id');
    }

    // =====================================================
    // STATIC HELPERS
    // =====================================================

    /**
     * Get menu for a specific location and journal
     */
    public static function getMenu(string $location, ?string $journalId = null): ?self
    {
        return static::active()
            ->location($location)
            ->forJournal($journalId)
            ->orderByRaw('journal_id IS NULL') // Prioritize journal-specific over site-wide
            ->first();
    }

    /**
     * Get all available locations (Header Navigation Only)
     */
    public static function getLocations(): array
    {
        return [
            self::LOCATION_PRIMARY => 'Primary Header',
            self::LOCATION_USER_TOP => 'User Topbar',
            self::LOCATION_FOOTER => 'Footer',
        ];
    }
}
