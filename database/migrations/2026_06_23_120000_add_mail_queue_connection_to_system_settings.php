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
            ['key' => 'mail_queue_connection'],
            [
                'value'       => 'sync',
                'type'        => 'string',
                'group'       => 'email',
                'description' => 'Mail queue driver to use (sync = send immediately/cPanel, database = queue in database, redis = queue in Redis).',
            ]
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        SystemSetting::where('key', 'mail_queue_connection')->delete();
    }
};
