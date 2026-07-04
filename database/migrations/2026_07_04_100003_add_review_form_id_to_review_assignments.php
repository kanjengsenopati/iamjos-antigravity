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
        Schema::table('review_assignments', function (Blueprint $table) {
            $table->uuid('review_form_id')->nullable()->after('reviewer_id')->index();

            $table->foreign('review_form_id')
                ->references('id')
                ->on('review_forms')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('review_assignments', function (Blueprint $table) {
            $table->dropForeign(['review_form_id']);
            $table->dropColumn('review_form_id');
        });
    }
};
