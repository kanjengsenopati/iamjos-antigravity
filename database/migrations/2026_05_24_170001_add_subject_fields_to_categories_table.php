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
            if (Schema::hasTable('categories')) {
                Schema::table('categories', function (Blueprint $table) {
                    if (!Schema::hasColumn('categories', 'slug')) {
                        $table->string('slug')->nullable()->after('name');
                    }
                    if (!Schema::hasColumn('categories', 'icon')) {
                        $table->string('icon')->nullable()->after('description');
                    }
                    if (!Schema::hasColumn('categories', 'color')) {
                        $table->string('color')->nullable()->after('icon');
                    }
                    
                    // Make journal_id nullable for site-level categories
                    $table->uuid('journal_id')->nullable()->change();
                });
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Could not alter categories table: ' . $e->getMessage());
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            if (Schema::hasTable('categories')) {
                Schema::table('categories', function (Blueprint $table) {
                    $colsToDrop = [];
                    if (Schema::hasColumn('categories', 'slug')) $colsToDrop[] = 'slug';
                    if (Schema::hasColumn('categories', 'icon')) $colsToDrop[] = 'icon';
                    if (Schema::hasColumn('categories', 'color')) $colsToDrop[] = 'color';
                    if (!empty($colsToDrop)) {
                        $table->dropColumn($colsToDrop);
                    }
                });
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Could not revert categories table: ' . $e->getMessage());
        }
    }
};
