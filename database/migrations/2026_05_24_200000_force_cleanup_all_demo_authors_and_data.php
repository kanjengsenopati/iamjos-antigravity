<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * FORCE cleanup of ALL demo/seed data from production database:
     * 1. Remove ALL users with @demo.iamjos.id emails (regardless of submissions)
     * 2. Clear application cache to refresh stats
     */
    public function up(): void
    {
        Log::info('Starting FORCE cleanup of all demo data...');
        
        try {
            DB::transaction(function () {
                // Find ALL demo users (not just orphaned ones)
                $demoUserIds = DB::table('users')
                    ->where('email', 'LIKE', '%@demo.iamjos.id')
                    ->pluck('id');
                
                if ($demoUserIds->isNotEmpty()) {
                    Log::info('Found demo users to delete', ['count' => $demoUserIds->count()]);
                    
                    // 1. Remove from submission_authors (if any)
                    $deletedSubmissionAuthors = DB::table('submission_authors')
                        ->whereIn('user_id', $demoUserIds)
                        ->delete();
                    Log::info('Deleted submission_authors records', ['count' => $deletedSubmissionAuthors]);
                    
                    // 2. Remove role assignments
                    $deletedRoles = DB::table('model_has_roles')
                        ->where('model_type', 'App\\Models\\User')
                        ->whereIn('model_uuid', $demoUserIds)
                        ->delete();
                    Log::info('Deleted role assignments', ['count' => $deletedRoles]);
                    
                    // 3. Remove journal user roles
                    $deletedJournalRoles = DB::table('journal_user_roles')
                        ->whereIn('user_id', $demoUserIds)
                        ->delete();
                    Log::info('Deleted journal_user_roles', ['count' => $deletedJournalRoles]);
                    
                    // 4. Remove permissions
                    $deletedPermissions = DB::table('model_has_permissions')
                        ->where('model_type', 'App\\Models\\User')
                        ->whereIn('model_uuid', $demoUserIds)
                        ->delete();
                    Log::info('Deleted permissions', ['count' => $deletedPermissions]);
                    
                    // 5. Delete the users
                    $deletedUsers = DB::table('users')
                        ->whereIn('id', $demoUserIds)
                        ->delete();
                    Log::info('Deleted demo users', ['count' => $deletedUsers]);
                    
                    // 6. Clear application cache
                    try {
                        \Illuminate\Support\Facades\Artisan::call('cache:clear');
                        Log::info('Cleared application cache');
                    } catch (\Exception $e) {
                        Log::warning('Could not clear cache', ['error' => $e->getMessage()]);
                    }
                } else {
                    Log::info('No demo users found to delete');
                }
            });
            
            Log::info('FORCE cleanup completed successfully');
        } catch (\Exception $e) {
            Log::error('FORCE cleanup migration failed', [
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
        Log::info('Cannot reverse demo data cleanup');
    }
};
