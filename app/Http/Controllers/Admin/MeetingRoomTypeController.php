<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Services\ImageService;
use App\Models\MeetingRoomType;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MeetingRoomTypeRequest;

class MeetingRoomTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (request()->ajax()) {
            $court = MeetingRoomType::latest();
            return DataTables::of($court)
                ->addColumn('action', function ($data) {
                    $actionEdit = route('meeting-room-type.edit', $data->id);
                    $actionDelete = route('meeting-room-type.destroy', $data->id);
                    return "<div class='d-flex justify-content-center'>" .
                        view('components.action.edit', ['action' => $actionEdit]) .
                        view('components.action.delete', ['action' => $actionDelete, 'id' => $data->id]) .
                        "</div>";
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('admins.meeting-room-type.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admins.meeting-room-type.create-edit');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(MeetingRoomTypeRequest $request)
    {
        $data = $request->validated();
        if ($request->hasFile('image')) {
            $imageService = app(ImageService::class);
            $thumbnailPath = $imageService->storeImage($request->file('image'), 'images/meeting-room-type');
            $data['image'] = 'storage/' . $thumbnailPath;
        }
        MeetingRoomType::create($data);
        return redirect()->route('meeting-room-type.index')->with('success', 'Tipe ruang pertemuan berhasil ditambahkan.');
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
        $meetingRoomType = MeetingRoomType::findOrFail($id);
        return view('admins.meeting-room-type.create-edit', compact('meetingRoomType'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(MeetingRoomTypeRequest $request, string $id)
    {
        $meetingRoomType = MeetingRoomType::findOrFail($id);
        $data = $request->validated();

        if ($request->hasFile('image')) {
            file_exists($meetingRoomType->image) ? unlink($meetingRoomType->image) : null;
            $imageService = app(ImageService::class);
            $thumbnailPath = $imageService->storeImage($request->file('image'), 'images/meeting-room-type');
            $data['image'] = 'storage/' . $thumbnailPath;
        }

        $meetingRoomType->update($data);
        return redirect()->route('meeting-room-type.index')->with('success', 'Tipe ruang pertemuan berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $meetingRoomType = MeetingRoomType::findOrFail($id);
        file_exists($meetingRoomType->image) ? unlink($meetingRoomType->image) : null;
        $meetingRoomType->delete();
        return redirect()->route('meeting-room-type.index')->with('success', 'Tipe ruang pertemuan berhasil dihapus.');
    }
}
