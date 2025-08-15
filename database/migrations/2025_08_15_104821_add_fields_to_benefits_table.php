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
        Schema::table('benefits', function (Blueprint $table) {
            if (!Schema::hasColumn('benefits', 'title_en')) {
                $table->string('title_en')->nullable()->after('title');
            }
            if (!Schema::hasColumn('benefits', 'subtitle_en')) {
                $table->string('subtitle_en')->nullable()->after('subtitle');
            }
            if (!Schema::hasColumn('benefits', 'button_text_en')) {
                $table->string('button_text_en')->nullable()->after('button_text');
            }
            if (!Schema::hasColumn('benefits', 'image_2')) {
                $table->string('image_2')->nullable()->after('image');
            }
            if (!Schema::hasColumn('benefits', 'image_3')) {
                $table->string('image_3')->nullable()->after('image_2');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('benefits', function (Blueprint $table) {
            $table->dropColumn(['title_en', 'subtitle_en', 'button_text_en', 'image_2', 'image_3']);
        });
    }
};
