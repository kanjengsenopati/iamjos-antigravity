<?php

namespace App\Http\Controllers\Admin;

use App\Models\MeetingRoom;
use App\Models\MeetingVenue;
use App\Models\Province;
use App\Models\Regency;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Controller;
use App\Http\Requests\MeetingVenueRequest;
use App\Services\PhriMeetingRoomService;
use App\Services\ImageService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class MeetingRoomController extends Controller
{
    protected $phriMeetingRoomService;

    public function __construct(PhriMeetingRoomService $phriMeetingRoomService)
    {
        $this->phriMeetingRoomService = $phriMeetingRoomService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (request()->ajax()) {
            $data = MeetingVenue::with(['meeting_rooms'])->orderBy('hotel');
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function ($data) {
                    $actionShow = route('meeting-room.show', $data->id);
                    $actionEdit = route('meeting-room.edit', $data->id);
                    $actionDelete = route('meeting-room.destroy', $data->id);
                    return "<div class='d-flex justify-content-center'>" .
                        view('components.action.show', ['action' => $actionShow]) .
                        view('components.action.edit', ['action' => $actionEdit]) .
                        view('components.action.delete', ['action' => $actionDelete, 'id' => $data->id]) .
                        "</div>";
                })
                ->addColumn('photo', function ($data) {
                    if ($data->photo) {
                        return '<img src="' . Storage::url($data->photo) . '" alt="' . $data->hotel . '" class="img-thumbnail" style="max-width: 80px; max-height: 60px;">';
                    }
                    return '<span class="text-muted">Tidak ada foto</span>';
                })
                ->addColumn('province_name', function ($data) {
                    return $data->province_name ?: '-';
                })
                ->addColumn('city_name', function ($data) {
                    return $data->city_name ?: '-';
                })
                ->addColumn('rooms_count', function ($data) {
                    return $data->meeting_rooms->count() . ' ruang';
                })
                ->addColumn('max_capacity', function ($data) {
                    return $data->max_capacity ? $data->max_capacity . ' orang' : '-';
                })
                ->rawColumns(['action', 'photo'])
                ->make(true);
        }
        return view('admins.meeting-room.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $provinces = Province::orderBy('name')->get();
        return view('admins.meeting-room.create-edit', compact('provinces'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(MeetingVenueRequest $request)
    {
        $validated = $request->validated();

        $province = Province::find($validated['province_id']);
        $regency = Regency::find($validated['regency_id']);

        $data = [
            'hotel' => $validated['hotel'],
            'address' => $validated['address'],
            'province_id' => $validated['province_id'],
            'regency_id' => $validated['regency_id'],
            'province_name' => $province->name,
            'city_name' => $regency->name,
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'max_capacity' => $validated['max_capacity'] ?? null,
        ];

        // Handle photo upload
        if ($request->hasFile('photo')) {
            $imageService = app(ImageService::class);
            $data['photo'] = $imageService->storeImage($request->file('photo'), 'meeting-venues');
        }

        MeetingVenue::create($data);

        return redirect()->route('meeting-room.index')->with('success', 'Meeting venue berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(MeetingVenue $meetingRoom)
    {
        $meetingRoom->load(['meeting_rooms.meeting_room_layouts']);
        return view('admins.meeting-room.show', compact('meetingRoom'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(MeetingVenue $meetingRoom)
    {
        $provinces = Province::orderBy('name')->get();
        $regencies = Regency::where('province_id', $meetingRoom->province_id)->orderBy('name')->get();
        return view('admins.meeting-room.create-edit', compact('meetingRoom', 'provinces', 'regencies'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(MeetingVenueRequest $request, MeetingVenue $meetingRoom)
    {
        $validated = $request->validated();

        $province = Province::find($validated['province_id']);
        $regency = Regency::find($validated['regency_id']);

        $data = [
            'hotel' => $validated['hotel'],
            'address' => $validated['address'],
            'province_id' => $validated['province_id'],
            'regency_id' => $validated['regency_id'],
            'province_name' => $province->name,
            'city_name' => $regency->name,
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'max_capacity' => $validated['max_capacity'] ?? null,
        ];

        // Handle photo upload
        if ($request->hasFile('photo')) {
            $imageService = app(ImageService::class);

            // Delete old photo if exists
            if ($meetingRoom->photo) {
                Storage::delete($meetingRoom->photo);
            }

            $data['photo'] = $imageService->storeImage($request->file('photo'), 'meeting-venues');
        }

        $meetingRoom->update($data);

        return redirect()->route('meeting-room.index')->with('success', 'Meeting venue berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MeetingVenue $meetingRoom)
    {
        // Delete photo if exists
        if ($meetingRoom->photo) {
            Storage::delete($meetingRoom->photo);
        }

        $meetingRoom->delete();
        return redirect()->route('meeting-room.index')->with('success', 'Meeting venue berhasil dihapus.');
    }

    /**
     * Sync data from PHRI API
     */
    public function sync()
    {
        try {
            $result = $this->phriMeetingRoomService->sync();

            if (isset($result['not_modified']) && $result['not_modified']) {
                return redirect()->route('meeting-room.index')->with('info', 'Data meeting room sudah up-to-date, tidak ada perubahan.');
            }

            $message = sprintf(
                'Sinkronisasi berhasil! Venue: %d ditambah, %d diperbarui. Ruang: %d diperbarui. Layout: %d diperbarui.',
                $result['venues_inserted'] ?? 0,
                $result['venues_updated'] ?? 0,
                $result['rooms_upserted'] ?? 0,
                $result['layouts_upserted'] ?? 0
            );

            return redirect()->route('meeting-room.index')->with('success', $message);
        } catch (\Exception $e) {
            Log::error('Error syncing meeting room data: ' . $e->getMessage());
            return redirect()->route('meeting-room.index')->with('error', 'Terjadi kesalahan saat sinkronisasi data: ' . $e->getMessage());
        }
    }

    /**
     * Get regencies by province (AJAX)
     */
    public function getRegencies($provinceId)
    {
        try {
            \Log::info('Getting regencies for province ID: ' . $provinceId);
            $regencies = Regency::where('province_id', $provinceId)->orderBy('name')->get(['id', 'name']);
            \Log::info('Found regencies: ' . $regencies->count());
            return response()->json($regencies);
        } catch (\Exception $e) {
            \Log::error('Error getting regencies: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to load regencies'], 500);
        }
    }
}
