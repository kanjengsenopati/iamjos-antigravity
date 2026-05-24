<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tambah kolom funding_info ke tabel publications.
 *
 * funding_info disimpan sebagai JSONB array dengan struktur:
 * [
 *   {
 *     "funder_name": "Kementerian Pendidikan dan Kebudayaan",
 *     "funder_doi": "10.13039/501100003093",   // opsional — Crossref Funder Registry DOI
 *     "award_number": "123/UN1/2024"            // opsional — nomor hibah
 *   }
 * ]
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('publications', function (Blueprint $table) {
            // JSONB untuk PostgreSQL, JSON untuk MySQL/SQLite
            $table->json('funding_info')->nullable()->after('license_url')
                ->comment('Array of funding sources: [{funder_name, funder_doi, award_number}]');
        });
    }

    public function down(): void
    {
        Schema::table('publications', function (Blueprint $table) {
            $table->dropColumn('funding_info');
        });
    }
};
