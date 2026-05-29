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
        // 1. Bersihkan assignment yatim piatu (orphaned) terlebih dahulu agar tidak memicu error FK
        DB::table('navigation_menu_item_assignments')
            ->whereNotNull('parent_id')
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('navigation_menu_item_assignments as parent')
                    ->whereColumn('parent.id', 'navigation_menu_item_assignments.parent_id');
            })
            ->delete();

        // 2. Tambahkan foreign key constraint pada kolom parent_id
        Schema::table('navigation_menu_item_assignments', function (Blueprint $table) {
            $table->foreign('parent_id')
                ->references('id')
                ->on('navigation_menu_item_assignments')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('navigation_menu_item_assignments', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
        });
    }
};
