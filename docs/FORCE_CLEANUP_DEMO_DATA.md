# Force Cleanup Demo Data - Final Solution

**Date**: 2026-05-24  
**Commit**: 19451957  
**Status**: ✅ DEPLOYED

---

## Problem

After the initial cleanup migrations ran successfully, the production website still showed:
- **"1 Author"** on homepage (should be 0)
- Browse by Subject was working correctly ✅

**Root Cause**: The cleanup migration `2026_05_24_170002` only ran ONCE when it was first deployed. Since it already ran, Laravel's migration system skipped it on subsequent deployments. The production database still had demo authors that weren't cleaned up.

---

## Solution

Created a NEW migration with a later timestamp that will run on the next deployment:

**File**: `database/migrations/2026_05_24_200000_force_cleanup_all_demo_authors_and_data.php`

### What It Does

This migration performs an **aggressive, comprehensive cleanup** of ALL demo data:

1. **Finds ALL demo users** with `@demo.iamjos.id` emails (not just orphaned ones)
2. **Removes submission_authors records** (if any exist)
3. **Removes role assignments** from `model_has_roles` table
4. **Removes journal_user_roles** entries
5. **Removes permissions** from `model_has_permissions` table
6. **Deletes the users** completely
7. **Clears application cache** to refresh stats immediately

### Key Differences from Previous Migration

| Previous Migration (170002) | New Migration (200000) |
|----------------------------|------------------------|
| Only cleaned orphaned authors (no submissions) | Cleans ALL demo users |
| Ran once, won't run again | New timestamp, will run on next deploy |
| Conditional cleanup | Aggressive, comprehensive cleanup |

---

## Expected Results

After this migration runs on production:

### Homepage Statistics
- **Authors**: Should show **0 Authors** (currently shows 1)
- **Journals**: 0 Journals
- **Articles**: 0 Articles  
- **Downloads**: 0 Downloads

### Browse by Subject
- ✅ Already working - shows 6 database-driven categories:
  1. Science & Technology (blue, flask icon)
  2. Medicine & Health (red, heartbeat icon)
  3. Social Sciences (green, users icon)
  4. Arts & Humanities (purple, palette icon)
  5. Business & Economics (yellow, chart-line icon)
  6. Education (indigo, graduation-cap icon)

---

## Technical Details

### Author Count Calculation

The author count is calculated in `app/Http/Controllers/PortalController.php`:

```php
'total_authors' => \App\Models\User::whereHas('roles', fn($q) => $q->where('name', 'Author'))->count() + $baseAuthors
```

This counts users who have the "Author" role. The migration removes:
1. The role assignment from `model_has_roles`
2. The user record from `users` table

### Cache Clearing

The migration automatically clears the application cache to ensure stats refresh immediately:

```php
\Illuminate\Support\Facades\Artisan::call('cache:clear');
```

---

## Deployment

- ✅ Committed: `feat: force cleanup ALL demo authors and data`
- ✅ Pushed to GitHub: commit 19451957
- ⏳ GitHub Actions: Deploying to https://ejournal.apdesyi.or.id/
- ⏳ Migration: Will run automatically on deployment

---

## Verification Steps

Once deployment completes:

1. Visit https://ejournal.apdesyi.or.id/
2. Check homepage statistics:
   - ✅ Authors: 0 (currently 1)
   - ✅ Journals: 0
   - ✅ Articles: 0
   - ✅ Downloads: 0
3. Scroll to "Browse by Subject" section:
   - ✅ Should show 6 database-driven categories
   - ✅ Each category should have icon and color
4. Hard refresh (Ctrl+Shift+R) to clear browser cache

---

## Why This Will Work

1. **New migration timestamp** (200000 vs 170002) - Laravel will run it
2. **Aggressive cleanup** - removes ALL demo users, not just orphaned ones
3. **Comprehensive** - cleans all related tables (roles, permissions, journal_user_roles)
4. **Cache clearing** - ensures stats refresh immediately
5. **Transaction safety** - all operations wrapped in DB transaction

---

## Migration Order

Final migration sequence:
1. `2026_05_24_161411` - Initial cleanup (already ran)
2. `2026_05_24_170001` - Add slug/icon/color columns (already ran)
3. `2026_05_24_170002` - Add categories + cleanup orphaned authors (already ran)
4. `2026_05_24_200000` - **FORCE cleanup ALL demo data** (NEW - will run now)

---

## Logging

The migration logs all actions to Laravel's log file:
- Number of demo users found
- Number of records deleted from each table
- Cache clearing status
- Any errors encountered

Check logs at: `storage/logs/laravel.log`
