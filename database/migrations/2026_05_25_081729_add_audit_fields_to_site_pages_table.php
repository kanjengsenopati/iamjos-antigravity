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
        try {
            if (Schema::hasTable('site_pages')) {
                Schema::table('site_pages', function (Blueprint $table) {
                    if (!Schema::hasColumn('site_pages', 'created_by')) {
                        $table->uuid('created_by')->nullable()->after('sort_order');
                    }
                    if (!Schema::hasColumn('site_pages', 'updated_by')) {
                        $table->uuid('updated_by')->nullable()->after('created_by');
                    }
                    if (!Schema::hasColumn('site_pages', 'deleted_at')) {
                        $table->softDeletes()->after('updated_by');
                    }
                    if (!Schema::hasColumn('site_pages', 'deleted_by')) {
                        $table->uuid('deleted_by')->nullable()->after('deleted_at');
                    }
                    if (!Schema::hasColumn('site_pages', 'meta_description')) {
                        $table->string('meta_description', 160)->nullable()->after('content');
                    }
                });
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Could not alter site_pages: ' . $e->getMessage());
        }
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
