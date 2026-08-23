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
        try {
            if (Schema::hasTable('article_metrics')) {
                Schema::table('article_metrics', function (Blueprint $table) {
                    $table->string('ip_address', 45)->nullable()->change();
                });
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Could not alter article_metrics ip_address column: ' . $e->getMessage());
        }
    }

    public function down(): void
    {
        try {
            if (Schema::hasTable('article_metrics')) {
                Schema::table('article_metrics', function (Blueprint $table) {
                    $table->string('ip_address', 45)->nullable(false)->change();
                });
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Could not revert article_metrics ip_address column: ' . $e->getMessage());
        }
    }
};
