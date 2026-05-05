<?php

namespace App\Http\Controllers\Admin\Tools;

use App\Http\Controllers\Controller;
use App\Models\LegacySourceConfig;
use App\Models\LegacyMapping;
use App\Models\Journal;
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
        $journalBreakdown = [];
        $previewData = [];

        if ($config && $config->database) {
            $filePath = \Illuminate\Support\Facades\Storage::disk('local')->path('migrations/' . $config->database);
            if (file_exists($filePath)) {
                try {
                    $this->migrationService->setSqlSource($filePath);
                    $stats = $this->migrationService->getMigrationStats();
                    $previewData = $this->migrationService->getSqlPreviewStats();
                } catch (\Exception $e) {
                    $fileError = $e->getMessage();
                }
            } else {
                $fileError = "File SQL tidak ditemukan di storage.";
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
        $step = $request->step;
        $config = LegacySourceConfig::where('is_active', true)->first();

        if (!$config || !$config->database) {
            return response()->json(['success' => false, 'message' => 'File SQL belum diunggah.']);
        }

        $filePath = \Illuminate\Support\Facades\Storage::disk('local')->path('migrations/' . $config->database);
        if (!file_exists($filePath)) {
            return response()->json(['success' => false, 'message' => 'File SQL hilang dari storage.']);
        }

        try {
            $this->migrationService->setSqlSource($filePath);

            switch ($step) {
                case 'users': $this->migrationService->migrateUsers(); break;
                case 'journals': $this->migrationService->migrateJournals(); break;
                case 'sections': $this->migrationService->migrateSections(); break;
                case 'issues': $this->migrationService->migrateIssues(); break;
                case 'submissions': $this->migrationService->migrateSubmissions(); break;
                case 'authors': $this->migrationService->migrateAuthors(); break;
                case 'reviews': $this->migrationService->migrateReviews(); break;
                case 'discussions': $this->migrationService->migrateDiscussions(); break;
                case 'logs': $this->migrationService->migrateLogs(); break;
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
            $path = \Illuminate\Support\Facades\Storage::disk('local')->path('migrations/' . $config->database);
            if (file_exists($path)) {
                unlink($path);
            }
            $config->delete();
        }
        
        return back()->with('success', 'Konfigurasi dan file migration telah dihapus.');
    }
}
