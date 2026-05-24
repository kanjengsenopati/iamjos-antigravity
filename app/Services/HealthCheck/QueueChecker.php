<?php

namespace App\Services\HealthCheck;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Carbon;

class QueueChecker implements HealthCheckerInterface
{
    public function check(): CheckResult
    {
        $start = microtime(true);

        try {
            $lastProcessedAt = Cache::get('queue:last_processed_at');

            $latencyMs = (microtime(true) - $start) * 1000;

            // Jika null, kembalikan error dengan metadata
            if ($lastProcessedAt === null) {
                return new CheckResult(
                    status: 'error',
                    message: 'Queue worker may not be running (no activity in last 5 minutes)',
                    latencyMs: $latencyMs,
                    metadata: ['last_processed_at' => null]
                );
            }

            $lastProcessedCarbon = Carbon::parse($lastProcessedAt);

            // Jika lebih dari 5 menit yang lalu, kembalikan error
            if ($lastProcessedCarbon->lt(now()->subMinutes(5))) {
                return new CheckResult(
                    status: 'error',
                    message: 'Queue worker may not be running (no activity in last 5 minutes)',
                    latencyMs: $latencyMs,
                    metadata: ['last_processed_at' => $lastProcessedAt]
                );
            }

            return CheckResult::ok($latencyMs, ['last_processed_at' => $lastProcessedAt]);
        } catch (\Throwable $e) {
            $latencyMs = (microtime(true) - $start) * 1000;

            return new CheckResult(
                status: 'error',
                message: 'Queue worker may not be running (no activity in last 5 minutes)',
                latencyMs: $latencyMs,
                metadata: ['last_processed_at' => null]
            );
        }
    }
}
