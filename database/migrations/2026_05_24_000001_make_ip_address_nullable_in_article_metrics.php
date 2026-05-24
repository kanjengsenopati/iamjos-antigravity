<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ip_address dibuat nullable karena:
 * - RecordArticleMetricJob menerima ?string $ipAddress
 * - Privacy: beberapa deployment mungkin tidak menyimpan IP
 * - Test environment tidak selalu menyediakan IP
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('article_metrics', function (Blueprint $table) {
            $table->string('ip_address', 45)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('article_metrics', function (Blueprint $table) {
            $table->string('ip_address', 45)->nullable(false)->change();
        });
    }
};
