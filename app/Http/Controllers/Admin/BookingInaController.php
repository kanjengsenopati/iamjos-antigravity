<?php

namespace App\Http\Controllers\Admin;

use App\Models\BookingIna;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Services\ImageService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BookingInaRequest;

class BookingInaController extends Controller
{
    public function index()
    {
        $bookingIna = BookingIna::latest()->first();
        return view('admins.booking-ina.index', compact('bookingIna'));
    }

    public function store(BookingInaRequest $request, ImageService $imageService)
    {
        $data = $request->validated();

        // Ambil record lama saat update (jika ada id)
        $bookingIna = $request->filled('id')
            ? BookingIna::find($request->id)
            : null;

        if ($request->hasFile('image')) {
            // Hapus gambar lama hanya saat update & ada file lama
            if ($bookingIna && file_exists($bookingIna->image)) {
                unlink($bookingIna->image);
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
        if ($bookingIna) {
            $bookingIna->update($data);
        } else {
            $bookingIna = BookingIna::create($data);
        }

        return redirect()
            ->route('booking-ina.index')
            ->with('success', 'Data berhasil disimpan');
    }
}
