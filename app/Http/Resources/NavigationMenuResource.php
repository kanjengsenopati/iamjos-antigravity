<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class NavigationMenuResource extends JsonResource
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
            'area_name' => $this->area_name,
            'is_active' => $this->is_active,
            
            // Include menu items with hierarchical structure
            'items' => NavigationMenuItemResource::collection($this->whenLoaded('items')),
            
            // Timestamps
            'created_at' => $this->created_at?->toISOString(),
            'created_at_human' => $this->created_at?->diffForHumans(),
            'updated_at' => $this->updated_at?->toISOString(),
            'updated_at_human' => $this->updated_at?->diffForHumans(),
        ];
    }
}
