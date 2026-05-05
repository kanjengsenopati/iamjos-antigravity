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
    protected $parser;
    protected $sqlFile;

    public function __construct(SqlDumpParserService $parser)
    {
        $this->parser = $parser;
    }

    /**
     * Set the SQL source file
     */
    public function setSqlSource(string $path)
    {
        $this->sqlFile = $path;
        $this->parser->setFile($path);
    }

    /**
     * Get Migration Matrix Statistics from SQL file
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
            $legacyTable = $config['legacy_table'];

            $stats[$key] = [
                'legacy_count' => 'File Uploaded',
                'migrated_count' => $config['new_model']::count(),
                'mapping_count' => LegacyMapping::where('legacy_table', $legacyTable)->count(),
            ];
        }

        $stats['metrics_views'] = ['legacy_count' => '-', 'migrated_count' => ArticleMetric::where('type', ArticleMetric::TYPE_VIEW)->count()];
        $stats['metrics_downloads'] = ['legacy_count' => '-', 'migrated_count' => ArticleMetric::where('type', ArticleMetric::TYPE_DOWNLOAD)->count()];

        return $stats;
    }

    /**
     * Get preview of the data inside the SQL dump before migrating.
     */
    public function getSqlPreviewStats(): array
    {
        if (!$this->parser) return [];
        
        $preview = [];
        $journals = $this->getLegacyRows('journals');
        $journalSettings = $this->getLegacyRows('journal_settings');
        $sections = $this->getLegacyRows('sections');
        $issues = $this->getLegacyRows('issues');
        $submissions = $this->getLegacyRows('submissions');
        
        $journalSettingsMap = collect($journalSettings)->map(fn($r) => $this->mapRow('journal_settings', $r));
        $sectionsMap = collect($sections)->map(fn($r) => $this->mapRow('sections', $r));
        $issuesMap = collect($issues)->map(fn($r) => $this->mapRow('issues', $r));
        $submissionsMap = collect($submissions)->map(fn($r) => $this->mapRow('submissions', $r));
        
        foreach ($journals as $row) {
            $lJournal = $this->mapRow('journals', $row);
            $jId = $lJournal->journal_id;
            
            $name = $journalSettingsMap->where('journal_id', $jId)->where('setting_name', 'name')->first()?->setting_value ?? 'Unknown Journal';
            
            $preview[] = [
                'id' => $jId,
                'name' => $name,
                'path' => $lJournal->path,
                'sections_count' => $sectionsMap->where('journal_id', $jId)->count(),
                'issues_count' => $issuesMap->where('journal_id', $jId)->count(),
                'articles_count' => $submissionsMap->where('context_id', $jId)->count(),
            ];
        }
        
        return $preview;
    }

    /**
     * Helper to get rows from a table as an array
     */
    protected function getLegacyRows(string $tableName): array
    {
        $rows = [];
        foreach ($this->parser->getTableData($tableName) as $row) {
            $rows[] = $row;
        }
        return $rows;
    }

    /**
     * Helper to map numeric row to associative array
     * This is the TRICKY part: we need to know the column order in the dump.
     * Standard OJS dumps have a specific order, but it might vary.
     * We'll assume the order from standard OJS schemas.
     */
    protected function mapRow(string $table, array $row)
    {
        $columns = [];
        switch ($table) {
            case 'journals':
                $columns = ['journal_id', 'path', 'seq', 'primary_locale', 'enabled'];
                break;
            case 'journal_settings':
                $columns = ['journal_id', 'locale', 'setting_name', 'setting_value', 'setting_type'];
                break;
            case 'sections':
                $columns = ['section_id', 'journal_id', 'review_form_id', 'seq', 'editor_restricted', 'meta_indexed', 'meta_reviewed', 'abstracts_not_required', 'hide_title', 'hide_author', 'hide_about', 'disable_comments', 'abstract_word_count', 'abbrev'];
                break;
            case 'section_settings':
                $columns = ['section_id', 'locale', 'setting_name', 'setting_value', 'setting_type'];
                break;
            case 'issues':
                $columns = ['issue_id', 'journal_id', 'volume', 'number', 'year', 'published', 'current', 'date_published', 'date_notified', 'last_modified', 'access_status', 'open_access_date', 'show_volume', 'show_number', 'show_year', 'show_title', 'style_file_name', 'original_style_file_name'];
                break;
            case 'submissions':
                $columns = ['submission_id', 'locale', 'context_id', 'section_id', 'language', 'date_submitted', 'last_modified', 'date_status_modified', 'status', 'submission_progress', 'current_publication_id', 'pages'];
                break;
            case 'publications':
                $columns = ['publication_id', 'submission_id', 'access_status', 'date_published', 'last_modified', 'section_id', 'seq', 'status', 'url_path', 'version', 'issue_id'];
                break;
            case 'publication_settings':
                $columns = ['publication_id', 'locale', 'setting_name', 'setting_value', 'setting_type'];
                break;
            case 'authors':
                $columns = ['author_id', 'publication_id', 'email', 'include_in_browse', 'seq', 'user_group_id'];
                break;
            case 'author_settings':
                $columns = ['author_id', 'locale', 'setting_name', 'setting_value', 'setting_type'];
                break;
            case 'metrics':
                $columns = ['load_id', 'context_id', 'announcement_id', 'issue_id', 'submission_id', 'representation_id', 'submission_file_id', 'assoc_type', 'assoc_id', 'day', 'month', 'metric', 'metric_type'];
                break;
            case 'publication_galleys':
            case 'submission_galleys':
                $columns = ['galley_id', 'submission_id', 'locale', 'label', 'file_id', 'seq', 'remote_url'];
                break;
            case 'submission_files':
                $columns = ['submission_file_id', 'source_submission_file_id', 'submission_id', 'file_stage', 'file_id', 'viewable', 'created_at', 'updated_at'];
                break;
        }

        $result = new \stdClass();
        foreach ($columns as $index => $col) {
            if (isset($row[$index])) {
                $result->$col = $row[$index];
            }
        }
        return $result;
    }

    /**
     * Migrate Journals
     */
    public function migrateJournals()
    {
        $settingsRows = collect($this->getLegacyRows('journal_settings'))
            ->map(fn($r) => $this->mapRow('journal_settings', $r));

        foreach ($this->parser->getTableData('journals') as $row) {
            $lJournal = $this->mapRow('journals', $row);

            // Guard: skip if path is null or empty
            if (empty($lJournal->path ?? null)) continue;

            $settings = $settingsRows->where('journal_id', $lJournal->journal_id);

            $names         = $settings->where('setting_name', 'name')->pluck('setting_value', 'locale');
            $descriptions  = $settings->where('setting_name', 'description')->pluck('setting_value', 'locale');
            $abbreviations = $settings->where('setting_name', 'acronym')->pluck('setting_value', 'locale');
            $issnPrint     = $settings->where('setting_name', 'printIssn')->value('setting_value');
            $issnOnline    = $settings->where('setting_name', 'onlineIssn')->value('setting_value');
            $publisher     = $settings->where('setting_name', 'publisherInstitution')->value('setting_value');

            $journal = Journal::updateOrCreate(
                ['path' => $lJournal->path],      // ✅ correct column name
                [
                    'slug'         => $lJournal->path,  // ✅ slug = path (used by getRouteKeyName)
                    'name'         => strip_tags($names->first() ?? 'Migrated Journal'),
                    'abbreviation' => $abbreviations->first() ?? strtoupper(substr($lJournal->path, 0, 6)),
                    'description'  => strip_tags($descriptions->first() ?? null),
                    'publisher'    => $publisher ?? null,
                    'issn_print'   => $issnPrint ?? null,
                    'issn_online'  => $issnOnline ?? null,
                    'enabled'      => (bool)($lJournal->enabled ?? true),
                    'visible'      => true,
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
        $settingsRows = collect($this->getLegacyRows('section_settings'))
            ->map(fn($r) => $this->mapRow('section_settings', $r));

        foreach ($this->parser->getTableData('sections') as $row) {
            $lSection = $this->mapRow('sections', $row);

            $newJournalId = LegacyMapping::getMapping('journals', $lSection->journal_id);
            if (!$newJournalId) continue;

            $settings = $settingsRows->where('section_id', $lSection->section_id);
            $titles = $settings->where('setting_name', 'title')->pluck('setting_value', 'locale');

            $section = Section::updateOrCreate(
                [
                    'journal_id' => $newJournalId,
                    'abbreviation' => $lSection->abbrev ?? 'SEC',
                ],
                [
                    'name' => $titles->first() ?? 'General Section',
                    'sort_order' => (int)$lSection->seq,
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
        $settingsRows = collect($this->getLegacyRows('issue_settings'))
            ->map(fn($r) => $this->mapRow('issue_settings', $r));

        foreach ($this->parser->getTableData('issues') as $row) {
            $lIssue = $this->mapRow('issues', $row);

            $newJournalId = LegacyMapping::getMapping('journals', $lIssue->journal_id);
            if (!$newJournalId) continue;

            $settings = $settingsRows->where('issue_id', $lIssue->issue_id);
            $titles = $settings->where('setting_name', 'title')->pluck('setting_value', 'locale');
            $descriptions = $settings->where('setting_name', 'description')->pluck('setting_value', 'locale');

            $issue = Issue::updateOrCreate(
                [
                    // OJS issue_id is globally unique across all journals — use as stable primary key
                    'seq_id' => (int)$lIssue->issue_id,
                ],
                [
                    'journal_id'   => $newJournalId,
                    'volume'       => (int)($lIssue->volume ?? 1),
                    'number'       => (int)($lIssue->number ?? 1),
                    'year'         => (int)($lIssue->year ?? date('Y')),
                    'title'        => $titles->first() ?? "Vol. {$lIssue->volume} No. {$lIssue->number} ({$lIssue->year})",
                    'description'  => $descriptions->first() ?? null,
                    'is_published' => (bool)$lIssue->published,
                    'published_at' => $lIssue->date_published,
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
        $publicationRows = collect($this->getLegacyRows('publications'))
            ->map(fn($r) => $this->mapRow('publications', $r));
        
        $metadataRows = collect($this->getLegacyRows('publication_settings'))
            ->map(fn($r) => $this->mapRow('publication_settings', $r));

        foreach ($this->parser->getTableData('submissions') as $row) {
            $lSub = $this->mapRow('submissions', $row);
            
            $newJournalId = LegacyMapping::getMapping('journals', $lSub->context_id);
            if (!$newJournalId) continue;

            $newSectionId = LegacyMapping::getMapping('sections', $lSub->section_id);
            
            // Fallback for orphaned submissions to prevent NOT NULL constraint violation
            if (!$newSectionId) {
                $fallbackSection = \App\Models\Section::where('journal_id', $newJournalId)->orderBy('sort_order')->first();
                if (!$fallbackSection) {
                    $fallbackSection = \App\Models\Section::create([
                        'journal_id' => $newJournalId,
                        'name' => 'Uncategorized (Migrated)',
                        'abbreviation' => 'UNC',
                    ]);
                }
                $newSectionId = $fallbackSection->id;
            }
            
            $lPub = $publicationRows->where('publication_id', $lSub->current_publication_id)->first();
            $newIssueId = $lPub ? LegacyMapping::getMapping('issues', $lPub->issue_id) : null;

            $metadata = $metadataRows->where('publication_id', $lSub->current_publication_id);

            $titles = $metadata->where('setting_name', 'title')->pluck('setting_value', 'locale');
            $abstracts = $metadata->where('setting_name', 'abstract')->pluck('setting_value', 'locale');

            // Citations extraction
            $rawCitations = null;
            $citationsSetting = $metadata->whereIn('setting_name', ['citations', 'references'])->first();
            
            if ($citationsSetting && !empty($citationsSetting->setting_value)) {
                $rawCitations = $citationsSetting->setting_value;
            }
            
            $rawCitations = $rawCitations ? strip_tags($rawCitations) : null;

            // Use OJS submission_id as stable unique key (seq_id) for idempotent re-runs
            $submission = Submission::updateOrCreate(
                ['seq_id' => (int)$lSub->submission_id],
                [
                    'journal_id' => $newJournalId,
                    'user_id'    => User::first()->id,
                    'section_id' => $newSectionId,
                    'issue_id'   => $newIssueId,
                    'status'     => Submission::STATUS_PUBLISHED,
                    'stage'      => Submission::STAGE_PRODUCTION,
                    'title'      => strip_tags($titles->first() ?? 'Untitled Migration'),
                    'abstract'   => $abstracts->first() ?? null,
                    'references' => $rawCitations,
                    'created_at' => $lSub->date_submitted,
                ]
            );

            LegacyMapping::setMapping('submissions', $lSub->submission_id, $submission->id);
            
            Publication::updateOrCreate(
                [
                    'submission_id' => $submission->id,
                    'version' => 1,
                ],
                [
                    'title' => strip_tags($titles->first() ?? 'Untitled Migration'),
                    'abstract' => $abstracts->first(),
                    'references' => $rawCitations,
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
        $publicationRows = collect($this->getLegacyRows('publications'))
            ->map(fn($r) => $this->mapRow('publications', $r));
            
        $settingsRows = collect($this->getLegacyRows('author_settings'))
            ->map(fn($r) => $this->mapRow('author_settings', $r));

        foreach ($this->parser->getTableData('authors') as $row) {
            $lAuthor = $this->mapRow('authors', $row);
            
            $lPublication = $publicationRows->where('publication_id', $lAuthor->publication_id)->first();
            if (!$lPublication) continue;

            $newSubmissionId = LegacyMapping::getMapping('submissions', $lPublication->submission_id);
            if (!$newSubmissionId) continue;

            $settings = $settingsRows->where('author_id', $lAuthor->author_id);
            
            $givenName  = $settings->where('setting_name', 'givenName')->first()?->setting_value ?? '';
            $familyName = $settings->where('setting_name', 'familyName')->first()?->setting_value ?? '';
            $affiliation = $settings->where('setting_name', 'affiliation')->first()?->setting_value ?? null;

            $author = SubmissionAuthor::updateOrCreate(
                [
                    // Stable key: same author in same submission identified by email+sort_order
                    'submission_id' => $newSubmissionId,
                    'email'         => $lAuthor->email ?: "author_{$lAuthor->author_id}@migrated.local",
                    'sort_order'    => (int)$lAuthor->seq,
                ],
                [
                    'given_name'       => $givenName ?: 'Author',
                    'family_name'      => $familyName,
                    'name'             => trim(($givenName ?: 'Author') . ' ' . $familyName),
                    'first_name'       => $givenName ?: 'Author',
                    'last_name'        => $familyName,
                    'affiliation'      => $affiliation,
                    'is_corresponding' => (bool)$lAuthor->include_in_browse,
                ]
            );

            LegacyMapping::setMapping('authors', $lAuthor->author_id, $author->id);
        }
    }

    /**
     * Migrate Metrics
     */
    public function migrateMetrics()
    {
        foreach ($this->parser->getTableData('metrics') as $row) {
            $lMetric = $this->mapRow('metrics', $row);
            $legacySubId = $lMetric->submission_id ?? null;
            $type = ArticleMetric::TYPE_VIEW;

            if ($lMetric->assoc_type == 515 || $lMetric->assoc_type == 516) {
                $type = ArticleMetric::TYPE_DOWNLOAD;
            }

            if (!$legacySubId && ($lMetric->assoc_type == 259 || $lMetric->assoc_type == 1048585)) {
                $legacySubId = $lMetric->pkid ?? $lMetric->assoc_id;
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

        $submissionsRows = collect($this->getLegacyRows('submissions'))->map(fn($r) => $this->mapRow('submissions', $r));
        $journalsRows = collect($this->getLegacyRows('journals'))->map(fn($r) => $this->mapRow('journals', $r));
        $filesRows = collect($this->getLegacyRows('submission_files'))->map(fn($r) => $this->mapRow('submission_files', $r));

        $galleyTable = 'submission_galleys'; // Default to older OJS
        // We'll check if publication_galleys has data
        $galleyData = $this->parser->getTableData('publication_galleys');
        if (!$galleyData->valid()) {
            $galleyData = $this->parser->getTableData('submission_galleys');
        } else {
            $galleyTable = 'publication_galleys';
        }

        foreach ($galleyData as $row) {
            $lGalley = $this->mapRow($galleyTable, $row);
            $newSubmissionId = LegacyMapping::getMapping('submissions', $lGalley->submission_id);
            if (!$newSubmissionId) continue;

            $submission = Submission::with('journal')->find($newSubmissionId);
            if (!$submission) continue;

            $lSub = $submissionsRows->where('submission_id', $lGalley->submission_id)->first();
            $lJournal = $lSub ? $journalsRows->where('journal_id', $lSub->context_id)->first() : null;
            $journalPath = $lJournal ? $lJournal->path : $submission->journal->slug;

            $lFile = $filesRows->where('submission_file_id', $lGalley->file_id)->first();
            if (!$lFile && empty($lGalley->remote_url)) continue;

            $filename = "galley_{$lGalley->galley_id}.pdf";
            $targetDir = "journals/{$submission->journal->slug}/articles/{$submission->seq_id}/galleys/{$lGalley->galley_id}";
            $targetPath = "{$targetDir}/{$filename}";

            if ($baseUrl) {
                // $baseUrl now used as LOCAL PATH to OJS files_dir
                $filesDir = base_path($baseUrl);
                $found = false;

                // Pattern 1: {files_path}/{galley_id}.pdf
                $path1 = $filesDir . "/{$lGalley->galley_id}.pdf";
                if (file_exists($path1)) {
                    \Illuminate\Support\Facades\Storage::disk('public')->put($targetPath, file_get_contents($path1));
                    $found = true;
                } 

                // Pattern 2: Recursive search by submission_file_id
                if (!$found) {
                    $searchId = $lGalley->file_id;
                    $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($filesDir));
                    foreach ($iterator as $file) {
                        if ($file->isFile() && str_starts_with($file->getFilename(), $searchId . '-')) {
                            \Illuminate\Support\Facades\Storage::disk('public')->put($targetPath, file_get_contents($file->getPathname()));
                            $found = true;
                            break;
                        }
                    }
                }

                if (!$found) {
                    \Illuminate\Support\Facades\Log::warning("Gagal menemukan file galley untuk ID {$lGalley->galley_id} di path: {$baseUrl}");
                    continue;
                }
            } else {
                \Illuminate\Support\Facades\Log::error("Path file OJS belum diset. Lewati migrasi galley.");
                continue;
            }

            $subFile = \App\Models\SubmissionFile::updateOrCreate(
                [
                    'submission_id' => $submission->id,
                    'file_name' => $filename,
                ],
                [
                    'file_path' => $targetPath,
                    'file_type' => \App\Models\SubmissionFile::TYPE_GALLEY,
                    'mime_type' => 'application/pdf',
                    'file_size' => 0,
                    'stage' => \App\Models\SubmissionFile::STAGE_PRODUCTION,
                    'uploaded_by' => User::first()->id,
                ]
            );

            \App\Models\PublicationGalley::updateOrCreate(
                [
                    'submission_id' => $submission->id,
                    'label' => $lGalley->label,
                ],
                [
                    'file_id' => $subFile->id,
                    'locale' => $lGalley->locale ?? 'en',
                    'seq' => (int)$lGalley->seq,
                    'seq_id' => (int)$lGalley->galley_id,
                ]
            );

            LegacyMapping::setMapping('galleys', $lGalley->galley_id, $submission->id);
        }
    }
}
