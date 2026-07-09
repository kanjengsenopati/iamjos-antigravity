<?php

namespace Tests\Feature;

use App\Models\Journal;
use App\Models\Section;
use App\Models\User;
use App\Models\Submission;
use App\Models\CopyeditingAssignment;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CopyeditorAssignmentTest extends TestCase
{
    use RefreshDatabase;

    protected $journal;
    protected $editor;
    protected $copyeditor;
    protected $submission;
    protected $section;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles and permissions
        $this->artisan('db:seed', ['--class' => 'Database\\Seeders\\RolesAndPermissionsSeeder']);

        // Setup Journal and Users
        $this->journal = Journal::factory()->create(['slug' => 'test-journal', 'enabled' => true]);
        $this->section = Section::factory()->create(['journal_id' => $this->journal->id]);
        
        $this->editor = User::factory()->create();
        $this->copyeditor = User::factory()->create();

        // Assign Editor role
        $editorRole = Role::withoutGlobalScope('journal')
            ->where('permission_level', Role::LEVEL_EDITOR)
            ->whereNull('journal_id')
            ->first();
        
        if ($editorRole) {
            $this->editor->journalRoles()->create([
                'journal_id' => $this->journal->id,
                'role_id' => $editorRole->id
            ]);
        }

        // Assign Copyeditor role
        $copyeditorRole = Role::withoutGlobalScope('journal')
            ->where('name', 'Copyeditor')
            ->whereNull('journal_id')
            ->first();
        
        if ($copyeditorRole) {
            $this->copyeditor->journalRoles()->create([
                'journal_id' => $this->journal->id,
                'role_id' => $copyeditorRole->id
            ]);
        }

        // Create a submission
        $this->submission = Submission::create([
            'journal_id' => $this->journal->id,
            'user_id' => $this->editor->id,
            'section_id' => $this->section->id,
            'title' => 'Test Copyediting Assignment Submission',
            'abstract' => 'This is a test abstract for copyediting assignment.',
            'status' => Submission::STATUS_SUBMITTED,
            'stage_id' => Submission::STAGE_ID_COPYEDITING,
            'seq_id' => 1001,
        ]);
    }

    /** @test */
    public function it_can_assign_copyeditor_to_submission()
    {
        $this->actingAs($this->editor);

        $response = $this->post(route('journal.workflow.assign-copyeditor', [
            'journal' => $this->journal->slug,
            'submission' => $this->submission->id
        ]), [
            'user_id' => $this->copyeditor->id,
        ]);

        $response->assertRedirect(route('journal.submissions.show', [
            'journal' => $this->journal->slug,
            'submission' => $this->submission->slug
        ]));

        $response->assertSessionHas('success', 'Copyeditor assigned successfully.');

        // Verify CopyeditingAssignment was created
        $this->assertDatabaseHas('copyediting_assignments', [
            'submission_id' => $this->submission->id,
            'copyeditor_id' => $this->copyeditor->id,
            'assigned_by' => $this->editor->id,
            'status' => CopyeditingAssignment::STATUS_PENDING,
        ]);
    }

    /** @test */
    public function it_prevents_duplicate_copyeditor_assignment()
    {
        // First assignment
        CopyeditingAssignment::create([
            'submission_id' => $this->submission->id,
            'copyeditor_id' => $this->copyeditor->id,
            'assigned_by' => $this->editor->id,
            'status' => CopyeditingAssignment::STATUS_PENDING,
            'assigned_at' => now(),
        ]);

        $this->actingAs($this->editor);

        $response = $this->post(route('journal.workflow.assign-copyeditor', [
            'journal' => $this->journal->slug,
            'submission' => $this->submission->id
        ]), [
            'user_id' => $this->copyeditor->id,
        ]);

        $response->assertSessionHasErrors(['user_id']);
    }

    /** @test */
    public function it_allows_reassignment_of_cancelled_copyeditor()
    {
        // Create cancelled assignment
        CopyeditingAssignment::create([
            'submission_id' => $this->submission->id,
            'copyeditor_id' => $this->copyeditor->id,
            'assigned_by' => $this->editor->id,
            'status' => CopyeditingAssignment::STATUS_CANCELLED,
            'assigned_at' => now(),
        ]);

        $this->actingAs($this->editor);

        $response = $this->post(route('journal.workflow.assign-copyeditor', [
            'journal' => $this->journal->slug,
            'submission' => $this->submission->id
        ]), [
            'user_id' => $this->copyeditor->id,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Should have a new pending assignment
        $this->assertDatabaseHas('copyediting_assignments', [
            'submission_id' => $this->submission->id,
            'copyeditor_id' => $this->copyeditor->id,
            'status' => CopyeditingAssignment::STATUS_PENDING,
        ]);
    }

    /** @test */
    public function it_validates_required_fields()
    {
        $this->actingAs($this->editor);

        $response = $this->post(route('journal.workflow.assign-copyeditor', [
            'journal' => $this->journal->slug,
            'submission' => $this->submission->id
        ]), [
            // Missing user_id
        ]);

        $response->assertSessionHasErrors(['user_id']);
    }

    /** @test */
    public function it_validates_user_exists()
    {
        $this->actingAs($this->editor);

        $response = $this->post(route('journal.workflow.assign-copyeditor', [
            'journal' => $this->journal->slug,
            'submission' => $this->submission->id
        ]), [
            'user_id' => '00000000-0000-0000-0000-000000000000', // Non-existent UUID
        ]);

        $response->assertSessionHasErrors(['user_id']);
    }

    /** @test */
    public function it_rejects_assignment_for_wrong_journal()
    {
        // Create another journal
        $otherJournal = Journal::factory()->create(['slug' => 'other-journal']);
        
        // Create submission in other journal
        $otherSubmission = Submission::create([
            'journal_id' => $otherJournal->id,
            'user_id' => $this->editor->id,
            'section_id' => $this->section->id,
            'title' => 'Other Journal Submission',
            'abstract' => 'This submission is in a different journal.',
            'status' => Submission::STATUS_SUBMITTED,
            'stage_id' => Submission::STAGE_ID_COPYEDITING,
            'seq_id' => 1002,
        ]);

        $this->actingAs($this->editor);

        // Try to assign copyeditor to submission in different journal
        $response = $this->post(route('journal.workflow.assign-copyeditor', [
            'journal' => $this->journal->slug, // Current journal context
            'submission' => $otherSubmission->id // Submission from different journal
        ]), [
            'user_id' => $this->copyeditor->id,
        ]);

        $response->assertStatus(404);
    }
}