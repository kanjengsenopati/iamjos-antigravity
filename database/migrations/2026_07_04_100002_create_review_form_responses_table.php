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
        Schema::create('review_form_responses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('review_assignment_id')->index();
            $table->uuid('review_form_element_id')->index();
            $table->text('response_value');
            $table->timestamps();

            $table->foreign('review_assignment_id')
                ->references('id')
                ->on('review_assignments')
                ->onDelete('cascade');

            $table->foreign('review_form_element_id')
                ->references('id')
                ->on('review_form_elements')
                ->onDelete('cascade');

            // Ensure one response per element per review assignment
            $table->unique(['review_assignment_id', 'review_form_element_id'], 'unique_response_per_element');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('review_form_responses');
    }
};
