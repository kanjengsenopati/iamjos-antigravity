<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Str;
use App\Services\ImageService;
use App\Models\AboutUsInformation;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\Admin\AboutUsInformationRequest;

class AboutUsInformationController extends Controller
{
    public function index()
    {
        $aboutUsInformation = AboutUsInformation::latest()->first();
        return view('admins.about-us.information.index', compact('aboutUsInformation'));
    }

    public function store(AboutUsInformationRequest $request, ImageService $imageService)
    {
        $data = $request->validated();

        // Ambil record lama saat update (jika ada id)
        $info = $request->filled('id')
            ? AboutUsInformation::find($request->id)
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
                dir: 'about-us'
            );

            // Simpan path relatif (konsisten dengan pola kamu sebelumnya)
            $data['image'] = 'storage/' . $saved['path'];
        }

        // Simpan data: update jika ada id, jika tidak create
        if ($info) {
            $info->update($data);
        } else {
            $info = AboutUsInformation::create($data);
        }

        return redirect()
            ->route('aboutus-information.index')
            ->with('success', 'Informasi Tentang Kami berhasil diperbarui.');
    }
}
