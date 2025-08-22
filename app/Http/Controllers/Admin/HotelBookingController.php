<?php

namespace App\Http\Controllers\Admin;

use App\Models\HotelBooking;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\HotelBookingRequest;
use App\Services\ImageService;

class HotelBookingController extends Controller
{
    // 
    public function index()
    {
        if (request()->ajax()) {
            $data = HotelBooking::latest();
            return DataTables::of($data)
                ->editColumn('price', function ($data) {
                    return 'Rp. ' . number_format($data->price, 0, ',', '.');
                })
                ->editColumn('url', function ($data) {
                    return '<a href="' . $data->url . '" target="_blank">' . $data->url . '</a>';
                })
                ->addColumn('status', function ($data) {
                    return $data->is_active ? '<span class="badge bg-success text-white">Aktif</span>' : '<span class="badge bg-danger text-white">Tidak Aktif</span>';
                })
                ->addColumn('action', function ($data) {
                    $actionEdit = route('hotel-booking.edit', $data->id);
                    $actionDelete = route('hotel-booking.destroy', $data->id);
                    return "<div class='d-flex justify-content-center'>" .
                        view('components.action.edit', ['action' => $actionEdit]) .
                        view('components.action.delete', ['action' => $actionDelete, 'id' => $data->id]) .
                        "</div>";
                })
                ->rawColumns(['action', 'url', 'status'])
                ->make(true);
        }
        return view('admins.hotel-booking.index');
    }

    public function create()
    {
        return view('admins.hotel-booking.create-edit');
    }

    public function store(HotelBookingRequest $request, ImageService $imageService)
    {
        $data = $request->validated();
        $data['price'] = preg_replace('/[^0-9]/', '', $data['price']);
        if ($request->hasFile('image')) {
            $saved = $imageService->storeSingleWebp(
                file: $request->file('image'),
                maxWidth: null,        // contoh: 1200 untuk resize, null untuk tanpa resize
                quality: 50,
                disk: 'public',
                dir: 'home-sectors'    // simpan di storage/app/public/home-sectors
            );

            // Simpan 'path' relatif ke DB (lebih aman saat domain berubah)
            $data['image'] = 'storage/' . $saved['path'];
        }
        HotelBooking::create($data);
        return redirect()->route('hotel-booking.index')->with('success', 'Hotel Booking created successfully');
    }

    public function edit($id)
    {
        $hotelBooking = HotelBooking::findOrFail($id);
        return view('admins.hotel-booking.create-edit', compact('hotelBooking'));
    }

    public function update(HotelBookingRequest $request, $id, ImageService $imageService)
    {
        $hotelBooking = HotelBooking::findOrFail($id);
        $data = $request->validated();
        if ($request->hasFile('image')) {
            if (file_exists($hotelBooking->image)) {
                unlink($hotelBooking->image);
            }
            $saved = $imageService->storeSingleWebp(
                file: $request->file('image'),
                maxWidth: null,        // contoh: 1200 untuk resize, null untuk tanpa resize
                quality: 50,
                disk: 'public',
                dir: 'home-sectors'    // simpan di storage/app/public/home-sectors
            );

            // Simpan 'path' relatif ke DB (lebih aman saat domain berubah)
            $data['image'] = 'storage/' . $saved['path'];
        }
        $hotelBooking->update($data);
        return redirect()->route('hotel-booking.index')->with('success', 'Hotel Booking updated successfully');
    }

    public function destroy($id)
    {
        $hotelBooking = HotelBooking::findOrFail($id);
        if ($hotelBooking->image) {
            if (file_exists($hotelBooking->image)) {
                unlink($hotelBooking->image);
            }
        }
        $hotelBooking->delete();
        return redirect()->route('hotel-booking.index')->with('success', 'Hotel Booking deleted successfully');
    }
}
