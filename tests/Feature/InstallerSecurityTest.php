<?php

namespace Tests\Feature;

use App\Http\Controllers\InstallController;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class InstallerSecurityTest extends TestCase
{
    use DatabaseTransactions;

    protected string $installedLockFile;
    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->installedLockFile = storage_path('installed');

        // Setup Super Admin for admin panel tests
        $role = Role::findOrCreate('Super Admin', 'web');
        $this->admin = User::factory()->create();
        $this->admin->assignRole($role);
    }

    protected function tearDown(): void
    {
        // Ensure installed lock file is cleaned up after testing
        if (File::exists($this->installedLockFile)) {
            File::delete($this->installedLockFile);
        }

        parent::tearDown();
    }

    /** @test */
    public function installer_is_accessible_when_installed_lock_file_does_not_exist()
    {
        if (File::exists($this->installedLockFile)) {
            File::delete($this->installedLockFile);
        }

        $response = $this->get('/install');

        // When not installed, either renders step 1 (200) or redirects to install.index
        $this->assertContains($response->status(), [200, 302]);
        if ($response->status() === 302) {
            $this->assertStringContainsString('/install', $response->headers->get('Location'));
        }
    }

    /** @test */
    public function installer_returns_404_when_installed_lock_file_exists()
    {
        // Create storage/installed marker
        File::put($this->installedLockFile, 'installed_at: ' . now());

        $this->assertTrue(File::exists($this->installedLockFile));

        // Test root install route
        $response1 = $this->get('/install');
        $response1->assertStatus(404);

        // Test step routes
        $response2 = $this->get('/install/step2');
        $response2->assertStatus(404);

        $response3 = $this->post('/install/execute', []);
        $response3->assertStatus(404);
    }

    /** @test */
    public function installer_step1_checks_php_version_with_laravel_12_minimum()
    {
        if (File::exists($this->installedLockFile)) {
            File::delete($this->installedLockFile);
        }

        $response = $this->get('/install');

        // Requirements view should contain PHP version and required 8.2.0
        if ($response->status() === 200) {
            $response->assertSee('8.2.0');
        }
    }

    /** @test */
    public function check_binary_available_respects_environment_override()
    {
        $controller = new InstallController();
        $reflector = new \ReflectionClass($controller);
        $method = $reflector->getMethod('checkBinaryAvailable');
        $method->setAccessible(true);

        // Create temporary dummy executable
        $tempDir = sys_get_temp_dir();
        $fakeBinary = $tempDir . DIRECTORY_SEPARATOR . 'fake_ffmpeg.exe';
        file_put_contents($fakeBinary, 'binary content');

        putenv("FFMPEG_BINARIES={$fakeBinary}");

        $result = $method->invoke($controller, 'ffmpeg');

        // Cleanup
        putenv('FFMPEG_BINARIES');
        if (file_exists($fakeBinary)) {
            @unlink($fakeBinary);
        }

        $this->assertTrue($result);
    }

    /** @test */
    public function system_settings_displays_locked_status_when_installed_file_exists()
    {
        File::put($this->installedLockFile, 'installed_at: ' . now());

        $response = $this->actingAs($this->admin)->get('/admin/system-settings');

        $response->assertStatus(200);
        $response->assertSee('Installer');
        $response->assertSee('Locked');
    }

    /** @test */
    public function system_settings_displays_unlocked_status_when_installed_file_missing()
    {
        if (File::exists($this->installedLockFile)) {
            File::delete($this->installedLockFile);
        }

        $response = $this->actingAs($this->admin)->get('/admin/system-settings');

        $response->assertStatus(200);
        $response->assertSee('Installer');
        $response->assertSee('Unlocked');
    }

    /** @test */
    public function installer_returns_404_when_capitalized_installed_lock_file_exists()
    {
        $capitalizedLockFile = storage_path('Installed');
        File::put($capitalizedLockFile, 'installed_at: ' . now());

        try {
            $response = $this->get('/install');
            $response->assertStatus(404);
        } finally {
            if (File::exists($capitalizedLockFile)) {
                File::delete($capitalizedLockFile);
            }
        }
    }

    /** @test */
    public function lowspec_docker_fpm_configuration_file_exists_and_sets_ondemand()
    {
        $lowspecConfPath = base_path('docker/php/zz-docker-lowspec.conf');
        $this->assertFileExists($lowspecConfPath);

        $content = file_get_contents($lowspecConfPath);
        $this->assertStringContainsString('pm = ondemand', $content);
        $this->assertStringContainsString('pm.max_children = 5', $content);
    }
}
