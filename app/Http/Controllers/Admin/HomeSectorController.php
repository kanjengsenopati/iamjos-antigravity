<?php

namespace App\Http\Controllers\Admin;

use App\Models\HomeSector;
use Illuminate\Http\Request;
use App\Services\ImageService;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Controller;
use Stichoza\GoogleTranslate\GoogleTranslate;
use App\Http\Requests\Admin\HomeSectorRequest;

class HomeSectorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (request()->ajax()) {
            $data = HomeSector::latest();
            return DataTables::of($data)
                ->addColumn('action', function ($data) {
                    $actionEdit = route('home-sector.edit', $data->id);
                    $actionDelete = route('home-sector.destroy', $data->id);
                    return "<div class='d-flex justify-content-center'>" .
                        view('components.action.edit', ['action' => $actionEdit]) .
                        view('components.action.delete', ['action' => $actionDelete, 'id' => $data->id]) .
                        "</div>";
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('admins.home-sector.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admins.home-sector.create-edit');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(HomeSectorRequest $request, ImageService $imageService)
    {
        $data = $request->validated();
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
        $data['name_en'] = GoogleTranslate::trans($request->name, 'en');
        $data['description_en'] = GoogleTranslate::trans($request->description, 'en');
        HomeSector::create($data);
        return redirect()->route('home-sector.index')->with('success', 'Badan Usaha berhasil ditambahkan.');
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
    public function edit(HomeSector $homeSector)
    {
        return view('admins.home-sector.create-edit', compact('homeSector'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(HomeSectorRequest $request, HomeSector $homeSector, ImageService $imageService)
    {
        $data = $request->validated();
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if (file_exists($homeSector->image)) {
                unlink($homeSector->image);
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
        $data['name_en'] = GoogleTranslate::trans($request->name, 'en');
        $data['description_en'] = GoogleTranslate::trans($request->description, 'en');
        $homeSector->update($data);
        return redirect()->route('home-sector.index')->with('success', 'Badan Usaha berhasil diupdate.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(HomeSector $homeSector)
    {
        // Delete image if exists
        if (file_exists($homeSector->image)) {
            unlink($homeSector->image);
        }
        $homeSector->delete();
        return redirect()->route('home-sector.index')->with('success', 'Badan Usaha berhasil dihapus.');
    }
}
