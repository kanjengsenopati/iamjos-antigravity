<?php

namespace Tests\Unit\Requests;

use App\Http\Requests\Admin\ContentBlockRequest;
use App\Models\SiteContentBlock;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class ContentBlockRequestTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create the manage-content-blocks permission if it doesn't exist
        Permission::findOrCreate('manage-content-blocks', 'web');
    }

    /** @test */
    public function it_authorizes_users_with_manage_content_blocks_permission()
    {
        $user = User::factory()->create();
        $user->givePermissionTo('manage-content-blocks');
        
        $request = new ContentBlockRequest();
        $request->setUserResolver(fn() => $user);

        $this->assertTrue($request->authorize());
    }

    /** @test */
    public function it_denies_users_without_manage_content_blocks_permission()
    {
        $user = User::factory()->create();
        
        $request = new ContentBlockRequest();
        $request->setUserResolver(fn() => $user);

        $this->assertFalse($request->authorize());
    }

    /** @test */
    public function it_validates_required_fields()
    {
        $request = new ContentBlockRequest();
        $validator = Validator::make([], $request->rules());

        $this->assertFalse($validator->passes());
        $this->assertTrue($validator->errors()->has('title'));
        $this->assertTrue($validator->errors()->has('identifier'));
        $this->assertTrue($validator->errors()->has('category'));
        $this->assertTrue($validator->errors()->has('is_active'));
    }

    /** @test */
    public function it_validates_identifier_format()
    {
        $request = new ContentBlockRequest();
        
        // Test invalid formats
        $invalidIdentifiers = [
            'Test Block',          // uppercase and spaces
            'test-block',          // hyphens
            'test block',          // spaces
            'test__block',         // double underscores
            'Test_Block',          // uppercase
            'test@block',          // special characters
        ];

        foreach ($invalidIdentifiers as $identifier) {
            $validator = Validator::make([
                'title' => 'Test Block',
                'identifier' => $identifier,
                'category' => 'content',
                'is_active' => true,
            ], $request->rules());

            $this->assertFalse($validator->passes(), "Identifier '{$identifier}' should be invalid");
            $this->assertTrue($validator->errors()->has('identifier'));
        }
    }

    /** @test */
    public function it_accepts_valid_identifier_formats()
    {
        $request = new ContentBlockRequest();
        
        $validIdentifiers = [
            'test_block',
            'hero_search',
            'block_123',
            'test_block_2',
            'content',
        ];

        foreach ($validIdentifiers as $identifier) {
            $validator = Validator::make([
                'title' => 'Test Block',
                'identifier' => $identifier,
                'category' => 'content',
                'is_active' => true,
            ], $request->rules());

            $this->assertTrue($validator->passes(), "Identifier '{$identifier}' should be valid");
        }
    }

    /** @test */
    public function it_validates_identifier_uniqueness_on_create()
    {
        SiteContentBlock::factory()->create(['key' => 'existing_block']);

        $request = new ContentBlockRequest();
        $validator = Validator::make([
            'title' => 'Test Block',
            'identifier' => 'existing_block',
            'category' => 'content',
            'is_active' => true,
        ], $request->rules());

        $this->assertFalse($validator->passes());
        $this->assertTrue($validator->errors()->has('identifier'));
    }

    /** @test */
    public function it_validates_category_values()
    {
        $request = new ContentBlockRequest();
        
        // Test invalid category
        $validator = Validator::make([
            'title' => 'Test Block',
            'identifier' => 'test_block',
            'category' => 'invalid_category',
            'is_active' => true,
        ], $request->rules());

        $this->assertFalse($validator->passes());
        $this->assertTrue($validator->errors()->has('category'));
    }

    /** @test */
    public function it_accepts_valid_category_values()
    {
        $request = new ContentBlockRequest();
        
        $validCategories = ['content', 'hero', 'feature', 'stats', 'cta', 'footer'];

        foreach ($validCategories as $category) {
            $validator = Validator::make([
                'title' => 'Test Block',
                'identifier' => 'test_block',
                'category' => $category,
                'is_active' => true,
            ], $request->rules());

            $this->assertTrue($validator->passes(), "Category '{$category}' should be valid");
        }
    }

    /** @test */
    public function it_validates_is_active_as_boolean()
    {
        $request = new ContentBlockRequest();
        
        // Test invalid values
        $validator = Validator::make([
            'title' => 'Test Block',
            'identifier' => 'test_block',
            'category' => 'content',
            'is_active' => 'yes',
        ], $request->rules());

        $this->assertFalse($validator->passes());
        $this->assertTrue($validator->errors()->has('is_active'));
    }

    /** @test */
    public function it_validates_sort_order_as_integer()
    {
        $request = new ContentBlockRequest();
        $validator = Validator::make([
            'title' => 'Test Block',
            'identifier' => 'test_block',
            'category' => 'content',
            'is_active' => true,
            'sort_order' => 'not-a-number',
        ], $request->rules());

        $this->assertFalse($validator->passes());
        $this->assertTrue($validator->errors()->has('sort_order'));
    }

    /** @test */
    public function it_validates_sort_order_minimum_value()
    {
        $request = new ContentBlockRequest();
        $validator = Validator::make([
            'title' => 'Test Block',
            'identifier' => 'test_block',
            'category' => 'content',
            'is_active' => true,
            'sort_order' => -1,
        ], $request->rules());

        $this->assertFalse($validator->passes());
        $this->assertTrue($validator->errors()->has('sort_order'));
    }

    /** @test */
    public function it_allows_nullable_fields()
    {
        $request = new ContentBlockRequest();
        $validator = Validator::make([
            'title' => 'Test Block',
            'identifier' => 'test_block',
            'category' => 'content',
            'is_active' => true,
            // description, content, sort_order, icon, and config are nullable
        ], $request->rules());

        $this->assertTrue($validator->passes());
    }

    /** @test */
    public function it_validates_config_as_array()
    {
        $request = new ContentBlockRequest();
        $validator = Validator::make([
            'title' => 'Test Block',
            'identifier' => 'test_block',
            'category' => 'content',
            'is_active' => true,
            'config' => 'not-an-array',
        ], $request->rules());

        $this->assertFalse($validator->passes());
        $this->assertTrue($validator->errors()->has('config'));
    }

    /** @test */
    public function it_accepts_valid_config_array()
    {
        $request = new ContentBlockRequest();
        $validator = Validator::make([
            'title' => 'Test Block',
            'identifier' => 'test_block',
            'category' => 'content',
            'is_active' => true,
            'config' => ['key' => 'value', 'nested' => ['data' => 'test']],
        ], $request->rules());

        $this->assertTrue($validator->passes());
    }

    /** @test */
    public function it_has_custom_error_messages()
    {
        $request = new ContentBlockRequest();
        $messages = $request->messages();

        $this->assertArrayHasKey('title.required', $messages);
        $this->assertArrayHasKey('identifier.required', $messages);
        $this->assertArrayHasKey('identifier.unique', $messages);
        $this->assertArrayHasKey('identifier.regex', $messages);
        $this->assertArrayHasKey('category.required', $messages);
        $this->assertArrayHasKey('category.in', $messages);
        $this->assertArrayHasKey('is_active.required', $messages);
    }

    /** @test */
    public function it_has_custom_attributes()
    {
        $request = new ContentBlockRequest();
        $attributes = $request->attributes();

        $this->assertArrayHasKey('title', $attributes);
        $this->assertArrayHasKey('identifier', $attributes);
        $this->assertArrayHasKey('description', $attributes);
        $this->assertArrayHasKey('content', $attributes);
        $this->assertArrayHasKey('category', $attributes);
        $this->assertArrayHasKey('is_active', $attributes);
        $this->assertArrayHasKey('sort_order', $attributes);
        $this->assertArrayHasKey('icon', $attributes);
        $this->assertArrayHasKey('config', $attributes);
    }

    /** @test */
    public function it_maps_identifier_to_key_in_prepare_for_validation()
    {
        $request = new ContentBlockRequest();
        $request->merge([
            'identifier' => 'test_block',
        ]);
        
        // Manually call prepareForValidation since it's protected
        $reflection = new \ReflectionClass($request);
        $method = $reflection->getMethod('prepareForValidation');
        $method->setAccessible(true);
        $method->invoke($request);

        $this->assertEquals('test_block', $request->input('key'));
    }

    /** @test */
    public function it_maps_identifier_to_key_in_validated_data()
    {
        $request = new ContentBlockRequest();
        $request->merge([
            'title' => 'Test Block',
            'identifier' => 'test_block',
            'category' => 'content',
            'is_active' => true,
        ]);
        
        // Manually call prepareForValidation
        $reflection = new \ReflectionClass($request);
        $method = $reflection->getMethod('prepareForValidation');
        $method->setAccessible(true);
        $method->invoke($request);

        $validator = Validator::make($request->all(), $request->rules());
        $this->assertTrue($validator->passes());

        // Get validated data through the request's validated method
        $validated = $request->validated();
        
        $this->assertArrayHasKey('key', $validated);
        $this->assertArrayNotHasKey('identifier', $validated);
        $this->assertEquals('test_block', $validated['key']);
    }

    /** @test */
    public function it_passes_validation_with_all_valid_data()
    {
        $request = new ContentBlockRequest();
        $validator = Validator::make([
            'title' => 'Test Block',
            'identifier' => 'test_block',
            'description' => 'This is a test block',
            'content' => '<p>This is test content</p>',
            'category' => 'content',
            'is_active' => true,
            'sort_order' => 1,
            'icon' => 'fa-cube',
            'config' => ['key' => 'value'],
        ], $request->rules());

        $this->assertTrue($validator->passes());
    }
}

