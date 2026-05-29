<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\NavigationMenuItemRequest;
use App\Http\Resources\NavigationMenuItemResource;
use App\Models\NavigationMenu;
use App\Models\NavigationMenuItem;
use App\Models\NavigationMenuItemAssignment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * NavigationMenuItemController
 * 
 * Manages navigation menu items with CRUD operations.
 * Handles menu item creation, updates, deletion, and assignment management.
 * Supports nested menu structures through parent-child relationships.
 */
class NavigationMenuItemController extends Controller
{
    /**
     * Store a newly created menu item under a specific menu.
     * Creates both the menu item and its assignment to the menu.
     * 
     * @param NavigationMenuItemRequest $request
     * @return JsonResponse
     */
    public function store(NavigationMenuItemRequest $request): JsonResponse
    {
        $this->authorize('manage-navigation');

        $validated = $request->validated();
        
        // Extract menu_id and parent_id for assignment
        $menuId = $validated['menu_id'];
        $parentId = $validated['parent_id'] ?? null;
        
        // Remove assignment-specific fields from menu item data
        unset($validated['menu_id'], $validated['parent_id']);
        
        // Set journal_id from current context
        $journal = current_journal();
        if ($journal) {
            $validated['journal_id'] = $journal->id;
        }

        // Create the menu item
        $menuItem = NavigationMenuItem::create($validated);

        // Calculate the next order position
        $maxOrder = NavigationMenuItemAssignment::where('menu_id', $menuId)
            ->when($parentId, function ($query) use ($parentId) {
                return $query->where('parent_id', $parentId);
            }, function ($query) {
                return $query->whereNull('parent_id');
            })
            ->max('order') ?? -1;

        // Create the assignment
        $assignment = NavigationMenuItemAssignment::create([
            'menu_id' => $menuId,
            'menu_item_id' => $menuItem->id,
            'parent_id' => $parentId,
            'order' => $maxOrder + 1,
        ]);

        // Load relationships for response
        $menuItem->load(['assignments' => function ($query) use ($menuId) {
            $query->where('menu_id', $menuId);
        }]);

        return response()->json([
            'success' => true,
            'message' => 'Menu item created successfully',
            'data' => new NavigationMenuItemResource($menuItem),
        ], 201);
    }

    /**
     * Update the specified menu item.
     * Handles changes to menu item properties and parent assignment.
     * 
     * @param NavigationMenuItemRequest $request
     * @param NavigationMenuItem $navigationMenuItem
     * @return JsonResponse
     */
    public function update(NavigationMenuItemRequest $request, NavigationMenuItem $navigationMenuItem): JsonResponse
    {
        $this->authorize('manage-navigation');

        $validated = $request->validated();
        
        // Extract assignment-specific fields
        $menuId = $validated['menu_id'];
        $newParentId = $validated['parent_id'] ?? null;
        
        // Remove assignment-specific fields from menu item data
        unset($validated['menu_id'], $validated['parent_id']);

        // Update the menu item
        $navigationMenuItem->update($validated);

        // Update the assignment if parent changed
        $assignment = NavigationMenuItemAssignment::where('menu_item_id', $navigationMenuItem->id)
            ->where('menu_id', $menuId)
            ->first();

        if ($assignment) {
            // Check if parent changed
            if ($assignment->parent_id !== $newParentId) {
                // Calculate new order position in the new parent context
                $maxOrder = NavigationMenuItemAssignment::where('menu_id', $menuId)
                    ->when($newParentId, function ($query) use ($newParentId) {
                        return $query->where('parent_id', $newParentId);
                    }, function ($query) {
                        return $query->whereNull('parent_id');
                    })
                    ->where('id', '!=', $assignment->id)
                    ->max('order') ?? -1;

                $assignment->update([
                    'parent_id' => $newParentId,
                    'order' => $maxOrder + 1,
                ]);
            }
        }

        // Load relationships for response
        $navigationMenuItem->load(['assignments' => function ($query) use ($menuId) {
            $query->where('menu_id', $menuId);
        }]);

        return response()->json([
            'success' => true,
            'message' => 'Menu item updated successfully',
            'data' => new NavigationMenuItemResource($navigationMenuItem->fresh()),
        ]);
    }

    /**
     * Remove the specified menu item.
     * Deletes both the menu item and its assignments.
     * 
     * @param Request $request
     * @param NavigationMenuItem $navigationMenuItem
     * @return JsonResponse
     */
    public function destroy(Request $request, NavigationMenuItem $navigationMenuItem): JsonResponse
    {
        $this->authorize('manage-navigation');

        $itemTitle = $navigationMenuItem->title;
        
        // Delete all assignments first
        $navigationMenuItem->assignments->each(function ($assignment) {
            $assignment->delete();
        });
        
        // Delete the menu item
        $navigationMenuItem->delete();

        return response()->json([
            'success' => true,
            'message' => "Menu item '{$itemTitle}' deleted successfully",
        ]);
    }
}
