<?php

namespace App\Console\Commands;

use App\Models\HomeAds;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SyncAdsCounters extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ads:sync-counters {--force : Force sync even if count is low}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync ads view and click counters from Redis to database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting ads counters sync...');

        $syncedAds = 0;
        $totalViews = 0;
        $totalClicks = 0;

        // Ambil semua ads yang aktif
        $activeAds = HomeAds::where('is_active', true)->get();

        foreach ($activeAds as $ads) {
            $viewKey = "ads_view_count:{$ads->id}";
            $clickKey = "ads_click_count:{$ads->id}";

            $pendingViews = $this->getCounterValue($viewKey);
            $pendingClicks = $this->getCounterValue($clickKey);

            // Sync jika ada pending atau jika dipaksa
            if ($pendingViews > 0 || $pendingClicks > 0 || $this->option('force')) {
                try {
                    // Update database
                    if ($pendingViews > 0) {
                        $ads->increment('total_view', $pendingViews);
                        $this->clearCounter($viewKey);
                        $totalViews += $pendingViews;
                    }

                    if ($pendingClicks > 0) {
                        $ads->increment('total_click', $pendingClicks);
                        $this->clearCounter($clickKey);
                        $totalClicks += $pendingClicks;
                    }

                    $syncedAds++;
                    $this->line("✓ Synced ads ID {$ads->id}: {$pendingViews} views, {$pendingClicks} clicks");
                } catch (\Exception $e) {
                    $this->error("✗ Failed to sync ads ID {$ads->id}: " . $e->getMessage());
                    Log::error("Failed to sync ads counters for ads {$ads->id}: " . $e->getMessage());
                }
            }
        }

        $this->info("Sync completed!");
        $this->table(['Metric', 'Count'], [
            ['Ads Synced', $syncedAds],
            ['Total Views Synced', $totalViews],
            ['Total Clicks Synced', $totalClicks],
        ]);

        return Command::SUCCESS;
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
