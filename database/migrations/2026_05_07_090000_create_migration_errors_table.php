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
        Schema::create('migration_errors', function (Blueprint $blueprint) {
            $blueprint->uuid('id')->primary();
            $blueprint->string('legacy_table')->nullable();
            $blueprint->string('legacy_id')->nullable();
            $blueprint->string('error_type'); // missing_file, mapping_failure, etc.
            $blueprint->text('message');
            $blueprint->jsonb('metadata')->nullable();
            $blueprint->boolean('is_fixed')->default(false);
            $blueprint->timestamps();

            $blueprint->index(['legacy_table', 'legacy_id']);
            $blueprint->index('error_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('migration_errors');
    }
};
