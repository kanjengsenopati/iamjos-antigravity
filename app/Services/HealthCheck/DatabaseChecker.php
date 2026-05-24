<?php

namespace App\Services\HealthCheck;

use Illuminate\Support\Facades\DB;

class DatabaseChecker implements HealthCheckerInterface
{
    public function check(): CheckResult
    {
        $start = microtime(true);

        try {
            DB::select('SELECT 1');

            $latencyMs = (microtime(true) - $start) * 1000;

            return CheckResult::ok($latencyMs);
        } catch (\Illuminate\Database\QueryException $e) {
            $latencyMs = (microtime(true) - $start) * 1000;

            // Jangan ekspos hostname, password, atau connection string dalam pesan error
            return CheckResult::error('Database connection failed', $latencyMs);
        } catch (\PDOException $e) {
            $latencyMs = (microtime(true) - $start) * 1000;

            return CheckResult::error('Database connection failed', $latencyMs);
        } catch (\Throwable $e) {
            $latencyMs = (microtime(true) - $start) * 1000;

            return CheckResult::error('Database connection failed', $latencyMs);
        }
    }
}
