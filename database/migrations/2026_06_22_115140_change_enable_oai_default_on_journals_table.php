<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('journals', function (Blueprint $table) {
            $table->boolean('enable_oai')->default(true)->change();
        });

        // Aktifkan OAI untuk semua jurnal yang sudah ada
        DB::table('journals')->update(['enable_oai' => true]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('journals', function (Blueprint $table) {
            $table->boolean('enable_oai')->default(false)->change();
        });
    }
};
