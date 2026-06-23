<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Models\SystemSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use App\Models\Role;
use Tests\TestCase;

class SystemSettingControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $regularUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Setup Spatie Role
        $role = Role::findOrCreate('Super Admin', 'web');
        $this->admin = User::factory()->create();
        $this->admin->assignRole($role);

        $this->regularUser = User::factory()->create();

        // Seed some system settings
        SystemSetting::create([
            'key' => 'mail_mailer',
            'value' => 'log',
            'type' => 'string',
            'group' => 'email',
            'description' => 'Mail driver'
        ]);
        SystemSetting::create([
            'key' => 'mail_host',
            'value' => '127.0.0.1',
            'type' => 'string',
            'group' => 'email',
            'description' => 'SMTP Host'
        ]);
        SystemSetting::create([
            'key' => 'mail_port',
            'value' => '1025',
            'type' => 'integer',
            'group' => 'email',
            'description' => 'SMTP Port'
        ]);
    }

    /** @test */
    public function regular_user_cannot_access_system_settings()
    {
        $response = $this->actingAs($this->regularUser)
            ->get('/admin/system-settings');

        $response->assertStatus(403);
    }

    /** @test */
    public function super_admin_can_access_system_settings()
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/system-settings');

        $response->assertStatus(200);
        $response->assertSee('System Settings');
        $response->assertSee('mail_mailer');
    }

    /** @test */
    public function super_admin_can_update_system_settings()
    {
        $response = $this->actingAs($this->admin)
            ->post('/admin/system-settings', [
                'mail_mailer' => 'smtp',
                'mail_host' => 'smtp.mailtrap.io',
                'mail_port' => '587'
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'System settings updated successfully.');

        $this->assertDatabaseHas('system_settings', [
            'key' => 'mail_mailer',
            'value' => 'smtp'
        ]);

        $this->assertDatabaseHas('system_settings', [
            'key' => 'mail_host',
            'value' => 'smtp.mailtrap.io'
        ]);

        $this->assertDatabaseHas('system_settings', [
            'key' => 'mail_port',
            'value' => '587'
        ]);
    }

    /** @test */
    public function super_admin_can_send_test_email()
    {
        Mail::fake();

        $response = $this->actingAs($this->admin)
            ->postJson('/admin/system-settings/test-email', [
                'email' => 'test-recipient@example.com'
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Test email successfully sent to test-recipient@example.com.'
        ]);

        Mail::assertSent(\App\Mail\SystemTestMail::class, function ($mail) {
            return $mail->recipientEmail === 'test-recipient@example.com';
        });
    }

    /** @test */
    public function test_email_requires_valid_email()
    {
        $response = $this->actingAs($this->admin)
            ->postJson('/admin/system-settings/test-email', [
                'email' => 'invalid-email-address'
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
    }
}
