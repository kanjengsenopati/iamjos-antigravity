<?php

namespace App\Http\Controllers\Admin\Stats;

use App\Http\Controllers\Controller;
use App\Models\ArticleMetric;
use App\Models\Submission;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;

class ArticleStatsController extends Controller
{
    protected const DATE_FORMAT_DAILY = "DATE_FORMAT(date, '%Y-%m-%d')";

    /**
     * Show the statistics dashboard page
     */
    public function index()
    {
        return view('manager.statistics.articles');
    }

    /**
     * Get statistics data as JSON for AJAX requests
     */
    public function getData(Request $request)
    {
        $journal = current_journal();
        $journalId = $journal?->id;

        $start = $request->get('start', now()->subDays(13)->toDateString()); // Default 14 days (StatCounter style)
        $end = $request->get('end', now()->toDateString());
        $granularity = $request->get('granularity', 'daily');

        $driver = \Illuminate\Support\Facades\DB::connection()->getDriverName();
        if ($driver === 'pgsql') {
            $dateFormatDaily = "TO_CHAR(date, 'YYYY-MM-DD')";
        } elseif ($driver === 'sqlite') {
            $dateFormatDaily = "strftime('%Y-%m-%d', date)";
        } else {
            $dateFormatDaily = "DATE_FORMAT(date, '%Y-%m-%d')";
        }

        // 1. KPI DATA
        $kpiViews = ArticleMetric::where('type', ArticleMetric::TYPE_VIEW)
            ->when($journalId, fn($q) => $q->whereHas('submission', fn($s) => $s->where('journal_id', $journalId)))
            ->whereBetween('date', [$start, $end])
            ->count();

        $kpiDownloads = ArticleMetric::where('type', ArticleMetric::TYPE_DOWNLOAD)
            ->when($journalId, fn($q) => $q->whereHas('submission', fn($s) => $s->where('journal_id', $journalId)))
            ->whereBetween('date', [$start, $end])
            ->count();

        // Distinct Visitors (Distinct IPs)
        $kpiVisitors = ArticleMetric::when($journalId, fn($q) => $q->whereHas('submission', fn($s) => $s->where('journal_id', $journalId)))
            ->whereBetween('date', [$start, $end])
            ->whereNotNull('ip_address')
            ->where('ip_address', '!=', '')
            ->distinct('ip_address')
            ->count('ip_address');

        // Sessions (Distinct IP per day)
        $kpiSessions = ArticleMetric::when($journalId, fn($q) => $q->whereHas('submission', fn($s) => $s->where('journal_id', $journalId)))
            ->whereBetween('date', [$start, $end])
            ->whereNotNull('ip_address')
            ->where('ip_address', '!=', '')
            ->selectRaw("COUNT(DISTINCT CONCAT(ip_address, '_', date)) as total")
            ->value('total') ?? $kpiVisitors;

        // New Visitors (IPs first seen within the date range)
        $firstVisitsSub = ArticleMetric::selectRaw('ip_address, MIN(date) as min_date')
            ->when($journalId, fn($q) => $q->whereHas('submission', fn($s) => $s->where('journal_id', $journalId)))
            ->whereNotNull('ip_address')
            ->where('ip_address', '!=', '')
            ->groupBy('ip_address');

        $kpiNewVisitors = \Illuminate\Support\Facades\DB::table(\Illuminate\Support\Facades\DB::raw("({$firstVisitsSub->toSql()}) as fv"))
            ->mergeBindings($firstVisitsSub->getQuery())
            ->whereBetween('min_date', [$start, $end])
            ->count();

        // Top Country
        $topCountry = ArticleMetric::selectRaw('country_code, count(*) as total')
            ->when($journalId, fn($q) => $q->whereHas('submission', fn($s) => $s->where('journal_id', $journalId)))
            ->whereBetween('date', [$start, $end])
            ->whereNotNull('country_code')
            ->where('country_code', '!=', '')
            ->groupBy('country_code')
            ->orderByDesc('total')
            ->first();

        // Busiest Day
        $busiestDay = ArticleMetric::selectRaw('date, count(*) as total')
            ->when($journalId, fn($q) => $q->whereHas('submission', fn($s) => $s->where('journal_id', $journalId)))
            ->whereBetween('date', [$start, $end])
            ->groupBy('date')
            ->orderByDesc('total')
            ->first();

        // 2. CHART DATA (Multi-metric: Views, Sessions, Visitors, New Visitors, Downloads)
        $chartData = $this->getChartData($journalId, $start, $end, $granularity);

        // 3. MAP DATA (Format for jsVectorMap: { "ID": count, "US": count })
        $mapData = ArticleMetric::where('type', ArticleMetric::TYPE_VIEW)
            ->when($journalId, fn($q) => $q->whereHas('submission', fn($s) => $s->where('journal_id', $journalId)))
            ->whereBetween('date', [$start, $end])
            ->whereNotNull('country_code')
            ->where('country_code', '!=', '')
            ->selectRaw('country_code, count(*) as total')
            ->groupBy('country_code')
            ->orderByDesc('total')
            ->limit(50)
            ->pluck('total', 'country_code')
            ->toArray();

        // 4. TOP ARTICLES TABLE
        $tableData = Submission::with(['authors', 'section'])
            ->where('status', Submission::STATUS_PUBLISHED)
            ->when($journalId, fn($q) => $q->where('journal_id', $journalId))
            ->withCount([
                'articleMetrics as views_count' => function ($query) use ($start, $end) {
                    $query->where('type', ArticleMetric::TYPE_VIEW)
                        ->whereBetween('date', [$start, $end]);
                },
                'articleMetrics as downloads_count' => function ($query) use ($start, $end) {
                    $query->where('type', ArticleMetric::TYPE_DOWNLOAD)
                        ->whereBetween('date', [$start, $end]);
                }
            ])
            ->orderByDesc('views_count')
            ->take(20)
            ->get()
            ->map(function ($submission) use ($start, $end, $dateFormatDaily) {
                // Get sparkline data (daily views)
                $sparklineData = ArticleMetric::selectRaw($dateFormatDaily . " as day, count(*) as count")
                    ->where('submission_id', $submission->id)
                    ->where('type', ArticleMetric::TYPE_VIEW)
                    ->whereBetween('date', [$start, $end])
                    ->groupByRaw($dateFormatDaily)
                    ->orderByRaw($dateFormatDaily)
                    ->pluck('count', 'day')
                    ->toArray();

                // Fill missing days with 0 and sample for display
                $period = CarbonPeriod::create(Carbon::parse($start), '1 day', Carbon::parse($end));
                $sparkline = [];
                foreach ($period as $date) {
                    $key = $date->format('Y-m-d');
                    $sparkline[] = (int) ($sparklineData[$key] ?? 0);
                }

                // Sample sparkline (max 20 bars)
                $step = max(1, ceil(count($sparkline) / 20));
                $sampledSparkline = [];
                for ($i = 0; $i < count($sparkline); $i += $step) {
                    $sampledSparkline[] = $sparkline[$i];
                }

                return [
                    'id' => $submission->id,
                    'title' => $submission->title,
                    'slug' => $submission->slug,
                    'url' => route('journal.submissions.show', ['journal' => current_journal()->slug, 'submission' => $submission->slug]),
                    'author' => $submission->authors->pluck('last_name')->join(', ') ?: 'Unknown',
                    'section' => $submission->section->name ?? 'Uncategorized',
                    'views' => (int) $submission->views_count,
                    'downloads' => (int) $submission->downloads_count,
                    'sparkline' => $sampledSparkline,
                ];
            });

        // 5. RECENT ACTIVITY STREAM (StatCounter recent activity log)
        $recentActivity = ArticleMetric::with(['submission'])
            ->when($journalId, fn($q) => $q->whereHas('submission', fn($s) => $s->where('journal_id', $journalId)))
            ->orderByDesc('id')
            ->take(25)
            ->get()
            ->map(function ($metric) {
                $maskedIp = $metric->ip_address ? preg_replace('/(\d+)\.(\d+)\.\d+\.\d+/', '$1.$2.xxx.xxx', $metric->ip_address) : '180.252.xxx.xxx';
                return [
                    'id' => $metric->id,
                    'article_title' => $metric->submission?->title ?? 'Article #' . substr($metric->submission_id, 0, 8),
                    'article_url' => $metric->submission ? route('journal.submissions.show', ['journal' => current_journal()->slug, 'submission' => $metric->submission->slug]) : '#',
                    'type' => $metric->type,
                    'ip_address' => $maskedIp,
                    'country_code' => $metric->country_code ?: 'ID',
                    'country_name' => $this->getCountryName($metric->country_code ?: 'ID'),
                    'city' => $metric->city ?: 'Unknown',
                    'date' => Carbon::parse($metric->date)->format('M d, Y'),
                    'time_ago' => $metric->created_at ? $metric->created_at->diffForHumans() : Carbon::parse($metric->date)->diffForHumans(),
                ];
            });

        return response()->json([
            'kpi' => [
                'views' => $kpiViews,
                'downloads' => $kpiDownloads,
                'visitors' => $kpiVisitors,
                'sessions' => $kpiSessions,
                'new_visitors' => $kpiNewVisitors,
                'top_country' => $topCountry ? [
                    'code' => $topCountry->country_code,
                    'name' => $this->getCountryName($topCountry->country_code),
                    'total' => $topCountry->total,
                ] : null,
                'busiest_day' => $busiestDay ? [
                    'date' => $busiestDay->date,
                    'formatted' => Carbon::parse($busiestDay->date)->format('M d, Y'),
                    'total' => $busiestDay->total,
                ] : null,
            ],
            'chart' => $chartData,
            'map' => $mapData,
            'table' => $tableData,
            'recent_activity' => $recentActivity,
        ]);
    }

    /**
     * Get chart data with granularity support for 5 StatCounter metrics:
     * Page Views, Sessions, Visitors, New Visitors, Downloads
     */
    protected function getChartData($journalId, $start, $end, $granularity = 'daily')
    {
        $driver = \Illuminate\Support\Facades\DB::connection()->getDriverName();
        if ($driver === 'pgsql') {
            $dateFormat = match ($granularity) {
                'weekly' => "TO_CHAR(date, 'IYYY-IW')",
                'yearly' => "TO_CHAR(date, 'YYYY')",
                'monthly' => "TO_CHAR(date, 'YYYY-MM')",
                default => "TO_CHAR(date, 'YYYY-MM-DD')",
            };
        } elseif ($driver === 'sqlite') {
            $dateFormat = match ($granularity) {
                'weekly' => "strftime('%Y-%W', date)",
                'yearly' => "strftime('%Y', date)",
                'monthly' => "strftime('%Y-%m', date)",
                default => "strftime('%Y-%m-%d', date)",
            };
        } else {
            $dateFormat = match ($granularity) {
                'weekly' => "DATE_FORMAT(date, '%Y-%u')",
                'yearly' => "DATE_FORMAT(date, '%Y')",
                'monthly' => "DATE_FORMAT(date, '%Y-%m')",
                default => "DATE_FORMAT(date, '%Y-%m-%d')",
            };
        }

        // Generate all periods in range
        $periods = $this->generatePeriods($start, $end, $granularity);

        // Raw Views & Downloads
        $rawData = ArticleMetric::selectRaw("{$dateFormat} as period, type, count(*) as count")
            ->when($journalId, fn($q) => $q->whereHas('submission', fn($s) => $s->where('journal_id', $journalId)))
            ->whereBetween('date', [$start, $end])
            ->groupByRaw("{$dateFormat}, type")
            ->orderByRaw($dateFormat)
            ->get();

        // Visitors (Distinct IP per period)
        $visitorsRaw = ArticleMetric::selectRaw("{$dateFormat} as period, COUNT(DISTINCT ip_address) as count")
            ->when($journalId, fn($q) => $q->whereHas('submission', fn($s) => $s->where('journal_id', $journalId)))
            ->whereBetween('date', [$start, $end])
            ->whereNotNull('ip_address')
            ->where('ip_address', '!=', '')
            ->groupByRaw($dateFormat)
            ->pluck('count', 'period')
            ->toArray();

        // Sessions (Distinct IP per day grouped by period)
        $sessionsRaw = ArticleMetric::selectRaw("{$dateFormat} as period, COUNT(DISTINCT CONCAT(ip_address, '_', date)) as count")
            ->when($journalId, fn($q) => $q->whereHas('submission', fn($s) => $s->where('journal_id', $journalId)))
            ->whereBetween('date', [$start, $end])
            ->whereNotNull('ip_address')
            ->where('ip_address', '!=', '')
            ->groupByRaw($dateFormat)
            ->pluck('count', 'period')
            ->toArray();

        // New Visitors per period (First seen date of IP in this period)
        $firstVisits = ArticleMetric::selectRaw('ip_address, MIN(date) as min_date')
            ->when($journalId, fn($q) => $q->whereHas('submission', fn($s) => $s->where('journal_id', $journalId)))
            ->whereNotNull('ip_address')
            ->where('ip_address', '!=', '')
            ->groupBy('ip_address')
            ->get();

        $newVisitorsMap = [];
        foreach ($firstVisits as $fv) {
            $fvDate = Carbon::parse($fv->min_date);
            if ($fvDate->toDateString() >= $start && $fvDate->toDateString() <= $end) {
                $pKey = match ($granularity) {
                    'weekly' => $fvDate->format('o-W'),
                    'yearly' => $fvDate->format('Y'),
                    'monthly' => $fvDate->format('Y-m'),
                    default => $fvDate->format('Y-m-d'),
                };
                $newVisitorsMap[$pKey] = ($newVisitorsMap[$pKey] ?? 0) + 1;
            }
        }

        // Initialize data arrays
        $viewsData = array_fill_keys($periods, 0);
        $downloadsData = array_fill_keys($periods, 0);
        $visitorsData = array_fill_keys($periods, 0);
        $sessionsData = array_fill_keys($periods, 0);
        $newVisitorsData = array_fill_keys($periods, 0);

        foreach ($rawData as $row) {
            $period = $row->period;
            if (!isset($viewsData[$period])) continue;

            if ($row->type === ArticleMetric::TYPE_VIEW) {
                $viewsData[$period] = (int) $row->count;
            } else {
                $downloadsData[$period] = (int) $row->count;
            }
        }

        foreach ($periods as $p) {
            $visitorsData[$p] = (int) ($visitorsRaw[$p] ?? 0);
            $sessionsData[$p] = (int) ($sessionsRaw[$p] ?? $visitorsData[$p]);
            $newVisitorsData[$p] = (int) ($newVisitorsMap[$p] ?? 0);
        }

        // StatCounter-style formatted label categories (e.g., "7 Tues", "8 Wed")
        $formattedCategories = array_map(function ($p) use ($granularity) {
            if ($granularity === 'daily') {
                $dt = Carbon::parse($p);
                return $dt->format('j D'); // e.g. "7 Tue"
            }
            return $p;
        }, array_values($periods));

        return [
            'raw_periods' => array_values($periods),
            'categories' => $formattedCategories,
            'views' => array_values($viewsData),
            'sessions' => array_values($sessionsData),
            'visitors' => array_values($visitorsData),
            'new_visitors' => array_values($newVisitorsData),
            'downloads' => array_values($downloadsData),
        ];
    }

    /**
     * Export statistics data as CSV file
     */
    public function exportCsv(Request $request)
    {
        $journal = current_journal();
        $journalId = $journal?->id;
        $start = $request->get('start', now()->subDays(13)->toDateString());
        $end = $request->get('end', now()->toDateString());

        $fileName = 'article_stats_' . $start . '_to_' . $end . '.csv';

        $metrics = ArticleMetric::with(['submission'])
            ->when($journalId, fn($q) => $q->whereHas('submission', fn($s) => $s->where('journal_id', $journalId)))
            ->whereBetween('date', [$start, $end])
            ->orderByDesc('date')
            ->get();

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename={$fileName}",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function () use ($metrics) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Date', 'Type', 'Article Title', 'Section', 'Country', 'City', 'IP Address (Masked)']);

            foreach ($metrics as $metric) {
                $maskedIp = $metric->ip_address ? preg_replace('/(\d+)\.(\d+)\.\d+\.\d+/', '$1.$2.xxx.xxx', $metric->ip_address) : '180.252.xxx.xxx';
                fputcsv($file, [
                    $metric->id,
                    $metric->date,
                    strtoupper($metric->type),
                    $metric->submission?->title ?? 'Unknown Article',
                    $metric->submission?->section?->name ?? 'Uncategorized',
                    $metric->country_code ?: 'ID',
                    $metric->city ?: 'Unknown',
                    $maskedIp,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Generate period labels based on granularity
     */
    protected function generatePeriods(string $start, string $end, string $granularity): array
    {
        $periods = [];
        $startDate = Carbon::parse($start);
        $endDate = Carbon::parse($end);

        if ($granularity === 'weekly') {
            $current = $startDate->copy()->startOfWeek();
            while ($current <= $endDate) {
                $periods[] = $current->format('o-W');
                $current->addWeek();
            }
            return $periods;
        }

        if ($granularity === 'yearly') {
            $current = $startDate->copy()->startOfYear();
            while ($current <= $endDate) {
                $periods[] = $current->format('Y');
                $current->addYear();
            }
            return $periods;
        }

        if ($granularity === 'daily') {
            $period = CarbonPeriod::create($startDate, '1 day', $endDate);
            foreach ($period as $date) {
                $periods[] = $date->format('Y-m-d');
            }
            return $periods;
        }

        // Monthly
        $current = $startDate->copy()->startOfMonth();
        while ($current <= $endDate) {
            $periods[] = $current->format('Y-m');
            $current->addMonth();
        }

        return $periods;
    }

    /**
     * Get country name from code
     */
    protected function getCountryName(string $code): string
    {
        $countries = [
            'ID' => 'Indonesia',
            'US' => 'United States',
            'MY' => 'Malaysia',
            'SG' => 'Singapore',
            'AU' => 'Australia',
            'GB' => 'United Kingdom',
            'JP' => 'Japan',
            'KR' => 'South Korea',
            'CN' => 'China',
            'IN' => 'India',
            'DE' => 'Germany',
            'FR' => 'France',
            'NL' => 'Netherlands',
            'CA' => 'Canada',
            'BR' => 'Brazil',
            'PH' => 'Philippines',
            'TH' => 'Thailand',
            'VN' => 'Vietnam',
            'PK' => 'Pakistan',
            'BD' => 'Bangladesh',
            'NG' => 'Nigeria',
            'EG' => 'Egypt',
            'ZA' => 'South Africa',
            'SA' => 'Saudi Arabia',
            'AE' => 'UAE',
            'TR' => 'Turkey',
            'RU' => 'Russia',
            'IT' => 'Italy',
            'ES' => 'Spain',
            'MX' => 'Mexico',
        ];

        return $countries[$code] ?? $code;
    }
}
