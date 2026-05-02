# 🚀 IamJOS Migration Guide: OJS to IamJOS
**Target**: Technical Team / System Administrator
**Engine**: `MigrateLegacyOjs` Command

Panduan ini menjelaskan langkah-langkah untuk melakukan migrasi data komprehensif dari database OJS (MySQL) ke IamJOS (PostgreSQL) dengan menjaga integritas riwayat naskah.

---

## 📋 Prasyarat (Prerequisites)
1.  **Akses Database**: IamJOS harus memiliki akses network ke database MySQL OJS.
2.  **File Assets**: Pastikan direktori `files_dir` pada server OJS dapat diakses atau sudah disalin ke server IamJOS.
3.  **PHP PDO**: Pastikan ekstensi `pdo_mysql` dan `pdo_pgsql` aktif di server.

---

## ⚙️ Langkah 1: Konfigurasi Environment
Tambahkan kredensial database OJS lama ke file `.env` di root IamJOS:

```env
# Database IamJOS (PostgreSQL)
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_DATABASE=iamjos_db
...

# Database OJS Lama (MySQL)
DB_LEGACY_HOST=ip_server_mysql
DB_LEGACY_PORT=3306
DB_LEGACY_DATABASE=ojs_legacy_db
DB_LEGACY_USERNAME=root
DB_LEGACY_PASSWORD=password_anda
```

---

## 🛠️ Langkah 2: Persiapan Database
Jalankan migrasi untuk membuat tabel pendukung pemetaan ID:

```bash
php artisan migrate --path=database/migrations/2026_04_30_000000_create_legacy_mappings_table.php
```

---

## 🏃 Langkah 3: Eksekusi Migrasi
Disarankan untuk menjalankan migrasi secara bertahap untuk memantau performa.

### A. Migrasi Inti (Users & Journals)
```bash
php artisan iamjos:migrate-ojs --step=users
php artisan iamjos:migrate-ojs --step=journals
```

### B. Migrasi Konten (Submissions & Publications)
```bash
php artisan iamjos:migrate-ojs --step=submissions
```

### C. Migrasi Workflow & Riwayat (Timeline)
```bash
php artisan iamjos:migrate-ojs --step=history
php artisan iamjos:migrate-ojs --step=emails
php artisan iamjos:migrate-ojs --step=reviews
php artisan iamjos:migrate-ojs --step=discussions
```

### D. Migrasi File & Galley
```bash
php artisan iamjos:migrate-ojs --step=files
php artisan iamjos:migrate-ojs --step=galleys
```

> **Tip**: Gunakan `--step=all` jika Anda ingin menjalankan semuanya sekaligus dalam satu perintah.

---

## 🛡️ Langkah 4: Verifikasi & Pasca-Migrasi
Setelah proses selesai, lakukan pengecekan manual:
1.  **Audit Trail**: Masuk ke dashboard IamJOS, buka salah satu artikel hasil migrasi, dan pastikan **Workflow History** (Timeline) muncul secara kronologis.
2.  **Password**: Ingatkan seluruh user yang dimigrasi untuk menggunakan fitur **"Forgot Password"** untuk mengatur ulang password mereka (karena perbedaan algoritma enkripsi).
3.  **File Access**: Coba unduh salah satu file Galley PDF untuk memastikan pemindahan file fisik sukses.

---

## ⚠️ Troubleshooting
*   **Connection Timeout**: Jika data sangat besar, jalankan perintah per step untuk menghindari timeout.
*   **Memory Limit**: Jika terkena memory limit, naikkan via `php -d memory_limit=512M artisan ...`.

---
**IamJOS Migration Engine v1.1** - *Built for academic integrity.*
