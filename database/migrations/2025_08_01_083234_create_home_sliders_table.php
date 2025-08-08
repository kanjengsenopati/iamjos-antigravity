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
        Schema::create('home_sliders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title');
            $table->string('title_en')->nullable();
            $table->text('description');
            $table->text('description_en')->nullable();
            $table->string('button_text', 100);
            $table->string('button_text_en', 100)->nullable();
            $table->string('button_link');
            $table->string('media')->nullable();
            $table->enum('media_type', ['image', 'video'])->nullable();
            $table->enum('media_processing_status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->string('thumbnail_path')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['is_active', 'sort_order']);
            $table->index('media_processing_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('home_sliders');
    }
};
