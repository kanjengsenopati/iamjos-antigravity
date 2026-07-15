<?php

namespace Tests\Unit\Notifications;

use App\Notifications\ReviewInvitation;
use App\Models\ReviewAssignment;
use App\Models\Submission;
use App\Models\User;
use App\Models\Journal;
use App\Models\Section;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewInvitationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_strips_html_tags_from_submission_abstract_in_mail()
    {
        $journal = Journal::factory()->create();
        $section = Section::factory()->create(['journal_id' => $journal->id]);
        $user = User::factory()->create();
        
        $submission = Submission::factory()->create([
            'journal_id' => $journal->id,
            'section_id' => $section->id,
            'user_id' => $user->id,
            'title' => 'Test Title',
            'abstract' => '<p>This is a <strong>formatted</strong> abstract with <a href="#">HTML</a> tags.</p>',
            'seq_id' => 101,
        ]);

        $review = ReviewAssignment::create([
            'submission_id' => $submission->id,
            'reviewer_id' => $user->id,
            'round' => 1,
            'due_date' => now()->addWeeks(2),
        ]);

        $notification = new ReviewInvitation($review);
        $mailMessage = $notification->toMail($user);
        
        $mailData = $mailMessage->toArray();
        
        // Find the abstract line from the email lines
        $abstractLine = collect($mailData['introLines'])->first(fn($line) => str_contains($line, 'Abstract:'));
        
        $this->assertNotNull($abstractLine, 'Abstract line was not found in the email.');
        $this->assertStringContainsString('This is a formatted abstract with HTML tags.', $abstractLine);
        $this->assertStringNotContainsString('<p>', $abstractLine);
        $this->assertStringNotContainsString('<strong>', $abstractLine);
    }
}
