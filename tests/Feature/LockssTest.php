<?php namespace Tests\Feature;

use App\Models\Journal;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class LockssTest extends TestCase
{
    use RefreshDatabase;

    public function test_lockss_manifest_page_returns_404_if_disabled(): void
    {
        $journal = Journal::factory()->create([
            'enable_lockss' => false,
        ]);

        $response = $this->get(route('journal.lockss.manifest', $journal->slug));

        $response->assertStatus(404);
    }

    public function test_lockss_manifest_page_loads_if_enabled(): void
    {
        $journal = Journal::factory()->create([
            'enable_lockss' => true,
        ]);

        $response = $this->get(route('journal.lockss.manifest', $journal->slug));

        $response->assertStatus(200);
        $response->assertViewIs('public.lockss.manifest');
        $response->assertSee('LOCKSS Manifest Page');
    }

    public function test_clockss_manifest_page_returns_404_if_disabled(): void
    {
        $journal = Journal::factory()->create([
            'enable_clockss' => false,
        ]);

        $response = $this->get(route('journal.clockss.manifest', $journal->slug));

        $response->assertStatus(404);
    }

    public function test_clockss_manifest_page_loads_if_enabled(): void
    {
        $journal = Journal::factory()->create([
            'enable_clockss' => true,
        ]);

        $response = $this->get(route('journal.clockss.manifest', $journal->slug));

        $response->assertStatus(200);
        $response->assertViewIs('public.lockss.clockss_manifest');
        $response->assertSee('CLOCKSS Manifest Page');
    }
}
