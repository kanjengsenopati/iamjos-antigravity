# 📜 IAMJOS HOLY GRAIL PRD
**Version**: 1.1 (Migration Focused)
**Status**: DRAFT / LIVING DOCUMENT

---

## 1. Visi & Misi
IamJOS adalah platform manajemen jurnal akademik modern yang dirancang untuk menggantikan sistem legacy (seperti OJS) dengan arsitektur yang lebih aman, cepat, dan berbasis data terpusat (KAMPUS (Kantor Manajemen Pusat IamJOS)).

---

## 2. Modul Utama
### 2.1. Submission & Workflow Engine
*   Manajemen naskah dari draf hingga publikasi.
*   Multi-stage workflow: Submission, Review, Copyediting, Production.
*   **Workflow Timeline**: Visualisasi kronologis seluruh aktivitas naskah.

### 2.2. Publisher & Journal Management
*   Multi-journal support.
*   Pengaturan ISSN, DOI, dan License.

### 2.3. Advanced Security (The Shield)
*   **Anti-Hijacking Protection**: Enkripsi kode, domain locking, dan sistem lisensi remote.
*   **Toggle Mechanism**: Fitur keamanan dapat diaktifkan/dinonaktifkan via `.env`.

---

## 3. Legacy Data Migration & ETL Architecture (NEW)
### 3.1. Objektif Migrasi
Memindahkan seluruh data dari OJS (MySQL) ke IamJOS (PostgreSQL) tanpa kehilangan data (**Zero Data Loss**) dan menjaga integritas sejarah.

### 3.2. Cakupan Data (Mandatory)
1.  **Core Entities**: Jurnal, User (Roles & Permissions), Issues, Submissions, Publications.
2.  **Workflow History (Critical)**: 
    *   Seluruh log dari tabel `event_log` OJS.
    *   Sejarah keputusan editor dari `edit_decisions`.
    *   Sejarah penugasan dan rekomendasi dari `review_assignments`.
    *   Seluruh diskusi (Queries/Discussions) antar pengguna.
3.  **Digital Assets**: Pemindahan file PDF, XML, dan file pendukung dari direktori OJS ke IamJOS Storage.

### 3.3. Prinsip Teknis Migrasi
*   **Chronological Integrity**: Penonaktifan timestamps otomatis selama migrasi untuk mempertahankan tanggal asli dari data lama.
*   **ID Mapping**: Penggunaan tabel transisi untuk memetakan Integer ID (OJS) ke UUID (IamJOS).
*   **JSONB Metadata Storage**: Menyimpan data tambahan OJS yang tidak memiliki kolom tetap ke dalam kolom metadata JSONB agar tetap dapat diaudit.

---

## 4. KAMPUS (Kantor Manajemen Pusat IamJOS)
*   Remote License Management.
*   Monitoring Kesehatan Server (Telemetry).
*   OTA (Over-The-Air) Updates untuk security patches.

---

## 5. Roadmap Pengembangan
1.  **Phase 1**: Migrasi Inti (Users, Journals).
2.  **Phase 2**: Migrasi Konten (Submissions, Publications).
3.  **Phase 3**: Migrasi Sejarah & Workflow (Log, Review, Discussion).
4.  **Phase 4**: Security Shield Integration.
5.  **Phase 5**: KAMPUS (Kantor Manajemen Pusat IamJOS) Deployment.

---

## 6. Non-Regression Covenant (Aturan Mengikat Permanen)

> **ATURAN INI BERSIFAT MUTLAK DAN BERLAKU PERMANEN UNTUK SEMUA CI/CD MULTI-DEPLOYMENT.**

### 6.1. Zero Regression Policy
Setiap perubahan kode — baik penambahan fitur baru, perbaikan bug, enhancement UI, maupun refactoring — **DILARANG KERAS** merusak, mengubah, atau mengganggu:
1.  **Sistem & Logika Bisnis** yang sudah berjalan dan settled.
2.  **Behavior & Workflow** yang sudah stabil di production.
3.  **UI Rendering & Interaksi** yang sudah berfungsi identik di multi-deployment.
4.  **Database Schema & Data Integrity** yang sudah mapan.

### 6.2. Prinsip Pelaksanaan
*   **Non-Destructive**: Perubahan harus bersifat additive/isolated, bukan replacing.
*   **Backwards Compatible**: Tidak boleh memutus kompatibilitas mundur.
*   **Single Source of Truth**: Setiap entitas data harus memiliki satu sumber kebenaran yang jelas (contoh: Publication → authors, bukan dual query OR).
*   **Idempotent Operations**: Operasi yang bisa dipanggil berulang harus menghasilkan hasil yang sama (contoh: `getOrCreatePublication()` tidak boleh duplicate data).

### 6.3. Cakupan Berlaku
Aturan ini berlaku untuk:
*   Semua push ke branch `main`, `staging`, dan `dev`.
*   Semua deployment ke multi-VPS dan multi-domain.
*   Semua kontribusi dari developer manusia maupun AI.
*   Berlaku **persistent** dan **permanen** — tidak boleh di-override tanpa persetujuan eksplisit dari pemilik proyek.

---

## 7. Mass Distribution Architecture

### 7.1. Prinsip Distribusi Massal
IamJOS dirancang untuk di-clone dan didistribusikan secara massal ke multi-VPS, multi-domain/subdomain. Setiap instance deployment **HARUS** menghasilkan sistem dan UI yang **identik sama persis**.

### 7.2. Deployment Rules
*   **Self-Contained Assets**: Setiap dependency kritis harus memiliki jalur muat mandiri, tidak boleh bergantung pada satu chain tunggal.
*   **Environment-Agnostic UI**: UI harus berfungsi identik di semua kondisi server (dengan/tanpa URL rewriting, prefix `/index.php/`, panel apapun).
*   **Deterministic Deployment**: Apa yang di-build di CI = apa yang di-deploy di semua target.
*   **Post-Deploy Verification**: Setiap deployment harus diverifikasi otomatis (health check) sebelum dianggap sukses.

---
*Dokumen ini adalah Kitab Suci pengembangan IamJOS. Setiap perubahan kode harus merujuk pada PRD ini.*
