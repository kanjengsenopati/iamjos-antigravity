<?php

namespace Database\Factories;

use App\Models\NavigationMenuItem;
use App\Models\NavigationMenu;
use App\Models\SitePage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\NavigationMenuItem>
 */
class NavigationMenuItemFactory extends Factory
{
    protected $model = NavigationMenuItem::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'journal_id' => null, // Site-level by default
            'title' => fake()->words(2, true),
            'type' => NavigationMenuItem::TYPE_CUSTOM,
            'url' => fake()->url(),
            'route_name' => null,
            'path' => null,
            'content' => null,
            'related_id' => null,
            'icon' => null,
            'target' => '_self',
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the item is a custom link.
     */
    public function custom(?string $url = null): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => NavigationMenuItem::TYPE_CUSTOM,
            'url' => $url ?? fake()->url(),
            'route_name' => null,
            'related_id' => null,
        ]);
    }

    /**
     * Indicate that the item is a route link.
     */
    public function route(string $routeName = 'home'): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => NavigationMenuItem::TYPE_ROUTE,
            'route_name' => $routeName,
            'url' => null,
            'related_id' => null,
        ]);
    }

    /**
     * Indicate that the item is a page link.
     */
    public function page(?SitePage $page = null): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => NavigationMenuItem::TYPE_PAGE,
            'related_id' => $page?->id ?? SitePage::factory(),
            'url' => null,
            'route_name' => null,
        ]);
    }

    /**
     * Indicate that the item has an icon.
     */
    public function withIcon(string $icon = 'fa-home'): static
    {
        return $this->state(fn (array $attributes) => [
            'icon' => $icon,
        ]);
    }

    /**
     * Indicate that the item opens in a new window.
     */
    public function newWindow(): static
    {
        return $this->state(fn (array $attributes) => [
            'target' => '_blank',
        ]);
    }

    /**
     * Indicate that the item is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
