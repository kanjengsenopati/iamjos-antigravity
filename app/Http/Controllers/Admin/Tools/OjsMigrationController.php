<?php

namespace App\Http\Controllers\Admin\Tools;

use App\Http\Controllers\Controller;
use App\Models\LegacySourceConfig;
use App\Models\LegacyMapping;
use App\Services\OjsMigrationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OjsMigrationController extends Controller
{
    protected $migrationService;

    public function __construct(OjsMigrationService $migrationService)
    {
        $this->migrationService = $migrationService;
    }

    /**
     * Dashboard Index
     */
    public function index()
    {
        $config = LegacySourceConfig::where('is_active', true)->first();
        $stats = [];
        $fileError = null;

        if ($config && $config->database) {
            $filePath = storage_path('app/migrations/' . $config->database);
            if (file_exists($filePath)) {
                try {
                    $stats = $this->migrationService->getMigrationStats();
                } catch (\Exception $e) {
                    $fileError = $e->getMessage();
                }
            } else {
                $fileError = "File SQL tidak ditemukan di storage.";
            }
        }

        return view('admin.tools.migration.index', compact('config', 'stats', 'fileError'));
    }

    /**
     * Update Migration Configuration
     */
    public function upload(Request $request)
    {
        $type = $request->input('type', 'sql');

        if ($type === 'sql') {
            $request->validate([
                'sql_file' => 'required|file',
            ]);

            $file = $request->file('sql_file');
            $filename = 'ojs_dump_' . time() . '.' . $file->getClientOriginalExtension();
            $file->storeAs('migrations', $filename);

            LegacySourceConfig::updateOrCreate(
                ['is_active' => true],
                [
                    'driver' => 'sql_file',
                    'database' => $filename,
                    'host' => 'localhost',
                    'username' => 'none',
                    'password' => encrypt('none'),
                ]
            );
        } else {
            $request->validate([
                'base_url' => 'required|string',
            ]);

            LegacySourceConfig::updateOrCreate(
                ['is_active' => true],
                [
                    'base_url' => $request->base_url,
                    'host' => 'localhost',
                    'database' => 'none',
                    'username' => 'none',
                    'password' => encrypt('none'),
                ]
            );
        }

        return back()->with('success', 'Konfigurasi berhasil diperbarui.');
    }

    /**
     * Reset Migration Progress
     */
    public function resetProgress()
    {
        \App\Models\LegacyMapping::truncate();
        
        return back()->with('success', 'Progress migrasi berhasil direset.');
    }

    /**
     * Reset All Article Data
     */
    public function resetArticles()
    {
        \App\Models\Submission::query()->forceDelete();
        \App\Models\Publication::query()->forceDelete();
        \App\Models\SubmissionFile::query()->forceDelete();
        \App\Models\LegacyMapping::where('legacy_type', 'submissions')->delete();
        \App\Models\LegacyMapping::where('legacy_type', 'galleys')->delete();
        
        return back()->with('success', 'Semua data artikel berhasil dihapus.');
    }

    /**
     * Reset All Issue Data
     */
    public function resetIssues()
    {
        \App\Models\Issue::query()->forceDelete();
        \App\Models\LegacyMapping::where('legacy_type', 'issues')->delete();
        
        return back()->with('success', 'Semua data issue berhasil dihapus.');
    }

    /**
     * Reset All Journal Data
     */
    public function resetJournals()
    {
        // Journals often have many relations, we use forceDelete
        \App\Models\Journal::query()->forceDelete();
        \App\Models\LegacyMapping::where('legacy_type', 'journals')->delete();
        \App\Models\LegacyMapping::where('legacy_type', 'sections')->delete();
        
        return back()->with('success', 'Semua data jurnal berhasil dihapus.');
    }

    /**
     * Run Migration Step
     */
    public function runStep(Request $request)
    {
        $step = $request->step;
        $config = LegacySourceConfig::where('is_active', true)->first();

        if (!$config || !$config->database) {
            return response()->json(['success' => false, 'message' => 'File SQL belum diunggah.']);
        }

        $filePath = storage_path('app/migrations/' . $config->database);
        if (!file_exists($filePath)) {
            return response()->json(['success' => false, 'message' => 'File SQL hilang dari storage.']);
        }

        try {
            $this->migrationService->setSqlSource($filePath);

            switch ($step) {
                case 'journals': $this->migrationService->migrateJournals(); break;
                case 'sections': $this->migrationService->migrateSections(); break;
                case 'issues': $this->migrationService->migrateIssues(); break;
                case 'submissions': $this->migrationService->migrateSubmissions(); break;
                case 'authors': $this->migrationService->migrateAuthors(); break;
                case 'metrics': $this->migrationService->migrateMetrics(); break;
                case 'galleys': $this->migrationService->migrateGalleys($config->base_url); break;
                default: throw new \Exception("Step tidak valid.");
            }

            return response()->json(['success' => true, 'message' => "Migrasi step $step berhasil."]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Reset Migration
     */
    public function reset()
    {
        $config = LegacySourceConfig::where('is_active', true)->first();
        if ($config && $config->database) {
            $path = storage_path('app/migrations/' . $config->database);
            if (file_exists($path)) {
                unlink($path);
            }
            $config->delete();
        }
        
        return back()->with('success', 'Konfigurasi dan file migration telah dihapus.');
    }
}
