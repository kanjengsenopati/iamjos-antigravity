<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
            $this->command->warn('⚠️  SUPER_ADMIN_EMAIL not configured - will preserve all existing users');
            $superAdminEmail = 'none@example.com'; // Dummy value to prevent deletion of all users
        }
        
        $this->command->info('🧹 Starting production demo data cleanup...');
        $this->command->newLine();
        
        try {
            DB::transaction(function () use ($superAdminEmail) {
                // Phase 0: Identification
                $journalIds = $this->identifyDemoJournals();
                $userIds = $this->identifyDemoUsers($superAdminEmail);
                
                $this->command->info('📊 Identification Phase:');
                $this->command->info("   ✓ Found {$journalIds->count()} demo journals");
                $this->command->info("   ✓ Found {$userIds->count()} demo users");
                
                if ($journalIds->isEmpty() && $userIds->isEmpty()) {
                    $this->command->warn('   ⚠️  No demo data found to remove');
                    return;
                }
                
                // Get submission IDs for cascade deletions
                $submissionIds = DB::table('submissions')
                    ->whereIn('journal_id', $journalIds)
                    ->pluck('id');
                
                $this->command->info("   ✓ Found {$submissionIds->count()} submissions to remove");
                $this->command->newLine();
                
                // Phase 1-7: Deletions
                $counts = [];
                
                if ($submissionIds->isNotEmpty()) {
                    $this->command->info('🗑️  Phase 1: Metrics & Logs');
                    $counts = array_merge($counts, $this->deleteMetricsAndLogs($journalIds, $submissionIds));
                    
                    $this->command->info('🗑️  Phase 2: Workflow Data');
                    $counts = array_merge($counts, $this->deleteWorkflowData($submissionIds));
                    
                    $this->command->info('🗑️  Phase 3: Publication Data');
                    $counts = array_merge($counts, $this->deletePublicationData($submissionIds));
                    
                    $this->command->info('🗑️  Phase 4: Submission Data');
                    $counts = array_merge($counts, $this->deleteSubmissions($submissionIds));
                }
                
                if ($journalIds->isNotEmpty()) {
                    $this->command->info('🗑️  Phase 5: Journal Content');
                    $counts = array_merge($counts, $this->deleteJournalContent($journalIds));
                    
                    $this->command->info('🗑️  Phase 6: Journals');
                    $counts['journals'] = $this->deleteJournals($journalIds);
                }
                
                if ($userIds->isNotEmpty()) {
                    $this->command->info('🗑️  Phase 7: Demo Users');
                    $counts['users'] = $this->deleteDemoUsers($userIds);
                }
                
                $this->logCleanupSummary($counts, $superAdminEmail);
            });
        } catch (\Exception $e) {
            $this->command->error('❌ Cleanup failed: ' . $e->getMessage());
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
        $this->command->newLine();
        $this->command->warn('⚠️  ROLLBACK WARNING');
        $this->command->warn('');
        $this->command->warn('This migration removed demo data from production.');
        $this->command->warn('Deleted data CANNOT be automatically restored.');
        $this->command->warn('');
        $this->command->info('To restore demo data:');
        $this->command->info('  1. Ensure APP_ENV is set to "local" or "staging"');
        $this->command->info('  2. Run: php artisan db:seed --class=DemoSeeder');
        $this->command->warn('');
        $this->command->warn('⚠️  DO NOT run DemoSeeder in production!');
        $this->command->newLine();
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
        
        $counts['article_metrics'] = DB::table('article_metrics')
            ->whereIn('submission_id', $submissionIds)
            ->delete();
        
        $logIds = DB::table('submission_logs')
            ->whereIn('submission_id', $submissionIds)
            ->pluck('id');
        
        if ($logIds->isNotEmpty()) {
            $counts['submission_log_files'] = DB::table('submission_log_files')
                ->whereIn('submission_log_id', $logIds)
                ->delete();
        }
        
        $counts['submission_logs'] = DB::table('submission_logs')
            ->whereIn('submission_id', $submissionIds)
            ->delete();
        
        $counts['submission_notes'] = DB::table('submission_notes')
            ->whereIn('submission_id', $submissionIds)
            ->delete();
        
        $counts['crossref_logs'] = DB::table('crossref_logs')
            ->whereIn('journal_id', $journalIds)
            ->delete();
        
        return $counts;
    }
    
    private function deleteWorkflowData(Collection $submissionIds): array
    {
        $counts = [];
        
        $discussionIds = DB::table('discussions')
            ->whereIn('submission_id', $submissionIds)
            ->pluck('id');
        
        if ($discussionIds->isNotEmpty()) {
            $counts['discussion_files'] = DB::table('discussion_files')
                ->whereIn('discussion_id', $discussionIds)
                ->delete();
            
            $counts['discussion_messages'] = DB::table('discussion_messages')
                ->whereIn('discussion_id', $discussionIds)
                ->delete();
            
            $counts['discussion_participants'] = DB::table('discussion_participants')
                ->whereIn('discussion_id', $discussionIds)
                ->delete();
        }
        
        $counts['discussions'] = DB::table('discussions')
            ->whereIn('submission_id', $submissionIds)
            ->delete();
        
        $counts['review_assignments'] = DB::table('review_assignments')
            ->whereIn('submission_id', $submissionIds)
            ->delete();
        
        $counts['review_rounds'] = DB::table('review_rounds')
            ->whereIn('submission_id', $submissionIds)
            ->delete();
        
        $counts['editorial_assignments'] = DB::table('editorial_assignments')
            ->whereIn('submission_id', $submissionIds)
            ->delete();
        
        return $counts;
    }
    
    private function deletePublicationData(Collection $submissionIds): array
    {
        $counts = [];
        
        $publicationIds = DB::table('publications')
            ->whereIn('submission_id', $submissionIds)
            ->pluck('id');
        
        if ($publicationIds->isNotEmpty()) {
            $counts['publication_galleys'] = DB::table('publication_galleys')
                ->whereIn('publication_id', $publicationIds)
                ->delete();
            
            $counts['submission_authors_pub'] = DB::table('submission_authors')
                ->whereIn('publication_id', $publicationIds)
                ->delete();
        }
        
        $counts['publications'] = DB::table('publications')
            ->whereIn('submission_id', $submissionIds)
            ->delete();
        
        return $counts;
    }
    
    private function deleteSubmissions(Collection $submissionIds): array
    {
        $counts = [];
        
        $counts['submission_keyword'] = DB::table('submission_keyword')
            ->whereIn('submission_id', $submissionIds)
            ->delete();
        
        $counts['submission_files'] = DB::table('submission_files')
            ->whereIn('submission_id', $submissionIds)
            ->delete();
        
        $counts['submission_authors'] = DB::table('submission_authors')
            ->whereIn('submission_id', $submissionIds)
            ->whereNull('publication_id')
            ->delete();
        
        $counts['submissions'] = DB::table('submissions')
            ->whereIn('id', $submissionIds)
            ->delete();
        
        return $counts;
    }
    
    private function deleteJournalContent(Collection $journalIds): array
    {
        $counts = [];
        
        $menuIds = DB::table('navigation_menus')
            ->whereIn('journal_id', $journalIds)
            ->pluck('id');
        
        if ($menuIds->isNotEmpty()) {
            $counts['navigation_items'] = DB::table('navigation_items')
                ->whereIn('navigation_menu_id', $menuIds)
                ->delete();
        }
        
        $counts['navigation_menus'] = DB::table('navigation_menus')
            ->whereIn('journal_id', $journalIds)
            ->delete();
        
        $counts['sidebar_blocks'] = DB::table('sidebar_blocks')
            ->whereIn('journal_id', $journalIds)
            ->delete();
        
        $counts['announcements'] = DB::table('announcements')
            ->whereIn('journal_id', $journalIds)
            ->delete();
        
        $counts['notification_templates'] = DB::table('notification_templates')
            ->whereIn('journal_id', $journalIds)
            ->delete();
        
        $counts['sections'] = DB::table('sections')
            ->whereIn('journal_id', $journalIds)
            ->delete();
        
        $counts['issues'] = DB::table('issues')
            ->whereIn('journal_id', $journalIds)
            ->delete();
        
        $counts['journal_settings'] = DB::table('journal_settings')
            ->whereIn('journal_id', $journalIds)
            ->delete();
        
        return $counts;
    }
    
    private function deleteJournals(Collection $journalIds): int
    {
        return DB::table('journals')
            ->whereIn('id', $journalIds)
            ->delete();
    }
    
    private function deleteDemoUsers(Collection $userIds): int
    {
        DB::table('journal_user_roles')
            ->whereIn('user_id', $userIds)
            ->delete();
        
        DB::table('model_has_roles')
            ->where('model_type', 'App\\Models\\User')
            ->whereIn('model_id', $userIds)
            ->delete();
        
        return DB::table('users')
            ->whereIn('id', $userIds)
            ->delete();
    }
    
    private function logCleanupSummary(array $counts, string $superAdminEmail): void
    {
        $totalRecords = array_sum($counts);
        
        Log::info('Production cleanup completed', [
            'total_records' => $totalRecords,
            'breakdown' => $counts,
            'super_admin_preserved' => $superAdminEmail,
        ]);
        
        $this->command->newLine();
        $this->command->info('✅ Production cleanup completed successfully!');
        $this->command->info("   Total records removed: {$totalRecords}");
        $this->command->info("   Super admin preserved: {$superAdminEmail}");
        $this->command->info('   System infrastructure: Intact');
        $this->command->newLine();
    }
};
