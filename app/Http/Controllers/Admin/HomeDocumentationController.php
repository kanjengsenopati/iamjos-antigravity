<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Models\HomeDocumentation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use App\Services\VideoCompressionService;
use App\Http\Requests\Admin\HomeDocumentationRequest;

class HomeDocumentationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (request()->ajax()) {
            $data = HomeDocumentation::latest();
            return DataTables::of($data)
                ->addColumn('action', function ($data) {
                    $actionEdit = route('home-documentation.edit', $data->id);
                    $actionDelete = route('home-documentation.destroy', $data->id);
                    return "<div class='d-flex justify-content-center'>" .
                        view('components.action.edit', ['action' => $actionEdit]) .
                        view('components.action.delete', ['action' => $actionDelete, 'id' => $data->id]) .
                        "</div>";
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('admins.home-documentation.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admins.home-documentation.create-edit');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(HomeDocumentationRequest $request)
    {
        // Gunakan service yang sudah kita buat sebelumnya
        // Anda bisa juga menggunakan dependency injection di constructor controller
        $videoService = new VideoCompressionService();
        $data = $request->validated();

        DB::beginTransaction();

        try {
            // 1. Tentukan tipe media berdasarkan file yang diupload
            $isUploadedVideo = false;
            if ($request->hasFile('media')) {
                $mimeType = $request->file('media')->getClientMimeType();
                if (str_starts_with($mimeType, 'video/')) {
                    $data['media_type'] = 'video';
                    $isUploadedVideo = true;
                } else {
                    $data['media_type'] = 'image';
                }
            }

            // 2. Simpan file media utama
            if ($request->hasFile('media')) {
                // store() mengembalikan path relatif seperti 'home-documentation/filename.mp4'
                $mediaRelativePath = $request->file('media')->store('home-documentation', 'public');
                $data['media_url'] = Storage::disk('public')->url($mediaRelativePath);
            }

            // 3. Handle thumbnail
            if ($isUploadedVideo && !$request->hasFile('thumbnail')) {
                // KASUS: Video diupload, tapi thumbnail tidak. Kita generate.

                // Buat path relatif untuk thumbnail baru
                $thumbnailRelativePath = 'home-documentation/thumbnails/' . pathinfo($mediaRelativePath, PATHINFO_FILENAME) . '.jpg';

                // Panggil service dengan path RELATIF
                $success = $videoService->createVideoThumbnail(
                    $mediaRelativePath,       // contoh: 'home-documentation/video.mp4'
                    $thumbnailRelativePath,   // contoh: 'home-documentation/thumbnails/video.jpg'
                    5                         // Ambil frame dari detik ke-5
                );

                if (!$success) {
                    // Jika gagal membuat thumbnail, batalkan semua proses
                    throw new \Exception('Gagal membuat thumbnail untuk video.');
                }

                // Jika berhasil, simpan URL publik dari thumbnail
                $data['thumbnail'] = Storage::disk('public')->url($thumbnailRelativePath);
            } elseif ($request->hasFile('thumbnail')) {
                // KASUS: Thumbnail diupload manual (baik untuk video atau gambar)
                $thumbnailRelativePath = $request->file('thumbnail')->store('home-documentation/thumbnails', 'public');
                $data['thumbnail'] = Storage::disk('public')->url($thumbnailRelativePath);
            } else {
                // KASUS: Gambar diupload tanpa thumbnail, thumbnail sama dengan gambar utama
                $data['thumbnail'] = $data['media_url'] ?? null;
            }

            // 4. Simpan ke database
            HomeDocumentation::create($data);

            DB::commit();

            return redirect()->route('home-documentation.index')->with('success', 'Dokumentasi berhasil dibuat.');
        } catch (\Throwable $e) {
            DB::rollBack();

            // Hapus file yang mungkin sudah terupload jika terjadi error
            if (isset($mediaRelativePath)) {
                Storage::disk('public')->delete($mediaRelativePath);
            }
            if (isset($thumbnailRelativePath)) {
                Storage::disk('public')->delete($thumbnailRelativePath);
            }

            Log::error('Gagal membuat dokumentasi: ' . $e->getMessage());

            return back()->with('error', 'Gagal membuat dokumentasi: ' . $e->getMessage())->withInput();
        }
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
    public function destroy(string $id)
    {
        try {
            $documentation = HomeDocumentation::findOrFail($id);
            if ($documentation->media_url && file_exists($documentation->media_url)) {
                unlink($documentation->media_url);
            }
            if ($documentation->thumbnail && file_exists($documentation->thumbnail)) {
                unlink($documentation->thumbnail);
            }
            $documentation->delete();
            return redirect()->route('home-documentation.index')->with('success', 'Berhasil menghapus dokumentasi.');
        } catch (\Exception $e) {
            Log::error('Failed to delete documentation: ' . $e->getMessage());
            return redirect()->route('home-documentation.index')->with('error', 'Gagal menghapus dokumentasi: ' . $e->getMessage());
        }
    }
}
