<?php

namespace Tests\Feature\Admin;

use App\Models\NavigationMenu;
use App\Models\NavigationMenuItem;
use App\Models\NavigationMenuItemAssignment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class NavigationMenuControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Create the manage-navigation permission
        Permission::findOrCreate('manage-navigation', 'web');

        // Create a user with permission
        $this->user = User::factory()->create();
        $this->user->givePermissionTo('manage-navigation');
    }

    /** @test */
    public function it_can_list_menus_with_pagination()
    {
        // Create test menus
        NavigationMenu::factory()->count(30)->create();

        $response = $this->actingAs($this->user)
            ->getJson('/admin/api/navigation-menus');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'id',
                        'title',
                        'area_name',
                        'is_active',
                    ],
                ],
                'meta' => [
                    'current_page',
                    'last_page',
                    'per_page',
                    'total',
                ],
            ]);

        $this->assertEquals(25, count($response->json('data')));
    }

    /** @test */
    public function it_can_create_a_menu()
    {
        $menuData = [
            'title' => 'Primary Menu',
            'area_name' => 'primary',
            'is_active' => true,
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/admin/api/navigation-menus', $menuData);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Navigation menu created successfully',
            ]);

        $this->assertDatabaseHas('navigation_menus', [
            'title' => 'Primary Menu',
            'area_name' => 'primary',
        ]);
    }

    /** @test */
    public function it_can_show_a_single_menu()
    {
        $menu = NavigationMenu::factory()->create();

        $response = $this->actingAs($this->user)
            ->getJson("/admin/api/navigation-menus/{$menu->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $menu->id,
                    'title' => $menu->title,
                    'area_name' => $menu->area_name,
                ],
            ]);
    }

    /** @test */
    public function it_can_update_a_menu()
    {
        $menu = NavigationMenu::factory()->create([
            'title' => 'Original Title',
        ]);

        $updateData = [
            'title' => 'Updated Title',
            'area_name' => $menu->area_name,
            'is_active' => $menu->is_active,
        ];

        $response = $this->actingAs($this->user)
            ->putJson("/admin/api/navigation-menus/{$menu->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Navigation menu updated successfully',
            ]);

        $this->assertDatabaseHas('navigation_menus', [
            'id' => $menu->id,
            'title' => 'Updated Title',
        ]);
    }

    /** @test */
    public function it_can_delete_a_menu()
    {
        $menu = NavigationMenu::factory()->create();

        $response = $this->actingAs($this->user)
            ->deleteJson("/admin/api/navigation-menus/{$menu->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseMissing('navigation_menus', [
            'id' => $menu->id,
        ]);
    }

    /** @test */
    public function it_can_reorder_menu_items()
    {
        $menu = NavigationMenu::factory()->create();
        
        // Create menu items
        $item1 = NavigationMenuItem::factory()->create();
        $item2 = NavigationMenuItem::factory()->create();
        $item3 = NavigationMenuItem::factory()->create();

        // Create assignments
        $assignment1 = NavigationMenuItemAssignment::create([
            'menu_id' => $menu->id,
            'menu_item_id' => $item1->id,
            'order' => 0,
        ]);

        $assignment2 = NavigationMenuItemAssignment::create([
            'menu_id' => $menu->id,
            'menu_item_id' => $item2->id,
            'order' => 1,
        ]);

        $assignment3 = NavigationMenuItemAssignment::create([
            'menu_id' => $menu->id,
            'menu_item_id' => $item3->id,
            'order' => 2,
        ]);

        $reorderData = [
            'items' => [
                ['id' => $assignment3->id, 'order' => 0, 'parent_id' => null],
                ['id' => $assignment1->id, 'order' => 1, 'parent_id' => null],
                ['id' => $assignment2->id, 'order' => 2, 'parent_id' => null],
            ],
        ];

        $response = $this->actingAs($this->user)
            ->postJson("/admin/api/navigation-menus/{$menu->id}/reorder", $reorderData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Menu items reordered successfully',
            ]);

        $this->assertDatabaseHas('navigation_menu_item_assignments', [
            'id' => $assignment3->id,
            'order' => 0,
        ]);
    }

    /** @test */
    public function it_can_reorder_with_parent_relationships()
    {
        $menu = NavigationMenu::factory()->create();
        
        // Create menu items
        $item1 = NavigationMenuItem::factory()->create();
        $item2 = NavigationMenuItem::factory()->create();

        // Create assignments
        $assignment1 = NavigationMenuItemAssignment::create([
            'menu_id' => $menu->id,
            'menu_item_id' => $item1->id,
            'order' => 0,
        ]);

        $assignment2 = NavigationMenuItemAssignment::create([
            'menu_id' => $menu->id,
            'menu_item_id' => $item2->id,
            'order' => 1,
        ]);

        // Make item2 a child of item1
        $reorderData = [
            'items' => [
                ['id' => $assignment1->id, 'order' => 0, 'parent_id' => null],
                ['id' => $assignment2->id, 'order' => 0, 'parent_id' => $assignment1->id],
            ],
        ];

        $response = $this->actingAs($this->user)
            ->postJson("/admin/api/navigation-menus/{$menu->id}/reorder", $reorderData);

        $response->assertStatus(200);

        $this->assertDatabaseHas('navigation_menu_item_assignments', [
            'id' => $assignment2->id,
            'parent_id' => $assignment1->id,
        ]);
    }

    /** @test */
    public function it_requires_authentication()
    {
        $response = $this->getJson('/admin/api/navigation-menus');
        $response->assertStatus(401);
    }

    /** @test */
    public function it_requires_permission_to_create_menu()
    {
        $userWithoutPermission = User::factory()->create();

        $menuData = [
            'title' => 'Test Menu',
            'area_name' => 'primary',
            'is_active' => true,
        ];

        $response = $this->actingAs($userWithoutPermission)
            ->postJson('/admin/api/navigation-menus', $menuData);

        $response->assertStatus(403);
    }

    /** @test */
    public function it_validates_required_fields()
    {
        $response = $this->actingAs($this->user)
            ->postJson('/admin/api/navigation-menus', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title', 'area_name', 'is_active']);
    }

    /** @test */
    public function it_validates_unique_area_name_per_journal()
    {
        NavigationMenu::factory()->create([
            'area_name' => 'primary',
            'journal_id' => null,
        ]);

        $menuData = [
            'title' => 'Another Primary Menu',
            'area_name' => 'primary',
            'journal_id' => null,
            'is_active' => true,
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/admin/api/navigation-menus', $menuData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['area_name']);
    }

    /** @test */
    public function it_loads_hierarchical_items_structure()
    {
        $menu = NavigationMenu::factory()->create();
        
        // Create parent and child items
        $parentItem = NavigationMenuItem::factory()->create(['title' => 'Parent']);
        $childItem = NavigationMenuItem::factory()->create(['title' => 'Child']);

        $parentAssignment = NavigationMenuItemAssignment::create([
            'menu_id' => $menu->id,
            'menu_item_id' => $parentItem->id,
            'order' => 0,
        ]);

        NavigationMenuItemAssignment::create([
            'menu_id' => $menu->id,
            'menu_item_id' => $childItem->id,
            'parent_id' => $parentAssignment->id,
            'order' => 0,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson("/admin/api/navigation-menus/{$menu->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'title',
                    'items',
                ],
            ]);
    }
}

