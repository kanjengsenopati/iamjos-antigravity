# Project-Specific Rules

## Laragon PHP Execution
- Untuk menjalankan PHP, Composer, dan Laravel (seperti Artisan) dalam proyek ini, selalu gunakan PHP binary atau lingkungan dari instalasi Laragon yang berada di direktori `F:\Laragon` (misalnya: `F:\Laragon\bin\php\php-8.4.12-nts-Win32-vs17-x64\php.exe`).
- Jangan menggunakan PHP bawaan sistem global (CLI) di luar direktori Laragon tersebut untuk menghindari perbedaan versi php atau masalah ekstensi database.

## Bahasa Respon
- Selalu gunakan Bahasa Indonesia untuk setiap penjelasan, feedback, dan komunikasi dengan user.

## Stabilitas Sistem & Keamanan Regresi (Aturan Ketat)
- Dilarang keras mengubah, memodifikasi, atau merusak alur sistem, bisnis logik, atau modul UI lain yang sudah stabil sebelumnya.
- Setiap perubahan, baik berupa penambahan fitur baru, perbaikan bug, atau enhancement UI, harus bersifat non-destructive dan terisolasi dengan baik.
- Pertahankan kompatibilitas mundur (backwards compatibility) di semua komponen dan database schema.
- Aturan ini bersifat permanen dan persistent untuk setiap pengerjaan tugas di workspace ini.

## Restore Point & Cadangan Modul Submission (Stabil)
- **Kondisi Stabil**: Modul Submission Modals (Assign Editor, Galley, Edit Review) telah stabil di commit `e3153abb`.
- **Git Tag Point**: Tag Git `v1.1-stable-modals` dikunci di commit tersebut. 
- **File Cadangan Cloud & Lokal**: File `resources/views/submissions/show.blade.php.backup-stable` adalah cadangan yang sinkron dengan GitHub Cloud.
- **Cara Rollback/Restore**: Jika terjadi kerusakan di file utama `show.blade.php` akibat pembaruan/fitur baru, ingatkan user/jalankan instruksi ini:
  - Cara 1 (Git Terminal):
    ```bash
    git checkout v1.1-stable-modals -- resources/views/submissions/show.blade.php
    ```
  - Cara 2 (Manual): Salin isi file `show.blade.php.backup-stable` ke `show.blade.php`.
