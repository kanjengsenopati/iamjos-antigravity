# Bugfix: MissingAppKeyException di GitHub Actions

## 🐛 Deskripsi Bug

Test suite gagal di GitHub Actions dengan error:
```
FAILED Tests\Feature\NativeXmlExportTest > export native xml with sections
MissingAppKeyException: No application encryption key has been specified.
```

Error terjadi pada file:
- `vendor/laravel/framework/src/Illuminate/Encryption/EncryptionServiceProvider.php:83`

## 🔍 Root Cause Analysis

### Masalah Utama
Ada **race condition** dalam GitHub Actions workflow di mana `APP_KEY` tidak tersedia untuk test processes yang berjalan secara parallel.

### Detail Teknis

1. **Step "Siapkan environment testing"** menjalankan:
   ```bash
   php artisan key:generate --ansi
   ```
   Command ini menghasilkan `APP_KEY` dan menulisnya ke file `.env`, tapi **TIDAK** menyimpannya ke GitHub Actions environment variables.

2. **Step "Jalankan test suite"** mencoba menggunakan:
   ```yaml
   env:
     APP_KEY: ${{ env.APP_KEY }}  # ❌ Variabel ini KOSONG!
   ```
   
   Karena `env.APP_KEY` tidak pernah di-set, variabel ini bernilai kosong.

3. **Ketika test berjalan dengan `--parallel`**:
   - Laravel membuat multiple test processes
   - Setiap process membaca environment variables
   - Karena `APP_KEY` di environment variables kosong, beberapa test process gagal
   - File `.env` mungkin tidak terbaca konsisten oleh semua parallel processes

### Mengapa Ini Terjadi?

- `php artisan key:generate` hanya menulis ke **file** `.env`
- GitHub Actions environment variables (`${{ env.APP_KEY }}`) adalah **variabel terpisah**
- Parallel test processes mungkin tidak membaca file `.env` dengan konsisten
- Environment variables memiliki prioritas lebih tinggi daripada file `.env`

## ✅ Solusi

### Perubahan di `.github/workflows/deploy.yml`

**Sebelum:**
```yaml
- name: Siapkan environment testing
  run: |
    cp .env.example .env
    mkdir -p storage/framework/cache/data
    mkdir -p storage/framework/sessions
    mkdir -p storage/framework/views
    mkdir -p storage/logs
    mkdir -p bootstrap/cache
    chmod -R 775 storage bootstrap/cache
    php artisan key:generate --ansi
  env:
    APP_ENV: testing
    DB_CONNECTION: pgsql
    ...
```

**Sesudah:**
```yaml
- name: Siapkan environment testing
  run: |
    cp .env.example .env
    mkdir -p storage/framework/cache/data
    mkdir -p storage/framework/sessions
    mkdir -p storage/framework/views
    mkdir -p storage/logs
    mkdir -p bootstrap/cache
    chmod -R 775 storage bootstrap/cache
    php artisan key:generate --ansi
    # Capture APP_KEY dari .env dan export ke GITHUB_ENV
    APP_KEY=$(grep '^APP_KEY=' .env | cut -d '=' -f2)
    echo "APP_KEY=$APP_KEY" >> $GITHUB_ENV
  env:
    APP_ENV: testing
    DB_CONNECTION: pgsql
    ...
```

### Penjelasan Fix

1. **Capture APP_KEY dari file `.env`:**
   ```bash
   APP_KEY=$(grep '^APP_KEY=' .env | cut -d '=' -f2)
   ```
   Membaca nilai `APP_KEY` yang baru di-generate dari file `.env`

2. **Export ke GitHub Actions environment:**
   ```bash
   echo "APP_KEY=$APP_KEY" >> $GITHUB_ENV
   ```
   Menyimpan `APP_KEY` ke GitHub Actions environment variables sehingga tersedia untuk step-step berikutnya

3. **Step test suite sekarang bisa menggunakan:**
   ```yaml
   env:
     APP_KEY: ${{ env.APP_KEY }}  # ✅ Sekarang terisi!
   ```

## 🧪 Verifikasi

Setelah fix ini diterapkan:

1. ✅ `APP_KEY` tersedia di environment variables untuk semua test processes
2. ✅ Parallel test execution tidak lagi mengalami race condition
3. ✅ Test `NativeXmlExportTest` dan test lainnya yang membutuhkan encryption berjalan dengan sukses

## 📚 Referensi

- [Laravel Encryption Documentation](https://laravel.com/docs/11.x/encryption)
- [GitHub Actions Environment Variables](https://docs.github.com/en/actions/learn-github-actions/variables)
- [Laravel Parallel Testing](https://laravel.com/docs/11.x/testing#running-tests-in-parallel)

## 🏷️ Tags

`bugfix` `github-actions` `ci-cd` `laravel` `encryption` `parallel-testing` `race-condition`
