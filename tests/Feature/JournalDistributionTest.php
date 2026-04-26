<?php namespace Tests\Feature;

use App\Models\Journal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class JournalDistributionTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_journal_page_shows_index_follow_robots_by_default(): void
    {
        $journal = Journal::factory()->create([
            'block_search_indexing' => false,
        ]);

        $response = $this->get(route('journal.public.home', $journal->slug));

        $response->assertStatus(200);
        $response->assertSee('<meta name="robots" content="index, follow">', false);
        $response->assertDontSee('<meta name="robots" content="noindex, nofollow">', false);
    }

    public function test_public_journal_page_shows_noindex_nofollow_if_search_indexing_blocked(): void
    {
        $journal = Journal::factory()->create([
            'block_search_indexing' => true,
        ]);

        $response = $this->get(route('journal.public.home', $journal->slug));

        $response->assertStatus(200);
        $response->assertSee('<meta name="robots" content="noindex, nofollow">', false);
        $response->assertDontSee('<meta name="robots" content="index, follow">', false);
    }

    public function test_public_journal_page_renders_custom_meta_tags(): void
    {
        $journal = Journal::factory()->create([
            'custom_meta_tags' => '<meta name="custom-tag" content="test-value">',
        ]);

        $response = $this->get(route('journal.public.home', $journal->slug));

        $response->assertStatus(200);
        $response->assertSee('<meta name="custom-tag" content="test-value">', false);
    }
}
