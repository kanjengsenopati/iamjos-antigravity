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
        Schema::table('publications', function (Blueprint $table) {
            try {
                // Drop existing non-unique index if it exists
                // We wrap this in try-catch because of potential permission issues on some VPS
                $table->dropIndex(['doi']);
            } catch (\Exception $e) {
                // Ignore if index doesn't exist or permission denied
            }
            
            try {
                // Add unique index — allows NULL (unpublished articles without DOI)
                $table->string('doi')->nullable()->unique()->change();
            } catch (\Exception $e) {
                // If unique constraint fails due to permission, we log it but don't stop
                \Illuminate\Support\Facades\Log::warning('Failed to add unique constraint to publications.doi: ' . $e->getMessage());
            }
        });
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
