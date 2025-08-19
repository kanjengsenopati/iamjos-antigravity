<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Services\ImageService;
use Yajra\DataTables\DataTables;
use App\Models\RegionalCoordinator;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RegionalCoordinatorRequest;

class RegionalCoordinatorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (request()->ajax()) {
            $data = RegionalCoordinator::latest();
            return DataTables::of($data)
                ->addColumn('action', function ($data) {
                    $actionEdit = route('regional-coordinator.edit', $data->id);
                    $actionDelete = route('regional-coordinator.destroy', $data->id);
                    return "<div class='d-flex justify-content-center'>" .
                        view('components.action.edit', ['action' => $actionEdit]) .
                        view('components.action.delete', ['action' => $actionDelete, 'id' => $data->id]) .
                        "</div>";
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('admins.regional-coordinator.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admins.regional-coordinator.create-edit');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(RegionalCoordinatorRequest $request, ImageService $imageService)
    {
        $data = $request->validated();
        if ($request->hasFile('image')) {
            $saved = $imageService->storeSingleWebp(
                file: $request->file('image'),
                maxWidth: null,
                quality: 50,
                disk: 'public',
                dir: 'regional-coordinator'
            );

            $data['image'] = 'storage/' . $saved['path'];
        }
        RegionalCoordinator::create($data);
        return redirect()->route('regional-coordinator.index')->with('success', 'Koordinator Wilayah berhasil ditambahkan');
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
    public function edit(string $id)
    {
        $regionalCoordinator = RegionalCoordinator::findOrFail($id);
        return view('admins.regional-coordinator.create-edit', compact('regionalCoordinator'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(RegionalCoordinatorRequest $request, RegionalCoordinator $regionalCoordinator, ImageService $imageService)
    {
        $data = $request->validated();
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if (file_exists($regionalCoordinator->image)) {
                unlink($regionalCoordinator->image);
            }
            $saved = $imageService->storeSingleWebp(
                file: $request->file('image'),
                maxWidth: null,
                quality: 50,
                disk: 'public',
                dir: 'regional-coordinator'
            );

            $data['image'] = 'storage/' . $saved['path'];
        }
        $regionalCoordinator->update($data);
        return redirect()->route('regional-coordinator.index')->with('success', 'Koordinator Wilayah berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(RegionalCoordinator $regionalCoordinator)
    {
        // Delete image if exists
        if (file_exists($regionalCoordinator->image)) {
            unlink($regionalCoordinator->image);
        }
        $regionalCoordinator->delete();
        return redirect()->route('regional-coordinator.index')->with('success', 'Koordinator Wilayah berhasil dihapus');
    }
}
