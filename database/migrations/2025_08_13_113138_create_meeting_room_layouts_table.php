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
        Schema::create('meeting_room_layouts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('meeting_room_id');
            $table->string('layout'); // contoh: theater, classroom, boardroom, dll (snake_case)
            $table->unsignedInteger('capacity')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meeting_room_layouts');
    }
};
