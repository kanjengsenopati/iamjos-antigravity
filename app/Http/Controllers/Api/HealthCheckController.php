<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Journal;
use App\Models\Submission;
use App\Services\HealthCheck\DatabaseChecker;
use App\Services\HealthCheck\HealthReport;
use App\Services\HealthCheck\HealthStatus;
use App\Services\HealthCheck\QueueChecker;
use App\Services\HealthCheck\RedisChecker;
use App\Services\HealthCheck\StorageChecker;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class HealthCheckController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        try {
            // Jalankan semua checker dalam try/catch individual
            // agar satu checker yang gagal tidak menghentikan checker lain
            $checks = [];

            try {
                $checks['database'] = (new DatabaseChecker())->check();
            } catch (\Throwable $e) {
                $checks['database'] = \App\Services\HealthCheck\CheckResult::error('Database connection failed', 0);
            }

            try {
                $checks['redis'] = (new RedisChecker())->check();
            } catch (\Throwable $e) {
                $checks['redis'] = \App\Services\HealthCheck\CheckResult::error('Redis connection failed', 0);
            }

            try {
                $checks['storage'] = (new StorageChecker())->check();
            } catch (\Throwable $e) {
                $checks['storage'] = \App\Services\HealthCheck\CheckResult::error('Storage is not writable', 0);
            }

            try {
                $checks['queue'] = (new QueueChecker())->check();
            } catch (\Throwable $e) {
                $checks['queue'] = \App\Services\HealthCheck\CheckResult::error('Queue worker may not be running (no activity in last 5 minutes)', 0);
            }

            // Tentukan HealthStatus keseluruhan
            $dbOk      = $checks['database']->status === 'ok';
            $redisOk   = $checks['redis']->status === 'ok';
            $storageOk = $checks['storage']->status === 'ok';
            $queueOk   = $checks['queue']->status === 'ok';

            if ($dbOk && $redisOk && $storageOk && $queueOk) {
                $overallStatus = HealthStatus::Healthy;
            } elseif ($dbOk && $redisOk && (!$storageOk || !$queueOk)) {
                // Komponen non-kritis (storage/queue) error, tapi DB dan Redis ok
                $overallStatus = HealthStatus::Degraded;
            } else {
                // Komponen kritis (DB atau Redis) error
                $overallStatus = HealthStatus::Unhealthy;
            }

            // Hitung uptime dari waktu bootstrap cache atau REQUEST_TIME
            $uptimeSeconds = $this->calculateUptime();

            // Ambil versi aplikasi
            $version = $this->getAppVersion();

            // Instance ID
            $instanceId = config('iamjos.instance_id', env('IAMJOS_INSTANCE_ID', 'unknown'));

            // Metrics — tangkap exception agar tidak mengganggu health check
            $activeJournals     = 0;
            $pendingSubmissions = 0;

            try {
                $activeJournals = Journal::where('enabled', true)->count();
            } catch (\Throwable $e) {
                // Biarkan 0 jika DB tidak tersedia
            }

            try {
                $pendingSubmissions = Submission::whereIn('status', [
                    'submitted',
                    'under_review',
                    'in_review',
                ])->count();
            } catch (\Throwable $e) {
                // Biarkan 0 jika DB tidak tersedia
            }

            $report = new HealthReport(
                status: $overallStatus,
                timestamp: now()->utc()->toIso8601ZuluString(),
                version: $version,
                uptimeSeconds: $uptimeSeconds,
                instanceId: $instanceId,
                checks: $checks,
                metrics: [
                    'active_journals'     => $activeJournals,
                    'pending_submissions' => $pendingSubmissions,
                ],
            );

            return response()->json($report->toArray(), $report->httpStatus());
        } catch (\Throwable $e) {
            // Exception tidak tertangani — kembalikan 503 TANPA stack trace
            return response()->json([
                'status'    => 'unhealthy',
                'timestamp' => now()->utc()->toIso8601ZuluString(),
                'message'   => 'An unexpected error occurred',
            ], 503);
        }
    }

    /**
     * Hitung uptime aplikasi dalam detik.
     * Menggunakan REQUEST_TIME server atau waktu modifikasi bootstrap/cache.
     */
    private function calculateUptime(): int
    {
        // Coba dari REQUEST_TIME (waktu request masuk, bukan uptime server)
        // Fallback ke waktu modifikasi bootstrap/cache sebagai proxy uptime
        try {
            $bootstrapCache = base_path('bootstrap/cache');
            if (is_dir($bootstrapCache)) {
                $mtime = filemtime($bootstrapCache);
                if ($mtime !== false) {
                    return (int) now()->diffInSeconds(Carbon::createFromTimestamp($mtime));
                }
            }
        } catch (\Throwable $e) {
            // Ignore
        }

        // Fallback: gunakan REQUEST_TIME jika tersedia
        if (isset($_SERVER['REQUEST_TIME'])) {
            return (int) (time() - $_SERVER['REQUEST_TIME']);
        }

        return 0;
    }

    /**
     * Ambil versi aplikasi dari config atau composer.json.
     */
    private function getAppVersion(): string
    {
        // Coba dari config app.version
        $version = config('app.version');
        if (!empty($version)) {
            return (string) $version;
        }

        // Coba dari composer.json
        try {
            $composerPath = base_path('composer.json');
            if (file_exists($composerPath)) {
                $composer = json_decode(file_get_contents($composerPath), true);
                if (!empty($composer['version'])) {
                    return (string) $composer['version'];
                }
            }
        } catch (\Throwable $e) {
            // Ignore
        }

        return '1.0.0';
    }
}
