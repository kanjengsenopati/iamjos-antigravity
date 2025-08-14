<?php

namespace App\Http\Controllers\Admin;

use App\Models\HomePartner;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\HomePartnerRequest;
use App\Services\ImageService;

class HomePartnerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (request()->ajax()) {
            $data = HomePartner::latest();
            return DataTables::of($data)
                ->addColumn('action', function ($data) {
                    $actionEdit = route('home-partner.edit', $data->id);
                    $actionDelete = route('home-partner.destroy', $data->id);
                    return "<div class='d-flex justify-content-center'>" .
                        view('components.action.edit', ['action' => $actionEdit]) .
                        view('components.action.delete', ['action' => $actionDelete, 'id' => $data->id]) .
                        "</div>";
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('admins.home-partner.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admins.home-partner.create-edit');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(HomePartnerRequest $request, ImageService $imageService)
    {
        $data = $request->validated();
        if ($request->hasFile('image')) {
            $saved = $imageService->storeSingleWebp(
                file: $request->file('image'),
                maxWidth: null,
                quality: 50,
                disk: 'public',
                dir: 'home-partner'
            );

            $data['image'] = 'storage/' . $saved['path'];
        }
        HomePartner::create($data);
        return redirect()->route('home-partner.index')->with('success', 'Berhasil Menambahkan Partner');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(HomePartner $homePartner)
    {
        return view('admins.home-partner.create-edit', compact('homePartner'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(HomePartnerRequest $request, HomePartner $homePartner, ImageService $imageService)
    {
        $data = $request->validated();
        if ($request->hasFile('image')) {
            // Hapus gambar lama jika ada
            if (file_exists($homePartner->image)) {
                unlink($homePartner->image);
            }
            $saved = $imageService->storeSingleWebp(
                file: $request->file('image'),
                maxWidth: null,
                quality: 50,
                disk: 'public',
                dir: 'home-partner'
            );

            $data['image'] = 'storage/' . $saved['path'];
        }
        $homePartner->update($data);
        return redirect()->route('home-partner.index')->with('success', 'Berhasil Mengupdate Partner');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(HomePartner $homePartner)
    {
        // Hapus gambar jika ada
        if (file_exists($homePartner->image)) {
            unlink($homePartner->image);
        }
        $homePartner->delete();
        return redirect()->route('home-partner.index')->with('success', 'Berhasil Menghapus Partner');
    }
}
