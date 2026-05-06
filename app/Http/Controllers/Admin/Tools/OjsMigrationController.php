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

        // Per-journal integrity breakdown
        $journalBreakdown = Journal::withCount(['issues', 'submissions'])
            ->orderBy('name')
            ->get()
            ->map(function ($journal) {
                $mappedJournals = LegacyMapping::where('legacy_table', 'journals')
                    ->where('new_uuid', $journal->id)
                    ->exists();

                return [
                    'id'            => $journal->id,
                    'name'          => $journal->name,
                    'abbreviation'  => $journal->abbreviation,
                    'path'          => $journal->path ?? $journal->slug,
                    'enabled'       => $journal->enabled,
                    'is_migrated'   => $mappedJournals,
                    'issues_count'  => (int)$journal->issues_count,
                    'articles_count'=> (int)$journal->submissions_count,
                    'integrity'     => $mappedJournals
                        ? ($journal->issues_count > 0 && $journal->submissions_count > 0 ? 'complete'
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
    public function resetArticles(Request $request)
    {
        $journalIds = $request->input('journal_ids');

        $querySubmissions = \App\Models\LegacyMapping::where('legacy_table', 'submissions');
        $queryPublications = \App\Models\LegacyMapping::where('legacy_table', 'publications');
        $queryGalleys = \App\Models\LegacyMapping::where('legacy_table', 'galleys');
        $queryAuthors = \App\Models\LegacyMapping::where('legacy_table', 'authors');

        if (!empty($journalIds)) {
            $submissionIds = \App\Models\Submission::whereIn('journal_id', $journalIds)->pluck('id');
            $querySubmissions->whereIn('new_uuid', $submissionIds);
            
            // For publications, authors, galleys we need to link back to submissions
            $queryPublications->whereIn('new_uuid', function($q) use ($submissionIds) {
                $q->select('id')->from('publications')->whereIn('submission_id', $submissionIds);
            });
            $queryGalleys->whereIn('new_uuid', function($q) use ($submissionIds) {
                $q->select('id')->from('submission_files')->whereIn('submission_id', $submissionIds);
            });
            $queryAuthors->whereIn('new_uuid', function($q) use ($submissionIds) {
                $q->select('id')->from('authors')->whereIn('submission_id', $submissionIds);
            });
        }

        $migratedSubmissions = $querySubmissions->pluck('new_uuid')->filter();
        $migratedPublications = $queryPublications->pluck('new_uuid')->filter();
        $migratedGalleys = $queryGalleys->pluck('new_uuid')->filter();

        if ($migratedSubmissions->isNotEmpty()) \App\Models\Submission::whereIn('id', $migratedSubmissions)->forceDelete();
        if ($migratedPublications->isNotEmpty()) \App\Models\Publication::whereIn('id', $migratedPublications)->forceDelete();
        if ($migratedGalleys->isNotEmpty()) \App\Models\SubmissionFile::whereIn('id', $migratedGalleys)->forceDelete();

        // Delete mappings
        $querySubmissions->delete();
        $queryPublications->delete();
        $queryGalleys->delete();
        $queryAuthors->delete();
        
        $scope = !empty($journalIds) ? 'pada jurnal terpilih' : 'seluruh jurnal';
        return back()->with('success', "Hanya data artikel hasil migrasi {$scope} yang berhasil dihapus.");
    }

    /**
     * Reset All Issue Data
     */
    public function resetIssues(Request $request)
    {
        $journalIds = $request->input('journal_ids');
        $query = \App\Models\LegacyMapping::where('legacy_table', 'issues');

        if (!empty($journalIds)) {
            $issueIds = \App\Models\Issue::whereIn('journal_id', $journalIds)->pluck('id');
            $query->whereIn('new_uuid', $issueIds);
        }

        $migratedIssues = $query->pluck('new_uuid')->filter();
        
        if ($migratedIssues->isNotEmpty()) {
            \App\Models\Issue::whereIn('id', $migratedIssues)->forceDelete();
        }
        
        $query->delete();
        
        $scope = !empty($journalIds) ? 'pada jurnal terpilih' : 'seluruh jurnal';
        return back()->with('success', "Hanya data issue hasil migrasi {$scope} yang berhasil dihapus.");
    }

    /**
     * Get issues and articles for a specific journal (JSON)
     */
    public function getJournalDetails(Journal $journal)
    {
        $issues = \App\Models\Issue::where('journal_id', $journal->id)
            ->orderBy('year', 'desc')
            ->orderBy('volume', 'desc')
            ->orderBy('number', 'desc')
            ->get()
            ->map(fn($i) => [
                'id' => $i->id,
                'title' => $i->title ?? "Vol {$i->volume} No {$i->number} ({$i->year})",
                'is_migrated' => LegacyMapping::where('legacy_table', 'issues')->where('new_uuid', $i->id)->exists(),
            ]);

        $articles = \App\Models\Submission::where('journal_id', $journal->id)
            ->with('currentPublication')
            ->latest()
            ->limit(500) // Safety limit
            ->get()
            ->map(fn($s) => [
                'id' => $s->id,
                'title' => $s->currentPublication->title ?? 'Untitled',
                'is_migrated' => LegacyMapping::where('legacy_table', 'submissions')->where('new_uuid', $s->id)->exists(),
            ]);

        return response()->json([
            'success' => true,
            'journal' => ['id' => $journal->id, 'name' => $journal->name],
            'issues' => $issues,
            'articles' => $articles
        ]);
    }

    /**
     * Reset selected items (Issues/Articles)
     */
    public function resetSelectedItems(Request $request)
    {
        $request->validate([
            'type' => 'required|in:issues,articles',
            'ids' => 'required|array'
        ]);

        $type = $request->type;
        $ids = $request->ids;

        if ($type === 'articles') {
            \App\Models\Submission::whereIn('id', $ids)->forceDelete();
            \App\Models\LegacyMapping::where('legacy_table', 'submissions')->whereIn('new_uuid', $ids)->delete();
            \App\Models\LegacyMapping::where('legacy_table', 'publications')->whereIn('new_uuid', function($q) use ($ids) {
                $q->select('id')->from('publications')->whereIn('submission_id', $ids);
            })->delete();
            \App\Models\LegacyMapping::where('legacy_table', 'authors')->whereIn('new_uuid', function($q) use ($ids) {
                $q->select('id')->from('authors')->whereIn('submission_id', $ids);
            })->delete();
        } else {
            \App\Models\Issue::whereIn('id', $ids)->forceDelete();
            \App\Models\LegacyMapping::where('legacy_table', 'issues')->whereIn('new_uuid', $ids)->delete();
        }

        return response()->json(['success' => true, 'message' => 'Item terpilih berhasil dihapus.']);
    }

    /**
     * Reset specific journal data
     */
    public function resetJournal(Journal $journal)
    {
        // 1. Get all submission IDs for this journal
        $submissionIds = \App\Models\Submission::where('journal_id', $journal->id)->pluck('id');
        
        // 2. Get all issue IDs for this journal
        $issueIds = \App\Models\Issue::where('journal_id', $journal->id)->pluck('id');

        // 3. Delete Submissions (will trigger cascaded deletes if configured, but let's be safe)
        if ($submissionIds->isNotEmpty()) {
            \App\Models\Submission::whereIn('id', $submissionIds)->forceDelete();
            \App\Models\LegacyMapping::where('legacy_table', 'submissions')->whereIn('new_uuid', $submissionIds)->delete();
            \App\Models\LegacyMapping::where('legacy_table', 'publications')->whereIn('new_uuid', function($q) use ($submissionIds) {
                $q->select('id')->from('publications')->whereIn('submission_id', $submissionIds);
            })->delete();
        }

        // 4. Delete Issues
        if ($issueIds->isNotEmpty()) {
            \App\Models\Issue::whereIn('id', $issueIds)->forceDelete();
            \App\Models\LegacyMapping::where('legacy_table', 'issues')->whereIn('new_uuid', $issueIds)->delete();
        }

        // 5. Delete Sections
        $sectionIds = \App\Models\Section::where('journal_id', $journal->id)->pluck('id');
        if ($sectionIds->isNotEmpty()) {
            \App\Models\Section::whereIn('id', $sectionIds)->forceDelete();
            \App\Models\LegacyMapping::where('legacy_table', 'sections')->whereIn('new_uuid', $sectionIds)->delete();
        }

        // 6. Finally delete the journal if it was migrated
        $isMigrated = \App\Models\LegacyMapping::where('legacy_table', 'journals')->where('new_uuid', $journal->id)->exists();
        if ($isMigrated) {
            $journal->forceDelete();
            \App\Models\LegacyMapping::where('legacy_table', 'journals')->where('new_uuid', $journal->id)->delete();
        }

        return back()->with('success', "Data jurnal '{$journal->name}' berhasil dibersihkan.");
    }

    /**
     * Reset All Journal Data
     */
    public function resetJournals(Request $request)
    {
        $journalIds = $request->input('journal_ids');

        $queryJournals = \App\Models\LegacyMapping::where('legacy_table', 'journals');
        $querySections = \App\Models\LegacyMapping::where('legacy_table', 'sections');

        if (!empty($journalIds)) {
            $queryJournals->whereIn('new_uuid', $journalIds);
            $querySections->whereIn('new_uuid', function($q) use ($journalIds) {
                $q->select('id')->from('sections')->whereIn('journal_id', $journalIds);
            });
        }

        $migratedJournals = $queryJournals->pluck('new_uuid')->filter();
        $migratedSections = $querySections->pluck('new_uuid')->filter();

        if ($migratedJournals->isNotEmpty()) {
            \App\Models\Journal::whereIn('id', $migratedJournals)->forceDelete();
        }
        
        if ($migratedSections->isNotEmpty()) {
            \App\Models\Section::whereIn('id', $migratedSections)->forceDelete();
        }

        $queryJournals->delete();
        $querySections->delete();
        
        $scope = !empty($journalIds) ? 'pada jurnal terpilih' : 'seluruh jurnal';
        return back()->with('success', "Hanya data jurnal hasil migrasi {$scope} yang berhasil dihapus.");
    }


    /**
     * Run Migration Step
     */
    public function runStep(Request $request)
    {
        $step   = $request->step;
        $journalIds = $request->input('journal_ids', []); // New: journal filter
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

            // Apply journal filters if provided
            if (!empty($journalIds)) {
                $this->migrationService->setJournalFilters($journalIds);
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
