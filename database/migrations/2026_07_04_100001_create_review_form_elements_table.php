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
        Schema::create('review_form_elements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('review_form_id')->index();
            $table->string('element_type'); // text, textarea, checkbox, radio, select, rating
            $table->text('question');
            $table->text('description')->nullable();
            $table->json('options')->nullable(); // For select/radio/checkbox
            $table->boolean('required')->default(false);
            $table->integer('sequence')->default(0)->index();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('review_form_id')
                ->references('id')
                ->on('review_forms')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('review_form_elements');
    }
};
