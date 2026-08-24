# Goal Description

Mengubah arsitektur *deployment* IAMJOS dari metode konvensional (install manual di aaPanel/cPanel) menjadi **Containerized Ecosystem** menggunakan Docker. Tujuan utamanya adalah menciptakan pengalaman *Plug and Play*: siapapun yang ingin menginstal IAMJOS hanya perlu menjalankan satu perintah, dan sistem akan menyala beserta database, redis, dan web server-nya sendiri, tanpa drama versi PHP bentrok, *extension* kurang, atau *permission denied*.

## User Review Required

> [!IMPORTANT]
> **Keputusan Arsitektur Container:**
> Apakah Anda ingin menggunakan arsitektur **Standar (Nginx + PHP-FPM)** yang umum dan stabil, atau arsitektur **High-Performance (Laravel Octane + FrankenPHP/Swoole)** yang sangat cepat dan digabung dalam 1 container tunggal? (Rencana di bawah ini menggunakan arsitektur Standar karena lebih mudah di-*debug* dan sangat familiar bagi ekosistem aaPanel/VPS tradisional).

## Open Questions

> [!NOTE]
> 1. Apakah *container* ini nantinya akan didistribusikan ke klien/institusi lain (sebagai *Docker Image* di Docker Hub), atau hanya akan di-*build* secara mandiri di setiap VPS?
> 2. Di server aaPanel Anda, apakah port 80 dan 443 sudah dikuasai oleh Nginx bawaan aaPanel? Jika ya, Docker IAMJOS harus di- *expose* ke port lain (misal 8080) lalu di-*reverse proxy* dari aaPanel.

## Proposed Changes

---

### 1. Dockerfile (Jantung Aplikasi)
Membuat cetak biru lingkungan PHP 8.2+ yang mandiri, di mana *composer* dan *extension* PHP wajib sudah terpasang dan dikonfigurasi.

#### [NEW] `Dockerfile`
- Menggunakan *Multi-stage build* (Node.js untuk *build frontend* Vite, dan PHP-FPM Alpine untuk *backend*).
- Menginstal ekstensi wajib: `pdo_pgsql`, `bcmath`, `gd`, `zip`.
- Mengatur kepemilikan folder (chown) secara absolut ke `www-data` agar bebas dari drama *permission*.

---

### 2. Orkestrasi Ekosistem (Docker Compose)
Menyatukan Aplikasi, Database, dan Cache dalam satu jaringan terisolasi.

#### [NEW] `docker-compose.yml`
- **Service `app`**: Menjalankan IAMJOS (PHP-FPM).
- **Service `web`**: Menjalankan Nginx (sebagai pintu gerbang).
- **Service `db`**: Menjalankan PostgreSQL 15 (dengan data yang di-*mount* ke *volume* agar permanen).
- **Service `redis`**: Menjalankan Redis 7 (untuk *cache* & *session* super cepat).
- **Service `worker`**: Menjalankan `php artisan queue:work` secara *background* untuk memproses email/notifikasi.

---

### 3. Konfigurasi Pendukung

#### [NEW] `docker/nginx/default.conf`
- File konfigurasi Nginx khusus untuk di dalam *container*, yang secara otomatis mengarahkan trafik (URL Rewrite) ke `index.php` milik IAMJOS.

#### [NEW] `docker/entrypoint.sh`
- Skrip ajaib *Plug & Play*. Saat *container* dinyalakan, skrip ini akan otomatis:
  1. Menjalankan `composer install` (jika belum).
  2. Menyesuaikan hak akses (anti-crash).
  3. Menjalankan `php artisan migrate --force` (opsional).
  4. Menjalankan `php-fpm`.

#### [MODIFY] `.env.example`
- Menyesuaikan nilai *default* agar langsung mengenali nama *service* di Docker.
  - `DB_HOST=db` (bukan 127.0.0.1)
  - `REDIS_HOST=redis` (bukan 127.0.0.1)

---

## Verification Plan

### Automated Tests
- Memastikan `docker-compose build` berhasil tanpa error dependensi.

### Manual Verification
1. Melakukan *clone* repositori ke folder kosong.
2. Menjalankan perintah `docker compose up -d`.
3. Membuka browser ke `localhost:8080` dan memverifikasi bahwa Web Installer IAMJOS langsung muncul tanpa peringatan ekstensi PHP yang kurang atau error *Permission*.
