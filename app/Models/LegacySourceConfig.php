<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LegacySourceConfig extends Model
{
    protected $fillable = [
        'connection_name',
        'driver',
        'host',
        'port',
        'database',
        'username',
        'password',
        'base_url',
        'is_active',
        'last_synced_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_synced_at' => 'datetime',
    ];

    /**
     * Get decrypted password
     */
    public function getPasswordAttribute($value)
    {
        try {
            return decrypt($value);
        } catch (\Exception $e) {
            return $value;
        }
    }

    /**
     * Set encrypted password
     */
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = encrypt($value);
    }
}
