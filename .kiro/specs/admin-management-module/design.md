# Design Document

## Overview

The Admin Management Module is a comprehensive database-driven interface that enables Super Admins to manage site-wide content through three integrated management interfaces: System Settings, Subject Fields (Categories), and Accreditation Management. This module transforms the current hardcoded configuration approach into a dynamic, user-friendly admin panel with real-time preview capabilities, drag-and-drop reordering, and comprehensive audit logging.

### Key Design Goals

1. **Database-Driven Configuration**: Replace hardcoded values and manual seeder execution with persistent database storage
2. **Unified Admin Experience**: Provide a consistent interface pattern across all three management areas
3. **Real-Time Feedback**: Implement immediate cache invalidation and preview modes for instant visual feedback
4. **Security-First**: Enforce strict access control, input validation, and comprehensive audit logging
5. **Performance**: Maintain sub-second response times through efficient caching and database indexing
6. **Backward Compatibility**: Ensure seamless integration with existing data and public portal functionality

### Scope

**In Scope:**
- System Settings CRUD interface with grouped display
- Subject Fields (Categories) management with icon/color pickers and drag-and-drop reordering
- Accreditation management with badge preview and bulk operations
- Comprehensive audit logging for all administrative operations
- Cache management with automatic invalidation
- Input validation and XSS/SQL injection prevention
- Concurrent edit detection and conflict resolution
- Data export/import functionality
- Search and filtering capabilities
- Responsive UI for desktop and tablet devices

**Out of Scope:**
- Journal-specific settings management (handled by existing journal admin interface)
- User role management (handled by existing permission system)
- Public portal redesign (only preview rendering is included)
- Mobile app interface
- Multi-language admin interface (English only)
- Automated testing of UI interactions (manual testing required)


## Architecture

### System Architecture

The Admin Management Module follows Laravel's MVC architecture with additional service layer abstraction for business logic:

```
┌─────────────────────────────────────────────────────────────┐
│                     Presentation Layer                       │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐      │
│  │   System     │  │   Subject    │  │ Accreditation│      │
│  │   Settings   │  │   Fields     │  │  Management  │      │
│  │   Blade UI   │  │   Blade UI   │  │   Blade UI   │      │
│  └──────────────┘  └──────────────┘  └──────────────┘      │
└─────────────────────────────────────────────────────────────┘
                            │
                            ▼
┌─────────────────────────────────────────────────────────────┐
│                     Controller Layer                         │
│  ┌──────────────────────────────────────────────────────┐   │
│  │  SystemSettingController                             │   │
│  │  CategoryAdminController                             │   │
│  │  AccreditationAdminController                        │   │
│  └──────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────┘
                            │
                            ▼
┌─────────────────────────────────────────────────────────────┐
│                      Service Layer                           │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐      │
│  │   Settings   │  │   Category   │  │Accreditation │      │
│  │   Manager    │  │   Service    │  │   Service    │      │
│  │  (existing)  │  │    (new)     │  │    (new)     │      │
│  └──────────────┘  └──────────────┘  └──────────────┘      │
│                                                               │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐      │
│  │    Audit     │  │    Cache     │  │  Validation  │      │
│  │   Service    │  │   Manager    │  │   Service    │      │
│  └──────────────┘  └──────────────┘  └──────────────┘      │
└─────────────────────────────────────────────────────────────┘
                            │
                            ▼
┌─────────────────────────────────────────────────────────────┐
│                       Data Layer                             │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐      │
│  │SystemSetting │  │   Category   │  │Accreditation │      │
│  │    Model     │  │    Model     │  │    Model     │      │
│  └──────────────┘  └──────────────┘  └──────────────┘      │
│                                                               │
│  ┌──────────────────────────────────────────────────────┐   │
│  │              ActivityLog Model                       │   │
│  │         (Spatie Activity Log Package)                │   │
│  └──────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────┘
```


### Technology Stack

- **Backend Framework**: Laravel 11.x
- **Database**: MySQL 8.0+ (with UUID support)
- **Caching**: Laravel Cache (Redis/File-based)
- **Frontend**: Blade Templates with Alpine.js for interactivity
- **CSS Framework**: Tailwind CSS 3.x
- **Icons**: Font Awesome 6 Free
- **Drag-and-Drop**: Sortable.js
- **Audit Logging**: Spatie Laravel Activity Log
- **Validation**: Laravel Form Requests
- **Authentication**: Laravel Sanctum (existing)
- **Authorization**: Spatie Laravel Permission (existing)

### Design Patterns

1. **Service Layer Pattern**: Business logic encapsulated in service classes
2. **Repository Pattern**: Data access abstracted through Eloquent models
3. **Facade Pattern**: SettingsManager accessible via `Settings` facade
4. **Observer Pattern**: Model events trigger cache invalidation and audit logging
5. **Strategy Pattern**: Different validation strategies for each entity type
6. **Factory Pattern**: Form request validation factories for reusable validation rules


## Components and Interfaces

### 1. System Settings Management

#### SystemSettingController

**Responsibilities:**
- Handle HTTP requests for system settings CRUD operations
- Enforce Super Admin authorization
- Coordinate with SettingsManager service
- Return appropriate views and JSON responses

**Key Methods:**
```php
public function index(): View
public function update(UpdateSystemSettingsRequest $request): RedirectResponse
public function store(StoreSystemSettingRequest $request): JsonResponse
public function destroy(string $key): JsonResponse
public function export(): JsonResponse
public function import(ImportSettingsRequest $request): JsonResponse
```

#### SettingsManager Service (Enhanced)

**Existing Functionality:**
- `system(string $key, mixed $default)`: Get system setting value
- `setSystem(string $key, mixed $value, string $type)`: Set system setting
- `flushSystem()`: Clear system settings cache

**New Functionality to Add:**
```php
public function getAllGrouped(): Collection // Get all settings grouped by category
public function validateSettingValue(string $key, mixed $value): bool
public function getDefaultValue(string $key): mixed
public function bulkUpdate(array $settings): void
```


### 2. Subject Fields (Categories) Management

#### CategoryAdminController

**Responsibilities:**
- Handle HTTP requests for category CRUD operations
- Manage drag-and-drop reordering
- Coordinate bulk operations
- Provide preview rendering

**Key Methods:**
```php
public function index(): View
public function create(): View
public function store(StoreCategoryRequest $request): RedirectResponse
public function edit(string $id): View
public function update(UpdateCategoryRequest $request, string $id): RedirectResponse
public function destroy(string $id): JsonResponse
public function reorder(ReorderCategoriesRequest $request): JsonResponse
public function bulkActivate(BulkCategoryRequest $request): JsonResponse
public function bulkDeactivate(BulkCategoryRequest $request): JsonResponse
public function preview(string $id): View
public function search(Request $request): JsonResponse
```

#### CategoryService

**Responsibilities:**
- Business logic for category operations
- Slug generation and validation
- Sort order management
- Cache invalidation coordination

**Key Methods:**
```php
public function create(array $data): Category
public function update(Category $category, array $data): Category
public function delete(Category $category): bool
public function reorder(array $orderMap): void
public function bulkUpdateStatus(array $ids, bool $isActive): int
public function generateSlug(string $name): string
public function checkJournalUsage(Category $category): int
public function getActiveCategories(): Collection
```


### 3. Accreditation Management

#### AccreditationAdminController

**Responsibilities:**
- Handle HTTP requests for accreditation CRUD operations
- Manage drag-and-drop reordering
- Coordinate bulk operations
- Provide badge preview rendering

**Key Methods:**
```php
public function index(): View
public function create(): View
public function store(StoreAccreditationRequest $request): RedirectResponse
public function edit(string $id): View
public function update(UpdateAccreditationRequest $request, string $id): RedirectResponse
public function destroy(string $id): JsonResponse
public function reorder(ReorderAccreditationsRequest $request): JsonResponse
public function bulkActivate(BulkAccreditationRequest $request): JsonResponse
public function bulkDeactivate(BulkAccreditationRequest $request): JsonResponse
public function previewBadge(string $id): View
public function search(Request $request): JsonResponse
```

#### AccreditationService

**Responsibilities:**
- Business logic for accreditation operations
- Slug generation and validation
- Sort order management
- Cache invalidation coordination

**Key Methods:**
```php
public function create(array $data): Accreditation
public function update(Accreditation $accreditation, array $data): Accreditation
public function delete(Accreditation $accreditation): bool
public function reorder(array $orderMap): void
public function bulkUpdateStatus(array $ids, bool $isActive): int
public function generateSlug(string $name): string
public function checkJournalUsage(Accreditation $accreditation): int
public function getActiveAccreditations(): Collection
```


### 4. Shared Services

#### AuditService

**Responsibilities:**
- Centralized audit logging for all admin operations
- Integration with Spatie Activity Log
- Structured logging format

**Key Methods:**
```php
public function logCreate(string $entityType, Model $entity, User $user): void
public function logUpdate(string $entityType, Model $entity, array $oldValues, array $newValues, User $user): void
public function logDelete(string $entityType, Model $entity, User $user): void
public function logBulkOperation(string $entityType, string $action, array $entityIds, User $user): void
public function logUnauthorizedAccess(string $resource, User $user): void
```

#### CacheManager

**Responsibilities:**
- Centralized cache invalidation logic
- Cache key management
- Selective cache clearing

**Key Methods:**
```php
public function clearSystemSettings(): void
public function clearCategories(): void
public function clearAccreditations(): void
public function clearPublicPortal(): void
public function clearAll(): void
public function getCacheKeys(string $type): array
```

#### ValidationService

**Responsibilities:**
- Reusable validation logic
- XSS prevention
- Format validation

**Key Methods:**
```php
public function sanitizeInput(string $input): string
public function validateUrl(string $url): bool
public function validateFontAwesomeIcon(string $icon): bool
public function validateColor(string $color, array $allowedColors): bool
public function validateSlug(string $slug): bool
```


## Data Models

### SystemSetting Model (Enhanced)

**Table:** `system_settings`

**Schema:**
```php
id: bigint (primary key)
key: varchar(255) unique not null
value: longtext nullable
type: varchar(20) default 'string' // string, boolean, integer, json
group: varchar(100) default 'general'
description: text nullable
created_at: timestamp
updated_at: timestamp
```

**Indexes:**
- Primary: `id`
- Unique: `key`
- Index: `group` (for grouped queries)

**Relationships:**
- None (standalone configuration table)

**Traits:**
- `LogsActivity` (Spatie Activity Log)

**Key Attributes:**
- `typed_value`: Accessor that casts value based on type field


### Category Model (Enhanced)

**Table:** `categories`

**Schema:**
```php
id: uuid (primary key)
journal_id: uuid nullable (null = site-level category)
name: varchar(255) not null
slug: varchar(255) not null
path: varchar(255) not null (legacy field, kept for backward compatibility)
description: text nullable
icon: varchar(100) nullable // Font Awesome icon class (e.g., "fa-flask")
color: varchar(50) nullable // Tailwind color name (e.g., "blue", "red")
sort_order: integer default 0
is_active: boolean default true
created_at: timestamp
updated_at: timestamp
deleted_at: timestamp nullable (soft deletes)
```

**Indexes:**
- Primary: `id`
- Index: `journal_id`
- Index: `slug`
- Index: `sort_order`
- Index: `is_active`
- Unique: `(journal_id, slug)`

**Relationships:**
- `belongsTo(Journal)` - optional, null for site-level categories

**Traits:**
- `HasUuids`
- `SoftDeletes`
- `LogsActivity`

**Scopes:**
- `active()`: Filter by is_active = true
- `ordered()`: Order by sort_order ASC
- `siteLevel()`: Filter by journal_id IS NULL

**Validation Rules:**
- name: required, max:255
- icon: nullable, regex:/^fa-[a-z0-9-]+$/
- color: nullable, in:blue,red,green,purple,amber,indigo,slate,orange,emerald,teal,cyan,gray
- sort_order: integer, min:0


### Accreditation Model (Enhanced)

**Table:** `accreditations`

**Schema:**
```php
id: uuid (primary key)
name: varchar(255) not null
slug: varchar(255) unique not null
level: varchar(10) not null // 2-character code (e.g., "S1", "S2", "SC", "DJ")
color: varchar(50) not null // Badge color (amber, slate, blue, purple, green, red)
sort_order: integer default 0
is_active: boolean default true
created_at: timestamp
updated_at: timestamp
```

**Indexes:**
- Primary: `id`
- Unique: `slug`
- Index: `sort_order`
- Index: `is_active`
- Index: `level`

**Relationships:**
- None (referenced by journals but no direct foreign key)

**Traits:**
- `HasUuids`
- `LogsActivity`

**Scopes:**
- `active()`: Filter by is_active = true
- `ordered()`: Order by sort_order ASC

**Accessors:**
- `color_classes`: Returns Tailwind CSS classes for badge styling

**Validation Rules:**
- name: required, max:255
- level: required, size:2, regex:/^[A-Z0-9]{2}$/
- color: required, in:amber,slate,blue,purple,green,red
- sort_order: integer, min:0


## User Interface Design

### Common UI Patterns

All three management interfaces share consistent UI patterns:

1. **List View Layout:**
   - Search bar at top
   - Filter buttons (All / Active / Inactive)
   - Action buttons (Create New, Bulk Actions, Export)
   - Data table with sortable columns
   - Drag handles for reordering
   - Action column with Edit/Delete/Preview buttons
   - Pagination controls at bottom

2. **Form Layout:**
   - Two-column responsive layout
   - Left column: Primary fields (name, description)
   - Right column: Meta fields (status, order, colors)
   - Preview panel at bottom
   - Save/Cancel buttons with confirmation

3. **Color Scheme:**
   - Primary: Indigo (admin actions)
   - Success: Green (confirmations)
   - Warning: Amber (cautions)
   - Danger: Red (deletions)
   - Neutral: Gray (secondary actions)

4. **Interactive Elements:**
   - Toast notifications (3-second auto-dismiss)
   - Modal confirmations for destructive actions
   - Inline validation with error messages
   - Loading spinners for async operations
   - Drag-and-drop visual feedback

