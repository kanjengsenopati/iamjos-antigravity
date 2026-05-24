# Requirements: Hardcoded to Database-Driven Settings

## Overview

This feature converts hardcoded configuration values scattered across the IAMJOS Laravel application into a unified, database-driven settings system. The system introduces a `Settings_Manager` service that provides a consistent API for reading and writing settings across three scopes: system-level (`system_settings`), site-level (`site_settings`), and journal-level (`journal_settings`). All settings are cached for performance and manageable through admin UI pages.

---

## Requirement 1

**User Story:** As a system administrator, I want a unified Settings_Manager service, so that I can read and write settings across all scopes through a consistent API without touching code.

### Acceptance Criteria

1. WHEN the Settings_Manager is asked for a setting THEN the system SHALL return the value from the appropriate scope (system, site, or journal) with a fallback default
2. WHEN a setting is written via Settings_Manager THEN the system SHALL persist it to the correct database table and invalidate the relevant cache
3. WHEN a setting is read THEN the system SHALL serve it from cache if available, otherwise query the database and populate the cache
4. WHEN a journal-scoped setting is requested THEN the system SHALL require a journal identifier to scope the lookup
5. WHEN a setting does not exist in the database THEN the system SHALL return the caller-supplied default value

---

## Requirement 2

**User Story:** As a system administrator, I want a `system_settings` table for application-wide technical configuration, so that I can manage infrastructure-level settings without editing `.env` files.

### Acceptance Criteria

1. WHEN the system_settings table is queried THEN the system SHALL store settings as key-value rows with columns: `key`, `value`, `type` (string/boolean/integer/json), `group`, and `description`
2. WHEN a system setting is created or updated THEN the system SHALL validate the value against the declared type
3. WHEN the application boots THEN the system SHALL load system settings into cache with a configurable TTL
4. WHEN system settings are updated via the admin UI THEN the system SHALL flush and rebuild the system settings cache

---

## Requirement 3

**User Story:** As a system administrator, I want the existing `site_settings` table to be extended and managed through the Settings_Manager, so that all site-level configuration is accessible via a single API.

### Acceptance Criteria

1. WHEN site settings are read THEN the system SHALL return values from the `site_settings` table (which already exists) via the Settings_Manager
2. WHEN site settings are updated THEN the system SHALL flush the `site_settings` cache key and reload
3. WHEN the site settings admin page is loaded THEN the system SHALL display all current site settings grouped by category
4. WHEN a site setting value is saved THEN the system SHALL validate input before persisting

---

## Requirement 4

**User Story:** As a journal manager, I want journal-specific settings managed through the Settings_Manager, so that each journal can have its own configuration without code changes.

### Acceptance Criteria

1. WHEN journal settings are read THEN the system SHALL scope the lookup to the specific journal by `journal_id`
2. WHEN a journal setting is written THEN the system SHALL upsert the row in `journal_settings` keyed by `(journal_id, setting_name)`
3. WHEN journal settings are cached THEN the system SHALL use a per-journal cache key (e.g., `journal_settings_{journal_id}`)
4. WHEN a journal is deleted THEN the system SHALL cascade-delete all associated journal settings

---

## Requirement 5

**User Story:** As a system administrator, I want an admin UI page to manage system settings, so that I can change technical configuration without editing files or running commands.

### Acceptance Criteria

1. WHEN a Super Admin visits the system settings page THEN the system SHALL display all system settings grouped by their `group` field
2. WHEN a Super Admin submits the system settings form THEN the system SHALL validate, persist, and cache the new values
3. WHEN an unauthorized user attempts to access the system settings page THEN the system SHALL return a 403 response
4. WHEN a system setting is updated THEN the system SHALL display a success confirmation to the administrator

---

## Requirement 6

**User Story:** As a system administrator, I want the existing site settings admin page to use the Settings_Manager, so that site-level settings are managed consistently.

### Acceptance Criteria

1. WHEN the site settings form is submitted THEN the system SHALL route the update through Settings_Manager rather than direct model calls
2. WHEN site settings are displayed THEN the system SHALL read values through Settings_Manager
3. WHEN the cache is cleared from the admin panel THEN the system SHALL also clear the site_settings cache entry

---

## Requirement 7

**User Story:** As a developer, I want all existing direct `SiteSetting::first()` calls replaced with Settings_Manager calls, so that the codebase has a single consistent way to access settings.

### Acceptance Criteria

1. WHEN any part of the application needs a site setting THEN the system SHALL use `Settings_Manager::site($key, $default)` instead of `SiteSetting::first()->$key`
2. WHEN any part of the application needs a journal setting THEN the system SHALL use `Settings_Manager::journal($journalId, $key, $default)` instead of direct `JournalSetting::get()` calls
3. WHEN the Settings_Manager is registered THEN the system SHALL be available as a Laravel facade and as a singleton in the service container

---

## Requirement 8

**User Story:** As a system administrator, I want settings changes to be reflected immediately without requiring a server restart, so that configuration updates take effect in real time.

### Acceptance Criteria

1. WHEN a setting is updated through any admin UI THEN the system SHALL invalidate only the affected cache scope (system, site, or journal-specific)
2. WHEN the cache is invalidated THEN the next request SHALL re-read from the database and repopulate the cache
3. WHEN multiple settings in the same scope are updated in one request THEN the system SHALL invalidate the cache once after all updates are applied
