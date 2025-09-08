<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use App\Models\Event;
use App\Models\Member;
use App\Models\Article;
use App\Models\Contact;
use App\Models\HomeAds;
use App\Models\ContactUs;
use App\Models\MediaCorner;
use App\Models\MeetingRoom;
use App\Models\HotelBooking;
use App\Models\MeetingVenue;
use App\Models\HonoraryCouncil;
use Illuminate\Support\Facades\DB;
use App\Models\RegionalCoordinator;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    public function index()
    {
        // --- Bound bulan ini & bulan lalu (akurat lintas tahun)
        $now        = now();
        $startThis  = $now->copy()->startOfMonth();
        $endThis    = $now->copy()->endOfMonth();
        $startLast  = $now->copy()->subMonthNoOverflow()->startOfMonth();
        $endLast    = $now->copy()->subMonthNoOverflow()->endOfMonth();

        // --- Cache ringan untuk angka total (opsional)
        $ttl = 60;
        $totalHotels   = Cache::remember('phri.total_hotels',   $ttl, fn() => \App\Models\MeetingVenue::count());
        $totalArticles = Cache::remember('phri.total_articles', $ttl, fn() => \App\Models\Article::where('is_active', true)->count());
        $totalEvents   = Cache::remember('phri.total_events',   $ttl, fn() => \App\Models\Event::where('is_active', true)->count());
        $totalVideos   = Cache::remember('phri.total_videos',   $ttl, fn() => \App\Models\MediaCorner::where('is_active', true)->count());

        // --- Delta per entitas (bulan ini vs bulan lalu)
        $hotelsThis  = $this->countInRange(\App\Models\MeetingVenue::class, $startThis, $endThis);
        $hotelsLast  = $this->countInRange(\App\Models\MeetingVenue::class, $startLast, $endLast);
        $hotelDelta  = $this->pctDelta($hotelsThis, $hotelsLast);

        $eventsThis  = $this->countInRange(\App\Models\Event::class, $startThis, $endThis);
        $eventsLast  = $this->countInRange(\App\Models\Event::class, $startLast, $endLast);
        $eventsDelta = $this->pctDelta($eventsThis, $eventsLast);

        $videosThis  = $this->countInRange(\App\Models\MediaCorner::class, $startThis, $endThis);
        $videosLast  = $this->countInRange(\App\Models\MediaCorner::class, $startLast, $endLast);
        $videosDelta = $this->pctDelta($videosThis, $videosLast);

        $articlesThis  = $this->countInRange(\App\Models\Article::class, $startThis, $endThis);
        $articlesLast  = $this->countInRange(\App\Models\Article::class, $startLast, $endLast);
        $articlesDelta = $this->pctDelta($articlesThis, $articlesLast);

        // --- Total “Pengurus/Anggota” (OR ter-grup) + delta
        $totalOrganization = \App\Models\Member::query()
            ->where(function ($q) {
                $q->whereHas('position')
                    ->orWhereHas('bppOrganization');
            })
            ->count();
        $totalCouncil  = \App\Models\HonoraryCouncil::count() + \App\Models\RegionalCoordinator::count();
        $totalMembers  = max($totalOrganization + $totalCouncil, 0);

        $orgThis = $this->countInRange(
            \App\Models\Member::class,
            $startThis,
            $endThis,
            null,
            fn($q) => $q->where(function ($w) {
                $w->whereHas('position')->orWhereHas('bppOrganization');
            })
        );
        $orgLast = $this->countInRange(
            \App\Models\Member::class,
            $startLast,
            $endLast,
            null,
            fn($q) => $q->where(function ($w) {
                $w->whereHas('position')->orWhereHas('bppOrganization');
            })
        );
        $councilThis   = $this->countInRange(\App\Models\HonoraryCouncil::class, $startThis, $endThis)
            +  $this->countInRange(\App\Models\RegionalCoordinator::class, $startThis, $endThis);
        $councilLast   = $this->countInRange(\App\Models\HonoraryCouncil::class, $startLast, $endLast)
            +  $this->countInRange(\App\Models\RegionalCoordinator::class, $startLast, $endLast);
        $membersDelta  = $this->pctDelta(max($orgThis + $councilThis, 0), max($orgLast + $councilLast, 0));

        // --- Kartu stats (field minimal)
        $stats = [
            [
                'label'    => 'Total Hotel Anggota',
                'value'    => $totalHotels,
                'delta'    => $hotelDelta['label'],
                'icon'     => 'bi-building',
                'positive' => $hotelDelta['positive'],
                'route'    => 'meeting-room.index',
            ],
            [
                'label'    => 'Total Pengurus',
                'value'    => $totalMembers,
                'delta'    => $membersDelta['label'],
                'icon'     => 'bi-people',
                'positive' => $membersDelta['positive'],
            ],
            [
                'label'    => 'Jumlah Event',
                'value'    => $totalEvents,
                'delta'    => $eventsDelta['label'],
                'icon'     => 'bi-calendar-event',
                'positive' => $eventsDelta['positive'],
                'route'    => 'event.index',
            ],
            [
                'label'    => 'Jumlah Artikel',
                'value'    => $totalArticles,
                'delta'    => $articlesDelta['label'],
                'icon'     => 'bi-journal-text',
                'positive' => $articlesDelta['positive'],
                'route'    => 'article.index',
            ],
            [
                'label'    => 'Jumlah Video',
                'value'    => $totalVideos,
                'delta'    => $videosDelta['label'],
                'icon'     => 'bi-camera-video',
                'positive' => $videosDelta['positive'],
                'route'    => 'media-corner.index',
            ],
        ];

        // --- Kota (distinct + urut + tanpa null)
        $cities = \App\Models\MeetingVenue::query()
            ->whereNotNull('city_name')
            ->select('city_name')->distinct()
            ->orderBy('city_name')
            ->pluck('city_name')
            ->prepend('Semua Kota')
            ->values()->all();

        // --- Hotel list: hindari N+1 dengan withCount
        $hotels = \App\Models\MeetingVenue::query()
            ->select('id', 'name', 'city_name', 'max_capacity')
            ->withCount(['meeting_rooms as rooms'])
            ->get()
            ->map(fn($h) => [
                'id'           => $h->id,
                'name'         => $h->name,
                'city'         => $h->city_name ?? 'N/A',
                'rooms'        => (int) $h->rooms,
                'max_capacity' => (int) ($h->max_capacity ?? 0),
            ])
            ->toArray();

        // --- Pesan summary (1 query) + list
        $agg = \App\Models\ContactUs::selectRaw('COUNT(*) as total, SUM(CASE WHEN is_read THEN 1 ELSE 0 END) as confirmed')->first();
        $total = (int) ($agg->total ?? 0);
        $confirmed = (int) ($agg->confirmed ?? 0);
        $pesanSummary = [
            'total'     => $total,
            'pending'   => max($total - $confirmed, 0),
            'confirmed' => $confirmed,
        ];
        $pesanList = \App\Models\ContactUs::query()
            ->latest()
            ->limit(10)
            ->get(['id', 'name', 'email', 'phone', 'message', 'is_read', 'created_at']);

        // --- Analytics Iklan: seri bulanan + KPI
        $labels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $views  = array_fill(1, 12, 0);
        $clicks = array_fill(1, 12, 0);

        $driver    = DB::getDriverName();                         // 'mysql' | 'pgsql' | ...
        $year      = $now->year;
        $monthExpr = $driver === 'pgsql' ? "EXTRACT(MONTH FROM created_at)" : "MONTH(created_at)";
        $yearExpr  = $driver === 'pgsql' ? "EXTRACT(YEAR FROM created_at)"  : "YEAR(created_at)";

        $rows = \App\Models\HomeAds::query()
            ->selectRaw("$monthExpr AS m,
                     SUM(COALESCE(total_view, 0))  AS v,
                     SUM(COALESCE(total_click, 0)) AS c")
            ->whereRaw("$yearExpr = ?", [$year])
            ->groupBy('m')
            ->orderBy('m')
            ->get();

        foreach ($rows as $r) {
            $m = (int) $r->m;
            if ($m >= 1 && $m <= 12) {
                $views[$m]  = (int) $r->v;
                $clicks[$m] = (int) $r->c;
            }
        }

        $adsSeries = [
            'labels'       => $labels,
            'views'        => array_values($views),
            'clicks'       => array_values($clicks),
        ];
        $adsSeries['total_views']  = array_sum($adsSeries['views']);
        $adsSeries['total_clicks'] = array_sum($adsSeries['clicks']);
        $adsSeries['ctr']          = $adsSeries['total_views'] > 0
            ? round(($adsSeries['total_clicks'] / $adsSeries['total_views']) * 100, 2)
            : 0.00;

        // --- Statistik per iklan (logo iklan / favicon)
        $ads = \App\Models\HomeAds::query()
            ->select('id', 'media_type', 'media_url', 'link', 'is_active', 'order', 'start_date', 'end_date', 'total_view', 'total_click')
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('start_date')->orWhere('start_date', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('end_date')->orWhere('end_date', '>=', now());
            })
            ->orderBy('order')
            ->get();

        $totalViews  = (int) $ads->sum('total_view');
        $totalClicks = (int) $ads->sum('total_click');

        $adsList = $ads->map(function ($ad) use ($totalViews, $totalClicks) {
            $host = null;
            if (!empty($ad->link)) {
                $host = parse_url($ad->link, PHP_URL_HOST) ?: null;
                if ($host && Str::startsWith($host, 'www.')) $host = substr($host, 4);
            }

            $logo = null;
            if ($ad->media_type === 'image' && !empty($ad->media_url)) {
                $logo = asset($ad->media_url);
            } elseif ($host) {
                $logo = "https://www.google.com/s2/favicons?domain={$host}&sz=64";
            }

            $views  = (int) $ad->total_view;
            $clicks = (int) $ad->total_click;

            return [
                'id'           => $ad->id,
                'name'         => $host ? Str::ucfirst($host) : "Iklan #{$ad->id}",
                'logo'         => $logo,
                'views'        => $views,
                'clicks'       => $clicks,
                'share_views'  => $totalViews  > 0 ? round(($views  / $totalViews)  * 100, 1) : 0.0,
                'share_clicks' => $totalClicks > 0 ? round(($clicks / $totalClicks) * 100, 1) : 0.0,
                'ctr'          => $views > 0 ? round(($clicks / $views) * 100, 2) : 0.0,
                'link'         => $ad->link ?? '#',
            ];
        })->toArray();

        $adsStats = [
            'total_views'  => $totalViews,
            'total_clicks' => $totalClicks,
            'ctr'          => $totalViews > 0 ? round(($totalClicks / $totalViews) * 100, 2) : 0.00,
        ];
        $isPasswordSafe = $this->isPasswordSafe(Auth::user()->password);

        return view('admins.dashboard.index', compact(
            'stats',
            'cities',
            'hotels',
            'pesanSummary',
            'pesanList',
            'adsSeries',
            'adsList',
            'adsStats',
            'isPasswordSafe'
        ));
    }

    /**
     * Hitung jumlah row di rentang waktu, optional filter kolom boolean aktif, dan optional scope (closure).
     *
     * @param class-string<\Illuminate\Database\Eloquent\Model> $modelClass
     * @param Carbon $start
     * @param Carbon $end
     * @param string|null $activeColumn
     * @param callable(\Illuminate\Database\Eloquent\Builder):void|null $scope
     */
    private function countInRange(
        string $modelClass,
        Carbon $start,
        Carbon $end,
        ?string $activeColumn = null,
        ?callable $scope = null
    ): int {
        /** @var \Illuminate\Database\Eloquent\Builder $q */
        $q = $modelClass::query()->whereBetween('created_at', [$start, $end]);

        if ($activeColumn) {
            $q->where($activeColumn, true);
        }
        if ($scope) {
            $scope($q); // terapkan whereHas / filter tambahan di sini
        }

        return (int) $q->count();
    }

    private function pctDelta(int $thisPeriod, int $lastPeriod): array
    {
        if ($lastPeriod > 0) {
            $delta = (($thisPeriod - $lastPeriod) / $lastPeriod) * 100;
        } else {
            $delta = $thisPeriod > 0 ? 100 : 0;
        }
        return [
            'value'    => $delta,
            'positive' => $delta >= 0,
            'label'    => ($delta >= 0 ? '+' : '') . number_format($delta, 1) . '%',
        ];
    }

    public function isPasswordSafe($password)
    {
        $datasets = ['12345678', '12345', '123', 'bismillah', 'admin123', 'admin', 'qwerty', 'password', 'welcome', '123abc', '123qwe', 'iloveyou', 'abc123', '123456789', '1234567', '1234', '123456', 'master', '696969', 'mustang', 'batman', 'anjing', 'sayang', 'cinta', 'kucing', 'indonesia', 'ganteng', 'cantik', '1234567890', 'qazwsx', '987654321', '1q2w3e4r', '123123', '555555'];

        foreach ($datasets as $dataset) {
            if (Hash::check($dataset, $password)) {
                return false;
            }
        }
        return true;
    }
}
