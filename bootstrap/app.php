<?php

use Illuminate\Foundation\Application;
use App\Http\Middleware\ValidateApiKey;
use App\Http\Middleware\AdsTrackingRateLimit;
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
        $middleware->alias([
            'validate_api_key' => ValidateApiKey::class,
            'ads_rate_limit' => AdsTrackingRateLimit::class,
        ]);
        // contoh jika mau auto-apply ke grup API:
        // $middleware->appendToGroup('api', [ValidateApiKey::class]);
    })
    ->withSchedule(function (Schedule $schedule) {
        // Lebih aman pisahkan argumen
        $schedule->command('fetch:phri-news', ['1w'])->dailyAt('01:00');
        $schedule->command('phri:sync-provinces')->dailyAt('02:00');
        $schedule->command('phri:sync-regencies')->dailyAt('02:15');
        $schedule->command('phri:sync-meetingrooms')->dailyAt('02:30');
        $schedule->command('events:fetch')->dailyAt('01:45');
        $schedule->command('media:sync-phri --pages=2')->everySixHours()
            ->withoutOverlapping()
            ->onOneServer();
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->create();
