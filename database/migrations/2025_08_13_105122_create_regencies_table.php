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
        Schema::create('regencies', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->unsignedBigInteger('external_id');     // id dari PHRI (kab/kota)
            $table->uuid('province_id')->nullable();       // id_provinsi dari PHRI (referensi eksternal)
            $table->unsignedBigInteger('phri_province_id');
            $table->string('name');                      // nama kab/kota (boleh tanpa prefix)
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('regencies');
    }
};
