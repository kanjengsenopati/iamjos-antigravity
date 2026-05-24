# Rencana Implementasi: IAMJOS Phase 1 Infrastructure

## Ikhtisar

Implementasi empat area fondasi infrastruktur IAMJOS: Health Check API, perbaikan CI/CD Pipeline, pembersihan repository dari file sensitif, dan konfigurasi Redis yang aman untuk production multi-instance.

## Tasks

- [x] 1. Buat struktur data dan interface Health Check
  - Buat direktori `app/Services/HealthCheck/`
  - Buat interface `HealthCheckerInterface` dengan method `check(): CheckResult`
  - Buat value object `CheckResult` dengan factory methods `ok()` dan `error()`
  - Buat enum `HealthStatus` dengan case `Healthy`, `Degraded`, `Unhealthy`
  - Buat DTO `HealthReport` dengan method `toArray()` dan `httpStatus()`
  - _Requirements: 1.4_

- [x] 2. Implementasi checker services
  - [x] 2.1 Implementasi `DatabaseChecker`
    - Jalankan `DB::select('SELECT 1')` dengan timeout 500ms
    - Tangkap `QueryException` dan `PDOException`
    - Pastikan pesan error tidak mengekspos kredensial (hostname, password, connection string)
    - _Requirements: 1.5_

  - [ ]* 2.2 Tulis property test untuk `DatabaseChecker`
    - **Property 3: Status komponen mencerminkan kegagalan**
    - **Validates: Requirements 1.5**

  - [x] 2.3 Implementasi `RedisChecker`
    - Jalankan `Redis::ping()` dengan timeout 500ms
    - Tangkap `RedisException` dan `ConnectionException`
    - _Requirements: 1.6_

  - [x] 2.4 Implementasi `StorageChecker`
    - Tulis file temporary ke `storage/app/` menggunakan `Storage::put()`
    - Hapus file temporary setelah pengecekan
    - Tangkap `IOException` dan permission errors
    - _Requirements: 1.7_

  - [x] 2.5 Implementasi `QueueChecker`
    - Baca cache key `queue:last_processed_at` untuk mendapatkan timestamp terakhir queue worker aktif
    - Bandingkan dengan `now()->subMinutes(5)`
    - Kembalikan `"error"` jika tidak ada aktivitas dalam 5 menit terakhir
    - _Requirements: 1.8_

  - [ ]* 2.6 Tulis unit tests untuk semua checker services
    - Test `DatabaseChecker` dengan mock koneksi gagal
    - Test `RedisChecker` dengan mock Redis timeout
    - Test `StorageChecker` dengan mock storage tidak dapat ditulis
    - Test `QueueChecker` dengan berbagai skenario timestamp
    - _Requirements: 1.5, 1.6, 1.7, 1.8_

- [x] 3. Implementasi `HealthCheckController`
  - [x] 3.1 Buat `App\Http\Controllers\Api\HealthCheckController` sebagai single-action controller
    - Jalankan semua checker (DatabaseChecker, RedisChecker, StorageChecker, QueueChecker)
    - Agregasi hasil dan tentukan `HealthStatus` keseluruhan:
      - `healthy` jika semua komponen `"ok"`
      - `degraded` jika storage atau queue `"error"` tapi database dan Redis `"ok"`
      - `unhealthy` jika database atau Redis `"error"`
    - Bangun `HealthReport` dengan semua field wajib termasuk `metrics.active_journals` dan `metrics.pending_submissions`
    - Tangkap semua exception tidak tertangani, kembalikan 503 tanpa stack trace
    - _Requirements: 1.2, 1.3, 1.4, 1.10, 1.12_

  - [ ]* 3.2 Tulis property test untuk `HealthCheckController` — HTTP status mencerminkan status kesehatan
    - **Property 1: HTTP Status Mencerminkan Status Kesehatan**
    - **Validates: Requirements 1.2, 1.3**
    - Generate kombinasi acak komponen sehat/gagal, verifikasi HTTP status code konsisten

  - [ ]* 3.3 Tulis property test untuk `HealthCheckController` — struktur response selalu lengkap
    - **Property 2: Struktur Response Selalu Lengkap**
    - **Validates: Requirements 1.4, 1.10**
    - Generate kondisi sistem acak, verifikasi semua field wajib selalu ada dalam response

  - [ ]* 3.4 Tulis property test untuk `HealthCheckController` — exception tidak mengekspos stack trace
    - **Property 6: Exception Tidak Mengekspos Stack Trace**
    - **Validates: Requirements 1.12**
    - Simulasikan exception tidak tertangani, verifikasi response 503 tanpa stack trace atau informasi internal

- [x] 4. Daftarkan route Health Check API
  - Tambahkan route `GET /api/v1/health` di `routes/api.php` yang mengarah ke `HealthCheckController`
  - Terapkan middleware `throttle:60,1` pada route tersebut
  - Pastikan tidak ada middleware autentikasi pada route ini
  - _Requirements: 1.1, 1.9_

  - [ ]* 4.1 Tulis property test untuk rate limiting
    - **Property 4: Rate Limiting Konsisten**
    - **Validates: Requirements 1.9**
    - Kirim 61+ request dari IP yang sama, verifikasi request ke-61 mendapat 429 dengan header `Retry-After`

  - [ ]* 4.2 Tulis unit tests untuk Health Check API secara end-to-end
    - Test endpoint dapat diakses tanpa autentikasi
    - Test response 200 ketika semua komponen sehat (mock semua checker)
    - Test response 503 ketika masing-masing komponen gagal
    - Test semua field wajib ada dalam response
    - Test header `Content-Type: application/json` selalu ada
    - _Requirements: 1.1, 1.2, 1.3, 1.4, 1.10_

- [x] 5. Checkpoint — Verifikasi Health Check API
  - Pastikan semua tests Health Check API lulus, tanyakan kepada user jika ada pertanyaan.

- [x] 6. Pembersihan repository dari file sensitif
  - [x] 6.1 Hapus file-file sensitif dari repository
    - Hapus `db_iamjoss_20260429_013003_pgsql_data.sql.gz` jika ada
    - Hapus `iamjoss.sql.gz` jika ada
    - Hapus `iamjoss.id.tar.gz` jika ada
    - Hapus direktori `db_iamjoss_20260429_013003_pgsql_data.sql/` jika ada
    - Hapus direktori `iamjoss.sql/` jika ada
    - Hapus direktori `scratch/` beserta seluruh isinya jika ada
    - Hapus file `test_*.php` di direktori root jika ada
    - Hapus file `scratch_db_check.php` jika ada
    - _Requirements: 3.1, 3.3, 3.4_

  - [x] 6.2 Perbarui `.gitignore` dengan entri untuk file sensitif
    - Tambahkan pola `*.sql`, `*.sql.gz`, `*.tar.gz` untuk mencegah SQL dump ter-commit
    - Tambahkan `/scratch/` untuk mencegah direktori scratch ter-commit
    - Tambahkan `test_*.php` dan `scratch_*.php` untuk mencegah script ad-hoc ter-commit
    - Tambahkan `.env` dan `.env.*` (kecuali `!.env.example`) untuk environment files
    - _Requirements: 3.2, 3.5, 3.8_

  - [ ]* 6.3 Tulis property test untuk `.gitignore`
    - **Property 7: .gitignore Mencegah File Sensitif**
    - **Validates: Requirements 3.2, 3.5, 3.8**
    - Generate nama file acak dengan pola sensitif (`*.sql`, `*.sql.gz`, `*.tar.gz`, `.env.*`)
    - Verifikasi `git check-ignore` mengembalikan bahwa file tersebut diabaikan

- [x] 7. Perbarui `SETUP.md` dengan branding komersial dan dokumentasi Redis
  - Hapus kalimat yang menyebut IAMJOS sebagai "open-source"
  - Ganti dengan: "IamJOS is a commercial proprietary academic journal management platform"
  - Perbarui bagian License untuk mencerminkan lisensi komersial
  - Tambahkan bagian "Redis Setup" yang mencakup: instalasi Redis, konfigurasi password, konfigurasi multiple database, dan verifikasi koneksi
  - _Requirements: 3.6, 3.7, 4.8_

  - [ ]* 7.1 Tulis unit test untuk verifikasi konten `SETUP.md`
    - Test `SETUP.md` tidak mengandung kata "open-source"
    - Test `SETUP.md` mengandung informasi lisensi komersial
    - _Requirements: 3.6, 3.7_

- [x] 8. Konfigurasi Redis untuk production multi-instance
  - [x] 8.1 Perbarui `config/cache.php` dengan prefix berbasis Instance ID
    - Set `prefix` menggunakan `env('CACHE_PREFIX', env('IAMJOS_INSTANCE_ID', 'iamjos') . '-cache-')`
    - Set `CACHE_STORE` default ke `redis`
    - _Requirements: 4.4, 4.10_

  - [x] 8.2 Perbarui `config/queue.php` dengan nama queue berbasis Instance ID
    - Set `queue` pada koneksi `redis` menggunakan `env('IAMJOS_INSTANCE_ID', 'iamjos') . '-default'`
    - Set `QUEUE_CONNECTION` default ke `redis`
    - _Requirements: 4.5_

  - [x] 8.3 Perbarui `config/session.php` untuk menggunakan koneksi Redis terpisah
    - Set `connection` ke `env('SESSION_CONNECTION', 'session')`
    - Set `SESSION_DRIVER` default ke `redis`
    - _Requirements: 4.6_

  - [x] 8.4 Tambahkan koneksi Redis `session` di `config/database.php`
    - Tambahkan koneksi `session` pada array `redis` yang menggunakan `REDIS_SESSION_DB` (default: `2`)
    - Pastikan `REDIS_DB` (default: `0`), `REDIS_CACHE_DB` (default: `1`), dan `REDIS_SESSION_DB` (default: `2`) berbeda
    - _Requirements: 4.6, 4.10_

  - [ ]* 8.5 Tulis property test untuk isolasi database Redis
    - **Property 10: Isolasi Database Redis**
    - **Validates: Requirements 4.6, 4.10**
    - Verifikasi nilai `REDIS_DB`, `REDIS_CACHE_DB`, dan `REDIS_SESSION_DB` selalu berbeda satu sama lain

  - [ ]* 8.6 Tulis property test untuk cache prefix mengandung Instance ID
    - **Property 8: Cache Prefix Mengandung Instance ID**
    - **Validates: Requirements 4.4**
    - Generate nilai `IAMJOS_INSTANCE_ID` acak, verifikasi prefix cache selalu mengandung nilai tersebut

  - [ ]* 8.7 Tulis property test untuk queue name mengandung Instance ID
    - **Property 9: Queue Name Mengandung Instance ID**
    - **Validates: Requirements 4.5**
    - Generate nilai `IAMJOS_INSTANCE_ID` acak, verifikasi nama queue selalu mengandung nilai tersebut sebagai prefix

- [x] 9. Tambahkan warning Redis tanpa password di production
  - Di `app/Providers/AppServiceProvider.php`, tambahkan pengecekan di method `boot()`
  - Jika `app()->environment('production')` dan `REDIS_PASSWORD` kosong, catat `Log::warning()` dengan pesan yang jelas
  - _Requirements: 4.9_

  - [ ]* 9.1 Tulis unit test untuk warning Redis tanpa password
    - **Property 11: Warning Redis Tanpa Password di Production**
    - **Validates: Requirements 4.9**
    - Test bahwa warning dicatat ke log saat environment production dengan REDIS_PASSWORD kosong
    - Test bahwa warning tidak dicatat saat environment non-production

- [x] 10. Perbarui `.env.example` dengan konfigurasi Redis lengkap
  - Tambahkan section `IAMJOS INSTANCE CONFIGURATION` dengan `IAMJOS_INSTANCE_ID` dan `IAMJOS_LICENSE_KEY` beserta komentar penjelasan
  - Tambahkan section `REDIS CONFIGURATION` dengan semua variabel: `REDIS_CLIENT`, `REDIS_HOST`, `REDIS_PASSWORD`, `REDIS_PORT`, `REDIS_DB`, `REDIS_CACHE_DB`, `REDIS_SESSION_DB`
  - Set nilai default `CACHE_STORE=redis`, `QUEUE_CONNECTION=redis`, `SESSION_DRIVER=redis`
  - Sertakan komentar yang menjelaskan kegunaan setiap variabel dan peringatan keamanan
  - _Requirements: 4.1, 4.2, 4.3, 4.7_

- [x] 11. Checkpoint — Verifikasi konfigurasi Redis dan pembersihan repository
  - Pastikan semua tests Redis configuration lulus, tanyakan kepada user jika ada pertanyaan.

- [x] 12. Buat CI/CD Pipeline `.github/workflows/deploy.yml`
  - [x] 12.1 Buat job `test` yang berjalan pada semua push dan pull request
    - Setup PHP 8.4 dengan ekstensi `pgsql`, `redis`, `gd`, `zip`
    - Cache Composer dependencies menggunakan `actions/cache`
    - Jalankan `php artisan test` (Pest)
    - Jalankan `./vendor/bin/phpstan analyse --level=5`
    - _Requirements: 2.2, 2.3_

  - [x] 12.2 Buat job `build` yang bergantung pada job `test`
    - Jalankan `composer install --no-dev --optimize-autoloader`
    - Jalankan `npm ci && npm run build`
    - Upload artifact `build-assets`
    - _Requirements: 2.1, 2.11_

  - [x] 12.3 Buat job `deploy` yang bergantung pada job `build`
    - Konfigurasi agar hanya berjalan pada push ke branch `main`, `staging`, atau `dev`
    - Untuk branch `main`: gunakan GitHub Environment `production` dengan required reviewers (approval gate)
    - Implementasi langkah backup PostgreSQL sebelum migration: `pg_dump | gzip > backup-$(date).sql.gz`
    - Hentikan deployment jika `pg_dump` gagal (exit code non-zero)
    - Jalankan `php artisan migrate --force` setelah backup berhasil
    - Jalankan verifikasi health check: `curl -f GET /api/v1/health` setelah migration
    - Tandai job gagal dan kirim notifikasi jika health check tidak mengembalikan 200
    - Upload artifact backup dengan `retention-days: 7`
    - _Requirements: 2.1, 2.5, 2.6, 2.7, 2.8, 2.9, 2.10_

- [x] 13. Checkpoint akhir — Verifikasi semua komponen terintegrasi
  - Pastikan semua tests lulus, verifikasi route health check terdaftar dengan benar, tanyakan kepada user jika ada pertanyaan.

## Catatan

- Tasks bertanda `*` bersifat opsional dan dapat dilewati untuk MVP yang lebih cepat
- Setiap task mereferensikan requirements spesifik untuk keterlacakan
- Checkpoint memastikan validasi inkremental di setiap tahap
- Property tests memvalidasi properti kebenaran universal
- Unit tests memvalidasi contoh spesifik dan edge cases
- Semua pesan error di Health Check API tidak boleh mengekspos kredensial atau informasi internal sistem
