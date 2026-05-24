<?php

namespace App\Services;

use App\Models\JournalSetting;
use App\Models\SiteSetting;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Cache;

class SettingsManager
{
    // -------------------------------------------------------------------------
    // Read methods
    // -------------------------------------------------------------------------

    /**
     * Get a system-scoped setting value.
     *
     * Loads all system settings into cache on first miss (associative array
     * keyed by setting key, values are typed via getTypedValueAttribute).
     * Cache TTL: 3600 seconds.
     *
     * @param  string  $key
     * @param  mixed   $default  Returned when the key is absent from the DB.
     * @return mixed
     */
    public function system(string $key, mixed $default = null): mixed
    {
        $settings = Cache::remember('system_settings', 3600, function () {
            return SystemSetting::all()
                ->mapWithKeys(fn ($setting) => [$setting->key => $setting->typed_value])
                ->toArray();
        });

        return array_key_exists($key, $settings) ? $settings[$key] : $default;
    }

    /**
     * Get a site-scoped setting value.
     *
     * Caches the single SiteSetting row as an attribute array.
     * Cache TTL: 3600 seconds.
     *
     * @param  string  $key
     * @param  mixed   $default  Returned when the key is absent from the model.
     * @return mixed
     */
    public function site(string $key, mixed $default = null): mixed
    {
        $attributes = Cache::remember('site_settings', 3600, function () {
            $model = SiteSetting::first();

            return $model ? $model->toArray() : [];
        });

        return array_key_exists($key, $attributes) ? $attributes[$key] : $default;
    }

    /**
     * Get a journal-scoped setting value.
     *
     * Caches all settings for the given journal as an associative array
     * keyed by setting_name. Cache TTL: 900 seconds.
     *
     * @param  string  $journalId
     * @param  string  $key
     * @param  mixed   $default  Returned when the key is absent.
     * @return mixed
     */
    public function journal(string $journalId, string $key, mixed $default = null): mixed
    {
        $cacheKey = "journal_settings_{$journalId}";

        $settings = Cache::remember($cacheKey, 900, function () use ($journalId) {
            return JournalSetting::getAllForJournal($journalId);
        });

        return array_key_exists($key, $settings) ? $settings[$key] : $default;
    }

    // -------------------------------------------------------------------------
    // Write methods
    // -------------------------------------------------------------------------

    /**
     * Persist a system-scoped setting and invalidate the system cache.
     *
     * Supported types: string, boolean, integer, json.
     * Values are serialized to string before storage.
     *
     * @param  string  $key
     * @param  mixed   $value
     * @param  string  $type  One of: string, boolean, integer, json.
     * @throws \InvalidArgumentException  When $type is not supported.
     */
    public function setSystem(string $key, mixed $value, string $type = 'string'): void
    {
        $this->validateType($type);

        $serialized = match ($type) {
            'boolean' => $value ? '1' : '0',
            'integer' => (string) $value,
            'json'    => json_encode($value),
            default   => (string) $value,
        };

        SystemSetting::updateOrCreate(
            ['key' => $key],
            ['value' => $serialized, 'type' => $type]
        );

        $this->flushSystem();
    }

    /**
     * Persist a site-scoped setting and invalidate the site cache.
     *
     * Updates the single SiteSetting row.
     *
     * @param  string  $key
     * @param  mixed   $value
     */
    public function setSite(string $key, mixed $value): void
    {
        $model = SiteSetting::first();

        if ($model) {
            $model->update([$key => $value]);
        }

        $this->flushSite();
    }

    /**
     * Persist a journal-scoped setting and invalidate that journal's cache.
     *
     * @param  string  $journalId
     * @param  string  $key
     * @param  mixed   $value
     * @param  string  $type
     * @param  string  $group
     */
    public function setJournal(string $journalId, string $key, mixed $value, string $type = 'string', string $group = 'general'): void
    {
        JournalSetting::set($journalId, $key, $value, $type, $group);

        $this->flushJournal($journalId);
    }

    // -------------------------------------------------------------------------
    // Cache flush helpers
    // -------------------------------------------------------------------------

    /**
     * Invalidate the system settings cache.
     */
    public function flushSystem(): void
    {
        Cache::forget('system_settings');
    }

    /**
     * Invalidate the site settings cache.
     */
    public function flushSite(): void
    {
        Cache::forget('site_settings');
    }

    /**
     * Invalidate the cache for a specific journal's settings.
     *
     * @param  string  $journalId
     */
    public function flushJournal(string $journalId): void
    {
        Cache::forget("journal_settings_{$journalId}");
    }

    // -------------------------------------------------------------------------
    // Internal helpers
    // -------------------------------------------------------------------------

    /**
     * Validate that the given type is one of the supported setting types.
     *
     * @param  string  $type
     * @throws \InvalidArgumentException
     */
    private function validateType(string $type): void
    {
        $allowed = ['string', 'boolean', 'integer', 'json'];

        if (! in_array($type, $allowed, true)) {
            throw new \InvalidArgumentException(
                "Unsupported setting type: {$type}. Allowed: string, boolean, integer, json"
            );
        }
    }
}
