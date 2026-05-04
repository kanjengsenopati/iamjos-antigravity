<?php

namespace App\Services;

use App\Models\Journal;
use App\Models\Section;
use App\Models\Issue;
use App\Models\Submission;
use App\Models\Publication;
use App\Models\LegacyMapping;
use App\Models\SubmissionAuthor;
use App\Models\ArticleMetric;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;

class OjsMigrationService
{
    /**
     * Test connection to legacy database
     */
    public function testConnection(array $config): bool
    {
        try {
            $this->setupConnection($config);
            DB::connection('legacy')->getPdo();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Setup dynamic database connection
     */
    public function setupConnection(array $config)
    {
        Config::set('database.connections.legacy', [
            'driver' => $config['driver'] ?? 'mysql',
            'host' => $config['host'],
            'port' => $config['port'] ?? '3306',
            'database' => $config['database'],
            'username' => $config['username'],
            'password' => $config['password'],
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
        ]);

        DB::purge('legacy');
    }

    /**
     * Get Migration Matrix Statistics
     */
    public function getMigrationStats(): array
    {
        $stats = [];
        
        $modules = [
            'journals' => ['legacy_table' => 'journals', 'new_model' => Journal::class],
            'sections' => ['legacy_table' => 'sections', 'new_model' => Section::class],
            'issues' => ['legacy_table' => 'issues', 'new_model' => Issue::class],
            'submissions' => ['legacy_table' => 'submissions', 'new_model' => Submission::class],
            'authors' => ['legacy_table' => 'authors', 'new_model' => SubmissionAuthor::class],
            'galleys' => ['legacy_table' => 'galleys', 'new_model' => \App\Models\PublicationGalley::class],
        ];

        foreach ($modules as $key => $config) {
            $legacyCount = 0;
            $legacyTable = $config['legacy_table'];

            try {
                // Resilient table detection
                if (\Illuminate\Support\Facades\Schema::connection('legacy')->hasTable($legacyTable)) {
                    $legacyCount = DB::connection('legacy')->table($legacyTable)->count();
                } elseif ($key === 'submissions' && \Illuminate\Support\Facades\Schema::connection('legacy')->hasTable('articles')) {
                    $legacyCount = DB::connection('legacy')->table('articles')->count();
                } elseif ($key === 'galleys') {
                    if (\Illuminate\Support\Facades\Schema::connection('legacy')->hasTable('publication_galleys')) {
                        $legacyCount = DB::connection('legacy')->table('publication_galleys')->count();
                    } elseif (\Illuminate\Support\Facades\Schema::connection('legacy')->hasTable('submission_galleys')) {
                        $legacyCount = DB::connection('legacy')->table('submission_galleys')->count();
                    }
                }
            } catch (\Exception $e) {
                // Fallback to 0 if table not accessible
            }

            $stats[$key] = [
                'legacy_count' => $legacyCount,
                'migrated_count' => $config['new_model']::count(),
                'mapping_count' => LegacyMapping::where('legacy_table', $legacyTable)->count(),
            ];
        }

        // Resilient Metrics Stats
        $stats['metrics_views'] = ['legacy_count' => 0, 'migrated_count' => ArticleMetric::where('type', ArticleMetric::TYPE_VIEW)->count()];
        $stats['metrics_downloads'] = ['legacy_count' => 0, 'migrated_count' => ArticleMetric::where('type', ArticleMetric::TYPE_DOWNLOAD)->count()];

        try {
            if (\Illuminate\Support\Facades\Schema::connection('legacy')->hasTable('metrics')) {
                $stats['metrics_views']['legacy_count'] = DB::connection('legacy')->table('metrics')->whereIn('assoc_type', [259, 1048585])->sum('metric') ?: 0;
                $stats['metrics_downloads']['legacy_count'] = DB::connection('legacy')->table('metrics')->where('assoc_type', 515)->sum('metric') ?: 0;
            }
        } catch (\Exception $e) {}

        return $stats;
    }

    /**
     * Migrate Journals
     */
    public function migrateJournals()
    {
        $legacyJournals = DB::connection('legacy')->table('journals')->get();

        foreach ($legacyJournals as $lJournal) {
            $settings = DB::connection('legacy')->table('journal_settings')
                ->where('journal_id', $lJournal->journal_id)
                ->get();

            $names = $settings->where('setting_name', 'name')->pluck('setting_value', 'locale');
            $descriptions = $settings->where('setting_name', 'description')->pluck('setting_value', 'locale');

            $journal = Journal::updateOrCreate(
                ['slug' => $lJournal->path],
                [
                    'name' => strip_tags($names->first() ?? 'Migrated Journal'),
                    'description' => strip_tags($descriptions->first() ?? null),
                    'enabled' => (bool)$lJournal->enabled,
                    'seq' => (int)$lJournal->seq,
                ]
            );

            LegacyMapping::setMapping('journals', $lJournal->journal_id, $journal->id);
        }
    }

    /**
     * Migrate Sections
     */
    public function migrateSections()
    {
        $legacySections = DB::connection('legacy')->table('sections')->get();

        foreach ($legacySections as $lSection) {
            $newJournalId = LegacyMapping::getMapping('journals', $lSection->journal_id);
            if (!$newJournalId) continue;

            $settings = DB::connection('legacy')->table('section_settings')
                ->where('section_id', $lSection->section_id)
                ->get();

            $titles = $settings->where('setting_name', 'title')->pluck('setting_value', 'locale');

            $section = Section::updateOrCreate(
                [
                    'journal_id' => $newJournalId,
                    'abbreviation' => $lSection->abbrev ?? 'SEC',
                ],
                [
                    'title' => $titles->first() ?? 'General Section',
                    'seq' => (int)$lSection->seq,
                    'is_active' => true,
                ]
            );

            LegacyMapping::setMapping('sections', $lSection->section_id, $section->id);
        }
    }

    /**
     * Migrate Issues
     */
    public function migrateIssues()
    {
        $legacyIssues = DB::connection('legacy')->table('issues')->get();

        foreach ($legacyIssues as $lIssue) {
            $newJournalId = LegacyMapping::getMapping('journals', $lIssue->journal_id);
            if (!$newJournalId) continue;

            $settings = DB::connection('legacy')->table('issue_settings')
                ->where('issue_id', $lIssue->issue_id)
                ->get();

            $titles = $settings->where('setting_name', 'title')->pluck('setting_value', 'locale');
            $descriptions = $settings->where('setting_name', 'description')->pluck('setting_value', 'locale');

            $issue = Issue::updateOrCreate(
                [
                    'journal_id' => $newJournalId,
                    'volume' => $lIssue->volume,
                    'number' => $lIssue->number,
                    'year' => $lIssue->year,
                ],
                [
                    'title' => $titles->first() ?? null,
                    'description' => $descriptions->first() ?? null,
                    'is_published' => (bool)$lIssue->published,
                    'published_at' => $lIssue->date_published,
                    'seq_id' => (int)$lIssue->issue_id, // Map OJS issue_id to seq_id
                ]
            );

            LegacyMapping::setMapping('issues', $lIssue->issue_id, $issue->id);
        }
    }

    /**
     * Migrate Submissions
     */
    public function migrateSubmissions()
    {
        $legacySubmissions = DB::connection('legacy')->table('submissions')->get();

        foreach ($legacySubmissions as $lSub) {
            $newJournalId = LegacyMapping::getMapping('journals', $lSub->context_id);
            $newSectionId = LegacyMapping::getMapping('sections', $lSub->section_id);
            $newIssueId = LegacyMapping::getMapping('issues', $lSub->current_publication_id ? 
                DB::connection('legacy')->table('publications')->where('publication_id', $lSub->current_publication_id)->value('issue_id') : null);

            if (!$newJournalId) continue;

            $metadata = DB::connection('legacy')->table('publication_settings')
                ->where('publication_id', $lSub->current_publication_id)
                ->get();

            $titles = $metadata->where('setting_name', 'title')->pluck('setting_value', 'locale');
            $abstracts = $metadata->where('setting_name', 'abstract')->pluck('setting_value', 'locale');

            // --- Deep Dive: Citations / References Extraction ---
            $rawCitations = null;
            $citationsSetting = $metadata->whereIn('setting_name', ['citations', 'references'])->first();
            
            if ($citationsSetting && !empty($citationsSetting->setting_value)) {
                $rawCitations = $citationsSetting->setting_value;
            } else {
                try {
                    if (\Illuminate\Support\Facades\Schema::connection('legacy')->hasTable('citations')) {
                        $citationRows = DB::connection('legacy')->table('citations')
                            ->where('publication_id', $lSub->current_publication_id)
                            ->orderBy('seq')
                            ->pluck('raw_citation')
                            ->toArray();
                            
                        if (!empty($citationRows)) {
                            $rawCitations = implode("\n", array_filter($citationRows));
                        }
                    }
                } catch (\Exception $e) {
                    // Silently continue if citations table doesn't exist
                }
            }
            
            // Clean HTML from citations for clean Google Scholar indexing
            $rawCitations = $rawCitations ? strip_tags($rawCitations) : null;
            // ----------------------------------------------------

            $existingId = LegacyMapping::getMapping('submissions', $lSub->submission_id);
            
            // Self-healing: if no mapping, try to find by title and journal
            if (!$existingId) {
                $existingId = Submission::where('journal_id', $newJournalId)
                    ->where('title', $titles->first())
                    ->whereDate('created_at', Carbon::parse($lSub->date_submitted)->toDateString())
                    ->value('id');
            }

            $submission = Submission::updateOrCreate(
                ['id' => $existingId ?? Str::uuid()->toString()],
                [
                    'journal_id' => $newJournalId,
                    'user_id' => User::first()->id, // Placeholder
                    'section_id' => $newSectionId,
                    'issue_id' => $newIssueId,
                    'status' => Submission::STATUS_PUBLISHED,
                    'stage' => Submission::STAGE_PRODUCTION,
                    'title' => strip_tags($titles->first() ?? 'Untitled Migration'),
                    'abstract' => $abstracts->first() ?? null,
                    'references' => $rawCitations, // Map extracted citations
                    'created_at' => $lSub->date_submitted,
                    'seq_id' => (int)$lSub->submission_id, // Map OJS submission_id to seq_id
                ]
            );

            LegacyMapping::setMapping('submissions', $lSub->submission_id, $submission->id);
            
            Publication::updateOrCreate(
                [
                    'submission_id' => $submission->id,
                    'version' => 1,
                ],
                [
                    'title' => $titles->first(),
                    'abstract' => $abstracts->first(),
                    'references' => $rawCitations, // Map extracted citations to Publication
                    'status' => Publication::STATUS_PUBLISHED,
                    'date_published' => $lSub->date_submitted,
                ]
            );
        }
    }

    /**
     * Migrate Authors
     */
    public function migrateAuthors()
    {
        $legacyAuthors = DB::connection('legacy')->table('authors')->get();

        foreach ($legacyAuthors as $lAuthor) {
            $lPublication = DB::connection('legacy')->table('publications')
                ->where('publication_id', $lAuthor->publication_id)
                ->first();
            
            if (!$lPublication) continue;

            $newSubmissionId = LegacyMapping::getMapping('submissions', $lPublication->submission_id);
            if (!$newSubmissionId) continue;

            $settings = DB::connection('legacy')->table('author_settings')
                ->where('author_id', $lAuthor->author_id)
                ->get();
            
            $givenName = $settings->where('setting_name', 'givenName')->first()?->setting_value ?? '';
            $familyName = $settings->where('setting_name', 'familyName')->first()?->setting_value ?? '';
            $affiliation = $settings->where('setting_name', 'affiliation')->first()?->setting_value ?? null;

            // Self-healing: check if author with same email already exists in this submission
            $existingAuthor = \App\Models\SubmissionAuthor::where('submission_id', $newSubmissionId)
                ->where('email', $lAuthor->email)
                ->first();

            $author = SubmissionAuthor::updateOrCreate(
                ['id' => $existingAuthor?->id ?? Str::uuid()->toString()],
                [
                    'submission_id' => $newSubmissionId,
                    'email' => $lAuthor->email,
                    'given_name' => $givenName,
                    'family_name' => $familyName,
                    'name' => trim($givenName . ' ' . $familyName),
                    'first_name' => $givenName,
                    'last_name' => $familyName,
                    'affiliation' => $affiliation,
                    'is_corresponding' => (bool)$lAuthor->include_in_browse,
                    'sort_order' => (int)$lAuthor->seq,
                ]
            );

            // CRITICAL: Set the mapping so dashboard shows "Synced"
            LegacyMapping::setMapping('authors', $lAuthor->author_id, $author->id);
        }
    }

    /**
     * Migrate Metrics
     */
    public function migrateMetrics()
    {
        $legacyMetrics = DB::connection('legacy')->table('metrics')->get();

        foreach ($legacyMetrics as $lMetric) {
            $legacySubId = $lMetric->submission_id ?? null;
            $type = ArticleMetric::TYPE_VIEW;

            if ($lMetric->assoc_type == 515 || $lMetric->assoc_type == 516) {
                $type = ArticleMetric::TYPE_DOWNLOAD;
            }

            if (!$legacySubId) {
                if ($lMetric->assoc_type == 259 || $lMetric->assoc_type == 1048585) {
                    $legacySubId = $lMetric->pkid ?? $lMetric->assoc_id;
                }
            }

            if (!$legacySubId) continue;
            $newSubmissionId = LegacyMapping::getMapping('submissions', $legacySubId);
            if (!$newSubmissionId) continue;

            for ($i = 0; $i < $lMetric->metric; $i++) {
                ArticleMetric::create([
                    'submission_id' => $newSubmissionId,
                    'type' => $type,
                    'ip_address' => '127.0.0.1',
                    'date' => $lMetric->day ? Carbon::createFromFormat('Ymd', $lMetric->day)->format('Y-m-d') : now(),
                ]);
            }
        }
    }

    /**
     * Migrate Galleys & Physical Files
     */
    public function migrateGalleys(?string $baseUrl = null)
    {
        if (!$baseUrl) {
            throw new \Exception("Base URL legacy belum diatur. Cloud download tidak dapat berjalan.");
        }

        $baseUrl = rtrim($baseUrl, '/');

        // Detect correct table (OJS 3.3 vs 3.2-)
        $galleyTable = \Illuminate\Support\Facades\Schema::connection('legacy')->hasTable('publication_galleys') 
            ? 'publication_galleys' 
            : 'submission_galleys';

        $legacyGalleys = DB::connection('legacy')->table($galleyTable)
            ->join('submissions', "$galleyTable.submission_id", '=', 'submissions.submission_id')
            ->join('journals', 'submissions.context_id', '=', 'journals.journal_id')
            ->select(
                "$galleyTable.*", 
                'submissions.submission_id as legacy_submission_id',
                'journals.path as journal_path'
            )
            ->get();

        foreach ($legacyGalleys as $lGalley) {
            $newSubmissionId = LegacyMapping::getMapping('submissions', $lGalley->legacy_submission_id);
            if (!$newSubmissionId) continue;

            $submission = Submission::with('journal')->find($newSubmissionId);
            if (!$submission) continue;

            // 1. Resolve File Meta from Legacy
            $lFile = DB::connection('legacy')->table('submission_files')
                ->where('submission_file_id', $lGalley->submission_file_id ?? ($lGalley->file_id ?? null))
                ->first();

            if (!$lFile && empty($lGalley->url_remote)) continue;

            $filename = $lFile->original_file_name ?? "galley_{$lGalley->galley_id}.pdf";
            $galleyId = $lGalley->galley_id ?? $lGalley->publication_galley_id;

            // 2. Construct Target Directory (Matching Public URL Style)
            // Path: public/journals/{slug}/articles/{seq_id}/galleys/{galley_seq_id}/
            $targetDir = "journals/{$submission->journal->slug}/articles/{$submission->seq_id}/galleys/{$galleyId}";
            $targetPath = "{$targetDir}/{$filename}";

            // 3. Cloud Download
            if (empty($lGalley->url_remote)) {
                $downloadUrl = "{$baseUrl}/index.php/{$lGalley->journal_path}/article/download/{$lGalley->legacy_submission_id}/{$galleyId}";
                
                try {
                    $response = \Illuminate\Support\Facades\Http::timeout(30)->get($downloadUrl);
                    if ($response->successful()) {
                        \Illuminate\Support\Facades\Storage::disk('public')->put($targetPath, $response->body());
                    }
                } catch (\Exception $e) {
                    // Log error but continue
                    \Illuminate\Support\Facades\Log::error("Gagal mendownload galley {$galleyId}: " . $e->getMessage());
                    continue;
                }
            } else {
                // Remote Galley
                $targetPath = $lGalley->url_remote;
            }

            // 4. Create SubmissionFile Record
            $subFile = \App\Models\SubmissionFile::updateOrCreate(
                [
                    'submission_id' => $submission->id,
                    'file_name' => $filename,
                ],
                [
                    'file_path' => $targetPath,
                    'file_type' => \App\Models\SubmissionFile::TYPE_GALLEY,
                    'mime_type' => \Illuminate\Support\Arr::get([
                        'pdf' => 'application/pdf',
                        'html' => 'text/html',
                    ], strtolower(pathinfo($filename, PATHINFO_EXTENSION)), 'application/octet-stream'),
                    'file_size' => $lFile->file_size ?? 0,
                    'stage' => \App\Models\SubmissionFile::STAGE_PRODUCTION,
                    'uploaded_by' => User::first()->id,
                ]
            );

            // 5. Create PublicationGalley Record
            \App\Models\PublicationGalley::updateOrCreate(
                [
                    'submission_id' => $submission->id,
                    'label' => $lGalley->label,
                ],
                [
                    'file_id' => $subFile->id,
                    'locale' => $lGalley->locale ?? 'en',
                    'seq' => (int)$lGalley->seq,
                    'seq_id' => (int)$galleyId, // Map legacy ID to seq_id for friendly URL
                ]
            );

            LegacyMapping::setMapping('galleys', $galleyId, $submission->id);
        }
    }
}
