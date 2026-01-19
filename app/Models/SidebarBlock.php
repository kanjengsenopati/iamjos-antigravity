<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SidebarBlock extends Model
{
    use HasFactory, HasUuids;

    /**
     * Block type constants
     */
    public const TYPE_SYSTEM = 'system';
    public const TYPE_CUSTOM = 'custom';

    /**
     * Position constants
     */
    public const POSITION_LEFT = 'left';
    public const POSITION_RIGHT = 'right';

    /**
     * System block component names
     */
    public const SYSTEM_BLOCKS = [
        'information' => [
            'name' => 'Information',
            'component' => 'sidebar.information-block',
            'icon' => 'fa-solid fa-info-circle',
            'description' => 'Displays journal information and key details.',
        ],
        'login' => [
            'name' => 'Login',
            'component' => 'sidebar.login-block',
            'icon' => 'fa-solid fa-sign-in-alt',
            'description' => 'User login form for the sidebar.',
        ],
        'language' => [
            'name' => 'Language Selector',
            'component' => 'sidebar.language-block',
            'icon' => 'fa-solid fa-globe',
            'description' => 'Language switcher dropdown.',
        ],
        'submit' => [
            'name' => 'Submit Article',
            'component' => 'sidebar.submit-block',
            'icon' => 'fa-solid fa-paper-plane',
            'description' => 'Call-to-action button for article submission.',
        ],
        'search' => [
            'name' => 'Search',
            'component' => 'sidebar.search-block',
            'icon' => 'fa-solid fa-search',
            'description' => 'Search form for the journal.',
        ],
        'current-issue' => [
            'name' => 'Current Issue',
            'component' => 'sidebar.current-issue-block',
            'icon' => 'fa-solid fa-book-open',
            'description' => 'Displays the current issue details.',
        ],
        'categories' => [
            'name' => 'Categories',
            'component' => 'sidebar.categories-block',
            'icon' => 'fa-solid fa-tags',
            'description' => 'List of article categories.',
        ],
        'announcements' => [
            'name' => 'Recent Announcements',
            'component' => 'sidebar.announcements-block',
            'icon' => 'fa-solid fa-bullhorn',
            'description' => 'Recent journal announcements.',
        ],
    ];

    protected $fillable = [
        'journal_id',
        'type',
        'title',
        'content',
        'component_name',
        'icon',
        'settings',
        'is_active',
        'position',
        'order',
    ];

    protected function casts(): array
    {
        return [
            'settings' => 'array',
            'is_active' => 'boolean',
            'order' => 'integer',
        ];
    }

    // =====================================================
    // RELATIONSHIPS
    // =====================================================

    /**
     * Get the journal this block belongs to
     */
    public function journal(): BelongsTo
    {
        return $this->belongsTo(Journal::class);
    }

    // =====================================================
    // ACCESSORS
    // =====================================================

    /**
     * Check if this is a system block
     */
    public function getIsSystemAttribute(): bool
    {
        return $this->type === self::TYPE_SYSTEM;
    }

    /**
     * Check if this is a custom block
     */
    public function getIsCustomAttribute(): bool
    {
        return $this->type === self::TYPE_CUSTOM;
    }

    /**
     * Get the Blade component name for rendering
     */
    public function getComponentAttribute(): ?string
    {
        if ($this->is_system && $this->component_name) {
            return $this->component_name;
        }
        return null;
    }

    /**
     * Get system block info
     */
    public function getSystemBlockInfoAttribute(): ?array
    {
        if (!$this->is_system) {
            return null;
        }

        foreach (self::SYSTEM_BLOCKS as $key => $block) {
            if ($block['component'] === $this->component_name) {
                return array_merge(['key' => $key], $block);
            }
        }
        return null;
    }

    // =====================================================
    // SCOPES
    // =====================================================

    /**
     * Scope to active blocks only
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope by position
     */
    public function scopePosition($query, string $position)
    {
        return $query->where('position', $position);
    }

    /**
     * Scope ordered blocks
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }

    /**
     * Scope by journal
     */
    public function scopeForJournal($query, string $journalId)
    {
        return $query->where('journal_id', $journalId);
    }

    // =====================================================
    // STATIC HELPERS
    // =====================================================

    /**
     * Get available system blocks
     */
    public static function getSystemBlocks(): array
    {
        return self::SYSTEM_BLOCKS;
    }

    /**
     * Get blocks for a journal sidebar
     */
    public static function getActiveBlocks(string $journalId, string $position = 'right'): \Illuminate\Database\Eloquent\Collection
    {
        return static::forJournal($journalId)
            ->active()
            ->position($position)
            ->ordered()
            ->get();
    }

    /**
     * Create default blocks for a new journal
     */
    public static function createDefaultBlocks(string $journalId): void
    {
        $defaults = [
            ['type' => 'system', 'component_name' => 'sidebar.information-block', 'title' => 'Information', 'icon' => 'fa-solid fa-info-circle', 'order' => 1],
            ['type' => 'system', 'component_name' => 'sidebar.submit-block', 'title' => 'Submit Article', 'icon' => 'fa-solid fa-paper-plane', 'order' => 2],
            ['type' => 'system', 'component_name' => 'sidebar.current-issue-block', 'title' => 'Current Issue', 'icon' => 'fa-solid fa-book-open', 'order' => 3],
        ];

        foreach ($defaults as $block) {
            static::create(array_merge($block, [
                'journal_id' => $journalId,
                'position' => 'right',
                'is_active' => true,
            ]));
        }
    }
}
