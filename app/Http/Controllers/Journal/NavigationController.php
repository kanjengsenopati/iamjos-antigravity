<?php

namespace App\Http\Controllers\Journal;

use App\Http\Controllers\Controller;
use App\Models\NavigationMenu;
use App\Models\NavigationItem;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class NavigationController extends Controller
{
    /**
     * Display navigation manager page
     */
    public function index(): View
    {
        $journal = current_journal();

        // Get or create default menus for this journal
        $menus = collect();
        foreach (NavigationMenu::getLocations() as $location => $name) {
            $menu = NavigationMenu::firstOrCreate(
                ['journal_id' => $journal->id, 'location' => $location],
                ['name' => $name, 'is_active' => true]
            );
            $menus->push($menu->load(['items' => fn($q) => $q->orderBy('order')]));
        }

        // Available routes for creating route-type items
        $availableRoutes = $this->getAvailableRoutes($journal);

        return view('journal.admin.settings.navigation', compact('journal', 'menus', 'availableRoutes'));
    }

    /**
     * Store a new navigation item
     */
    public function storeItem(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'menu_id' => 'required|uuid|exists:navigation_menus,id',
            'parent_id' => 'nullable|uuid|exists:navigation_items,id',
            'label' => 'required|string|max:255',
            'type' => 'required|in:custom,page,route,divider',
            'url' => 'nullable|string|max:500',
            'route_name' => 'nullable|string|max:255',
            'route_params' => 'nullable|array',
            'icon' => 'nullable|string|max:100',
            'target' => 'in:_self,_blank',
        ]);

        // Get next order
        $maxOrder = NavigationItem::where('menu_id', $validated['menu_id'])
            ->where('parent_id', $validated['parent_id'] ?? null)
            ->max('order') ?? 0;

        $item = NavigationItem::create([
            'menu_id' => $validated['menu_id'],
            'parent_id' => $validated['parent_id'] ?? null,
            'label' => $validated['label'],
            'type' => $validated['type'],
            'url' => $validated['url'] ?? null,
            'route_name' => $validated['route_name'] ?? null,
            'route_params' => $validated['route_params'] ?? null,
            'icon' => $validated['icon'] ?? null,
            'target' => $validated['target'] ?? '_self',
            'order' => $maxOrder + 1,
            'is_active' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Menu item created successfully.',
            'item' => $item,
        ]);
    }

    /**
     * Update a navigation item
     */
    public function updateItem(Request $request, NavigationItem $item): JsonResponse
    {
        $validated = $request->validate([
            'label' => 'sometimes|required|string|max:255',
            'type' => 'sometimes|in:custom,page,route,divider',
            'url' => 'nullable|string|max:500',
            'route_name' => 'nullable|string|max:255',
            'route_params' => 'nullable|array',
            'icon' => 'nullable|string|max:100',
            'target' => 'in:_self,_blank',
            'is_active' => 'boolean',
        ]);

        $item->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Menu item updated successfully.',
            'item' => $item->fresh(),
        ]);
    }

    /**
     * Delete a navigation item
     */
    public function destroyItem(NavigationItem $item): JsonResponse
    {
        $item->delete();

        return response()->json([
            'success' => true,
            'message' => 'Menu item deleted successfully.',
        ]);
    }

    /**
     * Reorder navigation items (drag-and-drop)
     */
    public function reorderItems(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|uuid|exists:navigation_items,id',
            'items.*.order' => 'required|integer|min:0',
            'items.*.parent_id' => 'nullable|uuid|exists:navigation_items,id',
        ]);

        foreach ($validated['items'] as $itemData) {
            NavigationItem::where('id', $itemData['id'])->update([
                'order' => $itemData['order'],
                'parent_id' => $itemData['parent_id'] ?? null,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Menu items reordered successfully.',
        ]);
    }

    /**
     * Get available routes for menu items
     */
    protected function getAvailableRoutes($journal): array
    {
        return [
            ['name' => 'journal.public.home', 'label' => 'Homepage', 'params' => ['journal' => '{journal}']],
            ['name' => 'journal.public.about', 'label' => 'About', 'params' => ['journal' => '{journal}']],
            ['name' => 'journal.public.editorial-team', 'label' => 'Editorial Team', 'params' => ['journal' => '{journal}']],
            ['name' => 'journal.public.current', 'label' => 'Current Issue', 'params' => ['journal' => '{journal}']],
            ['name' => 'journal.public.archives', 'label' => 'Archives', 'params' => ['journal' => '{journal}']],
            ['name' => 'journal.submissions.create', 'label' => 'Submit Article', 'params' => ['journal' => '{journal}']],
            ['name' => 'login', 'label' => 'Login', 'params' => []],
            ['name' => 'register', 'label' => 'Register', 'params' => []],
        ];
    }
}
