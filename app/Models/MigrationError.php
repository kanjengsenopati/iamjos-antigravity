<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class MigrationError extends Model
{
    use HasUuids;

    protected $fillable = [
        'legacy_table',
        'legacy_id',
        'error_type',
        'message',
        'metadata',
        'is_fixed',
    ];

    protected $casts = [
        'metadata' => 'array',
        'is_fixed' => 'boolean',
    ];

    /**
     * Log a migration error.
     */
    public static function log(string $type, string $message, ?string $table = null, ?string $id = null, array $metadata = []): self
    {
        return self::create([
            'error_type' => $type,
            'message' => $message,
            'legacy_table' => $table,
            'legacy_id' => $id,
            'metadata' => $metadata,
        ]);
    }
}
