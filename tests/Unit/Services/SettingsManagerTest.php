<?php

/**
 * Test untuk SettingsManager
 *
 * Memverifikasi bahwa:
 * - Scope system: baca/tulis ke system_settings table
 * - Scope site: baca/tulis ke site_settings table
 * - Scope journal: baca/tulis ke journal_settings table per journal
 * - Cache digunakan dan di-invalidate dengan benar
 * - Default value dikembalikan jika key tidak ada
 * - Settings Facade berfungsi sebagai proxy ke SettingsManager
 */

use App\Facades\Settings;
use App\Models\Journal;
use App\Services\SettingsManager;
use Illuminate\Support\Facades\Cache;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

describe('SettingsManager — Scope System', function () {

    beforeEach(function () {
        Cache::flush(); // Clear cache between tests
    });

    it('mengembalikan default jika key tidak ada', function () {
        $manager = app(SettingsManager::class);

        $value = $manager->system('key_tidak_ada', 'default_value');

        expect($value)->toBe('default_value');
    });

    it('dapat menyimpan dan membaca nilai string', function () {
        $manager = app(SettingsManager::class);

        $manager->setSystem('test_key', 'test_value', 'string');
        $value = $manager->system('test_key');

        expect($value)->toBe('test_value');
    });

    it('dapat menyimpan dan membaca nilai integer', function () {
        $manager = app(SettingsManager::class);

        $manager->setSystem('pagination_test', 25, 'integer');
        $value = $manager->system('pagination_test');

        expect($value)->toBe(25);
    });

    it('dapat menyimpan dan membaca nilai boolean', function () {
        $manager = app(SettingsManager::class);

        $manager->setSystem('feature_enabled', true, 'boolean');
        $value = $manager->system('feature_enabled');

        expect($value)->toBeTrue();
    });

    it('menginvalidasi cache setelah setSystem', function () {
        $manager = app(SettingsManager::class);

        // Baca pertama kali untuk mengisi cache
        $manager->system('cache_test_key', 'old_value');

        // Update nilai
        $manager->setSystem('cache_test_key', 'new_value', 'string');

        // Baca lagi — harus mendapat nilai baru
        $value = $manager->system('cache_test_key');
        expect($value)->toBe('new_value');
    });

    it('mengembalikan null sebagai default jika tidak ada default yang diberikan', function () {
        $manager = app(SettingsManager::class);

        $value = $manager->system('key_yang_pasti_tidak_ada');

        expect($value)->toBeNull();
    });

});

describe('SettingsManager — Scope Site', function () {

    beforeEach(function () {
        // Pastikan ada satu row di site_settings untuk test write
        if (!\App\Models\SiteSetting::exists()) {
            \App\Models\SiteSetting::create(['site_title' => 'Test Site']);
        }
        Cache::flush(); // Clear cache between tests
    });

    it('mengembalikan default jika key tidak ada', function () {
        $manager = app(SettingsManager::class);

        $value = $manager->site('site_key_tidak_ada', 'site_default');

        expect($value)->toBe('site_default');
    });

    it('dapat menyimpan dan membaca nilai site setting', function () {
        $manager = app(SettingsManager::class);

        $manager->setSite('site_title', 'IAMJOS Platform');
        $value = $manager->site('site_title');

        expect($value)->toBe('IAMJOS Platform');
    });

    it('menginvalidasi cache setelah setSite', function () {
        $manager = app(SettingsManager::class);

        $manager->setSite('site_title', 'Nilai Lama');
        $manager->site('site_title'); // isi cache

        $manager->setSite('site_title', 'Nilai Baru');
        $value = $manager->site('site_title');

        expect($value)->toBe('Nilai Baru');
    });

});

describe('SettingsManager — Scope Journal', function () {

    beforeEach(function () {
        Cache::flush(); // Clear cache between tests
    });

    it('mengembalikan default jika key tidak ada untuk journal tertentu', function () {
        $journal = Journal::factory()->create();
        $manager = app(SettingsManager::class);

        $value = $manager->journal($journal->id, 'journal_key_tidak_ada', 'journal_default');

        expect($value)->toBe('journal_default');
    });

    it('dapat menyimpan dan membaca nilai journal setting', function () {
        $journal = Journal::factory()->create();
        $manager = app(SettingsManager::class);

        $manager->setJournal($journal->id, 'primary_color', '#4F46E5');
        $value = $manager->journal($journal->id, 'primary_color');

        expect($value)->toBe('#4F46E5');
    });

    it('isolasi antar journal — setting journal A tidak bocor ke journal B', function () {
        $journalA = Journal::factory()->create();
        $journalB = Journal::factory()->create();
        $manager  = app(SettingsManager::class);

        $manager->setJournal($journalA->id, 'shared_key', 'nilai_A');
        $manager->setJournal($journalB->id, 'shared_key', 'nilai_B');

        expect($manager->journal($journalA->id, 'shared_key'))->toBe('nilai_A');
        expect($manager->journal($journalB->id, 'shared_key'))->toBe('nilai_B');
    });

    it('menginvalidasi cache journal yang benar setelah setJournal', function () {
        $journal = Journal::factory()->create();
        $manager = app(SettingsManager::class);

        $manager->setJournal($journal->id, 'journal_cache_test', 'lama');
        $manager->journal($journal->id, 'journal_cache_test'); // isi cache

        $manager->setJournal($journal->id, 'journal_cache_test', 'baru');
        $value = $manager->journal($journal->id, 'journal_cache_test');

        expect($value)->toBe('baru');
    });

    it('tidak menginvalidasi cache journal lain saat update satu journal', function () {
        $journalA = Journal::factory()->create();
        $journalB = Journal::factory()->create();
        $manager  = app(SettingsManager::class);

        $manager->setJournal($journalA->id, 'key_a', 'nilai_a');
        $manager->setJournal($journalB->id, 'key_b', 'nilai_b');

        // Baca keduanya untuk mengisi cache
        $manager->journal($journalA->id, 'key_a');
        $manager->journal($journalB->id, 'key_b');

        // Update journal A
        $manager->setJournal($journalA->id, 'key_a', 'nilai_a_baru');

        // Journal B harus tetap tidak berubah
        expect($manager->journal($journalB->id, 'key_b'))->toBe('nilai_b');
    });

});

describe('Settings Facade', function () {

    beforeEach(function () {
        Cache::flush(); // Clear cache between tests
        // Ensure site_settings row exists
        if (!\App\Models\SiteSetting::exists()) {
            \App\Models\SiteSetting::create(['site_title' => 'Test Site']);
        }
    });

    it('Settings::system() berfungsi sebagai proxy ke SettingsManager::system()', function () {
        Settings::setSystem('facade_test', 'facade_value', 'string');

        expect(Settings::system('facade_test'))->toBe('facade_value');
    });

    it('Settings::site() berfungsi sebagai proxy ke SettingsManager::site()', function () {
        Settings::setSite('site_title', 'facade_site_value');

        expect(Settings::site('site_title'))->toBe('facade_site_value');
    });

    it('Settings::journal() berfungsi sebagai proxy ke SettingsManager::journal()', function () {
        $journal = Journal::factory()->create();

        Settings::setJournal($journal->id, 'facade_journal_test', 'facade_journal_value');

        expect(Settings::journal($journal->id, 'facade_journal_test'))->toBe('facade_journal_value');
    });

    it('Settings::system() mengembalikan default jika key tidak ada', function () {
        $value = Settings::system('key_tidak_ada_sama_sekali', 'default_facade');

        expect($value)->toBe('default_facade');
    });

});
