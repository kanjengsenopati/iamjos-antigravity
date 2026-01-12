<?php

namespace App\Jobs;

use App\Models\HomeSlider;
use App\Services\ImageCompressionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessImageCompressionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 300; // 5 minutes

    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $sliderId,
        public string $tempPath,
        public string $finalPath,
        public array $compressionOptions = []
    ) {}

    /**
     * Execute the job.
     */
    public function handle(ImageCompressionService $imageCompressionService): void
    {
        try {
            Log::info("Starting image compression for slider: {$this->sliderId}");

            $success = $imageCompressionService->compressImage(
                $this->tempPath,
                $this->finalPath,
                $this->compressionOptions
            );

            if ($success) {
                // Update slider record with final path
                $slider = HomeSlider::find($this->sliderId);
                if ($slider) {
                    $slider->update(['media' => $this->finalPath]);
                }

                // Create thumbnail
                $thumbnailPath = str_replace('home-sliders/', 'home-sliders/thumbnails/', $this->finalPath);
                $imageCompressionService->createThumbnail($this->finalPath, $thumbnailPath);

                Log::info("Image compression completed for slider: {$this->sliderId}");
            } else {
                Log::error("Image compression failed for slider: {$this->sliderId}");
                $this->fail('Image compression failed');
            }
        } catch (\Exception $e) {
            Log::error("Image compression job failed for slider {$this->sliderId}: " . $e->getMessage());
            $this->fail($e);
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("Image compression job failed permanently for slider {$this->sliderId}: " . $exception->getMessage());

        // Optionally update slider record to indicate failure
        $slider = HomeSlider::find($this->sliderId);
        if ($slider) {
            $slider->update(['media_processing_status' => 'failed']);
        }
    }
}
