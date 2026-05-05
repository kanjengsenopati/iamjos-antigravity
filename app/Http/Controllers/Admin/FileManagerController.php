<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class FileManagerController extends Controller
{
    protected $basePath;

    public function __construct()
    {
        $this->basePath = base_path();
    }

    /**
     * List files and directories in a path
     */
    public function list(Request $request)
    {
        $relativePath = $request->get('path', '');
        $fullPath = $this->resolvePath($relativePath);

        if (!File::exists($fullPath) || !File::isDirectory($fullPath)) {
            return response()->json(['error' => 'Path tidak valid'], 400);
        }

        $items = [];
        
        // Add parent directory link if not at root
        if ($relativePath !== '') {
            $items[] = [
                'name' => '..',
                'type' => 'dir',
                'path' => dirname($relativePath) === '.' ? '' : dirname($relativePath),
                'size' => '-',
                'modified' => '-',
            ];
        }

        // Get directories
        foreach (File::directories($fullPath) as $dir) {
            $items[] = [
                'name' => basename($dir),
                'type' => 'dir',
                'path' => trim($relativePath . '/' . basename($dir), '/'),
                'size' => '-',
                'modified' => date('Y-m-d H:i', File::lastModified($dir)),
            ];
        }

        // Get files
        foreach (File::files($fullPath) as $file) {
            $items[] = [
                'name' => $file->getFilename(),
                'type' => 'file',
                'path' => trim($relativePath . '/' . $file->getFilename(), '/'),
                'size' => $this->formatSize($file->getSize()),
                'extension' => strtolower($file->getExtension()),
                'modified' => date('Y-m-d H:i', $file->getMTime()),
            ];
        }

        return response()->json([
            'currentPath' => $relativePath,
            'items' => $items,
            'breadcrumbs' => $this->getBreadcrumbs($relativePath)
        ]);
    }

    /**
     * Upload files to a path
     */
    public function upload(Request $request)
    {
        $request->validate([
            'path' => 'required|string',
            'files.*' => 'required|file',
        ]);

        $relativePath = $request->path;
        $fullPath = $this->resolvePath($relativePath);

        if (!File::exists($fullPath)) {
            File::makeDirectory($fullPath, 0755, true);
        }

        foreach ($request->file('files') as $file) {
            $file->move($fullPath, $file->getClientOriginalName());
        }

        return response()->json(['success' => true]);
    }

    /**
     * Create a new folder
     */
    public function createFolder(Request $request)
    {
        $request->validate([
            'path' => 'required|string',
            'name' => 'required|string',
        ]);

        $fullPath = $this->resolvePath($request->path . '/' . $request->name);

        if (File::exists($fullPath)) {
            return response()->json(['error' => 'Folder sudah ada'], 400);
        }

        File::makeDirectory($fullPath, 0755, true);

        return response()->json(['success' => true]);
    }

    /**
     * Delete a file or folder
     */
    public function delete(Request $request)
    {
        $request->validate([
            'path' => 'required|string',
        ]);

        $fullPath = $this->resolvePath($request->path);

        if (!File::exists($fullPath)) {
            return response()->json(['error' => 'File/Folder tidak ditemukan'], 400);
        }

        if (File::isDirectory($fullPath)) {
            File::deleteDirectory($fullPath);
        } else {
            File::delete($fullPath);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Resolve relative path to absolute path and prevent directory traversal
     */
    protected function resolvePath($relativePath)
    {
        $relativePath = str_replace(['..', './'], '', $relativePath);
        return $this->basePath . ($relativePath ? DIRECTORY_SEPARATOR . $relativePath : '');
    }

    /**
     * Format file size
     */
    protected function formatSize($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        for ($i = 0; $bytes > 1024; $i++) $bytes /= 1024;
        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Get breadcrumbs for navigation
     */
    protected function getBreadcrumbs($path)
    {
        if ($path === '') return [['name' => 'Root', 'path' => '']];
        
        $parts = explode('/', $path);
        $breadcrumbs = [['name' => 'Root', 'path' => '']];
        $current = '';
        
        foreach ($parts as $part) {
            if ($part === '') continue;
            $current = trim($current . '/' . $part, '/');
            $breadcrumbs[] = [
                'name' => $part,
                'path' => $current
            ];
        }
        
        return $breadcrumbs;
    }
}
