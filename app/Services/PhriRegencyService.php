<?php

namespace App\Services;

use App\Models\Province;
use App\Models\Regency;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class PhriRegencyService
{
    private const ETAG_KEY = 'phri:regency:etag';
    private const LM_KEY   = 'phri:regency:last_modified';

    protected function endpoint(): string
    {
        return env('REGENCY_URL', 'https://phri.or.id/membership/api/content/kabupaten');
    }

    protected function apiKey(): string
    {
        $key = (string) config('services.phri.key', '');
        if (trim($key) === '') {
            throw new \RuntimeException('PHRI_API_KEY durung disetel. Isi ing .env banjur php artisan config:cache');
        }
        return $key;
    }

    protected function httpHeaders(bool $useConditional = true): array
    {
        $headers = [
            'Accept'    => 'application/json',
            'x-api-key' => $this->apiKey(),
        ];

        if ($useConditional) {
            if ($etag = Cache::get(self::ETAG_KEY)) $headers['If-None-Match'] = $etag;
            if ($lm   = Cache::get(self::LM_KEY))   $headers['If-Modified-Since'] = $lm;
        }
        return $headers;
    }

    protected function fetchRaw(): array
    {
        $timeout = (int) config('services.phri.timeout', 20);
        $retry   = (int) config('services.phri.retry', 2);

        $resp = Http::timeout($timeout)
            ->retry($retry, 500)
            ->withHeaders($this->httpHeaders())
            ->get($this->endpoint());

        if (in_array($resp->status(), [401, 403], true)) {
            throw new \RuntimeException('Akses ditolak: API key PHRI salah/invalid (HTTP ' . $resp->status() . ').');
        }
        if ($resp->status() === 304) {
            return ['_not_modified' => true];
        }

        $resp->throw();

        if ($resp->hasHeader('ETag'))          Cache::put(self::ETAG_KEY, $resp->header('ETag'), now()->addHours(24));
        if ($resp->hasHeader('Last-Modified')) Cache::put(self::LM_KEY, $resp->header('Last-Modified'), now()->addHours(24));

        $json    = $resp->json();
        $records = Arr::get($json, 'RECORDS', $json); // kebanyakan top-level array

        if (!is_array($records)) {
            throw new \RuntimeException('Struktur respons kab/kota PHRI boten cocog ekspektasi.');
        }
        return $records;
    }

    /**
     * Normalisasi:
     * - external_id           : int (id)
     * - phri_province_id      : int (id_provinsi)
     * - province_id (UUID)    : diisi mengko liwat mapping
     * - name                  : kabupaten/kota tanpa prefix "KABUPATEN"/"KOTA"
     */
    protected function normalize(array $records): Collection
    {
        return collect($records)->map(function ($row) {
            $id         = Arr::get($row, 'id') ?? Arr::get($row, 'ID');
            $provPhriId = Arr::get($row, 'id_provinsi') ?? Arr::get($row, 'provinsi_id');
            $kab        = Arr::get($row, 'kabupaten') ?? Arr::get($row, 'name');

            if ($id === null || $provPhriId === null || $kab === null) return null;

            $kabStr = trim((string) $kab);
            $upper  = Str::upper($kabStr);

            // buang prefix supaya rapi (opsional)
            if (Str::startsWith($upper, 'KOTA ')) {
                $kabStr = trim(Str::substr($kabStr, 5));
            } elseif (Str::startsWith($upper, 'KABUPATEN ')) {
                $kabStr = trim(Str::substr($kabStr, 10));
            }

            return [
                'external_id'       => (int) $id,
                'phri_province_id'  => (int) $provPhriId,
                'province_id'       => null,      // diisi saat mapping
                'name'              => $kabStr,
                'created_at'        => now(),
                'updated_at'        => now(),
            ];
        })->filter()->values();
    }

    /**
     * Map phri_province_id -> provinces.id (bisa UUID utawa bigint; disimpen apa adanya)
     */
    protected function mapProvinceIds(Collection $rows): Collection
    {
        // nggo apa wae tipe PK (UUID/bigint), iki bakal ngehasilke array: [external_id => id]
        $map = Province::query()->pluck('id', 'external_id')->all();

        return $rows->map(function ($r) use ($map) {
            $r['province_id'] = $map[$r['phri_province_id']] ?? null;
            return $r;
        });
    }

    public function sync(): array
    {
        $raw = $this->fetchRaw();
        if (isset($raw['_not_modified'])) {
            return ['inserted' => 0, 'updated' => 0, 'mapped' => 0, 'not_modified' => true];
        }

        $rows = $this->normalize($raw);
        if ($rows->isEmpty()) return ['inserted' => 0, 'updated' => 0, 'mapped' => 0];

        $rows = $this->mapProvinceIds($rows);

        // cek sing wis ana adhedhasar external_id
        $existingIds = Regency::query()->pluck('external_id')->all();
        $exists      = array_fill_keys($existingIds, true);

        // upsert nganggo kunci external_id
        Regency::upsert(
            $rows->all(),
            ['external_id'],
            ['name', 'province_id', 'phri_province_id', 'updated_at']
        );

        $inserted = 0;
        $updated = 0;
        $mapped = 0;
        foreach ($rows as $r) {
            isset($exists[$r['external_id']]) ? $updated++ : $inserted++;
            if (!empty($r['province_id'])) $mapped++;
        }

        // backfill province_id sing isih null (yen provinsi lagi kasinkron mengko)
        Regency::whereNull('province_id')->chunkById(500, function ($chunk) {
            $map = Province::query()->pluck('id', 'external_id')->all();
            foreach ($chunk as $reg) {
                $provId = $map[$reg->phri_province_id] ?? null;
                if ($provId) {
                    $reg->province_id = $provId;
                    $reg->save();
                }
            }
        });

        return compact('inserted', 'updated', 'mapped');
    }
}
