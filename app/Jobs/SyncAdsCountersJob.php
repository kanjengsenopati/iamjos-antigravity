<?php

namespace App\Jobs;

use App\Models\HomeAds;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SyncAdsCountersJob implements ShouldQueue
{
    use Queueable;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $activeAds = HomeAds::where('is_active', true)->get();

            foreach ($activeAds as $ads) {
                $this->syncAdsCounters($ads);
            }

            Log::info('Ads counters sync job completed successfully');
        } catch (\Exception $e) {
            Log::error('Ads counters sync job failed: ' . $e->getMessage());
            throw $e; // Re-throw untuk retry mechanism
        }
    }

    /**
     * Sync counters untuk specific ads
     */
    private function syncAdsCounters(HomeAds $ads): void
    {
        $viewKey = "ads_view_count:{$ads->id}";
        $clickKey = "ads_click_count:{$ads->id}";

        $pendingViews = $this->getCounterValue($viewKey);
        $pendingClicks = $this->getCounterValue($clickKey);

        if ($pendingViews > 0 || $pendingClicks > 0) {
            try {
                if ($pendingViews > 0) {
                    $ads->increment('total_view', $pendingViews);
                    $this->clearCounter($viewKey);
                }

                if ($pendingClicks > 0) {
                    $ads->increment('total_click', $pendingClicks);
                    $this->clearCounter($clickKey);
                }

                Log::info("Synced ads ID {$ads->id}: {$pendingViews} views, {$pendingClicks} clicks");
            } catch (\Exception $e) {
                Log::error("Failed to sync ads ID {$ads->id}: " . $e->getMessage());
                throw $e;
            }
        }
    }

    /**
     * Helper methods untuk Redis/Cache fallback
     */
    private function getCounterValue($key)
    {
        try {
            // Coba gunakan Redis dulu
            if (extension_loaded('redis') && config('database.redis.default')) {
                return Redis::get($key) ?? 0;
            }
        } catch (\Exception $e) {
            // Fallback ke Cache driver
        }

        return Cache::get($key, 0);
    }

    private function clearCounter($key)
    {
        try {
            // Coba gunakan Redis dulu
            if (extension_loaded('redis') && config('database.redis.default')) {
                Redis::del($key);
                return;
            }
        } catch (\Exception $e) {
            // Fallback ke Cache driver
        }

        Cache::forget($key);
    }
}
