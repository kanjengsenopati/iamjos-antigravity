<?php

namespace App\Http\Controllers\Admin;

use App\Models\HomeSlider;
use Illuminate\Http\Request;
use App\Services\MediaService;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use App\Jobs\ProcessImageCompressionJob;
use App\Jobs\ProcessVideoCompressionJob;
use Stichoza\GoogleTranslate\GoogleTranslate;
use App\Http\Requests\Admin\HomeSliderRequest;

class HomeSliderController extends Controller
{
    public function __construct(
        protected MediaService $mediaService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (request()->ajax()) {
            $sliders = HomeSlider::latest();
            return DataTables::of($sliders)
                ->addColumn('media_preview', function ($data) {
                    if (!$data->media) {
                        return '<span class="badge badge-secondary">No Media</span>';
                    }

                    if ($data->isImage()) {
                        $url = $data->thumbnail_url ?: $data->media;
                        return "<img src='{$url}' alt='Preview' style='width: 60px; height: 40px; object-fit: cover;' class='rounded'>";
                    } elseif ($data->isVideo()) {
                        $thumbnailUrl = $data->thumbnail_url;
                        if ($thumbnailUrl) {
                            return "<div class='position-relative'><img src='{$thumbnailUrl}' alt='Video Preview' style='width: 60px; height: 40px; object-fit: cover;' class='rounded'><i class='fas fa-play-circle position-absolute top-50 start-50 translate-middle text-white'></i></div>";
                        }
                        return '<i class="fas fa-video fa-2x text-primary"></i>';
                    }

                    return '<span class="badge badge-warning">Processing</span>';
                })
                ->addColumn('status', function ($data) {
                    $activeStatus = $data->is_active
                        ? '<span class="badge badge-success">Active</span>'
                        : '<span class="badge badge-secondary">Inactive</span>';

                    // $processingStatus = match ($data->media_processing_status) {
                    //     'completed' => '<span class="badge badge-success ms-1">Ready</span>',
                    //     'processing' => '<span class="badge badge-warning ms-1">Processing</span>',
                    //     'failed' => '<span class="badge badge-danger ms-1">Failed</span>',
                    //     default => '<span class="badge badge-info ms-1">Pending</span>'
                    // };

                    // return $activeStatus . $processingStatus;
                    return $activeStatus;
                })
                ->addColumn('action', function ($data) {
                    $actionEdit = route('home-slider.edit', $data->id);
                    // $actionShow = route('home-slider.show', $data->id);
                    $actionDelete = route('home-slider.destroy', $data->id);
                    return "<div class='d-flex justify-content-center'>" .
                        // view('components.action.show', ['action' => $actionShow]) .
                        view('components.action.edit', ['action' => $actionEdit]) .
                        view('components.action.delete', ['action' => $actionDelete, 'id' => $data->id]) .
                        "</div>";
                })
                ->rawColumns(['media_preview', 'status', 'action'])
                ->make(true);
        }
        return view('admins.home-slider.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admins.home-slider.create-edit');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(HomeSliderRequest $request)
    {
        try {
            DB::transaction(function () use ($request) {
                $data = $request->validated();
                $data['title_en'] =  GoogleTranslate::trans($request->title, 'en');
                $data['description_en'] = GoogleTranslate::trans($request->description, 'en');
                $data['button_text_en'] = GoogleTranslate::trans($request->button_text, 'en');
                // Create slider record first
                // $sliderData = $request->except(['media']);
                $data['media_processing_status'] = 'processing';
                if ($request->hasFile('media')) {
                    $data['media'] = 'storage/' . $request->file('media')->store('sliders', ['disk' => 'public']);
                }
                $data['media_type'] = $request->file('media')->getClientMimeType() ? $request->file('media')->getClientMimeType() == 'image/jpeg' ? 'image' : 'video' : null;

                $slider = HomeSlider::create($data);
            });

            return redirect()->route('home-slider.index')
                ->with('success', 'Slider berhasil ditambahkan');
        } catch (\Exception $e) {
            Log::error('Failed to create home slider: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal menambahkan slider: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(HomeSlider $homeSlider)
    {
        return view('admins.home-slider.show', compact('homeSlider'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(HomeSlider $homeSlider)
    {
        return view('admins.home-slider.create-edit', compact('homeSlider'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(HomeSliderRequest $request, HomeSlider $homeSlider)
    {
        try {
            DB::transaction(function () use ($request, $homeSlider) {
                $data = $request->except(['media']);

                // Handle media update
                if ($request->hasFile('media')) {
                    // Delete old media
                    $this->deleteSliderMedia($homeSlider);

                    // Set processing status
                    $data['media_processing_status'] = 'processing';
                    $data['media'] = null;
                    $data['thumbnail_path'] = null;

                    $homeSlider->update($data);

                    // Process new media
                    $this->handleMediaUpload($request->file('media'), $homeSlider);
                } else {
                    $homeSlider->update($data);
                }
            });

            return redirect()->route('home-slider.index')
                ->with('success', 'Slider berhasil diperbarui.');
        } catch (\Exception $e) {
            Log::error('Failed to update home slider: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal memperbarui slider: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(HomeSlider $homeSlider)
    {
        try {
            DB::transaction(function () use ($homeSlider) {
                // Delete media files
                $this->deleteSliderMedia($homeSlider);

                // Delete record
                $homeSlider->delete();
            });

            return redirect()->route('home-slider.index')
                ->with('success', 'Slider berhasil dihapus.');
        } catch (\Exception $e) {
            Log::error('Failed to delete home slider: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Gagal menghapus slider: ' . $e->getMessage());
        }
    }

    /**
     * Handle media upload and processing
     */
    private function handleMediaUpload($file, HomeSlider $slider): void
    {
        $mediaInfo = $this->mediaService->storeMedia($file, 'home-sliders');

        // Update slider with media type
        $slider->update(['media_type' => $mediaInfo['type']]);

        // Dispatch appropriate compression job
        if ($mediaInfo['type'] === 'image') {
            ProcessImageCompressionJob::dispatch(
                $slider->id,
                $mediaInfo['temp_path'],
                $mediaInfo['path'],
                [
                    'max_width' => 1920,
                    'max_height' => 1080,
                    'quality' => 85
                ]
            );
        } elseif ($mediaInfo['type'] === 'video') {
            ProcessVideoCompressionJob::dispatch(
                $slider->id,
                $mediaInfo['temp_path'],
                $mediaInfo['path'],
                [
                    'max_width' => 1280,
                    'max_height' => 720,
                    'crf' => 28,
                    'preset' => 'medium'
                ]
            );
        }
    }

    /**
     * Delete slider media files
     */
    private function deleteSliderMedia(HomeSlider $slider): void
    {
        if ($slider->media) {
            $this->mediaService->deleteMedia($slider->media);
        }

        if ($slider->thumbnail_path) {
            $this->mediaService->deleteMedia($slider->thumbnail_path);
        }
    }
}
