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
        Schema::table('site_content_blocks', function (Blueprint $table) {
            // Add content column for rich text content
            $table->text('content')->nullable()->after('description');
            
            // Add audit trail fields
            $table->uuid('created_by')->nullable()->after('category');
            $table->uuid('updated_by')->nullable()->after('created_by');
            $table->uuid('deleted_by')->nullable()->after('updated_by');
            
            // Add soft deletes
            $table->softDeletes()->after('deleted_by');
            
            // Add foreign key constraints
            $table->foreign('created_by')
                ->references('id')
                ->on('users')
                ->onDelete('set null');
            
            $table->foreign('updated_by')
                ->references('id')
                ->on('users')
                ->onDelete('set null');
            
            $table->foreign('deleted_by')
                ->references('id')
                ->on('users')
                ->onDelete('set null');
            
            // Add indexes for performance
            $table->index('is_active');
            $table->index('sort_order');
            $table->index('category');
            $table->index('created_by');
            $table->index('updated_by');
        });
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
