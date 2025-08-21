<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Services\ImageService;
use App\Models\HonoraryCouncil;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\HonoraryCouncilRequest;

class HonoraryCouncilController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (request()->ajax()) {
            $data = HonoraryCouncil::latest();
            return DataTables::of($data)
                ->addColumn('action', function ($data) {
                    $actionEdit = route('honorary-council.edit', $data->id);
                    $actionDelete = route('honorary-council.destroy', $data->id);
                    return "<div class='d-flex justify-content-center'>" .
                        view('components.action.edit', ['action' => $actionEdit]) .
                        view('components.action.delete', ['action' => $actionDelete, 'id' => $data->id]) .
                        "</div>";
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('admins.honorary-council.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admins.honorary-council.create-edit');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(HonoraryCouncilRequest $request, ImageService $imageService)
    {
        $data = $request->validated();
        if ($request->hasFile('image')) {
            $saved = $imageService->storeSingleWebp(
                file: $request->file('image'),
                maxWidth: null,
                quality: 50,
                disk: 'public',
                dir: 'honorary-council'
            );

            $data['image'] = 'storage/' . $saved['path'];
        }
        HonoraryCouncil::create($data);
        return redirect()->route('honorary-council.index')->with('success', 'Dewan Kehormatan berhasil ditambahkan');
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
        $honoraryCouncil = HonoraryCouncil::findOrFail($id);
        return view('admins.honorary-council.create-edit', compact('honoraryCouncil'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(HonoraryCouncilRequest $request, HonoraryCouncil $honoraryCouncil, ImageService $imageService)
    {
        $data = $request->validated();
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if (file_exists($honoraryCouncil->image)) {
                unlink($honoraryCouncil->image);
            }
            $saved = $imageService->storeSingleWebp(
                file: $request->file('image'),
                maxWidth: null,
                quality: 50,
                disk: 'public',
                dir: 'honorary-council'
            );

            $data['image'] = 'storage/' . $saved['path'];
        }
        $honoraryCouncil->update($data);
        return redirect()->route('honorary-council.index')->with('success', 'Dewan Kehormatan berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(HonoraryCouncil $honoraryCouncil)
    {
        // Delete image if exists
        if (file_exists($honoraryCouncil->image)) {
            unlink($honoraryCouncil->image);
        }
        $honoraryCouncil->delete();
        return redirect()->route('honorary-council.index')->with('success', 'Dewan Kehormatan berhasil dihapus');
    }
}
