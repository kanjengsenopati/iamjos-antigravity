<?php

namespace App\View\Composers;

use App\Models\NavigationMenu;
use App\Models\NavigationItem;
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

        // Fetch Primary Menu Items (location = 'primary')
        $primaryMenu = $this->getMenuItems($journal->id, NavigationMenu::LOCATION_PRIMARY);

        // Fetch User Menu Items (location = 'user_top')
        $userMenu = $this->getMenuItems($journal->id, NavigationMenu::LOCATION_USER_TOP);

        // Fetch Footer Menu Items (location = 'footer')
        $footerMenu = $this->getMenuItems($journal->id, NavigationMenu::LOCATION_FOOTER);

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
     * Get menu items for a specific location as a tree structure.
     */
    protected function getMenuItems(string $journalId, string $location): \Illuminate\Support\Collection
    {
        // First, get or create the menu for this location
        $menu = NavigationMenu::where('journal_id', $journalId)
            ->where('location', $location)
            ->where('is_active', true)
            ->first();

        if (!$menu) {
            return collect([]);
        }

        // Get all items for this menu
        $items = NavigationItem::where('menu_id', $menu->id)
            ->where('is_active', true)
            ->orderBy('order')
            ->get();

        // Build tree structure
        return $this->buildTree($items);
    }

    /**
     * Build a tree structure from flat items.
     */
    protected function buildTree(\Illuminate\Support\Collection $items, ?string $parentId = null): \Illuminate\Support\Collection
    {
        $branch = collect([]);

        foreach ($items as $item) {
            if ($item->parent_id === $parentId) {
                $children = $this->buildTree($items, $item->id);

                if ($children->isNotEmpty()) {
                    $item->children = $children;
                } else {
                    $item->children = collect([]);
                }

                // Resolve dynamic URL for route-type items
                $item->resolved_url = $this->resolveItemUrl($item);

                $branch->push($item);
            }
        }

        return $branch;
    }

    /**
     * Resolve the URL for a menu item.
     */
    protected function resolveItemUrl(NavigationItem $item): string
    {
        if ($item->type === 'divider') {
            return '#';
        }

        if ($item->type === 'route' && $item->route_name) {
            try {
                $params = $item->route_params ?? [];
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

        return $item->url ?? '#';
    }
}
