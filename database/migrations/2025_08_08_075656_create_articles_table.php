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
        Schema::create('articles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('external_id')->unique(); // ID dari PHRI
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('source')->nullable();
            $table->string('image')->nullable();
            $table->text('summary')->nullable(); // bisa diambil dari potongan body
            $table->longText('body');
            $table->integer('estimated_reading_time')->nullable(); // waktu baca dalam menit
            $table->timestamp('published_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};
