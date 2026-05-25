<?php

namespace Tests\Feature\Api;

use App\Http\Resources\ContentBlockResource;
use App\Http\Resources\NavigationMenuItemResource;
use App\Http\Resources\NavigationMenuResource;
use App\Http\Resources\SitePageResource;
use App\Models\NavigationMenu;
use App\Models\NavigationMenuItem;
use App\Models\NavigationMenuItemAssignment;
use App\Models\SiteContentBlock;
use App\Models\SitePage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResourcesTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /** @test */
    public function site_page_resource_includes_all_required_fields()
    {
        $page = SitePage::factory()->create([
            'title' => 'Test Page',
            'slug' => 'test-page',
            'content' => '<p>Test content</p>',
            'meta_description' => 'Test meta description',
            'is_published' => true,
            'sort_order' => 1,
            'created_by' => $this->user->id,
            'updated_by' => $this->user->id,
        ]);

        $page->load(['creator', 'updater']);

        $resource = new SitePageResource($page);
        $array = $resource->toArray(request());

        // Check all required fields
        $this->assertArrayHasKey('id', $array);
        $this->assertArrayHasKey('title', $array);
        $this->assertArrayHasKey('slug', $array);
        $this->assertArrayHasKey('content', $array);
        $this->assertArrayHasKey('meta_description', $array);
        $this->assertArrayHasKey('is_published', $array);
        $this->assertArrayHasKey('sort_order', $array);

        // Check audit trail
        $this->assertArrayHasKey('created_by', $array);
        $this->assertArrayHasKey('updated_by', $array);
        $this->assertIsArray($array['created_by']);
        $this->assertArrayHasKey('id', $array['created_by']);
        $this->assertArrayHasKey('name', $array['created_by']);

        // Check human-readable timestamps
        $this->assertArrayHasKey('created_at_human', $array);
        $this->assertArrayHasKey('updated_at_human', $array);
        $this->assertNotNull($array['created_at_human']);
        $this->assertNotNull($array['updated_at_human']);
    }

    /** @test */
    public function content_block_resource_includes_all_required_fields()
    {
        $block = SiteContentBlock::factory()->create([
            'key' => 'test-block',
            'title' => 'Test Block',
            'description' => 'Test description',
            'content' => '<p>Test content</p>',
            'config' => ['key' => 'value'],
            'is_active' => true,
            'sort_order' => 1,
            'icon' => 'fa-cube',
            'category' => 'content',
            'created_by' => $this->user->id,
            'updated_by' => $this->user->id,
        ]);

        $block->load(['creator', 'updater']);

        $resource = new ContentBlockResource($block);
        $array = $resource->toArray(request());

        // Check all required fields
        $this->assertArrayHasKey('id', $array);
        $this->assertArrayHasKey('key', $array);
        $this->assertArrayHasKey('title', $array);
        $this->assertArrayHasKey('description', $array);
        $this->assertArrayHasKey('content', $array);
        $this->assertArrayHasKey('config', $array);
        $this->assertArrayHasKey('is_active', $array);
        $this->assertArrayHasKey('sort_order', $array);
        $this->assertArrayHasKey('icon', $array);
        $this->assertArrayHasKey('category', $array);

        // Check audit trail
        $this->assertArrayHasKey('created_by', $array);
        $this->assertArrayHasKey('updated_by', $array);
        $this->assertIsArray($array['created_by']);
        $this->assertArrayHasKey('id', $array['created_by']);
        $this->assertArrayHasKey('name', $array['created_by']);

        // Check human-readable timestamps
        $this->assertArrayHasKey('created_at_human', $array);
        $this->assertArrayHasKey('updated_at_human', $array);
    }

    /** @test */
    public function navigation_menu_resource_includes_hierarchical_structure()
    {
        $menu = NavigationMenu::factory()->create([
            'title' => 'Test Menu',
            'area_name' => 'primary',
            'is_active' => true,
        ]);

        // Create menu items
        $parentItem = NavigationMenuItem::factory()->create([
            'title' => 'Parent Item',
            'type' => 'custom',
            'url' => '/parent',
        ]);

        $childItem = NavigationMenuItem::factory()->create([
            'title' => 'Child Item',
            'type' => 'custom',
            'url' => '/child',
        ]);

        // Create assignments with hierarchy
        $parentAssignment = NavigationMenuItemAssignment::create([
            'menu_id' => $menu->id,
            'menu_item_id' => $parentItem->id,
            'parent_id' => null,
            'order' => 0,
        ]);

        NavigationMenuItemAssignment::create([
            'menu_id' => $menu->id,
            'menu_item_id' => $childItem->id,
            'parent_id' => $parentAssignment->id,
            'order' => 0,
        ]);

        $menu->load('items.item');

        $resource = new NavigationMenuResource($menu);
        $array = $resource->toArray(request());

        // Check menu fields
        $this->assertArrayHasKey('id', $array);
        $this->assertArrayHasKey('title', $array);
        $this->assertArrayHasKey('area_name', $array);
        $this->assertArrayHasKey('is_active', $array);

        // Check items are included
        $this->assertArrayHasKey('items', $array);
        $this->assertIsArray($array['items']);

        // Check timestamps
        $this->assertArrayHasKey('created_at_human', $array);
        $this->assertArrayHasKey('updated_at_human', $array);
    }

    /** @test */
    public function navigation_menu_item_resource_includes_parent_child_relationships()
    {
        $item = NavigationMenuItem::factory()->create([
            'title' => 'Test Item',
            'type' => 'custom',
            'url' => '/test',
            'icon' => 'fa-home',
            'target' => '_self',
            'is_active' => true,
        ]);

        $resource = new NavigationMenuItemResource($item);
        $array = $resource->toArray(request());

        // Check all required fields
        $this->assertArrayHasKey('id', $array);
        $this->assertArrayHasKey('title', $array);
        $this->assertArrayHasKey('type', $array);
        $this->assertArrayHasKey('url', $array);
        $this->assertArrayHasKey('icon', $array);
        $this->assertArrayHasKey('target', $array);
        $this->assertArrayHasKey('is_active', $array);

        // Check timestamps
        $this->assertArrayHasKey('created_at_human', $array);
        $this->assertArrayHasKey('updated_at_human', $array);
    }
}
