<?php

namespace App\Http\Controllers\Admin\Tools;

use App\Http\Controllers\Controller;
use App\Models\LegacySourceConfig;
use App\Models\LegacyMapping;
use App\Models\Journal;
use App\Services\OjsMigrationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\SqlDumpParserService;

class OjsMigrationController extends Controller
{
    protected $migrationService;
    protected $parser;

    public function __construct(OjsMigrationService $migrationService, SqlDumpParserService $parser)
    {
        $this->migrationService = $migrationService;
        $this->parser = $parser;
    }

    /**
     * Dashboard Index
     */
    public function index()
    {
        $config = LegacySourceConfig::where('is_active', true)->first();
        $stats = [];
        $fileError = null;
        $journalBreakdown = [];
        $previewData = [];

        if ($config) {
            try {
                if ($config->connection_name === 'ojs_legacy') {
                    // --- Engine A: Direct Database ---
                    $this->migrationService->setDbSource($config);
                } elseif ($config->database && $config->database !== 'none') {
                    // --- Engine B: SQL File ---
                    $filePath = \Illuminate\Support\Facades\Storage::disk('local')->path('migrations/' . $config->database);
                    if (!file_exists($filePath)) throw new \Exception("File SQL tidak ditemukan di storage.");
                    $this->migrationService->setSqlSource($filePath);
                }
                $stats = $this->migrationService->getMigrationStats();
                $previewData = $this->migrationService->getSqlPreviewStats();
            } catch (\Exception $e) {
                $fileError = $e->getMessage();
            }
        }

        // Per-journal integrity breakdown (always load if journals exist)
        $journalBreakdown = Journal::withCount([
            'issues',
            'submissions as articles_count',
        ])
        ->with(['issues' => fn($q) => $q->select('id', 'journal_id')])
        ->select('id', 'name', 'abbreviation', 'slug', 'path', 'enabled')
        ->orderBy('name')
        ->get()
        ->map(function ($journal) {
            $mappedJournals = LegacyMapping::where('legacy_table', 'journals')
                ->where('new_uuid', $journal->id)
                ->exists();

            $mappedIssuesCount = LegacyMapping::where('legacy_table', 'issues')
                ->whereIn('new_uuid', $journal->issues->pluck('id'))
                ->count();

            return [
                'id'            => $journal->id,
                'name'          => $journal->name,
                'abbreviation'  => $journal->abbreviation,
                'path'          => $journal->path ?? $journal->slug,
                'enabled'       => $journal->enabled,
                'is_migrated'   => $mappedJournals,
                'issues_count'  => $journal->issues_count,
                'articles_count'=> $journal->articles_count,
                'mapped_issues' => $mappedIssuesCount,
                'integrity'     => $mappedJournals
                    ? ($journal->issues_count > 0 && $journal->articles_count > 0 ? 'complete'
                        : ($journal->issues_count > 0 ? 'partial' : 'empty'))
                    : 'native',
            ];
        });

        return view('admin.tools.migration.index', compact('config', 'stats', 'fileError', 'journalBreakdown', 'previewData'));
    }

    /**
     * Update Migration Configuration
     */
    public function upload(Request $request)
    {
        $type = $request->input('type', 'sql');

        $msg = 'Konfigurasi berhasil diperbarui.';

        if ($type === 'database') {
            // --- Engine A: Direct DB Connection ---
            $request->validate([
                'host'     => 'required|string',
                'port'     => 'required|integer',
                'database' => 'required|string',
                'username' => 'required|string',
            ]);

            // Test the connection first
            try {
                \Illuminate\Support\Facades\Config::set('database.connections.ojs_legacy', [
                    'driver'    => 'mysql',
                    'host'      => $request->host,
                    'port'      => $request->port,
                    'database'  => $request->database,
                    'username'  => $request->username,
                    'password'  => $request->password ?? '',
                    'charset'   => 'utf8mb4',
                    'collation' => 'utf8mb4_unicode_ci',
                    'prefix'    => '',
                ]);
                DB::connection('ojs_legacy')->getPdo();
            } catch (\Exception $e) {
                return back()->withErrors(['host' => 'Koneksi gagal: ' . $e->getMessage()])->withInput();
            }

            LegacySourceConfig::updateOrCreate(
                ['is_active' => true],
                [
                    'connection_name' => 'ojs_legacy',
                    'driver'          => 'mysql',
                    'host'            => $request->host,
                    'port'            => (int)$request->port,
                    'database'        => $request->database,
                    'username'        => $request->username,
                    'password'        => encrypt($request->password ?? ''),
                ]
            );

        } elseif ($type === 'sql') {
            // --- Engine B: SQL File Upload ---
            $request->validate([
                'sql_file' => 'required|file',
            ]);

            $file = $request->file('sql_file');
            $filename = 'ojs_dump_' . time() . '.' . $file->getClientOriginalExtension();
            $file->storeAs('migrations', $filename);

            $config = LegacySourceConfig::updateOrCreate(
                ['is_active' => true],
                [
                    'connection_name' => 'sql_file',
                    'driver'          => 'sql_file',
                    'database'        => $filename,
                    'host'            => 'localhost',
                    'username'        => 'none',
                    'password'        => encrypt('none'),
                ]
            );

            // Trigger Indexing to SQLite
            try {
                $this->parser->setFile(storage_path("app/private/migrations/{$filename}"));
                $this->parser->indexFile();
                $msg = 'Konfigurasi berhasil diperbarui dan data SQL telah di-index ke SQLite.';
            } catch (\Exception $e) {
                return back()->with('error', 'Gagal memproses SQL: ' . $e->getMessage());
            }
        } else {
            // Legacy: files path
            $request->validate(['base_url' => 'required|string']);
            LegacySourceConfig::updateOrCreate(
                ['is_active' => true],
                [
                    'base_url'  => $request->base_url,
                    'host'      => 'localhost',
                    'database'  => 'none',
                    'username'  => 'none',
                    'password'  => encrypt('none'),
                ]
            );
        }

        return back()->with('success', $msg);
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
        $migratedSubmissions = \App\Models\LegacyMapping::where('legacy_table', 'submissions')->pluck('new_uuid')->filter();
        $migratedPublications = \App\Models\LegacyMapping::where('legacy_table', 'publications')->pluck('new_uuid')->filter();
        $migratedGalleys = \App\Models\LegacyMapping::where('legacy_table', 'galleys')->pluck('new_uuid')->filter();

        if ($migratedSubmissions->isNotEmpty()) \App\Models\Submission::whereIn('id', $migratedSubmissions)->forceDelete();
        if ($migratedPublications->isNotEmpty()) \App\Models\Publication::whereIn('id', $migratedPublications)->forceDelete();
        if ($migratedGalleys->isNotEmpty()) \App\Models\SubmissionFile::whereIn('id', $migratedGalleys)->forceDelete();

        \App\Models\LegacyMapping::where('legacy_table', 'submissions')->delete();
        \App\Models\LegacyMapping::where('legacy_table', 'publications')->delete();
        \App\Models\LegacyMapping::where('legacy_table', 'galleys')->delete();
        \App\Models\LegacyMapping::where('legacy_table', 'authors')->delete();
        
        return back()->with('success', 'Hanya data artikel hasil migrasi yang berhasil dihapus.');
    }

    /**
     * Reset All Issue Data
     */
    public function resetIssues()
    {
        $migratedIssues = \App\Models\LegacyMapping::where('legacy_table', 'issues')->pluck('new_uuid')->filter();
        
        if ($migratedIssues->isNotEmpty()) {
            \App\Models\Issue::whereIn('id', $migratedIssues)->forceDelete();
        }
        
        \App\Models\LegacyMapping::where('legacy_table', 'issues')->delete();
        
        return back()->with('success', 'Hanya data issue hasil migrasi yang berhasil dihapus.');
    }

    /**
     * Reset All Journal Data
     */
    public function resetJournals()
    {
        // Journals often have many relations, we use forceDelete
        $migratedJournals = \App\Models\LegacyMapping::where('legacy_table', 'journals')->pluck('new_uuid')->filter();
        $migratedSections = \App\Models\LegacyMapping::where('legacy_table', 'sections')->pluck('new_uuid')->filter();

        if ($migratedJournals->isNotEmpty()) {
            \App\Models\Journal::whereIn('id', $migratedJournals)->forceDelete();
        }
        
        if ($migratedSections->isNotEmpty()) {
            \App\Models\Section::whereIn('id', $migratedSections)->forceDelete();
        }

        \App\Models\LegacyMapping::where('legacy_table', 'journals')->delete();
        \App\Models\LegacyMapping::where('legacy_table', 'sections')->delete();
        
        return back()->with('success', 'Hanya data jurnal hasil migrasi yang berhasil dihapus.');
    }

    /**
     * Run Migration Step
     */
    public function runStep(Request $request)
    {
        $step   = $request->step;
        $config = LegacySourceConfig::where('is_active', true)->first();

        if (!$config) {
            return response()->json(['success' => false, 'message' => 'Konfigurasi migrasi belum ada. Silakan isi di tab Database Source.']);
        }

        try {
            // --- Route to correct engine ---
            if ($config->connection_name === 'ojs_legacy') {
                $this->migrationService->setDbSource($config);
            } else {
                $filePath = \Illuminate\Support\Facades\Storage::disk('local')->path('migrations/' . $config->database);
                if (!file_exists($filePath)) {
                    return response()->json(['success' => false, 'message' => 'File SQL hilang dari storage.']);
                }
                $this->migrationService->setSqlSource($filePath);
            }

            switch ($step) {
                case 'users':       $this->migrationService->migrateUsers(); break;
                case 'journals':    $this->migrationService->migrateJournals(); break;
                case 'sections':    $this->migrationService->migrateSections(); break;
                case 'issues':      $this->migrationService->migrateIssues(); break;
                case 'submissions': $this->migrationService->migrateSubmissions(); break;
                case 'authors':     $this->migrationService->migrateAuthors(); break;
                case 'reviews':     $this->migrationService->migrateReviews(); break;
                case 'discussions': $this->migrationService->migrateDiscussions(); break;
                case 'logs':        $this->migrationService->migrateLogs(); break;
                case 'metrics':     $this->migrationService->migrateMetrics(); break;
                case 'galleys':     $this->migrationService->migrateGalleys($config->base_url); break;
                default: throw new \Exception("Step tidak valid.");
            }

            $config->update(['last_synced_at' => now()]);
            return response()->json(['success' => true, 'message' => "Migrasi step '{$step}' berhasil."]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('MigrationStep Error: ' . $e->getMessage());
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
            $path = \Illuminate\Support\Facades\Storage::disk('local')->path('migrations/' . $config->database);
            if (file_exists($path)) {
                unlink($path);
            }
            $config->delete();
        }
        
        return back()->with('success', 'Konfigurasi dan file migration telah dihapus.');
    }
}
