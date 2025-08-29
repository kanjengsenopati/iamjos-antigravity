# Home Ads Counter API Documentation

## Overview
API untuk tracking view dan click counter pada iklan homepage. Dirancang untuk performa tinggi dengan menggunakan Redis cache dan batch updates untuk mengurangi beban database.

## Features
- **Real-time tracking**: View dan click counting menggunakan Redis
- **Batch updates**: Counter disinkronisasi ke database secara batch untuk performa optimal
- **Lightweight endpoints**: API endpoint yang ringan dan cepat
- **Detailed statistics**: Analytics lengkap dengan proyeksi dan performa metrics
- **Auto-sync**: Sinkronisasi otomatis menggunakan Laravel Scheduler

## API Endpoints

### 1. Track View
**POST** `/api/v1/home-ads/{id}/view`

Track ketika iklan dilihat/ditampilkan.

**Parameters:**
- `id` (required): ID iklan

**Response:**
```json
{
    "code": 200,
    "message": "Successfully Get Data",
    "data": {
        "message": "View counted successfully",
        "ads_id": "9d3f8c2a-1234-5678-9abc-def123456789"
    }
}
```

**Usage Example:**
```javascript
// Track view ketika iklan muncul di viewport
fetch('/api/v1/home-ads/{ads_id}/view', {
    method: 'POST',
    headers: {
        'X-API-KEY': 'your-api-key'
    }
});
```

### 2. Track Click
**POST** `/api/v1/home-ads/{id}/click`

Track ketika iklan diklik.

**Parameters:**
- `id` (required): ID iklan

**Response:**
```json
{
    "code": 200,
    "message": "Successfully Get Data",
    "data": {
        "message": "Click counted successfully",
        "ads_id": "9d3f8c2a-1234-5678-9abc-def123456789",
        "redirect_url": "https://example.com"
    }
}
```

**Usage Example:**
```javascript
// Track click dan redirect
document.getElementById('ads-banner').addEventListener('click', async function() {
    const response = await fetch('/api/v1/home-ads/{ads_id}/click', {
        method: 'POST',
        headers: {
            'X-API-KEY': 'your-api-key'
        }
    });
    
    const data = await response.json();
    if (data.data.redirect_url) {
        window.open(data.data.redirect_url, '_blank');
    }
});
```

### 3. Get Statistics
**GET** `/api/v1/home-ads/{id}/statistics`

Mengambil statistik detail untuk iklan tertentu.

**Parameters:**
- `id` (required): ID iklan

**Response:**
```json
{
    "code": 200,
    "message": "Successfully Get Data",
    "data": {
        "ads": {
            "id": "9d3f8c2a-1234-5678-9abc-def123456789",
            "media_type": "image",
            "media_url": "/storage/home_ads/banner.jpg",
            "link": "https://example.com",
            "is_active": true,
            "start_date": "2025-08-01T00:00:00.000000Z",
            "end_date": "2025-08-31T23:59:59.000000Z",
            "created_at": "2025-08-01T10:00:00.000000Z"
        },
        "statistics": {
            "total_views": 15750,
            "total_clicks": 892,
            "pending_views": 3,
            "pending_clicks": 1,
            "ctr_percentage": 5.67,
            "days_remaining": 2,
            "total_campaign_days": 31,
            "avg_views_per_day": 508.06,
            "avg_clicks_per_day": 28.77
        }
    }
}
```

## Performance & Architecture

### Redis Caching Strategy
- **View Counter**: `ads_view_count:{ads_id}`
- **Click Counter**: `ads_click_count:{ads_id}`
- **TTL**: 24 jam untuk auto-cleanup
- **Batch Size**: Views sync setiap 10 count, Clicks setiap 5 count

### Database Sync
1. **Real-time**: Counter disimpan di Redis untuk performa tinggi
2. **Batch Updates**: Sinkronisasi ke database secara batch
3. **Auto Sync**: Laravel Scheduler menjalankan sync setiap 5 menit
4. **Manual Sync**: Command `php artisan ads:sync-counters`

### Error Handling
- API tetap responsif meski database bermasalah
- Graceful degradation jika Redis tidak tersedia
- Error logging untuk monitoring

## Installation & Setup

### 1. Jalankan Migration (jika diperlukan)
```bash
php artisan migrate
```

### 2. Setup Redis
Pastikan Redis sudah terinstall dan terkonfigurasi di `.env`:
```env
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

### 3. Setup Queue Worker (optional untuk background jobs)
```bash
php artisan queue:work
```

### 4. Setup Scheduler
Tambahkan ke crontab:
```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

## Commands

### Sync Counters
```bash
# Sync semua pending counters
php artisan ads:sync-counters

# Force sync meski count kecil
php artisan ads:sync-counters --force
```

## Monitoring

### CMS Admin Dashboard
- Akses detail statistik di: `/home-ads/{id}`
- View analytics lengkap dengan grafik dan proyeksi
- Monitor pending counters dan real-time data

### Logs
- Error logs tersimpan di `storage/logs/laravel.log`
- Sync job logs untuk monitoring

## Best Practices

1. **Frontend Implementation**:
   - Track view ketika iklan 50% visible di viewport
   - Debounce click events untuk mencegah double-count
   - Handle API errors gracefully

2. **Performance**:
   - API endpoints sangat ringan, safe untuk high-traffic
   - Jangan sync manual terlalu sering
   - Monitor Redis memory usage

3. **Security**:
   - Selalu gunakan API key authentication
   - Validate ads ID dan status sebelum tracking
   - Rate limiting untuk mencegah abuse

## Troubleshooting

### Redis Issues
```bash
# Check Redis connection
redis-cli ping

# Monitor Redis keys
redis-cli KEYS ads_*_count:*
```

### Database Sync Issues
```bash
# Manual force sync
php artisan ads:sync-counters --force

# Check queue status
php artisan queue:failed
```

### Performance Issues
- Monitor database slow queries
- Check Redis memory usage
- Verify scheduler is running properly
