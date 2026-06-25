<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\SystemSetting;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        SystemSetting::firstOrCreate(
            ['key' => 'password_reset_characters'],
            [
                'value'       => 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%',
                'type'        => 'string',
                'group'       => 'app',
                'description' => 'Characters used to generate auto-generated random passwords for password resets.',
            ]
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        SystemSetting::where('key', 'password_reset_characters')->delete();
    }
};
