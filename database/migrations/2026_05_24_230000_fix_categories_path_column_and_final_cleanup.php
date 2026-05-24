<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * CRITICAL FIX:
     * 1. Make path column nullable for site-level categories
     * 2. Delete ALL existing site-level categories
     * 3. Delete ALL accreditations
     * 4. Investigate and log remaining authors
     */
    public function up(): void
    {
        Log::info('Starting CRITICAL FIX: path column and final cleanup...');
        
        try {
            DB::transaction(function () {
                // Step 1: Make path column nullable for site-level categories
                Schema::table('categories', function (Blueprint $table) {
                    $table->string('path')->nullable()->change();
                });
                Log::info('Made path column nullable');
                
                // Step 2: Delete ALL site-level categories (journal_id IS NULL)
                $deletedCategories = DB::table('categories')
                    ->whereNull('journal_id')
                    ->delete();
                Log::info('Deleted ALL site-level categories', ['count' => $deletedCategories]);
                
                // Step 3: Delete ALL accreditations
                $deletedAccreditations = DB::table('accreditations')->delete();
                Log::info('Deleted ALL accreditations', ['count' => $deletedAccreditations]);
                
                // Step 4: Investigate remaining authors
                $remainingAuthors = DB::table('users')
                    ->join('model_has_roles', function($join) {
                        $join->on('users.id', '=', 'model_has_roles.model_uuid')
                            ->where('model_has_roles.model_type', '=', 'App\\Models\\User');
                    })
                    ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
                    ->where('roles.name', '=', 'Author')
                    ->select('users.id', 'users.email', 'users.given_name', 'users.family_name')
                    ->get();
                
                Log::info('Remaining authors after cleanup', [
                    'count' => $remainingAuthors->count(),
                    'authors' => $remainingAuthors->toArray()
                ]);
                
                // Step 5: Delete orphaned authors (those without submissions)
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
                    ->pluck('users.id');
                
                if ($orphanedAuthorIds->isNotEmpty()) {
                    Log::info('Found orphaned authors to delete', [
                        'count' => $orphanedAuthorIds->count(),
                        'ids' => $orphanedAuthorIds->toArray()
                    ]);
                    
                    // Remove role assignments
                    DB::table('model_has_roles')
                        ->where('model_type', 'App\\Models\\User')
                        ->whereIn('model_uuid', $orphanedAuthorIds)
                        ->delete();
                    
                    // Remove journal user roles
                    DB::table('journal_user_roles')
                        ->whereIn('user_id', $orphanedAuthorIds)
                        ->delete();
                    
                    // Remove permissions
                    DB::table('model_has_permissions')
                        ->where('model_type', 'App\\Models\\User')
                        ->whereIn('model_uuid', $orphanedAuthorIds)
                        ->delete();
                    
                    // Delete the users
                    $deletedUsers = DB::table('users')
                        ->whereIn('id', $orphanedAuthorIds)
                        ->delete();
                    
                    Log::info('Deleted orphaned authors', ['count' => $deletedUsers]);
                } else {
                    Log::info('No orphaned authors found');
                }
                
                // Step 6: Clear application cache
                try {
                    \Illuminate\Support\Facades\Artisan::call('cache:clear');
                    Log::info('Cleared application cache');
                } catch (\Exception $e) {
                    Log::warning('Could not clear cache', ['error' => $e->getMessage()]);
                }
            });
            
            Log::info('CRITICAL FIX completed successfully');
        } catch (\Exception $e) {
            Log::error('CRITICAL FIX migration failed', [
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
        Log::info('Cannot reverse critical fix');
    }
};
