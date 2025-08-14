<?php

namespace App\Services;

use App\Models\Province;
use App\Models\Regency;
use App\Models\MeetingVenue;
use App\Models\MeetingRoom;
use App\Models\MeetingRoomLayout;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
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
        $rooms   = [];   // per item: ['venue_external_id','name']
        $layouts = [];   // per item: ['venue_external_id','room_name','layout','capacity']

        foreach ($records as $row) {
            $extId = (int) (Arr::get($row, 'id') ?? 0);
            if ($extId <= 0) continue;

            $venues[] = [
                'external_id'      => $extId,
                'phri_province_id' => (int) (Arr::get($row, 'id_province') ?? Arr::get($row, 'id_provinsi') ?? 0),
                'phri_regency_id'  => (int) (Arr::get($row, 'id_city') ?? 0),
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
                        // simpan semua layout, termasuk 0 jika Anda mau
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

    protected function mapLocalIds(array $venues): array
    {
        // PROVINSI: kunci dari PHRI = provinces.phri_id
        $provMap = Province::query()->pluck('id', 'external_id')->all();        // [phri_id => uuid]
        // REGENCY: kunci dari PHRI = regencies.external_id
        $regMap  = Regency::query()->pluck('id', 'external_id')->all();     // [external_id => uuid]

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
                'venues_deleted'   => 0,
                'rooms_upserted'   => 0,
                'rooms_deleted'    => 0,
                'layouts_upserted' => 0,
                'layouts_deleted'  => 0,
            ];
        }

        $norm    = $this->normalize($raw);
        $venues  = $this->mapLocalIds($norm['venues']);
        $rooms   = $norm['rooms'];
        $layouts = $norm['layouts'];

        // Group rooms/layouts by venue_ext and room_name
        $roomsByVenue = [];
        foreach ($rooms as $r) {
            $roomsByVenue[$r['venue_external_id']][] = $r['name'];
        }
        $roomsByVenue = array_map(function ($names) {
            return array_values(array_unique($names));
        }, $roomsByVenue);

        $layoutsByVenueRoom = [];
        foreach ($layouts as $lt) {
            $layoutsByVenueRoom[$lt['venue_external_id']][$lt['room_name']][$lt['layout']] = (int) $lt['capacity'];
        }

        // ===== Upsert VENUE & catat UUID lokal =========================
        $existingVenues = MeetingVenue::query()->pluck('id', 'external_id')->all(); // [ext => uuid]
        $apiExtIds = array_map(fn($v) => $v['external_id'], $venues);
        $apiExtIds = array_values(array_unique($apiExtIds));

        $venueIdMap = []; // [ext => uuid]
        $venuesInserted = 0;
        $venuesUpdated  = 0;

        DB::beginTransaction();
        try {
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
                    'updated_at'       => now(),
                ];

                if (isset($existingVenues[$ext])) {
                    $venueId = $existingVenues[$ext];
                    MeetingVenue::where('id', $venueId)->update($payload);
                    $venuesUpdated++;
                } else {
                    $venueId = (string) Str::uuid();
                    MeetingVenue::create(array_merge([
                        'id'          => $venueId,
                        'external_id' => $ext,
                        'created_at'  => now(),
                    ], $payload));
                    $venuesInserted++;
                }
                $venueIdMap[$ext] = $venueId;
            }

            // ===== Hapus VENUE yang tidak ada di API ===================
            $toDeleteExt = array_diff(array_keys($existingVenues), $apiExtIds);
            $venuesDeleted = 0;
            $roomsDeleted = 0;
            $layoutsDeleted = 0;

            if (!empty($toDeleteExt)) {
                $toDeleteVenueIds = array_values(array_map(fn($ext) => $existingVenues[$ext], $toDeleteExt));

                // ambil room ids di venue yang akan dihapus
                $roomIds = MeetingRoom::whereIn('meeting_venue_id', $toDeleteVenueIds)->pluck('id')->all();
                if (!empty($roomIds)) {
                    // hapus layouts
                    $layoutsDeleted += MeetingRoomLayout::whereIn('meeting_room_id', $roomIds)->delete();
                    // hapus rooms (force, karena pakai soft deletes)
                    $roomsDeleted   += MeetingRoom::whereIn('id', $roomIds)->forceDelete();
                }

                // hapus venues (force)
                $venuesDeleted += MeetingVenue::whereIn('id', $toDeleteVenueIds)->forceDelete();
            }

            // ===== Upsert ROOMS & LAYOUTS per venue (dan purge yang hilang) ===
            $roomsUpserted = 0;
            $layoutsUpserted = 0;
            foreach ($venueIdMap as $ext => $venueId) {
                // ---- ROOMS ----
                $apiRoomNames = $roomsByVenue[$ext] ?? [];

                // DB rooms map: [name => id]
                $dbRooms = MeetingRoom::where('meeting_venue_id', $venueId)
                    ->get(['id', 'name'])
                    ->keyBy('name')
                    ->map(fn($m) => $m->id)
                    ->all();

                // upsert rooms
                foreach ($apiRoomNames as $roomName) {
                    if (isset($dbRooms[$roomName])) {
                        MeetingRoom::where('id', $dbRooms[$roomName])->update(['updated_at' => now()]);
                    } else {
                        $newId = (string) Str::uuid();
                        MeetingRoom::create([
                            'id'               => $newId,
                            'meeting_venue_id' => $venueId,
                            'name'             => $roomName,
                            'created_at'       => now(),
                            'updated_at'       => now(),
                        ]);
                        $dbRooms[$roomName] = $newId;
                    }
                    $roomsUpserted++;
                }

                // purge rooms yang hilang
                $missingRoomNames = array_diff(array_keys($dbRooms), $apiRoomNames);
                if (!empty($missingRoomNames)) {
                    $missingRoomIds = array_values(array_intersect_key($dbRooms, array_flip($missingRoomNames)));
                    if (!empty($missingRoomIds)) {
                        $layoutsDeleted += MeetingRoomLayout::whereIn('meeting_room_id', $missingRoomIds)->delete();
                        $roomsDeleted   += MeetingRoom::whereIn('id', $missingRoomIds)->forceDelete();
                    }
                }

                // ---- LAYOUTS per room ----
                $apiLayoutsForVenue = $layoutsByVenueRoom[$ext] ?? []; // [roomName => [layout => capacity]]
                foreach ($apiLayoutsForVenue as $roomName => $layoutMap) {
                    $roomId = $dbRooms[$roomName] ?? null;
                    if (!$roomId) continue;

                    // DB layouts map: [layout => id]
                    $dbLayouts = MeetingRoomLayout::where('meeting_room_id', $roomId)
                        ->get(['id', 'layout'])
                        ->keyBy('layout')
                        ->map(fn($m) => $m->id)
                        ->all();

                    // upsert layouts
                    foreach ($layoutMap as $layout => $cap) {
                        if (isset($dbLayouts[$layout])) {
                            MeetingRoomLayout::where('id', $dbLayouts[$layout])->update([
                                'capacity'   => (int) $cap,
                                'updated_at' => now(),
                            ]);
                        } else {
                            MeetingRoomLayout::create([
                                'id'              => (string) Str::uuid(),
                                'meeting_room_id' => $roomId,
                                'layout'          => $layout,
                                'capacity'        => (int) $cap,
                                'created_at'      => now(),
                                'updated_at'      => now(),
                            ]);
                            $dbLayouts[$layout] = true; // mark present
                        }
                        $layoutsUpserted++;
                    }

                    // purge layouts yang hilang
                    $missingLayouts = array_diff(array_keys($dbLayouts), array_keys($layoutMap));
                    if (!empty($missingLayouts)) {
                        $layoutsDeleted += MeetingRoomLayout::where('meeting_room_id', $roomId)
                            ->whereIn('layout', $missingLayouts)
                            ->delete();
                    }
                }
            }

            DB::commit();

            return [
                'venues_inserted'  => $venuesInserted,
                'venues_updated'   => $venuesUpdated,
                'venues_deleted'   => $venuesDeleted,
                'rooms_upserted'   => $roomsUpserted,
                'rooms_deleted'    => $roomsDeleted,
                'layouts_upserted' => $layoutsUpserted,
                'layouts_deleted'  => $layoutsDeleted,
            ];
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
