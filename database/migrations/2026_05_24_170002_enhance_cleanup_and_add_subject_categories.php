<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * 1. Clean up orphaned demo authors (users with Author role but no submissions)
     * 2. Add default subject categories for "Browse by Subject" section
     * 
     * NOTE: Categories are NO LONGER auto-inserted. Admin must configure them manually.
     */
    public function up(): void
    {
        Log::info('Starting enhanced cleanup (categories auto-insert DISABLED)...');
        
        try {
            DB::transaction(function () {
                // Phase 1: Clean up orphaned demo authors
                $this->cleanupOrphanedAuthors();
                
                // Phase 2: DELETE any existing demo categories
                $this->cleanupDemoCategories();
            });
            
            Log::info('Enhanced cleanup completed successfully');
        } catch (\Exception $e) {
            Log::error('Enhanced cleanup migration failed', [
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
        Log::info('Rolling back enhanced cleanup - categories will remain');
    }
    
    /**
     * Clean up orphaned demo authors (users with no submissions).
     */
    private function cleanupOrphanedAuthors(): void
    {
        // Find users with Author role who have no submission_authors records
        $orphanedAuthorIds = DB::table('users')
            ->join('model_has_roles', function($join) {
                $join->on('users.id', '=', 'model_has_roles.model_uuid')
                    ->where('model_has_roles.model_type', '=', 'App\\Models\\User');
            })
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->where('roles.name', '=', 'Author')
            ->whereNotExists(function($query) {
                $query->select(DB::raw(1))
                    ->from('submission_authors')
                    ->whereColumn('submission_authors.user_id', 'users.id');
            })
            ->where('users.email', 'LIKE', '%@demo.iamjos.id')
            ->pluck('users.id');
        
        if ($orphanedAuthorIds->isNotEmpty()) {
            // Remove role assignments
            DB::table('model_has_roles')
                ->where('model_type', 'App\\Models\\User')
                ->whereIn('model_uuid', $orphanedAuthorIds)
                ->delete();
            
            // Remove journal user roles
            DB::table('journal_user_roles')
                ->whereIn('user_id', $orphanedAuthorIds)
                ->delete();
            
            // Delete orphaned authors
            $deletedCount = DB::table('users')
                ->whereIn('id', $orphanedAuthorIds)
                ->delete();
            
            Log::info('Cleaned up orphaned demo authors', ['count' => $deletedCount]);
        } else {
            Log::info('No orphaned demo authors found');
        }
    }
    
    /**
     * Add default subject categories for Browse by Subject section.
     * 
     * DEPRECATED: This method is no longer called. Categories must be added manually by admin.
     */
    private function addDefaultCategories(): void
    {
        Log::info('addDefaultCategories() is deprecated - categories must be configured manually');
    }
    
    /**
     * Clean up any demo categories that were auto-inserted.
     */
    private function cleanupDemoCategories(): void
    {
        // Delete site-level categories (journal_id IS NULL)
        $deletedCount = DB::table('categories')
            ->whereNull('journal_id')
            ->delete();
        
        Log::info('Cleaned up site-level categories', ['count' => $deletedCount]);
    }
};
