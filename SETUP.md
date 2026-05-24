# 🚀 IamJOS — Quick Setup Guide

IamJOS is a **commercial proprietary** academic journal management platform. All rights reserved. Unauthorized copying, distribution, or modification is strictly prohibited.  
Fresh installations start **empty** — no demo journals, no sample data. You create journals and assign editors via the Admin Dashboard.

---

## Requirements

| Software      | Version  | Notes                           |
|---------------|----------|---------------------------------|
| PHP           | 8.2+     | With `pgsql`, `gd`, `zip` ext  |
| Composer      | 2.x      |                                 |
| Node.js       | 18+      | For Vite asset build            |
| PostgreSQL    | 15+      | Recommended for production      |
| SQLite        | 3.x      | Zero-setup local development    |

---

## Installation

```bash
# 1. Clone repository
git clone https://github.com/kanjengsenopati/iamjos-antigravity.git
cd iamjos-antigravity

# 2. Install PHP dependencies
composer install

# 3. Install Node.js dependencies
npm install

# 4. Environment setup
cp .env.example .env
php artisan key:generate
```

### 5. Configure Database

**Option A — SQLite (quick local dev, zero setup):**
```env
DB_CONNECTION=sqlite
```
The SQLite file is created automatically during migration.

**Option B — PostgreSQL (recommended for production):**
```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=iamjos
DB_USERNAME=postgres
DB_PASSWORD=your_password
```

### 6. Configure Super Admin

Edit `.env` before seeding:
```env
SUPER_ADMIN_EMAIL=admin@yoursite.com
SUPER_ADMIN_NAME="Your Name"
SUPER_ADMIN_PASSWORD=          # Leave blank to auto-generate
```

### 7. Run Migrations & Seed

```bash
php artisan migrate
php artisan db:seed
```

This creates:
- ✅ Roles & Permissions (RBAC matrix)
- ✅ Super Admin account
- ✅ Site content templates
- ✅ Email & notification templates
- ❌ **No journals** — create them via Admin Dashboard

### 8. Build Assets & Serve

```bash
npm run build                 # Production build
php artisan serve             # http://localhost:8000
```

For development with HMR:
```bash
npm run dev                   # Vite dev server
php artisan serve             # In another terminal
```

---

## Optional: Demo Data

For development/staging, you can seed sample journals and users:

```bash
php artisan db:seed --class=DemoSeeder
```

This creates 5 demo journals and 4 demo users:

| Email                      | Role     |
|---------------------------|----------|
| admin@demo.iamjos.id      | Admin    |
| editor@demo.iamjos.id     | Editor   |
| reviewer@demo.iamjos.id   | Reviewer |
| author@demo.iamjos.id     | Author   |

Default password: `Demo@IamJOS2026!` (configurable via `DEMO_USER_PASSWORD` in `.env`).

> ⚠️ **Never run DemoSeeder in production.**

---

## Storage Link

If you need file uploads to be publicly accessible:
```bash
php artisan storage:link
```

---

## Redis Setup (Production)

Redis diperlukan untuk cache, queue, dan session di production. Driver `database` tidak direkomendasikan untuk production karena setiap operasi cache/queue tetap hit database.

### Instalasi Redis

```bash
# Ubuntu/Debian
sudo apt-get install redis-server
sudo systemctl enable redis-server
sudo systemctl start redis-server
```

### Konfigurasi Password (Wajib di Production)

Edit `/etc/redis/redis.conf`:
```
requirepass your-strong-redis-password
```

Restart Redis: `sudo systemctl restart redis-server`

### Konfigurasi Multiple Database

IAMJOS menggunakan 3 database Redis terpisah untuk menghindari konflik antar penggunaan:

| Database | Env Var | Default | Kegunaan |
|----------|---------|---------|----------|
| DB 0 | `REDIS_DB` | `0` | Queue dan koneksi default |
| DB 1 | `REDIS_CACHE_DB` | `1` | Cache aplikasi |
| DB 2 | `REDIS_SESSION_DB` | `2` | Session pengguna |

Ketika beberapa instance IAMJOS berbagi satu Redis server, isolasi dicapai melalui prefix `IAMJOS_INSTANCE_ID`.

### Verifikasi Koneksi

```bash
redis-cli -a your-password ping
# Output: PONG

redis-cli -a your-password info server | grep redis_version
```

### Konfigurasi `.env` untuk Redis

```env
IAMJOS_INSTANCE_ID=nama-unik-instance-ini

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=your-strong-redis-password
REDIS_PORT=6379
REDIS_DB=0
REDIS_CACHE_DB=1
REDIS_SESSION_DB=2

CACHE_STORE=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
```

---

## Production Deployment Checklist

- [ ] Set `APP_ENV=production` and `APP_DEBUG=false`
- [ ] Set `APP_URL` to your domain
- [ ] Configure PostgreSQL in `.env`
- [ ] Set `SUPER_ADMIN_EMAIL` and `SUPER_ADMIN_PASSWORD`
- [ ] Run `composer install --optimize-autoloader --no-dev`
- [ ] Run `npm run build`
- [ ] Run `php artisan migrate --force`
- [ ] Run `php artisan db:seed --force`
- [ ] Run `php artisan config:cache && php artisan route:cache && php artisan view:cache`
- [ ] Configure web server (Nginx/Apache) to serve `public/` directory
- [ ] Set up SSL (Let's Encrypt / Cloudflare)
- [ ] Configure SMTP for email notifications
- [ ] Set up queue workers via Supervisor (lihat `docs/SUPERVISOR_CONFIG.md`):
  ```bash
  # Nama queue harus sesuai IAMJOS_INSTANCE_ID
  php artisan queue:work redis --queue={IAMJOS_INSTANCE_ID}-default
  ```
- [ ] Set up cron scheduler: `* * * * * php artisan schedule:run`

---

## Directory Structure

```
iamjos-php/
├── app/
│   ├── Http/Controllers/    # Route controllers
│   ├── Models/              # Eloquent models
│   └── Services/            # Business logic services
├── database/
│   ├── migrations/          # Database schema
│   └── seeders/             # Data seeders
│       ├── DatabaseSeeder.php           # Production (clean)
│       └── DemoSeeder.php               # Dev/staging only
├── resources/
│   ├── js/                  # React/Vite frontend
│   ├── css/                 # Stylesheets
│   └── views/               # Blade templates
├── routes/                  # Route definitions
├── .env.example             # Environment template
└── SETUP.md                 # This file
```

---

## License

IamJOS is **commercial proprietary software**. All rights reserved.

Unauthorized copying, distribution, modification, or use of this software without a valid license is strictly prohibited. Contact [support@iamjos.id](mailto:support@iamjos.id) for licensing information.
