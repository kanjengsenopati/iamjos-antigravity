# Implementation Plan: Fix PailServiceProvider Not Found

## Root Cause Analysis
The fatal error Class \
Laravel\Pail\PailServiceProvider\ not found occurs on existing environments (like IAMJOS ID) during the deployment step php artisan migrate. 
Here is what happened:
1. Previously, these existing servers might have had dev-dependencies installed, or their ootstrap/cache/packages.php was caching the PailServiceProvider.
2. Our new CI/CD pipeline correctly builds and ships the endor/ folder **without dev-dependencies** (--no-dev), which removes laravel/pail.
3. When the new endor/ is extracted to the server, the old ootstrap/cache/packages.php still exists and references the now-deleted PailServiceProvider.
4. The moment the deployment script runs php artisan migrate, Laravel tries to boot up, reads the old packages.php, cannot find the class, and crashes.

## Proposed Changes
We need to manually clear Laravel's cache files **before** running any php artisan commands in the deployment script.

### .github/workflows/deploy.yml
In both deploy-production and deploy-preview SSH scripts, right before executing php artisan migrate, we will add a command to delete the old cache files:
\\\ash
# Bersihkan cache usang untuk menghindari error class not found
rm -f bootstrap/cache/*.php
\\\

## Verification
1. Push the fix to deploy.yml.
2. Monitor the GitHub Actions deployment.
3. The deployment to IAMJOS ID (and other existing sites) should no longer crash at the database migration step.

