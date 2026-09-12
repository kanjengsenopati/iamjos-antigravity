<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class AcronMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }

    /**
     * Handle tasks after the response has been sent to the browser.
     * This acts as an OJS Acron equivalent (Web-based pseudo-cron).
     */
    public function terminate(Request $request, Response $response): void
    {
        // Hanya eksekusi pada method GET agar tidak menginterupsi submission form (POST/PUT dll)
        if (!$request->isMethod('GET')) {
            return;
        }

        // Jangan eksekusi jika request berupa asset/ajax untuk meminimalisir overhead
        if ($request->ajax() || $request->is('build/*') || $request->is('assets/*') || $request->is('api/*')) {
            return;
        }

        // Acron lock interval (30 menit)
        $lockKey = 'acron_scheduler_lock';
        $intervalSeconds = 1800; // 30 menit

        // Cek apakah Acron sudah berjalan dalam 30 menit terakhir
        if (!Cache::has($lockKey)) {
            // Gunakan lock atomik untuk mencegah race condition (2 pengunjung akses bersamaan)
            $lock = Cache::lock('acron_scheduler_running', 120);

            if ($lock->get()) {
                try {
                    // Segera set lock interval agar request berikutnya dalam antrean tidak mencoba masuk
                    Cache::put($lockKey, true, $intervalSeconds);

                    // Panggil crossref:check-status atau schedule:run.
                    // Menjalankan scheduler global agar selaras dengan OJS behavior.
                    Artisan::call('schedule:run');
                    
                    Log::info('Acron pseudo-cron executed successfully via Terminable Middleware.');
                } catch (\Exception $e) {
                    Log::error('Acron pseudo-cron failed: ' . $e->getMessage());
                } finally {
                    $lock->release();
                }
            }
        }
    }
}
