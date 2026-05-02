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
        Schema::create('legacy_source_configs', function (Blueprint $table) {
            $table->id();
            $table->string('connection_name')->default('legacy');
            $table->string('driver')->default('mysql');
            $table->string('host');
            $table->string('port')->default('3306');
            $table->string('database');
            $table->string('username');
            $table->text('password'); // Encrypted
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('legacy_source_configs');
    }
};
