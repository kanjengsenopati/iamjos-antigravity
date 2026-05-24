# Deployment URL Update

**Date**: 2026-05-24  
**Status**: ✅ COMPLETED

## Overview

Updated deployment URLs from `iamjos.id` to `ejournal.apdesyi.or.id` across all environments.

## Changes Made

### 1. GitHub Actions Workflow

**File**: `.github/workflows/deploy.yml`

**Environment URLs Updated**:
```yaml
# BEFORE:
environment:
  url: ${{ github.ref_name == 'main' && 'https://iamjos.id' || ... }}

# AFTER:
environment:
  url: ${{ github.ref_name == 'main' && 'https://ejournal.apdesyi.or.id' || ... }}
```

**Deployment Variables Updated**:
```bash
# BEFORE:
APP_URL=https://iamjos.id              # Production
APP_URL=https://staging.iamjos.id      # Staging
APP_URL=https://dev.iamjos.id          # Development

# AFTER:
APP_URL=https://ejournal.apdesyi.or.id              # Production
APP_URL=https://staging.ejournal.apdesyi.or.id      # Staging
APP_URL=https://dev.ejournal.apdesyi.or.id          # Development
```

### 2. Environment Example File

**File**: `.env.example`

**Updated**:
```env
# BEFORE:
APP_URL=http://localhost

# AFTER:
APP_URL=https://ejournal.apdesyi.or.id
```

## Deployment Environments

### Production
- **Branch**: `main`
- **URL**: https://ejournal.apdesyi.or.id
- **Environment**: production (requires manual approval)
- **Target Path**: `${{ secrets.PROD_PATH }}`

### Staging
- **Branch**: `staging`
- **URL**: https://staging.ejournal.apdesyi.or.id
- **Environment**: staging
- **Target Path**: `${{ secrets.STAGING_PATH }}`

### Development
- **Branch**: `dev`
- **URL**: https://dev.ejournal.apdesyi.or.id
- **Environment**: development
- **Target Path**: `${{ secrets.DEV_PATH }}`

## Health Check Verification

After deployment, the workflow automatically verifies the application is running by checking:

```bash
curl -s -o /dev/null -w "%{http_code}" \
  --max-time 10 \
  "https://ejournal.apdesyi.or.id/api/v1/health"
```

Expected responses:
- `200` = healthy (all services running)
- `503` = degraded/unhealthy (but application is running)

## DNS Configuration Required

Ensure DNS records are properly configured:

```
# Production
ejournal.apdesyi.or.id          A/CNAME    → Server IP

# Staging (if needed)
staging.ejournal.apdesyi.or.id  A/CNAME    → Server IP

# Development (if needed)
dev.ejournal.apdesyi.or.id      A/CNAME    → Server IP
```

## SSL/TLS Certificate

Ensure SSL certificates are configured for:
- `ejournal.apdesyi.or.id`
- `staging.ejournal.apdesyi.or.id` (if used)
- `dev.ejournal.apdesyi.or.id` (if used)

Recommended: Use Let's Encrypt with automatic renewal via certbot.

## Server Configuration

Update Nginx/Apache virtual host configuration to use new domain:

### Nginx Example
```nginx
server {
    listen 80;
    listen [::]:80;
    server_name ejournal.apdesyi.or.id;
    
    # Redirect to HTTPS
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name ejournal.apdesyi.or.id;
    
    ssl_certificate /etc/letsencrypt/live/ejournal.apdesyi.or.id/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/ejournal.apdesyi.or.id/privkey.pem;
    
    root /path/to/iamjos/public;
    index index.php;
    
    # ... rest of configuration
}
```

## Application Configuration

After deployment, ensure `.env` file on server has correct APP_URL:

```env
APP_URL=https://ejournal.apdesyi.or.id
```

This affects:
- Asset URLs (CSS, JS, images)
- Email links
- API responses
- OAI-PMH base URL
- Sitemap generation
- Canonical URLs

## Verification Steps

1. ✅ Update workflow file with new URLs
2. ✅ Update .env.example with production URL
3. ⏳ Commit and push changes
4. ⏳ Wait for GitHub Actions to deploy
5. ⏳ Verify DNS resolution
6. ⏳ Verify SSL certificate
7. ⏳ Test application access
8. ⏳ Verify health check endpoint

## Testing Checklist

After deployment, verify:

- [ ] Homepage loads: https://ejournal.apdesyi.or.id
- [ ] Health check: https://ejournal.apdesyi.or.id/api/v1/health
- [ ] Assets load correctly (CSS, JS, images)
- [ ] Login functionality works
- [ ] Journal pages accessible
- [ ] OAI-PMH endpoint works
- [ ] Admin panel accessible
- [ ] Email links use correct domain

## Rollback Plan

If issues occur, rollback by:

1. Revert commit with URL changes
2. Push to trigger re-deployment
3. Or manually update `.env` on server:
   ```bash
   cd /path/to/iamjos
   nano .env
   # Change APP_URL back to old value
   php artisan config:cache
   sudo systemctl reload php8.4-fpm
   sudo systemctl reload nginx
   ```

## Related Documentation

- `.github/workflows/deploy.yml` - CI/CD pipeline configuration
- `docs/AUTO_DEPLOYMENT_WORKFLOW.md` - Deployment workflow documentation
- `SETUP.md` - Server setup and configuration guide

---

**Status**: ✅ COMPLETED  
**Production URL**: https://ejournal.apdesyi.or.id  
**Last Updated**: 2026-05-24
