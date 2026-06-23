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
        // Ubah allow_registration menjadi false untuk semua role Reviewer
        DB::table('roles')
            ->where('name', 'Reviewer')
            ->orWhere('slug', 'reviewer')
            ->update(['allow_registration' => false]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Kembalikan allow_registration menjadi true untuk semua role Reviewer jika dirollback
        DB::table('roles')
            ->where('name', 'Reviewer')
            ->orWhere('slug', 'reviewer')
            ->update(['allow_registration' => true]);
    }
};
