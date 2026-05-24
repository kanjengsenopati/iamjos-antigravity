<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * FINAL CLEANUP - Remove ALL demo/seed data:
     * 1. Delete ALL site-level categories (Browse by Subject)
     * 2. Delete ALL accreditations
     * 3. Delete ALL demo authors (including the persistent one)
     * 4. Clear all caches
     */
    public function up(): void
    {
        Log::info('Starting FINAL cleanup of ALL demo/seed data...');
        
        try {
            DB::transaction(function () {
                // 1. Delete ALL site-level categories
                $deletedCategories = DB::table('categories')
                    ->whereNull('journal_id')
                    ->delete();
                Log::info('Deleted site-level categories', ['count' => $deletedCategories]);
                
                // 2. Delete ALL accreditations
                $deletedAccreditations = DB::table('accreditations')->delete();
                Log::info('Deleted accreditations', ['count' => $deletedAccreditations]);
                
                // 3. Find ALL users with Author role (not just demo emails)
                $authorUserIds = DB::table('users')
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
                    ->pluck('users.id');
                
                if ($authorUserIds->isNotEmpty()) {
                    Log::info('Found orphaned authors to delete', ['count' => $authorUserIds->count()]);
                    
                    // Remove submission_authors (if any)
                    DB::table('submission_authors')
                        ->whereIn('user_id', $authorUserIds)
                        ->delete();
                    
                    // Remove role assignments
                    DB::table('model_has_roles')
                        ->where('model_type', 'App\\Models\\User')
                        ->whereIn('model_uuid', $authorUserIds)
                        ->delete();
                    
                    // Remove journal user roles
                    DB::table('journal_user_roles')
                        ->whereIn('user_id', $authorUserIds)
                        ->delete();
                    
                    // Remove permissions
                    DB::table('model_has_permissions')
                        ->where('model_type', 'App\\Models\\User')
                        ->whereIn('model_uuid', $authorUserIds)
                        ->delete();
                    
                    // Delete the users
                    $deletedUsers = DB::table('users')
                        ->whereIn('id', $authorUserIds)
                        ->delete();
                    
                    Log::info('Deleted orphaned authors', ['count' => $deletedUsers]);
                } else {
                    Log::info('No orphaned authors found');
                }
                
                // 4. Clear application cache
                try {
                    \Illuminate\Support\Facades\Artisan::call('cache:clear');
                    Log::info('Cleared application cache');
                } catch (\Exception $e) {
                    Log::warning('Could not clear cache', ['error' => $e->getMessage()]);
                }
            });
            
            Log::info('FINAL cleanup completed successfully - database is now CLEAN');
        } catch (\Exception $e) {
            Log::error('FINAL cleanup migration failed', [
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
        Log::info('Cannot reverse final cleanup');
    }
};
