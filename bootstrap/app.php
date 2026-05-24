<?php

use Illuminate\Foundation\Application;
use App\Http\Middleware\ValidateApiKey;
use App\Http\Middleware\JournalContextMiddleware;
use App\Http\Middleware\DetectJournalContext;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');

        $middleware->redirectGuestsTo(function (\Illuminate\Http\Request $request) {
            $journal = $request->route('journal');
            if ($journal) {
                $slug = $journal instanceof \App\Models\Journal ? $journal->slug : $journal;
                return route('journal.login', $slug);
            }
            return route('login');
        });

        $middleware->validateCsrfTokens(except: [
            '*/oai', // Allow OAI-PMH POST requests without CSRF token
            'logout',
            '*/logout',
        ]);

        $middleware->alias([
            'validate_api_key' => ValidateApiKey::class,
            'journal.context' => JournalContextMiddleware::class,
            'journal.detect' => DetectJournalContext::class,
            'check_installed' => \App\Http\Middleware\CheckIfInstalled::class,
            // Spatie Permission middleware
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            // IAMJOS License middleware
            'iamjos.license' => \App\Http\Middleware\LicenseMiddleware::class,
        ]);

        $middleware->append(\App\Http\Middleware\RedirectIfUninstalled::class);

        // contoh jika mau auto-apply ke grup API:
        // $middleware->appendToGroup('api', [ValidateApiKey::class]);
    })
    ->withSchedule(function (Schedule $schedule) {
        $schedule->job(new \App\Jobs\ReviewerReminderJob)->dailyAt('08:00');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->create();
