# Task 2.1 Implementation: SitePageRequest Form Request Class

## Overview
Created a Laravel Form Request class for SitePage validation with comprehensive validation rules, custom error messages, authorization checks, and automatic slug generation.

## Files Created

### 1. Form Request Class
**Location:** `app/Http/Requests/Admin/SitePageRequest.php`

**Features Implemented:**
- ✅ Authorization check using `can('manage-site-pages')` permission
- ✅ Comprehensive validation rules for all SitePage fields
- ✅ Automatic slug generation from title in `prepareForValidation()`
- ✅ Unique slug validation (except for current record on update)
- ✅ Custom error messages with actionable text
- ✅ Custom attribute names for better error messages
- ✅ Support for both create and update operations

**Validation Rules:**
- `title`: required, string, min:3, max:255
- `slug`: required, string, max:255, regex pattern for URL-friendly format, unique
- `content`: nullable, string
- `meta_description`: nullable, string, max:160 (SEO optimized)
- `is_published`: required, boolean
- `sort_order`: nullable, integer, min:0

**Slug Validation Pattern:**
- Only lowercase letters, numbers, and hyphens
- No spaces, underscores, or special characters
- No leading or trailing hyphens
- No consecutive hyphens
- Pattern: `/^[a-z0-9]+(?:-[a-z0-9]+)*$/`

### 2. Unit Tests
**Location:** `tests/Unit/Requests/SitePageRequestTest.php`

**Test Coverage:**
- ✅ Authorization tests (with/without permission, guest users)
- ✅ Required field validation
- ✅ Title length validation (min 3, max 255)
- ✅ Slug format validation (valid and invalid formats)
- ✅ Slug uniqueness validation (create vs update)
- ✅ Meta description length validation (max 160)
- ✅ Boolean validation for is_published
- ✅ Integer validation for sort_order
- ✅ Nullable field validation
- ✅ Custom error messages verification
- ✅ Custom attributes verification
- ✅ Automatic slug generation from title
- ✅ Slug preservation when provided
- ✅ Complete valid data validation

**Total Tests:** 23 comprehensive test cases

## Integration with Existing Code

### Model Integration
The form request integrates seamlessly with the existing `SitePage` model:
- Uses the model's `setSlugAttribute()` mutator for unique slug generation
- Validates against the model's fillable fields
- Respects the model's audit trail fields (created_by, updated_by)

### Permission System
- Uses Spatie Laravel Permission package
- Requires `manage-site-pages` permission
- Permission is created in test setup (needs to be added to RoleSeeder for production)

## Usage Example

### In Controller (Create)
```php
public function store(SitePageRequest $request)
{
    $sitePage = SitePage::create($request->validated());
    
    return response()->json([
        'message' => 'Page created successfully',
        'data' => new SitePageResource($sitePage)
    ], 201);
}
```

### In Controller (Update)
```php
public function update(SitePageRequest $request, SitePage $sitePage)
{
    $sitePage->update($request->validated());
    
    return response()->json([
        'message' => 'Page updated successfully',
        'data' => new SitePageResource($sitePage)
    ]);
}
```

## Error Message Examples

### User-Friendly Messages
- **Title too short:** "Page title must be at least 3 characters"
- **Invalid slug:** "Page slug must contain only lowercase letters, numbers, and hyphens (e.g., about-us)"
- **Duplicate slug:** "This slug is already in use. Please choose a different slug or try adding a number (e.g., about-us-2)"
- **Meta description too long:** "Meta description cannot exceed 160 characters for optimal SEO"

## Next Steps

### Required for Production
1. Add `manage-site-pages` permission to `database/seeders/RoleSeeder.php`
2. Assign permission to appropriate roles (Super Admin, Admin, Journal Manager)
3. Run migrations if not already done (audit fields should exist from Task 1.1)

### Testing
To run the tests:
```bash
php artisan test --filter=SitePageRequestTest
```

Or run all unit tests:
```bash
php artisan test tests/Unit/Requests/SitePageRequestTest.php
```

## Requirements Satisfied

This implementation satisfies the following requirements from the spec:
- ✅ Requirement 1: Site Pages Inline Create Modal (validation support)
- ✅ Requirement 2: Site Pages Inline Edit Modal (validation support)
- ✅ Requirement 13: Client-Side Form Validation (server-side counterpart)
- ✅ Requirement 14: Server-Side Form Validation (complete implementation)
- ✅ Requirement 21: Slug Auto-Generation (prepareForValidation)

## Technical Notes

### Slug Auto-Generation
The `prepareForValidation()` method automatically generates a slug from the title if no slug is provided. This happens before validation, ensuring the slug field is always present for validation.

### Update vs Create
The validation rules automatically detect whether the request is for creating or updating a record by checking the HTTP method (PUT/PATCH for update) and excluding the current record from uniqueness checks.

### Route Parameter Handling
The code handles both snake_case (`site_page`) and camelCase (`sitePage`) route parameter names for flexibility.

## Code Quality

- ✅ Follows Laravel Form Request conventions
- ✅ Follows project coding standards (based on existing PublisherRequest)
- ✅ Comprehensive PHPDoc comments
- ✅ Type hints for all methods
- ✅ No syntax errors or diagnostics issues
- ✅ Comprehensive test coverage (23 test cases)
- ✅ Clear, actionable error messages
- ✅ SEO-friendly validation (meta description length)
