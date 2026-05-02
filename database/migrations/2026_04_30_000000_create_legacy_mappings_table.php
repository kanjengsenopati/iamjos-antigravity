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
        Schema::create('legacy_mappings', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->string('legacy_table');
            $blueprint->string('legacy_id'); // String because some OJS IDs might be weird or non-int
            $blueprint->uuid('new_uuid')->nullable();
            $blueprint->integer('new_int_id')->nullable(); // Fallback if destination uses seq_id/int
            $blueprint->jsonb('metadata')->nullable(); // To store extra context
            $blueprint->timestamps();

            $blueprint->index(['legacy_table', 'legacy_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('legacy_mappings');
    }
};
