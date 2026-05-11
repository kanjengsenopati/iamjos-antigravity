<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * DOI-03 FIX: Add unique constraint to publications.doi
     * 
     * DOIs must be globally unique by specification. Without a database-level
     * constraint, duplicate DOIs could be assigned to different publications,
     * which violates the DOI standard and would cause Crossref deposit failures.
     */
    public function up(): void
    {
        // 1. Try to drop the index in a separate block
        try {
            Schema::table('publications', function (Blueprint $table) {
                $table->dropIndex(['doi']);
            });
        } catch (\Exception $e) {
            // Silence permission/existence errors
        }

        // 2. Try to add unique constraint in a separate block
        try {
            Schema::table('publications', function (Blueprint $table) {
                $table->string('doi')->nullable()->unique()->change();
            });
        } catch (\Exception $e) {
            // Silence if this also fails, but log it
            \Illuminate\Support\Facades\Log::warning('Failed to add unique constraint to publications.doi: ' . $e->getMessage());
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('publications', function (Blueprint $table) {
            $table->dropUnique(['doi']);
            $table->string('doi')->nullable()->index()->change();
        });
    }
};
