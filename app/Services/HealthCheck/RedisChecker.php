<?php

namespace App\Services\HealthCheck;

use Illuminate\Support\Facades\Redis;

class RedisChecker implements HealthCheckerInterface
{
    public function check(): CheckResult
    {
        $start = microtime(true);

        try {
            Redis::ping();

            $latencyMs = (microtime(true) - $start) * 1000;

            return CheckResult::ok($latencyMs);
        } catch (\Throwable $e) {
            $latencyMs = (microtime(true) - $start) * 1000;

            return CheckResult::error('Redis connection failed', $latencyMs);
        }
    }
}
