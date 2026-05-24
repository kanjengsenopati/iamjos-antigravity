# Dokumen Persyaratan

## Pendahuluan

IAMJOS (I Am Journal of Science) adalah platform manajemen jurnal akademik komersial berbasis Laravel 12 yang di-deploy ke banyak domain/server berbeda. Fase 1 infrastruktur ini mencakup empat area penting: Health Check API untuk pemantauan oleh KAMPUS (Kantor Manajemen Pusat IamJOS), perbaikan pipeline CI/CD, pembersihan repository dari file sensitif, dan konfigurasi Redis yang aman untuk production.

Keempat item ini bersifat fondasi — memastikan platform dapat dipantau, di-deploy dengan aman, bebas dari data sensitif di repository, dan berjalan optimal di production.

## Glosarium

- **IAMJOS**: Platform manajemen jurnal akademik komersial yang menjadi subjek pengembangan ini
- **KAMPUS (KAMPUS (Kantor Manajemen Pusat IamJOS))**: Aplikasi eksternal terpisah yang memantau satu atau lebih instance IAMJOS
- **Instance**: Satu deployment IAMJOS pada satu domain/server tertentu
- **Health_Check_API**: Endpoint publik `GET /api/v1/health` yang melaporkan status instance IAMJOS
- **CI_CD_Pipeline**: Alur otomasi GitHub Actions untuk testing, build, dan deployment
- **Redis**: In-memory data store yang digunakan untuk cache, queue, dan session di production
- **PHPStan**: Alat analisis statis untuk kode PHP
- **Approval_Gate**: Langkah manual dalam pipeline yang memerlukan persetujuan manusia sebelum deploy ke production
- **IAMJOS_INSTANCE_ID**: Identifier unik untuk setiap instance IAMJOS yang terdaftar di KAMPUS
- **IAMJOS_LICENSE_KEY**: Kunci lisensi komersial yang memvalidasi instance IAMJOS

---

## Persyaratan

### Persyaratan 1: Health Check API Endpoint

**User Story:** Sebagai operator KAMPUS, saya ingin dapat melakukan polling ke endpoint health check setiap instance IAMJOS, sehingga saya dapat memantau status dan ketersediaan semua instance secara terpusat.

#### Kriteria Penerimaan

1. THE Health_Check_API SHALL menyediakan endpoint `GET /api/v1/health` yang dapat diakses tanpa autentikasi.

2. WHEN KAMPUS mengirim request `GET /api/v1/health`, THE Health_Check_API SHALL mengembalikan response JSON dengan HTTP status 200 jika semua komponen berstatus sehat.

3. WHEN KAMPUS mengirim request `GET /api/v1/health` dan satu atau lebih komponen tidak sehat, THE Health_Check_API SHALL mengembalikan response JSON dengan HTTP status 503.

4. THE Health_Check_API SHALL menyertakan field-field berikut dalam setiap response:
   - `status`: nilai `"healthy"` atau `"degraded"` atau `"unhealthy"`
   - `timestamp`: waktu response dalam format ISO 8601 UTC
   - `version`: versi aplikasi IAMJOS
   - `uptime_seconds`: jumlah detik sejak aplikasi terakhir di-restart
   - `instance_id`: nilai dari `IAMJOS_INSTANCE_ID`
   - `checks.database.status`: `"ok"` atau `"error"`
   - `checks.queue.status`: `"ok"` atau `"error"`
   - `checks.storage.status`: `"ok"` atau `"error"`
   - `checks.redis.status`: `"ok"` atau `"error"`
   - `metrics.active_journals`: jumlah jurnal dengan status aktif
   - `metrics.pending_submissions`: jumlah submission dengan status pending

5. WHEN koneksi database tidak dapat dijangkau, THE Health_Check_API SHALL mengisi `checks.database.status` dengan nilai `"error"` dan menyertakan pesan error yang tidak mengekspos kredensial.

6. WHEN koneksi Redis tidak dapat dijangkau, THE Health_Check_API SHALL mengisi `checks.redis.status` dengan nilai `"error"`.

7. WHEN direktori storage tidak dapat ditulis, THE Health_Check_API SHALL mengisi `checks.storage.status` dengan nilai `"error"`.

8. WHEN queue worker tidak aktif selama lebih dari 5 menit, THE Health_Check_API SHALL mengisi `checks.queue.status` dengan nilai `"error"`.

9. WHILE satu IP address mengirim lebih dari 60 request per menit ke Health_Check_API, THE Health_Check_API SHALL mengembalikan HTTP status 429 dengan header `Retry-After`.

10. THE Health_Check_API SHALL mengembalikan header `Content-Type: application/json` pada setiap response.

11. THE Health_Check_API SHALL menyelesaikan setiap request dalam waktu kurang dari 2000ms meskipun semua komponen diperiksa.

12. IF terjadi exception yang tidak tertangani saat pemeriksaan komponen, THEN THE Health_Check_API SHALL mengembalikan HTTP status 503 dengan `status: "unhealthy"` tanpa mengekspos stack trace.

---

### Persyaratan 2: Perbaikan CI/CD Pipeline

**User Story:** Sebagai developer IAMJOS, saya ingin pipeline deployment otomatis yang menjalankan test dan analisis statis sebelum deploy, memerlukan persetujuan manual untuk production, dan memverifikasi kesehatan aplikasi setelah deploy, sehingga risiko deployment yang merusak production dapat diminimalkan.

#### Kriteria Penerimaan

1. THE CI_CD_Pipeline SHALL memisahkan proses menjadi tiga job berurutan: `test`, `build`, dan `deploy`.

2. WHEN ada push atau pull request ke branch manapun, THE CI_CD_Pipeline SHALL menjalankan job `test` yang mengeksekusi `php artisan test` secara lengkap.

3. WHEN ada push atau pull request ke branch manapun, THE CI_CD_Pipeline SHALL menjalankan PHPStan static analysis pada level minimum 5 sebagai bagian dari job `test`.

4. IF job `test` gagal, THEN THE CI_CD_Pipeline SHALL menghentikan pipeline dan tidak melanjutkan ke job `build` atau `deploy`.

5. WHEN job `test` berhasil dan target deployment adalah environment `production`, THE CI_CD_Pipeline SHALL memerlukan persetujuan manual dari minimal satu reviewer yang terdaftar sebelum job `deploy` dieksekusi.

6. WHEN job `deploy` akan dieksekusi pada environment `production`, THE CI_CD_Pipeline SHALL menjalankan backup database PostgreSQL sebelum menjalankan `php artisan migrate`.

7. WHEN backup database gagal pada environment `production`, THE CI_CD_Pipeline SHALL menghentikan deployment dan tidak menjalankan migration.

8. WHEN `php artisan migrate` berhasil dieksekusi, THE CI_CD_Pipeline SHALL menjalankan verifikasi health check dengan melakukan request ke `GET /api/v1/health` pada instance yang baru di-deploy.

9. IF health check setelah deploy mengembalikan status selain 200, THEN THE CI_CD_Pipeline SHALL menandai job `deploy` sebagai gagal dan mengirim notifikasi.

10. THE CI_CD_Pipeline SHALL menyimpan artifact backup database selama minimal 7 hari.

11. WHEN job `build` dieksekusi, THE CI_CD_Pipeline SHALL menjalankan `composer install --no-dev --optimize-autoloader` dan `npm run build` untuk menghasilkan asset production.

---

### Persyaratan 3: Pembersihan Repository dari File Sensitif

**User Story:** Sebagai maintainer IAMJOS, saya ingin repository bebas dari SQL dump, file scratch, dan klaim open-source yang tidak akurat, sehingga keamanan data dan integritas branding komersial platform terjaga.

#### Kriteria Penerimaan

1. THE Repository SHALL tidak mengandung file `db_iamjoss_20260429_013003_pgsql_data.sql.gz`, `iamjoss.sql.gz`, dan `iamjoss.id.tar.gz` setelah pembersihan dilakukan.

2. THE Repository SHALL memiliki entri `.gitignore` yang mencegah file dengan pola `*.sql`, `*.sql.gz`, `*.tar.gz` di root repository ter-commit ke dalam repository.

3. THE Repository SHALL tidak mengandung file dengan pola `test_*.php` di direktori root.

4. THE Repository SHALL tidak mengandung direktori `scratch/` beserta seluruh isinya.

5. WHEN developer mencoba melakukan `git add` pada file SQL dump baru, THE Repository SHALL menolak penambahan file tersebut karena tercakup oleh aturan `.gitignore`.

6. THE Repository SHALL memiliki file `SETUP.md` yang tidak mengandung klaim bahwa IAMJOS adalah perangkat lunak open-source.

7. WHERE `SETUP.md` sebelumnya menyebutkan lisensi open-source, THE Repository SHALL mengganti keterangan tersebut dengan informasi lisensi komersial yang akurat.

8. THE Repository SHALL memiliki `.gitignore` yang juga mencakup pola untuk file environment (`.env`, `.env.*` kecuali `.env.example`) agar tidak ter-commit.

---

### Persyaratan 4: Konfigurasi Redis untuk Production

**User Story:** Sebagai system administrator yang men-deploy IAMJOS ke production, saya ingin konfigurasi Redis yang lengkap dan aman tersedia sebagai default, sehingga saya dapat mengkonfigurasi instance baru dengan benar tanpa harus mencari dokumentasi tambahan.

#### Kriteria Penerimaan

1. THE Repository SHALL memiliki file `.env.example` yang menyertakan konfigurasi Redis lengkap dengan placeholder yang jelas untuk: `REDIS_HOST`, `REDIS_PASSWORD`, `REDIS_PORT`, `REDIS_DB`, dan `REDIS_CACHE_DB`.

2. THE Repository SHALL memiliki file `.env.example` yang menyertakan placeholder `IAMJOS_INSTANCE_ID` dengan komentar yang menjelaskan bahwa nilai ini harus unik per instance.

3. THE Repository SHALL memiliki file `.env.example` yang menyertakan placeholder `IAMJOS_LICENSE_KEY` dengan komentar yang menjelaskan cara mendapatkan kunci lisensi.

4. WHEN `CACHE_DRIVER` di-set ke `redis`, THE Cache_Config SHALL menggunakan prefix yang menyertakan nilai `IAMJOS_INSTANCE_ID` untuk menghindari konflik antar instance yang berbagi Redis server yang sama.

5. WHEN `QUEUE_CONNECTION` di-set ke `redis`, THE Queue_Config SHALL menggunakan nama queue yang menyertakan nilai `IAMJOS_INSTANCE_ID` sebagai prefix.

6. WHEN `SESSION_DRIVER` di-set ke `redis`, THE Session_Config SHALL menggunakan database Redis yang terpisah dari database yang digunakan untuk cache.

7. THE Repository SHALL memiliki nilai default `CACHE_DRIVER=redis`, `QUEUE_CONNECTION=redis`, dan `SESSION_DRIVER=redis` di dalam `.env.example`.

8. THE Repository SHALL memiliki dokumentasi setup Redis dalam `SETUP.md` atau file dokumentasi terpisah yang mencakup: instalasi Redis, konfigurasi password, konfigurasi multiple database, dan verifikasi koneksi.

9. IF `REDIS_PASSWORD` tidak di-set (kosong) saat aplikasi berjalan di environment `production`, THEN THE Application SHALL mencatat peringatan ke log bahwa Redis berjalan tanpa autentikasi.

10. THE Cache_Config SHALL menggunakan `redis` sebagai default store dengan `REDIS_CACHE_DB` yang berbeda dari `REDIS_DB` yang digunakan untuk session dan queue.
