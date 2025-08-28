<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Regency;
use App\Models\Province;
use App\Models\MeetingVenue;
use Illuminate\Http\Request;
use App\Models\MeetingRoomInfo;
use App\Http\Controllers\Controller;

class MeetingRoomController extends Controller
{

    public function index(Request $request)
    {
        $meetingRoomInformation = MeetingRoomInfo::latest()->first();

        $data = MeetingVenue::query()
            ->with('meeting_rooms.meeting_room_layouts')

            // === SEARCH (digroup agar aman dengan orWhere) ===
            ->when($request->filled('search'), function ($q) use ($request) {
                $s = $request->input('search');
                $q->where(function ($qq) use ($s) {
                    $qq->where('hotel', 'like', "%{$s}%")
                        ->orWhere('address', 'like', "%{$s}%")
                        ->orWhere('email', 'like', "%{$s}%")
                        ->orWhere('phone', 'like', "%{$s}%")
                        ->orWhereHas('meeting_rooms', function ($qr) use ($s) {
                            $qr->where('name', 'like', "%{$s}%");
                        });
                });
            })

            // === FILTER WILAYAH ===
            ->when($request->filled('province_id'), fn($q) => $q->where('province_id', $request->input('province_id')))
            ->when($request->filled('regency_id'),  fn($q) => $q->where('regency_id',  $request->input('regency_id')))

            // === MULTI KAPASITAS (min/max/range[]) ===
            ->when($this->hasCapacityRanges($request), function ($q) use ($request) {
                $ranges = $this->normalizeCapacityRanges($request); // -> array [['min'=>?, 'max'=>?], ...]
                $q->where(function ($w) use ($ranges) {
                    foreach ($ranges as $i => $r) {
                        $min = $r['min'];
                        $max = $r['max'];
                        if (!is_null($min) && !is_null($max)) {
                            $i === 0
                                ? $w->whereBetween('max_capacity', [$min, $max])
                                : $w->orWhereBetween('max_capacity', [$min, $max]);
                        } elseif (!is_null($min)) {
                            $i === 0
                                ? $w->where('max_capacity', '>=', $min)
                                : $w->orWhere('max_capacity', '>=', $min);
                        } elseif (!is_null($max)) {
                            $i === 0
                                ? $w->where('max_capacity', '<=', $max)
                                : $w->orWhere('max_capacity', '<=', $max);
                        }
                    }
                });
            })

            ->latest()
            ->paginate(10);

        return $this->getSuccessResponse([
            'meeting_room_information' => $meetingRoomInformation,
            'meeting_venues' => $data,
        ]);
    }

    /**
     * Cek apakah ada parameter kapasitas dalam format apa pun.
     */
    private function hasCapacityRanges(Request $request): bool
    {
        return $request->filled('capacity_ranges')
            || $request->filled('min_capacity')
            || $request->filled('max_capacity');
    }

    /**
     * Normalisasi semua format input kapasitas menjadi array [['min'=>int|null, 'max'=>int|null], ...]
     * - Dukung:
     *   - min_capacity[] & max_capacity[] (dipasangkan per index)
     *   - capacity_ranges[] berupa "min-max" (contoh "0-50", "200-")
     *   - single min_capacity / max_capacity
     */
    private function normalizeCapacityRanges(Request $request): array
    {
        $out = [];

        // 1) Array pasangan min_capacity[] & max_capacity[]
        if (is_array($request->input('min_capacity')) || is_array($request->input('max_capacity'))) {
            $mins  = (array) $request->input('min_capacity', []);
            $maxs  = (array) $request->input('max_capacity', []);
            $count = max(count($mins), count($maxs));

            for ($i = 0; $i < $count; $i++) {
                $min = array_key_exists($i, $mins) && $mins[$i] !== '' ? (int) $mins[$i] : null;
                $max = array_key_exists($i, $maxs) && $maxs[$i] !== '' ? (int) $maxs[$i] : null;

                if (!is_null($min) || !is_null($max)) {
                    if (!is_null($min) && !is_null($max) && $min > $max) {
                        [$min, $max] = [$max, $min]; // swap jika kebalik
                    }
                    $out[] = ['min' => $min, 'max' => $max];
                }
            }

            return $out;
        }

        // 2) capacity_ranges[] string "min-max" (boleh kosong salah satu sisi)
        if (is_array($request->input('capacity_ranges'))) {
            foreach ($request->input('capacity_ranges', []) as $r) {
                $min = null;
                $max = null;

                if (is_array($r)) {
                    $min = isset($r['min']) && $r['min'] !== '' ? (int) $r['min'] : null;
                    $max = isset($r['max']) && $r['max'] !== '' ? (int) $r['max'] : null;
                } else {
                    // contoh: "0-50", "200-", "-150"
                    $parts = preg_split('/\s*-\s*/', (string) $r, 2);
                    if (isset($parts[0]) && $parts[0] !== '') $min = (int) $parts[0];
                    if (isset($parts[1]) && $parts[1] !== '') $max = (int) $parts[1];
                }

                if (!is_null($min) || !is_null($max)) {
                    if (!is_null($min) && !is_null($max) && $min > $max) {
                        [$min, $max] = [$max, $min];
                    }
                    $out[] = ['min' => $min, 'max' => $max];
                }
            }

            return $out;
        }

        // 3) Single min_capacity / max_capacity
        $min = $request->filled('min_capacity') ? (int) $request->input('min_capacity') : null;
        $max = $request->filled('max_capacity') ? (int) $request->input('max_capacity') : null;
        if (!is_null($min) || !is_null($max)) {
            if (!is_null($min) && !is_null($max) && $min > $max) {
                [$min, $max] = [$max, $min];
            }
            $out[] = ['min' => $min, 'max' => $max];
        }

        return $out;
    }

    public function province(Request $request)
    {
        $data = Province::when($request->input('search'), function ($query) use ($request) {
            $query->where('name', 'like', '%' . $request->input('search') . '%');
        })->orderBy('name')->get();
        return $this->getSuccessResponse($data);
    }

    public function regency(Request $request)
    {
        $data = Regency::with('province')->when($request->input('search'), function ($query) use ($request) {
            $query->where('name', 'like', '%' . $request->input('search') . '%');
        })
            ->when($request->input('province_id'), function ($query) use ($request) {
                $query->where('province_id', $request->input('province_id'));
            })
            ->orderBy('name')->get();
        return $this->getSuccessResponse($data);
    }

    public function filterCapacity(Request $request)
    {
        $bins = [
            ['label' => '0-500',     'min' => 0,    'max' => 500],
            ['label' => '501-1000',  'min' => 501,  'max' => 1000],
            ['label' => '1001-1500', 'min' => 1001, 'max' => 1500],
            ['label' => '1501+',     'min' => 1501, 'max' => null],
        ];

        // inisialisasi counter
        $counters = [];
        foreach ($bins as $b) {
            $counters[$b['label']] = $b + ['count' => 0];
        }

        $capacities = MeetingVenue::with('meeting_rooms.meeting_room_layouts')
            ->get()
            ->flatMap(fn($venue) => $venue->meeting_rooms->flatMap(
                fn($room) => $room->meeting_room_layouts->pluck('capacity')
            ))
            ->filter()
            ->all();

        foreach ($capacities as $cap) {
            foreach ($bins as $b) {
                if ($cap >= $b['min'] && (is_null($b['max']) || $cap <= $b['max'])) {
                    $counters[$b['label']]['count']++;
                    break;
                }
            }
        }

        // kembalikan sebagai urutan tetap (array of objects)
        $data = array_values($counters);
        return $this->getSuccessResponse($data);
    }

    public function show($id)
    {
        $meetingVenue = MeetingVenue::with('meeting_rooms.meeting_room_layouts.type', 'galleries')->find($id);
        if (!$meetingVenue) {
            return $this->failedResponse('Meeting venue not found', 404);
        }
        $meetingVenue['total_rooms'] = $meetingVenue->meeting_rooms->count();
        return $this->getSuccessResponse($meetingVenue);
    }
}
