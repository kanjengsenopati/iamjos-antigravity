<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Structured citations table — derived from publications.references (TEXT blob).
     *
     * This is an ADDITIVE migration. It does NOT modify any existing tables.
     * The publications.references column remains the source of truth.
     * This table acts as a structured enrichment layer for better
     * Google Scholar and CrossRef citation matching.
     */
    public function up(): void
    {
        Schema::create('citations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('publication_id');
            $table->smallInteger('seq')->unsigned();          // 1-based order
            $table->text('raw_citation');                      // original text as-is
            $table->string('doi', 255)->nullable()->index();  // extracted DOI
            $table->text('title')->nullable();                 // extracted title
            $table->json('authors')->nullable();               // ["Evaluslana, E.", "Wongso, C."]
            $table->smallInteger('year')->nullable();          // publication year
            $table->string('source', 500)->nullable();         // journal/book name
            $table->string('volume', 20)->nullable();
            $table->string('issue', 20)->nullable();
            $table->string('first_page', 20)->nullable();
            $table->string('last_page', 20)->nullable();
            $table->text('url')->nullable();                   // non-DOI URL
            $table->boolean('is_structured')->default(false);  // successfully parsed?
            $table->string('processing_status', 20)->default('pending'); // pending/processed/failed
            $table->timestamps();

            $table->foreign('publication_id')
                  ->references('id')
                  ->on('publications')
                  ->onDelete('cascade');

            $table->unique(['publication_id', 'seq']);
            $table->index(['publication_id', 'seq']);
        });
    }

    /**
     * Reverse the migration — safe to rollback without affecting anything else.
     */
    public function down(): void
    {
        Schema::dropIfExists('citations');
    }
};
