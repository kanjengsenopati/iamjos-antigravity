<?php

namespace App\Http\Controllers\Api\V1;

use Carbon\Carbon;
use App\Models\HomeAds;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

class HomeAdsController extends Controller
{
    /**
     * Increment view counter for ads
     */
    public function incrementView(Request $request, $id)
    {
        try {
            // Validasi ads exists dan aktif
            $ads = HomeAds::where('id', $id)
                ->where('is_active', true)
                ->where('start_date', '<=', now())
                ->where('end_date', '>=', now())
                ->first();

            if (!$ads) {
                return $this->notFoundResponse('Iklan tidak ditemukan atau tidak aktif');
            }

            // Gunakan Redis/Cache untuk counter yang ringan
            $redisKey = "ads_view_count:{$id}";
            $currentCount = $this->incrementCounter($redisKey);

            // Set TTL 24 jam untuk batch update
            if ($currentCount == 1) {
                $this->setCacheExpiry($redisKey, 86400);
            }            // Batch update ke database setiap 10 views untuk mengurangi load DB
            if ($currentCount % 10 == 0) {
                $this->batchUpdateViewCount($id, 10);
            }

            return $this->postSuccessResponse("Berhasil menghitung view", [
                'ads_id' => $id
            ]);
        } catch (\Exception $e) {
            return $this->failedResponse('Gagal menghitung view', null, 500);
        }
    }

    /**
     * Increment click counter for ads
     */
    public function incrementClick(Request $request, $id)
    {
        try {
            // Validasi ads exists dan aktif
            $ads = HomeAds::where('id', $id)
                ->where('is_active', true)
                ->where('start_date', '<=', now())
                ->where('end_date', '>=', now())
                ->first();

            if (!$ads) {
                return $this->notFoundResponse('Iklan tidak ditemukan atau tidak aktif');
            }

            // Gunakan Redis/Cache untuk counter yang ringan
            $redisKey = "ads_click_count:{$id}";
            $currentCount = $this->incrementCounter($redisKey);

            // Set TTL 24 jam untuk batch update
            if ($currentCount == 1) {
                $this->setCacheExpiry($redisKey, 86400);
            }            // Batch update ke database setiap 5 clicks
            if ($currentCount % 5 == 0) {
                $this->batchUpdateClickCount($id, 5);
            }
            return $this->postSuccessResponse("Berhasil menghitung klik", [
                'ads_id' => $id,
                'redirect_url' => $ads->link
            ]);
        } catch (\Exception $e) {
            return $this->failedResponse('Gagal menghitung klik', null, 500);
        }
    }

    /**
     * Batch update view count ke database
     */
    private function batchUpdateViewCount($adsId, $count)
    {
        try {
            HomeAds::where('id', $adsId)->increment('total_view', $count);

            // Reset counter setelah update
            $redisKey = "ads_view_count:{$adsId}";
            $remainingCount = $this->getCounter($redisKey);
            if ($remainingCount >= $count) {
                $newCount = max(0, $remainingCount - $count);
                Cache::put($redisKey, $newCount, 86400);
            }
        } catch (\Exception $e) {
            // Log error tapi jangan throw exception agar tidak mengganggu API
            Log::error("Failed to batch update view count for ads {$adsId}: " . $e->getMessage());
        }
    }

    /**
     * Batch update click count ke database
     */
    private function batchUpdateClickCount($adsId, $count)
    {
        try {
            HomeAds::where('id', $adsId)->increment('total_click', $count);

            // Reset counter setelah update
            $redisKey = "ads_click_count:{$adsId}";
            $remainingCount = $this->getCounter($redisKey);
            if ($remainingCount >= $count) {
                $newCount = max(0, $remainingCount - $count);
                Cache::put($redisKey, $newCount, 86400);
            }
        } catch (\Exception $e) {
            // Log error tapi jangan throw exception agar tidak mengganggu API
            Log::error("Failed to batch update click count for ads {$adsId}: " . $e->getMessage());
        }
    }

    /**
     * Get detailed statistics for specific ads
     */

    public function getStatistics($id)
    {
        try {
            $ads = HomeAds::findOrFail($id);

            $pendingViews  = $this->getCounter("ads_view_count:{$id}");
            $pendingClicks = $this->getCounter("ads_click_count:{$id}");

            $totalViews  = (int) $ads->total_view  + (int) $pendingViews;
            $totalClicks = (int) $ads->total_click + (int) $pendingClicks;

            $ctr = $totalViews > 0 ? round(($totalClicks / $totalViews) * 100, 2) : 0;

            // --- SAFETY PARSE: handle string/null jadi Carbon atau null
            $start = $ads->start_date ? Carbon::parse($ads->start_date)->startOfDay() : null;
            $end   = $ads->end_date   ? Carbon::parse($ads->end_date)->endOfDay()   : null;

            // daysRemaining: selisih dari hari ini ke end (signed). 
            // Jika mau inklusif hari ini, tambahkan +1 saat positif.
            $daysRemaining = $end ? Carbon::now()->startOfDay()->diffInDays($end, false) : null;

            // totalDays kampanye (inklusif kedua ujung: +1)
            $totalDays = ($start && $end) ? $start->diffInDays($end) + 1 : 0;

            return $this->getSuccessResponse([
                'ads' => [
                    'id'         => $ads->id,
                    'media_type' => $ads->media_type,
                    'media_url'  => $ads->media_url,
                    'link'       => $ads->link,
                    'is_active'  => (bool) $ads->is_active,
                    'start_date' => $ads->start_date,
                    'end_date'   => $ads->end_date,
                    'created_at' => $ads->created_at,
                ],
                'statistics' => [
                    'total_views'         => $totalViews,
                    'total_clicks'        => $totalClicks,
                    'pending_views'       => (int) $pendingViews,
                    'pending_clicks'      => (int) $pendingClicks,
                    'ctr_percentage'      => $ctr,
                    'days_remaining'      => $daysRemaining,
                    'total_campaign_days' => $totalDays,
                    'avg_views_per_day'   => $totalDays > 0 ? round($totalViews / $totalDays, 2) : 0,
                    'avg_clicks_per_day'  => $totalDays > 0 ? round($totalClicks / $totalDays, 2) : 0,
                ]
            ]);
        } catch (\Exception $e) {
            return $this->failedResponse('Gagal mengambil statistik iklan', null, 500);
        }
    }

    /**
     * Helper methods untuk Redis/Cache fallback
     */
    private function incrementCounter($key)
    {
        try {
            // Coba gunakan Redis dulu
            if (extension_loaded('redis') && config('database.redis.default')) {
                return Redis::incr($key);
            }
        } catch (\Exception $e) {
            Log::debug("Redis not available, falling back to Cache: " . $e->getMessage());
        }

        // Fallback ke Cache driver
        $current = Cache::get($key, 0);
        $new = $current + 1;
        Cache::put($key, $new, 86400); // 24 hours
        return $new;
    }

    private function getCounter($key)
    {
        try {
            // Coba gunakan Redis dulu
            if (extension_loaded('redis') && config('database.redis.default')) {
                return Redis::get($key) ?? 0;
            }
        } catch (\Exception $e) {
            Log::debug("Redis not available, falling back to Cache: " . $e->getMessage());
        }

        // Fallback ke Cache driver
        return Cache::get($key, 0);
    }

    private function setCacheExpiry($key, $seconds)
    {
        try {
            // Coba gunakan Redis dulu
            if (extension_loaded('redis') && config('database.redis.default')) {
                Redis::expire($key, $seconds);
                return;
            }
        } catch (\Exception $e) {
            Log::debug("Redis not available for expiry: " . $e->getMessage());
        }

        // Cache Laravel sudah auto-expire dengan put()
    }
}
