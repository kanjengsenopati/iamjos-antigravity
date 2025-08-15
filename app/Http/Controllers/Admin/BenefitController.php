<?php

namespace App\Http\Controllers\Admin;

use App\Models\Benefit;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BenefitRequest;
use App\Services\ImageService;
use Illuminate\Support\Facades\Storage;

class BenefitController extends Controller
{
    private const REQUIRED_STRING_VALIDATION = 'required|string|max:255';
    private const NULLABLE_STRING_VALIDATION = 'nullable|string|max:255';
    private const IMAGE_VALIDATION = 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048';

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (request()->ajax()) {
            $data = Benefit::latest();
            return DataTables::of($data)
                ->addColumn('action', function ($data) {
                    $actionEdit = route('benefit.edit', $data->id);
                    $actionDelete = route('benefit.destroy', $data->id);
                    return "<div class='d-flex justify-content-center'>" .
                        view('components.action.edit', ['action' => $actionEdit]) .
                        view('components.action.delete', ['action' => $actionDelete, 'id' => $data->id]) .
                        "</div>";
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('admins.benefit.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admins.benefit.create-edit');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(BenefitRequest $request, ImageService $imageService)
    {
        $data = $request->validated();

        // Handle image uploads
        if ($request->hasFile('image')) {
            $saved = $imageService->storeSingleWebp(
                file: $request->file('image'),
                maxWidth: null,
                quality: 50,
                disk: 'public',
                dir: 'benefits'
            );

            $data['image'] = 'storage/' . $saved['path'];
        }

        if ($request->hasFile('image_2')) {
            $data['image_2'] = $request->file('image_2')->store('benefits', 'public');
            $saved = $imageService->storeSingleWebp(
                file: $request->file('image_2'),
                maxWidth: null,
                quality: 50,
                disk: 'public',
                dir: 'benefits'
            );

            $data['image_2'] = 'storage/' . $saved['path'];
        }

        if ($request->hasFile('image_3')) {
            $data['image_3'] = $request->file('image_3')->store('benefits', 'public');
            $saved = $imageService->storeSingleWebp(
                file: $request->file('image_3'),
                maxWidth: null,
                quality: 50,
                disk: 'public',
                dir: 'benefits'
            );

            $data['image_3'] = 'storage/' . $saved['path'];
        }

        Benefit::create($data);

        return redirect()->route('benefit.index')->with('success', 'Benefit berhasil ditambahkan');
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
        $benefit = Benefit::findOrFail($id);
        return view('admins.benefit.create-edit', compact('benefit'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(BenefitRequest $request, string $id, ImageService $imageService)
    {
        $benefit = Benefit::findOrFail($id);

        $data = $request->validated();

        // Handle image uploads
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if (file_exists($benefit->image)) {
                unlink($benefit->image);
            }
            $saved = $imageService->storeSingleWebp(
                file: $request->file('image'),
                maxWidth: null,
                quality: 50,
                disk: 'public',
                dir: 'benefits'
            );

            $data['image'] = 'storage/' . $saved['path'];
        }

        if ($request->hasFile('image_2')) {
            // Delete old image if exists
            if (file_exists($benefit->image_2)) {
                unlink($benefit->image_2);
            }
            $saved = $imageService->storeSingleWebp(
                file: $request->file('image_2'),
                maxWidth: null,
                quality: 50,
                disk: 'public',
                dir: 'benefits'
            );

            $data['image_2'] = 'storage/' . $saved['path'];
        }

        if ($request->hasFile('image_3')) {
            // Delete old image if exists
            if (file_exists($benefit->image_3)) {
                unlink($benefit->image_3);
            }
            $saved = $imageService->storeSingleWebp(
                file: $request->file('image_3'),
                maxWidth: null,
                quality: 50,
                disk: 'public',
                dir: 'benefits'
            );

            $data['image_3'] = 'storage/' . $saved['path'];
        }

        $benefit->update($data);

        return redirect()->route('benefit.index')->with('success', 'Benefit berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $benefit = Benefit::findOrFail($id);

        // Delete associated images
        if (file_exists($benefit->image)) {
            unlink($benefit->image);
        }
        if (file_exists($benefit->image_2)) {
            unlink($benefit->image_2);
        }
        if (file_exists($benefit->image_3)) {
            unlink($benefit->image_3);
        }

        $benefit->delete();

        return redirect()->route('benefit.index')->with('success', 'Benefit berhasil dihapus');
    }
}
