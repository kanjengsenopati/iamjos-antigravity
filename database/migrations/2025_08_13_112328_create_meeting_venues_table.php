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
        Schema::create('meeting_venues', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->unsignedBigInteger('external_id'); // id PHRI
            $table->unsignedBigInteger('phri_province_id'); // id_province (PHRI)
            $table->unsignedBigInteger('phri_regency_id');  // id_city (PHRI)

            // Mapping ke tabel lokal (sesuaikan tipe PK Anda):
            $table->uuid('province_id')->nullable();      // provinces.id (UUID)
            $table->uuid('regency_id')->nullable();       // regencies.id (UUID)

            // Nama denormalisasi (memudahkan filter)
            $table->string('province_name')->nullable();
            $table->string('city_name')->nullable();

            // Info venue
            $table->string('hotel');               // nama hotel/venue
            $table->string('address')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->unsignedInteger('max_capacity')->default(0);

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meeting_venues');
    }
};
