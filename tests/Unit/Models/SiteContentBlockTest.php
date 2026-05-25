<?php

namespace Tests\Unit\Models;

use App\Models\SiteContentBlock;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class SiteContentBlockTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_fillable_attributes()
    {
        $fillable = [
            'key',
            'title',
            'description',
            'content',
            'config',
            'is_active',
            'sort_order',
            'icon',
            'category',
            'created_by',
            'updated_by',
            'deleted_by',
        ];

        $block = new SiteContentBlock();
        
        $this->assertEquals($fillable, $block->getFillable());
    }

    /** @test */
    public function it_casts_attributes_correctly()
    {
        $block = new SiteContentBlock();
        
        $this->assertEquals('array', $block->getCasts()['config']);
        $this->assertEquals('boolean', $block->getCasts()['is_active']);
        $this->assertEquals('integer', $block->getCasts()['sort_order']);
    }

    /** @test */
    public function it_uses_soft_deletes()
    {
        $block = new SiteContentBlock();
        
        $this->assertArrayHasKey('deleted_at', $block->getCasts());
    }

    /** @test */
    public function it_has_creator_relationship()
    {
        $user = User::factory()->create();
        $block = SiteContentBlock::factory()->create(['created_by' => $user->id]);

        $this->assertInstanceOf(User::class, $block->creator);
        $this->assertEquals($user->id, $block->creator->id);
    }

    /** @test */
    public function it_has_updater_relationship()
    {
        $user = User::factory()->create();
        $block = SiteContentBlock::factory()->create(['updated_by' => $user->id]);

        $this->assertInstanceOf(User::class, $block->updater);
        $this->assertEquals($user->id, $block->updater->id);
    }

    /** @test */
    public function it_has_deleter_relationship()
    {
        $user = User::factory()->create();
        $block = SiteContentBlock::factory()->create([
            'deleted_by' => $user->id,
            'deleted_at' => now(),
        ]);

        $this->assertInstanceOf(User::class, $block->deleter);
        $this->assertEquals($user->id, $block->deleter->id);
    }

    /** @test */
    public function it_scopes_active_blocks()
    {
        SiteContentBlock::factory()->create(['is_active' => true]);
        SiteContentBlock::factory()->create(['is_active' => false]);

        $activeBlocks = SiteContentBlock::active()->get();

        $this->assertCount(1, $activeBlocks);
        $this->assertTrue($activeBlocks->first()->is_active);
    }

    /** @test */
    public function it_scopes_ordered_blocks()
    {
        SiteContentBlock::factory()->create(['sort_order' => 3]);
        SiteContentBlock::factory()->create(['sort_order' => 1]);
        SiteContentBlock::factory()->create(['sort_order' => 2]);

        $orderedBlocks = SiteContentBlock::ordered()->get();

        $this->assertEquals(1, $orderedBlocks->first()->sort_order);
        $this->assertEquals(2, $orderedBlocks->get(1)->sort_order);
        $this->assertEquals(3, $orderedBlocks->last()->sort_order);
    }

    /** @test */
    public function it_automatically_sets_created_by_and_updated_by_on_creation()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $block = SiteContentBlock::create([
            'key' => 'test_block',
            'title' => 'Test Block',
            'description' => 'Test description',
            'content' => 'Test content',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $this->assertEquals($user->id, $block->created_by);
        $this->assertEquals($user->id, $block->updated_by);
    }

    /** @test */
    public function it_automatically_sets_updated_by_on_update()
    {
        $creator = User::factory()->create();
        $updater = User::factory()->create();

        $this->actingAs($creator);
        $block = SiteContentBlock::create([
            'key' => 'test_block',
            'title' => 'Test Block',
            'description' => 'Test description',
            'content' => 'Test content',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $this->actingAs($updater);
        $block->title = 'Updated Title';
        $block->save();

        $this->assertEquals($creator->id, $block->created_by);
        $this->assertEquals($updater->id, $block->updated_by);
    }

    /** @test */
    public function it_automatically_sets_deleted_by_on_soft_delete()
    {
        $creator = User::factory()->create();
        $deleter = User::factory()->create();

        $this->actingAs($creator);
        $block = SiteContentBlock::create([
            'key' => 'test_block',
            'title' => 'Test Block',
            'description' => 'Test description',
            'content' => 'Test content',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $this->actingAs($deleter);
        $block->delete();

        $block->refresh();
        $this->assertEquals($deleter->id, $block->deleted_by);
        $this->assertNotNull($block->deleted_at);
    }

    /** @test */
    public function it_clears_cache_on_save()
    {
        Cache::shouldReceive('forget')
            ->once()
            ->with(SiteContentBlock::CACHE_KEY);
        
        Cache::shouldReceive('forget')
            ->once()
            ->with('featured_journals');
        
        Cache::shouldReceive('forget')
            ->once()
            ->with('portal_stats');
        
        Cache::shouldReceive('forget')
            ->once()
            ->with('all_journals');
        
        Cache::shouldReceive('forget')
            ->once()
            ->with('latest_articles');

        $block = SiteContentBlock::factory()->create();
    }

    /** @test */
    public function it_gets_and_sets_config_values()
    {
        $block = SiteContentBlock::factory()->create([
            'config' => ['key1' => 'value1', 'nested' => ['key2' => 'value2']]
        ]);

        $this->assertEquals('value1', $block->getConfig('key1'));
        $this->assertEquals('value2', $block->getConfig('nested.key2'));
        $this->assertEquals('default', $block->getConfig('nonexistent', 'default'));

        $block->setConfig('key3', 'value3');
        $this->assertEquals('value3', $block->getConfig('key3'));
    }

    /** @test */
    public function it_generates_component_name_from_key()
    {
        $block = SiteContentBlock::factory()->create(['key' => 'hero_search']);

        $this->assertEquals('site.blocks.hero-search', $block->getComponentName());
    }
}
