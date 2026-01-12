<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Jobs\SyncAdsCountersJob;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule ads counters sync every 5 minutes
Schedule::job(new SyncAdsCountersJob)->everyFiveMinutes()->withoutOverlapping();

// Alternative: Schedule artisan command every hour
Schedule::command('ads:sync-counters')->hourly();
