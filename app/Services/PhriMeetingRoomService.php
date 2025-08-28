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

/**
 * PHRI Meeting Room Sync Service — revised
 * - Mencegah dan membersihkan duplikasi meeting_venues.external_id
 * - Memastikan meeting_rooms.meeting_venue_id selalu menunjuk ke venue yang valid
 * - Mem-purge orphan rooms/layouts yang tidak punya induk venue/room
 * - Upsert dengan kunci natural: venue.external_id, room (venue_id + name), layout (room_id + type_id)
 */
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

    /** Fetch raw records from PHRI API with conditional GET support. */
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

    /** Normalize API payload. */
    protected function normalize(array $records): array
    {
        $venues  = [];
        $rooms   = [];
        $layouts = [];

        foreach ($records as $row) {
            // Jangan paksa ke int; simpan apa adanya sebagai string untuk mencegah collision
            $extIdRaw = Arr::get($row, 'id');
            if ($extIdRaw === null || $extIdRaw === '') continue;
            $extId = (string) $extIdRaw; // simpan sebagai string

            $venues[] = [
                'external_id'      => $extId,
                'phri_province_id' => (string) (Arr::get($row, 'id_province') ?? Arr::get($row, 'id_provinsi') ?? ''),
                'phri_regency_id'  => (string) (Arr::get($row, 'id_city') ?? ''),
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
                        $layoutName = $this->normalizeLayoutLabel((string) $k);
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

    /** Normalize raw layout key into Title Case label. */
    protected function normalizeLayoutLabel(string $raw): string
    {
        $label = Str::of($raw)->lower()
            ->replace(['(', ')'], '')
            ->replaceMatches('/\s+/', ' ')
            ->trim();
        return Str::of($label)->title();
    }

    /** Map PHRI province/regency external IDs to local UUIDs on venues array. */
    protected function mapLocalIds(array $venues): array
    {
        // Catatan: di sini diasumsikan kolom provinces.external_id & regencies.external_id bertipe string
        $provMap = Province::query()->pluck('id', 'external_id')->all(); // [phri_id => uuid]
        $regMap  = Regency::query()->pluck('id', 'external_id')->all();  // [phri_id => uuid]

        foreach ($venues as &$v) {
            $v['province_id'] = $provMap[(string) $v['phri_province_id']] ?? null;
            $v['regency_id']  = $regMap[(string) $v['phri_regency_id']]   ?? null;
        }
        unset($v);

        return $venues;
    }

    /** Ensure all meeting room types exist (by name). Return map: [name => id] */
    protected function ensureMeetingRoomTypes(array $allLayoutNames): array
    {
        $allLayoutNames = array_values(array_unique(
            array_filter(array_map('strval', $allLayoutNames), fn($v) => trim($v) !== '')
        ));
        if (empty($allLayoutNames)) return [];

        $existing = MeetingRoomType::query()
            ->whereIn('name', $allLayoutNames)
            ->get(['id', 'name'])
            ->keyBy('name');

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
            $existing = MeetingRoomType::query()
                ->whereIn('name', $allLayoutNames)
                ->get(['id', 'name'])
                ->keyBy('name');
        }

        return collect($existing)->map(fn($m) => $m->id)->all();
    }

    /**
     * PRE-CLEANUP 1: Deduplicate meeting_venues by external_id.
     * - Pilih venue "kanonis" (created_at paling tua) per external_id
     * - Reassign meeting_rooms dari duplikat ke kanonis
     * - Hapus duplikat (force)
     */
    protected function deduplicateVenuesByExternalId(): array
    {
        $dups = DB::table('meeting_venues')
            ->select('external_id', DB::raw('COUNT(*) as c'))
            ->groupBy('external_id')
            ->having('c', '>', 1)
            ->pluck('external_id')
            ->all();

        $reassignedRooms = 0;
        $deletedVenues   = 0;

        if (empty($dups)) {
            return compact('reassignedRooms', 'deletedVenues');
        }

        foreach ($dups as $ext) {
            $venues = MeetingVenue::where('external_id', $ext)
                ->orderBy('created_at', 'asc')
                ->get(['id', 'external_id', 'created_at']);

            if ($venues->count() < 2) continue;

            $canonical = $venues->first();
            $dupes     = $venues->slice(1)->pluck('id')->all();

            if (!empty($dupes)) {
                // Pindahkan rooms ke canonical
                $reassignedRooms += MeetingRoom::whereIn('meeting_venue_id', $dupes)
                    ->update(['meeting_venue_id' => $canonical->id, 'updated_at' => now()]);

                // Hapus duplikat venue
                $deletedVenues += MeetingVenue::whereIn('id', $dupes)->forceDelete();
            }
        }

        return compact('reassignedRooms', 'deletedVenues');
    }

    /**
     * PRE-CLEANUP 2: Purge orphan rooms/layouts:
     * - Hapus MeetingRoom yang meeting_venue_id tidak ada di tabel meeting_venues
     * - Hapus MeetingRoomLayout yang meeting_room_id tidak ada di tabel meeting_rooms
     */
    protected function purgeOrphans(): array
    {
        $validVenueIds = MeetingVenue::query()->pluck('id')->all();
        $validRoomIds  = MeetingRoom::query()->pluck('id')->all();

        $deletedLayouts = 0;
        $deletedRooms   = 0;

        // Layouts orphan karena room hilang
        $deletedLayouts += MeetingRoomLayout::whereNotIn('meeting_room_id', $validRoomIds ?: ['_none_'])->delete();

        // Rooms orphan karena venue hilang
        $deletedRooms   += MeetingRoom::whereNotIn('meeting_venue_id', $validVenueIds ?: ['_none_'])->forceDelete();

        return compact('deletedLayouts', 'deletedRooms');
    }

    /** Sync entry point */
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
                'reassigned_rooms' => 0,
                'dedup_venues'     => 0,
                'purged_orphans'   => ['rooms' => 0, 'layouts' => 0],
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

            $ext      = (string) $lt['venue_external_id'];
            $roomName = $lt['room_name'];
            $cap      = (int) $lt['capacity'];

            $layoutsByVenueRoom[$ext][$roomName][$typeId] = $cap;
        }

        // ===== PRE-CLEANUP: rapikan duplikat & orphan sebelum upsert =====
        DB::beginTransaction();
        try {
            $dedupStats = $this->deduplicateVenuesByExternalId();
            $purge1     = $this->purgeOrphans();
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        // ===== Upsert VENUE & catat UUID lokal =====
        $existingVenues = MeetingVenue::query()->pluck('id', 'external_id')->all(); // [ext(string) => uuid]
        $apiExtIds      = array_values(array_unique(array_map(fn($v) => (string) $v['external_id'], $venues)));

        $venueIdMap     = []; // [ext => uuid]
        $venuesInserted = 0;
        $venuesUpdated  = 0;

        DB::beginTransaction();
        try {
            foreach ($venues as $v) {
                $ext = (string) $v['external_id'];
                $payload = [
                    'phri_province_id' => (string) $v['phri_province_id'],
                    'phri_regency_id'  => (string) $v['phri_regency_id'],
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

            // Hapus venue yang tidak ada di API (beserta turunannya)
            $toDeleteExt   = array_diff(array_keys($existingVenues), $apiExtIds);
            $venuesDeleted = 0;
            $roomsDeleted  = 0;
            $layoutsDeleted = 0;

            if (!empty($toDeleteExt)) {
                $toDeleteVenueIds = array_values(array_map(fn($ext) => $existingVenues[$ext], $toDeleteExt));

                $roomIds = MeetingRoom::whereIn('meeting_venue_id', $toDeleteVenueIds)->pluck('id')->all();
                if (!empty($roomIds)) {
                    $layoutsDeleted += MeetingRoomLayout::whereIn('meeting_room_id', $roomIds)->delete();
                    $roomsDeleted   += MeetingRoom::whereIn('id', $roomIds)->forceDelete();
                }

                $venuesDeleted += MeetingVenue::whereIn('id', $toDeleteVenueIds)->forceDelete();
            }

            // ===== Upsert ROOMS & LAYOUTS per venue (dan purge yang hilang) =====
            $roomsUpserted    = 0;
            $layoutsUpserted  = 0;

            foreach ($venueIdMap as $ext => $venueId) {
                $apiRoomNames = $roomsByVenue[$ext] ?? [];

                // DB rooms map: [name => id]
                $dbRooms = MeetingRoom::where('meeting_venue_id', $venueId)
                    ->get(['id', 'name'])
                    ->keyBy('name')
                    ->map(fn($m) => $m->id)
                    ->all();

                // upsert rooms via natural key (venue_id + name)
                foreach ($apiRoomNames as $roomName) {
                    $roomId = $dbRooms[$roomName] ?? null;
                    if ($roomId) {
                        MeetingRoom::where('id', $roomId)->update(['updated_at' => now()]);
                    } else {
                        $roomId = (string) Str::uuid();
                        MeetingRoom::create([
                            'id'               => $roomId,
                            'meeting_venue_id' => $venueId,
                            'name'             => $roomName,
                            'created_at'       => now(),
                            'updated_at'       => now(),
                        ]);
                        $dbRooms[$roomName] = $roomId;
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

            // FINAL CLEANUP: pastikan tidak ada orphan tersisa setelah upsert
            $purge2 = $this->purgeOrphans();

            return [
                'venues_inserted'   => $venuesInserted,
                'venues_updated'    => $venuesUpdated,
                'venues_deleted'    => $venuesDeleted ?? 0,
                'rooms_upserted'    => $roomsUpserted,
                'rooms_deleted'     => $roomsDeleted ?? 0,
                'layouts_upserted'  => $layoutsUpserted,
                'layouts_deleted'   => $layoutsDeleted ?? 0,
                'reassigned_rooms'  => $dedupStats['reassignedRooms'] ?? 0,
                'dedup_venues'      => $dedupStats['deletedVenues'] ?? 0,
                'purged_orphans'    => [
                    'rooms'   => ($purge1['deletedRooms'] ?? 0) + ($purge2['deletedRooms'] ?? 0),
                    'layouts' => ($purge1['deletedLayouts'] ?? 0) + ($purge2['deletedLayouts'] ?? 0),
                ],
            ];
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
