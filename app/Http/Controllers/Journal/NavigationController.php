<?php

namespace App\Http\Controllers\Journal;

use App\Http\Controllers\Controller;
use App\Models\NavigationMenu;
use App\Models\NavigationMenuItem;
use App\Models\NavigationMenuItemAssignment;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class NavigationController extends Controller
{
    /**
     * Display navigation manager page
     */
    public function index(): View
    {
        $journal = current_journal();

        $menus = NavigationMenu::where('journal_id', $journal->id)
            ->with(['assignments.item'])
            ->get();

        // Get all items for this journal (system + custom merged properly)
        $items = $this->getAllMenuItems($journal);

        $availableRoutes = NavigationMenuItem::getAvailableRoutes();

        return view('journal.admin.settings.navigation', compact('journal', 'menus', 'items', 'availableRoutes'));
    }

    /**
     * Get all menu items (system + custom) without duplicates
     */
    private function getAllMenuItems($journal): \Illuminate\Support\Collection
    {
        // System route names
        $systemRouteNames = [
            'journal.public.home',
            'journal.public.about',
            'journal.public.editorial-team',
            'journal.public.current',
            'journal.public.archives',
            'journal.public.author-guidelines',
            'journal.public.announcements',
            'journal.submissions.create',
            'journal.public.search',
        ];

        // Get all DB items for this journal
        $dbItems = NavigationMenuItem::where('journal_id', $journal->id)
            ->orderBy('title')
            ->get()
            ->keyBy('route_name'); // Key by route_name for easy lookup

        // Build the items list
        $items = collect();

        // 1. Add system items (from DB if exists, or virtual)
        $systemConfigs = [
            'journal.public.home' => ['title' => 'Home', 'icon' => 'fa-solid fa-house'],
            'journal.public.about' => ['title' => 'About', 'icon' => 'fa-solid fa-info-circle'],
            'journal.public.editorial-team' => ['title' => 'Editorial Team', 'icon' => 'fa-solid fa-users'],
            'journal.public.current' => ['title' => 'Current Issue', 'icon' => 'fa-solid fa-newspaper'],
            'journal.public.archives' => ['title' => 'Archives', 'icon' => 'fa-solid fa-archive'],
            'journal.public.author-guidelines' => ['title' => 'Author Guidelines', 'icon' => 'fa-solid fa-book'],
            'journal.public.announcements' => ['title' => 'Announcements', 'icon' => 'fa-solid fa-bullhorn'],
            'journal.submissions.create' => ['title' => 'Submit Article', 'icon' => 'fa-solid fa-paper-plane'],
            'journal.public.search' => ['title' => 'Search', 'icon' => 'fa-solid fa-search'],
        ];

        foreach ($systemConfigs as $routeName => $config) {
            if ($dbItems->has($routeName)) {
                // Use DB version but mark as system
                $item = $dbItems->get($routeName);
                $item->is_system = true;
                $items->push($item);
            } else {
                // Create virtual item
                $item = new NavigationMenuItem([
                    'journal_id' => $journal->id,
                    'title' => $config['title'],
                    'type' => NavigationMenuItem::TYPE_ROUTE,
                    'route_name' => $routeName,
                    'icon' => $config['icon'],
                    'target' => '_self',
                    'is_active' => true,
                ]);
                $item->id = 'system_' . md5($routeName);
                $item->is_system = true;
                $items->push($item);
            }
        }

        // 2. Add custom items (items not in system routes)
        foreach ($dbItems as $routeName => $item) {
            if (!in_array($routeName, $systemRouteNames)) {
                $item->is_system = false;
                $items->push($item);
            }
        }

        return $items;
    }

    // =====================================================
    // MENU CRUD
    // =====================================================

    /**
     * Store a new menu
     */
    public function storeMenu(Request $request): RedirectResponse
    {
        $journal = current_journal();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'area_name' => 'nullable|string|in:primary,user',
        ]);

        // If assigning to an area, unassign other menus from that area
        if ($validated['area_name']) {
            NavigationMenu::where('journal_id', $journal->id)
                ->where('area_name', $validated['area_name'])
                ->update(['area_name' => null]);
        }

        NavigationMenu::create([
            'journal_id' => $journal->id,
            'title' => $validated['title'],
            'area_name' => $validated['area_name'],
            'is_active' => true,
        ]);

        return back()->with('success', 'Menu created successfully.');
    }

    /**
     * Update a menu
     */
    public function updateMenu(Request $request, string $journal, NavigationMenu $menu): RedirectResponse
    {
        $journal = current_journal();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'area_name' => 'nullable|string|in:primary,user',
        ]);

        // If assigning to an area, unassign other menus from that area
        if ($validated['area_name'] && $validated['area_name'] !== $menu->area_name) {
            NavigationMenu::where('journal_id', $journal->id)
                ->where('area_name', $validated['area_name'])
                ->where('id', '!=', $menu->id)
                ->update(['area_name' => null]);
        }

        $menu->update($validated);

        return back()->with('success', 'Menu updated successfully.');
    }

    /**
     * Delete a menu
     */
    public function destroyMenu(string $journal, NavigationMenu $menu): RedirectResponse
    {
        // Delete all assignments first
        $menu->assignments()->delete();
        $menu->delete();

        return back()->with('success', 'Menu deleted successfully.');
    }

    // =====================================================
    // MENU ITEM CRUD
    // =====================================================

    /**
     * Store a new menu item
     */
    public function storeItem(Request $request): RedirectResponse
    {
        $journal = current_journal();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:custom,route,page',
            'url' => 'nullable|string|max:500',
            'route_name' => 'nullable|string|max:255',
            'icon' => 'nullable|string|max:100',
            'target' => 'in:_self,_blank',
        ]);

        NavigationMenuItem::create([
            'journal_id' => $journal->id,
            'title' => $validated['title'],
            'type' => $validated['type'],
            'url' => $validated['url'] ?? null,
            'route_name' => $validated['route_name'] ?? null,
            'icon' => $validated['icon'] ?? null,
            'target' => $validated['target'] ?? '_self',
            'is_active' => true,
        ]);

        return back()->with('success', 'Menu item created successfully.');
    }

    /**
     * Update a menu item
     */
    public function updateItem(Request $request, string $journal, NavigationMenuItem $item): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:custom,route,page',
            'url' => 'nullable|string|max:500',
            'route_name' => 'nullable|string|max:255',
            'icon' => 'nullable|string|max:100',
            'target' => 'in:_self,_blank',
        ]);

        $item->update([
            'title' => $validated['title'],
            'type' => $validated['type'],
            'url' => $validated['url'] ?? null,
            'route_name' => $validated['route_name'] ?? null,
            'icon' => $validated['icon'] ?? null,
            'target' => $validated['target'] ?? '_self',
        ]);

        return back()->with('success', 'Menu item updated successfully.');
    }

    /**
     * Delete a menu item
     */
    public function destroyItem(string $journal, NavigationMenuItem $item): RedirectResponse
    {
        // Delete all assignments first
        $item->assignments()->delete();
        $item->delete();

        return back()->with('success', 'Menu item deleted successfully.');
    }

    // =====================================================
    // ASSIGNMENT ACTIONS
    // =====================================================

    /**
     * Assign an item to a menu
     */
    public function assignItem(Request $request): RedirectResponse
    {
        $journal = current_journal();
        
        $validated = $request->validate([
            'menu_id' => 'required|uuid|exists:navigation_menus,id',
            'menu_item_id' => 'required|string',
            'route_name' => 'nullable|string', // For system items
        ]);

        $menuItemId = $validated['menu_item_id'];

        // Handle system items (virtual items not yet in DB)
        if (str_starts_with($menuItemId, 'system_') && !empty($validated['route_name'])) {
            // Create the item in DB first
            $systemItem = $this->createSystemItemInDb($journal, $validated['route_name']);
            $menuItemId = $systemItem->id;
        }

        // Check if already assigned
        $exists = NavigationMenuItemAssignment::where('menu_id', $validated['menu_id'])
            ->where('menu_item_id', $menuItemId)
            ->exists();

        if (!$exists) {
            $maxOrder = NavigationMenuItemAssignment::where('menu_id', $validated['menu_id'])
                ->max('order') ?? 0;

            NavigationMenuItemAssignment::create([
                'menu_id' => $validated['menu_id'],
                'menu_item_id' => $menuItemId,
                'order' => $maxOrder + 1,
            ]);
        }

        return back()->with('success', 'Item assigned to menu.');
    }

    /**
     * Create a system item in database when first assigned
     */
    private function createSystemItemInDb($journal, string $routeName): NavigationMenuItem
    {
        // Check if already exists
        $existing = NavigationMenuItem::where('journal_id', $journal->id)
            ->where('route_name', $routeName)
            ->first();

        if ($existing) {
            return $existing;
        }

        // Find the system route config
        $systemRoutes = [
            'journal.public.home' => ['title' => 'Home', 'icon' => 'fa-solid fa-house'],
            'journal.public.about' => ['title' => 'About', 'icon' => 'fa-solid fa-info-circle'],
            'journal.public.editorial-team' => ['title' => 'Editorial Team', 'icon' => 'fa-solid fa-users'],
            'journal.public.current' => ['title' => 'Current Issue', 'icon' => 'fa-solid fa-newspaper'],
            'journal.public.archives' => ['title' => 'Archives', 'icon' => 'fa-solid fa-archive'],
            'journal.public.author-guidelines' => ['title' => 'Author Guidelines', 'icon' => 'fa-solid fa-book'],
            'journal.public.announcements' => ['title' => 'Announcements', 'icon' => 'fa-solid fa-bullhorn'],
            'journal.submissions.create' => ['title' => 'Submit Article', 'icon' => 'fa-solid fa-paper-plane'],
            'journal.public.search' => ['title' => 'Search', 'icon' => 'fa-solid fa-search'],
        ];

        $config = $systemRoutes[$routeName] ?? ['title' => $routeName, 'icon' => 'fa-solid fa-link'];

        return NavigationMenuItem::create([
            'journal_id' => $journal->id,
            'title' => $config['title'],
            'type' => NavigationMenuItem::TYPE_ROUTE,
            'route_name' => $routeName,
            'icon' => $config['icon'],
            'target' => '_self',
            'is_active' => true,
        ]);
    }

    /**
     * Unassign an item from a menu
     */
    public function unassignItem(string $journal, NavigationMenuItemAssignment $assignment): RedirectResponse
    {
        $assignment->delete();

        return back()->with('success', 'Item removed from menu.');
    }

    /**
     * Move item up in order
     */
    public function moveUp(string $journal, NavigationMenuItemAssignment $assignment): RedirectResponse
    {
        $previous = NavigationMenuItemAssignment::where('menu_id', $assignment->menu_id)
            ->whereNull('parent_id')
            ->where('order', '<', $assignment->order)
            ->orderBy('order', 'desc')
            ->first();

        if ($previous) {
            $tempOrder = $assignment->order;
            $assignment->update(['order' => $previous->order]);
            $previous->update(['order' => $tempOrder]);
        }

        return back();
    }

    /**
     * Move item down in order
     */
    public function moveDown(string $journal, NavigationMenuItemAssignment $assignment): RedirectResponse
    {
        $next = NavigationMenuItemAssignment::where('menu_id', $assignment->menu_id)
            ->whereNull('parent_id')
            ->where('order', '>', $assignment->order)
            ->orderBy('order', 'asc')
            ->first();

        if ($next) {
            $tempOrder = $assignment->order;
            $assignment->update(['order' => $next->order]);
            $next->update(['order' => $tempOrder]);
        }

        return back();
    }
}
