<?php

namespace App\Services;

use App\Models\Province;
use App\Models\Regency;
use App\Models\MeetingVenue;
use App\Models\MeetingRoom;
use App\Models\MeetingRoomLayout;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class PhriMeetingRoomService
{
    private const ETAG_KEY = 'phri:meetingrooms:etag';
    private const LM_KEY   = 'phri:meetingrooms:last_modified';

    protected function endpoint(): string
    {
        return env('MEETING_ROOM_URL', 'https://phri.or.id/membership/api/content/meetingrooms');
    }

    protected function apiKey(): string
    {
        $key = (string) config('services.phri.key', '');
        if (trim($key) === '') {
            throw new \RuntimeException('PHRI_API_KEY belum disetel di .env');
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
        $timeout = (int) config('services.phri.timeout', 30);
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

        $json = $resp->json();
        return Arr::get($json, 'RECORDS', $json) ?? [];
    }

    protected function normalize(array $records): array
    {
        $venues  = [];
        $rooms   = [];
        $layouts = [];

        foreach ($records as $row) {
            $extId = (int) (Arr::get($row, 'id') ?? 0);
            if ($extId <= 0) continue;

            $phriProv = (int) (Arr::get($row, 'id_province') ?? Arr::get($row, 'id_provinsi') ?? 0);
            $phriCity = (int) (Arr::get($row, 'id_city') ?? 0);

            $venues[] = [
                'external_id'      => $extId,
                'phri_province_id' => $phriProv,
                'phri_regency_id'  => $phriCity,
                'province_name'    => trim((string) Arr::get($row, 'provinsi')),
                'city_name'        => trim((string) Arr::get($row, 'kota')),
                'hotel'            => trim((string) Arr::get($row, 'hotel')),
                'address'          => trim((string) Arr::get($row, 'alamat')),
                'email'            => trim((string) Arr::get($row, 'email')),
                'phone'            => trim((string) Arr::get($row, 'telepon')),
                'max_capacity'     => (int) (Arr::get($row, 'max_capacity') ?? 0),
            ];

            $roomList = Arr::get($row, 'room', []);
            if (is_array($roomList)) {
                foreach ($roomList as $r) {
                    $roomName = trim((string) Arr::get($r, 'ruangan'));
                    if ($roomName === '') continue;

                    $rooms[] = [
                        'venue_external_id' => $extId,
                        'name'              => $roomName,
                    ];

                    foreach ($r as $k => $v) {
                        if ($k === 'ruangan') continue;
                        $layoutName = $this->normalizeLayoutKey($k);
                        $cap        = (int) ($v ?? 0);
                        if ($cap <= 0) continue;
                        $layouts[] = [
                            'venue_external_id' => $extId,
                            'room_name'         => $roomName,
                            'layout'            => $layoutName,
                            'capacity'          => $cap,
                        ];
                    }
                }
            }
        }

        return compact('venues', 'rooms', 'layouts');
    }

    protected function normalizeLayoutKey(string $key): string
    {
        $k = Str::of($key)->lower();
        $k = Str::of($k)->replace(['(', ')'], '');
        $k = Str::of($k)->replace(['  '], ' ');
        return Str::slug($k, '_');
    }

    /**
     * Map ke ID lokal (diasumsikan PK tabel provinces/regencies juga UUID).
     * provinces.id diambil via mapping phri_id -> id
     * regencies.id diambil via mapping external_id -> id
     */
    protected function mapLocalIds(array $venues): array
    {
        $provMap = Province::query()->pluck('id', 'external_id')->all();      // returns UUID string
        $regMap  = Regency::query()->pluck('id', 'external_id')->all();  // returns UUID string

        foreach ($venues as &$v) {
            $v['province_id'] = $provMap[$v['phri_province_id']] ?? null;
            $v['regency_id']  = $regMap[$v['phri_regency_id']]   ?? null;
        }
        unset($v);

        return $venues;
    }

    public function sync(): array
    {
        $raw = $this->fetchRaw();
        if (isset($raw['_not_modified'])) {
            return [
                'not_modified'     => true,
                'venues_inserted'  => 0,
                'venues_updated'   => 0,
                'rooms_upserted'   => 0,
                'layouts_upserted' => 0
            ];
        }

        $norm    = $this->normalize($raw);
        $venues  = $this->mapLocalIds($norm['venues']);
        $rooms   = $norm['rooms'];
        $layouts = $norm['layouts'];

        // ==== Upsert VENUES (manual, karena PK UUID & external_id tidak unik di DB) ====
        $existingVenues = MeetingVenue::query()->pluck('id', 'external_id')->all(); // ext_id => uuid
        $venueIdMap = []; // ext_id => uuid
        $venuesInserted = 0;
        $venuesUpdated  = 0;

        foreach ($venues as $v) {
            $ext = $v['external_id'];
            $payload = [
                'phri_province_id' => $v['phri_province_id'],
                'phri_regency_id'  => $v['phri_regency_id'],
                'province_id'      => $v['province_id'],
                'regency_id'       => $v['regency_id'],
                'province_name'    => $v['province_name'],
                'city_name'        => $v['city_name'],
                'hotel'            => $v['hotel'],
                'address'          => $v['address'],
                'email'            => $v['email'],
                'phone'            => $v['phone'],
                'max_capacity'     => $v['max_capacity'],
            ];

            if (isset($existingVenues[$ext])) {
                // update
                $venueId = $existingVenues[$ext];
                MeetingVenue::where('id', $venueId)->update($payload);
                $venueIdMap[$ext] = $venueId;
                $venuesUpdated++;
            } else {
                // insert dengan UUID baru
                $venueId = (string) Str::uuid();
                MeetingVenue::create(array_merge([
                    'id'           => $venueId,
                    'external_id'  => $ext,
                ], $payload));
                $venueIdMap[$ext] = $venueId;
                $venuesInserted++;
            }
        }

        // ==== Upsert ROOMS (unik: (meeting_venue_id, name)) ====
        $roomsUpserted = 0;

        // ambil semua room eksisting untuk venue yang terproses
        $venueIds = array_values(array_unique(array_values($venueIdMap)));
        $existingRooms = [];
        if (!empty($venueIds)) {
            MeetingRoom::whereIn('meeting_venue_id', $venueIds)
                ->get(['id', 'meeting_venue_id', 'name'])
                ->each(function ($rm) use (&$existingRooms) {
                    $existingRooms[$rm->meeting_venue_id . '|' . $rm->name] = $rm->id;
                });
        }

        foreach ($rooms as $r) {
            $venId = $venueIdMap[$r['venue_external_id']] ?? null;
            if (!$venId) continue;

            $key = $venId . '|' . $r['name'];
            if (isset($existingRooms[$key])) {
                // update timestamp saja agar "terlihat" diubah
                MeetingRoom::where('id', $existingRooms[$key])->update(['updated_at' => now()]);
            } else {
                $roomId = (string) Str::uuid();
                MeetingRoom::create([
                    'id'               => $roomId,
                    'meeting_venue_id' => $venId,
                    'name'             => $r['name'],
                ]);
                $existingRooms[$key] = $roomId;
            }
            $roomsUpserted++;
        }

        // ==== Upsert LAYOUTS (unik: (meeting_room_id, layout)) ====
        $layoutsUpserted = 0;

        // ambil layout eksisting untuk room-room yang terproses
        $roomIds = array_values($existingRooms);
        $existingLayouts = [];
        if (!empty($roomIds)) {
            MeetingRoomLayout::whereIn('meeting_room_id', $roomIds)
                ->get(['id', 'meeting_room_id', 'layout'])
                ->each(function ($lt) use (&$existingLayouts) {
                    $existingLayouts[$lt->meeting_room_id . '|' . $lt->layout] = $lt->id;
                });
        }

        foreach ($layouts as $lt) {
            $venId  = $venueIdMap[$lt['venue_external_id']] ?? null;
            if (!$venId) continue;

            $roomKey = $venId . '|' . $lt['room_name'];
            $roomId  = $existingRooms[$roomKey] ?? null;
            if (!$roomId) continue;

            $key = $roomId . '|' . $lt['layout'];
            if (isset($existingLayouts[$key])) {
                // update capacity
                MeetingRoomLayout::where('id', $existingLayouts[$key])->update([
                    'capacity'   => (int) $lt['capacity'],
                    'updated_at' => now(),
                ]);
            } else {
                MeetingRoomLayout::create([
                    'id'              => (string) Str::uuid(),
                    'meeting_room_id' => $roomId,
                    'layout'          => $lt['layout'],
                    'capacity'        => (int) $lt['capacity'],
                ]);
            }
            $layoutsUpserted++;
        }

        return [
            'venues_inserted'  => $venuesInserted,
            'venues_updated'   => $venuesUpdated,
            'rooms_upserted'   => $roomsUpserted,
            'layouts_upserted' => $layoutsUpserted,
        ];
    }
}
