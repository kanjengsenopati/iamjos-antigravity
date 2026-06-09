<?php

namespace App\View\Composers;

use App\Models\NavigationMenu;
use App\Models\NavigationMenuItem;
use App\Models\SidebarBlock;
use Illuminate\View\View;

class PublicLayoutComposer
{
    /**
     * Bind data to the view.
     */
    public function compose(View $view): void
    {
        $journal = current_journal();

        if (!$journal) {
            return;
        }

        // Fetch Primary Menu Items (area_name = 'primary')
        $primaryMenu = $this->getMenuItems($journal->id, NavigationMenu::AREA_PRIMARY);

        // FALLBACK: If no custom primary menu exists, use OJS 3.3 defaults
        if ($primaryMenu->isEmpty()) {
            $primaryMenu = $this->getDefaultOJSMenu($journal);
        }

        // Fetch User Menu Items (area_name = 'user')
        $userMenu = $this->getMenuItems($journal->id, NavigationMenu::AREA_USER);

        // Fetch Footer Menu Items (area_name = 'footer')
        $footerMenu = $this->getMenuItems($journal->id, 'footer');

        // Fetch Active Sidebar Blocks
        $sidebarBlocks = SidebarBlock::where('journal_id', $journal->id)
            ->where('is_active', true)
            ->orderBy('order')
            ->get();

        // Share data with the view
        $view->with([
            'primaryMenu' => $primaryMenu,
            'userMenu' => $userMenu,
            'footerMenu' => $footerMenu,
            'sidebarBlocks' => $sidebarBlocks ?? collect(),
            'journal' => $journal,
        ]);
    }

    /**
     * Get menu items for a specific area as a tree structure.
     */
    protected function getMenuItems(string $journalId, string $area): \Illuminate\Support\Collection
    {
        // First, get the active menu for this area and journal
        $menu = NavigationMenu::where('journal_id', $journalId)
            ->where('area_name', $area)
            ->where('is_active', true)
            ->first();

        if (!$menu) {
            return collect([]);
        }

        // Return the mapped items tree structure
        return $this->buildNavItems($menu);
    }

    /**
     * Build navigation items from menu assignments
     */
    protected function buildNavItems(NavigationMenu $menu): \Illuminate\Support\Collection
    {
        $assignments = $menu->rootAssignments()
            ->with(['item', 'children.item'])
            ->get();

        return $assignments->filter(fn($a) => $a->item && $a->item->is_active)
            ->map(function ($assignment) {
                $item = $assignment->item;

                return (object) [
                    'id' => $item->id,
                    'label' => $item->title,
                    'icon' => $item->icon,
                    'target' => $item->target ?? '_self',
                    'resolved_url' => $this->resolveItemUrl($item),
                    'is_divider' => false,
                    'children' => $assignment->children->filter(fn($c) => $c->item && $c->item->is_active)
                        ->map(function ($child) {
                            $childItem = $child->item;
                            return (object) [
                                'id' => $childItem->id,
                                'label' => $childItem->title,
                                'icon' => $childItem->icon,
                                'target' => $childItem->target ?? '_self',
                                'resolved_url' => $this->resolveItemUrl($childItem),
                                'is_divider' => false,
                            ];
                        }),
                ];
            });
    }

    /**
     * Resolve the URL for a menu item.
     */
    protected function resolveItemUrl($item): string
    {
        if ($item->type === 'custom' && $item->url) {
            return $item->url;
        }

        if ($item->type === 'route' && $item->route_name) {
            try {
                $params = [];
                // Add journal slug if route requires it
                if (str_contains($item->route_name, 'journal.')) {
                    $journal = current_journal();
                    if ($journal) {
                        $params = array_merge(['journal' => $journal->slug], $params);
                    }
                }
                return route($item->route_name, $params);
            } catch (\Exception $e) {
                return '#';
            }
        }

        if ($item->type === 'page' && $item->path) {
            $journal = current_journal();
            if ($journal) {
                return route('journal.custom-page', ['journal' => $journal->slug, 'path' => $item->path]);
            }
        }

        return '#';
    }

    /**
     * Generate default OJS 3.3 menu structure.
     * Returns a collection of objects mimicking NavigationItem structure.
     * Order: Current | Archives | Announcements | About (Dropdown)
     */
    protected function getDefaultOJSMenu($journal): \Illuminate\Support\Collection
    {
        $menu = collect([]);

        // 1. CURRENT (First item in OJS 3.3)
        $menu->push((object)[
            'label' => 'Current',
            'resolved_url' => route('journal.public.current', ['journal' => $journal->slug]),
            'children' => collect([]),
            'target' => '_self',
            'type' => 'route',
            'is_active' => true,
        ]);

        // 2. ARCHIVES
        $menu->push((object)[
            'label' => 'Archives',
            'resolved_url' => route('journal.public.archives', ['journal' => $journal->slug]),
            'children' => collect([]),
            'target' => '_self',
            'type' => 'route',
            'is_active' => true,
        ]);

        // 3. ANNOUNCEMENTS (Conditional - only if enabled)
        if ($journal->enable_announcements) {
            $menu->push((object)[
                'label' => 'Announcements',
                'resolved_url' => route('journal.announcement.index', ['journal' => $journal->slug]),
                'children' => collect([]),
                'target' => '_self',
                'type' => 'route',
                'is_active' => true,
            ]);
        }

        // 4. ABOUT (Dropdown)
        $aboutChildren = collect([
            (object)[
                'label' => 'About the Journal',
                'resolved_url' => route('journal.public.about', ['journal' => $journal->slug]),
                'target' => '_self',
                'type' => 'route',
                'is_active' => true,
            ],
            (object)[
                'label' => 'Submissions',
                'resolved_url' => route('journal.public.author-guidelines', ['journal' => $journal->slug]),
                'target' => '_self',
                'type' => 'route',
                'is_active' => true,
            ],
            (object)[
                'label' => 'Editorial Team',
                'resolved_url' => route('journal.public.editorial-team', ['journal' => $journal->slug]),
                'target' => '_self',
                'type' => 'route',
                'is_active' => true,
            ],
        ]);

        $menu->push((object)[
            'label' => 'About',
            'resolved_url' => '#',
            'children' => $aboutChildren,
            'target' => '_self',
            'type' => 'dropdown',
            'is_active' => true,
        ]);

        return $menu;
    }
}
