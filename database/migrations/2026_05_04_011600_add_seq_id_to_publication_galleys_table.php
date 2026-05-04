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
        if (Schema::hasTable('publication_galleys')) {
            Schema::table('publication_galleys', function (Blueprint $table) {
                $table->bigInteger('seq_id')->unsigned()->nullable()->unique();
            });

            // PostgreSQL specific sequence handling
            DB::statement('CREATE SEQUENCE IF NOT EXISTS publication_galleys_seq_id_seq');
            DB::statement("ALTER TABLE publication_galleys ALTER COLUMN seq_id SET DEFAULT nextval('publication_galleys_seq_id_seq')");
            DB::statement("UPDATE publication_galleys SET seq_id = nextval('publication_galleys_seq_id_seq') WHERE seq_id IS NULL");
            DB::statement("ALTER TABLE publication_galleys ALTER COLUMN seq_id SET NOT NULL");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('publication_galleys')) {
            Schema::table('publication_galleys', function (Blueprint $table) {
                $table->dropColumn('seq_id');
            });
            DB::statement('DROP SEQUENCE IF EXISTS publication_galleys_seq_id_seq');
        }
    }
};
