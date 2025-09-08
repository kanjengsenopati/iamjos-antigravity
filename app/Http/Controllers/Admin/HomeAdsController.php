<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\HomeAds;
use Carbon\CarbonInterface;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Cache;
use App\Http\Requests\Admin\HomeAdsRequest;

class HomeAdsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (request()->ajax()) {
            $data = HomeAds::latest();
            return DataTables::of($data)
                ->addColumn('statistics', function ($d) {
                    // Ambil statistik real-time untuk setiap ads
                    $pendingViews = $this->getCounterValue("ads_view_count:{$d->id}");
                    $pendingClicks = $this->getCounterValue("ads_click_count:{$d->id}");
                    $totalViews = $d->total_view + $pendingViews;
                    $totalClicks = $d->total_click + $pendingClicks;
                    $ctr = $totalViews > 0 ? round(($totalClicks / $totalViews) * 100, 1) : 0;

                    return "
                        <div class='text-center'>
                            <div class='fs-7 text-muted'>Views</div>
                            <div class='fw-bold text-primary'>" . number_format($totalViews) . "</div>
                            <div class='fs-7 text-muted mt-1'>Clicks</div>
                            <div class='fw-bold text-success'>" . number_format($totalClicks) . "</div>
                            <div class='fs-8 text-muted mt-1'>CTR: {$ctr}%</div>
                        </div>
                    ";
                })
                ->addColumn('date', function ($d) {
                    $parse = function ($v) {
                        if (!$v) return null;
                        return $v instanceof CarbonInterface ? $v->copy() : Carbon::parse($v);
                    };

                    $s = $parse($d->start_date);
                    $e = $parse($d->end_date);

                    $full  = fn(CarbonInterface $c) => $c->locale('id')->isoFormat('D MMMM YYYY');
                    $dOnly = fn(CarbonInterface $c) => $c->locale('id')->isoFormat('D');
                    $dm    = fn(CarbonInterface $c) => $c->locale('id')->isoFormat('D MMMM');

                    if ($s && $e) {
                        if ($s->isSameDay($e)) {
                            // 1 Agustus 2025
                            return $full($s);
                        }
                        if ($s->isSameMonth($e) && $s->isSameYear($e)) {
                            // 1–31 Agustus 2025
                            return $dOnly($s) . '–' . $full($e);
                        }
                        if ($s->isSameYear($e)) {
                            // 28 Agustus – 2 September 2025
                            return $dm($s) . ' – ' . $full($e);
                        }
                        // 28 Desember 2024 – 3 Januari 2025
                        return $full($s) . ' – ' . $full($e);
                    }

                    // Salah siji kosong
                    if ($s) return $full($s);
                    if ($e) return $full($e);

                    return '–';
                })
                ->addColumn('action', function ($data) {
                    $actionShow = route('home-ads.show', $data->id);
                    $actionEdit = route('home-ads.edit', $data->id);
                    $actionDelete = route('home-ads.destroy', $data->id);
                    return "<div class='d-flex justify-content-center'>" .
                        view('components.action.show', ['action' => $actionShow]) .
                        view('components.action.edit', ['action' => $actionEdit]) .
                        view('components.action.delete', ['action' => $actionDelete, 'id' => $data->id]) .
                        "</div>";
                })
                ->rawColumns(['action', 'statistics'])
                ->make(true);
        }
        return view('admins.home-ads.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admins.home-ads.create-edit');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(HomeAdsRequest $request)
    {
        $data = $request->validated();
        // deteksi media type apakah 'image' atau 'video'
        $data['media_type'] = $request->hasFile('media') ?
            ($request->file('media')->getClientMimeType() === 'video/mp4' ? 'video' : 'image') : 'image';
        // simpan data iklan
        if ($request->hasFile('media')) {
            $data['media_url'] = 'storage/' . $request->file('media')->store('home_ads', ['disk' => 'public']);
        }
        HomeAds::create($data);
        return redirect()->route('home-ads.index')->with('success', 'Iklan Berhasil ditambahkan');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $homeAds = HomeAds::findOrFail($id);

        // Ambil statistik real-time
        $statistics = $this->getAdsStatistics($homeAds);

        return view('admins.home-ads.show', compact('homeAds', 'statistics'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $homeAds = HomeAds::findOrFail($id);
        return view('admins.home-ads.create-edit', compact('homeAds'));
    }

    /**
     * Update the specified resource in storage.
     */

    public function update(HomeAdsRequest $request, HomeAds $homeAds)
    {
        $data = $request->validated();

        if ($request->hasFile('media')) {
            $file = $request->file('media');

            // Deteksi kaya versi store panjenengan
            $data['media_type'] = $file->getClientMimeType() === 'video/mp4' ? 'video' : 'image';

            // Hapus file lawas (yen ana)
            if (!empty($homeAds->media_url) && file_exists($homeAds->media_url)) {
                unlink($homeAds->media_url);
            }

            // Simpen file anyar
            $path = $file->store('home_ads', ['disk' => 'public']); // "home_ads/xxx"
            $data['media_url'] = 'storage/' . $path;
        } else {
            unset($data['media_url'], $data['media_type']);
        }

        $homeAds->update($data);

        return redirect()->route('home-ads.index')->with('success', 'Iklan berhasil diperbarui');
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $homeAds = HomeAds::findOrFail($id);
        if ($homeAds->media_url && file_exists($homeAds->media_url)) {
            unlink($homeAds->media_url);
        }
        $homeAds->delete();
        return redirect()->route('home-ads.index')->with('success', 'Iklan Berhasil dihapus');
    }

    /**
     * Get detailed statistics for ads
     */
    private function getAdsStatistics(HomeAds $ads): array
    {
        // Ambil pending counts dari Redis/Cache
        $pendingViews = $this->getCounterValue("ads_view_count:{$ads->id}");
        $pendingClicks = $this->getCounterValue("ads_click_count:{$ads->id}");

        // Total counts (DB + pending Redis)
        $totalViews = $ads->total_view + $pendingViews;
        $totalClicks = $ads->total_click + $pendingClicks;

        // Hitung CTR (Click Through Rate)
        $ctr = $totalViews > 0 ? round(($totalClicks / $totalViews) * 100, 2) : 0;

        // Hitung periode dan status
        $now = now();
        $isActive = $ads->is_active &&
            ($ads->start_date <= $now) &&
            ($ads->end_date >= $now);

        $daysRemaining = null;

        if ($ads->end_date) {
            // Selisih hari (bisa desimal), bertanda (negatif kalau sudah lewat)
            $diff = $now->diffInRealDays($ads->end_date, false);
            $daysRemaining = (int) round($diff);
        }

        $startDate = \Carbon\Carbon::parse($ads->start_date);
        $endDate   = \Carbon\Carbon::parse($ads->end_date);

        $totalDays = $startDate->diffInDays($endDate) + 1;


        $startDate = $ads->start_date ? \Carbon\Carbon::parse($ads->start_date) : null;

        $daysElapsed = 0;

        if ($startDate) {
            // Konversi selisih ke HARI (float), lalu bulatkan ke terdekat.
            $diffDaysFloat = $startDate->diffInSeconds($now) / 86400;
            $daysElapsed   = (int) round($diffDaysFloat) + 1; // +1 jika ingin hari mulai dihitung inklusif
        }

        // Batasi agar tidak melebihi total durasi
        if ($totalDays > 0) {
            $daysElapsed = min($daysElapsed, (int) $totalDays);
        }

        // Performance metrics
        $avgViewsPerDay = $daysElapsed > 0 ? round($totalViews / $daysElapsed, 2) : 0;
        $avgClicksPerDay = $daysElapsed > 0 ? round($totalClicks / $daysElapsed, 2) : 0;

        // Projected metrics
        $projectedViews = $totalDays > 0 && $avgViewsPerDay > 0 ?
            round($avgViewsPerDay * $totalDays) : 0;
        $projectedClicks = $totalDays > 0 && $avgClicksPerDay > 0 ?
            round($avgClicksPerDay * $totalDays) : 0;

        return [
            'total_views' => $totalViews,
            'total_clicks' => $totalClicks,
            'pending_views' => $pendingViews,
            'pending_clicks' => $pendingClicks,
            'ctr_percentage' => $ctr,
            'is_currently_active' => $isActive,
            'days_remaining' => $daysRemaining,
            'days_elapsed' => $daysElapsed,
            'total_campaign_days' => $totalDays,
            'avg_views_per_day' => $avgViewsPerDay,
            'avg_clicks_per_day' => $avgClicksPerDay,
            'projected_total_views' => $projectedViews,
            'projected_total_clicks' => $projectedClicks,
            'campaign_progress' => $totalDays > 0 ? round(($daysElapsed / $totalDays) * 100, 1) : 0,
        ];
    }

    /**
     * Helper method untuk mendapatkan counter value dengan fallback
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

        // Fallback ke Cache driver
        return Cache::get($key, 0);
    }
}
