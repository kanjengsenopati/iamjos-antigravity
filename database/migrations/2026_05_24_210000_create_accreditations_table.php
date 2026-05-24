<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('accreditations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name'); // e.g., "SINTA 1"
            $table->string('slug')->unique(); // e.g., "sinta-1"
            $table->string('level'); // e.g., "S1", "S2", "SC", "DJ"
            $table->string('color'); // e.g., "amber", "slate", "blue", "purple"
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Insert default accreditations
        $accreditations = [
            [
                'id' => \Illuminate\Support\Str::uuid()->toString(),
                'name' => 'SINTA 1',
                'slug' => 'sinta-1',
                'level' => 'S1',
                'color' => 'amber',
                'sort_order' => 1,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => \Illuminate\Support\Str::uuid()->toString(),
                'name' => 'SINTA 2',
                'slug' => 'sinta-2',
                'level' => 'S2',
                'color' => 'slate',
                'sort_order' => 2,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => \Illuminate\Support\Str::uuid()->toString(),
                'name' => 'Scopus Indexed',
                'slug' => 'scopus',
                'level' => 'SC',
                'color' => 'blue',
                'sort_order' => 3,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => \Illuminate\Support\Str::uuid()->toString(),
                'name' => 'DOAJ',
                'slug' => 'doaj',
                'level' => 'DJ',
                'color' => 'purple',
                'sort_order' => 4,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('accreditations')->insert($accreditations);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accreditations');
    }
};
