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

#### Workflow Editor — Modal Assign Editor (/submissions/{id})
- **[FIX]** Menemukan dan memperbaiki BUG KRITIS nesting HTML: kontainer tab Publication (x-data di show.blade.php) tidak memiliki tag penutup </div> yang tepat. Akibatnya, semua modal workflow (Assign Editor, Upload File, dll) tertelan ke dalam kontainer Publication yang tersembunyi saat tab Workflow aktif — modal tidak pernah bisa tampil.
  - Solusi: Menempatkan tag penutup </div> yang tepat setelah kedua modal Contributor (contributorModalOpen, reorderModalOpen) sehingga modal workflow lainnya berada di luar kontainer Publication.

- **[FIX]** Menemukan dan memperbaiki race condition Alpine.js yang menyebabkan error 'isSubmitting is not defined' dan 'editingContributor is not defined' di browser console saat halaman dimuat:
  - Penyebab: Kode pendaftaran Alpine.data('submissionWorkflow') menggunakan pola if (window.Alpine) { register() }. Karena Livewire memuat Alpine secara asinkron, kondisi window.Alpine sering sudah true ketika script berjalan, sehingga registerSubmissionWorkflow() dipanggil SEBELUM event alpine:init selesai. Alpine kemudian scan DOM sebelum data terdaftar, menyebabkan semua variabel scope tidak ditemukan.
  - Solusi: Mengganti semua if (window.Alpine) menjadi document.addEventListener('alpine:init', ...) secara konsisten.

- **[FIX]** Menghapus x-cloak dari modal-assign-editor.blade.php dan galley-modal.blade.php, menggantinya dengan style="display: none;". Mencegah bentrok prioritas CSS antara [x-cloak] { display: none !important; } global dengan Alpine x-show.

- **[FIX]** Menonaktifkan inject_assets = true di config/livewire.php (diubah ke false) untuk mencegah Livewire menyuntikkan Alpine.js duplikat ke halaman yang sudah memuat Alpine via @livewireScripts.

- **[FIX]** Menghapus tag <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"> dari layouts/app.blade.php yang memuat Alpine plugin secara terpisah dari CDN — berpotensi menciptakan dua Alpine instance yang saling bertabrakan.

---

#### Data dan Peran Jurnal "Journey"
- **[FIX]** Memperbaiki inkonsistensi nama peran (case-sensitive): nama peran 'Journal Manager' dan 'Editor' (kapital tidak standar) diganti menjadi 'Journal manager' dan 'Journal editor' (format baku sistem). Perbedaan ini menyebabkan query potentialEditors mengembalikan 0 hasil karena filter permit_submission=true hanya ada pada peran berformat baku.
- **[FIX]** Membersihkan record duplikat di tabel journal_user_roles yang berisi referensi ke peran yang sudah dihapus.

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
| resources/views/submissions/show.blade.php                        | MODIFIED   | Fix nesting HTML, race condition Alpine, hapus debug    |
| resources/views/submissions/partials/modal-assign-editor.blade.php| MODIFIED   | Ganti x-cloak dengan style display:none                 |
| resources/views/components/submissions/galley-modal.blade.php     | MODIFIED   | Ganti x-cloak dengan style display:none                 |
| resources/views/layouts/app.blade.php                             | MODIFIED   | Hapus CDN Alpine collapse plugin yang konflik           |
| config/livewire.php                                               | MODIFIED   | inject_assets diubah ke false                           |
| database/seeders/seed_journey.php                                 | NEW        | Seeder lengkap data dummy jurnal journey                |
| fix_journey_roles.php                                             | NEW (temp) | Script utilitas perbaikan nama peran di database        |

---

## Versi Sebelumnya

Lihat SECURITY_CHANGELOG.md untuk catatan perubahan keamanan (patch 2026-03-07).
