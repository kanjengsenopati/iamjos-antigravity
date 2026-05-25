<?php

namespace Tests\Feature\Admin;

use App\Models\SitePage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class SitePageControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Create the manage-site-pages permission
        Permission::findOrCreate('manage-site-pages', 'web');

        // Create a user with permission
        $this->user = User::factory()->create();
        $this->user->givePermissionTo('manage-site-pages');
    }

    /** @test */
    public function it_can_list_pages_with_pagination()
    {
        // Create test pages
        SitePage::factory()->count(30)->create();

        $response = $this->actingAs($this->user)
            ->getJson('/admin/site-pages');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'id',
                        'title',
                        'slug',
                        'content',
                        'is_published',
                        'sort_order',
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
    public function it_can_search_pages()
    {
        SitePage::factory()->create(['title' => 'About Us']);
        SitePage::factory()->create(['title' => 'Contact']);
        SitePage::factory()->create(['title' => 'Privacy Policy']);

        $response = $this->actingAs($this->user)
            ->getJson('/admin/site-pages?search=about');

        $response->assertStatus(200);
        $this->assertEquals(1, count($response->json('data')));
        $this->assertEquals('About Us', $response->json('data.0.title'));
    }

    /** @test */
    public function it_can_filter_pages_by_status()
    {
        SitePage::factory()->create(['is_published' => true]);
        SitePage::factory()->create(['is_published' => true]);
        SitePage::factory()->create(['is_published' => false]);

        $response = $this->actingAs($this->user)
            ->getJson('/admin/site-pages?status=published');

        $response->assertStatus(200);
        $this->assertEquals(2, count($response->json('data')));
    }

    /** @test */
    public function it_can_create_a_page()
    {
        $pageData = [
            'title' => 'Test Page',
            'slug' => 'test-page',
            'content' => '<p>Test content</p>',
            'meta_description' => 'Test meta description',
            'is_published' => true,
            'sort_order' => 1,
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/admin/site-pages', $pageData);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Page created successfully',
            ]);

        $this->assertDatabaseHas('site_pages', [
            'title' => 'Test Page',
            'slug' => 'test-page',
        ]);
    }

    /** @test */
    public function it_can_show_a_single_page()
    {
        $page = SitePage::factory()->create();

        $response = $this->actingAs($this->user)
            ->getJson("/admin/site-pages/{$page->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $page->id,
                    'title' => $page->title,
                    'slug' => $page->slug,
                ],
            ]);
    }

    /** @test */
    public function it_can_update_a_page()
    {
        $page = SitePage::factory()->create([
            'title' => 'Original Title',
        ]);

        $updateData = [
            'title' => 'Updated Title',
            'slug' => $page->slug,
            'content' => $page->content,
            'is_published' => $page->is_published,
        ];

        $response = $this->actingAs($this->user)
            ->putJson("/admin/site-pages/{$page->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Page updated successfully',
            ]);

        $this->assertDatabaseHas('site_pages', [
            'id' => $page->id,
            'title' => 'Updated Title',
        ]);
    }

    /** @test */
    public function it_can_delete_a_page()
    {
        $page = SitePage::factory()->create();

        $response = $this->actingAs($this->user)
            ->deleteJson("/admin/site-pages/{$page->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertSoftDeleted('site_pages', [
            'id' => $page->id,
        ]);
    }

    /** @test */
    public function it_can_duplicate_a_page()
    {
        $page = SitePage::factory()->create([
            'title' => 'Original Page',
            'slug' => 'original-page',
            'is_published' => true,
        ]);

        $response = $this->actingAs($this->user)
            ->postJson("/admin/site-pages/{$page->id}/duplicate");

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Page duplicated successfully',
            ]);

        $this->assertDatabaseHas('site_pages', [
            'title' => 'Original Page (Copy)',
            'is_published' => false, // Should be draft
        ]);
    }

    /** @test */
    public function it_can_toggle_page_status()
    {
        $page = SitePage::factory()->create(['is_published' => false]);

        $response = $this->actingAs($this->user)
            ->postJson("/admin/site-pages/{$page->id}/toggle");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'is_published' => true,
            ]);

        $this->assertDatabaseHas('site_pages', [
            'id' => $page->id,
            'is_published' => true,
        ]);
    }

    /** @test */
    public function it_can_reorder_pages()
    {
        $page1 = SitePage::factory()->create(['sort_order' => 0]);
        $page2 = SitePage::factory()->create(['sort_order' => 1]);
        $page3 = SitePage::factory()->create(['sort_order' => 2]);

        $reorderData = [
            'items' => [
                ['id' => $page3->id, 'order' => 0],
                ['id' => $page1->id, 'order' => 1],
                ['id' => $page2->id, 'order' => 2],
            ],
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/admin/site-pages/reorder', $reorderData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Page order updated successfully',
            ]);

        $this->assertDatabaseHas('site_pages', [
            'id' => $page3->id,
            'sort_order' => 0,
        ]);
    }

    /** @test */
    public function it_can_bulk_delete_pages()
    {
        $page1 = SitePage::factory()->create();
        $page2 = SitePage::factory()->create();
        $page3 = SitePage::factory()->create();

        $deleteData = [
            'ids' => [$page1->id, $page2->id],
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/admin/site-pages/bulk-delete', $deleteData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'count' => 2,
            ]);

        $this->assertSoftDeleted('site_pages', ['id' => $page1->id]);
        $this->assertSoftDeleted('site_pages', ['id' => $page2->id]);
        $this->assertDatabaseHas('site_pages', ['id' => $page3->id, 'deleted_at' => null]);
    }

    /** @test */
    public function it_requires_authentication()
    {
        $response = $this->getJson('/admin/site-pages');
        $response->assertStatus(401);
    }

    /** @test */
    public function it_requires_permission_to_create_page()
    {
        $userWithoutPermission = User::factory()->create();

        $pageData = [
            'title' => 'Test Page',
            'slug' => 'test-page',
            'is_published' => true,
        ];

        $response = $this->actingAs($userWithoutPermission)
            ->postJson('/admin/site-pages', $pageData);

        $response->assertStatus(403);
    }

    /** @test */
    public function it_validates_required_fields()
    {
        $response = $this->actingAs($this->user)
            ->postJson('/admin/site-pages', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title', 'is_published']);
    }

    /** @test */
    public function it_validates_slug_uniqueness()
    {
        SitePage::factory()->create(['slug' => 'existing-slug']);

        $pageData = [
            'title' => 'New Page',
            'slug' => 'existing-slug',
            'is_published' => true,
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/admin/site-pages', $pageData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['slug']);
    }

    /** @test */
    public function it_auto_generates_slug_from_title()
    {
        $pageData = [
            'title' => 'About Us Page',
            'is_published' => true,
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/admin/site-pages', $pageData);

        $response->assertStatus(201);

        $this->assertDatabaseHas('site_pages', [
            'title' => 'About Us Page',
            'slug' => 'about-us-page',
        ]);
    }

    /** @test */
    public function it_validates_meta_description_length()
    {
        $pageData = [
            'title' => 'Test Page',
            'slug' => 'test-page',
            'meta_description' => str_repeat('a', 161), // Exceeds 160 char limit
            'is_published' => true,
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/admin/site-pages', $pageData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['meta_description']);
    }
}
