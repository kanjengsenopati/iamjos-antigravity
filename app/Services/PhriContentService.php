<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;

class PhriContentService
{
    private const DATA_KEY  = 'phri:bpd:data';
    private const ETAG_KEY  = 'phri:bpd:etag';
    private const LM_KEY    = 'phri:bpd:last_modified';
    private const AT_KEY    = 'phri:bpd:fetched_at';
    private const TTL_HOURS = 23; // atur TTL cache

    protected function httpHeaders(bool $useConditional = true): array
    {
        $headers = [
            'Accept'    => 'application/json',
            'x-api-key' => config('services.phri.key'),
        ];

        if ($useConditional) {
            if ($etag = Cache::get(self::ETAG_KEY)) {
                $headers['If-None-Match'] = $etag;
            }
            if ($lm = Cache::get(self::LM_KEY)) {
                $headers['If-Modified-Since'] = $lm;
            }
        }
        return $headers;
    }

    protected function requestUpstream(bool $force = false): array
    {
        $url = rtrim(config('services.phri.base'), '/') . '/content/bpd';

        $resp = Http::timeout(15)
            ->retry(2, 500)
            ->withHeaders($this->httpHeaders(!$force))
            ->get($url);

        if ($resp->status() === 304) {
            // Tidak berubah → pakai cache lama
            $cached = Cache::get(self::DATA_KEY);
            if ($cached) return $cached;
            // fallback: kalau entah kenapa 304 tapi cache kosong, coba ambil lagi tanpa conditional
            return $this->requestUpstream(true);
        }

        if ($resp->successful()) {
            $data = (array) $resp->json();

            // Simpan cache data + metadata
            Cache::put(self::DATA_KEY, $data, now()->addHours(self::TTL_HOURS));
            Cache::put(self::AT_KEY, Carbon::now()->toIso8601String(), now()->addDays(7));

            if ($etag = $resp->header('ETag')) {
                Cache::put(self::ETAG_KEY, $etag, now()->addDays(7));
            }
            if ($lm = $resp->header('Last-Modified')) {
                Cache::put(self::LM_KEY, $lm, now()->addDays(7));
            }

            return $data;
        }

        // Gagal → coba pakai cache lama jika ada
        if ($cached = Cache::get(self::DATA_KEY)) {
            return $cached;
        }

        $resp->throw(); // betul-betul gagal & tak ada cache
    }

    /** Ambil data dari cache (atau tarik jika kosong/expired). */
    public function getBpd(): array
    {
        return Cache::remember(self::DATA_KEY, now()->addHours(self::TTL_HOURS), function () {
            return $this->requestUpstream(false);
        });
    }

    /** Paksa refresh dari upstream (abaikan ETag/LM). */
    public function refreshBpd(): array
    {
        return $this->requestUpstream(true);
    }

    public function lastFetchedAt(): ?string
    {
        return Cache::get(self::AT_KEY);
    }
}
