<?php

namespace Tests\Feature;

use App\Models\Journal;
use App\Models\Section;
use App\Models\User;
use App\Models\Submission;
use App\Models\ReviewAssignment;
use App\Models\Role;
use App\Models\Publication;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected $journal;
    protected $author;
    protected $editor;
    protected $reviewer;
    protected $section;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Setup Journal and Users
        $this->journal = Journal::factory()->create(['slug' => 'test-journal', 'enabled' => true]);
        $this->section = Section::factory()->create(['journal_id' => $this->journal->id]);
        
        $this->author = User::factory()->create();
        $this->editor = User::factory()->create();
        $this->reviewer = User::factory()->create();

        // Assign Roles (Manual sync to pivot table because of custom Role model)
        $this->editor->journalRoles()->create([
            'journal_id' => $this->journal->id,
            'role_id' => Role::where('level', Role::LEVEL_EDITOR)->first()->id
        ]);
    }

    /** @test */
    public function it_can_complete_full_submission_workflow()
    {
        // 1. Author Submits
        $this->actingAs($this->author);
        
        $submissionData = [
            'journal_id' => $this->journal->id,
            'section_id' => $this->section->id,
            'title' => 'Test Workflow Submission',
            'abstract' => 'This is a test abstract for the workflow.',
            'locale' => 'en',
            'authors' => [
                [
                    'first_name' => 'John',
                    'last_name' => 'Doe',
                    'email' => 'john@example.com',
                    'is_corresponding' => true,
                ]
            ]
        ];

        // Using a controller method or direct DB for setup if routes are complex
        $submission = Submission::create([
            'journal_id' => $this->journal->id,
            'user_id' => $this->author->id,
            'section_id' => $this->section->id,
            'title' => $submissionData['title'],
            'abstract' => $submissionData['abstract'],
            'status' => Submission::STATUS_SUBMITTED,
            'stage_id' => Submission::STAGE_ID_SUBMISSION,
            'seq_id' => 1001,
        ]);

        $this->assertDatabaseHas('submissions', ['title' => 'Test Workflow Submission']);

        // 2. Editor Assigns themselves and Promotes to Review
        $this->actingAs($this->editor);
        
        $submission->update([
            'status' => Submission::STATUS_IN_REVIEW,
            'stage_id' => Submission::STAGE_ID_REVIEW,
        ]);

        // 3. Editor Assigns Reviewer
        $reviewAssignment = ReviewAssignment::create([
            'submission_id' => $submission->id,
            'reviewer_id' => $this->reviewer->id,
            'round' => 1,
            'status' => ReviewAssignment::STATUS_PENDING,
            'due_date' => now()->addWeeks(2),
        ]);

        $this->assertDatabaseHas('review_assignments', ['reviewer_id' => $this->reviewer->id]);

        // 4. Reviewer Submits Review
        $this->actingAs($this->reviewer);
        $reviewAssignment->update([
            'status' => ReviewAssignment::STATUS_COMPLETED,
            'recommendation' => ReviewAssignment::RECOMMEND_ACCEPT,
            'completed_at' => now(),
        ]);

        // 5. Editor Accepts Submission
        $this->actingAs($this->editor);
        $submission->update(['status' => Submission::STATUS_ACCEPTED]);
        
        // 6. Editor Promotes to Production & Publishes
        $publication = Publication::create([
            'submission_id' => $submission->id,
            'title' => $submission->title,
            'status' => Publication::STATUS_PUBLISHED,
            'version' => 1,
        ]);

        $submission->update([
            'status' => Submission::STATUS_PUBLISHED,
            'published_at' => now(),
        ]);

        $this->assertEquals(Submission::STATUS_PUBLISHED, $submission->fresh()->status);
        
        // 7. Verify Public Visibility
        $response = $this->get(route('journal.public.article', ['journal' => $this->journal->slug, 'article' => $submission->seq_id]));
        $response->assertStatus(200);
        $response->assertSee('Test Workflow Submission');
    }
}
