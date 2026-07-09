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
        Schema::create('production_assignments', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('submission_id')->index();
            $table->uuid('production_user_id')->index(); // The assigned production staff
            $table->uuid('assigned_by')->nullable(); // Who assigned them

            $table->string('role')->nullable(); // layout_editor, proofreader, etc.
            $table->string('status')->default('assigned'); // assigned, completed, cancelled
            
            $table->timestamp('date_assigned')->useCurrent();
            $table->timestamp('date_notified')->nullable();
            $table->timestamp('date_completed')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Foreign keys
            $table->foreign('submission_id')
                ->references('id')
                ->on('submissions')
                ->onDelete('cascade');

            $table->foreign('production_user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->foreign('assigned_by')
                ->references('id')
                ->on('users')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('production_assignments');
    }
};
