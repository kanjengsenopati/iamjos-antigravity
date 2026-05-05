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
            'users'       => ['legacy_table' => 'users', 'new_model' => User::class],
            'journals'    => ['legacy_table' => 'journals', 'new_model' => Journal::class],
            'sections'    => ['legacy_table' => 'sections', 'new_model' => Section::class],
            'issues'      => ['legacy_table' => 'issues', 'new_model' => Issue::class],
            'submissions' => ['legacy_table' => 'submissions', 'new_model' => Submission::class],
            'authors'     => ['legacy_table' => 'authors', 'new_model' => SubmissionAuthor::class],
            'reviews'     => ['legacy_table' => 'review_assignments', 'new_model' => \App\Models\ReviewAssignment::class],
            'discussions' => ['legacy_table' => 'queries', 'new_model' => \App\Models\Discussion::class],
            'logs'        => ['legacy_table' => 'event_log', 'new_model' => \App\Models\SubmissionLog::class],
            'galleys'     => ['legacy_table' => 'galleys', 'new_model' => \App\Models\PublicationGalley::class],
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
            case 'users':
                $columns = ['user_id', 'username', 'password', 'email', 'locales', 'date_last_email', 'date_registered', 'date_validated', 'date_last_login', 'must_change_password', 'disabled', 'disabled_reason', 'auth_id', 'auth_str', 'phone', 'mailing_address', 'billing_address', 'inline_help'];
                break;
            case 'user_settings':
                $columns = ['user_id', 'locale', 'setting_name', 'setting_value', 'setting_type'];
                break;
            case 'user_groups':
                $columns = ['user_group_id', 'context_id', 'role_id', 'is_default', 'show_title', 'permit_self_registration'];
                break;
            case 'user_user_groups':
                $columns = ['user_group_id', 'user_id'];
                break;
            case 'issues':
                $columns = ['issue_id', 'journal_id', 'volume', 'number', 'year', 'published', 'current', 'date_published', 'date_notified', 'last_modified', 'access_status', 'open_access_date', 'show_volume', 'show_number', 'show_year', 'show_title', 'style_file_name', 'original_style_file_name'];
                break;
            case 'submissions':
                $columns = ['submission_id', 'locale', 'context_id', 'section_id', 'language', 'date_submitted', 'last_modified', 'date_status_modified', 'status', 'submission_progress', 'current_publication_id', 'pages', 'stage_id'];
                break;
            case 'stage_assignments':
                $columns = ['stage_assignment_id', 'submission_id', 'user_group_id', 'user_id', 'date_assigned', 'stage_id', 'recommend_only'];
                break;
            case 'review_rounds':
                $columns = ['review_round_id', 'submission_id', 'stage_id', 'round', 'review_revision', 'status'];
                break;
            case 'review_assignments':
                $columns = ['review_id', 'submission_id', 'reviewer_id', 'stage_id', 'review_method', 'round', 'step', 'recommendation', 'declined', 'cancelled', 'date_assigned', 'date_notified', 'date_completed', 'date_due', 'date_response_due', 'quality'];
                break;
            case 'queries':
                $columns = ['query_id', 'assoc_type', 'assoc_id', 'stage_id', 'sequence', 'is_closed'];
                break;
            case 'query_participants':
                $columns = ['query_id', 'user_id'];
                break;
            case 'notes':
                $columns = ['note_id', 'user_id', 'date_created', 'date_modified', 'contents', 'title', 'assoc_type', 'assoc_id'];
                break;
            case 'event_log':
                $columns = ['log_id', 'assoc_type', 'assoc_id', 'user_id', 'date_logged', 'event_type', 'message', 'is_translated'];
                break;
            case 'email_log':
                $columns = ['log_id', 'assoc_type', 'assoc_id', 'sender_id', 'date_sent', 'event_type', 'from_address', 'recipients', 'cc_receivers', 'bcc_receivers', 'subject', 'body'];
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
     * Migrate Users & Roles
     */
    public function migrateUsers()
    {
        $settingsRows = collect($this->getLegacyRows('user_settings'))
            ->map(fn($r) => $this->mapRow('user_settings', $r));

        $userUserGroups = collect($this->getLegacyRows('user_user_groups'))
            ->map(fn($r) => $this->mapRow('user_user_groups', $r));
            
        $userGroups = collect($this->getLegacyRows('user_groups'))
            ->map(fn($r) => $this->mapRow('user_groups', $r));

        foreach ($this->parser->getTableData('users') as $row) {
            $lUser = $this->mapRow('users', $row);

            $settings = $settingsRows->where('user_id', $lUser->user_id);
            $givenName = $settings->where('setting_name', 'givenName')->first()?->setting_value ?? $lUser->username;
            $familyName = $settings->where('setting_name', 'familyName')->first()?->setting_value ?? '';
            $affiliation = $settings->where('setting_name', 'affiliation')->first()?->setting_value ?? null;
            $country = $settings->where('setting_name', 'country')->first()?->setting_value ?? null;
            $orcid = $settings->where('setting_name', 'orcid')->first()?->setting_value ?? null;

            $fullName = trim($givenName . ' ' . $familyName);

            // Set default password
            $password = bcrypt('IamJOS2026!');

            $user = User::updateOrCreate(
                ['username' => $lUser->username],
                [
                    'name' => $fullName ?: $lUser->username,
                    'given_name' => $givenName,
                    'family_name' => $familyName,
                    'email' => $lUser->email ?? ($lUser->username . '@example.com'),
                    'password' => $password,
                    'affiliation' => $affiliation,
                    'country' => $country,
                    'orcid_id' => $orcid,
                    'date_registered' => $lUser->date_registered,
                    'date_last_login' => $lUser->date_last_login,
                ]
            );

            LegacyMapping::setMapping('users', $lUser->user_id, $user->id);

            // Now map roles
            $uGroups = $userUserGroups->where('user_id', $lUser->user_id);
            foreach ($uGroups as $ug) {
                $group = $userGroups->where('user_group_id', $ug->user_group_id)->first();
                if (!$group) continue;

                $newJournalId = LegacyMapping::getMapping('journals', $group->context_id);
                if (!$newJournalId) continue;

                $roleName = match((int)$group->role_id) {
                    1 => \App\Models\Role::ROLE_SUPERADMIN,
                    16 => \App\Models\Role::ROLE_MANAGER,
                    17 => \App\Models\Role::ROLE_EDITOR,
                    18 => \App\Models\Role::ROLE_SECTION_EDITOR,
                    19 => \App\Models\Role::ROLE_PRODUCTION,
                    4096 => \App\Models\Role::ROLE_REVIEWER,
                    65536 => \App\Models\Role::ROLE_AUTHOR,
                    1048576 => \App\Models\Role::ROLE_READER,
                    default => null
                };

                if ($roleName) {
                    $iamjosRole = \App\Models\Role::where('name', $roleName)->where('journal_id', $newJournalId)->first();
                    if ($iamjosRole) {
                        \App\Models\JournalUserRole::updateOrCreate([
                            'journal_id' => $newJournalId,
                            'user_id' => $user->id,
                            'role_id' => $iamjosRole->id
                        ]);
                    }
                }
            }
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

            // Determine IamJOS Stage and Status
            $lStageId = (int)($lSub->stage_id ?? 1);
            $iamjosStageId = match($lStageId) {
                1 => 1, // Submission
                2, 3 => 2, // Review (Internal/External)
                4 => 3, // Copyediting
                5 => 4, // Production
                default => 1
            };

            $iamjosStage = match($iamjosStageId) {
                1 => Submission::STAGE_SUBMISSION,
                2 => Submission::STAGE_REVIEW,
                3 => Submission::STAGE_COPYEDITING,
                4 => Submission::STAGE_PRODUCTION,
                default => Submission::STAGE_SUBMISSION
            };

            if ((int)$lSub->status === 3) {
                $iamjosStatus = Submission::STATUS_PUBLISHED;
                $iamjosStageId = 4;
                $iamjosStage = Submission::STAGE_PRODUCTION;
            } elseif ((int)$lSub->status === 4) {
                $iamjosStatus = Submission::STATUS_REJECTED;
            } elseif ((int)$lSub->status === 0 && (int)($lSub->submission_progress ?? 0) > 0) {
                $iamjosStatus = Submission::STATUS_DRAFT;
            } else {
                $iamjosStatus = match($iamjosStageId) {
                    1 => Submission::STATUS_SUBMITTED,
                    2 => Submission::STATUS_IN_REVIEW,
                    3 => Submission::STATUS_QUEUED_FOR_COPYEDITING,
                    4 => Submission::STATUS_IN_PRODUCTION,
                    default => Submission::STATUS_SUBMITTED
                };
            }

            // Find submitting user (fallback to SuperAdmin if not found)
            // Typically the user who submitted is in stage_assignments or is the first author
            $submittingUserId = User::first()->id ?? null;
            $authorGroup = \App\Models\LegacyMapping::where('legacy_table', 'authors')
                ->where('legacy_id', 'LIKE', $lSub->submission_id . '-%')
                ->first();
                
            if ($authorGroup) {
                // Not perfectly accurate but better than all being SuperAdmin. 
                // A more precise map requires stage_assignments which we'll process later, 
                // but we need a user_id NOW for the foreign key.
                $author = \App\Models\SubmissionAuthor::find($authorGroup->new_uuid);
                if ($author && $author->user_id) {
                    $submittingUserId = $author->user_id;
                }
            }

            // Use OJS submission_id as stable unique key (seq_id) for idempotent re-runs
            $submission = Submission::updateOrCreate(
                ['seq_id' => (int)$lSub->submission_id],
                [
                    'journal_id' => $newJournalId,
                    'user_id'    => $submittingUserId,
                    'section_id' => $newSectionId,
                    'issue_id'   => $newIssueId,
                    'status'     => $iamjosStatus,
                    'stage'      => $iamjosStage,
                    'stage_id'   => $iamjosStageId,
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
     * Migrate Editorial & Review Workflow
     */
    public function migrateReviews()
    {
        // 1. Migrate Stage Assignments (Editorial Assignments)
        foreach ($this->parser->getTableData('stage_assignments') as $row) {
            $lStage = $this->mapRow('stage_assignments', $row);
            
            $newSubmissionId = LegacyMapping::getMapping('submissions', $lStage->submission_id);
            $newUserId = LegacyMapping::getMapping('users', $lStage->user_id);
            
            if (!$newSubmissionId || !$newUserId) continue;

            $submission = Submission::find($newSubmissionId);
            if (!$submission) continue;

            // If stage is submission (1) and role is author (65536) then update the submission's user_id
            $groupRow = collect($this->getLegacyRows('user_groups'))->where('user_group_id', $lStage->user_group_id)->first();
            if ($groupRow && $groupRow[2] == 65536 && $lStage->stage_id == 1) { // 2nd index is role_id
                $submission->update(['user_id' => $newUserId]);
            } else {
                \App\Models\EditorialAssignment::updateOrCreate(
                    [
                        'submission_id' => $newSubmissionId,
                        'user_id' => $newUserId,
                    ],
                    [
                        'is_active' => true,
                        // stage_assignments in OJS doesn't directly map nicely to IamJOS specific fields,
                        // but creating the assignment grants them access
                    ]
                );
            }
        }

        // 2. Migrate Review Rounds
        foreach ($this->parser->getTableData('review_rounds') as $row) {
            $lRound = $this->mapRow('review_rounds', $row);
            
            $newSubmissionId = LegacyMapping::getMapping('submissions', $lRound->submission_id);
            if (!$newSubmissionId) continue;

            $round = \App\Models\ReviewRound::updateOrCreate(
                [
                    'submission_id' => $newSubmissionId,
                    'round' => (int)$lRound->round,
                ],
                [
                    'status' => \App\Models\ReviewRound::STATUS_COMPLETED, // simplify
                    'started_at' => now(), // fallback since OJS doesn't store start date in review_rounds table
                ]
            );
            
            LegacyMapping::setMapping('review_rounds', $lRound->review_round_id, $round->id);
        }

        // 3. Migrate Review Assignments
        foreach ($this->parser->getTableData('review_assignments') as $row) {
            $lRev = $this->mapRow('review_assignments', $row);
            
            $newSubmissionId = LegacyMapping::getMapping('submissions', $lRev->submission_id);
            $newReviewerId = LegacyMapping::getMapping('users', $lRev->reviewer_id);
            
            if (!$newSubmissionId || !$newReviewerId) continue;

            // Find matching review round
            $round = \App\Models\ReviewRound::where('submission_id', $newSubmissionId)
                        ->where('round', (int)$lRev->round)
                        ->first();
            
            $roundId = $round ? $round->id : null;

            // Recommendation Mapping
            $recommendation = match((int)$lRev->recommendation) {
                1 => \App\Models\ReviewAssignment::RECOMMEND_ACCEPT,
                2 => \App\Models\ReviewAssignment::RECOMMEND_MINOR_REVISION,
                3 => \App\Models\ReviewAssignment::RECOMMEND_MAJOR_REVISION,
                4 => \App\Models\ReviewAssignment::RECOMMEND_REJECT,
                default => null
            };

            // Status mapping
            $status = \App\Models\ReviewAssignment::STATUS_PENDING;
            if ($lRev->declined) $status = \App\Models\ReviewAssignment::STATUS_DECLINED;
            elseif ($lRev->cancelled) $status = \App\Models\ReviewAssignment::STATUS_CANCELLED;
            elseif ($lRev->date_completed) $status = \App\Models\ReviewAssignment::STATUS_COMPLETED;
            elseif ($lRev->date_confirmed) $status = \App\Models\ReviewAssignment::STATUS_ACCEPTED;

            \App\Models\ReviewAssignment::updateOrCreate(
                [
                    'submission_id' => $newSubmissionId,
                    'reviewer_id' => $newReviewerId,
                    'round' => (int)$lRev->round,
                ],
                [
                    'review_round_id' => $roundId,
                    'status' => $status,
                    'recommendation' => $recommendation,
                    'assigned_at' => $lRev->date_assigned,
                    'due_date' => $lRev->date_due,
                    'response_due_date' => $lRev->date_response_due,
                    'completed_at' => $lRev->date_completed,
                    'quality_rating' => (int)$lRev->quality,
                    'review_method' => $lRev->review_method == 2 ? 'double-blind' : 'blind',
                ]
            );
        }
    }

    /**
     * Migrate Discussions
     */
    public function migrateDiscussions()
    {
        $participantsTable = collect($this->getLegacyRows('query_participants'))
            ->map(fn($r) => $this->mapRow('query_participants', $r));
            
        $notesTable = collect($this->getLegacyRows('notes'))
            ->map(fn($r) => $this->mapRow('notes', $r));

        foreach ($this->parser->getTableData('queries') as $row) {
            $lQuery = $this->mapRow('queries', $row);
            
            // 1048585 is ASSOC_TYPE_SUBMISSION
            if ($lQuery->assoc_type != 1048585) continue;
            
            $newSubmissionId = LegacyMapping::getMapping('submissions', $lQuery->assoc_id);
            if (!$newSubmissionId) continue;

            $participants = $participantsTable->where('query_id', $lQuery->query_id);
            
            // Assume the first participant is the creator if we can't determine it
            $creatorLegacyId = $participants->first()?->user_id;
            $newCreatorId = $creatorLegacyId ? LegacyMapping::getMapping('users', $creatorLegacyId) : null;
            if (!$newCreatorId) continue;

            // Get the first note to act as the subject of the discussion
            $discussionNotes = $notesTable->where('assoc_type', 1048586)->where('assoc_id', $lQuery->query_id)->sortBy('date_created');
            $firstNote = $discussionNotes->first();
            $subject = $firstNote ? strip_tags($firstNote->title ?? 'Discussion') : 'Discussion';

            $discussion = \App\Models\Discussion::updateOrCreate(
                [
                    // using seq_id via legacy mapping would be cleaner but queries don't have natural keys easily,
                    // we'll use a hack by storing legacy query_id in subject temporarily or just rely on standard creation.
                    // To make it idempotent, we need a unique identifier. We can use the first note's date as a proxy.
                    'submission_id' => $newSubmissionId,
                    'stage_id' => (int)$lQuery->stage_id,
                    'subject' => $subject,
                    'created_at' => $firstNote ? $firstNote->date_created : now(),
                ],
                [
                    'user_id' => $newCreatorId,
                    'is_open' => !(bool)$lQuery->is_closed,
                ]
            );

            LegacyMapping::setMapping('discussions', $lQuery->query_id, $discussion->id);

            // Add Participants
            $newParticipantIds = [];
            foreach ($participants as $p) {
                $pId = LegacyMapping::getMapping('users', $p->user_id);
                if ($pId) $newParticipantIds[] = $pId;
            }
            if (!empty($newParticipantIds)) {
                $discussion->syncParticipants($newParticipantIds);
            }

            // Add Messages (Notes)
            foreach ($discussionNotes as $note) {
                $noteUserId = LegacyMapping::getMapping('users', $note->user_id) ?? $newCreatorId;
                
                \App\Models\DiscussionMessage::updateOrCreate(
                    [
                        'discussion_id' => $discussion->id,
                        'user_id' => $noteUserId,
                        'created_at' => $note->date_created,
                    ],
                    [
                        'body' => $note->contents ?? '',
                    ]
                );
            }
        }
    }

    /**
     * Migrate Logs
     */
    public function migrateLogs()
    {
        // 1. Event Logs
        foreach ($this->parser->getTableData('event_log') as $row) {
            $lLog = $this->mapRow('event_log', $row);
            
            if ($lLog->assoc_type != 1048585) continue; // ASSOC_TYPE_SUBMISSION
            
            $newSubmissionId = LegacyMapping::getMapping('submissions', $lLog->assoc_id);
            if (!$newSubmissionId) continue;

            $newUserId = LegacyMapping::getMapping('users', $lLog->user_id);
            $submission = Submission::find($newSubmissionId);

            \App\Models\SubmissionLog::updateOrCreate(
                [
                    'submission_id' => $newSubmissionId,
                    'created_at' => $lLog->date_logged,
                    'event_type' => \App\Models\SubmissionLog::EVENT_STAGE_CHANGED, // fallback generic
                ],
                [
                    'user_id' => $newUserId,
                    'title' => 'System Event: ' . $lLog->event_type,
                    'description' => $lLog->message ?? 'Migrated event',
                    'stage' => $submission ? $submission->stage : 'submission',
                ]
            );
        }

        // 2. Email Logs
        foreach ($this->parser->getTableData('email_log') as $row) {
            $eLog = $this->mapRow('email_log', $row);
            
            if ($eLog->assoc_type != 1048585) continue;
            
            $newSubmissionId = LegacyMapping::getMapping('submissions', $eLog->assoc_id);
            if (!$newSubmissionId) continue;

            $newSenderId = LegacyMapping::getMapping('users', $eLog->sender_id);
            $submission = Submission::find($newSubmissionId);

            \App\Models\SubmissionLog::updateOrCreate(
                [
                    'submission_id' => $newSubmissionId,
                    'created_at' => $eLog->date_sent,
                    'email_subject' => $eLog->subject,
                ],
                [
                    'user_id' => $newSenderId,
                    'event_type' => \App\Models\SubmissionLog::EVENT_DISCUSSION_MESSAGE, // email event
                    'title' => 'Email Sent: ' . $eLog->subject,
                    'description' => 'Sent to: ' . $eLog->recipients,
                    'email_body' => $eLog->body,
                    'stage' => $submission ? $submission->stage : 'submission',
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
