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
        Schema::create('home_ads', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('media_type')->default('image'); // 'image' or 'video'
            $table->string('media_url'); // URL to the image or video
            $table->string('link')->nullable(); // Link to redirect when ad is clicked
            $table->integer('order')->default(0); // Order of the ad
            $table->boolean('is_active')->default(true); // Whether the ad is active
            $table->timestamps();
            $table->softDeletes(); // For soft deletion support
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('home_ads');
    }
};
