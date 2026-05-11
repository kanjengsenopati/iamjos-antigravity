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
        Schema::table('publications', function (Blueprint $table) {
            // Enum: not_deposited, submitted, active, failed
            $table->string('doi_status')->default('not_deposited')->after('doi');
            $table->string('crossref_batch_id')->nullable()->after('doi_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('publications', function (Blueprint $table) {
            $table->dropColumn(['doi_status', 'crossref_batch_id']);
        });
    }
};
