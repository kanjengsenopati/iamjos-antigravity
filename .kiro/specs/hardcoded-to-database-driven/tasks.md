# Implementation Plan: Hardcoded to Database-Driven Settings

## Overview

Introduce a `SettingsManager` service and `Settings` facade that unifies all settings access across three scopes (system, site, journal). Migrate hardcoded values and direct model calls to use the new service. Add an admin UI for system settings management.

## Tasks

- [x] 1. Create `system_settings` migration, model, and seeder
  - [x] 1.1 Write migration `create_system_settings_table` with columns: `key` (unique), `value`, `type`, `group`, `description`, timestamps
    - _Requirements: 2.1_
  - [x] 1.2 Create `App\Models\SystemSetting` Eloquent model with `$fillable`, `getTypedValueAttribute` accessor for boolean/integer/json casting
    - _Requirements: 2.2_
  - [ ]* 1.3 Write unit tests for `SystemSetting::getTypedValueAttribute` covering boolean, integer, json, and string types
    - _Requirements: 2.2_
  - [x] 1.4 Create `SystemSettingsSeeder` with sensible defaults (e.g., `maintenance_mode`, `app_version`) and register it in `DatabaseSeeder`
    - _Requirements: 2.1, 2.3_

- [x] 2. Implement `SettingsManager` service
  - [x] 2.1 Create `App\Services\SettingsManager` with read methods: `system()`, `site()`, `journal()` — each checks cache first, falls back to DB, returns caller-supplied default if key absent
    - _Requirements: 1.1, 1.3, 1.5, 3.1, 4.1_
  - [x] 2.2 Add write methods: `setSystem()`, `setSite()`, `setJournal()` — each persists to DB, then invalidates only the affected scope's cache
    - _Requirements: 1.2, 2.2, 3.2, 4.2, 8.1, 8.3_
  - [x] 2.3 Add cache flush helpers: `flushSystem()`, `flushSite()`, `flushJournal(string $journalId)` using cache keys `system_settings`, `site_settings`, `journal_settings_{id}` with TTLs 3600/3600/900
    - _Requirements: 2.3, 2.4, 3.2, 4.3, 8.1, 8.2_
  - [x] 2.4 Add `setSystem()` type validation — throw `\InvalidArgumentException` for unsupported types before any DB write
    - _Requirements: 2.2_
  - [ ]* 2.5 Write unit tests for `SettingsManager`: default fallback for all three scopes, cache hit/miss behavior, correct cache key usage on read and flush
    - _Requirements: 1.3, 1.5_
  - [ ]* 2.6 Write property test — Property 1: system settings round-trip
    - Generate random string keys and string/boolean/integer values; call `setSystem()` then `system()`; assert returned value equals written value after type coercion
    - `// Feature: hardcoded-to-database-driven, Property 1: Settings round-trip (system scope)`
    - **Validates: Requirements 1.1, 1.2**
  - [ ]* 2.7 Write property test — Property 2: site settings round-trip
    - Generate random valid site setting keys; call `setSite()` then `site()`; assert equivalence
    - `// Feature: hardcoded-to-database-driven, Property 2: Settings round-trip (site scope)`
    - **Validates: Requirements 1.1, 1.2, 3.1, 3.2**
  - [ ]* 2.8 Write property test — Property 3: journal settings round-trip
    - Generate random journal IDs from seeded test journals and random setting keys/values; call `setJournal()` then `journal()`; assert equivalence
    - `// Feature: hardcoded-to-database-driven, Property 3: Settings round-trip (journal scope)`
    - **Validates: Requirements 1.1, 1.2, 4.1, 4.2**
  - [ ]* 2.9 Write property test — Property 4: cache invalidation on write
    - For each scope, write a value, write a different value, assert read returns the second value
    - `// Feature: hardcoded-to-database-driven, Property 4: Cache invalidation on write`
    - **Validates: Requirements 1.2, 8.2**
  - [ ]* 2.10 Write property test — Property 5: default fallback
    - Generate random keys guaranteed absent from DB; assert all three scope methods return the supplied default
    - `// Feature: hardcoded-to-database-driven, Property 5: Default fallback`
    - **Validates: Requirements 1.5**
  - [ ]* 2.11 Write property test — Property 6: journal scope isolation
    - Generate two distinct journal IDs with the same key; write different values; assert reading one does not return the other's value
    - `// Feature: hardcoded-to-database-driven, Property 6: Journal scope isolation`
    - **Validates: Requirements 4.1, 4.3**
  - [ ]* 2.12 Write property test — Property 7: type coercion correctness
    - For each type (boolean, integer, json), generate random values, store, read back, assert PHP native type matches declared type
    - `// Feature: hardcoded-to-database-driven, Property 7: Type coercion correctness`
    - **Validates: Requirements 2.2**
  - [ ]* 2.13 Write property test — Property 8: cache scope isolation
    - Write to one scope; assert the other two scopes' cached values remain unaffected
    - `// Feature: hardcoded-to-database-driven, Property 8: Cache scope isolation`
    - **Validates: Requirements 8.1**

- [x] 3. Register `Settings` facade and service provider
  - [x] 3.1 Create `App\Facades\Settings` facade class pointing to `SettingsManager` singleton
    - _Requirements: 7.3_
  - [x] 3.2 Register `SettingsManager` as a singleton in `AppServiceProvider` and add `Settings` alias to `config/app.php`
    - _Requirements: 7.3_

- [x] 4. Checkpoint — Ensure all tests pass, ask the user if questions arise.

- [x] 5. Replace direct `SiteSetting` and `JournalSetting` calls in controllers and services
  - [x] 5.1 Search for all `SiteSetting::first()` usages in controllers and services; replace each with `Settings::site($key, $default)`
    - _Requirements: 7.1_
  - [x] 5.2 Search for all direct `JournalSetting::` calls in controllers and services; replace each with `Settings::journal($journalId, $key, $default)` or `Settings::setJournal()`
    - _Requirements: 7.2_
  - [x] 5.3 Update `SiteAdminController::updateSettings()` to route writes through `Settings::setSite()` and flush via `Settings::flushSite()`
    - _Requirements: 6.1, 6.3_
  - [x] 5.4 Update `WebsiteSettingsController` reads/writes to use `Settings::journal()` / `Settings::setJournal()`
    - _Requirements: 4.1, 4.2_
  - [ ]* 5.5 Write unit tests confirming `SiteAdminController::updateSettings()` calls `Settings::setSite()` and `Settings::flushSite()` (mock the facade)
    - _Requirements: 6.1, 6.3_

- [x] 6. Replace hardcoded values in jobs and views
  - [x] 6.1 Identify hardcoded configuration values in job classes (e.g., WhatsApp gateway URL, sender number); replace with `Settings::site()` or `Settings::system()` calls
    - _Requirements: 7.1, 8.1_
  - [x] 6.2 Identify hardcoded values in Blade views (e.g., site title, reCAPTCHA keys); replace with `Settings::site()` calls
    - _Requirements: 7.1_

- [x] 7. Create `SystemSettingController` and admin UI
  - [x] 7.1 Create `App\Http\Controllers\Admin\SystemSettingController` with `index()` (display settings grouped by `group`) and `update(Request $request)` (validate, persist via `Settings::setSystem()`, flush cache)
    - _Requirements: 5.1, 5.2, 5.4_
  - [x] 7.2 Add route for system settings admin page protected by `role:Super Admin` middleware; register in the admin routes file
    - _Requirements: 5.3_
  - [x] 7.3 Create Blade view for system settings admin page: display settings grouped by `group`, render appropriate input type per `type` column, include CSRF-protected form
    - _Requirements: 5.1, 5.4_
  - [ ]* 7.4 Write unit tests for `SystemSettingController`: assert 403 for non-Super Admin, assert `Settings::setSystem()` is called on valid POST, assert success message is present in response
    - _Requirements: 5.2, 5.3, 5.4_

- [x] 8. Update site settings admin page to read through `Settings` facade
  - [x] 8.1 Update the existing site settings Blade view to read values via `Settings::site($key)` instead of direct model property access
    - _Requirements: 6.2_

- [x] 9. Final checkpoint — Ensure all tests pass, ask the user if questions arise.

## Notes

- Tasks marked with `*` are optional and can be skipped for a faster MVP
- Each task references specific requirements for traceability
- Property tests use [eris](https://github.com/giorgiosironi/eris) with a minimum of 100 iterations per property
- Each property test must include the comment tag `// Feature: hardcoded-to-database-driven, Property N: ...`
- The `site_settings` table schema is unchanged — `SettingsManager` wraps the existing single-row model transparently
- The `journal_settings` table schema is unchanged — existing key-value structure is reused as-is
