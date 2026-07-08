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
