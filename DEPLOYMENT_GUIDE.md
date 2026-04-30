# 🚀 Panduan Deployment & Alur Kerja Tim (IamJOS)

Dokumen ini menjelaskan cara tim bekerja dengan branch dan bagaimana proses deployment otomatis ke VPS dilakukan menggunakan GitHub Actions.

---

## 1. Struktur Branch (Git Flow)

Kita menggunakan 3 branch utama yang masing-masing terhubung ke lingkungan (environment) berbeda di VPS:

| Branch | Lingkungan | URL (Contoh) | Deskripsi |
| :--- | :--- | :--- | :--- |
| `dev` | Development | `dev.iamjos.id` | Tempat fitur baru digabungkan pertama kali. |
| `staging` | Staging | `staging.iamjos.id` | Tempat testing final sebelum rilis. |
| `main` | Production | `iamjos.id` | Versi stabil yang diakses oleh user umum. |

---

## 2. Cara Kerja Tim (Daily Workflow)

1.  **Tarik Kode Terbaru**: `git checkout dev` lalu `git pull origin dev`.
2.  **Buat Branch Fitur**: `git checkout -b feat/nama-fitur`.
3.  **Selesaikan Coding**: Lakukan commit seperti biasa.
4.  **Push ke GitHub**: `git push origin feat/nama-fitur`.
5.  **Buat Pull Request (PR)**: Di GitHub, buat PR dari branch fitur Anda ke branch `dev`.
6.  **Merge**: Setelah di-review, merge ke `dev`. GitHub Actions akan otomatis meng-update server development.

---

## 3. Konfigurasi GitHub Secrets

Agar deployment otomatis berjalan, Anda (sebagai pemilik Repo) harus mengisi **Secrets** di Settings GitHub Repo:

1.  Buka **Settings > Secrets and variables > Actions**.
2.  Tambahkan **Repository Secrets** berikut:

| Secret Name | Deskripsi | Contoh Nilai |
| :--- | :--- | :--- |
| `SERVER_IP` | Alamat IP VPS Anda | `103.87.67.8` |
| `SERVER_USER` | Username SSH (root/ubuntu) | `root` |
| `SSH_PRIVATE_KEY` | Isi dari file `.id_rsa` Anda | `-----BEGIN OPENSSH PRIVATE KEY-----...` |
| `DEV_PATH` | Path folder dev di VPS | `/var/www/iamjos-dev` |
| `STAGING_PATH` | Path folder staging di VPS | `/var/www/iamjos-staging` |
| `PROD_PATH` | Path folder production di VPS | `/var/www/iamjos-prod` |

---

## 4. Persiapan di Sisi VPS (Satu Kali Saja)

Di dalam server VPS, lakukan langkah berikut untuk setiap folder (`dev`, `staging`, `prod`):

1.  **Clone Repo**:
    ```bash
    git clone https://github.com/kanjengsenopati/iamjos-antigravity.git /var/www/iamjos-dev
    ```
2.  **Set Permission**:
    ```bash
    chown -R www-data:www-data /var/www/iamjos-dev/storage /var/www/iamjos-dev/bootstrap/cache
    ```
3.  **Setup .env**:
    Copy file `.env` manual ke masing-masing folder dan sesuaikan database-nya.

---

## 5. Keuntungan Alur Ini
- **Otomatis**: Tidak perlu lagi `ssh` dan `git pull` manual. Cukup `push` kode.
- **Aman**: Setiap branch punya database dan environment yang terisolasi.
- **Transparan**: Tim bisa melihat status deployment langsung di tab **Actions** GitHub.

---

> [!TIP]
> Jika deployment gagal, cek tab **Actions** di GitHub untuk melihat log error-nya secara detail.
