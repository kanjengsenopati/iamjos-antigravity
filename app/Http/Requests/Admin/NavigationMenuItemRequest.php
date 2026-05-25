<?php

namespace App\Http\Requests\Admin;

use App\Models\NavigationMenuItem;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class NavigationMenuItemRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Check if user has permission to manage navigation
        return $this->user() && $this->user()->can('manage-navigation');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $routeNavigationItem = $this->route('navigation_menu_item') ?? $this->route('navigationMenuItem') ?? $this->route('item');
        $navigationItemId = is_object($routeNavigationItem) ? $routeNavigationItem->getKey() : $routeNavigationItem;
        $isUpdate = in_array($this->method(), ['PUT', 'PATCH']);

        return [
            'title' => [
                'required',
                'string',
                'max:255',
            ],
            'type' => [
                'required',
                'string',
                Rule::in([
                    NavigationMenuItem::TYPE_CUSTOM,
                    NavigationMenuItem::TYPE_PAGE,
                    NavigationMenuItem::TYPE_ROUTE,
                ]),
            ],
            'url' => [
                'nullable',
                'string',
                'url',
                'max:500',
                // URL is required for custom type
                Rule::requiredIf(function () {
                    return $this->input('type') === NavigationMenuItem::TYPE_CUSTOM;
                }),
            ],
            'route_name' => [
                'nullable',
                'string',
                'max:255',
                // Route name is required for route type
                Rule::requiredIf(function () {
                    return $this->input('type') === NavigationMenuItem::TYPE_ROUTE;
                }),
            ],
            'related_id' => [
                'nullable',
                'uuid',
                'exists:site_pages,id',
                // Related ID is required for page type
                Rule::requiredIf(function () {
                    return $this->input('type') === NavigationMenuItem::TYPE_PAGE;
                }),
            ],
            'icon' => [
                'nullable',
                'string',
                'max:100',
            ],
            'target' => [
                'required',
                'string',
                Rule::in(['_self', '_blank']),
            ],
            'parent_id' => [
                'nullable',
                'uuid',
                'exists:navigation_menu_item_assignments,id',
                // Custom validation to prevent circular references
                function ($attribute, $value, $fail) use ($navigationItemId, $isUpdate) {
                    if ($value && $isUpdate && $navigationItemId) {
                        // Check if parent_id would create a circular reference
                        if ($this->wouldCreateCircularReference($navigationItemId, $value)) {
                            $fail('The selected parent would create a circular reference. A menu item cannot be its own ancestor.');
                        }
                    }
                },
            ],
            'menu_id' => [
                'required',
                'uuid',
                'exists:navigation_menus,id',
            ],
            'is_active' => [
                'required',
                'boolean',
            ],
        ];
    }

    /**
     * Get custom error messages for validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Menu item title is required',
            'title.max' => 'Menu item title cannot exceed 255 characters',
            
            'type.required' => 'Menu item type is required',
            'type.in' => 'Menu item type must be one of: custom, page, or route',
            
            'url.url' => 'Please enter a valid URL (e.g., https://example.com)',
            'url.max' => 'URL cannot exceed 500 characters',
            'url.required' => 'URL is required for custom link type',
            
            'route_name.max' => 'Route name cannot exceed 255 characters',
            'route_name.required' => 'Route name is required for route type',
            
            'related_id.uuid' => 'Invalid page reference',
            'related_id.exists' => 'The selected page does not exist',
            'related_id.required' => 'Page selection is required for page type',
            
            'icon.max' => 'Icon name cannot exceed 100 characters',
            
            'target.required' => 'Link target is required',
            'target.in' => 'Link target must be either "_self" (same window) or "_blank" (new window)',
            
            'parent_id.uuid' => 'Invalid parent menu item reference',
            'parent_id.exists' => 'The selected parent menu item does not exist',
            
            'menu_id.required' => 'Menu selection is required',
            'menu_id.uuid' => 'Invalid menu reference',
            'menu_id.exists' => 'The selected menu does not exist',
            
            'is_active.required' => 'Active status is required',
            'is_active.boolean' => 'Active status must be true or false',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'title' => 'menu item title',
            'type' => 'menu item type',
            'url' => 'URL',
            'route_name' => 'route name',
            'related_id' => 'page',
            'icon' => 'icon',
            'target' => 'link target',
            'parent_id' => 'parent menu item',
            'menu_id' => 'menu',
            'is_active' => 'active status',
        ];
    }

    /**
     * Check if setting the parent_id would create a circular reference.
     * 
     * A circular reference occurs when:
     * - An item is set as its own parent
     * - An item is set as a child of one of its descendants
     *
     * @param string $itemId The ID of the item being updated
     * @param string $parentId The proposed parent assignment ID
     * @return bool True if circular reference would be created
     */
    protected function wouldCreateCircularReference(string $itemId, string $parentId): bool
    {
        // Get the assignment being used as parent
        $parentAssignment = \App\Models\NavigationMenuItemAssignment::find($parentId);
        
        if (!$parentAssignment) {
            return false;
        }

        // Check if the parent assignment's menu_item_id is the same as the item being updated
        if ($parentAssignment->menu_item_id === $itemId) {
            return true;
        }

        // Check if the proposed parent is a descendant of this item
        return $this->isDescendant($itemId, $parentId);
    }

    /**
     * Check if a potential parent assignment is a descendant of the given item.
     * 
     * This recursively checks the parent chain to detect circular references.
     *
     * @param string $itemId The item ID to check
     * @param string $potentialParentAssignmentId The potential parent assignment ID
     * @return bool True if potentialParent is a descendant of item
     */
    protected function isDescendant(string $itemId, string $potentialParentAssignmentId): bool
    {
        $potentialParentAssignment = \App\Models\NavigationMenuItemAssignment::find($potentialParentAssignmentId);
        
        if (!$potentialParentAssignment) {
            return false;
        }

        // If the potential parent assignment has no parent, it's not a descendant
        if (!$potentialParentAssignment->parent_id) {
            return false;
        }

        // Get the parent assignment
        $parentAssignment = \App\Models\NavigationMenuItemAssignment::find($potentialParentAssignment->parent_id);
        
        if (!$parentAssignment) {
            return false;
        }

        // If the parent assignment's menu_item_id is the item, it's a descendant
        if ($parentAssignment->menu_item_id === $itemId) {
            return true;
        }

        // Recursively check up the parent chain
        return $this->isDescendant($itemId, $potentialParentAssignment->parent_id);
    }
}
