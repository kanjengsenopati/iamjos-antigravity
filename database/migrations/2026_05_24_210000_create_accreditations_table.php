<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Creates accreditations table but does NOT insert default data.
     * Admin must configure accreditations manually via Super Admin Panel.
     */
    public function up(): void
    {
        Schema::create('accreditations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name'); // e.g., "SINTA 1"
            $table->string('slug')->unique(); // e.g., "sinta-1"
            $table->string('level'); // e.g., "S1", "S2", "SC", "DJ"
            $table->string('color'); // e.g., "amber", "slate", "blue", "purple"
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // NO default data inserted - admin must configure manually
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accreditations');
    }
};
