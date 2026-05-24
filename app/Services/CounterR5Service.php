<?php

namespace App\Services;

use App\Models\Journal;
use App\Models\Submission;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;

/**
 * COUNTER R5 Statistics Service
 *
 * Menghasilkan laporan statistik sesuai standar COUNTER Release 5.
 * Referensi: https://www.projectcounter.org/code-of-practice-five-sections/
 *
 * Dua laporan utama:
 * - TR (Title Report)  — statistik agregat per jurnal per bulan
 * - IR (Item Report)   — statistik per artikel per bulan
 */
class CounterR5Service
{
    private const PLATFORM = 'IAMJOS';

    /**
     * TR — Title Report
     * Statistik agregat seluruh artikel dalam satu jurnal, dikelompokkan per bulan.
     *
     * @param  Journal $journal
     * @param  string  $beginDate  Format: YYYY-MM
     * @param  string  $endDate    Format: YYYY-MM
     * @return array
     */
    public function titleReport(Journal $journal, string $beginDate, string $endDate): array
    {
        [$begin, $end] = $this->parseDateRange($beginDate, $endDate);

        // Ambil semua submission_id milik jurnal ini
        $submissionIds = Submission::where('journal_id', $journal->id)
            ->where('status', Submission::STATUS_PUBLISHED)
            ->pluck('id');

        if ($submissionIds->isEmpty()) {
            return $this->buildReport('TR', 'Title Report', $journal, $beginDate, $endDate, []);
        }

        // Agregasi per bulan
        $rows = DB::table('article_metrics')
            ->selectRaw("
                type,
                {$this->dateToYearMonth('date')} AS period_month,
                COUNT(*) AS total
            ")
            ->whereIn('submission_id', $submissionIds)
            ->whereBetween('date', [$begin->toDateString(), $end->toDateString()])
            ->groupByRaw("type, {$this->dateToYearMonth('date')}")
            ->orderByRaw("{$this->dateToYearMonth('date')}")
            ->get();

        // Bangun performances per bulan
        $monthlyData = [];
        foreach ($rows as $row) {
            $monthlyData[$row->period_month][$row->type] = (int) $row->total;
        }

        $totalViews     = $rows->where('type', 'view')->sum('total');
        $totalDownloads = $rows->where('type', 'download')->sum('total');
        $totalRequests  = $totalViews + $totalDownloads;

        $performances = $this->buildMonthlyPerformances($monthlyData, $begin, $end);

        $reportItem = [
            'Title'                   => $journal->name,
            'Publisher'               => $journal->publisher ?? $journal->name,
            'Platform'                => self::PLATFORM,
            'Print_ISSN'              => $journal->issn_print ?? '',
            'Online_ISSN'             => $journal->issn_online ?? '',
            'Metric_Types'            => [
                'Total_Item_Requests'     => $totalRequests,
                'Unique_Item_Requests'    => $totalViews,    // views = abstract/landing page
                'Total_Item_Investigations' => $totalDownloads, // downloads = full-text
            ],
            'Reporting_Period_Total'  => $totalRequests,
            'Performances'            => $performances,
        ];

        return $this->buildReport('TR', 'Title Report', $journal, $beginDate, $endDate, [$reportItem]);
    }

    /**
     * IR — Item Report
     * Statistik per artikel dalam satu jurnal, dikelompokkan per bulan.
     *
     * @param  Journal $journal
     * @param  string  $beginDate  Format: YYYY-MM
     * @param  string  $endDate    Format: YYYY-MM
     * @return array
     */
    public function itemReport(Journal $journal, string $beginDate, string $endDate): array
    {
        [$begin, $end] = $this->parseDateRange($beginDate, $endDate);

        // Ambil semua artikel published beserta metadata
        $submissions = Submission::where('journal_id', $journal->id)
            ->where('status', Submission::STATUS_PUBLISHED)
            ->with(['currentPublication'])
            ->get()
            ->keyBy('id');

        if ($submissions->isEmpty()) {
            return $this->buildReport('IR', 'Item Report', $journal, $beginDate, $endDate, []);
        }

        // Agregasi per submission per bulan
        $rows = DB::table('article_metrics')
            ->selectRaw("
                submission_id,
                type,
                {$this->dateToYearMonth('date')} AS period_month,
                COUNT(*) AS total
            ")
            ->whereIn('submission_id', $submissions->keys())
            ->whereBetween('date', [$begin->toDateString(), $end->toDateString()])
            ->groupByRaw("submission_id, type, {$this->dateToYearMonth('date')}")
            ->orderByRaw("submission_id, {$this->dateToYearMonth('date')}")
            ->get()
            ->groupBy('submission_id');

        $reportItems = [];

        foreach ($submissions as $submissionId => $submission) {
            $pub = $submission->currentPublication;
            if (!$pub) {
                continue;
            }

            $submissionRows = $rows->get($submissionId, collect());

            // Bangun monthly data untuk submission ini
            $monthlyData = [];
            foreach ($submissionRows as $row) {
                $monthlyData[$row->period_month][$row->type] = (int) $row->total;
            }

            $totalViews     = $submissionRows->where('type', 'view')->sum('total');
            $totalDownloads = $submissionRows->where('type', 'download')->sum('total');
            $totalRequests  = $totalViews + $totalDownloads;

            $performances = $this->buildMonthlyPerformances($monthlyData, $begin, $end);

            $itemIds = [
                ['Type' => 'Proprietary', 'Value' => (string) $submission->seq_id],
            ];
            if (!empty($pub->doi)) {
                $itemIds[] = ['Type' => 'DOI', 'Value' => $pub->doi];
            }

            $reportItems[] = [
                'Item'                    => $pub->title ?? $submission->title,
                'Publisher'               => $journal->publisher ?? $journal->name,
                'Platform'                => self::PLATFORM,
                'DOI'                     => $pub->doi ?? '',
                'Item_ID'                 => $itemIds,
                'Metric_Types'            => [
                    'Total_Item_Requests'       => $totalRequests,
                    'Unique_Item_Requests'       => $totalViews,
                    'Total_Item_Investigations'  => $totalDownloads,
                ],
                'Reporting_Period_Total'  => $totalRequests,
                'Performances'            => $performances,
            ];
        }

        // Urutkan berdasarkan total requests descending
        usort($reportItems, fn($a, $b) => $b['Reporting_Period_Total'] <=> $a['Reporting_Period_Total']);

        return $this->buildReport('IR', 'Item Report', $journal, $beginDate, $endDate, $reportItems);
    }

    // ─── HELPERS ──────────────────────────────────────────────────────────────

    /**
     * Parse begin_date dan end_date dari format YYYY-MM ke Carbon.
     * @return array{0: Carbon, 1: Carbon}
     */
    private function parseDateRange(string $beginDate, string $endDate): array
    {
        $begin = Carbon::createFromFormat('Y-m', $beginDate)->startOfMonth();
        $end   = Carbon::createFromFormat('Y-m', $endDate)->endOfMonth();
        return [$begin, $end];
    }

    /**
     * Ekspresi SQL untuk mengekstrak YYYY-MM dari kolom date.
     * Kompatibel dengan PostgreSQL (production) dan SQLite (testing).
     */
    private function dateToYearMonth(string $column): string
    {
        $driver = DB::getDriverName();

        return match ($driver) {
            'pgsql'  => "TO_CHAR({$column}, 'YYYY-MM')",
            'sqlite' => "strftime('%Y-%m', {$column})",
            'mysql', 'mariadb' => "DATE_FORMAT({$column}, '%Y-%m')",
            default  => "strftime('%Y-%m', {$column})",
        };
    }

    /**
     * Bangun array performances per bulan sesuai format COUNTER R5.
     * Mengisi bulan yang tidak ada data dengan count 0.
     */
    private function buildMonthlyPerformances(array $monthlyData, Carbon $begin, Carbon $end): array
    {
        $performances = [];

        // Iterasi setiap bulan dalam range
        $period = CarbonPeriod::create($begin->copy()->startOfMonth(), '1 month', $end->copy()->startOfMonth());

        foreach ($period as $month) {
            $monthKey   = $month->format('Y-m');
            $monthBegin = $month->copy()->startOfMonth()->toDateString();
            $monthEnd   = $month->copy()->endOfMonth()->toDateString();

            $views     = $monthlyData[$monthKey]['view'] ?? 0;
            $downloads = $monthlyData[$monthKey]['download'] ?? 0;
            $total     = $views + $downloads;

            $performances[] = [
                'Period'    => [
                    'Begin_Date' => $monthBegin,
                    'End_Date'   => $monthEnd,
                ],
                'Instances' => [
                    ['Metric_Type' => 'Total_Item_Requests',       'Count' => $total],
                    ['Metric_Type' => 'Unique_Item_Requests',       'Count' => $views],
                    ['Metric_Type' => 'Total_Item_Investigations',  'Count' => $downloads],
                ],
            ];
        }

        return $performances;
    }

    /**
     * Bangun struktur laporan COUNTER R5 lengkap.
     */
    private function buildReport(
        string  $reportId,
        string  $reportName,
        Journal $journal,
        string  $beginDate,
        string  $endDate,
        array   $reportItems
    ): array {
        return [
            'Report_Header' => [
                'Report_Name'       => $reportName,
                'Report_ID'         => $reportId,
                'Release'           => '5',
                'Institution_Name'  => $journal->name,
                'Institution_ID'    => [
                    ['Type' => 'Proprietary', 'Value' => $journal->slug],
                ],
                'Reporting_Period'  => [
                    'Begin_Date' => $beginDate . '-01',
                    'End_Date'   => Carbon::createFromFormat('Y-m', $endDate)->endOfMonth()->toDateString(),
                ],
                'Created'           => now()->toDateString(),
                'Created_By'        => self::PLATFORM,
            ],
            'Report_Items' => $reportItems,
        ];
    }
}
