<?php

namespace App\Jobs;

use App\Models\HomeSlider;
use App\Services\VideoCompressionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessVideoCompressionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;
    public int $timeout = 1800; // 30 minutes for video processing

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
    public function handle(VideoCompressionService $videoCompressionService): void
    {
        try {
            Log::info("Starting video compression for slider: {$this->sliderId}");

            $success = $videoCompressionService->compressVideo(
                $this->tempPath,
                $this->finalPath,
                $this->compressionOptions
            );

            if ($success) {
                // Update slider record with final path
                $slider = HomeSlider::find($this->sliderId);
                if ($slider) {
                    $slider->update([
                        'media' => $this->finalPath,
                        'media_processing_status' => 'completed'
                    ]);
                }

                // Create video thumbnail
                $thumbnailPath = str_replace('home-sliders/', 'home-sliders/thumbnails/', $this->finalPath);
                $thumbnailPath = preg_replace('/\.(mp4|avi|mov|wmv)$/i', '.jpg', $thumbnailPath);
                $videoCompressionService->createVideoThumbnail($this->finalPath, $thumbnailPath);

                Log::info("Video compression completed for slider: {$this->sliderId}");
            } else {
                Log::error("Video compression failed for slider: {$this->sliderId}");
                $this->fail('Video compression failed');
            }
        } catch (\Exception $e) {
            Log::error("Video compression job failed for slider {$this->sliderId}: " . $e->getMessage());
            $this->fail($e);
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("Video compression job failed permanently for slider {$this->sliderId}: " . $exception->getMessage());

        // Update slider record to indicate failure
        $slider = HomeSlider::find($this->sliderId);
        if ($slider) {
            $slider->update(['media_processing_status' => 'failed']);
        }
    }
}
