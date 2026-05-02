<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LegacyMapping extends Model
{
    protected $fillable = [
        'legacy_table',
        'legacy_id',
        'new_uuid',
        'new_int_id',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    /**
     * Get a mapped ID
     */
    public static function getMapping(string $table, $legacyId): ?string
    {
        return self::where('legacy_table', $table)
            ->where('legacy_id', (string) $legacyId)
            ->first()
            ?->new_uuid;
    }

    /**
     * Save a new mapping
     */
    public static function setMapping(string $table, $legacyId, string $newId, ?array $metadata = null): self
    {
        return self::updateOrCreate(
            ['legacy_table' => $table, 'legacy_id' => (string) $legacyId],
            [
                'new_uuid' => \Illuminate\Support\Str::isUuid($newId) ? $newId : null,
                'new_int_id' => is_numeric($newId) ? $newId : null,
                'metadata' => $metadata
            ]
        );
    }
}
