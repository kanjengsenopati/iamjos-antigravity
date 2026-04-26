<?php namespace Tests\Feature;

use App\Models\Journal;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OaiTest extends TestCase
{
    use RefreshDatabase;

    public function test_oai_endpoint_returns_error_if_disabled(): void
    {
        $journal = Journal::factory()->create([
            'enable_oai' => false,
        ]);

        $response = $this->get(route('journal.oai', $journal->slug) . '?verb=Identify');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/xml; charset=UTF-8');
        $response->assertSee('<error code="noRecordsMatch">OAI-PMH is not enabled for this journal.</error>', false);
        $response->assertDontSee('<Identify>', false);
    }

    public function test_oai_endpoint_returns_valid_response_if_enabled(): void
    {
        $journal = Journal::factory()->create([
            'enable_oai' => true,
        ]);

        $response = $this->get(route('journal.oai', $journal->slug) . '?verb=Identify');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/xml; charset=UTF-8');
        $response->assertDontSee('<error code="noRecordsMatch">OAI-PMH is not enabled for this journal.</error>', false);
        $response->assertSee('<Identify', false);
    }

    public function test_oai_list_records_includes_license_metadata(): void
    {
        $journal = Journal::factory()->create([
            'enable_oai' => true,
            'abbreviation' => 'TEST',
            'license_url' => 'https://creativecommons.org/licenses/by/4.0/',
        ]);

        $submission = \App\Models\Submission::factory()->create([
            'journal_id' => $journal->id,
            'status' => 'published',
        ]);

        \App\Models\SubmissionAuthor::factory()->create([
            'submission_id' => $submission->id,
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);

        $response = $this->get(route('journal.oai', $journal->slug) . '?verb=ListRecords&metadataPrefix=oai_dc');

        $response->assertStatus(200);
        
        // Cek License
        $response->assertSee('<dc:rights>https://creativecommons.org/licenses/by/4.0/</dc:rights>', false);
        
        // Cek Creator (Author)
        $response->assertSee('<dc:creator>John Doe</dc:creator>', false);
    }
}
