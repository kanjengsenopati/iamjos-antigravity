<?php

namespace Tests\Feature;

use App\Models\Journal;
use App\Models\Section;
use App\Models\User;
use App\Models\Submission;
use App\Models\Discussion;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DiscussionTest extends TestCase
{
    use RefreshDatabase;

    protected $journal;
    protected $author;
    protected $editor;
    protected $section;
    protected $submission;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('db:seed', ['--class' => 'Database\\Seeders\\RolesAndPermissionsSeeder']);

        $this->journal = Journal::factory()->create(['slug' => 'test-journal', 'enabled' => true]);
        $this->section = Section::factory()->create(['journal_id' => $this->journal->id]);
        
        $this->author = User::factory()->create();
        $this->editor = User::factory()->create();

        // Assign Editor role
        $editorRole = Role::withoutGlobalScope('journal')
            ->where('permission_level', Role::LEVEL_EDITOR)
            ->whereNull('journal_id')
            ->first();
        
        $this->editor->journalRoles()->create([
            'journal_id' => $this->journal->id,
            'role_id' => $editorRole->id
        ]);

        $this->submission = Submission::create([
            'journal_id' => $this->journal->id,
            'user_id' => $this->author->id,
            'section_id' => $this->section->id,
            'title' => 'Test Discussion Submission',
            'abstract' => 'Abstract text',
            'status' => Submission::STATUS_SUBMITTED,
            'stage_id' => Submission::STAGE_ID_SUBMISSION,
            'seq_id' => 1001,
        ]);
    }

    /** @test */
    public function it_can_create_discussion_with_seq_id()
    {
        $this->actingAs($this->editor);

        $response = $this->post(route('journal.discussion.create', [
            'journal' => $this->journal->slug,
            'submission' => $this->submission->seq_id, // Pass the numeric seq_id (which is 1001)
        ]), [
            'subject' => 'Test Subject',
            'body' => 'Test Body Message',
            'stage_id' => 1,
            'participants' => [$this->author->id],
        ]);

        $response->assertStatus(302); // Redirects back
        $this->assertDatabaseHas('discussions', [
            'submission_id' => $this->submission->id,
            'subject' => 'Test Subject',
        ]);
    }
}
