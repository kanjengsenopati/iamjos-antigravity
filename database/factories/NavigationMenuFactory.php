<?php

namespace Database\Factories;

use App\Models\Journal;
use App\Models\NavigationMenu;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\NavigationMenu>
 */
class NavigationMenuFactory extends Factory
{
    protected $model = NavigationMenu::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'journal_id' => null, // Site-level menu by default
            'title' => fake()->words(3, true) . ' Menu',
            'area_name' => fake()->randomElement([
                NavigationMenu::AREA_PRIMARY,
                NavigationMenu::AREA_USER,
            ]),
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the menu belongs to a journal.
     */
    public function forJournal(?Journal $journal = null): static
    {
        return $this->state(fn (array $attributes) => [
            'journal_id' => $journal?->id ?? Journal::factory(),
        ]);
    }

    /**
     * Indicate that the menu is for the primary area.
     */
    public function primary(): static
    {
        return $this->state(fn (array $attributes) => [
            'area_name' => NavigationMenu::AREA_PRIMARY,
            'title' => 'Primary Navigation',
        ]);
    }

    /**
     * Indicate that the menu is for the user area.
     */
    public function user(): static
    {
        return $this->state(fn (array $attributes) => [
            'area_name' => NavigationMenu::AREA_USER,
            'title' => 'User Navigation',
        ]);
    }

    /**
     * Indicate that the menu is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
