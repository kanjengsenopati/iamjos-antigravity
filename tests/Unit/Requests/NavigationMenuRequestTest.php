<?php

namespace Tests\Unit\Requests;

use App\Http\Requests\Admin\NavigationMenuRequest;
use App\Models\Journal;
use App\Models\NavigationMenu;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class NavigationMenuRequestTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Journal $journal;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->journal = Journal::factory()->create();
    }

    /** @test */
    public function it_authorizes_users_with_manage_navigation_permission()
    {
        Gate::define('manage-navigation', fn($user) => true);
        
        $request = NavigationMenuRequest::create('/admin/navigation-menus', 'POST');
        $request->setUserResolver(fn() => $this->user);
        
        $this->assertTrue($request->authorize());
    }

    /** @test */
    public function it_denies_users_without_manage_navigation_permission()
    {
        Gate::define('manage-navigation', fn($user) => false);
        
        $request = NavigationMenuRequest::create('/admin/navigation-menus', 'POST');
        $request->setUserResolver(fn() => $this->user);
        
        $this->assertFalse($request->authorize());
    }

    /** @test */
    public function it_denies_guest_users()
    {
        $request = NavigationMenuRequest::create('/admin/navigation-menus', 'POST');
        $request->setUserResolver(fn() => null);
        
        $this->assertFalse($request->authorize());
    }

    /** @test */
    public function it_requires_title_field()
    {
        $request = $this->createRequest([
            'area_name' => 'primary',
            'is_active' => true,
        ]);
        
        $validator = validator($request->all(), $request->rules());
        
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('title', $validator->errors()->toArray());
    }

    /** @test */
    public function it_requires_area_name_field()
    {
        $request = $this->createRequest([
            'title' => 'Main Menu',
            'is_active' => true,
        ]);
        
        $validator = validator($request->all(), $request->rules());
        
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('area_name', $validator->errors()->toArray());
    }

    /** @test */
    public function it_requires_is_active_field()
    {
        $request = $this->createRequest([
            'title' => 'Main Menu',
            'area_name' => 'primary',
        ]);
        
        $validator = validator($request->all(), $request->rules());
        
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('is_active', $validator->errors()->toArray());
    }

    /** @test */
    public function it_validates_title_max_length()
    {
        $request = $this->createRequest([
            'title' => str_repeat('a', 256),
            'area_name' => 'primary',
            'is_active' => true,
        ]);
        
        $validator = validator($request->all(), $request->rules());
        
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('title', $validator->errors()->toArray());
    }

    /** @test */
    public function it_validates_area_name_max_length()
    {
        $request = $this->createRequest([
            'title' => 'Main Menu',
            'area_name' => str_repeat('a', 256),
            'is_active' => true,
        ]);
        
        $validator = validator($request->all(), $request->rules());
        
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('area_name', $validator->errors()->toArray());
    }

    /** @test */
    public function it_validates_is_active_is_boolean()
    {
        $request = $this->createRequest([
            'title' => 'Main Menu',
            'area_name' => 'primary',
            'is_active' => 'not-a-boolean',
        ]);
        
        $validator = validator($request->all(), $request->rules());
        
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('is_active', $validator->errors()->toArray());
    }

    /** @test */
    public function it_validates_area_name_is_unique_per_journal_on_create()
    {
        NavigationMenu::factory()->create([
            'journal_id' => $this->journal->id,
            'area_name' => 'primary',
        ]);
        
        $request = $this->createRequest([
            'title' => 'Another Menu',
            'area_name' => 'primary',
            'journal_id' => $this->journal->id,
            'is_active' => true,
        ]);
        
        $validator = validator($request->all(), $request->rules());
        
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('area_name', $validator->errors()->toArray());
    }

    /** @test */
    public function it_allows_same_area_name_for_different_journals()
    {
        $journal2 = Journal::factory()->create();
        
        NavigationMenu::factory()->create([
            'journal_id' => $this->journal->id,
            'area_name' => 'primary',
        ]);
        
        $request = $this->createRequest([
            'title' => 'Another Menu',
            'area_name' => 'primary',
            'journal_id' => $journal2->id,
            'is_active' => true,
        ]);
        
        $validator = validator($request->all(), $request->rules());
        
        $this->assertFalse($validator->fails());
    }

    /** @test */
    public function it_ignores_current_menu_on_update()
    {
        $menu = NavigationMenu::factory()->create([
            'journal_id' => $this->journal->id,
            'area_name' => 'primary',
            'title' => 'Main Menu',
        ]);
        
        $request = $this->createRequest([
            'title' => 'Updated Main Menu',
            'area_name' => 'primary',
            'journal_id' => $this->journal->id,
            'is_active' => true,
        ], 'PUT', $menu);
        
        $validator = validator($request->all(), $request->rules());
        
        $this->assertFalse($validator->fails());
    }

    /** @test */
    public function it_validates_journal_id_exists()
    {
        $request = $this->createRequest([
            'title' => 'Main Menu',
            'area_name' => 'primary',
            'journal_id' => '00000000-0000-0000-0000-000000000000',
            'is_active' => true,
        ]);
        
        $validator = validator($request->all(), $request->rules());
        
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('journal_id', $validator->errors()->toArray());
    }

    /** @test */
    public function it_validates_journal_id_is_uuid()
    {
        $request = $this->createRequest([
            'title' => 'Main Menu',
            'area_name' => 'primary',
            'journal_id' => 'not-a-uuid',
            'is_active' => true,
        ]);
        
        $validator = validator($request->all(), $request->rules());
        
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('journal_id', $validator->errors()->toArray());
    }

    /** @test */
    public function it_allows_null_journal_id()
    {
        $request = $this->createRequest([
            'title' => 'Main Menu',
            'area_name' => 'primary',
            'journal_id' => null,
            'is_active' => true,
        ]);
        
        $validator = validator($request->all(), $request->rules());
        
        $this->assertFalse($validator->fails());
    }

    /** @test */
    public function it_passes_validation_with_valid_data()
    {
        $request = $this->createRequest([
            'title' => 'Main Menu',
            'area_name' => 'primary',
            'journal_id' => $this->journal->id,
            'is_active' => true,
        ]);
        
        $validator = validator($request->all(), $request->rules());
        
        $this->assertFalse($validator->fails());
    }

    /** @test */
    public function it_has_custom_error_messages()
    {
        $request = new NavigationMenuRequest();
        $messages = $request->messages();
        
        $this->assertArrayHasKey('title.required', $messages);
        $this->assertArrayHasKey('title.max', $messages);
        $this->assertArrayHasKey('area_name.required', $messages);
        $this->assertArrayHasKey('area_name.max', $messages);
        $this->assertArrayHasKey('area_name.unique', $messages);
        $this->assertArrayHasKey('is_active.required', $messages);
        $this->assertArrayHasKey('is_active.boolean', $messages);
        $this->assertArrayHasKey('journal_id.uuid', $messages);
        $this->assertArrayHasKey('journal_id.exists', $messages);
    }

    /** @test */
    public function it_has_custom_attributes()
    {
        $request = new NavigationMenuRequest();
        $attributes = $request->attributes();
        
        $this->assertArrayHasKey('title', $attributes);
        $this->assertArrayHasKey('area_name', $attributes);
        $this->assertArrayHasKey('journal_id', $attributes);
        $this->assertArrayHasKey('is_active', $attributes);
    }

    /**
     * Create a request instance with given data
     */
    protected function createRequest(array $data, string $method = 'POST', ?NavigationMenu $menu = null): NavigationMenuRequest
    {
        $uri = $menu 
            ? "/admin/navigation-menus/{$menu->id}" 
            : '/admin/navigation-menus';
            
        $request = NavigationMenuRequest::create($uri, $method, $data);
        $request->setUserResolver(fn() => $this->user);
        
        if ($menu) {
            $request->setRouteResolver(function () use ($request, $menu) {
                $route = new \Illuminate\Routing\Route($request->method(), $request->path(), []);
                $route->bind($request);
                $route->setParameter('navigation_menu', $menu);
                $route->setParameter('navigationMenu', $menu);
                $route->setParameter('menu', $menu);
                return $route;
            });
        }
        
        return $request;
    }
}
