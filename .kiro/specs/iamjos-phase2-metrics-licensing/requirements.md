# Dokumen Requirements

## Pendahuluan

Fitur ini mencakup dua komponen yang saling independen untuk meningkatkan performa dan kesiapan komersialisasi platform IAMJOS.

**Komponen 1 — Article Metrics via Queued Job**: Saat ini pencatatan view dan download artikel dilakukan secara synchronous dalam request cycle di `PublicController`, menyebabkan penambahan latency pada setiap request artikel. Komponen ini memindahkan operasi insert ke background job menggunakan Laravel Queue, sehingga response time tidak terpengaruh oleh operasi database metrics.

**Komponen 2 — License Validation System (Scaffold)**: IAMJOS adalah platform komersial yang akan divalidasi lisensinya oleh KAMPUS (Kantor Manajemen Pusat IamJOS) — sebuah aplikasi monitoring terpisah yang belum dibangun. Komponen ini menyiapkan infrastruktur validasi lisensi yang dapat diaktifkan saat KAMPUS tersedia, tanpa mengganggu operasional instance yang sudah berjalan.

## Glosarium

- **IAMJOS**: Platform manajemen jurnal akademik berbasis Laravel 12 yang di-deploy ke banyak domain/server berbeda.
- **KAMPUS (Kantor Manajemen Pusat IamJOS)**: Aplikasi monitoring terpusat yang akan dibangun di masa depan untuk mengelola lisensi semua instance IAMJOS.
- **RecordArticleMetricJob**: Laravel queued job yang bertanggung jawab mencatat satu event metrik artikel ke database.
- **LicenseService**: Service class yang mengelola validasi dan caching status lisensi IAMJOS.
- **LicenseStatus**: PHP enum yang merepresentasikan status lisensi: `Valid`, `Invalid`, `Expired`, `Unregistered`, `Unchecked`.
- **CheckLicenseCommand**: Artisan command untuk memeriksa dan menampilkan status lisensi instance saat ini.
- **LicenseMiddleware**: HTTP middleware yang memvalidasi lisensi sebelum memproses request, hanya aktif jika dikonfigurasi.
- **FeatureFlag**: Helper class untuk memeriksa ketersediaan fitur berdasarkan paket lisensi aktif.
- **QueueChecker**: Service yang memeriksa kesehatan queue worker melalui Health Check API.
- **PublicController**: Controller yang menangani halaman publik termasuk tampilan dan unduhan artikel.
- **Grace Period**: Periode toleransi di mana instance tetap beroperasi menggunakan status lisensi cache terakhir ketika KAMPUS tidak dapat dijangkau.
- **article_metrics**: Tabel database yang menyimpan data view dan download artikel.
- **queue:last_processed_at**: Cache key Redis yang diperbarui setiap kali job berhasil diproses, digunakan oleh QueueChecker.

---

## Requirements

### Requirement 1: Queued Job untuk Pencatatan Metrik Artikel

**User Story:** Sebagai pengunjung platform IAMJOS, saya ingin halaman artikel dan unduhan merespons dengan cepat, sehingga pengalaman membaca tidak terganggu oleh operasi pencatatan statistik di background.

#### Acceptance Criteria

1. THE `RecordArticleMetricJob` SHALL menerima parameter: `submission_id` (string UUID), `type` (string: `view` atau `download`), `ip_address` (string nullable), `country_code` (string nullable), `city` (string nullable), dan `date` (string format `Y-m-d`).

2. WHEN `RecordArticleMetricJob` dieksekusi, THE `RecordArticleMetricJob` SHALL melakukan insert satu baris ke tabel `article_metrics` dengan kolom: `submission_id`, `type`, `ip_address`, `country_code`, `city`, `date`, `created_at`, `updated_at`.

3. WHEN `RecordArticleMetricJob` berhasil dieksekusi, THE `RecordArticleMetricJob` SHALL memperbarui cache key `queue:last_processed_at` dengan timestamp ISO 8601 saat ini menggunakan TTL 10 menit.

4. WHEN `PublicController` mendeteksi request view artikel bukan dari bot, THE `PublicController` SHALL mendispatch `RecordArticleMetricJob` sebagai pengganti `DB::table('article_metrics')->insert()` yang synchronous.

5. WHEN `PublicController` mendeteksi request download artikel bukan dari bot, THE `PublicController` SHALL mendispatch `RecordArticleMetricJob` sebagai pengganti `DB::table('article_metrics')->insert()` yang synchronous.

6. THE `RecordArticleMetricJob` SHALL menggunakan `updateOrInsert` atau mekanisme idempotency berbasis kombinasi `submission_id`, `type`, `ip_address`, dan `date` untuk mencegah duplikasi data jika job dijalankan ulang.

7. IF `RecordArticleMetricJob` melempar exception saat eksekusi, THEN THE `RecordArticleMetricJob` SHALL menangkap exception tersebut, mencatatnya ke Laravel log, dan menyelesaikan job tanpa melempar ulang exception sehingga queue worker tidak crash.

8. THE `RecordArticleMetricJob` SHALL mengimplementasikan interface `ShouldQueue` dan menggunakan trait `Dispatchable`, `InteractsWithQueue`, `Queueable`, `SerializesModels`.

9. WHEN `QueueChecker` memeriksa kesehatan queue, THE `QueueChecker` SHALL membaca cache key `queue:last_processed_at` yang diperbarui oleh `RecordArticleMetricJob` untuk menentukan apakah queue worker aktif dalam 5 menit terakhir.

---

### Requirement 2: LicenseStatus Enum

**User Story:** Sebagai developer IAMJOS, saya ingin representasi status lisensi yang type-safe, sehingga penanganan berbagai kondisi lisensi konsisten di seluruh codebase.

#### Acceptance Criteria

1. THE `LicenseStatus` enum SHALL mendefinisikan case: `Valid`, `Invalid`, `Expired`, `Unregistered`, `Unchecked`.

2. THE `LicenseStatus` enum SHALL menyediakan method `label(): string` yang mengembalikan deskripsi human-readable dalam Bahasa Indonesia untuk setiap case.

3. THE `LicenseStatus` enum SHALL menyediakan method `isOperational(): bool` yang mengembalikan `true` hanya untuk case `Valid` dan `Unchecked`.

4. THE `LicenseStatus` enum SHALL menyediakan method `fromString(string $value): self` sebagai static method untuk membuat instance dari string, dengan fallback ke `Unchecked` jika string tidak dikenali.

---

### Requirement 3: LicenseService

**User Story:** Sebagai sistem IAMJOS, saya ingin service terpusat untuk validasi lisensi, sehingga logika pengecekan lisensi tidak tersebar di berbagai bagian aplikasi.

#### Acceptance Criteria

1. THE `LicenseService` SHALL menyimpan status lisensi di Redis cache dengan key `iamjos:license:status` dan TTL 24 jam.

2. WHEN `LicenseService` diminta status lisensi dan cache tersedia, THE `LicenseService` SHALL mengembalikan `LicenseStatus` dari cache tanpa melakukan request ke KAMPUS.

3. WHEN `LicenseService` diminta status lisensi dan cache kosong, THE `LicenseService` SHALL melakukan HTTP request ke endpoint KAMPUS menggunakan `IAMJOS_KAMPUS_URL` dan `IAMJOS_LICENSE_KEY` untuk memvalidasi lisensi.

4. WHEN KAMPUS mengembalikan respons valid, THE `LicenseService` SHALL menyimpan `LicenseStatus` yang sesuai ke cache dengan TTL 24 jam.

5. IF KAMPUS tidak dapat dijangkau (timeout, connection refused, atau HTTP error 5xx), THEN THE `LicenseService` SHALL membaca cache key `iamjos:license:last_known_status` sebagai fallback.

6. IF cache `iamjos:license:last_known_status` tersedia dan usianya tidak lebih dari 7 hari, THEN THE `LicenseService` SHALL mengembalikan status dari cache tersebut (grace period).

7. IF cache `iamjos:license:last_known_status` tidak tersedia atau sudah lebih dari 7 hari, THEN THE `LicenseService` SHALL mengembalikan `LicenseStatus::Unchecked`.

8. WHEN `LicenseService` berhasil mendapatkan status `Valid` dari KAMPUS, THE `LicenseService` SHALL memperbarui cache key `iamjos:license:last_known_status` dengan TTL 7 hari.

9. THE `LicenseService` SHALL menyediakan method `getStatus(): LicenseStatus` sebagai entry point utama untuk mendapatkan status lisensi.

10. THE `LicenseService` SHALL menyediakan method `clearCache(): void` untuk menghapus semua cache key terkait lisensi, digunakan saat refresh manual diperlukan.

11. IF `IAMJOS_LICENSE_CHECK_ENABLED` bernilai `false` atau tidak di-set, THEN THE `LicenseService` SHALL mengembalikan `LicenseStatus::Valid` tanpa melakukan pengecekan apapun.

---

### Requirement 4: CheckLicenseCommand

**User Story:** Sebagai administrator sistem IAMJOS, saya ingin dapat memeriksa status lisensi instance melalui Artisan command, sehingga saya dapat mendiagnosis masalah lisensi tanpa harus mengakses database atau Redis secara manual.

#### Acceptance Criteria

1. THE `CheckLicenseCommand` SHALL terdaftar sebagai Artisan command dengan signature `iamjos:license:check`.

2. WHEN `CheckLicenseCommand` dijalankan, THE `CheckLicenseCommand` SHALL menampilkan status lisensi saat ini, nilai `IAMJOS_LICENSE_KEY` (disensor — hanya 8 karakter pertama yang ditampilkan), nilai `IAMJOS_KAMPUS_URL`, dan nilai `IAMJOS_LICENSE_CHECK_ENABLED`.

3. WHEN `CheckLicenseCommand` dijalankan dengan option `--refresh`, THE `CheckLicenseCommand` SHALL memanggil `LicenseService::clearCache()` sebelum mengambil status terbaru dari KAMPUS.

4. WHEN status lisensi adalah `Valid`, THE `CheckLicenseCommand` SHALL menampilkan output dengan warna hijau dan exit code 0.

5. WHEN status lisensi adalah `Expired`, `Invalid`, atau `Unregistered`, THE `CheckLicenseCommand` SHALL menampilkan output dengan warna merah dan exit code 1.

6. WHEN status lisensi adalah `Unchecked`, THE `CheckLicenseCommand` SHALL menampilkan output dengan warna kuning dan exit code 0.

---

### Requirement 5: LicenseMiddleware

**User Story:** Sebagai operator platform IAMJOS, saya ingin middleware yang dapat diaktifkan untuk memblokir akses ketika lisensi tidak valid, sehingga platform dapat dibatasi penggunaannya sesuai ketentuan lisensi komersial.

#### Acceptance Criteria

1. IF `IAMJOS_LICENSE_CHECK_ENABLED` bernilai `false` atau tidak di-set, THEN THE `LicenseMiddleware` SHALL meneruskan semua request tanpa melakukan pengecekan lisensi apapun.

2. WHILE `IAMJOS_LICENSE_CHECK_ENABLED` bernilai `true`, THE `LicenseMiddleware` SHALL memanggil `LicenseService::getStatus()` untuk setiap request yang masuk.

3. WHEN status lisensi adalah `Valid` atau `Unchecked`, THE `LicenseMiddleware` SHALL meneruskan request ke handler berikutnya.

4. WHEN status lisensi adalah `Expired`, `Invalid`, atau `Unregistered`, THE `LicenseMiddleware` SHALL mengembalikan HTTP response 402 dengan pesan yang menjelaskan kondisi lisensi.

5. IF request yang masuk adalah request ke endpoint Health Check API (`/api/health`), THEN THE `LicenseMiddleware` SHALL meneruskan request tersebut tanpa pengecekan lisensi.

6. THE `LicenseMiddleware` SHALL terdaftar sebagai named middleware dengan alias `iamjos.license` sehingga dapat diterapkan secara selektif pada route group.

---

### Requirement 6: FeatureFlag Helper

**User Story:** Sebagai developer IAMJOS, saya ingin helper untuk memeriksa ketersediaan fitur berdasarkan paket lisensi, sehingga fitur premium dapat dikontrol secara terpusat tanpa logika kondisional yang tersebar.

#### Acceptance Criteria

1. THE `FeatureFlag` helper SHALL menyediakan static method `isEnabled(string $feature): bool` untuk memeriksa apakah fitur tertentu aktif.

2. IF `IAMJOS_LICENSE_CHECK_ENABLED` bernilai `false` atau tidak di-set, THEN THE `FeatureFlag::isEnabled()` SHALL mengembalikan `true` untuk semua fitur tanpa pengecekan.

3. WHEN `FeatureFlag::isEnabled()` dipanggil dan `IAMJOS_LICENSE_CHECK_ENABLED` bernilai `true`, THE `FeatureFlag` SHALL membaca daftar fitur yang diizinkan dari cache lisensi aktif.

4. THE `FeatureFlag` helper SHALL mendefinisikan konstanta atau enum untuk nama-nama fitur yang dikenali, minimal: `ADVANCED_ANALYTICS`, `MULTI_JOURNAL`, `OAI_PMH`, `CROSSREF_INTEGRATION`.

5. IF status lisensi adalah `Unchecked`, THEN THE `FeatureFlag::isEnabled()` SHALL mengembalikan `true` untuk semua fitur (fail-open behavior).

---

### Requirement 7: Konfigurasi Environment

**User Story:** Sebagai developer atau operator yang melakukan deployment IAMJOS, saya ingin variabel environment yang terdokumentasi dengan jelas untuk fitur lisensi, sehingga konfigurasi dapat dilakukan dengan benar tanpa harus membaca source code.

#### Acceptance Criteria

1. THE `.env.example` SHALL mendefinisikan variabel `IAMJOS_LICENSE_CHECK_ENABLED` dengan nilai default `false` beserta komentar penjelasan.

2. THE `.env.example` SHALL mendefinisikan variabel `IAMJOS_KAMPUS_URL` dengan nilai kosong beserta komentar yang menjelaskan bahwa nilai ini disediakan oleh tim IAMJOS.

3. THE `.env.example` SHALL memperbarui komentar pada variabel `IAMJOS_LICENSE_KEY` yang sudah ada untuk menjelaskan penggunaannya dalam konteks validasi lisensi ke KAMPUS.

4. WHEN `IAMJOS_LICENSE_CHECK_ENABLED` tidak di-set di environment, THE sistem SHALL memperlakukannya sebagai `false` (fitur lisensi nonaktif).

5. WHEN `IAMJOS_KAMPUS_URL` tidak di-set dan `IAMJOS_LICENSE_CHECK_ENABLED` bernilai `true`, THE `LicenseService` SHALL mengembalikan `LicenseStatus::Unchecked` tanpa mencoba melakukan HTTP request.
