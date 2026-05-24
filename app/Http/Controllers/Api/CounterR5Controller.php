<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Journal;
use App\Services\CounterR5Service;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * COUNTER R5 Statistics API Controller
 *
 * Menyediakan endpoint publik untuk laporan statistik COUNTER R5.
 * Digunakan oleh Scopus, Web of Science, dan agregator akademik lainnya.
 */
class CounterR5Controller extends Controller
{
    public function __construct(
        private readonly CounterR5Service $counterService,
    ) {}

    /**
     * TR — Title Report
     * GET /api/v1/counter/tr/{journal}
     *
     * Query params:
     *   begin_date: YYYY-MM (default: 12 bulan lalu)
     *   end_date:   YYYY-MM (default: bulan ini)
     */
    public function titleReport(Request $request, string $journalSlug): JsonResponse
    {
        $journal = $this->resolveJournal($journalSlug);

        [$beginDate, $endDate] = $this->resolveDateRange($request);

        $report = $this->counterService->titleReport($journal, $beginDate, $endDate);

        return response()->json($report)
            ->header('X-COUNTER-Release', '5')
            ->header('X-Report-ID', 'TR');
    }

    /**
     * IR — Item Report
     * GET /api/v1/counter/ir/{journal}
     *
     * Query params:
     *   begin_date: YYYY-MM (default: 12 bulan lalu)
     *   end_date:   YYYY-MM (default: bulan ini)
     */
    public function itemReport(Request $request, string $journalSlug): JsonResponse
    {
        $journal = $this->resolveJournal($journalSlug);

        [$beginDate, $endDate] = $this->resolveDateRange($request);

        $report = $this->counterService->itemReport($journal, $beginDate, $endDate);

        return response()->json($report)
            ->header('X-COUNTER-Release', '5')
            ->header('X-Report-ID', 'IR');
    }

    // ─── HELPERS ──────────────────────────────────────────────────────────────

    private function resolveJournal(string $slug): Journal
    {
        $journal = Journal::where('slug', $slug)->where('enabled', true)->first();
        if (!$journal) {
            abort(404, 'Journal not found');
        }
        return $journal;
    }

    /**
     * Resolve begin_date dan end_date dari query params.
     * Default: 12 bulan terakhir.
     *
     * @return array{0: string, 1: string}  [beginDate YYYY-MM, endDate YYYY-MM]
     */
    private function resolveDateRange(Request $request): array
    {
        $endDate   = $request->query('end_date',   now()->format('Y-m'));
        $beginDate = $request->query('begin_date', now()->subMonths(11)->format('Y-m'));

        // Validasi format YYYY-MM
        if (!preg_match('/^\d{4}-\d{2}$/', $beginDate)) {
            abort(422, 'begin_date must be in YYYY-MM format');
        }
        if (!preg_match('/^\d{4}-\d{2}$/', $endDate)) {
            abort(422, 'end_date must be in YYYY-MM format');
        }

        // Pastikan begin <= end
        if ($beginDate > $endDate) {
            abort(422, 'begin_date must be before or equal to end_date');
        }

        // Batasi range maksimal 24 bulan untuk mencegah query berat
        $begin = Carbon::createFromFormat('Y-m', $beginDate);
        $end   = Carbon::createFromFormat('Y-m', $endDate);
        if ($begin->diffInMonths($end) > 24) {
            abort(422, 'Date range cannot exceed 24 months');
        }

        return [$beginDate, $endDate];
    }
}
