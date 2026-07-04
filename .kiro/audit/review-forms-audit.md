# Review Forms - Deep Dive Audit & Implementation Plan

## 📋 Executive Summary

Berdasarkan screenshot dan analisis codebase, sistem Review Forms sudah memiliki fondasi dasar tetapi **belum memiliki fitur inti** untuk:
- Membuat form elements/questions dinamis
- Menyimpan reviewer responses
- Mengaitkan forms dengan review assignments

## 🔍 Current State Analysis

### ✅ Yang Sudah Ada

#### 1. Database Schema
```sql
review_forms table:
- id (uuid)
- journal_id (uuid)
- title (string)
- description (text, nullable)
- elements (json, nullable) -- BELUM DIGUNAKAN
- is_active (boolean, default true)
- response_count (integer, default 0)
- timestamps, soft_deletes
```

#### 2. Model Layer
**File:** `app/Models/ReviewForm.php`
- UUID primary key
- SoftDeletes trait
- Relationship dengan Journal
- Scope untuk active forms
- Helper method: `incrementResponseCount()`

**Kekurangan:**
- Tidak ada accessor/mutator untuk elements JSON
- Tidak ada validation logic untuk form structure
- Tidak ada method untuk building/rendering forms

#### 3. Controller Layer
**File:** `app/Http/Controllers/WorkflowSettingsController.php`

**Methods yang ada:**
- `storeReviewForm()` - Create form (hanya title & description)
- `updateReviewForm()` - Update form (hanya title & description)
- `destroyReviewForm()` - Delete form (dengan check response_count)

**Kekurangan:**
- Tidak ada method untuk manage form elements/questions
- Tidak ada method untuk preview/render form
- Tidak ada method untuk handle responses

#### 4. View Layer
**File:** `resources/views/admin/journals/workflow.blade.php`

**UI yang ada:**
- ✅ Table listing review forms (title, status, responses)
- ✅ Create form modal (hanya title & description)
- ✅ Delete button (hanya jika response_count = 0)
- ❌ **MISSING:** Form builder interface
- ❌ **MISSING:** Edit form modal
- ❌ **MISSING:** Preview form
- ❌ **MISSING:** Form elements management

#### 5. Routes
**File:** `routes/web.php`
```php
Route::post('/review-forms', [WorkflowSettingsController::class, 'storeReviewForm'])
Route::put('/review-forms/{reviewForm}', [WorkflowSettingsController::class, 'updateReviewForm'])
Route::delete('/review-forms/{reviewForm}', [WorkflowSettingsController::class, 'destroyReviewForm'])
```

**Kekurangan:**
- Tidak ada route untuk manage form elements
- Tidak ada route untuk preview
- Tidak ada route untuk responses

## 🎯 OJS Reference Implementation

Berdasarkan dokumentasi PKP dan best practices OJS 3.x:

### Review Forms Feature Specification

#### Form Builder Components:
1. **Form Elements Types:**
   - Text Box (single line)
   - Text Area (multiple lines)
   - Checkboxes
   - Radio Buttons
   - Drop-down menu
   - Rating scale (1-5 stars)

2. **Element Properties:**
   - Element title/question
   - Element type
   - Required/Optional flag
   - Help text/description
   - Response options (for select/radio/checkbox)
   - Order/sequence

3. **Form Assignment:**
   - Assign form to specific review rounds
   - Associate with review types (Double Blind, Blind, Open)
   - Make form mandatory or optional

4. **Response Collection:**
   - Store reviewer responses
   - Link responses to review assignments
   - Export responses for editor analysis

## 📸 Screenshot Analysis

Dari gambar yang diberikan, UI menampilkan:

```
Review Forms
┌─────────────────────────────────────────┐
│ FORM TITLE    STATUS    RESPONSES    ACTIONS │
│ Contoh Review │ Active  │    0      │   🗑️    │
│ Contoh Review │         │           │         │
└─────────────────────────────────────────┘
                    [+ Create Form]
```

**Observasi:**
- ✅ Basic listing sudah ada
- ✅ Status badge (Active/Inactive)
- ✅ Response count tracking
- ❌ **MISSING:** Edit button
- ❌ **MISSING:** Preview button
- ❌ **MISSING:** Duplicate button
- ❌ **MISSING:** Form elements management

## 🚀 Implementation Plan

### Phase 1: Database & Models (Prioritas Tinggi)

#### 1.1 Create Review Form Elements Table
```php
// Migration: create_review_form_elements_table.php
Schema::create('review_form_elements', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->uuid('review_form_id')->index();
    $table->string('element_type'); // text, textarea, checkbox, radio, select, rating
    $table->text('question');
    $table->text('description')->nullable();
    $table->json('options')->nullable(); // For select/radio/checkbox
    $table->boolean('required')->default(false);
    $table->integer('sequence')->default(0);
    $table->timestamps();
    $table->softDeletes();
    
    $table->foreign('review_form_id')
          ->references('id')
          ->on('review_forms')
          ->onDelete('cascade');
});
```

#### 1.2 Create Review Form Responses Table
```php
// Migration: create_review_form_responses_table.php
Schema::create('review_form_responses', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->uuid('review_assignment_id')->index();
    $table->uuid('review_form_element_id')->index();
    $table->text('response_value');
    $table->timestamps();
    
    $table->foreign('review_assignment_id')
          ->references('id')
          ->on('review_assignments')
          ->onDelete('cascade');
    
    $table->foreign('review_form_element_id')
          ->references('id')
          ->on('review_form_elements')
          ->onDelete('cascade');
});
```

#### 1.3 Update Review Assignments Table
```php
// Migration: add_review_form_id_to_review_assignments.php
Schema::table('review_assignments', function (Blueprint $table) {
    $table->uuid('review_form_id')->nullable()->after('reviewer_id');
    
    $table->foreign('review_form_id')
          ->references('id')
          ->on('review_forms')
          ->onDelete('set null');
});
```

#### 1.4 Create Models
- `app/Models/ReviewFormElement.php`
- `app/Models/ReviewFormResponse.php`
- Update `app/Models/ReviewForm.php` dengan relationships

### Phase 2: Form Builder UI (Prioritas Tinggi)

#### 2.1 Create Form Elements Management Controller
```php
// app/Http/Controllers/ReviewFormElementController.php
- index() - List elements for a form
- store() - Add new element
- update() - Update element
- destroy() - Delete element
- reorder() - Change sequence
```

#### 2.2 Form Builder Interface
**File:** `resources/views/admin/journals/review-forms/builder.blade.php`

**Features:**
- Drag & drop element ordering
- Add element button dengan type selector
- Element configuration panel
- Live preview
- Save/Cancel actions

#### 2.3 Element Type Components
- Text Input Component
- Textarea Component
- Checkbox Component
- Radio Component
- Select Component
- Rating Component

### Phase 3: Form Assignment & Usage (Prioritas Sedang)

#### 3.1 Assign Form to Review Assignment
**UI Location:** Editor assigns reviewer screen

**Logic:**
- Dropdown untuk memilih review form (optional)
- Ketika reviewer diberi assignment, form ikut ter-assign

#### 3.2 Reviewer Form Submission
**File:** `resources/views/reviewer/submit-review.blade.php`

**Features:**
- Render form elements berdasarkan review_form_id
- Validation sesuai required fields
- Save draft functionality
- Submit review dengan responses

#### 3.3 Editor View Responses
**UI Location:** Editorial workflow - Review stage

**Features:**
- View all reviewer responses
- Compare responses antar reviewers
- Export to CSV/Excel

### Phase 4: Advanced Features (Prioritas Rendah)

#### 4.1 Form Templates
- Pre-built form templates (General Review, Technical Review, etc.)
- Import/Export form definitions
- Duplicate existing forms

#### 4.2 Conditional Logic
- Show/hide elements based on previous answers
- Skip logic for complex forms

#### 4.3 Analytics
- Response statistics
- Common patterns in reviews
- Form effectiveness metrics

## 📊 Database Relationships Diagram

```
Journal (1) ──< (many) ReviewForm
                           │
                           ├──< (many) ReviewFormElement
                           │
                           └──< (many) ReviewAssignment
                                           │
                                           └──< (many) ReviewFormResponse
                                                           │
                                                           └──> (1) ReviewFormElement
```

## 🛠️ Technical Specifications

### Form Element Types

```php
// app/Enums/ReviewFormElementType.php
enum ReviewFormElementType: string
{
    case TEXT = 'text';
    case TEXTAREA = 'textarea';
    case CHECKBOX = 'checkbox';
    case RADIO = 'radio';
    case SELECT = 'select';
    case RATING = 'rating';
}
```

### Form Element Structure (JSON)

```json
{
  "id": "uuid",
  "type": "rating",
  "question": "How would you rate the originality of this research?",
  "description": "Consider novelty and contribution to the field",
  "required": true,
  "sequence": 1,
  "options": {
    "min": 1,
    "max": 5,
    "labels": {
      "1": "Poor",
      "5": "Excellent"
    }
  }
}
```

## 🎨 UI/UX Improvements Needed

### Current Issues:
1. ❌ No edit functionality for forms
2. ❌ No way to add questions/elements
3. ❌ No preview before using form
4. ❌ No indication of form completeness (has elements or not)

### Proposed Enhancements:
1. ✅ Add "Edit" button next to each form
2. ✅ Add "Preview" button to see form before using
3. ✅ Add "Builder" page for managing form elements
4. ✅ Show element count in listing (e.g., "5 questions")
5. ✅ Add "Duplicate" button to clone forms
6. ✅ Add status indicator if form has no elements
7. ✅ Add inline editing for title/description

### Updated Actions Column:
```
[Preview] [Edit] [Builder] [Duplicate] [Delete]
```

## 📝 File Structure Plan

```
app/
├── Models/
│   ├── ReviewForm.php (update)
│   ├── ReviewFormElement.php (new)
│   └── ReviewFormResponse.php (new)
├── Http/
│   ├── Controllers/
│   │   ├── WorkflowSettingsController.php (update)
│   │   ├── ReviewFormElementController.php (new)
│   │   └── ReviewFormResponseController.php (new)
│   ├── Requests/
│   │   ├── StoreReviewFormRequest.php (new)
│   │   ├── StoreReviewFormElementRequest.php (new)
│   │   └── UpdateReviewFormElementRequest.php (new)
│   └── Resources/
│       ├── ReviewFormResource.php (new)
│       ├── ReviewFormElementResource.php (new)
│       └── ReviewFormResponseResource.php (new)
├── Enums/
│   └── ReviewFormElementType.php (new)
└── Services/
    └── ReviewFormService.php (new) -- Business logic

resources/
└── views/
    └── admin/
        └── journals/
            ├── workflow.blade.php (update)
            └── review-forms/
                ├── builder.blade.php (new)
                ├── preview.blade.php (new)
                ├── edit.blade.php (new)
                └── components/
                    ├── text-element.blade.php (new)
                    ├── textarea-element.blade.php (new)
                    ├── checkbox-element.blade.php (new)
                    ├── radio-element.blade.php (new)
                    ├── select-element.blade.php (new)
                    └── rating-element.blade.php (new)

database/
└── migrations/
    ├── 2026_07_04_001_create_review_form_elements_table.php (new)
    ├── 2026_07_04_002_create_review_form_responses_table.php (new)
    └── 2026_07_04_003_add_review_form_id_to_review_assignments.php (new)
```

## 🔒 Security Considerations

1. **Authorization:**
   - Only journal managers can create/edit forms
   - Reviewers can only view assigned forms
   - Editors can view all responses

2. **Validation:**
   - Prevent deletion of forms with responses
   - Validate element types
   - Sanitize user input in responses

3. **Data Integrity:**
   - Soft deletes untuk audit trail
   - Foreign key constraints
   - Transaction wrapping untuk multi-step operations

## 📈 Testing Strategy

### Unit Tests:
- ReviewForm model methods
- ReviewFormElement validation
- ReviewFormResponse storage

### Feature Tests:
- Create review form with elements
- Assign form to review assignment
- Submit reviewer responses
- Delete form cascade behavior

### Browser Tests:
- Form builder drag & drop
- Element configuration
- Form preview
- Response submission

## 🚦 Implementation Priority

### Must Have (MVP):
1. ✅ ReviewFormElement model & migration
2. ✅ ReviewFormResponse model & migration  
3. ✅ Form builder UI
4. ✅ Element management (CRUD)
5. ✅ Basic element types (text, textarea, rating)
6. ✅ Assign form to review
7. ✅ Submit responses

### Should Have:
1. All element types (checkbox, radio, select)
2. Edit existing form
3. Preview form
4. View responses
5. Export responses

### Nice to Have:
1. Drag & drop reordering
2. Duplicate form
3. Form templates
4. Conditional logic
5. Analytics

## 📅 Estimated Timeline

- **Phase 1 (Database & Models):** 2-3 hari
- **Phase 2 (Form Builder UI):** 5-7 hari
- **Phase 3 (Form Assignment & Usage):** 4-5 hari
- **Phase 4 (Advanced Features):** 7-10 hari

**Total:** ~3-4 minggu untuk implementasi lengkap

## 🎯 Success Criteria

Review Forms dianggap selesai ketika:
1. ✅ Journal manager dapat membuat form dengan berbagai tipe pertanyaan
2. ✅ Editor dapat assign form ke review assignment
3. ✅ Reviewer dapat mengisi dan submit form
4. ✅ Editor dapat melihat semua responses
5. ✅ Form tidak dapat dihapus jika sudah ada responses
6. ✅ UI intuitif dan sesuai dengan design system yang ada

## 📚 References

- OJS Documentation: https://docs.pkp.sfu.ca/learning-ojs/
- Current codebase analysis
- Screenshot UI requirements
- PKP best practices

---

**Document Version:** 1.0  
**Date:** July 4, 2026  
**Author:** Kiro AI Assistant  
**Status:** Ready for Implementation
