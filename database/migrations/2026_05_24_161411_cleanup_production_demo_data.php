<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Collection;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Removes all demo/seeded data from production while preserving
     * system infrastructure and super admin account.
     */
    public function up(): void
    {
        // Validate configuration
        $superAdminEmail = config('auth.super_admin_email', env('SUPER_ADMIN_EMAIL'));
        if (empty($superAdminEmail)) {
            Log::warning('SUPER_ADMIN_EMAIL not configured - will preserve all existing users');
            $superAdminEmail = 'none@example.com'; // Dummy value to prevent deletion of all users
        }
        
        Log::info('Starting production demo data cleanup...');
        
        try {
            DB::transaction(function () use ($superAdminEmail) {
                // Phase 0: Identification
                $journalIds = $this->identifyDemoJournals();
                $userIds = $this->identifyDemoUsers($superAdminEmail);
                
                Log::info('Identification Phase', [
                    'demo_journals' => $journalIds->count(),
                    'demo_users' => $userIds->count(),
                ]);
                
                if ($journalIds->isEmpty() && $userIds->isEmpty()) {
                    Log::warning('No demo data found to remove');
                    return;
                }
                
                // Get submission IDs for cascade deletions
                $submissionIds = DB::table('submissions')
                    ->whereIn('journal_id', $journalIds)
                    ->pluck('id');
                
                Log::info('Found submissions to remove', ['count' => $submissionIds->count()]);
                
                // Phase 1-7: Deletions
                $counts = [];
                
                if ($submissionIds->isNotEmpty()) {
                    Log::info('Phase 1: Metrics & Logs');
                    $counts = array_merge($counts, $this->deleteMetricsAndLogs($journalIds, $submissionIds));
                    
                    Log::info('Phase 2: Workflow Data');
                    $counts = array_merge($counts, $this->deleteWorkflowData($submissionIds));
                    
                    Log::info('Phase 3: Publication Data');
                    $counts = array_merge($counts, $this->deletePublicationData($submissionIds));
                    
                    Log::info('Phase 4: Submission Data');
                    $counts = array_merge($counts, $this->deleteSubmissions($submissionIds));
                }
                
                if ($journalIds->isNotEmpty()) {
                    Log::info('Phase 5: Journal Content');
                    $counts = array_merge($counts, $this->deleteJournalContent($journalIds));
                    
                    Log::info('Phase 6: Journals');
                    $counts['journals'] = $this->deleteJournals($journalIds);
                }
                
                if ($userIds->isNotEmpty()) {
                    Log::info('Phase 7: Demo Users');
                    $counts['users'] = $this->deleteDemoUsers($userIds);
                }
                
                $this->logCleanupSummary($counts, $superAdminEmail);
            });
        } catch (\Exception $e) {
            Log::error('Production cleanup migration failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
    
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Log::warning('ROLLBACK WARNING: This migration removed demo data from production.');
        Log::warning('Deleted data CANNOT be automatically restored.');
        Log::info('To restore demo data: 1. Set APP_ENV=local or staging, 2. Run: php artisan db:seed --class=DemoSeeder');
        Log::warning('DO NOT run DemoSeeder in production!');
    }
    
    private function identifyDemoJournals(): Collection
    {
        return DB::table('journals')
            ->whereIn('slug', ['jit', 'medika', 'jbe', 'eas', 'iamjos'])
            ->pluck('id');
    }
    
    private function identifyDemoUsers(string $superAdminEmail): Collection
    {
        return DB::table('users')
            ->where('email', 'LIKE', '%@demo.iamjos.id')
            ->where('email', '!=', $superAdminEmail)
            ->pluck('id');
    }
    
    private function deleteMetricsAndLogs(Collection $journalIds, Collection $submissionIds): array
    {
        $counts = [];
        
        if (Schema::hasTable('article_metrics')) {
            $counts['article_metrics'] = DB::table('article_metrics')
                ->whereIn('submission_id', $submissionIds)
                ->delete();
        }
        
        if (Schema::hasTable('submission_logs')) {
            $logIds = DB::table('submission_logs')
                ->whereIn('submission_id', $submissionIds)
                ->pluck('id');
            
            if ($logIds->isNotEmpty() && Schema::hasTable('submission_log_files')) {
                $counts['submission_log_files'] = DB::table('submission_log_files')
                    ->whereIn('submission_log_id', $logIds)
                    ->delete();
            }
            
            $counts['submission_logs'] = DB::table('submission_logs')
                ->whereIn('submission_id', $submissionIds)
                ->delete();
        }
        
        if (Schema::hasTable('submission_notes')) {
            $counts['submission_notes'] = DB::table('submission_notes')
                ->whereIn('submission_id', $submissionIds)
                ->delete();
        }
        
        if (Schema::hasTable('crossref_logs')) {
            $counts['crossref_logs'] = DB::table('crossref_logs')
                ->whereIn('journal_id', $journalIds)
                ->delete();
        }
        
        return $counts;
    }
    
    private function deleteWorkflowData(Collection $submissionIds): array
    {
        $counts = [];
        
        $discussionIds = DB::table('discussions')
            ->whereIn('submission_id', $submissionIds)
            ->pluck('id');
        
        if ($discussionIds->isNotEmpty()) {
            if (Schema::hasTable('discussion_files')) {
                if (Schema::hasColumn('discussion_files', 'discussion_message_id')) {
                    $messageIds = DB::table('discussion_messages')
                        ->whereIn('discussion_id', $discussionIds)
                        ->pluck('id');
                    if ($messageIds->isNotEmpty()) {
                        $counts['discussion_files'] = DB::table('discussion_files')
                            ->whereIn('discussion_message_id', $messageIds)
                            ->delete();
                    }
                } elseif (Schema::hasColumn('discussion_files', 'discussion_id')) {
                    $counts['discussion_files'] = DB::table('discussion_files')
                        ->whereIn('discussion_id', $discussionIds)
                        ->delete();
                }
            }
            
            if (Schema::hasTable('discussion_messages')) {
                $counts['discussion_messages'] = DB::table('discussion_messages')
                    ->whereIn('discussion_id', $discussionIds)
                    ->delete();
            }
            
            if (Schema::hasTable('discussion_participants')) {
                $counts['discussion_participants'] = DB::table('discussion_participants')
                    ->whereIn('discussion_id', $discussionIds)
                    ->delete();
            }
        }
        
        if (Schema::hasTable('discussions')) {
            $counts['discussions'] = DB::table('discussions')
                ->whereIn('submission_id', $submissionIds)
                ->delete();
        }
        
        if (Schema::hasTable('review_assignments')) {
            $counts['review_assignments'] = DB::table('review_assignments')
                ->whereIn('submission_id', $submissionIds)
                ->delete();
        }
        
        if (Schema::hasTable('review_rounds')) {
            $counts['review_rounds'] = DB::table('review_rounds')
                ->whereIn('submission_id', $submissionIds)
                ->delete();
        }
        
        if (Schema::hasTable('editorial_assignments')) {
            $counts['editorial_assignments'] = DB::table('editorial_assignments')
                ->whereIn('submission_id', $submissionIds)
                ->delete();
        }
        
        return $counts;
    }
    
    private function deletePublicationData(Collection $submissionIds): array
    {
        $counts = [];
        
        if (Schema::hasTable('publication_galleys')) {
            if (Schema::hasColumn('publication_galleys', 'submission_id')) {
                $counts['publication_galleys'] = DB::table('publication_galleys')
                    ->whereIn('submission_id', $submissionIds)
                    ->delete();
            }
        }
        
        if (Schema::hasTable('publications')) {
            $publicationIds = DB::table('publications')
                ->whereIn('submission_id', $submissionIds)
                ->pluck('id');
            
            if ($publicationIds->isNotEmpty()) {
                if (Schema::hasTable('publication_galleys') && Schema::hasColumn('publication_galleys', 'publication_id')) {
                    $counts['publication_galleys'] = DB::table('publication_galleys')
                        ->whereIn('publication_id', $publicationIds)
                        ->delete();
                }
                
                if (Schema::hasTable('submission_authors')) {
                    if (Schema::hasColumn('submission_authors', 'publication_id')) {
                        $counts['submission_authors_pub'] = DB::table('submission_authors')
                            ->whereIn('publication_id', $publicationIds)
                            ->delete();
                    }
                }
            }
            
            $counts['publications'] = DB::table('publications')
                ->whereIn('submission_id', $submissionIds)
                ->delete();
        }
        
        return $counts;
    }
    
    private function deleteSubmissions(Collection $submissionIds): array
    {
        $counts = [];
        
        if (Schema::hasTable('submission_keyword')) {
            $counts['submission_keyword'] = DB::table('submission_keyword')
                ->whereIn('submission_id', $submissionIds)
                ->delete();
        }
        
        if (Schema::hasTable('submission_files')) {
            $counts['submission_files'] = DB::table('submission_files')
                ->whereIn('submission_id', $submissionIds)
                ->delete();
        }
        
        if (Schema::hasTable('submission_authors')) {
            $counts['submission_authors'] = DB::table('submission_authors')
                ->whereIn('submission_id', $submissionIds)
                ->whereNull('publication_id')
                ->delete();
        }
        
        if (Schema::hasTable('submissions')) {
            $counts['submissions'] = DB::table('submissions')
                ->whereIn('id', $submissionIds)
                ->delete();
        }
        
        return $counts;
    }
    
    private function deleteJournalContent(Collection $journalIds): array
    {
        $counts = [];
        
        if (Schema::hasTable('navigation_menus')) {
            $menuIds = DB::table('navigation_menus')
                ->whereIn('journal_id', $journalIds)
                ->pluck('id');
            
            if ($menuIds->isNotEmpty() && Schema::hasTable('navigation_items')) {
                $counts['navigation_items'] = DB::table('navigation_items')
                    ->whereIn('navigation_menu_id', $menuIds)
                    ->delete();
            }
            
            $counts['navigation_menus'] = DB::table('navigation_menus')
                ->whereIn('journal_id', $journalIds)
                ->delete();
        }
        
        if (Schema::hasTable('sidebar_blocks')) {
            $counts['sidebar_blocks'] = DB::table('sidebar_blocks')
                ->whereIn('journal_id', $journalIds)
                ->delete();
        }
        
        if (Schema::hasTable('announcements')) {
            $counts['announcements'] = DB::table('announcements')
                ->whereIn('journal_id', $journalIds)
                ->delete();
        }
        
        if (Schema::hasTable('notification_templates')) {
            $counts['notification_templates'] = DB::table('notification_templates')
                ->whereIn('journal_id', $journalIds)
                ->delete();
        }
        
        if (Schema::hasTable('sections')) {
            $counts['sections'] = DB::table('sections')
                ->whereIn('journal_id', $journalIds)
                ->delete();
        }
        
        if (Schema::hasTable('issues')) {
            $counts['issues'] = DB::table('issues')
                ->whereIn('journal_id', $journalIds)
                ->delete();
        }
        
        if (Schema::hasTable('journal_settings')) {
            $counts['journal_settings'] = DB::table('journal_settings')
                ->whereIn('journal_id', $journalIds)
                ->delete();
        }
        
        return $counts;
    }
    
    private function deleteJournals(Collection $journalIds): int
    {
        if (Schema::hasTable('journals')) {
            return DB::table('journals')
                ->whereIn('id', $journalIds)
                ->delete();
        }
        return 0;
    }
    
    private function deleteDemoUsers(Collection $userIds): int
    {
        if (Schema::hasTable('journal_user_roles')) {
            DB::table('journal_user_roles')
                ->whereIn('user_id', $userIds)
                ->delete();
        }
        
        if (Schema::hasTable('model_has_roles')) {
            DB::table('model_has_roles')
                ->where('model_type', 'App\\Models\\User')
                ->whereIn('model_id', $userIds)
                ->delete();
        }
        
        if (Schema::hasTable('users')) {
            return DB::table('users')
                ->whereIn('id', $userIds)
                ->delete();
        }
        return 0;
    }
    
    private function logCleanupSummary(array $counts, string $superAdminEmail): void
    {
        $totalRecords = array_sum($counts);
        
        Log::info('Production cleanup completed successfully', [
            'total_records' => $totalRecords,
            'breakdown' => $counts,
            'super_admin_preserved' => $superAdminEmail,
            'system_infrastructure' => 'Intact',
        ]);
    }
};
