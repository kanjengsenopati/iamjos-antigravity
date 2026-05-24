# Supervisor Configuration untuk IAMJOS Queue Workers

Supervisor adalah process manager yang memastikan queue worker IAMJOS berjalan terus-menerus di production.

## Instalasi Supervisor

```bash
# Ubuntu/Debian
sudo apt-get install supervisor
sudo systemctl enable supervisor
sudo systemctl start supervisor
```

## Konfigurasi Queue Worker

Buat file `/etc/supervisor/conf.d/iamjos-worker.conf`:

```ini
[program:iamjos-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/iamjos/artisan queue:work redis --queue=%(ENV_IAMJOS_INSTANCE_ID)s-default --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/log/supervisor/iamjos-worker.log
stdout_logfile_maxbytes=10MB
stdout_logfile_backups=5
stopwaitsecs=3600
```

**Catatan penting:** Ganti `%(ENV_IAMJOS_INSTANCE_ID)s` dengan nilai `IAMJOS_INSTANCE_ID` dari `.env` Anda.
Contoh jika `IAMJOS_INSTANCE_ID=iamjos-jurnal-xyz`:

```ini
command=php /var/www/iamjos/artisan queue:work redis --queue=iamjos-jurnal-xyz-default --sleep=3 --tries=3 --max-time=3600
```

## Konfigurasi untuk Multiple Instances di Satu Server

Jika menjalankan beberapa instance IAMJOS di satu server, buat file terpisah per instance:

```ini
; /etc/supervisor/conf.d/iamjos-jurnal-a-worker.conf
[program:iamjos-jurnal-a-worker]
command=php /var/www/iamjos-jurnal-a/artisan queue:work redis --queue=iamjos-jurnal-a-default --sleep=3 --tries=3 --max-time=3600
user=www-data
numprocs=1
autostart=true
autorestart=true
redirect_stderr=true
stdout_logfile=/var/log/supervisor/iamjos-jurnal-a-worker.log

; /etc/supervisor/conf.d/iamjos-jurnal-b-worker.conf
[program:iamjos-jurnal-b-worker]
command=php /var/www/iamjos-jurnal-b/artisan queue:work redis --queue=iamjos-jurnal-b-default --sleep=3 --tries=3 --max-time=3600
user=www-data
numprocs=1
autostart=true
autorestart=true
redirect_stderr=true
stdout_logfile=/var/log/supervisor/iamjos-jurnal-b-worker.log
```

## Konfigurasi Scheduler (Cron)

Tambahkan ke crontab (`crontab -e` sebagai user www-data):

```cron
* * * * * cd /var/www/iamjos && php artisan schedule:run >> /dev/null 2>&1
```

## Perintah Supervisor

```bash
# Reload konfigurasi setelah perubahan
sudo supervisorctl reread
sudo supervisorctl update

# Cek status semua worker
sudo supervisorctl status

# Restart worker (setelah deploy)
sudo supervisorctl restart iamjos-worker:*

# Stop semua worker
sudo supervisorctl stop iamjos-worker:*

# Lihat log
sudo tail -f /var/log/supervisor/iamjos-worker.log
```

## Verifikasi Queue Worker Aktif

Setelah worker berjalan, cek Health Check API:

```bash
curl https://your-domain.com/api/v1/health
```

Field `checks.queue.status` harus bernilai `"ok"` jika worker aktif dalam 5 menit terakhir.

## Troubleshooting

**Worker tidak mau start:**
```bash
sudo supervisorctl tail iamjos-worker stderr
```

**Queue menumpuk (backlog tinggi):**
```bash
# Cek jumlah job pending
php artisan queue:monitor redis:iamjos-instance-1-default

# Tambah numprocs di supervisor config
```

**Worker crash setelah deploy:**
```bash
# Restart gracefully setelah deploy
php artisan queue:restart
sudo supervisorctl restart iamjos-worker:*
```
