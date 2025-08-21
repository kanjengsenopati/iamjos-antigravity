<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MeetingRoom;
use App\Models\MeetingVenue;
use App\Models\MeetingRoomLayout;
use App\Services\ImageService;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    /**
     * Display rooms for a venue
     */
    public function index(MeetingVenue $venue)
    {
        $rooms = $venue->meeting_rooms()->with('meeting_room_layouts')->get();
        return view('admins.room.index', compact('venue', 'rooms'));
    }

    /**
     * Show the form for creating a new room
     */
    public function create(MeetingVenue $venue)
    {
        // Hitung kapasitas tersisa
        $usedCapacity = $venue->meeting_rooms()
            ->with('meeting_room_layouts')
            ->get()
            ->sum(function ($room) {
                return $room->meeting_room_layouts->max('capacity') ?? 0;
            });
        $remainingCapacity = $venue->max_capacity - $usedCapacity;

        return view('admins.room.create-edit', compact('venue', 'remainingCapacity'));
    }

    /**
     * Store a newly created room
     */
    public function store(Request $request, MeetingVenue $venue)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'layouts' => 'nullable|array',
            'layouts.*.layout' => 'required|string',
            'layouts.*.capacity' => 'nullable|integer|min:1'
        ]);

        // Custom validation: total kapasitas semua ruang tidak boleh melebihi max_capacity venue
        if (!empty($validated['layouts']) && $venue->max_capacity > 0) {
            // Hitung total kapasitas ruang yang sudah ada
            $existingRoomCapacity = $venue->meeting_rooms()
                ->with('meeting_room_layouts')
                ->get()
                ->sum(function ($room) {
                    return $room->meeting_room_layouts->max('capacity') ?? 0;
                });

            // Hitung kapasitas maksimal ruang baru yang akan ditambah
            $newRoomMaxCapacity = 0;
            foreach ($validated['layouts'] as $index => $layout) {
                if (isset($layout['capacity']) && $layout['capacity'] > $newRoomMaxCapacity) {
                    $newRoomMaxCapacity = $layout['capacity'];
                }
            }

            // Cek apakah total akan melebihi max capacity venue
            $totalCapacity = $existingRoomCapacity + $newRoomMaxCapacity;
            if ($totalCapacity > $venue->max_capacity) {
                return redirect()->back()
                    ->withErrors([
                        'layouts' => "Total kapasitas semua ruang ($totalCapacity orang) akan melebihi kapasitas maksimal venue ({$venue->max_capacity} orang). Sisa kapasitas yang tersedia: " . ($venue->max_capacity - $existingRoomCapacity) . " orang."
                    ])
                    ->withInput();
            }
        }

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $imageService = new ImageService();
            $photoResult = $imageService->storeSingleWebp(
                $request->file('photo'),
                maxWidth: 1200,
                quality: 85,
                dir: 'meeting-rooms'
            );
            $photoPath = $photoResult['path'];
        }

        $room = $venue->meeting_rooms()->create([
            'name' => $validated['name'],
            'photo' => $photoPath
        ]);

        // Add layouts if provided
        if (!empty($validated['layouts'])) {
            foreach ($validated['layouts'] as $layout) {
                $room->meeting_room_layouts()->create([
                    'layout' => $layout['layout'],
                    'capacity' => $layout['capacity'] ?? 0
                ]);
            }
        }

        return redirect()->route('venue.rooms.index', $venue->id)
            ->with('success', 'Meeting room berhasil ditambahkan.');
    }

    /**
     * Show the form for editing room
     */
    public function edit(MeetingVenue $venue, MeetingRoom $room)
    {
        $room->load('meeting_room_layouts');

        // Hitung kapasitas tersisa (tidak termasuk ruang yang sedang diedit)
        $usedCapacity = $venue->meeting_rooms()
            ->where('id', '!=', $room->id)
            ->with('meeting_room_layouts')
            ->get()
            ->sum(function ($room) {
                return $room->meeting_room_layouts->max('capacity') ?? 0;
            });
        $remainingCapacity = $venue->max_capacity - $usedCapacity;

        return view('admins.room.create-edit', compact('venue', 'room', 'remainingCapacity'));
    }

    /**
     * Update room
     */
    public function update(Request $request, MeetingVenue $venue, MeetingRoom $room)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'layouts' => 'nullable|array',
            'layouts.*.layout' => 'required|string',
            'layouts.*.capacity' => 'nullable|integer|min:1'
        ]);

        // Custom validation: total kapasitas semua ruang tidak boleh melebihi max_capacity venue
        if (!empty($validated['layouts']) && $venue->max_capacity > 0) {
            // Hitung total kapasitas ruang yang sudah ada (kecuali ruang yang sedang diedit)
            $existingRoomCapacity = $venue->meeting_rooms()
                ->where('id', '!=', $room->id)
                ->with('meeting_room_layouts')
                ->get()
                ->sum(function ($room) {
                    return $room->meeting_room_layouts->max('capacity') ?? 0;
                });

            // Hitung kapasitas maksimal ruang yang sedang diedit
            $newRoomMaxCapacity = 0;
            foreach ($validated['layouts'] as $index => $layout) {
                if (isset($layout['capacity']) && $layout['capacity'] > $newRoomMaxCapacity) {
                    $newRoomMaxCapacity = $layout['capacity'];
                }
            }

            // Cek apakah total akan melebihi max capacity venue
            $totalCapacity = $existingRoomCapacity + $newRoomMaxCapacity;
            if ($totalCapacity > $venue->max_capacity) {
                return redirect()->back()
                    ->withErrors([
                        'layouts' => "Total kapasitas semua ruang ($totalCapacity orang) akan melebihi kapasitas maksimal venue ({$venue->max_capacity} orang). Sisa kapasitas yang tersedia: " . ($venue->max_capacity - $existingRoomCapacity) . " orang."
                    ])
                    ->withInput();
            }
        }

        $updateData = [
            'name' => $validated['name']
        ];

        // Handle photo upload
        if ($request->hasFile('photo')) {
            // Delete old photo if exists
            if ($room->photo) {
                \Storage::disk('public')->delete($room->photo);
            }

            $imageService = new ImageService();
            $photoResult = $imageService->storeSingleWebp(
                $request->file('photo'),
                maxWidth: 1200,
                quality: 85,
                dir: 'meeting-rooms'
            );
            $updateData['photo'] = $photoResult['path'];
        }

        $room->update($updateData);

        // Update layouts
        $room->meeting_room_layouts()->delete();
        if (!empty($validated['layouts'])) {
            foreach ($validated['layouts'] as $layout) {
                $room->meeting_room_layouts()->create([
                    'layout' => $layout['layout'],
                    'capacity' => $layout['capacity'] ?? 0
                ]);
            }
        }

        return redirect()->route('venue.rooms.index', $venue->id)
            ->with('success', 'Meeting room berhasil diperbarui.');
    }

    /**
     * Remove room
     */
    public function destroy(MeetingVenue $venue, MeetingRoom $room)
    {
        // Delete photo if exists
        if ($room->photo) {
            \Storage::disk('public')->delete($room->photo);
        }

        $room->delete();
        return redirect()->route('venue.rooms.index', $venue->id)
            ->with('success', 'Meeting room berhasil dihapus.');
    }
}
