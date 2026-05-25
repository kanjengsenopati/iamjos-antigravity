<?php

namespace Tests\Unit\Requests;

use App\Http\Requests\Admin\NavigationMenuItemRequest;
use App\Models\NavigationMenuItem;
use App\Models\NavigationMenu;
use App\Models\SitePage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class NavigationMenuItemRequestTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected NavigationMenu $menu;

    protected function setUp(): void
    {
        parent::setUp();

        // Create the manage-navigation permission
        Permission::findOrCreate('manage-navigation', 'web');

        // Create a user with manage-navigation permission
        $this->user = User::factory()->create();
        $this->user->givePermissionTo('manage-navigation');

        // Create a navigation menu
        $this->menu = NavigationMenu::factory()->create();
    }

    /** @test */
    public function it_validates_required_fields()
    {
        $request = new NavigationMenuItemRequest();
        $validator = Validator::make([], $request->rules());

        $this->assertFalse($validator->passes());
        $this->assertTrue($validator->errors()->has('title'));
        $this->assertTrue($validator->errors()->has('type'));
        $this->assertTrue($validator->errors()->has('target'));
        $this->assertTrue($validator->errors()->has('menu_id'));
        $this->assertTrue($validator->errors()->has('is_active'));
    }

    /** @test */
    public function it_validates_title_field()
    {
        $request = new NavigationMenuItemRequest();

        // Test max length
        $data = [
            'title' => str_repeat('a', 256),
            'type' => NavigationMenuItem::TYPE_CUSTOM,
            'url' => 'https://example.com',
            'target' => '_self',
            'menu_id' => $this->menu->id,
            'is_active' => true,
        ];
        $validator = Validator::make($data, $request->rules());
        $this->assertFalse($validator->passes());
        $this->assertTrue($validator->errors()->has('title'));

        // Test valid title
        $data['title'] = 'Valid Title';
        $validator = Validator::make($data, $request->rules());
        $this->assertTrue($validator->passes());
    }

    /** @test */
    public function it_validates_type_enum()
    {
        $request = new NavigationMenuItemRequest();

        // Test invalid type
        $data = [
            'title' => 'Test Item',
            'type' => 'invalid_type',
            'target' => '_self',
            'menu_id' => $this->menu->id,
            'is_active' => true,
        ];
        $validator = Validator::make($data, $request->rules());
        $this->assertFalse($validator->passes());
        $this->assertTrue($validator->errors()->has('type'));

        // Test valid types
        $validTypes = [
            NavigationMenuItem::TYPE_CUSTOM,
            NavigationMenuItem::TYPE_PAGE,
            NavigationMenuItem::TYPE_ROUTE,
        ];

        foreach ($validTypes as $type) {
            $data['type'] = $type;
            
            // Add required fields based on type
            if ($type === NavigationMenuItem::TYPE_CUSTOM) {
                $data['url'] = 'https://example.com';
                unset($data['route_name'], $data['related_id']);
            } elseif ($type === NavigationMenuItem::TYPE_ROUTE) {
                $data['route_name'] = 'home';
                unset($data['url'], $data['related_id']);
            } elseif ($type === NavigationMenuItem::TYPE_PAGE) {
                $page = SitePage::factory()->create();
                $data['related_id'] = $page->id;
                unset($data['url'], $data['route_name']);
            }

            $validator = Validator::make($data, $request->rules());
            $this->assertTrue($validator->passes(), "Type {$type} should be valid");
        }
    }

    /** @test */
    public function it_requires_url_for_custom_type()
    {
        $request = new NavigationMenuItemRequest();

        $data = [
            'title' => 'Test Item',
            'type' => NavigationMenuItem::TYPE_CUSTOM,
            'target' => '_self',
            'menu_id' => $this->menu->id,
            'is_active' => true,
        ];

        $validator = Validator::make($data, $request->rules());
        $this->assertFalse($validator->passes());
        $this->assertTrue($validator->errors()->has('url'));

        // Add valid URL
        $data['url'] = 'https://example.com';
        $validator = Validator::make($data, $request->rules());
        $this->assertTrue($validator->passes());
    }

    /** @test */
    public function it_validates_url_format()
    {
        $request = new NavigationMenuItemRequest();

        $data = [
            'title' => 'Test Item',
            'type' => NavigationMenuItem::TYPE_CUSTOM,
            'url' => 'not-a-valid-url',
            'target' => '_self',
            'menu_id' => $this->menu->id,
            'is_active' => true,
        ];

        $validator = Validator::make($data, $request->rules());
        $this->assertFalse($validator->passes());
        $this->assertTrue($validator->errors()->has('url'));

        // Test valid URLs
        $validUrls = [
            'https://example.com',
            'http://example.com',
            'https://example.com/path',
            'https://example.com/path?query=value',
        ];

        foreach ($validUrls as $url) {
            $data['url'] = $url;
            $validator = Validator::make($data, $request->rules());
            $this->assertTrue($validator->passes(), "URL {$url} should be valid");
        }
    }

    /** @test */
    public function it_validates_target_enum()
    {
        $request = new NavigationMenuItemRequest();

        // Test invalid target
        $data = [
            'title' => 'Test Item',
            'type' => NavigationMenuItem::TYPE_CUSTOM,
            'url' => 'https://example.com',
            'target' => '_invalid',
            'menu_id' => $this->menu->id,
            'is_active' => true,
        ];

        $validator = Validator::make($data, $request->rules());
        $this->assertFalse($validator->passes());
        $this->assertTrue($validator->errors()->has('target'));

        // Test valid targets
        foreach (['_self', '_blank'] as $target) {
            $data['target'] = $target;
            $validator = Validator::make($data, $request->rules());
            $this->assertTrue($validator->passes(), "Target {$target} should be valid");
        }
    }

    /** @test */
    public function it_requires_route_name_for_route_type()
    {
        $request = new NavigationMenuItemRequest();

        $data = [
            'title' => 'Test Item',
            'type' => NavigationMenuItem::TYPE_ROUTE,
            'target' => '_self',
            'menu_id' => $this->menu->id,
            'is_active' => true,
        ];

        $validator = Validator::make($data, $request->rules());
        $this->assertFalse($validator->passes());
        $this->assertTrue($validator->errors()->has('route_name'));

        // Add valid route name
        $data['route_name'] = 'home';
        $validator = Validator::make($data, $request->rules());
        $this->assertTrue($validator->passes());
    }

    /** @test */
    public function it_requires_related_id_for_page_type()
    {
        $request = new NavigationMenuItemRequest();

        $data = [
            'title' => 'Test Item',
            'type' => NavigationMenuItem::TYPE_PAGE,
            'target' => '_self',
            'menu_id' => $this->menu->id,
            'is_active' => true,
        ];

        $validator = Validator::make($data, $request->rules());
        $this->assertFalse($validator->passes());
        $this->assertTrue($validator->errors()->has('related_id'));

        // Add valid page
        $page = SitePage::factory()->create();
        $data['related_id'] = $page->id;
        $validator = Validator::make($data, $request->rules());
        $this->assertTrue($validator->passes());
    }

    /** @test */
    public function it_authorizes_users_with_manage_navigation_permission()
    {
        $request = new NavigationMenuItemRequest();
        $request->setUserResolver(fn() => $this->user);

        $this->assertTrue($request->authorize());
    }

    /** @test */
    public function it_denies_users_without_manage_navigation_permission()
    {
        $userWithoutPermission = User::factory()->create();

        $request = new NavigationMenuItemRequest();
        $request->setUserResolver(fn() => $userWithoutPermission);

        $this->assertFalse($request->authorize());
    }

    /** @test */
    public function it_has_custom_error_messages()
    {
        $request = new NavigationMenuItemRequest();
        $messages = $request->messages();

        $this->assertArrayHasKey('title.required', $messages);
        $this->assertArrayHasKey('type.in', $messages);
        $this->assertArrayHasKey('url.url', $messages);
        $this->assertArrayHasKey('target.in', $messages);
        $this->assertArrayHasKey('parent_id.exists', $messages);
    }
}
