<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NavigationMenuItem extends Model
{
    use HasFactory, HasUuids;

    /**
     * Item type constants
     */
    public const TYPE_CUSTOM = 'custom';
    public const TYPE_ROUTE = 'route';
    public const TYPE_PAGE = 'page';

    protected $fillable = [
        'journal_id',
        'title',
        'type',
        'url',
        'route_name',
        'related_id',
        'icon',
        'target',
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
     * Get the journal this item belongs to
     */
    public function journal(): BelongsTo
    {
        return $this->belongsTo(Journal::class);
    }

    /**
     * Get all assignments for this item
     */
    public function assignments(): HasMany
    {
        return $this->hasMany(NavigationMenuItemAssignment::class, 'menu_item_id');
    }

    // =====================================================
    // ACCESSORS
    // =====================================================

    /**
     * Get the resolved URL for this item
     */
    public function getResolvedUrlAttribute(): ?string
    {
        if ($this->type === self::TYPE_CUSTOM) {
            return $this->url;
        }

        if ($this->type === self::TYPE_ROUTE && $this->route_name) {
            try {
                // Get the journal from context
                $journal = current_journal();
                if ($journal && str_starts_with($this->route_name, 'journal.')) {
                    return route($this->route_name, ['journal' => $journal->slug]);
                }
                return route($this->route_name);
            } catch (\Exception $e) {
                return '#';
            }
        }

        if ($this->type === self::TYPE_PAGE && $this->related_id) {
            // TODO: Implement page URL resolution
            return '#';
        }

        return '#';
    }

    // =====================================================
    // SCOPES
    // =====================================================

    /**
     * Scope to active items only
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to items for a specific journal
     */
    public function scopeForJournal($query, $journalId)
    {
        return $query->where('journal_id', $journalId);
    }

    // =====================================================
    // STATIC METHODS
    // =====================================================

    /**
     * Get available route options for menu items
     */
    public static function getAvailableRoutes(): array
    {
        return [
            ['name' => 'journal.public.home', 'label' => 'Homepage'],
            ['name' => 'journal.public.about', 'label' => 'About'],
            ['name' => 'journal.public.editorial-team', 'label' => 'Editorial Team'],
            ['name' => 'journal.public.current', 'label' => 'Current Issue'],
            ['name' => 'journal.public.archives', 'label' => 'Archives'],
            ['name' => 'journal.public.author-guidelines', 'label' => 'Author Guidelines'],
            ['name' => 'journal.public.announcements', 'label' => 'Announcements'],
            ['name' => 'journal.submissions.create', 'label' => 'Submit Article'],
            ['name' => 'journal.login', 'label' => 'Login'],
            ['name' => 'journal.register', 'label' => 'Register'],
            ['name' => 'journal.public.search', 'label' => 'Search'],
        ];
    }
}
