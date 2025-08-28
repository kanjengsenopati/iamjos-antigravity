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
        Schema::table('meeting_room_layouts', function (Blueprint $table) {
            $table->uuid('meeting_room_type_id')->after('id')->nullable();
            $table->dropColumn('layout');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('meeting_room_layouts', function (Blueprint $table) {
            $table->dropColumn('meeting_room_type_id');
            $table->string('layout')->after('id')->nullable();
        });
    }
};
