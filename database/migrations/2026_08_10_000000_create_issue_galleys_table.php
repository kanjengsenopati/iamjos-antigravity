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
        Schema::create('issue_galleys', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('journal_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('issue_id')->constrained('issues')->cascadeOnDelete();
            
            $table->string('label'); // e.g., 'PDF'
            $table->string('locale')->nullable(); // e.g., 'en_US'
            $table->string('file_path'); // Path to the uploaded file
            $table->string('file_type')->nullable(); // e.g., 'application/pdf'
            $table->string('original_file_name')->nullable();
            
            $table->string('url_path')->nullable(); // Optional public URL slug
            $table->integer('seq_id')->unique()->nullable(); // Short sequence ID
            $table->integer('sort_order')->default(0);

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('issue_galleys');
    }
};
