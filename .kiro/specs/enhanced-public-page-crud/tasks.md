# Implementation Plan: Enhanced Public Page CRUD

## Overview

This implementation plan transforms the unified public page management interface at `/admin/public-page` into a fully interactive, modal-based CRUD system with real-time updates, drag-and-drop reordering, rich text editing, and comprehensive validation. The implementation follows 8 phases covering database enhancements, API development, frontend components, and comprehensive testing.

## Tasks

- [x] 1. Database Schema Enhancements and Model Updates
  - [x] 1.1 Create migration to add audit fields to site_pages table
    - Add `created_by` UUID column referencing users(id)
    - Add `updated_by` UUID column referencing users(id)
    - Add `meta_description` VARCHAR(160) column
    - Add indexes for `is_published` and `sort_order`
    - _Requirements: 25 (Audit Trail), 21 (Slug Auto-Generation)_
  
  - [x] 1.2 Create migration to enhance site_content_blocks table
    - Add `content` TEXT column for rich text content
    - Add `created_by` UUID column referencing users(id)
    - Add `updated_by` UUID column referencing users(id)
    - Add indexes for `is_active`, `sort_order`, and `category`
    - _Requirements: 5 (Page Builder Block Create), 25 (Audit Trail)_
  
  - [x] 1.3 Update SitePage Eloquent model
    - Add fillable fields: `meta_description`, `created_by`, `updated_by`
    - Add relationships: `creator()` and `updater()` BelongsTo User
    - Add scopes: `published()` and `ordered()`
    - Implement `generateUniqueSlug()` method with auto-increment suffix
    - Add boot method to auto-set `created_by` and `updated_by`
    - _Requirements: 21 (Slug Auto-Generation), 25 (Audit Trail)_
  
  - [x] 1.4 Update SiteContentBlock Eloquent model
    - Add fillable field: `content`, `created_by`, `updated_by`
    - Add relationships: `creator()` and `updater()` BelongsTo User
    - Add scopes: `active()` and `ordered()`
    - Add boot method to auto-set `created_by` and `updated_by`
    - _Requirements: 5 (Page Builder Block Create), 25 (Audit Trail)_

- [ ] 2. Backend API Controllers and Validation
  - [x] 2.1 Create SitePageRequest form request class
    - Implement validation rules: title (required, 3-255 chars), slug (required, unique, regex), content (nullable), meta_description (max 160), is_published (boolean)
    - Implement custom error messages with actionable text
    - Add `prepareForValidation()` to auto-generate slug from title
    - Implement authorization check using `can('manage-site-pages')`
    - _Requirements: 1 (Site Pages Create), 13 (Client Validation), 14 (Server Validation), 21 (Slug Auto-Generation)_
  
  - [x] 2.2 Create ContentBlockRequest form request class
    - Implement validation rules: key (required, unique, regex), title (required), content (nullable), config (array), is_active (boolean), category (enum)
    - Implement authorization check using `can('manage-content-blocks')`
    - Add custom error messages
    - _Requirements: 5 (Page Builder Block Create), 14 (Server Validation)_
  
  - [x] 2.3 Create NavigationMenuRequest form request class
    - Implement validation rules: title (required), area_name (required), is_active (boolean)
    - Implement authorization check using `can('manage-navigation')`
    - _Requirements: 9 (Navigation Menu Create), 14 (Server Validation)_
  
  - [x] 2.4 Create NavigationMenuItemRequest form request class
    - Implement validation rules: title (required), type (enum), url (nullable, url format), icon (nullable), target (enum), parent_id (nullable, exists)
    - Validate parent_id doesn't create circular references
    - _Requirements: 10 (Navigation Menu Item Create), 14 (Server Validation)_
  
  - [x] 2.5 Create SitePageResource API resource class
    - Transform model to JSON with id, title, slug, content, meta_description, is_published, sort_order
    - Include creator and updater user data (id, name)
    - Add human-readable timestamps using `diffForHumans()`
    - _Requirements: 25 (Audit Trail)_
  
  - [x] 2.6 Create ContentBlockResource API resource class
    - Transform model to JSON with all fields plus creator/updater data
    - _Requirements: 5 (Page Builder Block Create), 25 (Audit Trail)_
  
  - [x] 2.7 Create NavigationMenuResource and NavigationMenuItemResource classes
    - Transform models with nested relationships
    - Include hierarchical structure for menu items
    - _Requirements: 9 (Navigation Menu Create), 10 (Navigation Menu Item Create)_
  
  - [x] 2.8 Create SitePageController with CRUD endpoints
    - Implement `index()` with search, filter by status, pagination (25 per page), eager loading
    - Implement `store()` to create new page with validation
    - Implement `show()` to retrieve single page with relationships
    - Implement `update()` to update existing page
    - Implement `destroy()` to delete page
    - Implement `duplicate()` to copy page with "(Copy)" suffix and draft status
    - Implement `reorder()` to update sort_order for multiple pages
    - Implement `bulkDelete()` to delete multiple pages by IDs
    - Return consistent JSON responses with data and message keys
    - _Requirements: 1 (Create), 2 (Edit), 3 (Delete), 23 (Search/Filter), 24 (Pagination), 27 (Duplication), 22 (Bulk Delete)_
  
  - [x] 2.9 Create ContentBlockController with CRUD endpoints
    - Implement `index()` with pagination and filtering
    - Implement `store()`, `show()`, `update()`, `destroy()` methods
    - Implement `reorder()` for drag-drop support
    - _Requirements: 5 (Block Create), 6 (Block Edit), 7 (Drag-Drop Reordering)_
  
  - [x] 2.10 Create NavigationMenuController with CRUD endpoints
    - Implement `index()` to list all menus with items
    - Implement `store()`, `show()`, `update()`, `destroy()` methods
    - Implement `reorder()` for menu item reordering with parent relationship updates
    - _Requirements: 9 (Menu Create), 12 (Menu Drag-Drop Reordering)_
  
  - [ ] 2.11 Create NavigationMenuItemController
    - Implement `store()` to create menu item under specific menu
    - Implement `update()` to modify menu item including parent changes
    - Implement `destroy()` to delete menu item
    - _Requirements: 10 (Menu Item Create), 11 (Menu Item Edit)_
  
  - [ ] 2.12 Create ImageUploadController for rich text editor
    - Implement `store()` method to handle image uploads
    - Validate file type (jpeg, png, gif, webp), size (max 5MB), dimensions (max 4000x4000)
    - Store images in `public/images` directory
    - Return JSON with url, filename, size
    - _Requirements: 28 (Image Upload in Rich Text Editor)_
  
  - [ ] 2.13 Define API routes in routes/web.php
    - Create route group with `auth` and `admin` middleware at `/admin/api`
    - Register apiResource routes for site-pages, content-blocks, navigation-menus
    - Add custom routes: duplicate, reorder, bulk-delete, upload-image
    - Add navigation menu item routes under menu context
    - _Requirements: All CRUD requirements_

- [ ] 3. Frontend Core Components - Modal System
  - [ ] 3.1 Create modal Blade component (resources/views/components/modal.blade.php)
    - Implement Alpine.js reactive modal with show/hide state
    - Add backdrop with click-to-close functionality
    - Implement smooth enter/leave transitions (300ms ease-out)
    - Add focus trap using x-trap directive
    - Support Escape key to close with event listener
    - Make responsive: full-screen on mobile (<768px), centered on desktop
    - Add ARIA attributes: role="dialog", aria-modal="true", aria-labelledby
    - _Requirements: 1 (Create Modal), 2 (Edit Modal), 19 (Accessibility), 20 (Mobile Responsive)_
  
  - [ ] 3.2 Create modalManager Alpine.js component
    - Implement state management: modals object, activeModal, unsavedChanges flag
    - Implement `open(modalId, data)` method to show modal and populate data
    - Implement `close(modalId, force)` method with unsaved changes check
    - Implement `confirmClose(modalId)` to show confirmation dialog if unsaved changes
    - Implement `trackChanges()` to set unsavedChanges flag on form input
    - Implement `resetChanges()` to clear flag after successful save
    - _Requirements: 18 (Unsaved Changes Warning)_
  
  - [ ] 3.3 Create confirmation dialog Blade component
    - Implement modal dialog with title, message, confirm/cancel buttons
    - Add danger styling for destructive actions (red color scheme)
    - Support custom confirm button text and callback
    - Add loading state on confirm button during async operations
    - _Requirements: 3 (Delete Confirmation), 18 (Unsaved Changes Warning)_

- [ ] 4. Frontend Core Components - Forms and Validation
  - [ ] 4.1 Create formValidator Alpine.js component
    - Implement reactive state: fields object, errors object, touched object, isValid boolean
    - Implement `validate(fieldName)` method with rule checking (required, minLength, maxLength, pattern, in, url)
    - Implement `validateAll()` method to validate entire form
    - Implement `touch(fieldName)` to mark field as touched
    - Update `isValid` flag based on validation results
    - _Requirements: 13 (Client-Side Validation)_
  
  - [ ] 4.2 Create form input Blade component (resources/views/components/form/input.blade.php)
    - Support props: name, label, type, value, required, error, help
    - Integrate with formValidator using x-model and validation events
    - Display validation errors below field with red text and alert role
    - Show required asterisk for required fields
    - Add ARIA attributes: aria-describedby, aria-invalid
    - Style with Tailwind: focus ring, border colors for valid/invalid states
    - _Requirements: 13 (Client Validation), 19 (Accessibility)_
  
  - [ ] 4.3 Create form textarea Blade component
    - Similar to input component but for multi-line text
    - Support rows prop for height control
    - _Requirements: 13 (Client Validation)_
  
  - [ ] 4.4 Create form select Blade component
    - Support options array prop
    - Integrate with formValidator
    - Style with Tailwind custom select styling
    - _Requirements: 13 (Client Validation)_
  
  - [ ] 4.5 Create slug auto-generation JavaScript utility
    - Implement `generateSlug(title)` function: lowercase, replace spaces with hyphens, remove special chars
    - Add real-time slug generation on title input
    - Stop auto-generation if user manually edits slug field
    - _Requirements: 21 (Slug Auto-Generation)_

- [ ] 5. Frontend Core Components - Rich Text Editor and Utilities
  - [ ] 5.1 Install TinyMCE and dependencies
    - Run `npm install tinymce @tinymce/tinymce-vue`
    - Obtain TinyMCE API key and add to .env as TINYMCE_API_KEY
    - _Requirements: 4 (Rich Text Editor Integration)_
  
  - [ ] 5.2 Create richTextEditor Alpine.js component
    - Implement TinyMCE initialization with config: height, plugins, toolbar
    - Configure plugins: advlist, autolink, lists, link, image, code, table, wordcount
    - Configure toolbar: undo/redo, blocks, bold/italic, alignment, lists, link/image, code
    - Implement `handleImageUpload()` method to POST to /admin/api/upload-image
    - Implement `destroy()` method to properly cleanup TinyMCE instance
    - Dispatch 'content-changed' event on editor change
    - _Requirements: 4 (Rich Text Editor Integration), 28 (Image Upload)_
  
  - [ ] 5.3 Create rich-text Blade component (resources/views/components/form/rich-text.blade.php)
    - Integrate richTextEditor Alpine component
    - Support props: name, label, value, required, height
    - Include hidden textarea for form submission
    - Load TinyMCE script from CDN in @push('scripts')
    - _Requirements: 4 (Rich Text Editor Integration)_
  
  - [ ] 5.4 Install SortableJS for drag-and-drop
    - Run `npm install sortablejs`
    - _Requirements: 7 (Page Builder Drag-Drop), 12 (Navigation Drag-Drop)_
  
  - [ ] 5.5 Create dragDropManager Alpine.js component
    - Initialize SortableJS with config: animation 150ms, handle '.drag-handle', ghost/drag classes
    - Implement `handleReorder(evt)` method to update item positions
    - Perform optimistic UI update (move item in array immediately)
    - POST reorder data to backend API
    - Revert changes on error and show error toast
    - Dispatch success toast on successful reorder
    - _Requirements: 7 (Page Builder Drag-Drop), 12 (Navigation Drag-Drop)_
  
  - [ ] 5.6 Create sortable-list Blade component
    - Integrate dragDropManager Alpine component
    - Render items with drag handle icon (6-dots vertical icon)
    - Support touch gestures for mobile drag-drop
    - Add keyboard alternative buttons (up/down arrows) for accessibility
    - _Requirements: 7 (Drag-Drop), 19 (Accessibility), 20 (Mobile Responsive)_
  
  - [ ] 5.7 Create toastManager Alpine.js component
    - Implement state: toasts array, nextId counter
    - Implement `show({ type, message, duration })` method to add toast
    - Auto-dismiss success toasts after 5 seconds
    - Keep error toasts visible until manually dismissed
    - Implement `remove(id)` method to dismiss toast
    - Implement `announceToScreenReader()` to create ARIA live region announcement
    - Listen for global 'toast' events on window
    - _Requirements: 16 (Toast Notification System), 19 (Accessibility)_
  
  - [ ] 5.8 Create toast-container Blade component
    - Position fixed at top-right corner with z-50
    - Render toasts with color-coded backgrounds (green=success, red=error, blue=info, yellow=warning)
    - Add icons for each toast type (checkmark, X, info, warning)
    - Implement smooth enter/leave transitions
    - Add close button on each toast
    - Add ARIA live region attributes
    - _Requirements: 16 (Toast Notification System), 19 (Accessibility)_

- [ ] 6. AJAX Service Layer and Error Handling
  - [ ] 6.1 Create AjaxService JavaScript class (resources/js/services/ajax-service.js)
    - Initialize Axios instance with X-Requested-With and X-CSRF-TOKEN headers
    - Implement response interceptor for centralized error handling
    - Handle 422 validation errors: return structured error object
    - Handle 403 forbidden: show "insufficient permissions" toast
    - Handle 404 not found: show "resource not found" toast
    - Handle 500 server error: show "server error, contact support" toast
    - Handle network errors: show "network error, check connection" toast with retry button
    - Implement methods: `get()`, `post()`, `put()`, `delete()`
    - Export as global `window.ajaxService`
    - _Requirements: 15 (AJAX Operations), 17 (Error Handling)_
  
  - [ ] 6.2 Create loading state utility
    - Implement button loading state: disable button, show spinner, change text to "Saving..."
    - Create reusable `setLoading(element, isLoading, loadingText)` function
    - _Requirements: 15 (AJAX Operations with Loading States)_
  
  - [ ] 6.3 Implement retry mechanism for failed operations
    - Add retry button to error toasts
    - Store failed request details (url, method, data)
    - Implement `retry()` function to re-attempt failed request
    - _Requirements: 17 (Error Handling and Recovery)_

- [ ] 7. Site Pages CRUD Implementation
  - [ ] 7.1 Create site-page-modal Blade partial
    - Create modal with form fields: title (input), slug (input with auto-generation), content (rich-text), meta_description (textarea, max 160 chars), is_published (checkbox/toggle)
    - Integrate formValidator with validation rules
    - Add "Save as Draft" and "Publish" buttons
    - Show loading state on submit button during save
    - Display validation errors inline below each field
    - _Requirements: 1 (Create Modal), 2 (Edit Modal), 26 (Draft/Publish Workflow)_
  
  - [ ] 7.2 Implement create page functionality
    - Add "Create Page" button in Site Pages tab
    - On click, dispatch 'open-modal' event with empty data
    - On form submit, validate client-side then POST to /admin/api/site-pages
    - On success: close modal, refresh page list, show success toast
    - On validation error: display errors inline, keep modal open
    - _Requirements: 1 (Site Pages Inline Create Modal)_
  
  - [ ] 7.3 Implement edit page functionality
    - Add "Edit" button on each page row
    - On click, fetch page data via GET /admin/api/site-pages/{id}
    - Populate modal form with existing data
    - Load content into TinyMCE editor
    - On form submit, PUT to /admin/api/site-pages/{id}
    - On success: close modal, update row in list, show success toast
    - _Requirements: 2 (Site Pages Inline Edit Modal)_
  
  - [ ] 7.4 Implement delete page functionality
    - Add "Delete" button on each page row
    - On click, show confirmation dialog with page title and warning message
    - On confirm, DELETE to /admin/api/site-pages/{id}
    - Show loading state on confirm button
    - On success: remove row from list, show success toast
    - On cancel: close dialog without action
    - _Requirements: 3 (Site Pages Inline Delete with Confirmation)_
  
  - [ ] 7.5 Implement duplicate page functionality
    - Add "Duplicate" button on each page row
    - On click, POST to /admin/api/site-pages/{id}/duplicate
    - On success: add duplicated page to list with "(Copy)" suffix, show success toast
    - Optionally open edit modal for immediate editing
    - _Requirements: 27 (Content Duplication)_
  
  - [ ] 7.6 Implement drag-and-drop page reordering
    - Integrate sortable-list component for page rows
    - Show drag handle on hover
    - On drop, POST new order to /admin/api/site-pages/reorder
    - Show loading indicator during reorder
    - On success: show success toast
    - On error: revert to original order, show error toast
    - _Requirements: 7 (Drag-and-Drop Reordering - applies to pages too)_
  
  - [ ] 7.7 Implement bulk delete functionality
    - Add checkboxes to each page row
    - Add "Select All" checkbox in table header
    - Show "Delete Selected" button when items are selected
    - On click, show confirmation dialog with count of selected items
    - On confirm, POST to /admin/api/site-pages/bulk-delete with IDs array
    - On success: remove deleted rows, show toast with count
    - On partial failure: show toast listing which items failed
    - _Requirements: 22 (Bulk Delete Operations)_
  
  - [ ] 7.8 Implement search and filter functionality
    - Add search input field with debounce (300ms)
    - Add status filter dropdown (All, Published, Draft)
    - On search/filter change, fetch filtered results via GET /admin/api/site-pages with query params
    - Update page list without page reload
    - Show "No results" message when list is empty
    - _Requirements: 23 (Search and Filter Functionality)_
  
  - [ ] 7.9 Implement pagination
    - Display pagination controls when total pages > 25
    - Show page numbers, previous/next buttons, total count
    - Add "Items per page" selector (25, 50, 100)
    - On page change, fetch new page via AJAX maintaining search/filter state
    - Show loading indicator during page load
    - _Requirements: 24 (Pagination for Large Lists)_

- [ ] 8. Content Blocks CRUD Implementation
  - [ ] 8.1 Create content-block-modal Blade partial
    - Create modal with form fields: key (input), title (input), description (textarea), content (rich-text), category (select), is_active (toggle), icon (input)
    - Integrate formValidator with validation rules
    - Add save button with loading state
    - _Requirements: 5 (Page Builder Inline Block Create), 6 (Page Builder Inline Block Edit)_
  
  - [ ] 8.2 Implement create block functionality
    - Add "Add Block" button in Page Builder tab
    - On click, open modal with empty form
    - On submit, POST to /admin/api/content-blocks
    - On success: add block to list, show success toast
    - _Requirements: 5 (Page Builder Inline Block Create)_
  
  - [ ] 8.3 Implement edit block functionality
    - Add "Edit" button on each block card
    - On click, fetch block data and populate modal
    - On submit, PUT to /admin/api/content-blocks/{id}
    - On success: update block display, show success toast
    - _Requirements: 6 (Page Builder Inline Block Edit)_
  
  - [ ] 8.4 Implement delete block functionality
    - Add "Delete" button on each block card
    - Show confirmation dialog
    - On confirm, DELETE to /admin/api/content-blocks/{id}
    - On success: remove block from list, show success toast
    - _Requirements: Similar to Site Pages Delete_
  
  - [ ] 8.5 Implement drag-and-drop block reordering
    - Integrate sortable-list component for blocks
    - Show drag handle on each block card
    - On drop, POST to /admin/api/content-blocks/reorder
    - Update block positions with smooth animation
    - _Requirements: 7 (Page Builder Drag-and-Drop Reordering)_
  
  - [ ] 8.6 Implement live preview panel
    - Add preview toggle button in block modal
    - Create preview panel that renders block content with public site styling
    - Update preview in real-time as user edits content
    - Support switching between edit and preview modes
    - _Requirements: 8 (Page Builder Live Preview)_

- [ ] 9. Navigation CRUD Implementation
  - [ ] 9.1 Create navigation-menu-modal Blade partial
    - Create modal with form fields: title (input), area_name (select), is_active (toggle)
    - Integrate formValidator
    - _Requirements: 9 (Site Navigation Inline Menu Create)_
  
  - [ ] 9.2 Create menu-item-modal Blade partial
    - Create modal with form fields: title (input), type (select: custom/route/page), url (input), route_name (input), icon (icon-picker), target (select: _self/_blank), parent_id (select with nested options)
    - Show/hide fields based on type selection
    - Integrate formValidator with conditional validation
    - _Requirements: 10 (Site Navigation Inline Menu Item Create), 29 (Menu Item Icon Selection)_
  
  - [ ] 9.3 Implement create menu functionality
    - Add "Create Menu" button in Site Navigation tab
    - On click, open menu modal
    - On submit, POST to /admin/api/navigation-menus
    - On success: add menu to list, expand to show empty items area, show success toast
    - _Requirements: 9 (Site Navigation Inline Menu Create)_
  
  - [ ] 9.4 Implement create menu item functionality
    - Add "Add Item" button within each menu
    - On click, open menu item modal
    - Support parent item selection for nested menus
    - On submit, POST to /admin/api/navigation-menus/{menuId}/items
    - On success: add item to menu tree with proper indentation, show success toast
    - _Requirements: 10 (Site Navigation Inline Menu Item Create)_
  
  - [ ] 9.5 Implement edit menu item functionality
    - Add "Edit" button on each menu item
    - On click, fetch item data and populate modal
    - Allow changing parent item to restructure hierarchy
    - On submit, PUT to /admin/api/navigation-menu-items/{id}
    - On success: update menu tree display, move item if parent changed, show success toast
    - _Requirements: 11 (Site Navigation Inline Menu Item Edit)_
  
  - [ ] 9.6 Implement delete menu and menu item functionality
    - Add "Delete" buttons for menus and menu items
    - Show confirmation dialogs
    - On confirm, DELETE to appropriate endpoint
    - On success: remove from tree, show success toast
    - _Requirements: Similar to other delete operations_
  
  - [ ] 9.7 Implement drag-and-drop menu item reordering
    - Integrate sortable-list with nested support
    - Show drag handle on each menu item
    - Support dragging item onto another item to create nested relationship
    - Show valid drop zones during drag
    - On drop, POST to /admin/api/navigation-menus/{menuId}/reorder with items array including parent_id
    - On success: update tree structure, show success toast
    - On error: revert to original structure, show error toast
    - _Requirements: 12 (Site Navigation Drag-and-Drop Menu Reordering)_
  
  - [ ] 9.8 Create icon picker component
    - Display searchable grid of icons (Heroicons or Font Awesome)
    - Implement search filter to find icons by name
    - Show preview of selected icon
    - Allow clearing selection for text-only menu items
    - _Requirements: 29 (Menu Item Icon Selection)_

- [ ] 10. Accessibility and Keyboard Navigation
  - [ ] 10.1 Implement keyboard navigation for modals
    - Ensure Tab key cycles through focusable elements within modal
    - Implement focus trap to prevent tabbing outside modal
    - Move focus to first form field when modal opens
    - Restore focus to trigger button when modal closes
    - Support Escape key to close modal (with unsaved changes warning)
    - _Requirements: 19 (Accessibility Compliance)_
  
  - [ ] 10.2 Add ARIA attributes throughout interface
    - Add aria-label to all icon-only buttons
    - Add aria-describedby to form fields linking to error/help text
    - Add aria-invalid to fields with validation errors
    - Add role="alert" to error messages
    - Add aria-live="polite" to toast container
    - Add role="dialog", aria-modal="true", aria-labelledby to modals
    - _Requirements: 19 (Accessibility Compliance)_
  
  - [ ] 10.3 Implement keyboard shortcuts
    - Add Ctrl+N (Cmd+N on Mac) to open create page modal in Site Pages tab
    - Add Ctrl+S (Cmd+S on Mac) to submit form in modals
    - Add Escape to close modals and dialogs
    - Add Ctrl+/ (Cmd+/ on Mac) to show keyboard shortcuts help dialog
    - Prevent default browser behavior for captured shortcuts
    - _Requirements: 30 (Keyboard Shortcuts)_
  
  - [ ] 10.4 Add keyboard alternatives for drag-and-drop
    - Add up/down arrow buttons next to drag handles
    - Implement move up/down functionality via button clicks
    - Ensure buttons are keyboard accessible
    - _Requirements: 19 (Accessibility - Drag-Drop Alternatives)_
  
  - [ ] 10.5 Ensure sufficient color contrast
    - Verify all text meets WCAG 4.5:1 contrast ratio
    - Add visible focus indicators (2px blue ring) on all interactive elements
    - Ensure error states use both color and icons (not color alone)
    - _Requirements: 19 (Accessibility Compliance)_

- [ ] 11. Mobile Responsive Design
  - [ ] 11.1 Implement responsive modal behavior
    - Make modals full-screen on mobile (<768px width)
    - Use slide-up animation on mobile instead of scale
    - Add fixed header with close button on mobile modals
    - Ensure scrollable content area on mobile
    - _Requirements: 20 (Mobile Responsive Design)_
  
  - [ ] 11.2 Optimize forms for mobile
    - Stack form fields vertically on mobile
    - Increase touch target sizes to minimum 44x44px
    - Use appropriate mobile keyboard types (email, url, number)
    - Simplify TinyMCE toolbar for mobile screens
    - _Requirements: 20 (Mobile Responsive Design)_
  
  - [ ] 11.3 Implement touch-friendly drag-and-drop
    - Configure SortableJS for touch gestures
    - Set touchStartThreshold: 10, delay: 100ms, delayOnTouchOnly: true
    - Add visual feedback for touch drag operations
    - _Requirements: 20 (Mobile Responsive Design)_
  
  - [ ] 11.4 Make tables responsive
    - Convert table rows to card layout on mobile
    - Use horizontal scroll for wide tables with sticky headers
    - Ensure action buttons remain accessible on mobile
    - _Requirements: 20 (Mobile Responsive Design)_

- [ ] 12. Security and Performance
  - [ ] 12.1 Implement HTML sanitization for rich text content
    - Install HTMLPurifier: `composer require ezyang/htmlpurifier`
    - Create `sanitizeContent()` method in SitePage and ContentBlock models
    - Whitelist allowed HTML tags: p, br, strong, em, u, h1-h4, ul, ol, li, a[href], img[src|alt]
    - Strip dangerous attributes: onclick, onerror, onload
    - Apply sanitization before saving content to database
    - _Requirements: Security best practices_
  
  - [ ] 12.2 Create SitePagePolicy for authorization
    - Implement `create()`, `update()`, `delete()` methods
    - Check user has 'super-admin' or 'site-admin' role
    - Register policy in AuthServiceProvider
    - _Requirements: Security best practices_
  
  - [ ] 12.3 Create ContentBlockPolicy and NavigationMenuPolicy
    - Similar authorization checks as SitePagePolicy
    - _Requirements: Security best practices_
  
  - [ ] 12.4 Add database indexes for performance
    - Verify indexes exist on: site_pages(is_published, sort_order), site_content_blocks(is_active, sort_order, category)
    - Add composite indexes if needed for common queries
    - _Requirements: Performance optimization_
  
  - [ ] 12.5 Implement caching for active blocks
    - Cache active content blocks with 1-hour TTL
    - Invalidate cache on block create/update/delete
    - Use Cache::remember() in controller index method
    - _Requirements: Performance optimization_
  
  - [ ] 12.6 Optimize frontend assets
    - Minify JavaScript and CSS using Vite
    - Enable gzip compression in web server config
    - Lazy load TinyMCE only when modal opens
    - Implement code splitting for large components
    - _Requirements: Performance optimization_

- [ ] 13. Testing and Quality Assurance
  - [ ]* 13.1 Write unit tests for SitePage model
    - Test slug auto-generation with unique suffix
    - Test audit trail (created_by, updated_by auto-set)
    - Test scopes: published(), ordered()
    - Test relationships: creator(), updater()
    - _Requirements: Testing strategy_
  
  - [ ]* 13.2 Write unit tests for form request validation
    - Test SitePageRequest validation rules
    - Test slug uniqueness validation
    - Test prepareForValidation() slug auto-generation
    - Test ContentBlockRequest and NavigationMenuRequest validation
    - _Requirements: Testing strategy_
  
  - [ ]* 13.3 Write feature tests for SitePageController API
    - Test index with search, filter, pagination
    - Test store creates page with valid data
    - Test store returns 422 with invalid data
    - Test update modifies existing page
    - Test destroy deletes page
    - Test duplicate creates copy with "(Copy)" suffix
    - Test reorder updates sort_order
    - Test bulkDelete removes multiple pages
    - Test authorization (403 for non-admin users)
    - _Requirements: Testing strategy_
  
  - [ ]* 13.4 Write feature tests for ContentBlockController API
    - Test CRUD operations
    - Test reorder functionality
    - Test validation errors
    - _Requirements: Testing strategy_
  
  - [ ]* 13.5 Write feature tests for NavigationMenuController API
    - Test menu CRUD operations
    - Test menu item CRUD operations
    - Test nested menu item creation
    - Test reorder with parent relationship updates
    - _Requirements: Testing strategy_
  
  - [ ]* 13.6 Write feature tests for ImageUploadController
    - Test successful image upload
    - Test file type validation (reject non-images)
    - Test file size validation (reject >5MB)
    - Test dimension validation (reject >4000x4000)
    - _Requirements: Testing strategy_
  
  - [ ]* 13.7 Write browser tests (Dusk) for end-to-end workflows
    - Test create page via modal with rich text content
    - Test edit page and save changes
    - Test delete page with confirmation
    - Test drag-and-drop page reordering
    - Test bulk delete multiple pages
    - Test search and filter functionality
    - Test pagination navigation
    - _Requirements: Testing strategy_
  
  - [ ]* 13.8 Perform accessibility audit
    - Test keyboard navigation through all modals and forms
    - Test screen reader announcements for toasts and errors
    - Test focus management (trap, restore)
    - Verify ARIA attributes are correct
    - Test with NVDA/JAWS screen readers
    - Run axe DevTools accessibility scan
    - _Requirements: 19 (Accessibility Compliance)_
  
  - [ ]* 13.9 Perform mobile responsive testing
    - Test on iOS Safari (iPhone 12, 13, 14)
    - Test on Android Chrome (Samsung, Pixel)
    - Test on tablet devices (iPad, Android tablet)
    - Verify touch gestures work for drag-and-drop
    - Verify modals are full-screen on mobile
    - Verify forms are usable on small screens
    - _Requirements: 20 (Mobile Responsive Design)_
  
  - [ ]* 13.10 Perform cross-browser compatibility testing
    - Test on Chrome (latest)
    - Test on Firefox (latest)
    - Test on Safari (latest)
    - Test on Edge (latest)
    - Verify TinyMCE works in all browsers
    - Verify drag-and-drop works in all browsers
    - _Requirements: Quality assurance_

- [ ] 14. Documentation and Deployment
  - [ ] 14.1 Update .env.example with new configuration
    - Add TINYMCE_API_KEY with placeholder
    - Add MAX_IMAGE_SIZE=5120
    - Add ALLOWED_IMAGE_TYPES=jpeg,png,gif,webp
    - Document each variable with comments
    - _Requirements: Deployment considerations_
  
  - [ ] 14.2 Create deployment checklist document
    - List all migration files to run
    - List npm install and build commands
    - List cache clear/warm commands
    - List verification steps for each feature
    - _Requirements: Deployment considerations_
  
  - [ ] 14.3 Write API documentation
    - Document all API endpoints with request/response examples
    - Document error response formats
    - Document authentication requirements
    - Create Postman collection for API testing
    - _Requirements: Documentation_
  
  - [ ] 14.4 Create user guide for administrators
    - Document how to create/edit/delete pages
    - Document how to use rich text editor
    - Document how to reorder items via drag-and-drop
    - Document keyboard shortcuts
    - Include screenshots and GIFs
    - _Requirements: Documentation_
  
  - [ ] 14.5 Run database migrations in staging environment
    - Test migration up and down
    - Verify data integrity after migration
    - Backup database before production migration
    - _Requirements: Deployment considerations_
  
  - [ ] 14.6 Compile and deploy frontend assets
    - Run `npm install` to install dependencies
    - Run `npm run build` to compile for production
    - Verify build output in public/build directory
    - Deploy assets to CDN if applicable
    - _Requirements: Deployment considerations_
  
  - [ ] 14.7 Clear and warm application caches
    - Run `php artisan cache:clear`
    - Run `php artisan config:clear`
    - Run `php artisan route:clear`
    - Run `php artisan view:clear`
    - Run `php artisan config:cache`
    - Run `php artisan route:cache`
    - Run `php artisan view:cache`
    - _Requirements: Deployment considerations_
  
  - [ ] 14.8 Perform smoke testing in production
    - Test create page operation
    - Test edit page operation
    - Test delete page operation
    - Test image upload in rich text editor
    - Test drag-and-drop reordering
    - Test on mobile device
    - Verify no console errors
    - _Requirements: Deployment considerations_

- [ ] 15. Final Polish and Optimization
  - [ ] 15.1 Review and refactor code for consistency
    - Ensure PSR-12 compliance
    - Remove console.log statements
    - Remove commented-out code
    - Ensure consistent naming conventions
    - _Requirements: Code quality_
  
  - [ ] 15.2 Optimize database queries
    - Review N+1 query issues using Laravel Debugbar
    - Add eager loading where needed
    - Optimize pagination queries
    - _Requirements: Performance optimization_
  
  - [ ] 15.3 Add loading skeletons for better UX
    - Create skeleton loaders for page list while loading
    - Create skeleton loaders for modal content while fetching
    - Use Tailwind animate-pulse for skeleton effect
    - _Requirements: User experience enhancement_
  
  - [~] 15.4 Implement optimistic UI updates
    - Update UI immediately on user action (before API response)
    - Revert changes if API call fails
    - Show subtle loading indicators during background sync
    - _Requirements: User experience enhancement_
  
  - [~] 15.5 Add confirmation for unsaved changes on page navigation
    - Listen for beforeunload event
    - Show browser confirmation dialog if form has unsaved changes
    - Clear flag after successful save
    - _Requirements: 18 (Unsaved Changes Warning)_
  
  - [~] 15.6 Implement auto-save draft functionality (optional enhancement)
    - Auto-save form data to localStorage every 30 seconds
    - Restore draft on modal open if available
    - Clear draft after successful save
    - Show "Draft restored" message when applicable
    - _Requirements: User experience enhancement_

## Notes

- Tasks marked with `*` are optional testing and quality assurance tasks that can be skipped for faster MVP delivery
- Each task references specific requirements from the requirements document for traceability
- The implementation follows 8 major phases: Database, Backend API, Frontend Core Components (Modals, Forms, Rich Text/Drag-Drop), AJAX Layer, Site Pages CRUD, Content Blocks CRUD, Navigation CRUD, Accessibility/Mobile, Security/Performance, Testing, Documentation, and Final Polish
- All tasks build incrementally on previous tasks to ensure a working system at each checkpoint
- Testing tasks are marked optional but highly recommended for production deployment
- The design uses PHP (Laravel) for backend and JavaScript (Alpine.js) for frontend reactivity

## Task Dependency Graph

```json
{
  "waves": [
    {
      "id": 0,
      "tasks": ["1.1", "1.2"]
    },
    {
      "id": 1,
      "tasks": ["1.3", "1.4", "2.1", "2.2", "2.3", "2.4"]
    },
    {
      "id": 2,
      "tasks": ["2.5", "2.6", "2.7", "2.8", "2.9", "2.10", "2.11", "2.12", "2.13"]
    },
    {
      "id": 3,
      "tasks": ["3.1", "3.2", "3.3", "4.1", "4.2", "4.3", "4.4", "4.5", "5.1"]
    },
    {
      "id": 4,
      "tasks": ["5.2", "5.3", "5.4", "5.5", "5.6", "5.7", "5.8", "6.1", "6.2", "6.3"]
    },
    {
      "id": 5,
      "tasks": ["7.1", "8.1", "9.1", "9.2"]
    },
    {
      "id": 6,
      "tasks": ["7.2", "7.3", "7.4", "7.5", "8.2", "8.3", "8.4", "9.3", "9.4", "9.5", "9.6"]
    },
    {
      "id": 7,
      "tasks": ["7.6", "7.7", "7.8", "7.9", "8.5", "8.6", "9.7", "9.8"]
    },
    {
      "id": 8,
      "tasks": ["10.1", "10.2", "10.3", "10.4", "10.5", "11.1", "11.2", "11.3", "11.4"]
    },
    {
      "id": 9,
      "tasks": ["12.1", "12.2", "12.3", "12.4", "12.5", "12.6"]
    },
    {
      "id": 10,
      "tasks": ["13.1", "13.2", "13.3", "13.4", "13.5", "13.6", "13.7", "13.8", "13.9", "13.10"]
    },
    {
      "id": 11,
      "tasks": ["14.1", "14.2", "14.3", "14.4"]
    },
    {
      "id": 12,
      "tasks": ["14.5", "14.6", "14.7"]
    },
    {
      "id": 13,
      "tasks": ["14.8", "15.1", "15.2", "15.3", "15.4", "15.5", "15.6"]
    }
  ]
}
```
