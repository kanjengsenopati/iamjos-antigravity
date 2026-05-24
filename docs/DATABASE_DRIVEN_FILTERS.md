# Database-Driven Filters Implementation

**Date**: 2026-05-24  
**Commit**: bcbb21c7  
**Status**: ✅ DEPLOYED

---

## Summary

Converted ALL hardcoded filters on the journals page to be fully database-driven, making the system flexible and manageable through the admin panel.

---

## Changes Implemented

### 1. Subject Fields Filter (Browse by Subject)

**Before**: Hardcoded array of 8 subjects in the Blade template
**After**: Loads from `categories` table (same as homepage Browse by Subject section)

**Implementation**:
- ✅ Uses existing `categories` table
- ✅ Filters by `journal_id IS NULL` (site-level categories)
- ✅ Orders by `sort_order`
- ✅ Only shows active categories (`is_active = true`)

**Categories Available**:
1. Science & Technology
2. Medicine & Health
3. Social Sciences
4. Arts & Humanities
5. Business & Economics
6. Education

### 2. Accreditation Filter

**Before**: Hardcoded array of 4 accreditations in the Blade template
**After**: Loads from new `accreditations` table

**New Database Table**: `accreditations`

**Schema**:
```sql
CREATE TABLE accreditations (
    id UUID PRIMARY KEY,
    name VARCHAR(255),           -- e.g., "SINTA 1"
    slug VARCHAR(255) UNIQUE,    -- e.g., "sinta-1"
    level VARCHAR(255),          -- e.g., "S1", "S2", "SC", "DJ"
    color VARCHAR(255),          -- e.g., "amber", "slate", "blue", "purple"
    sort_order INTEGER DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

**Default Accreditations**:
1. SINTA 1 (S1, amber)
2. SINTA 2 (S2, slate)
3. Scopus Indexed (SC, blue)
4. DOAJ (DJ, purple)

---

## Files Created

### 1. Migration
**File**: `database/migrations/2026_05_24_210000_create_accreditations_table.php`
- Creates `accreditations` table
- Inserts 4 default accreditations
- Includes all necessary fields (name, slug, level, color, sort_order, is_active)

### 2. Model
**File**: `app/Models/Accreditation.php`
- UUID primary key
- Mass assignable fields
- Scopes: `active()`, `ordered()`
- Accessor: `getColorClassesAttribute()` for Tailwind classes

---

## Files Modified

### 1. Controller
**File**: `app/Http/Controllers/PortalController.php`

**Method**: `journals()`

**Changes**:
```php
// Load subject categories for filter
$subjectCategories = \App\Models\Category::whereNull('journal_id')
    ->where('is_active', true)
    ->orderBy('sort_order')
    ->get();

// Load accreditations for filter
$accreditations = \App\Models\Accreditation::active()
    ->ordered()
    ->get();

return view('site.journals', compact(
    'journals', 'search', 'alpha', 'sort', 'alphabet', 
    'totalJournals', 'settings', 'subjectCategories', 'accreditations'
));
```

### 2. Sidebar Component
**File**: `resources/views/components/site/journal-sidebar.blade.php`

**Changes**:
- Added `subjectCategories` and `accreditations` props
- Replaced hardcoded Subject Fields array with `@forelse($subjectCategories as $category)`
- Replaced hardcoded Accreditation array with `@forelse($accreditations as $accreditation)`
- Added empty states for both filters

**Before (Hardcoded)**:
```php
@foreach([
    'Engineering & Tech', 
    'Health & Medicine', 
    // ... hardcoded list
] as $subject)
```

**After (Database-Driven)**:
```php
@forelse($subjectCategories as $category)
    <label>
        <input type="checkbox" value="{{ $category->slug }}">
        <span>{{ $category->name }}</span>
    </label>
@empty
    <p>No subject fields available</p>
@endforelse
```

### 3. Journals View
**File**: `resources/views/site/journals.blade.php`

**Changes**:
- Passed `subjectCategories` and `accreditations` to sidebar component (both desktop and mobile)

---

## Benefits

### 1. Flexibility
- ✅ Admin can add/remove/edit categories and accreditations
- ✅ No code changes needed to update filters
- ✅ Can enable/disable filters without deployment

### 2. Consistency
- ✅ Subject Fields filter uses same data as homepage Browse by Subject
- ✅ Single source of truth for categories
- ✅ Consistent naming across the application

### 3. Maintainability
- ✅ No hardcoded data in templates
- ✅ Easy to add new accreditations (e.g., SINTA 3, Web of Science)
- ✅ Can reorder filters via `sort_order` field

### 4. Scalability
- ✅ Can add unlimited categories and accreditations
- ✅ Can add journal-specific categories later
- ✅ Can add filtering logic based on these fields

---

## Database Structure

### Categories Table (Existing)
```
id (UUID)
journal_id (UUID, nullable) -- NULL for site-level
name (VARCHAR)
path (VARCHAR)
slug (VARCHAR)
description (TEXT)
icon (VARCHAR)
color (VARCHAR)
sort_order (INTEGER)
is_active (BOOLEAN)
created_at (TIMESTAMP)
updated_at (TIMESTAMP)
```

### Accreditations Table (New)
```
id (UUID)
name (VARCHAR)
slug (VARCHAR, unique)
level (VARCHAR)
color (VARCHAR)
sort_order (INTEGER)
is_active (BOOLEAN)
created_at (TIMESTAMP)
updated_at (TIMESTAMP)
```

---

## Future Enhancements

### 1. Admin Panel Integration
- Create CRUD interface for managing accreditations
- Add category management for site-level categories
- Allow reordering via drag-and-drop

### 2. Journal-Accreditation Relationship
- Add `journal_accreditations` pivot table
- Link journals to their accreditations
- Filter journals by selected accreditations

### 3. Journal-Category Relationship
- Add `journal_categories` pivot table
- Link journals to subject categories
- Filter journals by selected subjects

### 4. Advanced Filtering
- Combine multiple filters (subject + accreditation)
- Add "Clear All Filters" button
- Show active filter count

---

## Deployment

- ✅ Committed: `feat: make Subject Fields and Accreditation filters database-driven`
- ✅ Pushed to GitHub: commit bcbb21c7
- ⏳ GitHub Actions: Deploying to https://ejournal.apdesyi.or.id/
- ⏳ Migration: Will run automatically on deployment

---

## Verification Steps

Once deployment completes:

1. Visit https://ejournal.apdesyi.or.id/journals
2. Check Subject Fields filter:
   - ✅ Should show 6 categories from database
   - ✅ Should match homepage Browse by Subject
3. Check Accreditation filter:
   - ✅ Should show 4 accreditations from database
   - ✅ Each should have correct icon and color
4. Verify no hardcoded data remains

---

## Technical Notes

- Both filters use `@forelse` to handle empty states gracefully
- Categories are cached on homepage (3600 seconds) but not on journals page
- Accreditations use scopes (`active()`, `ordered()`) for clean queries
- Color classes use Tailwind's dynamic class generation
- All data is loaded eagerly (no N+1 queries)
