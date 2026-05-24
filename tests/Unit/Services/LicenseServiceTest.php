<?php

/**
 * Test untuk LicenseService
 *
 * Memverifikasi bahwa:
 * - Jika LICENSE_CHECK_ENABLED=false, selalu return Valid (fail-open)
 * - Jika KAMPUS_URL kosong, return Unchecked
 * - Cache digunakan dengan benar (tidak hit KAMPUS dua kali)
 * - Grace period bekerja saat KAMPUS tidak bisa dijangkau
 */

use App\Enums\LicenseStatus;
use App\Services\LicenseService;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

describe('LicenseService', function () {

    // ─── Fail-open behavior ───────────────────────────────────────────────────

    it('mengembalikan Valid jika LICENSE_CHECK_ENABLED=false', function () {
        config(['iamjos.license_check_enabled' => false]);

        $service = app(LicenseService::class);
        $status  = $service->getStatus();

        expect($status)->toBe(LicenseStatus::Valid);
    });

    it('mengembalikan Valid jika LICENSE_CHECK_ENABLED tidak di-set', function () {
        config(['iamjos.license_check_enabled' => null]);

        $service = app(LicenseService::class);
        $status  = $service->getStatus();

        expect($status)->toBe(LicenseStatus::Valid);
    });

    it('mengembalikan Unchecked jika KAMPUS_URL kosong', function () {
        config([
            'iamjos.license_check_enabled' => true,
            'iamjos.kampus_url'            => null,
        ]);

        $service = app(LicenseService::class);
        $status  = $service->getStatus();

        expect($status)->toBe(LicenseStatus::Unchecked);
    });

    // ─── Cache behavior ───────────────────────────────────────────────────────

    it('membaca dari cache jika tersedia tanpa hit KAMPUS', function () {
        config([
            'iamjos.license_check_enabled' => true,
            'iamjos.kampus_url'            => 'https://kampus.iamjos.id',
        ]);

        // Isi cache dengan status Valid
        Cache::put('iamjos:license:status', 'valid', 86400);

        // Pastikan tidak ada HTTP request ke KAMPUS
        Http::fake();

        $service = app(LicenseService::class);
        $status  = $service->getStatus();

        expect($status)->toBe(LicenseStatus::Valid);
        Http::assertNothingSent();
    });

    it('melakukan HTTP request ke KAMPUS jika cache kosong', function () {
        config([
            'iamjos.license_check_enabled' => true,
            'iamjos.kampus_url'            => 'https://kampus.iamjos.id',
            'iamjos.license_key'           => 'test-key',
        ]);

        Cache::forget('iamjos:license:status');

        Http::fake([
            'kampus.iamjos.id/*' => Http::response(['status' => 'valid'], 200),
        ]);

        $service = app(LicenseService::class);
        $status  = $service->getStatus();

        expect($status)->toBe(LicenseStatus::Valid);
        Http::assertSentCount(1);
    });

    it('menyimpan status ke cache setelah mendapat respons dari KAMPUS', function () {
        config([
            'iamjos.license_check_enabled' => true,
            'iamjos.kampus_url'            => 'https://kampus.iamjos.id',
        ]);

        Cache::forget('iamjos:license:status');

        Http::fake([
            'kampus.iamjos.id/*' => Http::response(['status' => 'valid'], 200),
        ]);

        $service = app(LicenseService::class);
        $service->getStatus();

        expect(Cache::has('iamjos:license:status'))->toBeTrue();
    });

    // ─── Grace period ─────────────────────────────────────────────────────────

    it('menggunakan last_known_status sebagai fallback saat KAMPUS tidak bisa dijangkau', function () {
        config([
            'iamjos.license_check_enabled' => true,
            'iamjos.kampus_url'            => 'https://kampus.iamjos.id',
        ]);

        Cache::forget('iamjos:license:status');
        Cache::put('iamjos:license:last_known_status', 'valid', 604800);

        Http::fake([
            'kampus.iamjos.id/*' => Http::response([], 500), // KAMPUS error
        ]);

        $service = app(LicenseService::class);
        $status  = $service->getStatus();

        expect($status)->toBe(LicenseStatus::Valid);
    });

    it('mengembalikan Unchecked jika KAMPUS down dan tidak ada fallback cache', function () {
        config([
            'iamjos.license_check_enabled' => true,
            'iamjos.kampus_url'            => 'https://kampus.iamjos.id',
        ]);

        Cache::forget('iamjos:license:status');
        Cache::forget('iamjos:license:last_known_status');

        Http::fake([
            'kampus.iamjos.id/*' => Http::response([], 503),
        ]);

        $service = app(LicenseService::class);
        $status  = $service->getStatus();

        expect($status)->toBe(LicenseStatus::Unchecked);
    });

    // ─── clearCache ───────────────────────────────────────────────────────────

    it('clearCache menghapus kedua cache key', function () {
        Cache::put('iamjos:license:status', 'valid', 86400);
        Cache::put('iamjos:license:last_known_status', 'valid', 604800);

        $service = app(LicenseService::class);
        $service->clearCache();

        expect(Cache::has('iamjos:license:status'))->toBeFalse();
        expect(Cache::has('iamjos:license:last_known_status'))->toBeFalse();
    });

});
