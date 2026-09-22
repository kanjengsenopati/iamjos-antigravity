<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Acron Middleware — OJS Acron Plugin + Built-in Job Runner Equivalent
 * 
 * Web-based pseudo-cron yang menjalankan DUA tugas utama:
 * 
 * 1. JOB PROCESSING — Memproses pending jobs dari tabel `jobs` (database queue)
 *    tanpa membutuhkan daemon/worker/terminal. Identik OJS 3.4+ `job_runner=On`.
 * 
 * 2. STATUS POLLING — Mengecek status DOI Crossref yang masih `submitted`
 *    via Crossref Works API dan Deposit API. Identik OJS `CrossrefInfoSender`.
 * 
 * Kompatibel dengan semua lingkungan server:
 * - Apache mod_php, PHP-FPM, FastCGI, LiteSpeed, Nginx
 * - Shared Hosting, VPS, Dedicated, Cloud
 * - TANPA Redis, TANPA Supervisor, TANPA terminal commands
 * 
 * Cara Kerja:
 * 1. Setiap request GET web masuk, middleware mengecek cache interval.
 * 2. Jika interval sudah lewat, middleware mendaftarkan eksekusi via app()->terminating().
 * 3. Eksekusi terjadi SETELAH response dikirim ke browser (non-blocking untuk user).
 * 4. Atomic lock mencegah race condition dari concurrent requests.
 */
class AcronMiddleware
{
    /**
     * Interval pemrosesan queue jobs dalam detik (60 detik).
     * Job baru akan diproses paling lambat 60 detik setelah di-dispatch.
     */
    private const JOB_PROCESS_INTERVAL = 60;

    /**
     * Interval pengecekan status DOI Crossref dalam detik (30 menit).
     */
    private const STATUS_CHECK_INTERVAL = 1800;

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Guard: Hanya proses request GET halaman web biasa
        if (!$this->shouldProcess($request)) {
            return $response;
        }

        // Task 1: Process pending queue jobs (setiap 60 detik)
        $this->registerJobProcessing();

        // Task 2: Check Crossref deposit status (setiap 30 menit)
        $this->registerCrossrefCheck();

        return $response;
    }

    /**
     * Tentukan apakah request ini layak memicu Acron.
     */
    private function shouldProcess(Request $request): bool
    {
        // Hanya GET request (jangan ganggu form submission POST/PUT/DELETE)
        if (!$request->isMethod('GET')) {
            return false;
        }

        // Abaikan AJAX/fetch requests
        if ($request->ajax() || $request->wantsJson()) {
            return false;
        }

        // Abaikan request asset statis dan API
        if ($request->is('build/*', 'assets/*', 'storage/*', 'api/*', '*/favicon.ico')) {
            return false;
        }

        return true;
    }

    // =========================================================================
    // TASK 1: WEB-TRIGGERED JOB PROCESSING (OJS 3.4+ job_runner equivalent)
    // =========================================================================

    /**
     * Daftarkan pemrosesan queue jobs jika interval sudah lewat.
     */
    private function registerJobProcessing(): void
    {
        // Tidak perlu untuk sync driver — jobs sudah diproses inline
        if (config('queue.default') === 'sync') {
            return;
        }

        // Cek apakah sudah berjalan dalam interval terakhir
        if (Cache::has('acron_job_process_lock')) {
            return;
        }

        app()->terminating(function () {
            $this->processQueueJobs();
        });
    }

    /**
     * Proses pending jobs dari tabel database dengan atomic lock.
     * 
     * Identik dengan OJS 3.4+ built-in Job Runner:
     * - Dieksekusi di akhir web request (setelah response terkirim)
     * - Menggunakan fastcgi_finish_request() jika tersedia
     * - Atomic lock mencegah race condition dari concurrent visitors
     * - Max 30 detik dan 10 jobs per siklus untuk menjaga stabilitas
     */
    private function processQueueJobs(): void
    {
        // Double-check: cache mungkin sudah di-set oleh request paralel
        if (Cache::has('acron_job_process_lock')) {
            return;
        }

        // Atomic lock: mencegah dua pengunjung memproses jobs bersamaan
        $lock = Cache::lock('acron_job_processing', 60);

        if (!$lock->get()) {
            return;
        }

        try {
            // Set interval lock SEGERA
            Cache::put('acron_job_process_lock', true, self::JOB_PROCESS_INTERVAL);

            // Optimasi: Jika FastCGI tersedia, tutup koneksi browser terlebih dahulu
            if (function_exists('fastcgi_finish_request')) {
                fastcgi_finish_request();
            }

            // Proses semua pending jobs, berhenti ketika kosong
            // --max-time=30 : maksimal 30 detik per siklus
            // --max-jobs=10 : maksimal 10 jobs per siklus
            // --stop-when-empty : berhenti jika tidak ada job lagi
            Artisan::call('queue:work', [
                '--stop-when-empty' => true,
                '--max-time' => 30,
                '--max-jobs' => 10,
                '--memory' => 128,
                '--quiet' => true,
            ]);

            Log::channel('single')->info('[Acron] Queue jobs processed successfully.');
        } catch (\Exception $e) {
            Log::channel('single')->error('[Acron] Job processing failed: ' . $e->getMessage());
        } finally {
            $lock->release();
        }
    }

    // =========================================================================
    // TASK 2: CROSSREF STATUS POLLING (OJS CrossrefInfoSender equivalent)
    // =========================================================================

    /**
     * Daftarkan pengecekan status DOI jika interval sudah lewat.
     */
    private function registerCrossrefCheck(): void
    {
        if (Cache::has('acron_crossref_check_lock')) {
            return;
        }

        app()->terminating(function () {
            $this->executeCrossrefCheck();
        });
    }

    /**
     * Eksekusi pengecekan status DOI Crossref dengan atomic lock.
     */
    private function executeCrossrefCheck(): void
    {
        // Double-check
        if (Cache::has('acron_crossref_check_lock')) {
            return;
        }

        $lock = Cache::lock('acron_crossref_executing', 120);

        if (!$lock->get()) {
            return;
        }

        try {
            Cache::put('acron_crossref_check_lock', true, self::STATUS_CHECK_INTERVAL);

            if (function_exists('fastcgi_finish_request')) {
                fastcgi_finish_request();
            }

            Artisan::call('crossref:check-status');

            Log::channel('single')->info('[Acron] crossref:check-status executed successfully.');
        } catch (\Exception $e) {
            Log::channel('single')->error('[Acron] Crossref check failed: ' . $e->getMessage());
        } finally {
            $lock->release();
        }
    }
}
