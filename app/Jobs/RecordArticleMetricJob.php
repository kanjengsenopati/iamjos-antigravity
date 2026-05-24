<?php
namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RecordArticleMetricJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly string $submissionId,
        public readonly string $type,        // 'view' | 'download'
        public readonly ?string $ipAddress,
        public readonly ?string $countryCode,
        public readonly ?string $city,
        public readonly string $date,        // Y-m-d
    ) {}

    public function handle(): void
    {
        try {
            DB::table('article_metrics')->updateOrInsert(
                [
                    'submission_id' => $this->submissionId,
                    'type'          => $this->type,
                    'ip_address'    => $this->ipAddress,
                    'date'          => $this->date,
                ],
                [
                    'country_code' => $this->countryCode,
                    'city'         => $this->city,
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ]
            );

            // Perbarui heartbeat queue worker untuk QueueChecker (TTL 10 menit)
            Cache::put('queue:last_processed_at', now()->toIso8601String(), 600);
        } catch (\Throwable $e) {
            // Log error tapi jangan re-throw agar queue worker tidak crash
            Log::error('RecordArticleMetricJob gagal: ' . $e->getMessage(), [
                'submission_id' => $this->submissionId,
                'type'          => $this->type,
                'date'          => $this->date,
            ]);
        }
    }
}
