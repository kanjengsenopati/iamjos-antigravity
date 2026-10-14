<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds internal citation linking columns to citations table and
     * citation count metrics to submissions table.
     */
    public function up(): void
    {
        Schema::table('citations', function (Blueprint $table) {
            $table->uuid('target_submission_id')->nullable()->after('publication_id')->index();
            $table->string('matched_by', 50)->nullable()->after('target_submission_id');

            $table->foreign('target_submission_id')
                ->references('id')
                ->on('submissions')
                ->nullOnDelete();
        });

        Schema::table('submissions', function (Blueprint $table) {
            $table->unsignedInteger('citation_count')->default(0)->after('references')->index();
            $table->unsignedInteger('external_citation_count')->default(0)->after('citation_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();

        if (DB::getDriverName() === 'sqlite') {
            DB::statement('DROP INDEX IF EXISTS citations_target_submission_id_index');
            DB::statement('DROP INDEX IF EXISTS submissions_citation_count_index');

            Schema::table('citations', function (Blueprint $table) {
                $table->dropForeign(['target_submission_id']);
            });

            Schema::table('citations', function (Blueprint $table) {
                $table->dropColumn(['target_submission_id', 'matched_by']);
            });

            Schema::table('submissions', function (Blueprint $table) {
                $table->dropColumn(['citation_count', 'external_citation_count']);
            });
        } else {
            Schema::table('citations', function (Blueprint $table) {
                $table->dropForeign(['target_submission_id']);
                $table->dropIndex(['target_submission_id']);
                $table->dropColumn(['target_submission_id', 'matched_by']);
            });

            Schema::table('submissions', function (Blueprint $table) {
                $table->dropIndex(['citation_count']);
                $table->dropColumn(['citation_count', 'external_citation_count']);
            });
        }

        Schema::enableForeignKeyConstraints();
    }
};
