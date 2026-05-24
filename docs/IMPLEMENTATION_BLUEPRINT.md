# 📘 IAMJOS — Implementation Blueprint

**Versi:** 2.0  
**Terakhir diperbarui:** Mei 2026  
**Status:** Living Document — diperbarui setiap ada implementasi baru

Dokumen ini adalah blueprint master yang merangkum semua implementasi yang telah selesai dikerjakan, keputusan arsitektur, dan roadmap pengembangan selanjutnya.

---

## Daftar Isi

1. [Gambaran Arsitektur](#1-gambaran-arsitektur)
2. [Implementasi Selesai](#2-implementasi-selesai)
   - [Spec 0: Database-Driven Settings](#spec-0-database-driven-settings)
   - [Phase 1: Infrastructure](#phase-1-infrastructure)
   - [Phase 2: Article Metrics + License Scaffold](#phase-2-article-metrics--license-scaffold)
   - [Phase 3A: JATS XML Export](#phase-3a-jats-xml-export)
   - [Phase 3B: COUNTER R5 Statistics](#phase-3b-counter-r5-statistics)
   - [Phase 3C: Funding Metadata di Crossref](#phase-3c-funding-metadata-di-crossref)
   - [Phase 3D: OAI-PMH Multi-Format](#phase-3d-oai-pmh-multi-format)
   - [Phase 4A: S3 File Storage Config](#phase-4a-s3-file-storage-config)
   - [Phase 4B: Test Coverage](#phase-4b-test-coverage)
   - [Phase 4C: Error Tracking (Sentry)](#phase-4c-error-tracking-sentry)
   - [Phase 4D: Supervisor Config Documentation](#phase-4d-supervisor-config-documentation)
3. [Keputusan Arsitektur Penting](#3-keputusan-arsitektur-penting)
4. [Roadmap Selanjutnya](#4-roadmap-selanjutnya)
5. [Panduan Deployment](#5-panduan-deployment)
6. [Struktur File Kunci](#6-struktur-file-kunci)

---

## 1. Gambaran Arsitektur

IAMJOS adalah platform manajemen jurnal akademik komersial berbasis Laravel 12 yang dirancang untuk:

- **Multi-domain deployment** — satu atau banyak jurnal per server
- **Multi-server deployment** — setiap institusi bisa punya server sendiri
- **Dipantau oleh KAMPUS** (KAMPUS (Kantor Manajemen Pusat IamJOS)) — aplikasi monitoring terpisah
- **OJS-compatible** — mengikuti standar Open Journal Systems untuk interoperabilitas

```
┌─────────────────────────────────────────────────────────┐
│                  IAMJOS DEPLOYMENT MODELS                │
├─────────────────┬───────────────────┬───────────────────┤
│   SHARED VPS    │  DEDICATED VPS    │   ON-PREMISE      │
│ jurnal-a.id     │ univ-x.iamjos.id  │ Server institusi  │
│ jurnal-b.id     │ (1 server = 1     │ sendiri           │
│ jurnal-c.id     │  institusi)       │                   │
└─────────────────┴───────────────────┴───────────────────┘
         ↑                  ↑                  ↑
         └──────────────────┴──────────────────┘
                            │
                    KAMPUS (Panel Monitoring)
                    kampus.iamjos.id (aplikasi terpisah)
```

**Tech Stack:**
- PHP 8.4 + Laravel 12
- PostgreSQL 15+ (production), SQLite (development)
- Redis (cache, queue, session di production)
- Vite + Tailwind CSS 4
- GitHub Actions (CI/CD)

---

## 2. Implementasi Selesai

### Spec 0: Database-Driven Settings

**Spec path:** `.kiro/specs/hardcoded-to-database-driven/`  
**Status:** ✅ Semua task wajib selesai

#### Apa yang diimplementasikan

**SettingsManager Service** (`app/Services/SettingsManager.php`)

Unified service untuk membaca dan menulis settings dengan 3 scope:

| Scope | Method Baca | Method Tulis | Cache Key | TTL |
|-------|-------------|--------------|-----------|-----|
| System | `Settings::system($key, $default)` | `Settings::setSystem($key, $value, $type)` | `system_settings` | 3600s |
| Site | `Settings::site($key, $default)` | `Settings::setSite($key, $value)` | `site_settings` | 3600s |
| Journal | `Settings::journal($journalId, $key, $default)` | `Settings::setJournal($journalId, $key, $value)` | `journal_settings_{id}` | 900s |

**Tabel Database Baru:**
- `system_settings` — key-value store untuk konfigurasi system-wide (pagination, upload limits, API endpoints, dll.)

**Tabel yang Sudah Ada (tidak berubah schema):**
- `site_settings` — single-row, diakses via SettingsManager
- `journal_settings` — key-value per jurnal, diakses via SettingsManager

**Settings Facade** (`app/Facades/Settings.php`)
```php
// Contoh penggunaan
Settings::site('site_title', 'Default Title');
Settings::system('pagination_submissions', 10);
Settings::journal($journal->id, 'primary_color', '#4F46E5');
```

**System Settings yang Di-seed (32 keys):**

| Group | Keys |
|-------|------|
| `pagination` | `pagination_submissions`, `pagination_issues`, `pagination_journals`, `pagination_reviews`, `pagination_announcements`, `pagination_notifications`, `pagination_search_results`, `pagination_portal_journals`, `homepage_latest_articles_count`, `homepage_featured_journals_count`, `homepage_announcements_count`, `homepage_editorial_team_count`, `portal_featured_journals_count`, `portal_latest_articles_count` |
| `uploads` | `upload_max_size_manuscript`, `upload_max_size_galley`, `upload_max_size_avatar`, `upload_max_size_image`, `upload_allowed_extensions_manuscript`, `upload_allowed_extensions_galley`, `upload_allowed_extensions_avatar`, `upload_allowed_extensions_image` |
| `reviewer` | `reviewer_reminder_days_before`, `reviewer_reminder_overdue_interval_days` |
| `integrations` | `crossref_deposit_url_live`, `crossref_deposit_url_test`, `crossref_api_base_url`, `recaptcha_verify_url`, `google_scholar_search_url` |
| `app` | `maintenance_mode`, `app_version` |

**Admin UI:** `/admin/system-settings` — menampilkan semua system settings dikelompokkan per group, dengan input type yang sesuai (checkbox untuk boolean, number untuk integer, text/url untuk string).

**Migrasi Hardcoded Values:**
- Semua `SiteSetting::first()` → `Settings::site()`
- Semua `JournalSetting::get/set()` → `Settings::journal()/setJournal()`
- Stat labels fiktif (25%, 4 Weeks, 1000+) → dihapus, diganti empty string
- Placeholder announcements dan editorial team palsu → dihapus
- Mock GeoIP `'ID'` → `resolveGeoIp()` helper yang mendukung `stevebauman/location`
- `SiteContentSeeder` → data IAMJOS-spesifik diganti dengan placeholder generik
- `DemoSeeder` → ditambahkan production guard

---

### Phase 1: Infrastructure

**Spec path:** `.kiro/specs/iamjos-phase1-infrastructure/`  
**Status:** ✅ Semua task wajib selesai

#### 2.1 Health Check API

**Endpoint:** `GET /api/v1/health`  
**Akses:** Publik (tanpa autentikasi), rate limit 60 req/menit/IP

**File yang dibuat:**
```
app/Services/HealthCheck/
├── HealthCheckerInterface.php   — Interface untuk semua checker
├── CheckResult.php              — Value object hasil pemeriksaan
├── HealthStatus.php             — Enum: Healthy | Degraded | Unhealthy
├── HealthReport.php             — DTO response lengkap
├── DatabaseChecker.php          — Cek koneksi PostgreSQL
├── RedisChecker.php             — Cek koneksi Redis
├── StorageChecker.php           — Cek storage writable
└── QueueChecker.php             — Cek queue worker aktif (via cache key)

app/Http/Controllers/Api/
└── HealthCheckController.php    — Single-action controller
```

**Format Response:**
```json
{
  "status": "healthy | degraded | unhealthy",
  "timestamp": "2026-05-22T10:30:00Z",
  "version": "1.0.0",
  "uptime_seconds": 86400,
  "instance_id": "iamjos-instance-1",
  "checks": {
    "database": { "status": "ok", "message": null, "latency_ms": 12.5 },
    "redis":    { "status": "ok", "message": null, "latency_ms": 1.2 },
    "storage":  { "status": "ok", "message": null, "latency_ms": 5.0 },
    "queue":    { "status": "ok", "message": null, "latency_ms": 2.1, "last_processed_at": "..." }
  },
  "metrics": {
    "active_journals": 5,
    "pending_submissions": 12
  }
}
```

**Logika Status:**
- `healthy` — semua komponen `ok`
- `degraded` — storage atau queue `error`, tapi DB dan Redis `ok`
- `unhealthy` — DB atau Redis `error`

**HTTP Status:** 200 jika `healthy`, 503 jika `degraded` atau `unhealthy`

**Keamanan:** Pesan error tidak pernah mengekspos hostname, password, connection string, atau stack trace.

---

#### 2.2 Pembersihan Repository

**File yang dihapus:**
- SQL dump production: `db_iamjoss_*.sql.gz`, `iamjoss.sql.gz`
- Archive production: `iamjoss.id.tar.gz`
- Direktori: `scratch/`, `db_iamjoss_*/`, `iamjoss.sql/`
- Script test ad-hoc: 13 file `test_*.php` di root, `scratch_db_check.php`

**`.gitignore` diperbarui** dengan pola:
```gitignore
*.sql
*.sql.gz
*.tar.gz
/scratch/
test_*.php
scratch_*.php
.env.*
!.env.example
health-check-probe.tmp
/db_*/
```

**`SETUP.md` diperbarui:**
- Hapus klaim "open-source" → ganti dengan "commercial proprietary"
- Tambah bagian Redis Setup
- Update bagian License

---

#### 2.3 Konfigurasi Redis Production

**Arsitektur Redis Multi-Database:**

| Database | Env Var | Default | Kegunaan |
|----------|---------|---------|----------|
| DB 0 | `REDIS_DB` | `0` | Queue dan koneksi default |
| DB 1 | `REDIS_CACHE_DB` | `1` | Cache aplikasi |
| DB 2 | `REDIS_SESSION_DB` | `2` | Session pengguna |

**Isolasi antar instance** menggunakan `IAMJOS_INSTANCE_ID` sebagai prefix:
- Cache prefix: `{IAMJOS_INSTANCE_ID}-cache-`
- Queue name: `{IAMJOS_INSTANCE_ID}-default`

**File yang diperbarui:**
- `config/cache.php` — prefix berbasis Instance ID, default store `redis`
- `config/queue.php` — queue name berbasis Instance ID, default connection `redis`
- `config/session.php` — koneksi `session` terpisah, default driver `redis`
- `config/database.php` — tambah koneksi Redis `session` (DB 2)
- `app/Providers/AppServiceProvider.php` — warning jika Redis tanpa password di production
- `.env.example` — lengkap dengan semua variabel Redis + `IAMJOS_INSTANCE_ID` + `IAMJOS_LICENSE_KEY`

---

#### 2.4 CI/CD Pipeline

**File:** `.github/workflows/deploy.yml`

**3 Job Terpisah:**

```
Push ke branch → [test] → [build] → [deploy]
                   ↓
              Gagal = stop
```

**Job `test`** (semua push dan PR):
- PHP 8.4 + PostgreSQL 15 service
- `composer install` + `php artisan migrate`
- `php artisan test --parallel` (Pest)
- `vendor/bin/phpstan analyse --level=5`

**Job `build`** (hanya push, setelah test lulus):
- `composer install --no-dev --optimize-autoloader`
- `npm ci && npm run build`
- Upload artifact `build-assets`

**Job `deploy`** (hanya push, setelah build selesai):
- Branch `main` → GitHub Environment `production` → **approval manual wajib**
- Branch `staging` → GitHub Environment `staging`
- Branch `dev` → GitHub Environment `development`

**Langkah deploy production:**
1. `git pull`
2. Backup PostgreSQL → `/var/backups/iamjos/backup-YYYYMMDD-HHMMSS.sql.gz`
3. Gagal jika backup gagal (deployment dihentikan)
4. `composer install --no-dev`
5. `npm run build`
6. `php artisan migrate --force`
7. `php artisan config:cache && route:cache && view:cache`
8. Reload PHP-FPM + Nginx (graceful, zero-downtime)
9. Health check verification: `curl /api/v1/health` → gagal jika bukan 200/503
10. Hapus backup > 7 hari

**GitHub Secrets yang diperlukan:**

| Secret | Deskripsi |
|--------|-----------|
| `SERVER_IP` | IP address VPS |
| `SERVER_USER` | Username SSH |
| `SSH_PRIVATE_KEY` | Private key SSH |
| `SERVER_PORT` | Port SSH (default: 22) |
| `PROD_PATH` | Path folder production di VPS |
| `STAGING_PATH` | Path folder staging di VPS |
| `DEV_PATH` | Path folder development di VPS |

**`phpstan.neon`** — konfigurasi PHPStan level 5 untuk Laravel.

---

### Phase 2: Article Metrics + License Scaffold

**Spec path:** `.kiro/specs/iamjos-phase2-metrics-licensing/`  
**Status:** ✅ Semua task wajib selesai

#### 2.1 Article Metrics → Queued Job

`RecordArticleMetricJob` menggantikan `DB::insert()` sinkron di `PublicController` (4 lokasi). Job menggunakan `updateOrInsert` untuk idempotency dan memperbarui cache `queue:last_processed_at` setelah berhasil.

**File:**
- `app/Jobs/RecordArticleMetricJob.php`

#### 2.2 License Scaffold

Sistem lisensi dirancang **fail-open** — jika `IAMJOS_LICENSE_CHECK_ENABLED=false` (default), semua fitur aktif tanpa validasi.

| Komponen | File | Deskripsi |
|----------|------|-----------|
| `LicenseStatus` enum | `app/Enums/LicenseStatus.php` | `Valid`, `Expired`, `Invalid`, `Unchecked` |
| `LicenseService` | `app/Services/LicenseService.php` | Validasi ke KAMPUS, cache 24 jam, grace period 7 hari |
| `LicenseMiddleware` | `app/Http/Middleware/LicenseMiddleware.php` | Alias `iamjos.license` |
| `FeatureFlag` helper | `app/Helpers/FeatureFlag.php` | `FeatureFlag::enabled('feature_name')` |
| `CheckLicenseCommand` | `app/Console/Commands/CheckLicenseCommand.php` | `php artisan iamjos:license:check` |

**Config baru:** `config/iamjos.php` dengan key `kampus_url`, `license_key`, `license_check_enabled`.

**Env vars baru:** `IAMJOS_KAMPUS_URL`, `IAMJOS_LICENSE_CHECK_ENABLED`, `IAMJOS_LICENSE_KEY`.

---

### Phase 3A: JATS XML Export

**Spec path:** `.kiro/specs/iamjos-phase3a-jats-xml/`  
**Status:** ✅ Semua task wajib selesai

Implementasi ekspor JATS 1.3 XML menggunakan PHP `DOMDocument` (bukan string concatenation) untuk menjamin well-formedness.

**File yang dibuat:**

| File | Deskripsi |
|------|-----------|
| `app/Services/JatsXmlService.php` | Service utama — generate JATS 1.3 XML dari Submission |
| `app/Http/Controllers/Public/JatsXmlController.php` | Controller dengan 2 method: `article()` dan `workflowPreview()` |

**Routes:**
- `GET /{journal}/article/{article}/jats` → `journal.article.jats` (publik, hanya published)
- `GET /{journal}/workflow/{submission}/jats` → `journal.workflow.jats` (admin, preview mode)

**Fitur JatsXmlService:**
- BCP47 locale conversion (`id_ID` → `id`, `en_US` → `en`)
- Section title → article-type mapping (research-article, review-article, case-report, dll.)
- ORCID URL normalization (strip prefix duplikat)
- DOI extraction dari teks referensi via regex
- Keyword parsing: string CSV, JSON array, iterable
- Elemen opsional tidak muncul saat data null (DOI, ORCID, subtitle, license, dll.)
- `<funding-group>` dari `funding_info` JSON (Phase 3C)

---

### Phase 3B: COUNTER R5 Statistics

**Status:** ✅ Selesai

Implementasi laporan statistik sesuai standar [COUNTER Release 5](https://www.projectcounter.org/code-of-practice-five-sections/).

**File yang dibuat:**

| File | Deskripsi |
|------|-----------|
| `app/Services/CounterR5Service.php` | Service: `titleReport()` dan `itemReport()` |
| `app/Http/Controllers/Api/CounterR5Controller.php` | API controller publik |
| `app/Http/Controllers/Admin/Stats/CounterStatsController.php` | Admin panel + CSV export |
| `resources/views/journal/admin/stats/counter.blade.php` | Admin view |

**API Endpoints:**
- `GET /api/v1/counter/tr/{journal}` — Title Report (agregat per jurnal)
- `GET /api/v1/counter/ir/{journal}` — Item Report (per artikel)

**Query params:** `begin_date` (YYYY-MM), `end_date` (YYYY-MM). Default: 12 bulan terakhir. Maksimal range: 24 bulan.

**Validasi:**
- Format YYYY-MM wajib
- `begin_date` ≤ `end_date`
- Range maksimal 24 bulan (mencegah query berat)

**Response headers:** `X-COUNTER-Release: 5`, `X-Report-ID: TR/IR`

**Zero-fill:** Bulan tanpa data diisi dengan count 0 agar grafik tidak terputus.

---

### Phase 3C: Funding Metadata di Crossref

**Status:** ✅ Selesai

Menambahkan field funding ke `publications` table dan mengintegrasikannya ke Crossref XML dan JATS XML.

**Migration:** `2026_05_23_000001_add_funding_info_to_publications_table.php` — kolom `funding_info` JSON nullable.

**Format `funding_info`:**
```json
[
  {
    "funder_name": "Kementerian Pendidikan",
    "funder_doi": "10.13039/501100003093",
    "award_number": "HIBAH-2024-001"
  }
]
```

**Integrasi:**
- Crossref XML: `<fr:program>` dengan `<fr:assertion name="funder_name">` dan `<fr:assertion name="award_number">`
- JATS XML: `<funding-group>` dengan `<award-group>` per funder
- Form input: Alpine.js dynamic funder list di `submissions/show.blade.php`
- Display: sidebar artikel publik

---

### Phase 3D: OAI-PMH Multi-Format

**Status:** ✅ Selesai

Menambahkan dukungan 3 format metadata di OAI-PMH (sebelumnya hanya `oai_dc`):

| Format | Deskripsi |
|--------|-----------|
| `oai_dc` | Dublin Core — format default, sudah ada |
| `marc21` | MARC 21 XML — untuk perpustakaan |
| `rfc1807` | RFC 1807 — format teknis |

**File yang diperbarui:**
- `app/Http/Controllers/Public/OaiController.php` — `$supportedFormats` array, `metadataPrefix` diteruskan ke view
- `resources/views/journal/public/oai/list_records.blade.php` — switch format
- `resources/views/journal/public/oai/get_record.blade.php` — switch format
- `resources/views/journal/public/oai/metadata_formats.blade.php` — daftar 3 format

---

### Phase 4A: S3 File Storage Config

**Status:** ✅ Selesai

Menambahkan disk `iamjos-files` di `config/filesystems.php` yang bisa switch antara `local` dan `s3` berdasarkan env var `IAMJOS_FILES_DISK`.

```php
// config/filesystems.php
'iamjos-files' => [
    'driver' => env('IAMJOS_FILES_DISK', 'local') === 's3' ? 's3' : 'local',
    // ...
]
```

**Env vars baru:** `IAMJOS_FILES_DISK`, `AWS_ACCESS_KEY_ID`, `AWS_SECRET_ACCESS_KEY`, `AWS_DEFAULT_REGION`, `AWS_BUCKET`, `AWS_ENDPOINT`.

---

### Phase 4B: Test Coverage

**Status:** ✅ Selesai

Test suite lengkap menggunakan **Pest PHP** dengan `RefreshDatabase` trait.

| File | Jenis | Jumlah Test |
|------|-------|-------------|
| `tests/Feature/Api/HealthCheckTest.php` | Feature | 8 |
| `tests/Feature/Api/CounterR5Test.php` | Feature | 16 |
| `tests/Feature/Public/JatsXmlControllerTest.php` | Feature | 11 |
| `tests/Feature/OaiMultiFormatTest.php` | Feature | 14 |
| `tests/Unit/Services/JatsXmlServiceTest.php` | Unit | 22 |
| `tests/Unit/Services/CounterR5ServiceTest.php` | Unit | 14 |
| `tests/Unit/Services/LicenseServiceTest.php` | Unit | 8 |
| `tests/Unit/Services/SettingsManagerTest.php` | Unit | 13 |
| `tests/Unit/Enums/LicenseStatusTest.php` | Unit | 4 |
| `tests/Unit/Jobs/RecordArticleMetricJobTest.php` | Unit | 4 |

**Menjalankan test:**
```bash
php artisan test
# atau untuk single run tanpa watch mode:
php artisan test --parallel
```

---

### Phase 4C: Error Tracking (Sentry)

**Status:** ✅ Selesai (konfigurasi)

Env vars Sentry ditambahkan ke `.env.example`. Package `sentry/sentry-laravel` perlu diinstall secara manual:

```bash
composer require sentry/sentry-laravel
php artisan sentry:publish --dsn=<DSN>
```

**Env vars:** `SENTRY_LARAVEL_DSN`, `SENTRY_TRACES_SAMPLE_RATE`, `SENTRY_PROFILES_SAMPLE_RATE`.

---

### Phase 4D: Supervisor Config Documentation

**Status:** ✅ Selesai

Dokumentasi lengkap konfigurasi Supervisor untuk queue workers tersedia di `docs/SUPERVISOR_CONFIG.md`.

**Mencakup:**
- Konfigurasi multi-instance (satu Supervisor config per instance IAMJOS)
- Queue worker dengan `numprocs=2`, `autostart=true`, `autorestart=true`
- Cron scheduler untuk `php artisan schedule:run`
- Troubleshooting guide

---

### Settings: Single-row vs Key-Value

- `site_settings` — tetap single-row (tidak berubah schema) untuk backward compatibility
- `system_settings` — key-value baru untuk extensibility tanpa migration per setting
- `journal_settings` — key-value per jurnal (sudah ada, tidak berubah)

### Redis: Database Terpisah vs Prefix

Dipilih **kombinasi keduanya**:
- Database terpisah (0, 1, 2) untuk isolasi jenis data (queue, cache, session)
- Prefix `IAMJOS_INSTANCE_ID` untuk isolasi antar instance yang berbagi Redis server

### Health Check: Status Degraded

Diperkenalkan status `degraded` (selain `healthy` dan `unhealthy`) untuk membedakan:
- Komponen kritis gagal (DB, Redis) → `unhealthy` → HTTP 503
- Komponen non-kritis gagal (storage, queue) → `degraded` → HTTP 503
- Semua ok → `healthy` → HTTP 200

KAMPUS dapat membedakan apakah instance perlu immediate action atau hanya monitoring.

### CI/CD: Approval Gate

Deployment ke `production` memerlukan approval manual via GitHub Environments. Ini mencegah push tidak sengaja ke production dan memastikan ada review manusia sebelum perubahan live.

---

## 4. Roadmap Selanjutnya

### Fase 5 — Telemetry & Instance Management (Prioritas Tinggi)

| Item | Deskripsi | Estimasi |
|------|-----------|----------|
| Telemetry beacon | Kirim heartbeat ke KAMPUS setiap 5 menit | 2 hari |
| Instance registration | Daftarkan instance baru ke KAMPUS otomatis | 2 hari |
| License enforcement | Blokir fitur premium jika lisensi expired | 1 hari |
| OTA update mechanism | Update otomatis via KAMPUS | 3 hari |

### Fase 6 — Plugin & Extensibility (Roadmap)

| Item | Deskripsi |
|------|-----------|
| Plugin architecture | Sistem plugin untuk extensibility |
| Multi-language i18n | Dukungan banyak bahasa yang proper |
| Subscription management | Sistem subscription untuk jurnal berbayar |
| MODS metadata format | Tambah format MODS di OAI-PMH |

---

## 5. Panduan Deployment

### Fresh Install

```bash
# 1. Clone dan setup
git clone <repo> && cd iamjos-php
composer install
npm install
cp .env.example .env
php artisan key:generate

# 2. Konfigurasi .env
# - Set DB_CONNECTION=pgsql dan kredensial database
# - Set IAMJOS_INSTANCE_ID (unik per instance)
# - Set REDIS_PASSWORD (wajib di production)
# - Set SUPER_ADMIN_EMAIL dan SUPER_ADMIN_PASSWORD

# 3. Migrasi dan seed
php artisan migrate
php artisan db:seed

# 4. Build assets
npm run build

# 5. Cache
php artisan config:cache && php artisan route:cache && php artisan view:cache
```

### Checklist Production

- [ ] `APP_ENV=production`, `APP_DEBUG=false`
- [ ] `IAMJOS_INSTANCE_ID` diisi dengan nilai unik
- [ ] `REDIS_PASSWORD` diisi (wajib)
- [ ] `REDIS_DB`, `REDIS_CACHE_DB`, `REDIS_SESSION_DB` berbeda (0, 1, 2)
- [ ] `CACHE_STORE=redis`, `QUEUE_CONNECTION=redis`, `SESSION_DRIVER=redis`
- [ ] `SESSION_SECURE_COOKIE=true`, `SESSION_ENCRYPT=true`
- [ ] Queue worker berjalan: `php artisan queue:work --queue={INSTANCE_ID}-default`
- [ ] Scheduler berjalan: `* * * * * php artisan schedule:run`
- [ ] GitHub Environments dikonfigurasi dengan required reviewers untuk `production`
- [ ] GitHub Secrets diisi: `SERVER_IP`, `SERVER_USER`, `SSH_PRIVATE_KEY`, `PROD_PATH`

### Verifikasi Health

```bash
curl https://your-domain.com/api/v1/health
# Harus mengembalikan HTTP 200 dengan status "healthy"
```

---

## 6. Struktur File Kunci

```
iamjos-php/
├── .github/
│   └── workflows/
│       └── deploy.yml                      ← CI/CD Pipeline (3 job: test→build→deploy)
├── .kiro/
│   └── specs/
│       ├── hardcoded-to-database-driven/   ← Spec 0: Settings Manager
│       ├── iamjos-phase1-infrastructure/   ← Phase 1: Infrastructure
│       ├── iamjos-phase2-metrics-licensing/ ← Phase 2: Metrics + License
│       └── iamjos-phase3a-jats-xml/        ← Phase 3A: JATS XML
├── app/
│   ├── Console/Commands/
│   │   └── CheckLicenseCommand.php         ← php artisan iamjos:license:check
│   ├── Enums/
│   │   └── LicenseStatus.php               ← Valid | Expired | Invalid | Unchecked
│   ├── Facades/
│   │   └── Settings.php                    ← Settings Facade
│   ├── Helpers/
│   │   └── FeatureFlag.php                 ← FeatureFlag::enabled('feature')
│   ├── Http/Controllers/
│   │   ├── Admin/
│   │   │   ├── SystemSettingController.php ← Admin UI system settings
│   │   │   └── Stats/
│   │   │       └── CounterStatsController.php ← Admin COUNTER stats + CSV export
│   │   ├── Api/
│   │   │   ├── HealthCheckController.php   ← GET /api/v1/health
│   │   │   └── CounterR5Controller.php     ← GET /api/v1/counter/tr|ir/{journal}
│   │   └── Public/
│   │       ├── JatsXmlController.php       ← JATS XML download (publik + admin)
│   │       └── OaiController.php           ← OAI-PMH multi-format
│   ├── Http/Middleware/
│   │   └── LicenseMiddleware.php           ← Alias: iamjos.license
│   ├── Jobs/
│   │   └── RecordArticleMetricJob.php      ← Queued article metrics insert
│   ├── Models/
│   │   └── SystemSetting.php               ← Model untuk system_settings table
│   └── Services/
│       ├── CounterR5Service.php            ← COUNTER R5 TR + IR reports
│       ├── HealthCheck/                    ← Health Check services
│       │   ├── HealthCheckerInterface.php
│       │   ├── CheckResult.php
│       │   ├── HealthStatus.php
│       │   ├── HealthReport.php
│       │   ├── DatabaseChecker.php
│       │   ├── RedisChecker.php
│       │   ├── StorageChecker.php
│       │   └── QueueChecker.php
│       ├── JatsXmlService.php              ← JATS 1.3 XML generator (DOMDocument)
│       ├── LicenseService.php              ← License validation (fail-open)
│       └── SettingsManager.php             ← Core settings service (3 scope)
├── config/
│   ├── cache.php                           ← Redis cache + Instance ID prefix
│   ├── database.php                        ← Redis connections (default, cache, session)
│   ├── filesystems.php                     ← Disk iamjos-files (local/s3)
│   ├── iamjos.php                          ← IAMJOS-specific config (kampus_url, dll.)
│   ├── queue.php                           ← Redis queue + Instance ID prefix
│   └── session.php                         ← Redis session + separate connection
├── database/
│   ├── migrations/
│   │   ├── 2026_05_22_000001_create_system_settings_table.php
│   │   └── 2026_05_23_000001_add_funding_info_to_publications_table.php
│   └── seeders/
│       ├── SystemSettingsSeeder.php        ← 32 default system settings
│       ├── SiteContentSeeder.php           ← Neutral defaults
│       └── DemoSeeder.php                  ← Dev/staging only
├── docs/
│   ├── IMPLEMENTATION_BLUEPRINT.md         ← Dokumen ini
│   └── SUPERVISOR_CONFIG.md               ← Konfigurasi Supervisor untuk queue workers
├── resources/views/
│   ├── admin/system-settings/
│   │   └── index.blade.php                 ← Admin UI system settings
│   └── journal/
│       ├── admin/stats/
│       │   └── counter.blade.php           ← Admin COUNTER R5 stats view
│       └── public/
│           ├── article.blade.php           ← Tombol JATS XML + funding display
│           └── oai/
│               ├── list_records.blade.php  ← OAI ListRecords (multi-format)
│               ├── get_record.blade.php    ← OAI GetRecord (multi-format)
│               └── metadata_formats.blade.php ← OAI ListMetadataFormats
├── tests/
│   ├── Feature/
│   │   ├── Api/
│   │   │   ├── HealthCheckTest.php         ← 8 tests
│   │   │   └── CounterR5Test.php           ← 16 tests
│   │   ├── Public/
│   │   │   └── JatsXmlControllerTest.php   ← 11 tests
│   │   └── OaiMultiFormatTest.php          ← 14 tests
│   └── Unit/
│       ├── Enums/
│       │   └── LicenseStatusTest.php       ← 4 tests
│       ├── Jobs/
│       │   └── RecordArticleMetricJobTest.php ← 4 tests
│       └── Services/
│           ├── CounterR5ServiceTest.php    ← 14 tests
│           ├── JatsXmlServiceTest.php      ← 22 tests
│           ├── LicenseServiceTest.php      ← 8 tests
│           └── SettingsManagerTest.php     ← 13 tests
├── .env.example                            ← Template lengkap (Redis, S3, Sentry, IAMJOS)
├── .gitignore                              ← Mencegah SQL dump dan file sensitif
├── phpstan.neon                            ← Konfigurasi PHPStan level 5
└── SETUP.md                                ← Panduan instalasi (commercial proprietary)
```

---

*Dokumen ini diperbarui setiap ada implementasi baru. Untuk pertanyaan atau kontribusi, hubungi tim pengembangan IAMJOS.*
