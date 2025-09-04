<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;
use App\Services\PhriGalleryService; // <— tambahkan

class GalleryController extends Controller
{
    protected $disk;

    public function __construct()
    {
        $this->disk = Storage::disk('phri_gallery');
    }

    /**
     * GET /api/gallery
     * Kembalikan daftar album (hasil dari PhriGalleryService::albums()).
     */
    public function index(Request $request, PhriGalleryService $svc)
    {
        $albums = $svc->albums();

        return $this->getSuccessResponse([
            'total'  => count($albums),
            'albums' => $albums,
        ]);
    }

    /**
     * GET /api/gallery/preview?path=...
     * Stream file (inline).
     */
    public function preview(Request $request)
    {
        $path = $this->sanitizePath($request->query('path', ''));

        if ($path === '') {
            return response()->json(['message' => 'Path is required'], 422);
        }

        // pastikan path adalah FILE (bukan direktori)
        if (!$this->fileExists($path)) {
            return response()->json(['message' => 'File not found'], 404);
        }

        $mime = $this->guessMime($path);

        // Fast-path: jika disk lokal, gunakan absolute path + response()->file()
        try {
            if (method_exists($this->disk, 'path')) {
                $absolutePath = $this->disk->path($path);
                if (is_file($absolutePath)) {
                    return response()->file($absolutePath, [
                        'Content-Type'        => $mime,
                        'Content-Disposition' => 'inline; filename="' . basename($path) . '"',
                    ]);
                }
            }
        } catch (\Throwable $e) {
            // abaikan & lanjut ke stream
        }

        // Stream via Flysystem (hemat memori)
        $stream = $this->disk->readStream($path);
        if (is_resource($stream)) {
            return response()->stream(function () use ($stream) {
                fpassthru($stream);
                if (is_resource($stream)) {
                    fclose($stream);
                }
            }, 200, [
                'Content-Type'        => $mime,
                'Content-Disposition' => 'inline; filename="' . basename($path) . '"',
            ]);
        }

        // Fallback terakhir (load ke memori)
        $contents = $this->disk->get($path);
        return response($contents, 200, [
            'Content-Type'        => $mime,
            'Content-Disposition' => 'inline; filename="' . basename($path) . '"',
        ]);
    }

    /**
     * GET /api/gallery/download?path=...
     * Download file (attachment).
     */
    public function download(Request $request)
    {
        $path = $this->sanitizePath($request->query('path', ''));

        if (!$this->disk->exists($path) || $this->disk->mimeType($path) === 'directory') {
            return response()->json(['message' => 'File not found'], 404);
        }

        $stream = $this->disk->readStream($path);
        return response()->streamDownload(function () use ($stream) {
            fpassthru($stream);
        }, basename($path));
    }

    /**
     * (Opsional) daftar isi album per slug: GET /api/gallery/album/{slug}
     * Mengembalikan semua gambar dalam album tsb.
     */
    public function album(string $slug, PhriGalleryService $svc)
    {
        $dir = $svc->resolveSlug($slug);
        if (!$dir) {
            return response()->json(['message' => 'Album not found'], 404);
        }

        $files = collect($this->disk->files($dir))
            ->filter(fn($p) => str_starts_with((string) $this->disk->mimeType($p), 'image/'))
            ->sortByDesc(fn($p) => $this->disk->lastModified($p))
            ->values()
            ->map(fn($p) => [
                'type'          => 'file',
                'name'          => basename($p),
                'path'          => $p,
                'size'          => $this->disk->size($p),
                'last_modified' => $this->disk->lastModified($p),
                'mime'          => $this->disk->mimeType($p),
                'preview_url'   => route('gallery.preview', ['path' => $p]),
                'download_url'  => route('gallery.download', ['path' => $p]),
            ]);

        return $this->getSuccessResponse([
            'slug'  => $slug,
            'dir'   => $dir,
            'total' => $files->count(),
            'date'  => $files->isNotEmpty()
                ? \Carbon\Carbon::createFromTimestamp($files->first()['last_modified'])->locale('id')->isoFormat('D MMMM YYYY')
                : null,
            'files' => $files->all(),

        ]);
    }

    // ----------------- helpers -----------------

    protected function sanitizePath(string $path): string
    {
        $path = str_replace('\\', '/', $path);
        $path = ltrim($path, '/');
        if (Str::contains($path, ['..'])) {
            abort(400, 'Invalid path.');
        }
        return $path;
    }

    protected function guessMime(string $path): string
    {
        return $this->disk->mimeType($path) ?: 'application/octet-stream';
    }

    protected function fileExists(string $path): bool
    {
        // Laravel 10+ punya fileExists()/directoryExists()
        if (method_exists($this->disk, 'fileExists')) {
            return $this->disk->fileExists($path);
        }

        // Kompat lama: exists() true utk file/dir; bedakan manual
        if (!$this->disk->exists($path)) {
            return false;
        }

        try {
            if (method_exists($this->disk, 'directoryExists') && $this->disk->directoryExists($path)) {
                return false; // ternyata direktori
            }
        } catch (\Throwable $e) {
            // abaikan
        }

        // Cek kasar: apakah nama ini muncul di daftar direktori parent?
        $dir  = trim(dirname($path), '.');
        $base = basename($path);
        $parent = $dir === '' ? '' : $dir;
        foreach ($this->disk->directories($parent) as $d) {
            if (basename($d) === $base) {
                return false; // nama ini adalah direktori
            }
        }

        return true; // anggap file
    }
}
