<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('site_pages', function (Blueprint $table) {
            // Add audit fields for tracking who created and updated records
            $table->uuid('created_by')->nullable()->after('sort_order');
            $table->uuid('updated_by')->nullable()->after('created_by');
            
            // Add soft delete support
            $table->softDeletes()->after('updated_by');
            $table->uuid('deleted_by')->nullable()->after('deleted_at');
            
            // Add meta description field for SEO
            $table->string('meta_description', 160)->nullable()->after('content');
            
            // Add foreign key constraints for user references
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
            $table->index('is_published');
            $table->index('sort_order');
            $table->index('deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('site_pages', function (Blueprint $table) {
            // Drop foreign key constraints first
            $table->dropForeign(['created_by']);
            $table->dropForeign(['updated_by']);
            $table->dropForeign(['deleted_by']);
            
            // Drop indexes
            $table->dropIndex(['is_published']);
            $table->dropIndex(['sort_order']);
            $table->dropIndex(['deleted_at']);
            
            // Drop columns
            $table->dropColumn([
                'created_by',
                'updated_by',
                'deleted_at',
                'deleted_by',
                'meta_description'
            ]);
        });
    }
};
