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
        // 1. CLEANUP: Hapus data duplikat yang sudah terlanjur ada di database
        // Duplikat terjadi karena bug getOrCreatePublication sebelumnya.
        
        // Cari duplikat berdasarkan publication_id dan email
        $duplicates = DB::select("
            SELECT publication_id, email
            FROM submission_authors
            WHERE publication_id IS NOT NULL 
              AND email IS NOT NULL
              AND email != ''
            GROUP BY publication_id, email
            HAVING count(*) > 1
        ");

        foreach ($duplicates as $dup) {
            $records = DB::table('submission_authors')
                ->where('publication_id', $dup->publication_id)
                ->where('email', $dup->email)
                ->orderBy('created_at', 'asc')
                ->get();
            
            // Prioritaskan mempertahankan record yang memiliki submission_id (record asli)
            $keep = $records->firstWhere(function($r) {
                return !empty($r->submission_id) && !empty($r->publication_id);
            });
            
            if (!$keep) {
                $keep = $records->first();
            }
            
            $toDelete = $records->where('id', '!=', $keep->id)->pluck('id');
            
            if ($toDelete->isNotEmpty()) {
                DB::table('submission_authors')->whereIn('id', $toDelete)->delete();
            }
        }

        // Cari duplikat berdasarkan submission_id dan email (untuk jaga-jaga)
        $subDuplicates = DB::select("
            SELECT submission_id, email
            FROM submission_authors
            WHERE submission_id IS NOT NULL 
              AND email IS NOT NULL
              AND email != ''
            GROUP BY submission_id, email
            HAVING count(*) > 1
        ");

        foreach ($subDuplicates as $dup) {
            $records = DB::table('submission_authors')
                ->where('submission_id', $dup->submission_id)
                ->where('email', $dup->email)
                ->orderBy('created_at', 'asc')
                ->get();
            
            // Prioritaskan mempertahankan record yang memiliki publication_id
            $keep = $records->firstWhere(function($r) {
                return !empty($r->submission_id) && !empty($r->publication_id);
            });
            
            if (!$keep) {
                $keep = $records->first();
            }
            
            $toDelete = $records->where('id', '!=', $keep->id)->pluck('id');
            
            if ($toDelete->isNotEmpty()) {
                DB::table('submission_authors')->whereIn('id', $toDelete)->delete();
            }
        }

        // 2. HARDENING: Tambahkan Unique Constraint agar hal yang sama tidak terjadi lagi
        Schema::table('submission_authors', function (Blueprint $table) {
            // Karena satu tabel bisa memiliki submission_id null atau publication_id null,
            // kita buat constraint yang spesifik.
            // Note: Pada MySQL/PostgreSQL, kombinasi unique dengan nilai NULL tidak conflict.
            
            $table->unique(['publication_id', 'email'], 'sa_pub_email_unique');
            $table->unique(['submission_id', 'email'], 'sa_sub_email_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('submission_authors', function (Blueprint $table) {
            $table->dropUnique('sa_pub_email_unique');
            $table->dropUnique('sa_sub_email_unique');
        });
    }
};
