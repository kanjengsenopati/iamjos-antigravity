# FIX: Browse by Subject - Empty by Default

**Date**: 2026-05-24  
**Status**: FIXED  
**Issue**: Browse by Subject section masih menampilkan kartu dan judul meskipun database kosong

---

## Problem

### User Report
> "Semua perubahan sudah sukses, kecuali section Browse By Subject, kartu dan judul itu seharusnya kosong secara default, baru akan muncul jika di setting di super admin panel, cleanup hardcoded tersebut, sinkronkan dengan Subject Fields"

### Issues Identified
1. **Browse by Subject section masih muncul** meskipun tidak ada categories di database
2. **Kartu categories masih ditampilkan** dari data hardcoded di config
3. **Tidak sinkron dengan Subject Fields filter** yang sudah database-driven

---

## Root Cause Analysis

### Issue 1: View Component Menggunakan Config Categories
**File**: `resources/views/components/site/blocks/subject-categories.blade.php`

**Kode Lama**:
```php
$defaultCategories = [];
$categories = $config['categories'] ?? $defaultCategories;
$categoryData = $data['categories'] ?? [];
```

**Problem**:
- Component menggunakan `$config['categories']` yang bisa berisi data hardcoded
- Tidak menggunakan `$data['categories']` dari database
- Section tetap muncul meskipun tidak ada data dari database

### Issue 2: Controller Tidak Filter Site-Level Categories
**File**: `app/Http/Controllers/PortalController.php`

**Kode Lama**:
```php
return \App\Models\Category::orderBy('sort_order')
    ->get()
    ->map(function ($category) {
        // ...
    });
```

**Problem**:
- Memuat SEMUA categories tanpa filter `whereNull('journal_id')`
- Bisa memuat journal-level categories yang seharusnya tidak ditampilkan
- Tidak filter `is_active = true`

### Issue 3: Tidak Ada Kondisi untuk Hide Section
**Problem**:
- Section selalu ditampilkan meskipun `$categories` kosong
- Judul "Browse by Subject" tetap muncul
- Tidak ada kondisi `@if($categories->isNotEmpty())`

---

## Solution Implemented

### Fix 1: View Component - Gunakan Database Data Only
**File**: `resources/views/components/site/blocks/subject-categories.blade.php`

**Perubahan**:
```php
// BEFORE
$defaultCategories = [];
$categories = $config['categories'] ?? $defaultCategories;
$categoryData = $data['categories'] ?? [];

// AFTER
// ONLY use database-driven categories from $data
// NO hardcoded categories - admin must configure via Super Admin Panel
$categories = $data['categories'] ?? collect([]);
```

**Rationale**:
- Hanya menggunakan `$data['categories']` dari database
- Tidak ada fallback ke config atau hardcoded data
- Jika database kosong, `$categories` akan menjadi empty collection

### Fix 2: View Component - Hide Section When Empty
**File**: `resources/views/components/site/blocks/subject-categories.blade.php`

**Perubahan**:
```php
// BEFORE
<section class="py-16 md:py-24 bg-white">
    <!-- content -->
</section>

// AFTER
{{-- Only show section if categories exist in database --}}
@if($categories->isNotEmpty())
<section class="py-16 md:py-24 bg-white">
    <!-- content -->
</section>
@endif
```

**Rationale**:
- Section hanya muncul jika ada categories di database
- Judul "Browse by Subject" tidak muncul jika kosong
- Konsisten dengan philosophy: empty by default

### Fix 3: View Component - Gunakan journal_count dari Data
**File**: `resources/views/components/site/blocks/subject-categories.blade.php`

**Perubahan**:
```php
// BEFORE
$count = $categoryData[$category['slug'] ?? ''] ?? 0;

// AFTER
$count = $category['journal_count'] ?? 0;
```

**Rationale**:
- Menggunakan `journal_count` yang sudah ada di array category
- Tidak perlu lookup ke `$categoryData` yang sudah tidak digunakan
- Lebih sederhana dan konsisten

### Fix 4: Controller - Filter Site-Level Categories Only
**File**: `app/Http/Controllers/PortalController.php`

**Perubahan**:
```php
// BEFORE
return \App\Models\Category::orderBy('sort_order')
    ->get()
    ->map(function ($category) {
        return [
            'name' => $category->name,
            'slug' => $category->slug,
            'description' => $category->description,
            'icon' => $category->icon ?? 'folder',
            'color' => $category->color ?? 'primary',
            'journal_count' => 0,
        ];
    });

// AFTER
return \App\Models\Category::whereNull('journal_id') // Only site-level categories
    ->where('is_active', true) // Only active categories
    ->orderBy('sort_order')
    ->get()
    ->map(function ($category) {
        return [
            'name' => $category->name,
            'slug' => $category->slug,
            'description' => $category->description,
            'icon' => $category->icon ?? 'fa-folder',
            'color' => $category->color ?? 'blue',
            'journal_count' => 0, // Can be enhanced later
        ];
    });
```

**Rationale**:
- Filter `whereNull('journal_id')` untuk hanya site-level categories
- Filter `where('is_active', true)` untuk hanya active categories
- Sinkron dengan Subject Fields filter yang juga menggunakan filter yang sama
- Default icon `fa-folder` dan color `blue` konsisten dengan design

---

## Synchronization with Subject Fields

### Subject Fields Filter (Sidebar)
**File**: `app/Http/Controllers/PortalController.php` (journals method)

**Kode**:
```php
$subjectCategories = \App\Models\Category::whereNull('journal_id')
    ->where('is_active', true)
    ->orderBy('sort_order')
    ->get();
```

### Browse by Subject (Homepage)
**File**: `app/Http/Controllers/PortalController.php` (loadBlockData method)

**Kode**:
```php
return \App\Models\Category::whereNull('journal_id')
    ->where('is_active', true)
    ->orderBy('sort_order')
    ->get()
```

**Status**: ✅ **SYNCHRONIZED**
- Kedua menggunakan filter yang sama: `whereNull('journal_id')` dan `where('is_active', true)`
- Kedua menggunakan sort order yang sama: `orderBy('sort_order')`
- Kedua memuat dari database yang sama: `categories` table
- Jika database kosong, kedua akan kosong

---

## Expected Results After Deployment

### Homepage (https://ejournal.apdesyi.or.id/)

#### When Database is Empty (Default)
- **Browse by Subject section**: **TIDAK MUNCUL** (hidden completely)
- **Judul "Browse by Subject"**: **TIDAK MUNCUL**
- **Kartu categories**: **TIDAK MUNCUL**
- **Subtitle**: **TIDAK MUNCUL**

#### When Admin Adds Categories
- **Browse by Subject section**: **MUNCUL**
- **Judul "Browse by Subject"**: **MUNCUL**
- **Kartu categories**: **MUNCUL** (sesuai data dari database)
- **Subtitle**: **MUNCUL**

### Journals Page (https://ejournal.apdesyi.or.id/journals)

#### Subject Fields Filter
- **When database empty**: Filter **TIDAK MUNCUL**
- **When admin adds categories**: Filter **MUNCUL** dengan checkboxes

**Status**: ✅ **SYNCHRONIZED** dengan Browse by Subject

---

## Database-Driven Confirmation

### Browse by Subject
**Source**: `categories` table
**Filter**: `journal_id IS NULL AND is_active = true`
**Sort**: `sort_order ASC`
**Status**: ✅ **DATABASE-DRIVEN**

### Subject Fields Filter
**Source**: `categories` table
**Filter**: `journal_id IS NULL AND is_active = true`
**Sort**: `sort_order ASC`
**Status**: ✅ **DATABASE-DRIVEN**

### Synchronization
**Status**: ✅ **SYNCHRONIZED**
- Menggunakan table yang sama
- Menggunakan filter yang sama
- Menggunakan sort order yang sama
- Jika satu kosong, yang lain juga kosong
- Jika admin menambah category, muncul di kedua tempat

---

## Files Changed

### 1. View Component
**File**: `resources/views/components/site/blocks/subject-categories.blade.php`

**Changes**:
- Remove hardcoded categories fallback
- Use only `$data['categories']` from database
- Add `@if($categories->isNotEmpty())` condition
- Use `$category['journal_count']` instead of lookup
- Hide entire section when no categories

### 2. Controller
**File**: `app/Http/Controllers/PortalController.php`

**Changes**:
- Add `whereNull('journal_id')` filter
- Add `where('is_active', true)` filter
- Change default icon to `fa-folder`
- Change default color to `blue`
- Synchronize with Subject Fields filter

---

## Verification Steps

### 1. Check Database
```sql
-- Should return 0 for site-level categories
SELECT COUNT(*) FROM categories WHERE journal_id IS NULL;
```

Expected: **0**

### 2. Check Homepage
Visit: https://ejournal.apdesyi.or.id/

**Expected**:
- ✅ Browse by Subject section **TIDAK MUNCUL**
- ✅ Judul "Browse by Subject" **TIDAK MUNCUL**
- ✅ Kartu categories **TIDAK MUNCUL**

### 3. Check Journals Page
Visit: https://ejournal.apdesyi.or.id/journals

**Expected**:
- ✅ Subject Fields filter **TIDAK MUNCUL**
- ✅ Synchronized dengan Browse by Subject

### 4. Hard Refresh Browser
Press **Ctrl+Shift+R** (Windows/Linux) or **Cmd+Shift+R** (Mac)

### 5. Clear Cache (if needed)
```bash
php artisan cache:clear
```

---

## Admin Configuration Guide

### How to Add Categories (Browse by Subject)

1. **Login as Super Admin**
   - Navigate to Super Admin Panel

2. **Go to Categories Management**
   - Click "Categories" menu
   - Click "Add New Category"

3. **Fill Category Details**
   - **Name**: e.g., "Science & Technology"
   - **Slug**: e.g., "science-technology" (auto-generated)
   - **Description**: e.g., "Computer Science, Engineering, Mathematics"
   - **Icon**: e.g., "fa-flask" (Font Awesome icon)
   - **Color**: e.g., "blue" (blue, red, green, purple, amber, etc.)
   - **Sort Order**: e.g., 1 (lower number = higher priority)
   - **Is Active**: ✅ Checked
   - **Journal ID**: Leave **EMPTY** (for site-level category)

4. **Save Category**
   - Click "Save"
   - Category will appear in both:
     - Browse by Subject (homepage)
     - Subject Fields filter (journals page)

5. **Repeat for Other Categories**
   - Add as many categories as needed
   - They will automatically appear in both places

---

## Philosophy Confirmation

### ✅ Production Starts CLEAN
1. ✅ **No Hardcoded Data** - View component tidak menggunakan config categories
2. ✅ **Database-Driven Only** - Hanya memuat dari database
3. ✅ **Empty by Default** - Section hidden jika database kosong
4. ✅ **Admin Configuration** - Categories hanya muncul setelah admin configure

### ✅ Synchronized with Subject Fields
1. ✅ **Same Data Source** - Kedua menggunakan `categories` table
2. ✅ **Same Filters** - Kedua menggunakan `whereNull('journal_id')` dan `where('is_active', true)`
3. ✅ **Same Sort Order** - Kedua menggunakan `orderBy('sort_order')`
4. ✅ **Same Behavior** - Jika satu kosong, yang lain juga kosong

---

## Commit Message

```
fix: Browse by Subject empty by default, sync with Subject Fields

- Remove hardcoded categories fallback in view component
- Use only database-driven categories from $data
- Hide entire section when no categories in database
- Add whereNull('journal_id') filter in controller
- Add where('is_active', true) filter in controller
- Synchronize with Subject Fields filter logic
- Change default icon to fa-folder and color to blue

Ensures: Browse by Subject hidden by default
Ensures: Only shows when admin configures categories
Ensures: Synchronized with Subject Fields filter
```

---

## Success Criteria

- [x] View component tidak menggunakan hardcoded categories
- [x] View component hanya menggunakan `$data['categories']`
- [x] Section hidden jika `$categories->isEmpty()`
- [x] Controller filter `whereNull('journal_id')`
- [x] Controller filter `where('is_active', true)`
- [ ] Homepage tidak menampilkan Browse by Subject section
- [ ] Journals page tidak menampilkan Subject Fields filter
- [ ] Synchronized dengan Subject Fields filter
- [ ] Admin dapat menambah categories via Super Admin Panel
- [ ] Categories muncul di kedua tempat setelah admin configure

---

**Status**: FIXED & READY TO DEPLOY  
**Next**: Commit and push to trigger deployment
