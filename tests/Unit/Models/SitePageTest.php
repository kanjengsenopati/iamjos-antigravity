<?php

namespace Tests\Unit\Models;

use App\Models\SitePage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SitePageTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_fillable_attributes()
    {
        $fillable = [
            'slug',
            'title',
            'content',
            'meta_description',
            'is_published',
            'sort_order',
            'created_by',
            'updated_by',
            'deleted_by',
        ];

        $sitePage = new SitePage();
        
        $this->assertEquals($fillable, $sitePage->getFillable());
    }

    /** @test */
    public function it_casts_attributes_correctly()
    {
        $sitePage = new SitePage();
        
        $this->assertEquals('boolean', $sitePage->getCasts()['is_published']);
        $this->assertEquals('integer', $sitePage->getCasts()['sort_order']);
    }

    /** @test */
    public function it_uses_soft_deletes()
    {
        $sitePage = new SitePage();
        
        $this->assertArrayHasKey('deleted_at', $sitePage->getCasts());
    }

    /** @test */
    public function it_has_creator_relationship()
    {
        $user = User::factory()->create();
        $sitePage = SitePage::factory()->create(['created_by' => $user->id]);

        $this->assertInstanceOf(User::class, $sitePage->creator);
        $this->assertEquals($user->id, $sitePage->creator->id);
    }

    /** @test */
    public function it_has_updater_relationship()
    {
        $user = User::factory()->create();
        $sitePage = SitePage::factory()->create(['updated_by' => $user->id]);

        $this->assertInstanceOf(User::class, $sitePage->updater);
        $this->assertEquals($user->id, $sitePage->updater->id);
    }

    /** @test */
    public function it_has_deleter_relationship()
    {
        $user = User::factory()->create();
        $sitePage = SitePage::factory()->create([
            'deleted_by' => $user->id,
            'deleted_at' => now(),
        ]);

        $this->assertInstanceOf(User::class, $sitePage->deleter);
        $this->assertEquals($user->id, $sitePage->deleter->id);
    }

    /** @test */
    public function it_scopes_published_pages()
    {
        SitePage::factory()->create(['is_published' => true]);
        SitePage::factory()->create(['is_published' => false]);

        $publishedPages = SitePage::published()->get();

        $this->assertCount(1, $publishedPages);
        $this->assertTrue($publishedPages->first()->is_published);
    }

    /** @test */
    public function it_scopes_ordered_pages()
    {
        SitePage::factory()->create(['sort_order' => 3]);
        SitePage::factory()->create(['sort_order' => 1]);
        SitePage::factory()->create(['sort_order' => 2]);

        $orderedPages = SitePage::ordered()->get();

        $this->assertEquals(1, $orderedPages->first()->sort_order);
        $this->assertEquals(2, $orderedPages->get(1)->sort_order);
        $this->assertEquals(3, $orderedPages->last()->sort_order);
    }

    /** @test */
    public function it_generates_unique_slug()
    {
        $sitePage1 = new SitePage();
        $sitePage1->slug = 'test-page';
        $sitePage1->title = 'Test Page';
        $sitePage1->save();

        $sitePage2 = new SitePage();
        $sitePage2->slug = 'test-page';
        $sitePage2->title = 'Test Page';
        $sitePage2->save();

        $this->assertEquals('test-page', $sitePage1->slug);
        $this->assertEquals('test-page-1', $sitePage2->slug);
    }

    /** @test */
    public function it_automatically_sets_created_by_and_updated_by_on_creation()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $sitePage = SitePage::create([
            'slug' => 'test-page',
            'title' => 'Test Page',
            'content' => 'Test content',
            'is_published' => true,
            'sort_order' => 1,
        ]);

        $this->assertEquals($user->id, $sitePage->created_by);
        $this->assertEquals($user->id, $sitePage->updated_by);
    }

    /** @test */
    public function it_automatically_sets_updated_by_on_update()
    {
        $creator = User::factory()->create();
        $updater = User::factory()->create();

        $this->actingAs($creator);
        $sitePage = SitePage::create([
            'slug' => 'test-page',
            'title' => 'Test Page',
            'content' => 'Test content',
            'is_published' => true,
            'sort_order' => 1,
        ]);

        $this->actingAs($updater);
        $sitePage->title = 'Updated Title';
        $sitePage->save();

        $this->assertEquals($creator->id, $sitePage->created_by);
        $this->assertEquals($updater->id, $sitePage->updated_by);
    }

    /** @test */
    public function it_automatically_sets_deleted_by_on_soft_delete()
    {
        $creator = User::factory()->create();
        $deleter = User::factory()->create();

        $this->actingAs($creator);
        $sitePage = SitePage::create([
            'slug' => 'test-page',
            'title' => 'Test Page',
            'content' => 'Test content',
            'is_published' => true,
            'sort_order' => 1,
        ]);

        $this->actingAs($deleter);
        $sitePage->delete();

        $sitePage->refresh();
        $this->assertEquals($deleter->id, $sitePage->deleted_by);
        $this->assertNotNull($sitePage->deleted_at);
    }

    /** @test */
    public function it_converts_slug_to_lowercase_with_hyphens()
    {
        $sitePage = new SitePage();
        $sitePage->slug = 'Test Page With Spaces';
        $sitePage->title = 'Test Page';
        $sitePage->save();

        $this->assertEquals('test-page-with-spaces', $sitePage->slug);
    }
}
