<?php

namespace App\Http\Controllers\Admin\Stats;

use App\Http\Controllers\Controller;
use App\Services\CounterR5Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * COUNTER R5 Statistics — Admin Panel
 *
 * Menampilkan laporan COUNTER R5 (TR dan IR) di admin panel jurnal.
 * Digunakan oleh Journal Manager/Editor untuk melihat statistik penggunaan.
 */
class CounterStatsController extends Controller
{
    public function __construct(
        private readonly CounterR5Service $counterService,
    ) {}

    /**
     * Halaman utama COUNTER R5 statistics.
     * GET /{journal}/settings/statistics/counter
     */
    public function index(Request $request)
    {
        $journal = current_journal();
        if (!$journal) {
            abort(404);
        }

        // Default: 12 bulan terakhir
        $endDate   = $request->query('end_date',   now()->format('Y-m'));
        $beginDate = $request->query('begin_date', now()->subMonths(11)->format('Y-m'));

        return view('journal.admin.stats.counter', compact('journal', 'beginDate', 'endDate'));
    }

    /**
     * Data TR (Title Report) untuk AJAX / export.
     * GET /{journal}/settings/statistics/counter/tr
     */
    public function titleReport(Request $request): JsonResponse
    {
        $journal = current_journal();
        if (!$journal) {
            abort(404);
        }

        $endDate   = $request->query('end_date',   now()->format('Y-m'));
        $beginDate = $request->query('begin_date', now()->subMonths(11)->format('Y-m'));

        $this->validateDateRange($beginDate, $endDate);

        $report = $this->counterService->titleReport($journal, $beginDate, $endDate);

        return response()->json($report);
    }

    /**
     * Data IR (Item Report) untuk AJAX / export.
     * GET /{journal}/settings/statistics/counter/ir
     */
    public function itemReport(Request $request): JsonResponse
    {
        $journal = current_journal();
        if (!$journal) {
            abort(404);
        }

        $endDate   = $request->query('end_date',   now()->format('Y-m'));
        $beginDate = $request->query('begin_date', now()->subMonths(11)->format('Y-m'));

        $this->validateDateRange($beginDate, $endDate);

        $report = $this->counterService->itemReport($journal, $beginDate, $endDate);

        return response()->json($report);
    }

    /**
     * Export IR sebagai CSV.
     * GET /{journal}/settings/statistics/counter/ir/csv
     */
    public function exportCsv(Request $request)
    {
        $journal = current_journal();
        if (!$journal) {
            abort(404);
        }

        $endDate   = $request->query('end_date',   now()->format('Y-m'));
        $beginDate = $request->query('begin_date', now()->subMonths(11)->format('Y-m'));

        $this->validateDateRange($beginDate, $endDate);

        $report = $this->counterService->itemReport($journal, $beginDate, $endDate);

        // Bangun CSV
        $rows   = [];
        $rows[] = ['Title', 'DOI', 'Total Views', 'Total Downloads', 'Total Requests'];

        foreach ($report['Report_Items'] as $item) {
            $rows[] = [
                $item['Item'],
                $item['DOI'] ?? '',
                $item['Metric_Types']['Unique_Item_Requests']      ?? 0,
                $item['Metric_Types']['Total_Item_Investigations'] ?? 0,
                $item['Metric_Types']['Total_Item_Requests']       ?? 0,
            ];
        }

        $csv      = '';
        foreach ($rows as $row) {
            $csv .= implode(',', array_map(fn($v) => '"' . str_replace('"', '""', $v) . '"', $row)) . "\n";
        }

        $filename = "counter-ir-{$journal->slug}-{$beginDate}-{$endDate}.csv";

        return response($csv, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    private function validateDateRange(string $beginDate, string $endDate): void
    {
        if (!preg_match('/^\d{4}-\d{2}$/', $beginDate) || !preg_match('/^\d{4}-\d{2}$/', $endDate)) {
            abort(422, 'Date must be in YYYY-MM format');
        }
        if ($beginDate > $endDate) {
            abort(422, 'begin_date must be before or equal to end_date');
        }
    }
}
