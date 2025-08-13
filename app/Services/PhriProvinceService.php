<?php

namespace App\Services;

use App\Models\Province;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class PhriProvinceService
{
    private const ETAG_KEY = 'phri:province:etag';
    private const LM_KEY   = 'phri:province:last_modified';

    protected function endpoint(): string
    {
        return env('PROVINCE_URL', 'https://phri.or.id/membership/api/content/provinsi');
    }

    protected function httpHeaders(bool $useConditional = true): array
    {
        $headers = [
            'Accept'     => 'application/json',
            'x-api-key'  => config('services.phri.key'),
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

    /**
     * Ambil raw data dari API. Mengembalikan array record.
     */
    protected function fetchRaw(): array
    {
        $timeout = (int) config('services.phri.timeout', 20);
        $retry   = (int) config('services.phri.retry', 2);

        $resp = Http::timeout($timeout)
            ->retry($retry, 500)
            ->withHeaders($this->httpHeaders())
            ->get($this->endpoint());

        // 304 -> tidak berubah
        if ($resp->status() === 304) {
            return ['_not_modified' => true];
        }

        $resp->throw(); // akan lempar exception bila 4xx/5xx

        // Simpan ETag/Last-Modified jika ada
        if ($resp->hasHeader('ETag')) {
            Cache::put(self::ETAG_KEY, $resp->header('ETag'), now()->addHours(24));
        }
        if ($resp->hasHeader('Last-Modified')) {
            Cache::put(self::LM_KEY, $resp->header('Last-Modified'), now()->addHours(24));
        }

        $json = $resp->json();

        // Beberapa API PHRI mengemas data di key "RECORDS".
        // Kalau tidak ada, anggap top-level sudah array daftar record.
        $records = Arr::get($json, 'RECORDS', $json);

        if (!is_array($records)) {
            throw new \RuntimeException('Struktur respons PHRI tidak sesuai ekspektasi.');
        }

        return $records;
    }

    /**
     * Normalisasi record menjadi bentuk yang konsisten:
     * - external_id (int)
     * - name (string)
     */
    protected function normalize(array $records): Collection
    {
        return collect($records)
            ->map(function ($row) {
                // Coba beberapa kemungkinan nama kolom
                $id   = Arr::get($row, 'id') ?? Arr::get($row, 'ID') ?? Arr::get($row, 'provinsi_id');
                $name = Arr::get($row, 'provinsi') ?? Arr::get($row, 'name') ?? Arr::get($row, 'nama');

                if ($id === null || $name === null) {
                    // Abaikan record yang tidak lengkap
                    return null;
                }

                return [
                    'external_id'    => (int) $id,
                    'name'       => trim((string) $name),
                    'updated_at' => now(),
                    'created_at' => now(),
                ];
            })
            ->filter(); // buang null
    }

    /**
     * Sinkronisasi:
     * - Insert jika external_id belum ada
     * - (opsional) update nama jika berubah — lewat upsert kolom 'name'
     */
    public function sync(): array
    {
        $raw = $this->fetchRaw();

        if (isset($raw['_not_modified'])) {
            return ['inserted' => 0, 'updated' => 0, 'skipped' => 0, 'not_modified' => true];
        }

        $rows = $this->normalize($raw);

        if ($rows->isEmpty()) {
            return ['inserted' => 0, 'updated' => 0, 'skipped' => 0];
        }

        // Hitung before untuk estimasi inserted/updated
        $existingIds = Province::query()->pluck('external_id')->all();
        $existingSet = array_fill_keys($existingIds, true);

        $toUpsert = $rows->all();

        // Upsert by 'external_id', update kolom 'name' bila berubah
        Province::upsert($toUpsert, ['external_id'], ['name', 'updated_at']);

        // Estimasi metrik
        $inserted = 0;
        $updated  = 0;
        foreach ($toUpsert as $item) {
            if (!isset($existingSet[$item['external_id']])) {
                $inserted++;
            } else {
                $updated++;
            }
        }

        return compact('inserted', 'updated') + ['skipped' => 0];
    }
}
