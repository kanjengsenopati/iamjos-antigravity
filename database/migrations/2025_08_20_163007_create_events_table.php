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
        Schema::create('events', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('province_id')->nullable();
            $table->integer('external_id')->nullable();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('location')->nullable();
            $table->string('organized_by')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('web')->nullable();
            $table->boolean('is_approved')->default(false);
            $table->boolean('is_active')->default(true);
            $table->string('image')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
