# Rencana Implementasi: IAMJOS Phase 2 — Metrics & Licensing

## Gambaran Umum

Implementasi dibagi menjadi dua komponen independen:
1. **Metrics via Queued Job** — Memindahkan pencatatan view/download artikel ke background job untuk mengurangi latency request.
2. **License Validation Scaffold** — Infrastruktur validasi lisensi yang dapat diaktifkan saat KAMPUS tersedia.

## Tasks

- [x] 1. Buat `RecordArticleMetricJob`
  - [x] 1.1 Buat file `app/Jobs/RecordArticleMetricJob.php`
    - Implementasikan interface `ShouldQueue` dengan trait `Queueable` (menggunakan `Illuminate\Foundation\Queue\Queueable` sesuai pola `DepositCrossrefJob`)
    - Constructor menerima: `string $submissionId`, `string $type`, `?string $ipAddress`, `?string $countryCode`, `?string $city`, `string $date`
    - Method `handle()`: lakukan `DB::table('article_metrics')->updateOrInsert()` dengan unique key `['submission_id', 'type', 'ip_address', 'date']`
    - Setelah insert berhasil, perbarui `Cache::put('queue:last_processed_at', now()->toIso8601String(), 600)`
    - Tangkap semua `\Throwable` di dalam `handle()`, log dengan `Log::error()`, dan selesaikan job tanpa re-throw (agar queue worker tidak crash)
    - _Requirements: 1.1, 1.2, 1.3, 1.6, 1.7, 1.8_

  - [ ]* 1.2 Tulis unit test untuk `RecordArticleMetricJob`
    - Test: job melakukan insert ke tabel `article_metrics` dengan data yang benar
    - Test: job memperbarui cache `queue:last_processed_at` setelah berhasil
    - Test: job menggunakan `updateOrInsert` (tidak duplikasi jika dijalankan ulang dengan data sama)
    - Test: job menangkap exception dan tidak re-throw (queue worker tidak crash)
    - _Requirements: 1.2, 1.3, 1.6, 1.7_

- [x] 2. Refactor `PublicController` — ganti insert synchronous dengan dispatch job
  - [x] 2.1 Refactor method `article()` di `app/Http/Controllers/PublicController.php`
    - Hapus blok `DB::table('article_metrics')->insert([...])` di dalam kondisi `if (!$isBot)`
    - Ganti dengan `RecordArticleMetricJob::dispatch($article->id, 'view', $ip, $countryCode, $city, now()->toDateString())`
    - Tambahkan `use App\Jobs\RecordArticleMetricJob;` di bagian import
    - _Requirements: 1.4_

  - [x] 2.2 Refactor method `downloadGalley()` di `app/Http/Controllers/PublicController.php`
    - Hapus blok `DB::table('article_metrics')->insert([...])` di dalam kondisi `if (!$isBot)`
    - Ganti dengan `RecordArticleMetricJob::dispatch($submission->id, 'download', $ip, $countryCode, $city, now()->toDateString())`
    - _Requirements: 1.5_

  - [x] 2.3 Refactor method `downloadPdf()` di `app/Http/Controllers/PublicController.php`
    - Hapus blok `DB::table('article_metrics')->insert([...])` di dalam kondisi bot-check
    - Ganti dengan `RecordArticleMetricJob::dispatch($submission->id, 'download', request()->ip(), $countryCode, $city, now()->toDateString())`
    - _Requirements: 1.5_

  - [x] 2.4 Refactor method `viewGalley()` di `app/Http/Controllers/PublicController.php`
    - Hapus blok `DB::table('article_metrics')->insert([...])` di dalam kondisi bot-check
    - Ganti dengan `RecordArticleMetricJob::dispatch($submission->id, 'download', request()->ip(), $countryCode, $city, now()->toDateString())`
    - _Requirements: 1.5_

  - [ ]* 2.5 Tulis feature test untuk refactor `PublicController`
    - Test: request artikel (non-bot) men-dispatch `RecordArticleMetricJob` (gunakan `Queue::fake()`)
    - Test: request download (non-bot) men-dispatch `RecordArticleMetricJob`
    - Test: request dari bot tidak men-dispatch job apapun
    - _Requirements: 1.4, 1.5_

- [x] 3. Checkpoint — Pastikan semua tests lulus
  - Pastikan semua tests lulus, tanyakan kepada user jika ada pertanyaan.

- [x] 4. Buat `LicenseStatus` enum
  - [x] 4.1 Buat file `app/Enums/LicenseStatus.php`
    - Definisikan PHP enum dengan case: `Valid`, `Invalid`, `Expired`, `Unregistered`, `Unchecked`
    - Implementasikan method `label(): string` — kembalikan deskripsi Bahasa Indonesia per case (contoh: `Valid` → `'Lisensi aktif dan valid'`)
    - Implementasikan method `isOperational(): bool` — kembalikan `true` hanya untuk `Valid` dan `Unchecked`
    - Implementasikan static method `fromString(string $value): self` — parse string ke case enum, fallback ke `Unchecked` jika tidak dikenali
    - _Requirements: 2.1, 2.2, 2.3, 2.4_

  - [ ]* 4.2 Tulis unit test untuk `LicenseStatus`
    - Test: setiap case memiliki `label()` yang tidak kosong
    - Test: `isOperational()` hanya `true` untuk `Valid` dan `Unchecked`
    - Test: `fromString()` mengembalikan case yang tepat untuk string yang valid
    - Test: `fromString()` fallback ke `Unchecked` untuk string tidak dikenali
    - _Requirements: 2.2, 2.3, 2.4_

- [x] 5. Buat `LicenseService`
  - [x] 5.1 Buat file `app/Services/LicenseService.php`
    - Constructor menerima `\Illuminate\Contracts\Cache\Repository $cache` dan `\Illuminate\Http\Client\Factory $http` (injectable)
    - Implementasikan method `getStatus(): LicenseStatus` sebagai entry point utama
    - Jika `IAMJOS_LICENSE_CHECK_ENABLED` adalah `false` atau tidak di-set, langsung kembalikan `LicenseStatus::Valid`
    - Jika `IAMJOS_KAMPUS_URL` kosong, kembalikan `LicenseStatus::Unchecked`
    - Cek cache `iamjos:license:status` (TTL 24 jam) — jika ada, kembalikan dari cache
    - Jika cache kosong, lakukan HTTP GET ke `IAMJOS_KAMPUS_URL` dengan header `Authorization: Bearer {IAMJOS_LICENSE_KEY}`
    - Jika KAMPUS merespons sukses, simpan status ke cache `iamjos:license:status` (TTL 24 jam) dan `iamjos:license:last_known_status` (TTL 7 hari, hanya jika `Valid`)
    - Jika KAMPUS tidak dapat dijangkau (exception atau HTTP 5xx), baca `iamjos:license:last_known_status` sebagai fallback grace period
    - Jika fallback tidak tersedia, kembalikan `LicenseStatus::Unchecked`
    - Implementasikan method `clearCache(): void` — hapus key `iamjos:license:status` dan `iamjos:license:last_known_status`
    - _Requirements: 3.1, 3.2, 3.3, 3.4, 3.5, 3.6, 3.7, 3.8, 3.9, 3.10, 3.11_

  - [x] 5.2 Daftarkan `LicenseService` sebagai singleton di `AppServiceProvider`
    - Tambahkan `$this->app->singleton(LicenseService::class)` di method `register()`
    - _Requirements: 3.9_

  - [ ]* 5.3 Tulis unit test untuk `LicenseService`
    - Test: mengembalikan `Valid` langsung jika `LICENSE_CHECK_ENABLED=false`
    - Test: mengembalikan `Unchecked` jika `kampus_url` kosong
    - Test: membaca dari cache jika tersedia (tidak melakukan HTTP request)
    - Test: melakukan HTTP request ke KAMPUS jika cache kosong
    - Test: menyimpan status ke cache setelah mendapat respons KAMPUS
    - Test: fallback ke `last_known_status` jika KAMPUS tidak dapat dijangkau
    - Test: mengembalikan `Unchecked` jika KAMPUS down dan tidak ada fallback
    - Test: `clearCache()` menghapus kedua cache key
    - _Requirements: 3.1, 3.2, 3.3, 3.4, 3.5, 3.6, 3.7, 3.8, 3.10, 3.11_

- [x] 6. Buat `CheckLicenseCommand`
  - [x] 6.1 Buat file `app/Console/Commands/CheckLicenseCommand.php`
    - Signature: `iamjos:license:check`
    - Deskripsi: `Periksa status lisensi instance IAMJOS saat ini`
    - Tambahkan option `--refresh` untuk clear cache sebelum cek
    - Inject `LicenseService` melalui constructor atau method `handle(LicenseService $service)`
    - Jika `--refresh` diberikan, panggil `$service->clearCache()` terlebih dahulu
    - Tampilkan tabel info: status lisensi, `IAMJOS_LICENSE_KEY` (sensor — tampilkan hanya 8 karakter pertama + `****`), `IAMJOS_KAMPUS_URL`, `IAMJOS_LICENSE_CHECK_ENABLED`
    - Warna output: hijau untuk `Valid`, kuning untuk `Unchecked`, merah untuk `Expired`/`Invalid`/`Unregistered`
    - Exit code: `0` untuk `Valid` dan `Unchecked`, `1` untuk status lainnya
    - _Requirements: 4.1, 4.2, 4.3, 4.4, 4.5, 4.6_

  - [ ]* 6.2 Tulis unit test untuk `CheckLicenseCommand`
    - Test: command terdaftar dengan signature `iamjos:license:check`
    - Test: output menampilkan status dan config (dengan license key tersensor)
    - Test: `--refresh` memanggil `clearCache()` sebelum `getStatus()`
    - Test: exit code `0` untuk `Valid` dan `Unchecked`
    - Test: exit code `1` untuk `Expired`, `Invalid`, `Unregistered`
    - _Requirements: 4.1, 4.2, 4.3, 4.4, 4.5, 4.6_

- [x] 7. Buat `LicenseMiddleware`
  - [x] 7.1 Buat file `app/Http/Middleware/LicenseMiddleware.php`
    - Inject `LicenseService` melalui constructor
    - Jika `IAMJOS_LICENSE_CHECK_ENABLED` adalah `false` atau tidak di-set, langsung `return $next($request)`
    - Bypass middleware untuk path `/api/health` (cek `$request->is('api/health')`)
    - Panggil `$this->licenseService->getStatus()`
    - Jika status `Valid` atau `Unchecked`, lanjutkan request dengan `return $next($request)`
    - Jika status `Expired`, `Invalid`, atau `Unregistered`, kembalikan response HTTP 402 dengan JSON body `{'message': '...', 'status': '...'}`
    - _Requirements: 5.1, 5.2, 5.3, 5.4, 5.5_

  - [x] 7.2 Daftarkan `LicenseMiddleware` sebagai named middleware di `bootstrap/app.php`
    - Tambahkan alias `'iamjos.license' => \App\Http\Middleware\LicenseMiddleware::class` di dalam `$middleware->alias([...])`
    - _Requirements: 5.6_

  - [ ]* 7.3 Tulis unit test untuk `LicenseMiddleware`
    - Test: request diteruskan jika `LICENSE_CHECK_ENABLED=false`
    - Test: request ke `/api/health` selalu diteruskan
    - Test: request diteruskan jika status `Valid`
    - Test: request diteruskan jika status `Unchecked`
    - Test: response 402 jika status `Expired`
    - Test: response 402 jika status `Invalid`
    - Test: response 402 jika status `Unregistered`
    - _Requirements: 5.1, 5.2, 5.3, 5.4, 5.5_

- [x] 8. Checkpoint — Pastikan semua tests lulus
  - Pastikan semua tests lulus, tanyakan kepada user jika ada pertanyaan.

- [x] 9. Buat `FeatureFlag` helper
  - [x] 9.1 Buat file `app/Helpers/FeatureFlag.php`
    - Definisikan class `FeatureFlag` dengan konstanta fitur: `ADVANCED_ANALYTICS`, `MULTI_JOURNAL`, `OAI_PMH`, `CROSSREF_INTEGRATION`
    - Implementasikan static method `isEnabled(string $feature): bool`
    - Jika `IAMJOS_LICENSE_CHECK_ENABLED` adalah `false` atau tidak di-set, kembalikan `true` untuk semua fitur
    - Jika status lisensi adalah `Unchecked`, kembalikan `true` (fail-open behavior)
    - Jika `LICENSE_CHECK_ENABLED=true`, baca daftar fitur yang diizinkan dari cache lisensi aktif (key `iamjos:license:features`, berupa array JSON)
    - Kembalikan `true` jika fitur ada dalam daftar, `false` jika tidak
    - _Requirements: 6.1, 6.2, 6.3, 6.4, 6.5_

  - [ ]* 9.2 Tulis unit test untuk `FeatureFlag`
    - Test: `isEnabled()` mengembalikan `true` untuk semua fitur jika `LICENSE_CHECK_ENABLED=false`
    - Test: `isEnabled()` mengembalikan `true` jika status `Unchecked` (fail-open)
    - Test: `isEnabled()` membaca dari cache fitur jika `LICENSE_CHECK_ENABLED=true`
    - Test: `isEnabled()` mengembalikan `false` untuk fitur yang tidak ada di cache
    - _Requirements: 6.1, 6.2, 6.3, 6.5_

- [x] 10. Update `.env.example`
  - [x] 10.1 Tambahkan variabel lisensi baru ke `.env.example`
    - Tambahkan `IAMJOS_LICENSE_CHECK_ENABLED=false` dengan komentar penjelasan di bawah blok `IAMJOS_LICENSE_KEY`
    - Tambahkan `IAMJOS_KAMPUS_URL=` dengan komentar bahwa nilai ini disediakan oleh tim IAMJOS
    - Perbarui komentar pada `IAMJOS_LICENSE_KEY` yang sudah ada untuk menjelaskan penggunaannya dalam validasi lisensi ke KAMPUS
    - _Requirements: 7.1, 7.2, 7.3_

- [x] 11. Checkpoint akhir — Pastikan semua tests lulus
  - Pastikan semua tests lulus, tanyakan kepada user jika ada pertanyaan.

## Catatan

- Tasks bertanda `*` bersifat opsional dan dapat dilewati untuk MVP yang lebih cepat
- Setiap task mereferensikan requirement spesifik untuk keterlacakan
- `LicenseService` dan `FeatureFlag` dirancang fail-open: jika `LICENSE_CHECK_ENABLED=false`, semua operasi berjalan normal
- Urutan implementasi penting: `LicenseStatus` → `LicenseService` → `CheckLicenseCommand` + `LicenseMiddleware` + `FeatureFlag`
