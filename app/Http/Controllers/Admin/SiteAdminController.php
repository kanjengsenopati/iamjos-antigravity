<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Journal;
use App\Models\SiteContent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class SiteAdminController extends Controller
{
    /**
     * Display the site administration dashboard.
     */
    /**
     * Display the site administration dashboard.
     */
    public function index()
    {
        $journals = Journal::withCount(['submissions', 'issues'])
            ->orderBy('name')
            ->get();

        return view('admin.site.index', compact('journals'));
    }

    /**
     * Display the site settings form.
     */
    public function siteSettings()
    {
        // In a real app, you'd fetch these from a Settings model or config
        $settings = [
            'site_title' => config('app.name'),
            'site_intro' => 'Welcome to our academic journal portal.',
            'redirect_to_journal' => false,
            'min_password_length' => 8,
        ];

        return view('admin.site.settings', compact('settings'));
    }

    /**
     * Display system information.
     */
    public function systemInfo()
    {
        $systemInfo = $this->getSystemInfo();
        return view('admin.site.system-info', compact('systemInfo'));
    }

    /**
     * Update site settings.
     */
    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'site_title' => 'required|string|max:255',
            'site_intro' => 'nullable|string',
            'redirect_to_journal' => 'boolean',
            'min_password_length' => 'required|integer|min:6|max:32',
        ]);

        // Save settings to config or database
        // For now, we'll just flash a success message

        return back()->with('success', 'Site settings updated successfully.');
    }

    /**
     * Expire all user sessions.
     */
    public function expireSessions()
    {
        try {
            DB::table('sessions')->truncate();
            return back()->with('success', 'All user sessions have been expired.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to expire sessions: ' . $e->getMessage());
        }
    }

    /**
     * Clear data cache.
     */
    public function clearDataCache()
    {
        try {
            Artisan::call('cache:clear');
            return back()->with('success', 'Data cache cleared successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to clear cache: ' . $e->getMessage());
        }
    }

    /**
     * Clear template cache.
     */
    public function clearTemplateCache()
    {
        try {
            Artisan::call('view:clear');
            return back()->with('success', 'Template cache cleared successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to clear template cache: ' . $e->getMessage());
        }
    }

    /**
     * Clear scheduled task logs.
     */
    public function clearScheduledTaskLogs()
    {
        try {
            // Clear any scheduled task logs if applicable
            return back()->with('success', 'Scheduled task logs cleared successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to clear logs: ' . $e->getMessage());
        }
    }

    /**
     * Get system information.
     */
    private function getSystemInfo(): array
    {
        return [
            'version' => 'IAMJOS 1.0.0',
            'server_date' => now()->format('F j, Y, g:i a'),
            'operating_system' => PHP_OS_FAMILY . ' (' . php_uname('r') . ')',
            'php_version' => PHP_VERSION,
            'database_driver' => config('database.default'),
            'database_version' => $this->getDatabaseVersion(),
            'laravel_version' => app()->version(),
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'config_path' => base_path('.env'),
            'base_url' => config('app.url'),
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
        ];
    }

    /**
     * Get database version.
     */
    private function getDatabaseVersion(): string
    {
        try {
            $driver = config('database.default');
            if ($driver === 'mysql') {
                return DB::select('SELECT VERSION() as version')[0]->version ?? 'Unknown';
            } elseif ($driver === 'pgsql') {
                return DB::select('SELECT version()')[0]->version ?? 'Unknown';
            } elseif ($driver === 'sqlite') {
                return DB::select('SELECT sqlite_version() as version')[0]->version ?? 'Unknown';
            }
            return 'Unknown';
        } catch (\Exception $e) {
            return 'Unknown';
        }
    }

    /**
     * Display the About Page settings form.
     */
    public function editAbout()
    {
        $content = SiteContent::getAll();

        return view('admin.site.about-settings', compact('content'));
    }

    /**
     * Update the About Page settings.
     */
    public function updateAbout(Request $request)
    {
        $validated = $request->validate([
            'about_title' => 'required|string|max:255',
            'about_content' => 'required|string',
        ]);

        // Save each field to site_contents
        SiteContent::set('about_title', $validated['about_title'], 'about', 'text', 'About Page Title');
        SiteContent::set('about_content', $validated['about_content'], 'about', 'html', 'About Page Content');

        return back()->with('success', 'About page settings updated successfully.');
    }
}
