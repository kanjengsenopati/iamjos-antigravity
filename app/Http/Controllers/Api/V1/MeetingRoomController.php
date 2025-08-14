<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Regency;
use App\Models\Province;
use App\Models\MeetingVenue;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class MeetingRoomController extends Controller
{
    public function index(Request $request)
    {
        $data = MeetingVenue::with('meeting_rooms.meeting_room_layouts')
            ->when($request->input('search'), function ($query) use ($request) {
                $query->where('hotel', 'like', '%' . $request->input('search') . '%')
                    ->orWhere('address', 'like', '%' . $request->input('search') . '%')
                    ->orWhere('email', 'like', '%' . $request->input('search') . '%')
                    ->orWhere('phone', 'like', '%' . $request->input('search') . '%')
                    ->orWhereHas('meeting_rooms', function ($query) use ($request) {
                        $query->where('name', 'like', '%' . $request->input('search') . '%');
                    });
            })
            ->when($request->input('province_id'), function ($query) use ($request) {
                $query->where('province_id', $request->input('province_id'));
            })
            ->when($request->input('regency_id'), function ($query) use ($request) {
                $query->where('regency_id', $request->input('regency_id'));
            })
            ->when($request->filled('min_capacity') && $request->filled('max_capacity'), function ($query) use ($request) {
                $query->where(
                    'max_capacity',
                    '<=',
                    $request->input('max_capacity')
                )->where(
                    'max_capacity',
                    '>=',
                    $request->input('min_capacity')
                );
            })
            ->latest()->paginate(10);
        return $this->getSuccessResponse($data);
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
}
