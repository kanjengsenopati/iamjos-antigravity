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
        // 1. Drop existing unique constraints if they exist (to prevent blocking updates)
        Schema::table('submission_authors', function (Blueprint $table) {
            // Kita coba drop safely. Beberapa DB bisa error jika tidak ada, 
            // jadi kita bungkus dalam try-catch secara manual via DB statement atau biarkan exception.
        });
        
        try {
            Schema::table('submission_authors', function (Blueprint $table) {
                $table->dropUnique('sa_pub_email_unique');
            });
        } catch (\Exception $e) {}
        
        try {
            Schema::table('submission_authors', function (Blueprint $table) {
                $table->dropUnique('sa_sub_email_unique');
            });
        } catch (\Exception $e) {}

        // 2. Lakukan deduplikasi ketat menggunakan PHP logic agar kebal dari dialect DB
        // Ambil semua records
        $allAuthors = DB::table('submission_authors')->orderBy('created_at', 'asc')->get();
        
        $seenPub = [];
        $seenSub = [];
        $toDelete = [];

        foreach ($allAuthors as $author) {
            $email = strtolower(trim((string)$author->email));
            if (empty($email)) continue; // Abaikan yang tidak punya email

            $isDuplicate = false;

            // Cek duplikat di scope publication
            if (!empty($author->publication_id)) {
                $pubKey = $author->publication_id . '_' . $email;
                if (isset($seenPub[$pubKey])) {
                    $isDuplicate = true;
                } else {
                    $seenPub[$pubKey] = $author->id;
                }
            }

            // Cek duplikat di scope submission
            if (!empty($author->submission_id)) {
                $subKey = $author->submission_id . '_' . $email;
                if (isset($seenSub[$subKey])) {
                    $isDuplicate = true;
                } else {
                    // Hanya set seenSub jika belum ditandai duplikat
                    if (!$isDuplicate) {
                        $seenSub[$subKey] = $author->id;
                    }
                }
            }

            if ($isDuplicate) {
                $toDelete[] = $author->id;
            }
        }

        // 3. Hapus duplikat
        if (!empty($toDelete)) {
            // Delete dalam chunk agar aman
            $chunks = array_chunk($toDelete, 500);
            foreach ($chunks as $chunk) {
                DB::table('submission_authors')->whereIn('id', $chunk)->delete();
            }
        }

        // 4. Update dan standarisasi string email (trim & lowercase)
        // Lakukan per baris untuk menghindari error raw query yang beda antar DB
        $remaining = DB::table('submission_authors')->get();
        foreach ($remaining as $author) {
            $cleanEmail = strtolower(trim((string)$author->email));
            if ($author->email !== $cleanEmail) {
                DB::table('submission_authors')
                  ->where('id', $author->id)
                  ->update(['email' => $cleanEmail]);
            }
        }

        // 5. Pasang kembali constraint unik setelah data 100% bersih dan seragam
        Schema::table('submission_authors', function (Blueprint $table) {
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
