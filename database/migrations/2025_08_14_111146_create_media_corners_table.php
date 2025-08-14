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
        Schema::create('media_corners', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->string('video_id');
            $t->string('title');
            $t->text('description')->nullable();
            $t->string('channel')->nullable();
            $t->timestamp('published_at')->nullable();
            $t->string('url');
            $t->json('thumbnails')->nullable(); // jsonb untuk PostgreSQL juga oke
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media_corners');
    }
};
