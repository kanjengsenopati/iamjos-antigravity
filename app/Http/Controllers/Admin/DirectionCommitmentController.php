<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Models\DirectionCommitment;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\DirectionCommitmentRequest;
use App\Services\ImageService;

class DirectionCommitmentController extends Controller
{
    public function index()
    {
        if (request()->ajax()) {
            $data = DirectionCommitment::latest();
            return DataTables::of($data)
                ->addColumn('action', function ($data) {
                    $actionEdit = route('direction-commitment.edit', $data->id);
                    $actionDelete = route('direction-commitment.destroy', $data->id);
                    return "<div class='d-flex justify-content-center'>" .
                        view('components.action.edit', ['action' => $actionEdit]) .
                        view('components.action.delete', ['action' => $actionDelete, 'id' => $data->id]) .
                        "</div>";
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('admins.direction-commitment.index');
    }

    public function create()
    {
        return view('admins.direction-commitment.create-edit');
    }

    public function store(DirectionCommitmentRequest $request, ImageService $imageService)
    {
        $data = $request->validated();
        if ($request->hasFile('image')) {
            $saved = $imageService->storeSingleWebp(
                file: $request->file('image'),
                maxWidth: null,
                quality: 50,
                disk: 'public',
                dir: 'direction-commitment'
            );

            $data['image'] = 'storage/' . $saved['path'];
        }
        DirectionCommitment::create($data);

        return redirect()->route('direction-commitment.index')->with('success', 'Arah dan Komitmen berhasil dibuat.');
    }

    public function edit(DirectionCommitment $directionCommitment)
    {
        return view('admins.direction-commitment.create-edit', compact('directionCommitment'));
    }

    public function update(DirectionCommitmentRequest $request, DirectionCommitment $directionCommitment, ImageService $imageService)
    {
        $data = $request->validated();
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if (file_exists($directionCommitment->image)) {
                unlink($directionCommitment->image);
            }
            $saved = $imageService->storeSingleWebp(
                file: $request->file('image'),
                maxWidth: null,
                quality: 50,
                disk: 'public',
                dir: 'direction-commitment'
            );

            $data['image'] = 'storage/' . $saved['path'];
        }

        $directionCommitment->update($data);

        return redirect()->route('direction-commitment.index')->with('success', 'Arah dan Komitmen berhasil diperbarui.');
    }

    public function destroy(DirectionCommitment $directionCommitment)
    {
        if (file_exists($directionCommitment->image)) {
            unlink($directionCommitment->image);
        }
        $directionCommitment->delete();

        return redirect()->route('direction-commitment.index')->with('success', 'Arah dan Komitmen berhasil dihapus.');
    }
}
