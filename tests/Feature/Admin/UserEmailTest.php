<?php

namespace Tests\Feature\Admin;

use App\Models\Journal;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use App\Mail\GeneralNotificationMail;
use Tests\TestCase;

class UserEmailTest extends TestCase
{
    use RefreshDatabase;

    protected $journal;
    protected $manager;
    protected $recipient;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles and permissions
        $this->artisan('db:seed', ['--class' => 'Database\\Seeders\\RolesAndPermissionsSeeder']);

        // Create a journal
        $this->journal = Journal::factory()->create(['slug' => 'test-journal', 'enabled' => true]);

        // Create journal manager
        $this->manager = User::factory()->create();
        
        // Find or create global Journal Manager role
        $managerRole = Role::withoutGlobalScope('journal')
            ->where('name', 'Journal Manager')
            ->first();
            
        if ($managerRole) {
            $this->manager->journalRoles()->create([
                'journal_id' => $this->journal->id,
                'role_id' => $managerRole->id
            ]);
        }

        // Create a recipient user
        $this->recipient = User::factory()->create([
            'email' => 'recipient@example.com'
        ]);
    }

    /** @test */
    public function it_can_queue_email_via_ajax_request()
    {
        Mail::fake();

        $response = $this->actingAs($this->manager)
            ->postJson("/{$this->journal->slug}/users/{$this->recipient->id}/email", [
                'subject' => 'Test Subject',
                'body' => 'Test Body Content'
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Email successfully queued for ' . $this->recipient->name . '.'
        ]);

        Mail::assertQueued(GeneralNotificationMail::class, function ($mail) {
            return $mail->emailSubject === 'Test Subject'
                && $mail->emailBody === 'Test Body Content'
                && $mail->recipientName === $this->recipient->name
                && $mail->journalName === $this->journal->name;
        });
    }

    /** @test */
    public function it_validates_email_input_fields()
    {
        $response = $this->actingAs($this->manager)
            ->postJson("/{$this->journal->slug}/users/{$this->recipient->id}/email", [
                'subject' => '',
                'body' => ''
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['subject', 'body']);
    }
}
