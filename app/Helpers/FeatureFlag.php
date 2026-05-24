<?php
namespace App\Helpers;

use App\Enums\LicenseStatus;
use Illuminate\Support\Facades\Cache;

class FeatureFlag
{
    // Konstanta nama fitur yang dikenali
    public const ADVANCED_ANALYTICS   = 'advanced_analytics';
    public const MULTI_JOURNAL        = 'multi_journal';
    public const OAI_PMH              = 'oai_pmh';
    public const CROSSREF_INTEGRATION = 'crossref_integration';

    /**
     * Periksa apakah fitur tertentu aktif berdasarkan paket lisensi.
     * Fail-open: jika LICENSE_CHECK_ENABLED=false atau status Unchecked, semua fitur aktif.
     */
    public static function isEnabled(string $feature): bool
    {
        // Jika pengecekan lisensi dinonaktifkan, semua fitur aktif
        if (!config('iamjos.license_check_enabled', false)) {
            return true;
        }

        // Baca status lisensi dari cache
        $statusString = Cache::get('iamjos:license:status');
        $status       = $statusString
            ? LicenseStatus::fromString($statusString)
            : LicenseStatus::Unchecked;

        // Fail-open untuk Unchecked
        if ($status === LicenseStatus::Unchecked) {
            return true;
        }

        // Jika lisensi tidak operasional, semua fitur nonaktif
        if (!$status->isOperational()) {
            return false;
        }

        // Baca daftar fitur yang diizinkan dari cache lisensi
        $allowedFeatures = Cache::get('iamjos:license:features', []);

        if (empty($allowedFeatures)) {
            // Jika tidak ada daftar fitur, fail-open
            return true;
        }

        return in_array($feature, (array) $allowedFeatures, true);
    }
}
