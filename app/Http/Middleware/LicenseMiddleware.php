<?php
namespace App\Http\Middleware;

use App\Enums\LicenseStatus;
use App\Services\LicenseService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LicenseMiddleware
{
    public function __construct(
        private readonly LicenseService $licenseService,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        // Bypass jika pengecekan lisensi dinonaktifkan
        if (!config('iamjos.license_check_enabled', false)) {
            return $next($request);
        }

        // Bypass untuk Health Check API — selalu harus bisa diakses
        if ($request->is('api/v1/health') || $request->is('api/health')) {
            return $next($request);
        }

        $status = $this->licenseService->getStatus();

        if ($status->isOperational()) {
            return $next($request);
        }

        return response()->json([
            'message' => 'Akses ditolak: ' . $status->label(),
            'status'  => $status->value,
            'code'    => 'LICENSE_' . strtoupper($status->value),
        ], Response::HTTP_PAYMENT_REQUIRED);
    }
}
