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
            $table->string('type')->default('HOTEL');
            $table->string('thumbnail')->nullable();
            $table->dropColumn('hotel');
            $table->string('name')->nullable();
            $table->dropColumn('gallery');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('meeting_venues', function (Blueprint $table) {
            $table->dropColumn('type');
            $table->dropColumn('thumbnail');
            $table->string('hotel')->nullable();
            $table->dropColumn('name');
            $table->json('gallery')->nullable();
        });
    }
};
