# 🗄 honor Panduan Setup PostgreSQL & Migrasi Database Produksi (Lokal & GitHub Actions)

Dokumen ini memandu Anda langkah-demi-langkah untuk menginstal, membuat database PostgreSQL di mesin lokal (Windows), menyelaraskan konfigurasi Laravel, serta melakukan konfigurasi CI/CD di GitHub Actions agar migrasi berjalan secara otomatis di server produksi.

---

## 💻 Bagian 1: Setup PostgreSQL di Localhost Windows

Terdapat tiga cara mudah untuk mengaktifkan PostgreSQL di Windows. Pilih salah satu cara berikut yang paling sesuai dengan alur kerja Anda:

### Opsi A: Menggunakan Installer Resmi (EnterpriseDB) - *Direkomendasikan untuk Native Setup*
1. **Unduh Installer**:
   Kunjungi halaman unduhan resmi [PostgreSQL Windows](https://www.postgresql.org/download/windows/) dan unduh installer versi **15** atau yang lebih baru.
2. **Jalankan Instalasi**:
   - Klik dua kali installer `.exe` yang diunduh.
   - Pada pemilihan komponen, pastikan **PostgreSQL Server**, **pgAdmin 4** (alat manajemen visual), dan **Command Line Tools** tercentang.
   - **Tentukan Password Superuser**: Masukkan kata sandi untuk user default `postgres` (misalnya: `root` atau password lain yang mudah Anda ingat). Catat password ini.
   - **Port**: Biarkan port default tetap pada `5432`.
   - Selesaikan instalasi hingga akhir.

### Opsi B: Menggunakan Laragon - *Sangat Praktis*
Jika Anda menggunakan **Laragon** untuk development PHP di Windows:
1. Klik kanan pada aplikasi Laragon di system tray.
2. Pilih **Quick Settings** / **Services** ➔ **PostgreSQL** ➔ Centang **Enable**.
3. Laragon akan mengunduh dan menyiapkan PostgreSQL secara otomatis dengan user default `postgres` dan tanpa password (atau password sesuai konfigurasi bawaan Laragon).

### Opsi C: Menggunakan Docker Desktop - *Untuk Pengguna Docker*
Jika Anda memiliki Docker Desktop terpasang di Windows, jalankan perintah ini di PowerShell/CMD:
```powershell
docker run --name iamjos-postgres -e POSTGRES_PASSWORD=root -p 5432:5432 -d postgres:15
```

---

## 🛠️ Bagian 2: Pembuatan Database Lokal

Setelah server PostgreSQL aktif, buat database bernama `iamjos_dev` (sesuai konfigurasi `.env` Anda).

### Cara 1: Menggunakan Command Line (SQL Shell / psql)
1. Buka **CMD** atau **PowerShell** di Windows.
2. Hubungkan ke PostgreSQL (ganti port/host jika berbeda):
   ```cmd
   psql -U postgres
   ```
3. Masukkan password superuser yang telah Anda buat pada proses instalasi sebelumnya.
4. Jalankan perintah SQL berikut untuk membuat database baru:
   ```sql
   CREATE DATABASE iamjos_dev;
   ```
5. Ketik `\q` lalu tekan Enter untuk keluar dari shell PostgreSQL.

### Cara 2: Menggunakan pgAdmin 4 (Visual GUI)
1. Buka aplikasi **pgAdmin 4** dari Start Menu Windows.
2. Hubungkan ke server lokal pada tab **Servers** di sebelah kiri (Anda mungkin akan diminta memasukkan password superuser).
3. Klik kanan pada folder **Databases** ➔ **Create** ➔ **Database...**
4. Isi nama database dengan `iamjos_dev`.
5. Klik **Save** untuk membuat database.

---

## ⚙️ Bagian 3: Konfigurasi Laravel `.env` Lokal

Buka file [`.env`](file:///f:/Antigravity/Projects/iamjos-php/.env) pada proyek `iamjos-php` di Windows dan sesuaikan konfigurasi database PostgreSQL Anda pada blok berikut:

```env
# --- LOCAL DEVELOPMENT DATABASE (PostgreSQL) ---
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=iamjos_dev
DB_USERNAME=postgres
DB_PASSWORD=root        # ⚠️ SESUAIKAN dengan password PostgreSQL Anda
```

---

## 🏃 Bagian 4: Menjalankan Migrasi di Localhost

Buka terminal pada direktori proyek [iamjos-php](file:///f:/Antigravity/Projects/iamjos-php) lalu jalankan serangkaian perintah berikut untuk menyiapkan skema database steril:

```powershell
# 1. Pastikan seluruh dependensi PHP terinstal
composer install

# 2. Jalankan migrasi schema database ke PostgreSQL secara bersih (tanpa seeders)
php artisan migrate:fresh
```

---

## 🚀 Bagian 5: Integrasi GitHub & GitHub Actions (Continuous Deployment)

Repositori Anda telah dilengkapi dengan workflow CI/CD modern pada file [`.github/workflows/deploy.yml`](file:///f:/Antigravity/Projects/iamjos-php/.github/workflows/deploy.yml).

### A. Uji Coba Database Otomatis di GitHub Actions
Saat Anda mendorong (*push*) kode ke GitHub, GitHub Actions akan menjalankan job **`test`** menggunakan container PostgreSQL virtual (`postgres:15`) secara otomatis. Skema database akan dimigrasi dan diuji menggunakan Pest (Test Suite) tanpa mengganggu database produksi asli Anda.

### B. Konfigurasi GitHub Secrets untuk Deployment & Migrasi Produksi
Untuk mengizinkan GitHub Actions masuk ke server VPS Anda dan memicu migrasi database PostgreSQL produksi, Anda wajib mendaftarkan beberapa kunci rahasia (*Secrets*).

Langkah setup di GitHub:
1. Buka repositori proyek Anda di GitHub.
2. Pergi ke menu **Settings** ➔ **Secrets and variables** ➔ **Actions**.
3. Klik tombol **New repository secret** untuk menambahkan variabel berikut satu per satu:

| Nama Secret | Deskripsi / Nilai |
| :--- | :--- |
| `SERVER_IP` | Alamat IP Publik VPS tempat aplikasi di-deploy (contoh: `103.87.67.8`). |
| `SERVER_USER` | Username SSH untuk masuk ke server (contoh: `root` atau `www`). |
| `SSH_PRIVATE_KEY` | Isi dari file kunci privat SSH Anda (`id_rsa` / `id_ed25519`) yang memiliki akses ke VPS. |
| `PROD_PATH` | Path absolut folder web produksi di VPS Anda (contoh: `/www/wwwroot/ejournal.apdesyi.or.id`). |
| `STAGING_PATH` | *(Opsional)* Path absolut folder web staging di VPS (contoh: `/www/wwwroot/staging.ejournal.apdesyi.or.id`). |
| `DEV_PATH` | *(Opsional)* Path absolut folder web development di VPS (contoh: `/www/wwwroot/dev.ejournal.apdesyi.or.id`). |
| `SERVER_PORT` | *(Opsional)* Port SSH kustom VPS jika bukan port default `22`. |

### C. Alur Kerja Deployment & Migrasi di VPS Produksi
Ketika Anda melakukan *push* ke branch `main`, `staging`, atau `dev`, alur kerja berikut akan dieksekusi oleh GitHub Actions secara otomatis:

1. **Pull Kode**: Menarik kode terbaru ke server VPS.
2. **Database Backup**: Melakukan backup database PostgreSQL produksi otomatis menggunakan utilitas `pg_dump` ke direktori `/var/backups/iamjos/` di VPS sebelum memulai migrasi schema baru. Ini mengamankan data Anda dari potensi kehilangan data.
3. **Install Dependencies**: Menjalankan optimasi dependencies menggunakan Composer dan membangun bundel frontend menggunakan Vite.
4. **Auto Migrate**: Menjalankan migrasi database di produksi menggunakan perintah:
   ```bash
   php artisan migrate --force --ansi
   ```
5. **Cache Rebuild & Reload**: Memperbarui cache Laravel dan memicu reload graceful pada server web (Nginx & PHP-FPM) untuk memastikan perubahan schema langsung aktif tanpa mengalami *downtime*.
6. **Health Check**: Memverifikasi endpoint `/api/v1/health` di VPS untuk memastikan situs Anda kembali online dengan sukses.

---
**💡 Tips Keamanan**: Kredensial database produksi (seperti password DB) **tidak disimpan di GitHub Secrets**, melainkan dibaca secara aman langsung dari file `.env` lokal yang sudah ada di dalam VPS Anda pada direktori `TARGET_PATH`.

---

## 🌐 Arsitektur Multi-Database & Nginx Virtual Host (Single VPS, 1 IP)

Meskipun ketiga proyek berjalan di server VPS yang sama dengan alamat IP yang sama, mereka dipisahkan secara logis menggunakan konfigurasi server web (Nginx) dan database terpisah.

### 1. Skema Pemisahan Database PostgreSQL (Lokal & VPS)
Untuk menghindari pencampuran data antara modul marketplace, core engine tenant, dan control plane, buat database terpisah dalam satu instance PostgreSQL:

```sql
-- Masuk ke PostgreSQL console: psql -U postgres
-- Jalankan perintah berikut untuk membuat database terpisah:

-- Database untuk Website Marketplace Utama (website-iamjos)
CREATE DATABASE iamjos_website;

-- Database untuk Core System & Tenant Generator Engine (iamjos-php)
CREATE DATABASE iamjos_core;

-- Database untuk Kampus Control Plane (jika membutuhkan backend terpisah)
CREATE DATABASE iamjos_kampus;
```

Sesuaikan konfigurasi file `.env` pada masing-masing proyek di VPS agar menunjuk ke database yang sesuai:
*   Di **`website-iamjos`** ➔ `DB_DATABASE=iamjos_website`
*   Di **`iamjos-php`** ➔ `DB_DATABASE=iamjos_core`

---

### 2. Konfigurasi Nginx Server Block (Virtual Host) di VPS
Untuk mengarahkan domain/subdomain yang berbeda ke direktori yang tepat pada IP VPS yang sama, pasang konfigurasi Nginx berikut:

#### A. Konfigurasi untuk Website Utama (`iamjos.id`)
```nginx
server {
    listen 80;
    listen [::]:80;
    server_name iamjos.id www.iamjos.id;
    root /www/wwwroot/website-iamjos/public;

    index index.php index.html;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_pass unix:/run/php/php8.2-fpm.sock; # Sesuaikan versi PHP
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }
}
```

#### B. Konfigurasi untuk Kampus IamJOS Control Plane (`kampus.iamjos.id`)
Karena Kampus IamJOS SPA dibangun menggunakan React (Vite), Nginx hanya perlu menyajikan aset statis build (`dist`):
```nginx
server {
    listen 80;
    listen [::]:80;
    server_name kampus.iamjos.id;
    root /www/wwwroot/kampus-iamjos/dist;

    index index.html;

    location / {
        try_files $uri $uri/ /index.html; # Mengarahkan routing client-side ke index.html
    }

    # Proxy ke API backend Laravel jika diperlukan
    location /api {
        proxy_pass http://127.0.0.1:8000; # Port backend iamjos-php
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
    }
}
```

#### C. Konfigurasi untuk Core Engine & Dynamic Tenant Subdomains (`*.tenant.iamjos.id` / `*.iamjos.id`)
Core engine menangani akses domain kustom atau subdomain dari masing-masing tenant jurnal:
```nginx
server {
    listen 80;
    listen [::]:80;
    server_name ~^(?<tenant>.+)\.iamjos\.id$ ejournal.apdesyi.or.id; # Wildcard subdomain & custom domain
    root /www/wwwroot/iamjos-php/public;

    index index.php index.html;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_pass unix:/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }
}
```

---

### 3. Pemisahan Alur Git & CI/CD di GitHub Actions
Setiap proyek memiliki repositori git mandiri. Untuk memastikan deployment berjalan ke direktori yang benar di VPS, setel secret `PROD_PATH` pada masing-masing repositori GitHub secara berbeda:
*   Secret **`PROD_PATH`** pada repositori **`website-iamjos`** ➔ `/www/wwwroot/website-iamjos`
*   Secret **`PROD_PATH`** pada repositori **`kampus-iamjos`** ➔ `/www/wwwroot/kampus-iamjos`
*   Secret **`PROD_PATH`** pada repositori **`iamjos-php`** ➔ `/www/wwwroot/iamjos-php`

Dengan demikian, saat melakukan commit dan push, proses penarikan kode dan kompilasi hanya akan memengaruhi direktori proyek tersebut tanpa mengganggu proyek lainnya di VPS yang sama.
