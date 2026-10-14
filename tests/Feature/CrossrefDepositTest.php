<?php

namespace Tests\Feature;

use App\Models\CrossrefLog;
use App\Models\Issue;
use App\Models\Journal;
use App\Models\Publication;
use App\Models\Role;
use App\Models\Section;
use App\Models\Submission;
use App\Models\SubmissionAuthor;
use App\Models\User;
use App\Services\CrossrefDepositService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CrossrefDepositTest extends TestCase
{
    use DatabaseTransactions;

    protected Journal $journal;
    protected Section $section;
    protected Issue $issue;
    protected User $user;
    protected User $editor;
    protected CrossrefDepositService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(CrossrefDepositService::class);
        $this->user = User::factory()->create();

        // Create editor / admin user
        $adminRole = Role::findOrCreate('Super Admin', 'web');
        $this->editor = User::factory()->create();
        $this->editor->assignRole($adminRole);

        $this->journal = Journal::factory()->create([
            'name' => 'Journal of Crossref Testing',
            'slug' => 'jct-' . uniqid(),
            'path' => 'jct-' . uniqid(),
            'doi_prefix' => '10.1234',
            'enabled' => true,
        ]);

        $this->journal->setSetting('crossref_depositor_name', 'IamJOS Depositor');
        $this->journal->setSetting('crossref_depositor_email', 'depositor@iamjos.test');
        $this->journal->setSetting('crossref_username', 'iamjos_crossref_user');
        $this->journal->setSetting('crossref_password', 'secret_pwd');
        $this->journal->setSetting('crossref_test_mode', true);
        $this->journal->save();

        $this->section = Section::factory()->create(['journal_id' => $this->journal->id]);

        $this->issue = Issue::create([
            'journal_id' => $this->journal->id,
            'volume' => 1,
            'number' => 1,
            'year' => 2024,
            'seq_id' => 101,
            'doi' => '10.1234/jct.v1i1',
            'doi_status' => 'not_deposited',
            'is_published' => true,
            'published_at' => now(),
        ]);
    }

    protected function createTestArticle(string $title, string $doi, string $doiStatus = 'not_deposited'): array
    {
        $submission = Submission::create([
            'journal_id' => $this->journal->id,
            'section_id' => $this->section->id,
            'user_id' => $this->user->id,
            'issue_id' => $this->issue->id,
            'title' => $title,
            'locale' => 'en',
            'status' => Submission::STATUS_PUBLISHED,
            'stage' => Submission::STAGE_PRODUCTION,
            'submitted_at' => now()->subDays(10),
            'published_at' => now()->subDays(5),
            'seq_id' => random_int(10000, 99999),
        ]);

        $publication = Publication::create([
            'submission_id' => $submission->id,
            'section_id' => $this->section->id,
            'issue_id' => $this->issue->id,
            'version' => 1,
            'status' => Publication::STATUS_PUBLISHED,
            'title' => $title,
            'doi' => $doi,
            'doi_status' => $doiStatus,
            'date_published' => '2024-01-15',
            'copyright_year' => 2024,
        ]);

        SubmissionAuthor::create([
            'submission_id' => $submission->id,
            'publication_id' => $publication->id,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
            'seq' => 1,
            'is_primary_contact' => true,
        ]);

        return [$submission, $publication];
    }

    /** @test */
    public function deposit_sends_doMDUpload_and_marks_submitted_on_success()
    {
        [$submission, $publication] = $this->createTestArticle('Quantum Computing Advances', '10.1234/jct.v1i1.1');

        Http::fake([
            'https://test.crossref.org/servlet/deposit*' => Http::response(
                'SUCCESS: Your batch submission was successfully received.',
                200
            ),
        ]);

        $result = $this->service->deposit([$submission->id], $this->journal);

        $this->assertEquals('Success', $result['status']);

        // Verify request payload contained operation=doMDUpload
        Http::assertSent(function (Request $request) {
            $isPost = $request->method() === 'POST';
            $isTestHost = str_contains($request->url(), 'test.crossref.org');
            $hasDoMDUpload = false;

            // Inspect multipart parameters
            if ($request->isMultipart()) {
                foreach ($request->data() as $part) {
                    if (isset($part['name']) && $part['name'] === 'operation' && $part['contents'] === 'doMDUpload') {
                        $hasDoMDUpload = true;
                    }
                }
            }

            return $isPost && $isTestHost && $hasDoMDUpload;
        });

        // Verify publication status was updated to submitted
        $publication->refresh();
        $this->assertEquals('submitted', $publication->doi_status);
        $this->assertNotNull($publication->crossref_batch_id);

        // Verify CrossrefLog was created
        $this->assertDatabaseHas('crossref_logs', [
            'submission_id' => $submission->id,
            'journal_id' => $this->journal->id,
            'status' => 'Success',
        ]);
    }

    /** @test */
    public function deposit_detects_false_positive_http_200_failure_and_does_not_mark_submitted()
    {
        [$submission, $publication] = $this->createTestArticle('Neural Networks Study', '10.1234/jct.v1i1.2');

        // Crossref returns HTTP 200 with FAILURE body
        Http::fake([
            'https://test.crossref.org/servlet/deposit*' => Http::response(
                'FAILURE: Invalid login ID or password',
                200
            ),
        ]);

        $result = $this->service->deposit([$submission->id], $this->journal);

        $this->assertEquals('Failed', $result['status']);
        $this->assertStringContainsString('Crossref rejected deposit', $result['message']);

        // Verify publication was NOT marked as submitted
        $publication->refresh();
        $this->assertEquals('failed', $publication->doi_status);

        // Verify CrossrefLog records Failure
        $this->assertDatabaseHas('crossref_logs', [
            'submission_id' => $submission->id,
            'journal_id' => $this->journal->id,
            'status' => 'Failed',
        ]);
    }

    /** @test */
    public function deposit_issues_sends_doMDUpload_and_updates_status()
    {
        Http::fake([
            'https://test.crossref.org/servlet/deposit*' => Http::response(
                'SUCCESS: batch 12345 accepted',
                200
            ),
        ]);

        $result = $this->service->depositIssues([$this->issue->id], $this->journal);

        $this->assertEquals('Success', $result['status']);

        // Verify issue updated to submitted
        $this->issue->refresh();
        $this->assertEquals('submitted', $this->issue->doi_status);
        $this->assertNotNull($this->issue->crossref_batch_id);

        // Verify log
        $this->assertDatabaseHas('crossref_logs', [
            'journal_id' => $this->journal->id,
            'crossref_batch_id' => $this->issue->crossref_batch_id,
            'status' => 'Success',
        ]);
    }

    /** @test */
    public function check_doi_status_works_api_preserves_slashes_and_activates()
    {
        $doi = '10.1234/jct.v1i1.1';

        Http::fake([
            'https://api.crossref.org/works/10.1234/jct.v1i1.1' => Http::response([
                'status' => 'ok',
                'message' => ['DOI' => $doi, 'title' => ['Test Article']],
            ], 200),
        ]);

        $result = $this->service->checkDoiStatus(
            $doi,
            'batch-uuid-1',
            'user',
            'pass',
            'depositor@iamjos.test',
            true
        );

        $this->assertEquals('active', $result['status']);
        $this->assertEquals('Works API', $result['source']);

        // Verify request URL did not encode slashes as %2F
        Http::assertSent(function (Request $request) use ($doi) {
            return str_contains($request->url(), 'api.crossref.org/works/' . $doi);
        });
    }

    /** @test */
    public function check_doi_status_diagnostic_servlet_handles_completed_success()
    {
        $doi = '10.1234/jct.v1i1.1';
        $batchId = 'batch-uuid-success';

        $diagnosticXml = '<?xml version="1.0" encoding="UTF-8"?>
<doi_batch_diagnostic status="completed" sp="crossref">
   <submission_id>12345</submission_id>
   <batch_data>
      <record_count>1</record_count>
      <success_count>1</success_count>
      <warning_count>0</warning_count>
      <failure_count>0</failure_count>
   </batch_data>
   <record_diagnostic status="Success">
      <doi>' . $doi . '</doi>
      <msg>Successfully added</msg>
   </record_diagnostic>
</doi_batch_diagnostic>';

        Http::fake([
            'https://api.crossref.org/works/*' => Http::response([], 404),
            'https://test.crossref.org/servlet/submissionDownload*' => Http::response($diagnosticXml, 200),
        ]);

        $result = $this->service->checkDoiStatus(
            $doi,
            $batchId,
            'iamjos_crossref_user',
            'secret_pwd',
            'depositor@iamjos.test',
            true
        );

        $this->assertEquals('active', $result['status']);
        $this->assertEquals('Diagnostic Servlet', $result['source']);
    }

    /** @test */
    public function check_doi_status_diagnostic_servlet_handles_completed_failure()
    {
        $doi = '10.1234/jct.v1i1.2';
        $batchId = 'batch-uuid-failure';

        $diagnosticXml = '<?xml version="1.0" encoding="UTF-8"?>
<doi_batch_diagnostic status="completed" sp="crossref">
   <submission_id>12345</submission_id>
   <batch_data>
      <record_count>1</record_count>
      <success_count>0</success_count>
      <warning_count>0</warning_count>
      <failure_count>1</failure_count>
   </batch_data>
   <record_diagnostic status="Failure">
      <doi>' . $doi . '</doi>
      <msg>Error: DOI already assigned to another work</msg>
   </record_diagnostic>
</doi_batch_diagnostic>';

        Http::fake([
            'https://api.crossref.org/works/*' => Http::response([], 404),
            'https://test.crossref.org/servlet/submissionDownload*' => Http::response($diagnosticXml, 200),
        ]);

        $result = $this->service->checkDoiStatus(
            $doi,
            $batchId,
            'iamjos_crossref_user',
            'secret_pwd',
            'depositor@iamjos.test',
            true
        );

        $this->assertEquals('failed', $result['status']);
        $this->assertEquals('Diagnostic Servlet', $result['source']);
        $this->assertStringContainsString('DOI already assigned', $result['message']);
    }

    /** @test */
    public function artisan_command_polls_and_updates_submitted_dois()
    {
        [$submission, $publication] = $this->createTestArticle('Deep Learning in Biology', '10.1234/jct.v1i1.3', 'submitted');
        $publication->update(['crossref_batch_id' => 'batch-poll-1']);

        Http::fake([
            'https://api.crossref.org/works/10.1234/jct.v1i1.3' => Http::response([
                'status' => 'ok',
                'message' => ['DOI' => '10.1234/jct.v1i1.3'],
            ], 200),
        ]);

        $this->artisan('crossref:check-status')
            ->assertExitCode(0);

        $publication->refresh();
        $this->assertEquals('active', $publication->doi_status);
    }

    /** @test */
    public function manual_sync_status_action_in_controller_updates_dois()
    {
        [$submission, $publication] = $this->createTestArticle('Blockchain Research', '10.1234/jct.v1i1.4', 'submitted');
        $publication->update(['crossref_batch_id' => 'batch-sync-1']);

        Http::fake([
            'https://api.crossref.org/works/10.1234/jct.v1i1.4' => Http::response([
                'status' => 'ok',
                'message' => ['DOI' => '10.1234/jct.v1i1.4'],
            ], 200),
        ]);

        $response = $this->actingAs($this->editor)
            ->post(route('journal.settings.tools.crossref.sync_status', $this->journal->slug), [
                'submission_ids' => [$submission->id],
                'type' => 'article',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $publication->refresh();
        $this->assertEquals('active', $publication->doi_status);
    }

    /** @test */
    public function check_doi_status_correctly_differentiates_multi_item_batch_records()
    {
        $batchId = 'batch-multi-123';
        $doiSuccess = '10.1234/jct.success';
        $doiFailure = '10.1234/jct.failure';

        $diagnosticXml = '<?xml version="1.0" encoding="UTF-8"?>
<doi_batch_diagnostic status="completed" sp="test">
   <submission_id>12345</submission_id>
   <batch_data>
      <record_count>2</record_count>
      <success_count>1</success_count>
      <warning_count>0</warning_count>
      <failure_count>1</failure_count>
   </batch_data>
   <record_diagnostic status="Success">
      <doi>' . $doiSuccess . '</doi>
      <msg>Successfully added</msg>
   </record_diagnostic>
   <record_diagnostic status="Failure">
      <doi>' . $doiFailure . '</doi>
      <msg>Error: Duplicate DOI found</msg>
   </record_diagnostic>
</doi_batch_diagnostic>';

        Http::fake([
            'https://api.crossref.org/works/*' => Http::response([], 404),
            'https://test.crossref.org/servlet/submissionDownload*' => Http::response($diagnosticXml, 200),
        ]);

        // Check the successful DOI: MUST be active
        $resultSuccess = $this->service->checkDoiStatus(
            $doiSuccess,
            $batchId,
            'iamjos_crossref_user',
            'secret_pwd',
            'depositor@iamjos.test',
            true
        );

        $this->assertEquals('active', $resultSuccess['status']);
        $this->assertEquals('Diagnostic Servlet', $resultSuccess['source']);

        // Check the failed DOI: MUST be failed
        $resultFailure = $this->service->checkDoiStatus(
            $doiFailure,
            $batchId,
            'iamjos_crossref_user',
            'secret_pwd',
            'depositor@iamjos.test',
            true
        );

        $this->assertEquals('failed', $resultFailure['status']);
        $this->assertEquals('Diagnostic Servlet', $resultFailure['source']);
        $this->assertStringContainsString('Duplicate DOI', $resultFailure['message']);
    }

    /** @test */
    public function check_doi_status_strips_doi_uri_prefix_before_querying_works_api()
    {
        $prefixedDoi = 'https://doi.org/10.1234/jct.prefixed';

        Http::fake([
            'https://api.crossref.org/works/10.1234/jct.prefixed' => Http::response([
                'status' => 'ok',
                'message' => ['DOI' => '10.1234/jct.prefixed'],
            ], 200),
        ]);

        $result = $this->service->checkDoiStatus(
            $prefixedDoi,
            null,
            null,
            null,
            'depositor@iamjos.test',
            false
        );

        $this->assertEquals('active', $result['status']);
        $this->assertEquals('Works API', $result['source']);

        // Verify request was made to bare DOI without https://doi.org prefix
        Http::assertSent(function (Request $request) {
            return $request->url() === 'https://api.crossref.org/works/10.1234/jct.prefixed';
        });
    }

    /** @test */
    public function check_doi_status_handles_non_xml_diagnostic_error_gracefully()
    {
        $batchId = 'batch-nonxml-1';

        Http::fake([
            'https://api.crossref.org/works/*' => Http::response([], 404),
            'https://test.crossref.org/servlet/submissionDownload*' => Http::response('Invalid username or password', 200),
        ]);

        $result = $this->service->checkDoiStatus(
            '10.1234/test',
            $batchId,
            'invalid_user',
            'invalid_pass',
            'depositor@iamjos.test',
            true
        );

        $this->assertEquals('pending', $result['status']);
        $this->assertStringContainsString('Invalid username or password', $result['message']);
    }
}
