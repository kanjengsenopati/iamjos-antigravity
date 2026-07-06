<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApplicationSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Carbon\Carbon;

class ApplicationSettingController extends Controller
{
    /**
     * Display application settings page
     */
    public function index()
    {
        $settings = ApplicationSetting::first();
        return view('admins.application-setting.index', compact('settings'));
    }

    /**
     * Create and download database backup
     */
    public function backupDatabase()
    {
        try {
            $filename = 'backup_' . config('database.connections.mysql.database') . '_' . Carbon::now()->format('Y-m-d_H-i-s') . '.sql';
            $backupPath = storage_path('app/backups/' . $filename);

            // Create backups directory if it doesn't exist
            if (!file_exists(storage_path('app/backups'))) {
                mkdir(storage_path('app/backups'), 0755, true);
            }

            // Get database configuration
            $host = config('database.connections.mysql.host');
            $port = config('database.connections.mysql.port', 3306);
            $database = config('database.connections.mysql.database');
            $username = config('database.connections.mysql.username');
            $password = config('database.connections.mysql.password');

            // Build mysqldump command
            $command = [
                'mysqldump',
                '--host=' . $host,
                '--port=' . $port,
                '--user=' . $username,
                '--password=' . $password,
                '--single-transaction',
                '--routines',
                '--triggers',
                '--add-drop-table',
                '--extended-insert',
                '--complete-insert',
                $database
            ];

            // Execute mysqldump command
            $process = new Process($command);
            $process->setTimeout(300); // 5 minutes timeout
            $process->run();

            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }

            // Save backup to file
            file_put_contents($backupPath, $process->getOutput());

            // Check if file was created successfully
            if (!file_exists($backupPath) || filesize($backupPath) === 0) {
                throw new \Exception('Backup file was not created or is empty');
            }

            // Return file download response
            return response()->download($backupPath, $filename, [
                'Content-Type' => 'application/sql',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"'
            ])->deleteFileAfterSend(true);
        } catch (ProcessFailedException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat backup database: ' . $e->getMessage()
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get system information
     */
    public function getSystemInfo()
    {
        try {
            $systemInfo = [
                'php_version' => PHP_VERSION,
                'laravel_version' => app()->version(),
                'database_type' => config('database.default'),
                'database_name' => config('database.connections.mysql.database'),
                'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
                'memory_limit' => ini_get('memory_limit'),
                'max_execution_time' => ini_get('max_execution_time'),
                'upload_max_filesize' => ini_get('upload_max_filesize'),
                'post_max_size' => ini_get('post_max_size'),
                'disk_free_space' => $this->formatBytes(disk_free_space(storage_path())),
                'disk_total_space' => $this->formatBytes(disk_total_space(storage_path())),
            ];

            return response()->json([
                'success' => true,
                'data' => $systemInfo
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil informasi sistem: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get database information
     */
    public function getDatabaseInfo()
    {
        try {
            // Get database size
            $databaseName = config('database.connections.mysql.database');
            $databaseSize = DB::select("
                SELECT 
                    ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size_mb
                FROM information_schema.tables 
                WHERE table_schema = ?
            ", [$databaseName]);

            // Get table count
            $tableCount = DB::select("
                SELECT COUNT(*) as count 
                FROM information_schema.tables 
                WHERE table_schema = ?
            ", [$databaseName]);

            // Get recent backups (if any)
            $backupPath = storage_path('app/backups');
            $recentBackups = [];

            if (file_exists($backupPath)) {
                $files = glob($backupPath . '/*.sql');
                $files = array_map(function ($file) {
                    return [
                        'name' => basename($file),
                        'size' => $this->formatBytes(filesize($file)),
                        'created_at' => Carbon::createFromTimestamp(filemtime($file))->format('d M Y H:i:s')
                    ];
                }, $files);

                // Sort by creation time (newest first)
                usort($files, function ($a, $b) {
                    return strcmp($b['created_at'], $a['created_at']);
                });

                $recentBackups = array_slice($files, 0, 5); // Get last 5 backups
            }

            $databaseInfo = [
                'database_name' => $databaseName,
                'database_size' => $databaseSize[0]->size_mb . ' MB',
                'table_count' => $tableCount[0]->count,
                'recent_backups' => $recentBackups
            ];

            return response()->json([
                'success' => true,
                'data' => $databaseInfo
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil informasi database: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Format bytes to human readable format
     */
    private function formatBytes($bytes, $precision = 2)
    {
        if ($bytes === false || $bytes === null) {
            return 'Unknown';
        }

        $units = array('B', 'KB', 'MB', 'GB', 'TB');

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }

    /**
     * Upload AD/ART file
     */
    public function uploadAdArt(Request $request)
    {
        $request->validate([
            'ad_art' => 'required|file|mimes:pdf,doc,docx|max:10240', // 10MB
        ]);

        try {
            $settings = ApplicationSetting::firstOrCreate([]);

            // Hapus file lama jika ada
            if (!empty($settings->ad_art)) {
                // Karena di DB simpan "storage/..." → ubah ke path disk relatif
                $oldPath = str_replace('storage/', '', $settings->ad_art);
                if (Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }
            }

            // Simpan file baru
            $file     = $request->file('ad_art');
            $filename = 'ad_art_' . time() . '.' . $file->getClientOriginalExtension();
            $path     = $file->storeAs('documents', $filename, 'public'); // contoh: documents/ad_art_123.pdf

            // Simpan ke DB dengan prefix 'storage/'
            $settings->update([
                'ad_art' => 'storage/' . $path,
            ]);

            $payload = [
                'success'   => true,
                'message'   => 'File AD/ART berhasil diupload.',
                'file_url'  => $settings->ad_art ?? null,
                'file_name' => $file->getClientOriginalName(),
                'file_size' => $this->formatBytes($file->getSize() ?? 0),
            ];

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json($payload);
            }

            return redirect()->back()->with('success', $payload['message']);
        } catch (\Throwable $e) {
            $error = [
                'success' => false,
                'message' => 'Gagal mengupload file: ' . $e->getMessage(),
            ];

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json($error, 500);
            }

            return redirect()->back()->with('error', $error['message']);
        }
    }

    /**
     * Download AD/ART file
     */
    public function downloadAdArt()
    {
        try {
            $settings = ApplicationSetting::first();

            if (!$settings || !$settings->ad_art) {
                return redirect()->back()->with('error', 'File AD/ART tidak ditemukan.');
            }

            if (!file_exists($settings->ad_art)) {
                return redirect()->back()->with('error', 'File AD/ART tidak ditemukan di server.');
            }

            return response()->download($settings->ad_art);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal mendownload file: ' . $e->getMessage());
        }
    }

    /**
     * Delete AD/ART file
     */
    public function deleteAdArt()
    {
        try {
            $settings = ApplicationSetting::first();

            if (!$settings || !$settings->ad_art) {
                return response()->json([
                    'success' => false,
                    'message' => 'File AD/ART tidak ditemukan.'
                ], 404);
            }

            // Delete file from storage
            if (file_exists($settings->ad_art)) {
                unlink($settings->ad_art);
            }

            // Update settings
            $settings->update(['ad_art' => null]);

            return response()->json([
                'success' => true,
                'message' => 'File AD/ART berhasil dihapus.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus file: ' . $e->getMessage()
            ], 500);
        }
    }
}
