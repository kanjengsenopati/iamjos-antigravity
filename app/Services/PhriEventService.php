<?php

namespace App\Services;

use App\Models\Event;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class PhriEventService
{
    private const ETAG_KEY = 'phri:event:etag';
    private const LM_KEY   = 'phri:event:last_modified';

    protected function endpoint(): string
    {
        return env('EVENT_URL', 'https://phri.or.id/membership/api/content/event');
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
     * Normalisasi record menjadi bentuk yang konsisten untuk Event model
     */
    protected function normalize(array $records): Collection
    {
        return collect($records)
            ->map(function ($row) {
                $id = Arr::get($row, 'id');

                if ($id === null) {
                    // Abaikan record yang tidak lengkap
                    return null;
                }

                return [
                    'external_id'    => (int) $id,
                    'province_id'    => Arr::get($row, 'id_provinsi'),
                    'name'           => Arr::get($row, 'nama'),
                    'description'    => Arr::get($row, 'detail'),
                    'location'       => Arr::get($row, 'lokasi'),
                    'organized_by'   => Arr::get($row, 'organized_by'),
                    'start_date'     => Arr::get($row, 'date_start'),
                    'end_date'       => Arr::get($row, 'date_end'),
                    'web'            => Arr::get($row, 'website'),
                    'is_approved'    => Arr::get($row, 'is_approved', 0),
                    'is_active'      => 1,
                    'image'          => Arr::get($row, 'img'),
                    'updated_at'     => now(),
                    'created_at'     => now(),
                ];
            })
            ->filter(); // buang null
    }

    /**
     * Sinkronisasi events dari API PHRI
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

        $inserted = 0;
        $updated = 0;

        foreach ($rows as $row) {
            $external_id = $row['external_id'];
            unset($row['external_id'], $row['created_at']); // Remove keys not needed for update

            $event = Event::updateOrCreate(
                ['external_id' => $external_id],
                $row
            );

            if ($event->wasRecentlyCreated) {
                $inserted++;
            } else {
                $updated++;
            }
        }

        return compact('inserted', 'updated') + ['skipped' => 0];
    }
}
