# 🚀 Auto Deployment Workflow - IAMJOS

## 📋 Overview

Setiap perubahan code yang di-push ke repository akan **otomatis** melalui pipeline CI/CD lengkap:

```
Git Push → GitHub Actions → Tests → Build → Deploy ke VPS
```

## 🔄 Workflow Otomatis

### 1️⃣ Developer Push Code
```bash
git add .
git commit -m "feat: new feature"
git push origin main  # atau staging/dev
```

### 2️⃣ GitHub Actions Triggered (Otomatis)

Pipeline akan berjalan otomatis dengan 3 job:

#### **Job 1: Test & Analisis Statis** 🧪
- Setup PHP 8.4 + PostgreSQL 15
- Install dependencies
- Generate APP_KEY
- Run database migrations
- **Run test suite (Pest) dengan parallel execution**
- Run PHPStan level 5 static analysis

**Jika gagal:** Pipeline berhenti, tidak ada deployment

#### **Job 2: Build Assets** 🔨
- Setup Node.js 20
- Install npm dependencies
- Build production assets dengan Vite
- Upload build artifacts

**Hanya berjalan jika:** Job Test berhasil

#### **Job 3: Deploy ke VPS** 🚀
- **Branch `main`:** Deploy ke **Production** (memerlukan manual approval)
- **Branch `staging`:** Deploy ke **Staging** (otomatis)
- **Branch `dev`:** Deploy ke **Development** (otomatis)

**Hanya berjalan jika:** Job Build berhasil

### 3️⃣ Deployment ke VPS (Otomatis)

Deployment script akan:

1. **Pull kode terbaru** dari branch yang sesuai
2. **Backup database** (production & staging only)
3. **Install PHP dependencies** (production mode)
4. **Build frontend assets** (npm ci + npm run build)
5. **Run database migrations**
6. **Rebuild cache** (config, route, view)
7. **Restart services** (PHP-FPM + Nginx)
8. **Health check verification**

## 🌍 Environment Mapping

| Branch    | Environment | URL                          | Auto Deploy | Manual Approval |
|-----------|-------------|------------------------------|-------------|-----------------|
| `main`    | Production  | https://iamjos.id            | ✅          | ✅ Required     |
| `staging` | Staging     | https://staging.iamjos.id    | ✅          | ❌              |
| `dev`     | Development | https://dev.iamjos.id        | ✅          | ❌              |

## ⚙️ GitHub Secrets Configuration

Secrets yang diperlukan di GitHub repository settings:

```
SERVER_IP          → IP address VPS
SERVER_USER        → SSH username
SSH_PRIVATE_KEY    → Private key untuk SSH access
SERVER_PORT        → SSH port (default: 22)
PROD_PATH          → Path ke production directory
STAGING_PATH       → Path ke staging directory
DEV_PATH           → Path ke development directory
```

## 🔐 Production Deployment Approval

Untuk branch `main`, deployment memerlukan **manual approval**:

1. Push ke `main` → Tests & Build berjalan otomatis
2. Job Deploy akan **menunggu approval**
3. Reviewer harus approve di GitHub Actions UI
4. Setelah approved, deployment berjalan otomatis

**Setup approval:**
- GitHub → Settings → Environments → `production`
- Add required reviewers

## 📊 Monitoring Deployment

### Melihat Status Pipeline

1. Buka repository di GitHub
2. Klik tab **Actions**
3. Lihat workflow run terbaru

### Melihat Logs

- **Test logs:** Job "Test & Analisis Statis"
- **Build logs:** Job "Build Assets Production"
- **Deploy logs:** Job "Deploy ke {environment}"

### Health Check

Setelah deployment, sistem otomatis melakukan health check:

```bash
curl https://iamjos.id/api/v1/health
```

**Response:**
- `200` → Healthy
- `503` → Degraded/Unhealthy (tapi aplikasi berjalan)
- Other → Deployment failed

## 🛡️ Safety Features

### 1. Database Backup (Production & Staging)
Sebelum migration, sistem otomatis backup database:
```
/var/backups/iamjos/backup-{branch}-{timestamp}.sql.gz
```

Backup lama (>7 hari) otomatis dihapus.

### 2. Graceful Service Restart
Menggunakan `reload` bukan `restart` untuk zero-downtime:
```bash
sudo systemctl reload php8.4-fpm
sudo systemctl reload nginx
```

### 3. Rollback Strategy
Jika deployment gagal:
1. Health check akan mendeteksi
2. Pipeline akan fail
3. Restore dari backup database terakhir
4. Rollback ke commit sebelumnya

## 🐛 Troubleshooting

### Pipeline Gagal di Test Stage
```bash
# Jalankan test di local
php artisan test --parallel

# Cek specific test
php artisan test --filter=NativeXmlExportTest
```

### Pipeline Gagal di Build Stage
```bash
# Jalankan build di local
npm ci
npm run build
```

### Deployment Gagal
```bash
# SSH ke VPS
ssh user@server-ip

# Cek logs aplikasi
cd /path/to/app
tail -f storage/logs/laravel.log

# Cek service status
sudo systemctl status php8.4-fpm
sudo systemctl status nginx
```

## 📝 Recent Fixes

### ✅ MissingAppKeyException Fix (2024)
**Problem:** Test suite gagal dengan `MissingAppKeyException` di parallel execution

**Solution:** Capture dan export `APP_KEY` ke GitHub Actions environment:
```yaml
APP_KEY=$(grep '^APP_KEY=' .env | cut -d '=' -f2)
echo "APP_KEY=$APP_KEY" >> $GITHUB_ENV
```

**Documentation:** `docs/BUGFIX_APP_KEY_GITHUB_ACTIONS.md`

## 🔗 Related Documentation

- [GitHub Actions Workflow](.github/workflows/deploy.yml)
- [Bugfix: APP_KEY Exception](BUGFIX_APP_KEY_GITHUB_ACTIONS.md)
- [Setup Guide](../SETUP.md)
- [Supervisor Configuration](SUPERVISOR_CONFIG.md)

## 📞 Support

Jika ada masalah dengan deployment:
1. Cek GitHub Actions logs
2. Cek VPS application logs
3. Contact: support@iamjos.id

---

**Last Updated:** 2024 (setelah bugfix MissingAppKeyException)
