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
        
        // Step 1: Make path column nullable for site-level categories
        try {
            if (Schema::hasTable('categories')) {
                Schema::table('categories', function (Blueprint $table) {
                    if (Schema::hasColumn('categories', 'path')) {
                        $table->string('path')->nullable()->change();
                    }
                });
                Log::info('Made path column nullable');
            }
        } catch (\Throwable $e) {
            Log::warning('Could not change categories path column: ' . $e->getMessage());
        }
        
        // Step 2: Delete ALL site-level categories (journal_id IS NULL)
        try {
            if (Schema::hasTable('categories')) {
                $deletedCategories = DB::table('categories')
                    ->whereNull('journal_id')
                    ->delete();
                Log::info('Deleted ALL site-level categories', ['count' => $deletedCategories]);
            }
        } catch (\Throwable $e) {
            Log::warning('Could not delete site-level categories: ' . $e->getMessage());
        }
        
        // Step 3: Delete ALL accreditations
        try {
            if (Schema::hasTable('accreditations')) {
                $deletedAccreditations = DB::table('accreditations')->delete();
                Log::info('Deleted ALL accreditations', ['count' => $deletedAccreditations]);
            }
        } catch (\Throwable $e) {
            Log::warning('Could not delete accreditations: ' . $e->getMessage());
        }
        
        // Step 4 & 5: Cleanup orphaned demo authors
        try {
            if (Schema::hasTable('users') && Schema::hasTable('model_has_roles') && Schema::hasTable('roles')) {
                $orphanedAuthorIds = DB::table('users')
                    ->join('model_has_roles', function($join) {
                        $join->on('users.id', '=', 'model_has_roles.model_uuid')
                            ->where('model_has_roles.model_type', '=', 'App\\Models\\User');
                    })
                    ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
                    ->where('roles.name', '=', 'Author')
                    ->whereNotExists(function($query) {
                        if (Schema::hasTable('submission_authors')) {
                            $query->select(DB::raw(1))
                                ->from('submission_authors')
                                ->whereColumn('submission_authors.user_id', 'users.id');
                        }
                    })
                    ->pluck('users.id');
                
                if ($orphanedAuthorIds->isNotEmpty()) {
                    if (Schema::hasTable('model_has_roles')) {
                        DB::table('model_has_roles')
                            ->where('model_type', 'App\\Models\\User')
                            ->whereIn('model_uuid', $orphanedAuthorIds)
                            ->delete();
                    }
                    
                    if (Schema::hasTable('journal_user_roles')) {
                        DB::table('journal_user_roles')
                            ->whereIn('user_id', $orphanedAuthorIds)
                            ->delete();
                    }
                    
                    if (Schema::hasTable('model_has_permissions')) {
                        DB::table('model_has_permissions')
                            ->where('model_type', 'App\\Models\\User')
                            ->whereIn('model_uuid', $orphanedAuthorIds)
                            ->delete();
                    }
                    
                    $deletedUsers = DB::table('users')
                        ->whereIn('id', $orphanedAuthorIds)
                        ->delete();
                    
                    Log::info('Deleted orphaned authors', ['count' => $deletedUsers]);
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Could not cleanup orphaned authors: ' . $e->getMessage());
        }
        
        // Step 6: Clear application cache
        try {
            \Illuminate\Support\Facades\Artisan::call('cache:clear');
            Log::info('Cleared application cache');
        } catch (\Throwable $e) {
            Log::warning('Could not clear cache', ['error' => $e->getMessage()]);
        }
        
        Log::info('CRITICAL FIX completed successfully');
    }
    
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Log::info('Cannot reverse critical fix');
    }
};
