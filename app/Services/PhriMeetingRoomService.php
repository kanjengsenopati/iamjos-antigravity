<?php

namespace App\Services;

use App\Models\Province;
use App\Models\Regency;
use App\Models\MeetingVenue;
use App\Models\MeetingRoom;
use App\Models\MeetingRoomLayout;
use App\Models\MeetingRoomType;
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

    /**
     * Fetch raw records from PHRI API with conditional GET support.
     *
     * @return array
     */
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

    /**
     * Normalize API payload to structured arrays:
     *  - venues:   list of venues
     *  - rooms:    list of rooms {venue_external_id, name}
     *  - layouts:  list of layouts {venue_external_id, room_name, layout_name, capacity}
     */
    protected function normalize(array $records): array
    {
        $venues  = [];
        $rooms   = [];
        $layouts = [];

        foreach ($records as $row) {
            $extId = (int) (Arr::get($row, 'id') ?? 0);
            if ($extId <= 0) continue;

            $venues[] = [
                'external_id'      => $extId,
                'phri_province_id' => (int) (Arr::get($row, 'id_province') ?? Arr::get($row, 'id_provinsi') ?? 0),
                'phri_regency_id'  => (int) (Arr::get($row, 'id_city') ?? 0),
                'province_name'    => trim((string) Arr::get($row, 'provinsi')),
                'city_name'        => trim((string) Arr::get($row, 'kota')),
                'name'             => trim((string) (Arr::get($row, 'hotel') ?? Arr::get($row, 'resort') ?? '')),
                'type'             => trim((string) Arr::get($row, 'hotel')) ? 'HOTEL' : (trim((string) Arr::get($row, 'resort')) ? 'RESORT' : null),
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
                        $layoutName = $this->normalizeLayoutLabel($k); // human-readable & konsisten
                        $cap        = (int) ($v ?? 0);
                        $layouts[]  = [
                            'venue_external_id' => $extId,
                            'room_name'         => $roomName,
                            'layout_name'       => $layoutName,
                            'capacity'          => $cap,
                        ];
                    }
                }
            }
        }

        return compact('venues', 'rooms', 'layouts');
    }

    /**
     * Normalize raw layout key into a stable, human-readable Title Case label.
     * Example: "classroom (30)" => "Classroom"
     */
    protected function normalizeLayoutLabel(string $raw): string
    {
        // hilangkan tanda kurung & kompres spasi
        $label = Str::of($raw)->lower()
            ->replace(['(', ')'], '')
            ->replaceMatches('/\s+/', ' ')
            ->trim();

        // bentuk Title Case
        return Str::of($label)->title();
    }

    /**
     * Map PHRI province/regency external IDs to local UUIDs on venues array.
     */
    protected function mapLocalIds(array $venues): array
    {
        // Province: provinces.external_id (PHRI) => provinces.id (UUID)
        $provMap = Province::query()->pluck('id', 'external_id')->all();   // [phri_id => uuid]
        // Regency:  regencies.external_id (PHRI) => regencies.id (UUID)
        $regMap  = Regency::query()->pluck('id', 'external_id')->all();    // [external_id => uuid]

        foreach ($venues as &$v) {
            $v['province_id'] = $provMap[$v['phri_province_id']] ?? null;
            $v['regency_id']  = $regMap[$v['phri_regency_id']]   ?? null;
        }
        unset($v);

        return $venues;
    }

    /**
     * Ensure all meeting room types exist (by name). Create missing types without touching image/is_active.
     * Returns map: [name => id]
     */
    protected function ensureMeetingRoomTypes(array $allLayoutNames): array
    {
        $allLayoutNames = array_values(array_unique(
            array_filter(array_map('strval', $allLayoutNames), fn($v) => trim($v) !== '')
        ));

        if (empty($allLayoutNames)) return [];

        // Ambil yang sudah ada
        $existing = MeetingRoomType::query()
            ->whereIn('name', $allLayoutNames)
            ->get(['id', 'name'])
            ->keyBy('name');

        // Siapkan yang belum ada
        $toInsert = [];
        foreach ($allLayoutNames as $name) {
            if (!isset($existing[$name])) {
                $toInsert[] = [
                    'id'         => (string) Str::uuid(),
                    'name'       => $name,
                    'is_active'  => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        if (!empty($toInsert)) {
            DB::table('meeting_room_types')->insert($toInsert);
            // refresh map
            $existing = MeetingRoomType::query()
                ->whereIn('name', $allLayoutNames)
                ->get(['id', 'name'])
                ->keyBy('name');
        }

        // Return map: name => id
        return collect($existing)->map(fn($m) => $m->id)->all();
    }

    /**
     * Sync entry point. Upserts venues/rooms/layouts; purges missing ones.
     */
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

        // Kumpulkan semua nama layout untuk memastikan tipe tersedia
        $allLayoutNames = [];
        foreach ($layouts as $lt) {
            if (!empty($lt['layout_name'])) $allLayoutNames[] = $lt['layout_name'];
        }
        $layoutTypeMap = $this->ensureMeetingRoomTypes($allLayoutNames); // [name => type_id]

        // Grouping bantu
        $roomsByVenue = [];
        foreach ($rooms as $r) {
            $roomsByVenue[$r['venue_external_id']][] = $r['name'];
        }
        $roomsByVenue = array_map(fn($names) => array_values(array_unique($names)), $roomsByVenue);

        // [venue_ext][room_name][meeting_room_type_id] = capacity
        $layoutsByVenueRoom = [];
        foreach ($layouts as $lt) {
            $name = $lt['layout_name'] ?? null;
            if (!$name) continue;
            $typeId = $layoutTypeMap[$name] ?? null;
            if (!$typeId) continue;

            $ext      = $lt['venue_external_id'];
            $roomName = $lt['room_name'];
            $cap      = (int) $lt['capacity'];

            $layoutsByVenueRoom[$ext][$roomName][$typeId] = $cap;
        }

        // ===== Upsert VENUE & catat UUID lokal =====
        $existingVenues = MeetingVenue::query()->pluck('id', 'external_id')->all(); // [ext => uuid]
        $apiExtIds      = array_values(array_unique(array_map(fn($v) => $v['external_id'], $venues)));

        $venueIdMap     = []; // [ext => uuid]
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
                    'name'             => $v['name'],
                    'type'             => $v['type'],
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

            // Hapus venue yang tidak ada di API
            $toDeleteExt   = array_diff(array_keys($existingVenues), $apiExtIds);
            $venuesDeleted = 0;
            $roomsDeleted  = 0;
            $layoutsDeleted = 0;

            if (!empty($toDeleteExt)) {
                $toDeleteVenueIds = array_values(array_map(fn($ext) => $existingVenues[$ext], $toDeleteExt));

                // ambil room ids di venue yang akan dihapus
                $roomIds = MeetingRoom::whereIn('meeting_venue_id', $toDeleteVenueIds)->pluck('id')->all();
                if (!empty($roomIds)) {
                    // hapus layouts terlebih dahulu
                    $layoutsDeleted += MeetingRoomLayout::whereIn('meeting_room_id', $roomIds)->delete();
                    // hapus rooms (force)
                    $roomsDeleted   += MeetingRoom::whereIn('id', $roomIds)->forceDelete();
                }

                // hapus venues (force)
                $venuesDeleted += MeetingVenue::whereIn('id', $toDeleteVenueIds)->forceDelete();
            }

            // ===== Upsert ROOMS & LAYOUTS per venue (dan purge yang hilang) =====
            $roomsUpserted    = 0;
            $layoutsUpserted  = 0;

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
                $apiLayoutsForVenue = $layoutsByVenueRoom[$ext] ?? []; // [roomName => [typeId => capacity]]
                foreach ($apiLayoutsForVenue as $roomName => $layoutMap) {
                    $roomId = $dbRooms[$roomName] ?? null;
                    if (!$roomId) continue;

                    // DB layouts map: [meeting_room_type_id => id]
                    $dbLayouts = MeetingRoomLayout::where('meeting_room_id', $roomId)
                        ->get(['id', 'meeting_room_type_id'])
                        ->keyBy('meeting_room_type_id')
                        ->map(fn($m) => $m->id)
                        ->all();

                    // upsert layouts
                    foreach ($layoutMap as $typeId => $cap) {
                        if (isset($dbLayouts[$typeId])) {
                            MeetingRoomLayout::where('id', $dbLayouts[$typeId])->update([
                                'capacity'   => (int) $cap,
                                'updated_at' => now(),
                            ]);
                        } else {
                            MeetingRoomLayout::create([
                                'id'                   => (string) Str::uuid(),
                                'meeting_room_id'      => $roomId,
                                'meeting_room_type_id' => $typeId,
                                'capacity'             => (int) $cap,
                                'created_at'           => now(),
                                'updated_at'           => now(),
                            ]);
                            $dbLayouts[$typeId] = true; // mark present
                        }
                        $layoutsUpserted++;
                    }

                    // purge layouts yang hilang (berdasarkan type_id)
                    $missingTypeIds = array_diff(array_keys($dbLayouts), array_keys($layoutMap));
                    if (!empty($missingTypeIds)) {
                        $layoutsDeleted += MeetingRoomLayout::where('meeting_room_id', $roomId)
                            ->whereIn('meeting_room_type_id', $missingTypeIds)
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
