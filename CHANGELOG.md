# CHANGELOG

Semua perubahan signifikan pada proyek **IAmJOS** (Sistem Jurnal Open Source) didokumentasikan di sini.
Format mengacu pada [Keep a Changelog](https://keepachangelog.com/en/1.0.0/).

---

## [Unreleased] — 2026-07-12

### 🛠️ Perbaikan Bug (Bug Fixes)

#### Installer Web (/install)
- **[FIX]** Mengatur ulang urutan proses instalasi di InstallController.php — migrasi database dan seeding dilakukan SEBELUM penulisan file .env. Ini mencegah proses PHP mati di tengah jalan karena artisan serve mendeteksi perubahan .env dan me-restart server.
- **[FIX]** Mengganti firstOrCreate menjadi updateOrCreate pada pembuatan akun Super Admin di wizard instalasi, sehingga password yang dimasukkan pengguna selalu menimpa data bawaan seeder secara andal.
- **[FIX]** Menambahkan auto-generasi username dari email (misal: superadmin@demo.iamjos.id -> superadmin) untuk memenuhi constraint NOT NULL pada kolom username di database PostgreSQL.

---

#### Data dan Peran Jurnal "Journey"
- **[FIX]** Memperbaiki inkonsistensi nama peran (case-sensitive) pada user di database yang terdaftar untuk jurnal "journey": nama peran 'Journal Manager' dan 'Editor' (kapital tidak standar) diganti menjadi 'Journal manager' dan 'Journal editor' (format baku sistem) di model `JournalUserRole`. Perbaikan ini memastikan query `potentialEditors` di backend mengembalikan daftar editor yang valid, alih-alih mengembalikan 0 hasil (kosong) saat tombol Assign Editor diklik.

---

### ✨ Fitur dan Peningkatan (Features & Enhancements)

#### Data Demo — Jurnal "Journey"
- **[ADD]** Membuat seeder data dummy lengkap untuk jurnal journey mencakup:
  - 2 Edisi: Vol 1 No 1 (published) dan Vol 1 No 2 (draft)
  - 6 Akun Demo: Super Admin, Journal Admin, Chief Editor, Editor, Reviewer, Author
  - 4 Naskah Dummy mencakup setiap tahap workflow:
    - submission-001 — Tahap: Submission (Baru)
    - submission-002 — Tahap: Review (Dalam Proses)
    - submission-003 — Tahap: Copyediting
    - submission-004 — Tahap: Production / Published (dengan galley PDF)
  - File PDF placeholder terlampir pada setiap naskah untuk keperluan pengujian

---

### Akun Demo

| Nama               | Email                       | Password         | Peran di Jurnal  |
|--------------------|-----------------------------|------------------|------------------|
| Super Admin        | superadmin@demo.iamjos.id   | joni123#Marjoni  | Super Admin      |
| Journal Admin      | admin@demo.iamjos.id        | Demo@IamJOS2026! | Journal manager  |
| Chief Editor       | editor@demo.iamjos.id       | Demo@IamJOS2026! | Journal editor   |
| Reviewer           | reviewer@demo.iamjos.id     | Demo@IamJOS2026! | Reviewer         |
| Author             | author@demo.iamjos.id       | Demo@IamJOS2026! | Author           |

---

### File yang Dimodifikasi

| File                                                              | Perubahan  | Keterangan                                              |
|-------------------------------------------------------------------|------------|---------------------------------------------------------|
| app/Http/Controllers/InstallController.php                        | MODIFIED   | Urutan instalasi dan updateOrCreate untuk admin         |
| app/Models/JournalUserRole.php                                    | MODIFIED   | Penyelarasan format name pada role agar case-insensitive|
| database/seeders/seed_journey.php                                 | NEW        | Seeder lengkap data dummy jurnal journey                |

---

## Versi Sebelumnya

Lihat SECURITY_CHANGELOG.md untuk catatan perubahan keamanan (patch 2026-03-07).
