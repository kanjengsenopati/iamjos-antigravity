<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class NavigationMenuItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'journal_id' => $this->journal_id,
            'title' => $this->title,
            'type' => $this->type,
            'url' => $this->url,
            'route_name' => $this->route_name,
            'path' => $this->path,
            'content' => $this->content,
            'related_id' => $this->related_id,
            'icon' => $this->icon,
            'target' => $this->target,
            'is_active' => $this->is_active,
            
            // Assignment information (if loaded through pivot)
            'assignment_id' => $this->whenPivotLoaded('navigation_menu_item_assignments', function () {
                return $this->pivot->id;
            }),
            'parent_id' => $this->whenPivotLoaded('navigation_menu_item_assignments', function () {
                return $this->pivot->parent_id;
            }),
            'order' => $this->whenPivotLoaded('navigation_menu_item_assignments', function () {
                return $this->pivot->order;
            }),
            
            // Nested children (if loaded)
            'children' => NavigationMenuItemResource::collection($this->whenLoaded('children')),
            
            // Related page information (if loaded)
            'related_page' => $this->whenLoaded('relatedPage', function () {
                return [
                    'id' => $this->relatedPage->id,
                    'title' => $this->relatedPage->title,
                    'slug' => $this->relatedPage->slug,
                ];
            }),
            
            // Timestamps
            'created_at' => $this->created_at?->toISOString(),
            'created_at_human' => $this->created_at?->diffForHumans(),
            'updated_at' => $this->updated_at?->toISOString(),
            'updated_at_human' => $this->updated_at?->diffForHumans(),
        ];
    }
}
