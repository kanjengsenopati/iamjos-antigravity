# 🚀 IamJOS — Quick Setup Guide

IamJOS is an open-source academic journal management system inspired by [OJS (Open Journal Systems)](https://pkp.sfu.ca/software/ojs/).  
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
- [ ] Set up queue workers: `php artisan queue:work`

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

See [LICENSE](LICENSE) for details.
