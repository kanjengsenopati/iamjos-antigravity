<?php

namespace Tests\Unit\Requests;

use App\Http\Requests\Admin\SitePageRequest;
use App\Models\SitePage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class SitePageRequestTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create the manage-site-pages permission if it doesn't exist
        Permission::findOrCreate('manage-site-pages', 'web');
    }

    /** @test */
    public function it_authorizes_users_with_manage_site_pages_permission()
    {
        $user = User::factory()->create();
        $user->givePermissionTo('manage-site-pages');
        
        $request = new SitePageRequest();
        $request->setUserResolver(fn() => $user);

        $this->assertTrue($request->authorize());
    }

    /** @test */
    public function it_denies_users_without_manage_site_pages_permission()
    {
        $user = User::factory()->create();
        
        $request = new SitePageRequest();
        $request->setUserResolver(fn() => $user);

        $this->assertFalse($request->authorize());
    }

    /** @test */
    public function it_denies_guest_users()
    {
        $request = new SitePageRequest();
        $request->setUserResolver(fn() => null);

        $this->assertFalse($request->authorize());
    }

    /** @test */
    public function it_validates_required_fields()
    {
        $request = new SitePageRequest();
        $validator = Validator::make([], $request->rules());

        $this->assertFalse($validator->passes());
        $this->assertTrue($validator->errors()->has('title'));
        $this->assertTrue($validator->errors()->has('slug'));
        $this->assertTrue($validator->errors()->has('is_published'));
    }

    /** @test */
    public function it_validates_title_minimum_length()
    {
        $request = new SitePageRequest();
        $validator = Validator::make([
            'title' => 'ab',
            'slug' => 'test',
            'is_published' => true,
        ], $request->rules());

        $this->assertFalse($validator->passes());
        $this->assertTrue($validator->errors()->has('title'));
    }

    /** @test */
    public function it_validates_title_maximum_length()
    {
        $request = new SitePageRequest();
        $validator = Validator::make([
            'title' => str_repeat('a', 256),
            'slug' => 'test',
            'is_published' => true,
        ], $request->rules());

        $this->assertFalse($validator->passes());
        $this->assertTrue($validator->errors()->has('title'));
    }

    /** @test */
    public function it_validates_slug_format()
    {
        $request = new SitePageRequest();
        
        // Test invalid formats
        $invalidSlugs = [
            'Test Page',           // uppercase and spaces
            'test_page',           // underscores
            'test page',           // spaces
            'test--page',          // double hyphens
            '-test-page',          // leading hyphen
            'test-page-',          // trailing hyphen
            'test@page',           // special characters
        ];

        foreach ($invalidSlugs as $slug) {
            $validator = Validator::make([
                'title' => 'Test Page',
                'slug' => $slug,
                'is_published' => true,
            ], $request->rules());

            $this->assertFalse($validator->passes(), "Slug '{$slug}' should be invalid");
            $this->assertTrue($validator->errors()->has('slug'));
        }
    }

    /** @test */
    public function it_accepts_valid_slug_formats()
    {
        $request = new SitePageRequest();
        
        $validSlugs = [
            'test-page',
            'about-us',
            'contact',
            'page-123',
            'test-page-2',
        ];

        foreach ($validSlugs as $slug) {
            $validator = Validator::make([
                'title' => 'Test Page',
                'slug' => $slug,
                'is_published' => true,
            ], $request->rules());

            $this->assertTrue($validator->passes(), "Slug '{$slug}' should be valid");
        }
    }

    /** @test */
    public function it_validates_slug_uniqueness_on_create()
    {
        SitePage::factory()->create(['slug' => 'existing-page']);

        $request = new SitePageRequest();
        $validator = Validator::make([
            'title' => 'Test Page',
            'slug' => 'existing-page',
            'is_published' => true,
        ], $request->rules());

        $this->assertFalse($validator->passes());
        $this->assertTrue($validator->errors()->has('slug'));
    }

    /** @test */
    public function it_allows_same_slug_on_update()
    {
        $sitePage = SitePage::factory()->create(['slug' => 'existing-page']);

        $request = new SitePageRequest();
        $request->setMethod('PUT');
        $request->setRouteResolver(function () use ($sitePage) {
            return new class($sitePage) {
                public function __construct(private $sitePage) {}
                public function parameter($key) {
                    return $this->sitePage;
                }
            };
        });

        $validator = Validator::make([
            'title' => 'Updated Title',
            'slug' => 'existing-page',
            'is_published' => true,
        ], $request->rules());

        $this->assertTrue($validator->passes());
    }

    /** @test */
    public function it_validates_meta_description_maximum_length()
    {
        $request = new SitePageRequest();
        $validator = Validator::make([
            'title' => 'Test Page',
            'slug' => 'test-page',
            'meta_description' => str_repeat('a', 161),
            'is_published' => true,
        ], $request->rules());

        $this->assertFalse($validator->passes());
        $this->assertTrue($validator->errors()->has('meta_description'));
    }

    /** @test */
    public function it_accepts_valid_meta_description()
    {
        $request = new SitePageRequest();
        $validator = Validator::make([
            'title' => 'Test Page',
            'slug' => 'test-page',
            'meta_description' => str_repeat('a', 160),
            'is_published' => true,
        ], $request->rules());

        $this->assertTrue($validator->passes());
    }

    /** @test */
    public function it_validates_is_published_as_boolean()
    {
        $request = new SitePageRequest();
        
        // Test invalid values
        $validator = Validator::make([
            'title' => 'Test Page',
            'slug' => 'test-page',
            'is_published' => 'yes',
        ], $request->rules());

        $this->assertFalse($validator->passes());
        $this->assertTrue($validator->errors()->has('is_published'));
    }

    /** @test */
    public function it_accepts_valid_boolean_values_for_is_published()
    {
        $request = new SitePageRequest();
        
        $validValues = [true, false, 1, 0, '1', '0'];

        foreach ($validValues as $value) {
            $validator = Validator::make([
                'title' => 'Test Page',
                'slug' => 'test-page',
                'is_published' => $value,
            ], $request->rules());

            $this->assertTrue($validator->passes(), "Value '{$value}' should be valid for is_published");
        }
    }

    /** @test */
    public function it_validates_sort_order_as_integer()
    {
        $request = new SitePageRequest();
        $validator = Validator::make([
            'title' => 'Test Page',
            'slug' => 'test-page',
            'is_published' => true,
            'sort_order' => 'not-a-number',
        ], $request->rules());

        $this->assertFalse($validator->passes());
        $this->assertTrue($validator->errors()->has('sort_order'));
    }

    /** @test */
    public function it_validates_sort_order_minimum_value()
    {
        $request = new SitePageRequest();
        $validator = Validator::make([
            'title' => 'Test Page',
            'slug' => 'test-page',
            'is_published' => true,
            'sort_order' => -1,
        ], $request->rules());

        $this->assertFalse($validator->passes());
        $this->assertTrue($validator->errors()->has('sort_order'));
    }

    /** @test */
    public function it_accepts_valid_sort_order()
    {
        $request = new SitePageRequest();
        $validator = Validator::make([
            'title' => 'Test Page',
            'slug' => 'test-page',
            'is_published' => true,
            'sort_order' => 10,
        ], $request->rules());

        $this->assertTrue($validator->passes());
    }

    /** @test */
    public function it_allows_nullable_fields()
    {
        $request = new SitePageRequest();
        $validator = Validator::make([
            'title' => 'Test Page',
            'slug' => 'test-page',
            'is_published' => true,
            // content, meta_description, and sort_order are nullable
        ], $request->rules());

        $this->assertTrue($validator->passes());
    }

    /** @test */
    public function it_has_custom_error_messages()
    {
        $request = new SitePageRequest();
        $messages = $request->messages();

        $this->assertArrayHasKey('title.required', $messages);
        $this->assertArrayHasKey('slug.unique', $messages);
        $this->assertArrayHasKey('slug.regex', $messages);
        $this->assertArrayHasKey('meta_description.max', $messages);
        $this->assertArrayHasKey('is_published.required', $messages);
    }

    /** @test */
    public function it_has_custom_attributes()
    {
        $request = new SitePageRequest();
        $attributes = $request->attributes();

        $this->assertArrayHasKey('title', $attributes);
        $this->assertArrayHasKey('slug', $attributes);
        $this->assertArrayHasKey('content', $attributes);
        $this->assertArrayHasKey('meta_description', $attributes);
        $this->assertArrayHasKey('is_published', $attributes);
        $this->assertArrayHasKey('sort_order', $attributes);
    }

    /** @test */
    public function it_auto_generates_slug_from_title_when_slug_is_not_provided()
    {
        $request = new SitePageRequest();
        $request->merge([
            'title' => 'Test Page Title',
        ]);
        
        // Manually call prepareForValidation since it's protected
        $reflection = new \ReflectionClass($request);
        $method = $reflection->getMethod('prepareForValidation');
        $method->setAccessible(true);
        $method->invoke($request);

        $this->assertEquals('test-page-title', $request->input('slug'));
    }

    /** @test */
    public function it_does_not_override_provided_slug()
    {
        $request = new SitePageRequest();
        $request->merge([
            'title' => 'Test Page Title',
            'slug' => 'custom-slug',
        ]);
        
        // Manually call prepareForValidation
        $reflection = new \ReflectionClass($request);
        $method = $reflection->getMethod('prepareForValidation');
        $method->setAccessible(true);
        $method->invoke($request);

        $this->assertEquals('custom-slug', $request->input('slug'));
    }

    /** @test */
    public function it_passes_validation_with_all_valid_data()
    {
        $request = new SitePageRequest();
        $validator = Validator::make([
            'title' => 'Test Page',
            'slug' => 'test-page',
            'content' => '<p>This is test content</p>',
            'meta_description' => 'This is a test page for validation',
            'is_published' => true,
            'sort_order' => 1,
        ], $request->rules());

        $this->assertTrue($validator->passes());
    }
}
