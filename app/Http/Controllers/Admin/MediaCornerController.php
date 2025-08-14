<?php

namespace App\Http\Controllers\Admin;

use App\Models\MediaCorner;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Controller;

class MediaCornerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (request()->ajax()) {
            $data = MediaCorner::latest();
            return DataTables::of($data)
                ->addColumn('image', function ($data) {
                    // $data->thumbnails sudah array karena di-cast di model
                    $thumbs = $data->thumbnails ?? [];

                    // struktur YouTube: $thumbs['high']['url'], dst.
                    $url =
                        $thumbs['maxres']['url']  ??
                        $thumbs['standard']['url'] ??
                        $thumbs['high']['url']    ??
                        $thumbs['medium']['url']  ??
                        $thumbs['default']['url'] ?? null;

                    return $url
                        ? "<img src=\"{$url}\" alt=\"thumb\" class=\"img-fluid\" style=\"max-width:140px\">"
                        : '-';
                })
                ->editColumn('published_at', function ($data) {
                    return $data->published_at ? $data->published_at->format('d M Y H:i') : '-';
                })
                ->editColumn('url', function ($data) {
                    return $data->url ? '<i class="fa fa-link"></i> <a href="' . $data->url . '" target="_blank">' . $data->url . '</a>' : '-';
                })
                ->addColumn('action', function ($data) {
                    $actionToggleStatus = route('media-corner.toggle-status', $data->id);
                    $actionDelete = route('media-corner.destroy', $data->id);
                    return "<div class='d-flex justify-content-center'>" .
                        view('components.action.toggle-status', [
                            'action' => $actionToggleStatus,
                            'id' => $data->id,
                            'isActive' => $data->is_active
                        ]) .
                        view('components.action.delete', ['action' => $actionDelete, 'id' => $data->id]) .
                        "</div>";
                })
                ->rawColumns(['action', 'image', 'url'])
                ->make(true);
        }
        return view('admins.media-corner.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
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
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MediaCorner $mediaCorner)
    {
        // Hapus video dari tabel media_corners
        $mediaCorner->delete();
        return redirect()->route('media-corner.index')->with('success', 'Media berhasil dihapus.');
    }

    public function toggleStatus(MediaCorner $mediaCorner)
    {
        $mediaCorner->update(['is_active' => !$mediaCorner->is_active]);

        $status = $mediaCorner->is_active ? 'diaktifkan' : 'dinonaktifkan';

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Media telah {$status}.",
                'status' => $mediaCorner->is_active
            ]);
        }

        return redirect()->route('media-corner.index')->with('success', "Status media telah diubah menjadi {$status}.");
    }
}
