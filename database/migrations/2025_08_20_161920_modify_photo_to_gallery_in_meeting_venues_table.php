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
        Schema::table('meeting_venues', function (Blueprint $table) {
            // Drop the old photo column
            $table->dropColumn('photo');
            // Add new gallery column for JSON array of photo paths
            $table->json('gallery')->nullable()->after('max_capacity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('meeting_venues', function (Blueprint $table) {
            $table->dropColumn('gallery');
            $table->string('photo')->nullable()->after('max_capacity');
        });
    }
};
