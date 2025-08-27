<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Services\ImageService;
use App\Models\MeetingRoomInfo;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\Admin\MeetingRoomInfoRequest;

class MeetingRoomInfoController extends Controller
{
    public function index()
    {
        $meetingRoomInfo = MeetingRoomInfo::latest()->first();
        return view('admins.meeting-room-info.index', compact('meetingRoomInfo'));
    }

    public function store(MeetingRoomInfoRequest $request, ImageService $imageService)
    {
        $data = $request->validated();

        // Ambil record lama saat update (jika ada id)
        $info = $request->filled('id')
            ? MeetingRoomInfo::find($request->id)
            : null;

        if ($request->hasFile('image')) {
            // Hapus gambar lama hanya saat update & ada file lama
            if ($info && $info->image) {
                // Jika yang disimpan di DB "storage/xxx", buang prefix agar cocok dengan disk 'public'
                $oldPath = Str::of($info->image)->startsWith('storage/')
                    ? Str::after($info->image, 'storage/')
                    : $info->image;

                Storage::disk('public')->delete($oldPath);
            }

            $saved = $imageService->storeSingleWebp(
                file: $request->file('image'),
                maxWidth: null,
                quality: 50,
                disk: 'public',
                dir: 'meeting-room-info'
            );

            // Simpan path relatif (konsisten dengan pola kamu sebelumnya)
            $data['image'] = 'storage/' . $saved['path'];
        }

        // Simpan data: update jika ada id, jika tidak create
        if ($info) {
            $info->update($data);
        } else {
            $info = MeetingRoomInfo::create($data);
        }

        return redirect()
            ->route('meeting-room-info.index')
            ->with('success', 'Informasi Ruang Pertemuan berhasil diperbarui.');
    }
}
