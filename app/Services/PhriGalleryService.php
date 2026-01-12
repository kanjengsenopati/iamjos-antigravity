<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class PhriGalleryService
{
    protected $disk;
    protected array $imageExt = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

    public function __construct()
    {
        $this->disk = Storage::disk('phri_gallery');
    }

    public function albums(): array
    {
        $albums = [];

        foreach ($this->disk->directories('') as $dir) {
            // kumpulkan file gambar dalam folder album
            $images = collect($this->disk->files($dir))
                ->filter(function ($p) {
                    $ext = strtolower(pathinfo($p, PATHINFO_EXTENSION));
                    return in_array($ext, $this->imageExt, true);
                })
                ->sortByDesc(fn($p) => $this->disk->lastModified($p))
                ->values();

            if ($images->isEmpty()) {
                continue; // skip folder tanpa gambar
            }

            // tanggal = terakhir dimodifikasi dari gambar terbaru
            $latestTs = $images->map(fn($p) => $this->disk->lastModified($p))->max();
            $albumName = basename($dir);
            $slug = Str::slug($albumName);

            $albums[] = [
                'slug'   => $slug,
                'name'   => Str::headline($albumName),
                'total'  => $images->count(),
                'date'   => Carbon::createFromTimestamp($latestTs)->locale('id')->isoFormat('D MMMM YYYY'),
                'hero'   => route('gallery.preview', ['path' => $images->get(0)]),
                'thumbs' => [
                    $images->get(1) ? route('gallery.preview', ['path' => $images->get(1)]) : null,
                    $images->get(2) ? route('gallery.preview', ['path' => $images->get(2)]) : null,
                ],
                'see_all_url' => url('/galeri/' . $slug),
                'raw_dir'     => $dir,
            ];
        }

        // urutkan terbaru di atas
        return collect($albums)->sortByDesc(fn($a) => $a['date'])->values()->all();
    }

    public function resolveSlug(string $slug): ?string
    {
        foreach ($this->disk->directories('') as $dir) {
            if (Str::slug(basename($dir)) === $slug) return $dir;
        }
        return null;
    }
}
