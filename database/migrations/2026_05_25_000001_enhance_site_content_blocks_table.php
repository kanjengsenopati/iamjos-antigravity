<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Enhance site_content_blocks table with audit fields and additional functionality
     * for the enhanced public page CRUD feature.
     */
    public function up(): void
    {
        try {
            if (Schema::hasTable('site_content_blocks')) {
                Schema::table('site_content_blocks', function (Blueprint $table) {
                    if (!Schema::hasColumn('site_content_blocks', 'content')) {
                        $table->text('content')->nullable()->after('description');
                    }
                    if (!Schema::hasColumn('site_content_blocks', 'created_by')) {
                        $table->uuid('created_by')->nullable()->after('category');
                    }
                    if (!Schema::hasColumn('site_content_blocks', 'updated_by')) {
                        $table->uuid('updated_by')->nullable()->after('created_by');
                    }
                    if (!Schema::hasColumn('site_content_blocks', 'deleted_by')) {
                        $table->uuid('deleted_by')->nullable()->after('updated_by');
                    }
                    if (!Schema::hasColumn('site_content_blocks', 'deleted_at')) {
                        $table->softDeletes()->after('deleted_by');
                    }
                });
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Could not alter site_content_blocks: ' . $e->getMessage());
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('site_content_blocks', function (Blueprint $table) {
            // Drop foreign keys first
            $table->dropForeign(['created_by']);
            $table->dropForeign(['updated_by']);
            $table->dropForeign(['deleted_by']);
            
            // Drop indexes
            $table->dropIndex(['is_active']);
            $table->dropIndex(['sort_order']);
            $table->dropIndex(['category']);
            $table->dropIndex(['created_by']);
            $table->dropIndex(['updated_by']);
            
            // Drop columns
            $table->dropColumn([
                'content',
                'created_by',
                'updated_by',
                'deleted_by',
                'deleted_at'
            ]);
        });
    }
};
