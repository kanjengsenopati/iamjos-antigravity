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
        Schema::create('benefits', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title');
            $table->string('title_en')->nullable();
            $table->text('subtitle');
            $table->text('subtitle_en')->nullable();
            $table->string('url')->nullable();
            $table->string('button_text')->nullable();
            $table->string('button_text_en')->nullable();
            $table->string('image')->nullable();
            $table->string('image_2')->nullable();
            $table->string('image_3')->nullable();
            $table->integer('order')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('benefits');
    }
};
