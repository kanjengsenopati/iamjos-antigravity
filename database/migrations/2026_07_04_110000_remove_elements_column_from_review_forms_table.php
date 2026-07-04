<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Migrasikan data sisa dari kolom 'elements' di review_forms ke review_form_elements jika ada
        if (Schema::hasColumn('review_forms', 'elements')) {
            $forms = DB::table('review_forms')->whereNotNull('elements')->get();
            foreach ($forms as $form) {
                $elements = json_decode($form->elements, true);
                if (is_array($elements) && count($elements) > 0) {
                    // Cek jika review_form_elements sudah memiliki data untuk form ini
                    $exists = DB::table('review_form_elements')->where('review_form_id', $form->id)->exists();
                    if (!$exists) {
                        foreach ($elements as $index => $element) {
                            DB::table('review_form_elements')->insert([
                                'id' => (string) Str::uuid(),
                                'review_form_id' => $form->id,
                                'element_type' => $element['element_type'] ?? 'text',
                                'question' => $element['question'] ?? '',
                                'description' => $element['description'] ?? null,
                                'options' => isset($element['options']) ? json_encode($element['options']) : null,
                                'required' => $element['required'] ?? false,
                                'sequence' => $element['sequence'] ?? ($index + 1),
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                        }
                    }
                }
            }

            // 2. Hapus kolom 'elements' dari tabel 'review_forms'
            Schema::table('review_forms', function (Blueprint $table) {
                $table->dropColumn('elements');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('review_forms', function (Blueprint $table) {
            $table->json('elements')->nullable();
        });
    }
};
