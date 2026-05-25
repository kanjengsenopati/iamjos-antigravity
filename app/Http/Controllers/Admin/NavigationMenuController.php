<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\NavigationMenuRequest;
use App\Http\Resources\NavigationMenuResource;
use App\Models\NavigationMenu;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * NavigationMenuController
 * 
 * Manages navigation menus with hierarchical menu items.
 * Supports CRUD operations, drag-and-drop reordering, and nested menu structures.
 */
class NavigationMenuController extends Controller
{
    /**
     * Display a listing of navigation menus.
     * Supports AJAX requests with pagination and eager loading.
     */
    public function index(Request $request)
    {
        // Check authorization
        $this->authorize('manage-navigation');

        // Check if this is an AJAX/API request
        if ($request->wantsJson() || $request->is('admin/api/*')) {
            return $this->indexApi($request);
        }

        // Traditional view response
        $menus = NavigationMenu::with(['items.item', 'items.children.item'])->get();
        return view('admin.navigation-menus.index', compact('menus'));
    }

    /**
     * API endpoint for listing menus with pagination and eager loading
     */
    protected function indexApi(Request $request): JsonResponse
    {
        $query = NavigationMenu::query()
            ->with(['items.item', 'items.children.item']);

        // Pagination
        $perPage = $request->input('per_page', 25);
        $menus = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => NavigationMenuResource::collection($menus),
            'meta' => [
                'current_page' => $menus->currentPage(),
                'last_page' => $menus->lastPage(),
                'per_page' => $menus->perPage(),
                'total' => $menus->total(),
            ],
        ]);
    }

    /**
     * Store a newly created navigation menu.
     * Supports both traditional form submission and AJAX requests.
     */
    public function store(NavigationMenuRequest $request)
    {
        $validated = $request->validated();

        $menu = NavigationMenu::create($validated);

        // AJAX/API response
        if ($request->wantsJson() || $request->is('admin/api/*')) {
            return response()->json([
                'success' => true,
                'message' => 'Navigation menu created successfully',
                'data' => new NavigationMenuResource($menu->load(['items.item', 'items.children.item'])),
            ], 201);
        }

        // Traditional redirect response
        return redirect()->route('admin.navigation-menus.index')
            ->with('success', 'Navigation menu created successfully.');
    }

    /**
     * Display the specified navigation menu.
     * API endpoint for retrieving a single menu with hierarchical items.
     */
    public function show(NavigationMenu $navigationMenu): JsonResponse
    {
        $this->authorize('manage-navigation');

        $navigationMenu->load(['items.item', 'items.children.item']);

        return response()->json([
            'success' => true,
            'data' => new NavigationMenuResource($navigationMenu),
        ]);
    }

    /**
     * Update the specified navigation menu.
     * Supports both traditional form submission and AJAX requests.
     */
    public function update(NavigationMenuRequest $request, NavigationMenu $navigationMenu)
    {
        $validated = $request->validated();

        $navigationMenu->update($validated);

        // AJAX/API response
        if ($request->wantsJson() || $request->is('admin/api/*')) {
            return response()->json([
                'success' => true,
                'message' => 'Navigation menu updated successfully',
                'data' => new NavigationMenuResource($navigationMenu->fresh()->load(['items.item', 'items.children.item'])),
            ]);
        }

        // Traditional redirect response
        return redirect()->route('admin.navigation-menus.index')
            ->with('success', 'Navigation menu updated successfully.');
    }

    /**
     * Remove the specified navigation menu.
     * Supports both traditional form submission and AJAX requests.
     */
    public function destroy(Request $request, NavigationMenu $navigationMenu)
    {
        $this->authorize('manage-navigation');

        $menuTitle = $navigationMenu->title;
        $navigationMenu->delete();

        // AJAX/API response
        if ($request->wantsJson() || $request->is('admin/api/*')) {
            return response()->json([
                'success' => true,
                'message' => "Navigation menu '{$menuTitle}' deleted successfully",
            ]);
        }

        // Traditional redirect response
        return redirect()->route('admin.navigation-menus.index')
            ->with('success', 'Navigation menu deleted successfully.');
    }

    /**
     * Reorder menu items via AJAX (Drag & Drop).
     * Updates order and parent relationships for menu items.
     */
    public function reorder(Request $request, NavigationMenu $navigationMenu): JsonResponse
    {
        $this->authorize('manage-navigation');

        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|uuid|exists:navigation_menu_item_assignments,id',
            'items.*.order' => 'required|integer|min:0',
            'items.*.parent_id' => 'nullable|uuid|exists:navigation_menu_item_assignments,id',
        ]);

        // Update each item's order and parent relationship
        foreach ($validated['items'] as $item) {
            $navigationMenu->assignments()
                ->where('id', $item['id'])
                ->update([
                    'order' => $item['order'],
                    'parent_id' => $item['parent_id'] ?? null,
                ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Menu items reordered successfully',
        ]);
    }
}
