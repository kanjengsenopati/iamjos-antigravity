<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static mixed  system(string $key, mixed $default = null)
 * @method static mixed  site(string $key, mixed $default = null)
 * @method static mixed  journal(string $journalId, string $key, mixed $default = null)
 * @method static void   setSystem(string $key, mixed $value, string $type = 'string')
 * @method static void   setSite(string $key, mixed $value)
 * @method static void   setJournal(string $journalId, string $key, mixed $value, string $type = 'string', string $group = 'general')
 * @method static void   flushSystem()
 * @method static void   flushSite()
 * @method static void   flushJournal(string $journalId)
 *
 * @see \App\Services\SettingsManager
 */
class Settings extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \App\Services\SettingsManager::class;
    }
}
