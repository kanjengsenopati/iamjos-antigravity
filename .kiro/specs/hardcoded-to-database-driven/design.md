# Design: Hardcoded to Database-Driven Settings

## Overview

The IAMJOS application currently manages configuration through a mix of `.env` values, hardcoded PHP constants, and direct Eloquent model calls (`SiteSetting::first()`, `JournalSetting::get()`). This design introduces a `Settings_Manager` — a unified service layer that provides a consistent, cached, scope-aware API for all application settings.

Three scopes are defined:

- **System** — application-wide technical configuration (e.g., app version string, maintenance flags). Stored in a new `system_settings` key-value table.
- **Site** — portal-level configuration (e.g., site title, WhatsApp gateway, reCAPTCHA keys). Stored in the existing `site_settings` table.
- **Journal** — per-journal configuration (e.g., review timings, appearance colors, contact info). Stored in the existing `journal_settings` table.

All reads go through a cache layer. All writes invalidate only the affected scope's cache. Admin UI pages allow Super Admins to manage system and site settings; journal managers manage journal settings through the existing website settings page.

---

## Architecture

```mermaid
graph TD
    subgraph "Application Code"
        A[Controllers / Services / Views]
    end

    subgraph "Settings_Manager Facade"
        B[SettingsManager Service]
        B1[system\(key, default\)]
        B2[site\(key, default\)]
        B3[journal\(journalId, key, default\)]
        B4[setSystem\(key, value\)]
        B5[setSite\(key, value\)]
        B6[setJournal\(journalId, key, value\)]
    end

    subgraph "Cache Layer"
        C1[system_settings cache]
        C2[site_settings cache]
        C3[journal_settings_{id} cache]
    end

    subgraph "Database"
        D1[(system_settings)]
        D2[(site_settings)]
        D3[(journal_settings)]
    end

    A --> B
    B --> B1 & B2 & B3 & B4 & B5 & B6
    B1 --> C1 --> D1
    B2 --> C2 --> D2
    B3 --> C3 --> D3
    B4 --> D1 --> C1
    B5 --> D2 --> C2
    B6 --> D3 --> C3
```

### Key Design Decisions

**Single-row vs. key-value for site_settings**: The existing `site_settings` table uses a single-row, wide-column design. This is preserved to avoid a breaking migration. The Settings_Manager wraps it transparently — reads hydrate a single model instance into cache; writes update the single row and flush the cache.

**Key-value for system_settings**: New `system_settings` table uses a key-value design (one row per setting) to allow arbitrary extensibility without schema migrations for each new setting.

**Cache TTL**: System and site settings use a long TTL (1 hour default, configurable). Journal settings use a shorter TTL (15 minutes) since they change more frequently. All caches are explicitly invalidated on write.

**Facade + Singleton**: `Settings_Manager` is registered as a singleton in the service container and exposed via a `Settings` facade, matching Laravel conventions already used in the project.

---

## Components and Interfaces

### SettingsManager Service

`App\Services\SettingsManager`

```php
class SettingsManager
{
    // Read methods
    public function system(string $key, mixed $default = null): mixed;
    public function site(string $key, mixed $default = null): mixed;
    public function journal(string $journalId, string $key, mixed $default = null): mixed;

    // Write methods
    public function setSystem(string $key, mixed $value, string $type = 'string'): void;
    public function setSite(string $key, mixed $value): void;
    public function setJournal(string $journalId, string $key, mixed $value, string $type = 'string', string $group = 'general'): void;

    // Cache management
    public function flushSystem(): void;
    public function flushSite(): void;
    public function flushJournal(string $journalId): void;
}
```

### Settings Facade

`App\Facades\Settings`

Proxies to `SettingsManager` singleton. Registered in `AppServiceProvider`.

```php
// Usage examples
Settings::site('site_title', 'My Portal');
Settings::journal($journal->id, 'primary_color', '#4F46E5');
Settings::setSystem('maintenance_mode', false, 'boolean');
```

### SystemSettingController

`App\Http\Controllers\Admin\SystemSettingController`

- `index()` — display system settings grouped by `group`
- `update(Request $request)` — validate, persist via `Settings_Manager`, flush cache

### Updated SiteAdminController

Existing `updateSettings()` method routes writes through `Settings::setSite()` instead of direct model calls. Cache flush calls `Settings::flushSite()`.

### Updated WebsiteSettingsController

Existing journal settings reads/writes route through `Settings::journal()` / `Settings::setJournal()`.

---

## Data Models

### system_settings (new table)

| Column | Type | Notes |
|---|---|---|
| `id` | bigint PK auto-increment | |
| `key` | varchar(255) unique | Setting identifier, e.g. `maintenance_mode` |
| `value` | longtext nullable | Stored as string; cast on read |
| `type` | varchar(20) default `string` | `string`, `boolean`, `integer`, `json` |
| `group` | varchar(100) default `general` | For UI grouping |
| `description` | text nullable | Human-readable description for admin UI |
| `created_at` | timestamp | |
| `updated_at` | timestamp | |

Migration: `create_system_settings_table`

### site_settings (existing — no schema change)

Already has all required columns. The Settings_Manager wraps the existing single-row model. No migration needed.

Current columns: `id` (uuid), `site_title`, `site_intro`, `about_content`, `footer_content`, `min_password_length`, `redirect_to_journal`, `use_ojs_url_format`, `wa_api_url`, `wa_sender_number`, `wa_device_id`, `recaptcha_site_key`, `recaptcha_secret_key`, `timestamps`.

### journal_settings (existing — no schema change)

Already has the correct key-value structure with `journal_id`, `setting_name`, `setting_value`, `setting_type`, `group`. No migration needed.

### SystemSetting Model

`App\Models\SystemSetting`

```php
class SystemSetting extends Model
{
    protected $fillable = ['key', 'value', 'type', 'group', 'description'];

    public function getTypedValueAttribute(): mixed
    {
        return match ($this->type) {
            'boolean' => filter_var($this->value, FILTER_VALIDATE_BOOLEAN),
            'integer' => (int) $this->value,
            'json'    => json_decode($this->value, true),
            default   => $this->value,
        };
    }
}
```

### Cache Key Convention

| Scope | Cache Key | TTL |
|---|---|---|
| System | `system_settings` | 3600s (1 hour) |
| Site | `site_settings` | 3600s (1 hour) |
| Journal | `journal_settings_{journal_id}` | 900s (15 min) |

---

## Correctness Properties

*A property is a characteristic or behavior that should hold true across all valid executions of a system — essentially, a formal statement about what the system should do. Properties serve as the bridge between human-readable specifications and machine-verifiable correctness guarantees.*

### Property 1: Settings round-trip (system scope)

*For any* valid system setting key and string/boolean/integer value, writing the value via `Settings_Manager::setSystem()` and then reading it back via `Settings_Manager::system()` should return an equivalent value (after type coercion).

**Validates: Requirements 1.1, 1.2**

### Property 2: Settings round-trip (site scope)

*For any* valid site setting key and value, writing the value via `Settings_Manager::setSite()` and then reading it back via `Settings_Manager::site()` should return an equivalent value.

**Validates: Requirements 1.1, 1.2, 3.1, 3.2**

### Property 3: Settings round-trip (journal scope)

*For any* journal ID and valid setting key and value, writing via `Settings_Manager::setJournal()` and reading back via `Settings_Manager::journal()` should return an equivalent value.

**Validates: Requirements 1.1, 1.2, 4.1, 4.2**

### Property 4: Cache invalidation on write

*For any* scope (system, site, or journal), writing a value, then writing a different value, then reading should return the second written value — not the first cached value.

**Validates: Requirements 1.2, 8.2**

### Property 5: Default fallback

*For any* setting key that does not exist in the database, `Settings_Manager` should return the caller-supplied default value regardless of scope (system, site, or journal).

**Validates: Requirements 1.5**

### Property 6: Journal scope isolation

*For any* two distinct journal IDs and the same setting key, writing a value for one journal should not affect the value returned for the other journal.

**Validates: Requirements 4.1, 4.3**

### Property 7: Type coercion correctness

*For any* system setting stored with type `boolean`, `integer`, or `json`, reading the value back should return a PHP value of the correct native type (not a raw string).

**Validates: Requirements 2.2**

### Property 8: Cache scope isolation

*For any* write to one scope (system, site, or journal), the cached values of the other two scopes should remain unaffected.

**Validates: Requirements 8.1**

---

## Error Handling

- **Missing setting**: Returns the caller-supplied default. Never throws.
- **Cache unavailable**: Falls back to direct database query. Logs a warning.
- **Database unavailable**: Propagates the exception; no silent swallowing of DB errors.
- **Invalid type on write**: Throws `\InvalidArgumentException` with a descriptive message before any DB write.
- **Unauthorized access to admin pages**: Handled by existing `role:Super Admin` middleware; returns 403.
- **Journal not found on journal-scoped write**: Throws `\InvalidArgumentException` if the journal ID does not exist (validated at controller layer before reaching the service).

---

## Testing Strategy

### Unit Tests

Unit tests cover specific examples and edge cases:

- `SettingsManager::system()` returns default when key is absent
- `SettingsManager::site()` returns default when key is absent
- `SettingsManager::journal()` returns default when key is absent
- Type coercion: boolean `'1'` → `true`, `'0'` → `false`
- Type coercion: integer `'42'` → `42`
- Type coercion: json `'{"a":1}'` → `['a' => 1]`
- `SystemSettingController::update()` returns 403 for non-Super Admin
- Cache is called with the correct key on read
- Cache is flushed with the correct key on write

### Property-Based Tests

Property tests use [**eris**](https://github.com/giorgiosironi/eris) for PHP property-based testing. Minimum 100 iterations per property.

Each property test is tagged with a comment referencing the design property:

```
// Feature: hardcoded-to-database-driven, Property {N}: {property_text}
```

**Property 1 — System settings round-trip**
Generate random string keys and string/boolean/integer values. For each: call `setSystem()`, then `system()`. Assert returned value equals written value (after type coercion).

**Property 2 — Site settings round-trip**
Generate random valid site setting keys (from the known column set). For each: call `setSite()`, then `site()`. Assert equivalence.

**Property 3 — Journal settings round-trip**
Generate random journal IDs (from seeded test journals) and random setting keys/values. For each: call `setJournal()`, then `journal()`. Assert equivalence.

**Property 4 — Cache invalidation on write**
For any scope, write a value, then write a different value. Assert that reading after the second write returns the second value (not the first cached value).

**Property 5 — Default fallback**
Generate random keys guaranteed not to exist in the database. Assert that `system()`, `site()`, and `journal()` all return the supplied default.

**Property 6 — Journal scope isolation**
Generate two distinct journal IDs and the same key. Write different values for each. Assert that reading journal A's value does not return journal B's value.

**Property 7 — Type coercion correctness**
For each supported type (`boolean`, `integer`, `json`), generate random values of that type, store them, read them back, and assert the PHP type of the returned value matches the declared type.

**Property 8 — Cache scope isolation**
Write a value to one scope (e.g., system). Assert that the site and journal caches are not flushed (their previously cached values remain intact).
