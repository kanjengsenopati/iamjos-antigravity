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

        // Bypass untuk Health Check API dan SaaS Control Plane API — selalu harus bisa diakses
        if ($request->is('api/v1/health') || $request->is('api/health') || $request->is('api/v1/saas*')) {
            return $next($request);
        }

        $status = $this->licenseService->getStatus();

        if ($status->isOperational()) {
            return $next($request);
        }

        $code = 'LICENSE_' . strtoupper($status->value);
        $message = 'Akses ditolak: ' . $status->label();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'message' => $message,
                'status'  => $status->value,
                'code'    => $code,
            ], Response::HTTP_PAYMENT_REQUIRED);
        }

        return response()->view('errors.license', [
            'status'     => $status->value,
            'message'    => $status->label() . '. Hubungi administrator sistem Anda untuk memverifikasi kunci lisensi yang sah di panel Kampus IamJOS.',
            'instanceId' => config('iamjos.instance_id', env('IAMJOS_INSTANCE_ID', 'unknown')),
            'code'       => $code,
        ], Response::HTTP_PAYMENT_REQUIRED);
    }
}
