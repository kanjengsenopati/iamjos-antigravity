<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Acron Middleware — OJS Acron Plugin Equivalent
 * 
 * Web-based pseudo-cron yang memicu pengecekan status DOI Crossref
 * secara otomatis di latar belakang, tanpa bergantung pada crontab server.
 * 
 * Kompatibel dengan semua lingkungan server:
 * - Apache mod_php, PHP-FPM, FastCGI, LiteSpeed, Nginx
 * - Shared Hosting, VPS, Dedicated, Cloud (Vercel, Railway, dll)
 * 
 * Cara Kerja (Identik OJS Acron):
 * 1. Setiap request GET web masuk, middleware mengecek cache interval.
 * 2. Jika interval sudah lewat (30 menit), middleware mendaftarkan
 *    eksekusi crossref:check-status via app()->terminating().
 * 3. Eksekusi terjadi SETELAH response dikirim ke browser (non-blocking
 *    jika FastCGI/PHP-FPM), atau inline di akhir siklus (mod_php).
 * 4. Atomic lock mencegah race condition dari concurrent requests.
 */
class AcronMiddleware
{
    /**
     * Interval pengecekan dalam detik (30 menit).
     */
    private const CHECK_INTERVAL = 1800;

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

        // Guard: Cek apakah sudah berjalan dalam interval terakhir
        if (Cache::has('acron_crossref_check_lock')) {
            return $response;
        }

        // Daftarkan eksekusi di akhir siklus Laravel (setelah response terkirim)
        // app()->terminating() dipanggil oleh $kernel->terminate() di public/index.php
        // yang SELALU dieksekusi di semua environment PHP, setelah $response->send().
        app()->terminating(function () {
            $this->executeCrossrefCheck();
        });

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

    /**
     * Eksekusi pengecekan status DOI Crossref dengan atomic lock.
     */
    private function executeCrossrefCheck(): void
    {
        // Double-check: cache mungkin sudah di-set oleh request paralel
        if (Cache::has('acron_crossref_check_lock')) {
            return;
        }

        // Atomic lock: mencegah race condition dari concurrent web visitors
        $lock = Cache::lock('acron_crossref_executing', 120);

        if (!$lock->get()) {
            return; // Lock sudah dipegang request lain, skip
        }

        try {
            // Set interval lock SEGERA agar request berikutnya tidak mencoba masuk
            Cache::put('acron_crossref_check_lock', true, self::CHECK_INTERVAL);

            // Optimasi: Jika FastCGI tersedia, pastikan response sudah terkirim ke browser
            if (function_exists('fastcgi_finish_request')) {
                fastcgi_finish_request();
            }

            // === PERBEDAAN KRITIS DARI VERSI SEBELUMNYA ===
            // Versi lama memanggil Artisan::call('schedule:run') yang bergantung pada
            // cron expression (hanya jalan di menit ke-0 dan ke-30).
            // Versi baru memanggil crossref:check-status LANGSUNG, memastikan 
            // pengecekan SELALU berjalan setiap interval Acron terpenuhi.
            Artisan::call('crossref:check-status');

            Log::channel('single')->info('[Acron] crossref:check-status executed successfully.');
        } catch (\Exception $e) {
            Log::channel('single')->error('[Acron] Failed: ' . $e->getMessage());
        } finally {
            $lock->release();
        }
    }
}
