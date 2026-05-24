<?php
namespace App\Services;

use App\Enums\LicenseStatus;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Support\Facades\Log;

class LicenseService
{
    private const CACHE_KEY          = 'iamjos:license:status';
    private const FALLBACK_CACHE_KEY = 'iamjos:license:last_known_status';
    private const CACHE_TTL          = 86400;    // 24 jam
    private const FALLBACK_TTL       = 604800;   // 7 hari

    public function __construct(
        private readonly CacheRepository $cache,
        private readonly HttpFactory $http,
    ) {}

    /**
     * Dapatkan status lisensi instance saat ini.
     * Fail-open: jika LICENSE_CHECK_ENABLED=false, selalu return Valid.
     */
    public function getStatus(): LicenseStatus
    {
        // Jika pengecekan lisensi dinonaktifkan, langsung valid
        if (!config('iamjos.license_check_enabled', false)) {
            return LicenseStatus::Valid;
        }

        $kampusUrl  = config('iamjos.kampus_url');
        $licenseKey = config('iamjos.license_key');

        // Jika KAMPUS URL tidak dikonfigurasi, kembalikan Unchecked
        if (empty($kampusUrl)) {
            return LicenseStatus::Unchecked;
        }

        // Cek cache utama (TTL 24 jam)
        $cached = $this->cache->get(self::CACHE_KEY);
        if ($cached !== null) {
            return LicenseStatus::fromString($cached);
        }

        // Cache kosong — coba validasi ke KAMPUS
        try {
            $response = $this->http
                ->timeout(5)
                ->withToken($licenseKey ?? '')
                ->get(rtrim($kampusUrl, '/') . '/api/license/validate', [
                    'instance_id' => config('iamjos.instance_id'),
                ]);

            if ($response->successful()) {
                $statusString = $response->json('status', 'unchecked');
                $status       = LicenseStatus::fromString($statusString);

                // Simpan ke cache utama
                $this->cache->put(self::CACHE_KEY, $status->value, self::CACHE_TTL);

                // Simpan ke fallback cache hanya jika Valid
                if ($status === LicenseStatus::Valid) {
                    $this->cache->put(self::FALLBACK_CACHE_KEY, $status->value, self::FALLBACK_TTL);
                }

                return $status;
            }

            // HTTP error dari KAMPUS — gunakan fallback
            return $this->getFallbackStatus();
        } catch (\Throwable $e) {
            Log::warning('LicenseService: Tidak dapat menghubungi KAMPUS — menggunakan grace period.', [
                'error' => $e->getMessage(),
            ]);

            return $this->getFallbackStatus();
        }
    }

    /**
     * Hapus semua cache terkait lisensi.
     */
    public function clearCache(): void
    {
        $this->cache->forget(self::CACHE_KEY);
        $this->cache->forget(self::FALLBACK_CACHE_KEY);
    }

    /**
     * Ambil status dari fallback cache (grace period 7 hari).
     * Jika tidak ada, kembalikan Unchecked.
     */
    private function getFallbackStatus(): LicenseStatus
    {
        $fallback = $this->cache->get(self::FALLBACK_CACHE_KEY);

        if ($fallback !== null) {
            return LicenseStatus::fromString($fallback);
        }

        return LicenseStatus::Unchecked;
    }
}
