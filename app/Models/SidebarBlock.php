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
            'component' => 'public.blocks.information-block',
            'icon' => 'fa-solid fa-info-circle',
            'description' => 'Displays journal information and key details.',
        ],
        'login' => [
            'name' => 'Login',
            'component' => 'public.blocks.login-block',
            'icon' => 'fa-solid fa-sign-in-alt',
            'description' => 'User login form for the sidebar.',
        ],
        'submit' => [
            'name' => 'Submit Article',
            'component' => 'public.blocks.make-submission-block',
            'icon' => 'fa-solid fa-paper-plane',
            'description' => 'Call-to-action button for article submission.',
        ],
        'search' => [
            'name' => 'Search',
            'component' => 'public.blocks.search-block',
            'icon' => 'fa-solid fa-search',
            'description' => 'Search form for the journal.',
        ],
        'current-issue' => [
            'name' => 'Current Issue',
            'component' => 'public.blocks.current-issue-block',
            'icon' => 'fa-solid fa-book-open',
            'description' => 'Displays the current issue details.',
        ],
        'categories' => [
            'name' => 'Categories',
            'component' => 'public.blocks.categories-block',
            'icon' => 'fa-solid fa-tags',
            'description' => 'List of article categories.',
        ],
        'announcements' => [
            'name' => 'Recent Announcements',
            'component' => 'public.blocks.announcements-block',
            'icon' => 'fa-solid fa-bullhorn',
            'description' => 'Recent journal announcements.',
        ],
    ];

    protected $fillable = [
        'journal_id',
        'type', // 'system', 'block', 'page'
        'slug',
        'title',
        'show_title',
        'content',
        'sidebar_content',
        'component_name',
        'icon',
        'settings',
        'is_active',
        'position',
        'order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'metadata' => 'array',
    ];

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
            ['type' => 'system', 'component_name' => 'public.blocks.information-block', 'title' => 'Information', 'icon' => 'fa-solid fa-info-circle', 'order' => 1],
            ['type' => 'system', 'component_name' => 'public.blocks.make-submission-block', 'title' => 'Submit Article', 'icon' => 'fa-solid fa-paper-plane', 'order' => 2],
            ['type' => 'system', 'component_name' => 'public.blocks.current-issue-block', 'title' => 'Current Issue', 'icon' => 'fa-solid fa-book-open', 'order' => 3],
        ];

        foreach ($defaults as $block) {
            static::create(array_merge($block, [
                'journal_id' => $journalId,
                'position' => 'right',
                'is_active' => true,
            ]));
        }
    }

    /**
     * Get parsed content with correctly resolved relative links.
     */
    public function getParsedContentAttribute(): string
    {
        return $this->parseHtmlLinks($this->content);
    }

    /**
     * Get parsed sidebar teaser content with correctly resolved relative links.
     */
    public function getParsedSidebarContentAttribute(): string
    {
        return $this->parseHtmlLinks($this->sidebar_content);
    }

    /**
     * Scans HTML content for relative links and converts them into proper absolute/resolved URLs.
     */
    private function parseHtmlLinks(?string $html): string
    {
        if (empty($html)) {
            return '';
        }

        $journal = $this->journal;
        if (!$journal) {
            return $html;
        }

        return preg_replace_callback('/<a\s+([^>]*?)href=["\']([^"\']*)["\']([^>]*?)>/i', function ($matches) use ($journal) {
            $attrsBefore = $matches[1];
            $href = trim($matches[2]);
            $attrsAfter = $matches[3];

            // Ignore empty, anchor hashes, full protocols, mailto, tel, javascript, or protocol-relative
            if (empty($href) || 
                str_starts_with($href, '#') || 
                str_starts_with($href, 'http://') || 
                str_starts_with($href, 'https://') || 
                str_starts_with($href, 'mailto:') || 
                str_starts_with($href, 'tel:') || 
                str_starts_with($href, 'javascript:') ||
                str_starts_with($href, '//')) {
                return $matches[0];
            }

            // Clean leading slashes/spaces for matching
            $cleanPath = ltrim($href, '/');

            // 1. Resolve Custom Pages (Sidebar custom page or Navigation custom page)
            $customPageSlugs = \Cache::remember("journal_{$journal->id}_custom_pages", 60, function () use ($journal) {
                $sidebarPages = \App\Models\SidebarBlock::where('journal_id', $journal->id)
                    ->where('type', 'page')
                    ->whereNotNull('slug')
                    ->pluck('slug')
                    ->toArray();

                $menuPages = \App\Models\NavigationMenuItem::where('journal_id', $journal->id)
                    ->where('type', 'page')
                    ->whereNotNull('path')
                    ->pluck('path')
                    ->toArray();

                return array_unique(array_merge($sidebarPages, $menuPages));
            });

            $pageSlug = null;
            if (in_array($cleanPath, $customPageSlugs)) {
                $pageSlug = $cleanPath;
            } elseif (str_starts_with($cleanPath, 'page/')) {
                $possibleSlug = substr($cleanPath, 5);
                if (in_array($possibleSlug, $customPageSlugs)) {
                    $pageSlug = $possibleSlug;
                }
            }

            if ($pageSlug) {
                $newHref = route('journal.custom-page', ['journal' => $journal->slug, 'path' => $pageSlug]);
                return "<a {$attrsBefore}href=\"{$newHref}\"{$attrsAfter}>";
            }

            // 2. Resolve Built-in Routes
            $builtInRoutes = [
                'home' => 'journal.public.home',
                'about' => 'journal.public.about',
                'contact' => 'journal.public.contact',
                'editorial-team' => 'journal.public.editorial-team',
                'author-guidelines' => 'journal.public.author-guidelines',
                'archives' => 'journal.public.archives',
                'current' => 'journal.public.current',
                'announcement' => 'journal.announcement.index',
                'information/readers' => 'journal.info.readers',
                'information/authors' => 'journal.info.authors',
                'information/librarians' => 'journal.info.librarians',
            ];

            if (isset($builtInRoutes[$cleanPath])) {
                $newHref = route($builtInRoutes[$cleanPath], ['journal' => $journal->slug]);
                return "<a {$attrsBefore}href=\"{$newHref}\"{$attrsAfter}>";
            }

            // 3. Fallback: Prepend the absolute journal base URL
            $newHref = url('/' . $journal->slug . '/' . $cleanPath);
            return "<a {$attrsBefore}href=\"{$newHref}\"{$attrsAfter}>";
        }, $html);
    }
}
