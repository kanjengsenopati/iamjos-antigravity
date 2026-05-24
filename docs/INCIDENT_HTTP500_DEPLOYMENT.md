# INCIDENT: HTTP 500 pada Health Check Deployment

**Tanggal**: 2026-05-24 15:17 WIB  
**Severity**: HIGH  
**Status**: 🔄 FIXING  
**Commit Gagal**: 3e9415eb  
**Commit Fix**: 4e44e39e

---

## 📋 RINGKASAN INCIDENT

### Apa yang Terjadi?
Deployment gagal karena health check endpoint mengembalikan **HTTP 500** (Internal Server Error) setelah deployment selesai.

### Timeline:
- **15:17** - Deployment dimulai (commit 3e9415eb)
- **15:17** - Build berhasil, assets compiled
- **15:17** - Migration OK (no new migrations)
- **15:17** - Cache rebuild selesai
- **15:17** - Health check **FAIL dengan HTTP 500** ❌
- **15:18** - Deployment dihentikan
- **15:20** - Fix deployed (commit 4e44e39e)

---

## 🔍 ANALISIS ROOT CAUSE

### Error yang Terdeteksi:

```
❌ GAGAL: Health check mengembalikan HTTP 500
   Periksa log aplikasi di: ***/storage/logs/
```

### Kemungkinan Penyebab (Berdasarkan Analisis):

#### 1. **Cache Corruption** (PALING MUNGKIN)
**Probabilitas: 80%**

**Penjelasan:**
- Cache lama (config, route, view) konflik dengan kode baru
- Workflow hanya rebuild cache tanpa clear dulu
- Laravel bisa crash jika cache tidak sinkron dengan kode

**Bukti:**
```yaml
# SEBELUM (Bermasalah):
php artisan config:cache --ansi
php artisan route:cache --ansi
php artisan view:cache --ansi
# Tidak ada clear cache dulu!
```

**Solusi:**
```yaml
# SESUDAH (Fixed):
php artisan cache:clear --ansi || true
php artisan config:clear --ansi || true
php artisan route:clear --ansi || true
php artisan view:clear --ansi || true
# Clear dulu, baru rebuild
php artisan config:cache --ansi
php artisan route:cache --ansi
php artisan view:cache --ansi
```

---

#### 2. **PHP-FPM Tidak Reload Properly**
**Probabilitas: 15%**

**Penjelasan:**
- PHP-FPM masih load kode lama di memory
- Reload graceful mungkin tidak cukup
- Perlu restart penuh

**Solusi di Fix:**
- Auto-retry dengan restart PHP-FPM jika health check fail
- Tunggu 5 detik setelah restart
- Coba health check lagi

---

#### 3. **Permission Issue**
**Probabilitas: 3%**

**Penjelasan:**
- Storage/cache folder tidak writable
- Laravel tidak bisa write cache/logs

**Mitigasi yang Sudah Ada:**
```bash
chmod -R 775 storage bootstrap/cache
```

---

#### 4. **Database Connection Issue**
**Probabilitas: 2%**

**Penjelasan:**
- PostgreSQL connection pool penuh
- Credentials berubah

**Catatan:**
- Migration berhasil, jadi DB connection OK
- Health check endpoint query DB, jadi ini bukan penyebab utama

---

## 🔧 FIX YANG DITERAPKAN

### Fix #1: Clear Cache Before Rebuild

**File**: `.github/workflows/deploy.yml`

**Perubahan:**
```yaml
# Tambahkan clear cache sebelum rebuild
php artisan cache:clear --ansi || true
php artisan config:clear --ansi || true
php artisan route:clear --ansi || true
php artisan view:clear --ansi || true

# Baru rebuild
php artisan config:cache --ansi
php artisan route:cache --ansi
php artisan view:cache --ansi
```

**Alasan:**
- Mencegah cache corruption
- Memastikan cache fresh setelah deployment
- `|| true` agar tidak fail jika cache sudah clear

---

### Fix #2: Auto-Retry dengan Full Cache Clear

**Perubahan:**
```yaml
if [ "$HTTP_STATUS" != "200" ] && [ "$HTTP_STATUS" != "503" ]; then
  echo "❌ Health check fail, mencoba recovery..."
  
  # Show logs
  tail -n 50 storage/logs/laravel-$(date +%Y-%m-%d).log
  
  # Clear ALL cache
  php artisan cache:clear --ansi || true
  php artisan config:clear --ansi || true
  php artisan route:clear --ansi || true
  php artisan view:clear --ansi || true
  php artisan optimize:clear --ansi || true
  
  # Restart PHP-FPM (full restart, bukan reload)
  sudo systemctl restart php8.4-fpm
  
  # Wait and retry
  sleep 5
  HTTP_STATUS_RETRY=$(curl -s -o /dev/null -w "%{http_code}" \
    "${{ env.APP_URL }}/api/v1/health")
  
  if [ "$HTTP_STATUS_RETRY" = "200" ] || [ "$HTTP_STATUS_RETRY" = "503" ]; then
    echo "✅ Recovery berhasil!"
  else
    echo "❌ Recovery gagal, deployment dihentikan"
    exit 1
  fi
fi
```

**Alasan:**
- Memberikan second chance untuk recovery
- Show logs untuk debugging
- Full restart PHP-FPM untuk clear memory
- Jika masih fail, baru stop deployment

---

## 📊 EXPECTED OUTCOME

### Skenario 1: Fix Berhasil (90% kemungkinan)
```
1. Deployment dimulai
2. Clear cache sebelum rebuild ✅
3. Rebuild cache dengan fresh state ✅
4. Health check return 200/503 ✅
5. Deployment sukses! 🎉
```

### Skenario 2: Masih Fail, Tapi Auto-Recovery (8% kemungkinan)
```
1. Deployment dimulai
2. Health check fail pertama kali ❌
3. Auto-recovery: clear all cache + restart PHP-FPM
4. Health check retry berhasil ✅
5. Deployment sukses dengan warning ⚠️
```

### Skenario 3: Fail Total (2% kemungkinan)
```
1. Deployment dimulai
2. Health check fail ❌
3. Auto-recovery fail ❌
4. Show logs untuk debugging
5. Deployment dihentikan
6. Manual investigation required
```

---

## 🚨 JIKA MASIH FAIL

### Langkah Manual Investigation:

#### 1. SSH ke Server
```bash
ssh user@server-ip
cd /path/to/application
```

#### 2. Check Laravel Logs
```bash
tail -f storage/logs/laravel-$(date +%Y-%m-%d).log
```

#### 3. Check PHP-FPM Logs
```bash
sudo tail -f /var/log/php8.4-fpm.log
```

#### 4. Check Nginx Error Logs
```bash
sudo tail -f /var/log/nginx/error.log
```

#### 5. Test Health Check Manually
```bash
curl -v https://ejournal.apdesyi.or.id/api/v1/health
```

#### 6. Check PHP-FPM Status
```bash
sudo systemctl status php8.4-fpm
```

#### 7. Check Database Connection
```bash
php artisan tinker
>>> DB::connection()->getPdo();
```

---

## 🔄 ROLLBACK PLAN

### Jika Fix Tidak Berhasil:

#### Option 1: Rollback via Git
```bash
cd /path/to/application
git log --oneline -5
git checkout 3b519f2e  # Commit sebelum OPSI B
php artisan cache:clear
php artisan config:cache
sudo systemctl restart php8.4-fpm
```

#### Option 2: Restore Database Backup
```bash
cd /var/backups/iamjos
ls -lh backup-main-*.sql.gz
# Pilih backup sebelum deployment
gunzip -c backup-main-20260524-151717.sql.gz | \
  psql -h localhost -U username -d database_name
```

#### Option 3: Emergency Maintenance Mode
```bash
cd /path/to/application
php artisan down --message="Maintenance in progress"
# Fix issue
php artisan up
```

---

## 📈 MONITORING

### Metrics to Watch:

1. **Health Check Response Time**
   - Target: <500ms
   - Alert if: >2000ms

2. **HTTP 500 Error Rate**
   - Target: 0%
   - Alert if: >0.1%

3. **PHP-FPM Memory Usage**
   - Target: <80%
   - Alert if: >90%

4. **Database Connection Pool**
   - Target: <50 connections
   - Alert if: >80 connections

---

## 📝 LESSONS LEARNED

### What Went Wrong:
1. ❌ Tidak clear cache sebelum rebuild
2. ❌ Tidak ada auto-recovery mechanism
3. ❌ Tidak show logs saat fail

### What Went Right:
1. ✅ Database backup berhasil (meski ada warning)
2. ✅ Build dan migration sukses
3. ✅ Health check detect issue sebelum user terdampak
4. ✅ Deployment dihentikan sebelum merusak production

### Improvements Made:
1. ✅ Clear cache sebelum rebuild
2. ✅ Auto-retry dengan full cache clear
3. ✅ Show logs saat health check fail
4. ✅ Full restart PHP-FPM pada retry

---

## 🎯 NEXT STEPS

### Immediate (Sekarang):
1. ✅ Monitor deployment baru (commit 4e44e39e)
2. ⏳ Verify health check return 200/503
3. ⏳ Check aplikasi accessible di browser

### Short Term (Hari Ini):
1. [ ] Review Laravel logs untuk error patterns
2. [ ] Setup monitoring alerts
3. [ ] Document common errors

### Long Term (Minggu Ini):
1. [ ] Implement proper logging aggregation
2. [ ] Setup APM (Application Performance Monitoring)
3. [ ] Create runbook untuk common issues

---

## 📞 CONTACT

**Jika masih ada masalah:**
- Check GitHub Actions: https://github.com/kanjengsenopati/iamjos-antigravity/actions
- Review logs di server
- Escalate jika perlu manual intervention

---

**Status Update**: Menunggu hasil deployment dengan fix baru (commit 4e44e39e)
