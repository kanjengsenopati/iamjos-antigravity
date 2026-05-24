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

        if (!empty($journalIds)) {
            $submissionIds = \App\Models\Submission::whereIn('journal_id', $journalIds)->pluck('id');
            $querySubmissions->whereIn('new_uuid', $submissionIds);
        }

        $migratedSubmissions = $querySubmissions->pluck('new_uuid')->filter();

        if ($migratedSubmissions->isNotEmpty()) {
            $this->cleanupSubmissions($migratedSubmissions);
        }
        
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
            \App\Models\Submission::whereIn('issue_id', $migratedIssues)->update(['issue_id' => null]);
            \App\Models\Publication::whereIn('issue_id', $migratedIssues)->update(['issue_id' => null]);
            \App\Models\Issue::whereIn('id', $migratedIssues)->forceDelete();
            \App\Models\LegacyMapping::where('legacy_table', 'issues')->whereIn('new_uuid', $migratedIssues)->delete();
        }
        
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
            $migratedIds = \App\Models\LegacyMapping::where('legacy_table', 'submissions')
                ->whereIn('new_uuid', $ids)
                ->pluck('new_uuid')
                ->toArray();
            if (!empty($migratedIds)) {
                $this->cleanupSubmissions($migratedIds);
            }
        } else {
            $migratedIds = \App\Models\LegacyMapping::where('legacy_table', 'issues')
                ->whereIn('new_uuid', $ids)
                ->pluck('new_uuid')
                ->toArray();
            if (!empty($migratedIds)) {
                \App\Models\Submission::whereIn('issue_id', $migratedIds)->update(['issue_id' => null]);
                \App\Models\Publication::whereIn('issue_id', $migratedIds)->update(['issue_id' => null]);
                \App\Models\Issue::whereIn('id', $migratedIds)->forceDelete();
                \App\Models\LegacyMapping::where('legacy_table', 'issues')->whereIn('new_uuid', $migratedIds)->delete();
            }
        }

        return response()->json(['success' => true, 'message' => 'Item terpilih berhasil dihapus.']);
    }

    /**
     * Reset specific journal data
     */
    public function resetJournal(Journal $journal)
    {
        // Get only migrated submission IDs for this journal
        $submissionIds = \App\Models\Submission::where('journal_id', $journal->id)
            ->whereIn('id', function($q) {
                $q->select('new_uuid')->from('legacy_mappings')->where('legacy_table', 'submissions');
            })
            ->pluck('id');

        // Get only migrated issue IDs for this journal
        $issueIds = \App\Models\Issue::where('journal_id', $journal->id)
            ->whereIn('id', function($q) {
                $q->select('new_uuid')->from('legacy_mappings')->where('legacy_table', 'issues');
            })
            ->pluck('id');

        // Get only migrated section IDs for this journal
        $sectionIds = \App\Models\Section::where('journal_id', $journal->id)
            ->whereIn('id', function($q) {
                $q->select('new_uuid')->from('legacy_mappings')->where('legacy_table', 'sections');
            })
            ->pluck('id');

        // 1. Clean submissions
        if ($submissionIds->isNotEmpty()) {
            $this->cleanupSubmissions($submissionIds);
        }

        // 2. Clean issues
        if ($issueIds->isNotEmpty()) {
            \App\Models\Submission::whereIn('issue_id', $issueIds)->update(['issue_id' => null]);
            \App\Models\Publication::whereIn('issue_id', $issueIds)->update(['issue_id' => null]);
            \App\Models\Issue::whereIn('id', $issueIds)->forceDelete();
            \App\Models\LegacyMapping::where('legacy_table', 'issues')->whereIn('new_uuid', $issueIds)->delete();
        }

        // 3. Clean sections
        if ($sectionIds->isNotEmpty()) {
            \App\Models\Section::whereIn('id', $sectionIds)->forceDelete();
            \App\Models\LegacyMapping::where('legacy_table', 'sections')->whereIn('new_uuid', $sectionIds)->delete();
        }

        // 4. Finally delete the journal if it was migrated
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

        if (!empty($journalIds)) {
            $queryJournals->whereIn('new_uuid', $journalIds);
        }

        $migratedJournals = $queryJournals->pluck('new_uuid')->filter()->toArray();

        if (!empty($migratedJournals)) {
            // 1. Get and cleanup all migrated submissions of these journals
            $submissionIds = \App\Models\Submission::whereIn('journal_id', $migratedJournals)
                ->whereIn('id', function($q) {
                    $q->select('new_uuid')->from('legacy_mappings')->where('legacy_table', 'submissions');
                })
                ->pluck('id');
            if ($submissionIds->isNotEmpty()) {
                $this->cleanupSubmissions($submissionIds);
            }

            // 2. Get and cleanup all migrated issues of these journals
            $issueIds = \App\Models\Issue::whereIn('journal_id', $migratedJournals)
                ->whereIn('id', function($q) {
                    $q->select('new_uuid')->from('legacy_mappings')->where('legacy_table', 'issues');
                })
                ->pluck('id');
            if ($issueIds->isNotEmpty()) {
                \App\Models\Submission::whereIn('issue_id', $issueIds)->update(['issue_id' => null]);
                \App\Models\Publication::whereIn('issue_id', $issueIds)->update(['issue_id' => null]);
                \App\Models\Issue::whereIn('id', $issueIds)->forceDelete();
                \App\Models\LegacyMapping::where('legacy_table', 'issues')->whereIn('new_uuid', $issueIds)->delete();
            }

            // 3. Get and cleanup all migrated sections of these journals
            $sectionIds = \App\Models\Section::whereIn('journal_id', $migratedJournals)
                ->whereIn('id', function($q) {
                    $q->select('new_uuid')->from('legacy_mappings')->where('legacy_table', 'sections');
                })
                ->pluck('id');
            if ($sectionIds->isNotEmpty()) {
                \App\Models\Section::whereIn('id', $sectionIds)->forceDelete();
                \App\Models\LegacyMapping::where('legacy_table', 'sections')->whereIn('new_uuid', $sectionIds)->delete();
            }

            // 4. Finally delete the migrated journals themselves
            \App\Models\Journal::whereIn('id', $migratedJournals)->forceDelete();
            \App\Models\LegacyMapping::where('legacy_table', 'journals')->whereIn('new_uuid', $migratedJournals)->delete();
        }

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
     * Reset & Cleanup All Configuration, Temporaries, and Migrated Records
     */
    public function reset()
    {
        // 1. Clean up migrated submissions
        $submissionIds = \App\Models\LegacyMapping::where('legacy_table', 'submissions')->pluck('new_uuid')->filter()->toArray();
        if (!empty($submissionIds)) {
            $this->cleanupSubmissions($submissionIds);
        }

        // 2. Clean up migrated issues
        $issueIds = \App\Models\LegacyMapping::where('legacy_table', 'issues')->pluck('new_uuid')->filter()->toArray();
        if (!empty($issueIds)) {
            \App\Models\Submission::whereIn('issue_id', $issueIds)->update(['issue_id' => null]);
            \App\Models\Publication::whereIn('issue_id', $issueIds)->update(['issue_id' => null]);
            \App\Models\Issue::whereIn('id', $issueIds)->forceDelete();
        }

        // 3. Clean up migrated sections
        $sectionIds = \App\Models\LegacyMapping::where('legacy_table', 'sections')->pluck('new_uuid')->filter()->toArray();
        if (!empty($sectionIds)) {
            \App\Models\Section::whereIn('id', $sectionIds)->forceDelete();
        }

        // 4. Clean up migrated journals
        $journalIds = \App\Models\LegacyMapping::where('legacy_table', 'journals')->pluck('new_uuid')->filter()->toArray();
        if (!empty($journalIds)) {
            \App\Models\Journal::whereIn('id', $journalIds)->forceDelete();
        }

        // 5. Clean up migrated users and roles
        $userIds = \App\Models\LegacyMapping::where('legacy_table', 'users')->pluck('new_uuid')->filter()->toArray();
        if (!empty($userIds)) {
            // Clean up discussion files first
            $discussionFiles = DB::table('discussion_files')->whereIn('user_id', $userIds)->get();
            foreach ($discussionFiles as $dFile) {
                if (!empty($dFile->file_path)) {
                    foreach (['public', 'local'] as $disk) {
                        if (\Illuminate\Support\Facades\Storage::disk($disk)->exists($dFile->file_path)) {
                            \Illuminate\Support\Facades\Storage::disk($disk)->delete($dFile->file_path);
                        }
                    }
                }
            }
            DB::table('discussion_files')->whereIn('user_id', $userIds)->delete();

            DB::table('journal_user_roles')->whereIn('user_id', $userIds)->delete();
            DB::table('model_has_roles')->whereIn('model_uuid', $userIds)->where('model_type', \App\Models\User::class)->delete();
            DB::table('model_has_permissions')->whereIn('model_uuid', $userIds)->where('model_type', \App\Models\User::class)->delete();
            DB::table('notifications')->whereIn('notifiable_id', $userIds)->where('notifiable_type', \App\Models\User::class)->delete();
            DB::table('sessions')->whereIn('user_id', $userIds)->delete();
            \App\Models\User::whereIn('id', $userIds)->forceDelete();

            // Flush Spatie permission cache after raw table changes
            app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
        }

        // 6. Truncate migration mappings and error logs
        \App\Models\LegacyMapping::truncate();
        \App\Models\MigrationError::truncate();

        // 7. Delete SQL file and active migration configuration
        $config = LegacySourceConfig::where('is_active', true)->first();
        if ($config) {
            if ($config->database && $config->driver === 'sql_file') {
                $path = \Illuminate\Support\Facades\Storage::disk('local')->path('migrations/' . $config->database);
                if (file_exists($path)) {
                    @unlink($path);
                }
            }
            $config->delete();
        }

        // 8. Delete SQLite intermediate database file
        $sqlitePath = storage_path('app/migration_temp.sqlite');
        if (file_exists($sqlitePath)) {
            @unlink($sqlitePath);
        }

        return back()->with('success', 'Seluruh konfigurasi, file dump, database temporary, dan data migrasi berhasil dibersihkan.');
    }

    /**
     * Clean up submissions and all related sub-records and storage files safely
     */
    private function cleanupSubmissions($submissionIds)
    {
        if (empty($submissionIds)) {
            return;
        }

        if ($submissionIds instanceof \Illuminate\Support\Collection) {
            $submissionIds = $submissionIds->toArray();
        }

        // 1. Collect sub-record IDs for mapping cleanup
        $authorIds = DB::table('submission_authors')->whereIn('submission_id', $submissionIds)->pluck('id')->toArray();
        $reviewAssignmentIds = DB::table('review_assignments')->whereIn('submission_id', $submissionIds)->pluck('id')->toArray();
        $reviewRoundIds = DB::table('review_rounds')->whereIn('submission_id', $submissionIds)->pluck('id')->toArray();
        $editorialAssignmentIds = DB::table('editorial_assignments')->whereIn('submission_id', $submissionIds)->pluck('id')->toArray();

        $discussions = DB::table('discussions')->whereIn('submission_id', $submissionIds)->get();
        $discussionIds = $discussions->pluck('id')->toArray();

        // 1b. Collect file & galley IDs BEFORE deleting rows (for mapping cleanup)
        $fileIds = DB::table('submission_files')->whereIn('submission_id', $submissionIds)->pluck('id')->toArray();
        $galleyIds = DB::table('publication_galleys')->whereIn('submission_id', $submissionIds)->pluck('id')->toArray();

        // 2. Physical Files Cleanup (Submission Files)
        $files = DB::table('submission_files')->whereIn('submission_id', $submissionIds)->get();
        foreach ($files as $file) {
            if (!empty($file->file_path)) {
                foreach (['public', 'local'] as $disk) {
                    if (\Illuminate\Support\Facades\Storage::disk($disk)->exists($file->file_path)) {
                        \Illuminate\Support\Facades\Storage::disk($disk)->delete($file->file_path);
                    }
                }
            }
        }

        // 2b. Clean up entire submission directory: journals/{slug}/articles/{seq_id}
        $submissionsInfo = DB::table('submissions')
            ->join('journals', 'submissions.journal_id', '=', 'journals.id')
            ->whereIn('submissions.id', $submissionIds)
            ->select('submissions.seq_id', 'journals.slug')
            ->get();

        foreach ($submissionsInfo as $subInfo) {
            if (!empty($subInfo->slug) && !empty($subInfo->seq_id)) {
                $dirPath = "journals/{$subInfo->slug}/articles/{$subInfo->seq_id}";
                foreach (['public', 'local'] as $disk) {
                    \Illuminate\Support\Facades\Storage::disk($disk)->deleteDirectory($dirPath);
                }
            }
        }

        // 3. Discussion Files Cleanup
        if (!empty($discussionIds)) {
            $messages = DB::table('discussion_messages')->whereIn('discussion_id', $discussionIds)->get();
            $messageIds = $messages->pluck('id')->toArray();
            if (!empty($messageIds)) {
                $discussionFiles = DB::table('discussion_files')->whereIn('discussion_message_id', $messageIds)->get();
                foreach ($discussionFiles as $dFile) {
                    if (!empty($dFile->file_path)) {
                        foreach (['public', 'local'] as $disk) {
                            if (\Illuminate\Support\Facades\Storage::disk($disk)->exists($dFile->file_path)) {
                                \Illuminate\Support\Facades\Storage::disk($disk)->delete($dFile->file_path);
                            }
                        }
                    }
                }
                DB::table('discussion_files')->whereIn('discussion_message_id', $messageIds)->delete();
                DB::table('discussion_messages')->whereIn('discussion_id', $discussionIds)->delete();
            }
            DB::table('discussion_participants')->whereIn('discussion_id', $discussionIds)->delete();
            DB::table('discussions')->whereIn('submission_id', $submissionIds)->delete();
        }

        // 4. Force Delete all sub-records and pivot associations
        DB::table('submission_authors')->whereIn('submission_id', $submissionIds)->delete();
        DB::table('publication_galleys')->whereIn('submission_id', $submissionIds)->delete();
        DB::table('submission_files')->whereIn('submission_id', $submissionIds)->delete();
        DB::table('review_rounds')->whereIn('submission_id', $submissionIds)->delete();
        DB::table('review_assignments')->whereIn('submission_id', $submissionIds)->delete();
        DB::table('editorial_assignments')->whereIn('submission_id', $submissionIds)->delete();
        DB::table('article_metrics')->whereIn('submission_id', $submissionIds)->delete();
        DB::table('submission_logs')->whereIn('submission_id', $submissionIds)->delete();
        DB::table('submission_notes')->whereIn('submission_id', $submissionIds)->delete();
        DB::table('submission_index_stats')->whereIn('submission_id', $submissionIds)->delete();
        DB::table('crossref_logs')->whereIn('submission_id', $submissionIds)->delete();
        DB::table('submission_keyword')->whereIn('submission_id', $submissionIds)->delete();
        DB::table('publications')->whereIn('submission_id', $submissionIds)->delete();
        DB::table('submissions')->whereIn('id', $submissionIds)->delete();

        // 5. Clean up Legacy Mappings (submissions + event_log + email_log)
        LegacyMapping::whereIn('new_uuid', $submissionIds)
            ->whereIn('legacy_table', ['submissions', 'event_log', 'email_log'])
            ->delete();

        // 5b. Clean up file & galley mappings (these point to submission_files.id / publication_galleys.id)
        if (!empty($fileIds)) {
            LegacyMapping::whereIn('new_uuid', $fileIds)->where('legacy_table', 'files')->delete();
        }
        if (!empty($galleyIds)) {
            LegacyMapping::whereIn('new_uuid', $galleyIds)->where('legacy_table', 'galleys')->delete();
        }

        if (!empty($authorIds)) {
            LegacyMapping::whereIn('new_uuid', $authorIds)->where('legacy_table', 'authors')->delete();
        }
        if (!empty($reviewAssignmentIds)) {
            LegacyMapping::whereIn('new_uuid', $reviewAssignmentIds)->where('legacy_table', 'review_assignments')->delete();
        }
        if (!empty($reviewRoundIds)) {
            LegacyMapping::whereIn('new_uuid', $reviewRoundIds)->where('legacy_table', 'review_rounds')->delete();
        }
        if (!empty($editorialAssignmentIds)) {
            LegacyMapping::whereIn('new_uuid', $editorialAssignmentIds)->where('legacy_table', 'stage_assignments')->delete();
        }
        if (!empty($discussionIds)) {
            LegacyMapping::whereIn('new_uuid', $discussionIds)->where('legacy_table', 'queries')->delete();
        }

        // 6. Clean up migration_errors for these submissions
        $legacySubmissionIds = LegacyMapping::where('legacy_table', 'submissions')
            ->whereIn('new_uuid', $submissionIds)
            ->pluck('legacy_id')
            ->toArray();
        if (!empty($legacySubmissionIds)) {
            \App\Models\MigrationError::where('legacy_table', 'submissions')
                ->whereIn('legacy_id', $legacySubmissionIds)
                ->delete();
        }
    }
}
