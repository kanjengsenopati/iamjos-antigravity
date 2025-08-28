<?php

namespace App\Http\Controllers\Admin;

use App\Models\MeetingRoom;
use App\Models\MeetingVenue;
use App\Models\MeetingVenueGallery;
use App\Models\Province;
use App\Models\Regency;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UploadGalleryMeetingVenueRequest;
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
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = MeetingVenue::query();

            // Apply search filters
            if ($request->filled('search_venue')) {
                $searchTerm = $request->search_venue;
                $query->where(function ($q) use ($searchTerm) {
                    $q->whereRaw('LOWER(name) LIKE LOWER(?)', ['%' . $searchTerm . '%'])
                        ->orWhereRaw('LOWER(address) LIKE LOWER(?)', ['%' . $searchTerm . '%'])
                        ->orWhereRaw('LOWER(province_name) LIKE LOWER(?)', ['%' . $searchTerm . '%'])
                        ->orWhereRaw('LOWER(city_name) LIKE LOWER(?)', ['%' . $searchTerm . '%']);
                });
            }

            if ($request->filled('filter_province')) {
                $query->whereRaw('LOWER(province_name) = LOWER(?)', [$request->filter_province]);
            }

            if ($request->filled('filter_city')) {
                $query->whereRaw('LOWER(city_name) = LOWER(?)', [$request->filter_city]);
            }

            if ($request->filled('filter_capacity')) {
                $query->where('max_capacity', '>=', $request->filter_capacity);
            }

            $data = $query->latest();

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function ($data) {
                    $actionShow = route('meeting-room.show', $data->id);
                    $actionEdit = route('meeting-room.edit', $data->id);
                    $actionDelete = route('meeting-room.destroy', $data->id);
                    // $actionRooms = route('venue.rooms.index', $data->id);
                    return "<div class='d-flex justify-content-center'>" .
                        // "<a href='{$actionRooms}' class='btn btn-success btn-sm me-1' title='Kelola Ruang'><i class='fa fa-door-open'></i></a>" .
                        view('components.action.show', ['action' => $actionShow]) .
                        view('components.action.edit', ['action' => $actionEdit]) .
                        view('components.action.delete', ['action' => $actionDelete, 'id' => $data->id]) .
                        "</div>";
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
                ->rawColumns(['action'])
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
        $data = $request->validated();

        if ($request->hasFile('thumbnail')) {
            $imageService = app(ImageService::class);
            $thumbnailPath = $imageService->storeImage($request->file('thumbnail'), 'images/meeting-venues');
            $data['thumbnail'] = 'storage/' . $thumbnailPath;
        }

        $MeetingVenue = MeetingVenue::create($data);

        return redirect()->route('meeting-room.index')->with('success', 'Meeting venue berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(MeetingVenue $meetingRoom)
    {
        $meetingRoom->load(['meeting_rooms.meeting_room_layouts', 'galleries']);
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
        $data = $request->validated();

        if ($request->hasFile('thumbnail')) {
            file_exists($meetingRoom->thumbnail) ? unlink($meetingRoom->thumbnail) : null;
            $imageService = app(ImageService::class);
            $thumbnailPath = $imageService->storeImage($request->file('thumbnail'), 'images/meeting-venues');
            $data['thumbnail'] = 'storage/' . $thumbnailPath;
        }

        $meetingRoom->update($data);

        return redirect()->route('meeting-room.index')->with('success', 'Meeting venue berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MeetingVenue $meetingRoom)
    {
        file_exists($meetingRoom->thumbnail) ? unlink($meetingRoom->thumbnail) : null;

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

    /**
     * Get filter data for search
     */
    public function getFilterData()
    {
        $provinces = MeetingVenue::distinct()
            ->whereNotNull('province_name')
            ->orderBy('province_name')
            ->pluck('province_name');

        $cities = MeetingVenue::distinct()
            ->whereNotNull('city_name')
            ->orderBy('city_name')
            ->pluck('city_name');

        return response()->json([
            'provinces' => $provinces,
            'cities' => $cities
        ]);
    }

    /**
     * Get cities by province name for filter
     */
    public function getCitiesByProvince($provinceName)
    {
        $cities = MeetingVenue::whereRaw('LOWER(province_name) = LOWER(?)', [$provinceName])
            ->distinct()
            ->whereNotNull('city_name')
            ->orderBy('city_name')
            ->pluck('city_name')
            ->map(function ($city) {
                return ['name' => $city];
            });

        return response()->json($cities);
    }

    /**
     * Upload new gallery images (multiple)
     */
    public function uploadGallery(UploadGalleryMeetingVenueRequest $request, MeetingVenue $meetingRoom)
    {
        try {
            $imageService = app(ImageService::class);
            $uploadedCount = 0;
            $errors = [];

            foreach ($request->file('gallery_images') as $index => $image) {
                try {
                    $imagePath = $imageService->storeImage($image, 'meeting-venues/gallery');

                    // Create gallery record
                    MeetingVenueGallery::create([
                        'meeting_venue_id' => $meetingRoom->id,
                        'image' => 'storage/' . $imagePath,
                    ]);

                    $uploadedCount++;
                } catch (\Exception $e) {
                    Log::error('Error uploading single gallery image: ' . $e->getMessage());
                    $errors[] = "Gambar ke-" . ($index + 1) . " gagal diupload: " . $e->getMessage();
                }
            }

            if ($uploadedCount > 0) {
                $message = "Berhasil mengupload {$uploadedCount} gambar";
                if (count($errors) > 0) {
                    $message .= ". " . count($errors) . " gambar gagal diupload.";
                }

                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'uploaded_count' => $uploadedCount,
                    'errors' => $errors,
                    'redirect' => route('meeting-room.show', $meetingRoom->id)
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Semua gambar gagal diupload',
                    'errors' => $errors
                ], 500);
            }
        } catch (\Exception $e) {
            Log::error('Error uploading gallery images: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupload gambar: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete gallery image
     */
    public function deleteGallery(MeetingVenue $meetingRoom, MeetingVenueGallery $gallery)
    {
        try {
            // Delete file from storage
            if (file_exists($gallery->image)) {
                unlink($gallery->image);
            }

            // Delete record from database
            $gallery->delete();

            return response()->json([
                'success' => true,
                'message' => 'Gambar berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting gallery image: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus gambar: ' . $e->getMessage()
            ], 500);
        }
    }
}
