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
     */
    public function up(): void
    {
        Log::info('Starting enhanced cleanup and subject categories setup...');
        
        try {
            DB::transaction(function () {
                // Phase 1: Clean up orphaned demo authors
                $this->cleanupOrphanedAuthors();
                
                // Phase 2: Add default subject categories
                $this->addDefaultCategories();
            });
            
            Log::info('Enhanced cleanup and categories setup completed successfully');
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
                $join->on('users.id', '=', 'model_has_roles.model_id')
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
                ->whereIn('model_id', $orphanedAuthorIds)
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
     */
    private function addDefaultCategories(): void
    {
        $categories = [
            [
                'name' => 'Science & Technology',
                'slug' => 'science-technology',
                'description' => 'Computer Science, Engineering, Mathematics, Physics, Chemistry',
                'icon' => 'flask',
                'color' => 'blue',
                'sort_order' => 1,
            ],
            [
                'name' => 'Medicine & Health',
                'slug' => 'medicine-health',
                'description' => 'Clinical Medicine, Public Health, Nursing, Pharmacy, Biomedical Sciences',
                'icon' => 'heartbeat',
                'color' => 'red',
                'sort_order' => 2,
            ],
            [
                'name' => 'Social Sciences',
                'slug' => 'social-sciences',
                'description' => 'Psychology, Sociology, Anthropology, Political Science, Economics',
                'icon' => 'users',
                'color' => 'green',
                'sort_order' => 3,
            ],
            [
                'name' => 'Arts & Humanities',
                'slug' => 'arts-humanities',
                'description' => 'Literature, History, Philosophy, Languages, Cultural Studies',
                'icon' => 'palette',
                'color' => 'purple',
                'sort_order' => 4,
            ],
            [
                'name' => 'Business & Economics',
                'slug' => 'business-economics',
                'description' => 'Management, Finance, Marketing, Accounting, Entrepreneurship',
                'icon' => 'chart-line',
                'color' => 'yellow',
                'sort_order' => 5,
            ],
            [
                'name' => 'Education',
                'slug' => 'education',
                'description' => 'Pedagogy, Curriculum Development, Educational Technology, Learning Sciences',
                'icon' => 'graduation-cap',
                'color' => 'indigo',
                'sort_order' => 6,
            ],
        ];
        
        foreach ($categories as $category) {
            // Check if category already exists
            $exists = DB::table('categories')
                ->where('slug', $category['slug'])
                ->exists();
            
            if (!$exists) {
                DB::table('categories')->insert(array_merge($category, [
                    'id' => \Illuminate\Support\Str::uuid()->toString(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
                
                Log::info('Added category', ['name' => $category['name']]);
            }
        }
    }
};
