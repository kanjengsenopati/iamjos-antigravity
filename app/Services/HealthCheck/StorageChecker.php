<?php

namespace App\Services\HealthCheck;

use Illuminate\Support\Facades\Storage;

class StorageChecker implements HealthCheckerInterface
{
    public function check(): CheckResult
    {
        $start = microtime(true);

        try {
            Storage::put('health-check-probe.tmp', 'ok');
            Storage::delete('health-check-probe.tmp');

            $latencyMs = (microtime(true) - $start) * 1000;

            return CheckResult::ok($latencyMs);
        } catch (\Throwable $e) {
            $latencyMs = (microtime(true) - $start) * 1000;

            return CheckResult::error('Storage is not writable', $latencyMs);
        }
    }
}
