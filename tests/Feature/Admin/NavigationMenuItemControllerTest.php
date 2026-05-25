<?php

namespace Tests\Feature\Admin;

use App\Models\Journal;
use App\Models\NavigationMenu;
use App\Models\NavigationMenuItem;
use App\Models\NavigationMenuItemAssignment;
use App\Models\SitePage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class NavigationMenuItemControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Journal $journal;
    protected NavigationMenu $menu;

    protected function setUp(): void
    {
        parent::setUp();

        // Create the manage-navigation permission
        Permission::findOrCreate('manage-navigation', 'web');

        // Create a journal
        $this->journal = Journal::factory()->create();

        // Create a user with permission
        $this->user = User::factory()->create();
        $this->user->givePermissionTo('manage-navigation');

        // Create a test menu
        $this->menu = NavigationMenu::factory()->create([
            'journal_id' => $this->journal->id,
        ]);

        // Set current journal context
        session(['current_journal_id' => $this->journal->id]);
    }

    /** @test */
    public function it_can_create_a_menu_item()
    {
        $itemData = [
            'title' => 'Test Menu Item',
            'type' => NavigationMenuItem::TYPE_CUSTOM,
            'url' => 'https://example.com',
            'icon' => 'heroicon-home',
            'target' => '_self',
            'menu_id' => $this->menu->id,
            'is_active' => true,
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/admin/api/navigation-menu-items', $itemData);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Menu item created successfully',
            ]);

        $this->assertDatabaseHas('navigation_menu_items', [
            'title' => 'Test Menu Item',
            'type' => NavigationMenuItem::TYPE_CUSTOM,
            'url' => 'https://example.com',
        ]);

        // Verify assignment was created
        $menuItem = NavigationMenuItem::where('title', 'Test Menu Item')->first();
        $this->assertDatabaseHas('navigation_menu_item_assignments', [
            'menu_id' => $this->menu->id,
            'menu_item_id' => $menuItem->id,
            'parent_id' => null,
            'order' => 0,
        ]);
    }

    /** @test */
    public function it_can_create_a_nested_menu_item()
    {
        // Create a parent menu item
        $parentItem = NavigationMenuItem::factory()->create([
            'journal_id' => $this->journal->id,
        ]);
        $parentAssignment = NavigationMenuItemAssignment::create([
            'menu_id' => $this->menu->id,
            'menu_item_id' => $parentItem->id,
            'parent_id' => null,
            'order' => 0,
        ]);

        $itemData = [
            'title' => 'Child Menu Item',
            'type' => NavigationMenuItem::TYPE_CUSTOM,
            'url' => 'https://example.com/child',
            'target' => '_self',
            'menu_id' => $this->menu->id,
            'parent_id' => $parentAssignment->id,
            'is_active' => true,
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/admin/api/navigation-menu-items', $itemData);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Menu item created successfully',
            ]);

        // Verify nested assignment was created
        $childItem = NavigationMenuItem::where('title', 'Child Menu Item')->first();
        $this->assertDatabaseHas('navigation_menu_item_assignments', [
            'menu_id' => $this->menu->id,
            'menu_item_id' => $childItem->id,
            'parent_id' => $parentAssignment->id,
            'order' => 0,
        ]);
    }

    /** @test */
    public function it_can_create_a_page_type_menu_item()
    {
        $sitePage = SitePage::factory()->create();

        $itemData = [
            'title' => 'Page Link',
            'type' => NavigationMenuItem::TYPE_PAGE,
            'related_id' => $sitePage->id,
            'target' => '_self',
            'menu_id' => $this->menu->id,
            'is_active' => true,
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/admin/api/navigation-menu-items', $itemData);

        $response->assertStatus(201);

        $this->assertDatabaseHas('navigation_menu_items', [
            'title' => 'Page Link',
            'type' => NavigationMenuItem::TYPE_PAGE,
            'related_id' => $sitePage->id,
        ]);
    }

    /** @test */
    public function it_can_create_a_route_type_menu_item()
    {
        $itemData = [
            'title' => 'Home',
            'type' => NavigationMenuItem::TYPE_ROUTE,
            'route_name' => 'journal.public.home',
            'target' => '_self',
            'menu_id' => $this->menu->id,
            'is_active' => true,
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/admin/api/navigation-menu-items', $itemData);

        $response->assertStatus(201);

        $this->assertDatabaseHas('navigation_menu_items', [
            'title' => 'Home',
            'type' => NavigationMenuItem::TYPE_ROUTE,
            'route_name' => 'journal.public.home',
        ]);
    }

    /** @test */
    public function it_can_update_a_menu_item()
    {
        $menuItem = NavigationMenuItem::factory()->create([
            'journal_id' => $this->journal->id,
            'title' => 'Original Title',
        ]);
        NavigationMenuItemAssignment::create([
            'menu_id' => $this->menu->id,
            'menu_item_id' => $menuItem->id,
            'parent_id' => null,
            'order' => 0,
        ]);

        $updateData = [
            'title' => 'Updated Title',
            'type' => $menuItem->type,
            'url' => $menuItem->url,
            'target' => $menuItem->target,
            'menu_id' => $this->menu->id,
            'is_active' => true,
        ];

        $response = $this->actingAs($this->user)
            ->putJson("/admin/api/navigation-menu-items/{$menuItem->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Menu item updated successfully',
            ]);

        $this->assertDatabaseHas('navigation_menu_items', [
            'id' => $menuItem->id,
            'title' => 'Updated Title',
        ]);
    }

    /** @test */
    public function it_can_update_menu_item_parent()
    {
        // Create menu items
        $menuItem = NavigationMenuItem::factory()->create([
            'journal_id' => $this->journal->id,
        ]);
        $assignment = NavigationMenuItemAssignment::create([
            'menu_id' => $this->menu->id,
            'menu_item_id' => $menuItem->id,
            'parent_id' => null,
            'order' => 0,
        ]);

        $newParentItem = NavigationMenuItem::factory()->create([
            'journal_id' => $this->journal->id,
        ]);
        $newParentAssignment = NavigationMenuItemAssignment::create([
            'menu_id' => $this->menu->id,
            'menu_item_id' => $newParentItem->id,
            'parent_id' => null,
            'order' => 1,
        ]);

        $updateData = [
            'title' => $menuItem->title,
            'type' => $menuItem->type,
            'url' => $menuItem->url,
            'target' => $menuItem->target,
            'menu_id' => $this->menu->id,
            'parent_id' => $newParentAssignment->id,
            'is_active' => true,
        ];

        $response = $this->actingAs($this->user)
            ->putJson("/admin/api/navigation-menu-items/{$menuItem->id}", $updateData);

        $response->assertStatus(200);

        // Verify parent was updated
        $this->assertDatabaseHas('navigation_menu_item_assignments', [
            'id' => $assignment->id,
            'menu_item_id' => $menuItem->id,
            'parent_id' => $newParentAssignment->id,
        ]);
    }

    /** @test */
    public function it_can_delete_a_menu_item()
    {
        $menuItem = NavigationMenuItem::factory()->create([
            'journal_id' => $this->journal->id,
        ]);
        NavigationMenuItemAssignment::create([
            'menu_id' => $this->menu->id,
            'menu_item_id' => $menuItem->id,
            'parent_id' => null,
            'order' => 0,
        ]);

        $response = $this->actingAs($this->user)
            ->deleteJson("/admin/api/navigation-menu-items/{$menuItem->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseMissing('navigation_menu_items', [
            'id' => $menuItem->id,
        ]);

        // Verify assignment was also deleted
        $this->assertDatabaseMissing('navigation_menu_item_assignments', [
            'menu_item_id' => $menuItem->id,
        ]);
    }

    /** @test */
    public function it_requires_authentication()
    {
        $itemData = [
            'title' => 'Test',
            'type' => NavigationMenuItem::TYPE_CUSTOM,
            'url' => 'https://example.com',
            'target' => '_self',
            'menu_id' => $this->menu->id,
            'is_active' => true,
        ];

        $response = $this->postJson('/admin/api/navigation-menu-items', $itemData);
        $response->assertStatus(401);
    }

    /** @test */
    public function it_requires_permission_to_create_menu_item()
    {
        $userWithoutPermission = User::factory()->create();

        $itemData = [
            'title' => 'Test',
            'type' => NavigationMenuItem::TYPE_CUSTOM,
            'url' => 'https://example.com',
            'target' => '_self',
            'menu_id' => $this->menu->id,
            'is_active' => true,
        ];

        $response = $this->actingAs($userWithoutPermission)
            ->postJson('/admin/api/navigation-menu-items', $itemData);

        $response->assertStatus(403);
    }

    /** @test */
    public function it_validates_required_fields()
    {
        $response = $this->actingAs($this->user)
            ->postJson('/admin/api/navigation-menu-items', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title', 'type', 'target', 'menu_id', 'is_active']);
    }

    /** @test */
    public function it_validates_url_for_custom_type()
    {
        $itemData = [
            'title' => 'Test',
            'type' => NavigationMenuItem::TYPE_CUSTOM,
            'target' => '_self',
            'menu_id' => $this->menu->id,
            'is_active' => true,
            // Missing url for custom type
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/admin/api/navigation-menu-items', $itemData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['url']);
    }

    /** @test */
    public function it_validates_route_name_for_route_type()
    {
        $itemData = [
            'title' => 'Test',
            'type' => NavigationMenuItem::TYPE_ROUTE,
            'target' => '_self',
            'menu_id' => $this->menu->id,
            'is_active' => true,
            // Missing route_name for route type
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/admin/api/navigation-menu-items', $itemData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['route_name']);
    }

    /** @test */
    public function it_validates_related_id_for_page_type()
    {
        $itemData = [
            'title' => 'Test',
            'type' => NavigationMenuItem::TYPE_PAGE,
            'target' => '_self',
            'menu_id' => $this->menu->id,
            'is_active' => true,
            // Missing related_id for page type
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/admin/api/navigation-menu-items', $itemData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['related_id']);
    }

    /** @test */
    public function it_validates_url_format()
    {
        $itemData = [
            'title' => 'Test',
            'type' => NavigationMenuItem::TYPE_CUSTOM,
            'url' => 'not-a-valid-url',
            'target' => '_self',
            'menu_id' => $this->menu->id,
            'is_active' => true,
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/admin/api/navigation-menu-items', $itemData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['url']);
    }

    /** @test */
    public function it_validates_target_values()
    {
        $itemData = [
            'title' => 'Test',
            'type' => NavigationMenuItem::TYPE_CUSTOM,
            'url' => 'https://example.com',
            'target' => 'invalid_target',
            'menu_id' => $this->menu->id,
            'is_active' => true,
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/admin/api/navigation-menu-items', $itemData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['target']);
    }

    /** @test */
    public function it_calculates_correct_order_for_new_items()
    {
        // Create existing items
        $item1 = NavigationMenuItem::factory()->create(['journal_id' => $this->journal->id]);
        NavigationMenuItemAssignment::create([
            'menu_id' => $this->menu->id,
            'menu_item_id' => $item1->id,
            'parent_id' => null,
            'order' => 0,
        ]);

        $item2 = NavigationMenuItem::factory()->create(['journal_id' => $this->journal->id]);
        NavigationMenuItemAssignment::create([
            'menu_id' => $this->menu->id,
            'menu_item_id' => $item2->id,
            'parent_id' => null,
            'order' => 1,
        ]);

        // Create new item
        $itemData = [
            'title' => 'New Item',
            'type' => NavigationMenuItem::TYPE_CUSTOM,
            'url' => 'https://example.com',
            'target' => '_self',
            'menu_id' => $this->menu->id,
            'is_active' => true,
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/admin/api/navigation-menu-items', $itemData);

        $response->assertStatus(201);

        // Verify order is 2 (next after 0 and 1)
        $newItem = NavigationMenuItem::where('title', 'New Item')->first();
        $this->assertDatabaseHas('navigation_menu_item_assignments', [
            'menu_item_id' => $newItem->id,
            'order' => 2,
        ]);
    }
}
