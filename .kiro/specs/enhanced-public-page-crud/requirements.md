# Requirements Document

## Introduction

This document specifies requirements for enhancing the unified public page management interface at `/admin/public-page` with robust inline CRUD (Create, Read, Update, Delete) functionality. The current implementation provides a basic tabbed interface (Site Pages, Page Builder, Site Navigation) but requires users to navigate to separate pages for create/edit operations. This enhancement will provide inline modals, forms, and interactive components directly within each tab to improve user experience, eliminate blank pages, and enable real-time updates without page reloads.

The system will support:
- Inline CRUD operations for Site Pages with rich text editing
- Inline block management for Page Builder with drag-and-drop reordering
- Inline menu management for Site Navigation with nested menu support
- Comprehensive validation, error handling, and user feedback
- Full accessibility and mobile responsiveness

## Glossary

- **Admin_Interface**: The unified public page management interface at `/admin/public-page`
- **Site_Pages_Tab**: The first tab in Admin_Interface for managing static site pages
- **Page_Builder_Tab**: The second tab in Admin_Interface for managing content blocks
- **Site_Navigation_Tab**: The third tab in Admin_Interface for managing navigation menus
- **CRUD_Modal**: A modal dialog or slide-over panel for create/edit operations
- **Rich_Text_Editor**: A WYSIWYG editor component (TinyMCE or similar) for HTML content editing
- **Content_Block**: A reusable content component managed in Page_Builder_Tab
- **Navigation_Menu**: A collection of menu items with hierarchical structure
- **Navigation_Menu_Item**: An individual link or item within Navigation_Menu
- **AJAX_Operation**: An asynchronous HTTP request that updates data without page reload
- **Toast_Notification**: A temporary notification message displayed to users
- **Validation_Error**: An error message indicating invalid form input
- **Loading_State**: A visual indicator showing an operation is in progress
- **Drag_Drop_Interface**: An interactive interface allowing users to reorder items by dragging
- **Nested_Menu**: A hierarchical menu structure with parent-child relationships
- **Form_Validator**: A component that validates form input on client or server side
- **Backend_API**: Laravel controller endpoints that handle CRUD operations
- **Alpine_Component**: An Alpine.js reactive component for UI interactivity
- **Confirmation_Dialog**: A modal dialog requesting user confirmation before destructive actions

## Requirements

### Requirement 1: Site Pages Inline Create Modal

**User Story:** As an administrator, I want to create new site pages inline within the Site Pages tab, so that I can add pages without navigating away from the management interface.

#### Acceptance Criteria

1. WHEN the administrator clicks "Create Page" button in Site_Pages_Tab, THE Admin_Interface SHALL display CRUD_Modal with a blank form
2. THE CRUD_Modal SHALL contain fields for page title, slug, content, status, and meta description
3. THE CRUD_Modal SHALL include Rich_Text_Editor for the content field
4. WHEN the administrator submits the create form with valid data, THE Backend_API SHALL create a new site page record
5. WHEN the create operation succeeds, THE Admin_Interface SHALL close CRUD_Modal and display Toast_Notification with success message
6. WHEN the create operation succeeds, THE Admin_Interface SHALL refresh the site pages list without page reload
7. WHEN the administrator clicks cancel or close button, THE Admin_Interface SHALL close CRUD_Modal without saving changes
8. WHEN the administrator clicks outside CRUD_Modal, THE Admin_Interface SHALL display Confirmation_Dialog before closing if form has unsaved changes

### Requirement 2: Site Pages Inline Edit Modal

**User Story:** As an administrator, I want to edit existing site pages inline within the Site Pages tab, so that I can update page content without navigating to a separate edit page.

#### Acceptance Criteria

1. WHEN the administrator clicks "Edit" button on a site page row, THE Admin_Interface SHALL display CRUD_Modal pre-populated with existing page data
2. THE CRUD_Modal SHALL load existing content into Rich_Text_Editor
3. WHEN the administrator modifies form fields and submits, THE Backend_API SHALL update the site page record
4. WHEN the update operation succeeds, THE Admin_Interface SHALL close CRUD_Modal and display Toast_Notification with success message
5. WHEN the update operation succeeds, THE Admin_Interface SHALL refresh the updated page row without page reload
6. WHEN the administrator clicks cancel, THE Admin_Interface SHALL close CRUD_Modal and discard unsaved changes
7. WHILE the update operation is in progress, THE Admin_Interface SHALL display Loading_State on the submit button

### Requirement 3: Site Pages Inline Delete with Confirmation

**User Story:** As an administrator, I want to delete site pages inline with confirmation, so that I can remove pages safely without accidental deletion.

#### Acceptance Criteria

1. WHEN the administrator clicks "Delete" button on a site page row, THE Admin_Interface SHALL display Confirmation_Dialog
2. THE Confirmation_Dialog SHALL display the page title and warning message about permanent deletion
3. WHEN the administrator confirms deletion, THE Backend_API SHALL delete the site page record
4. WHEN the delete operation succeeds, THE Admin_Interface SHALL remove the page row from the list and display Toast_Notification
5. WHEN the administrator cancels deletion, THE Admin_Interface SHALL close Confirmation_Dialog without deleting
6. WHILE the delete operation is in progress, THE Admin_Interface SHALL display Loading_State on the confirmation button

### Requirement 4: Rich Text Editor Integration

**User Story:** As an administrator, I want a rich text editor for page content, so that I can format text, add images, and create styled content without writing HTML.

#### Acceptance Criteria

1. WHEN CRUD_Modal displays a content field, THE Admin_Interface SHALL initialize Rich_Text_Editor
2. THE Rich_Text_Editor SHALL support text formatting (bold, italic, underline, headings)
3. THE Rich_Text_Editor SHALL support lists (ordered and unordered)
4. THE Rich_Text_Editor SHALL support links and image insertion
5. THE Rich_Text_Editor SHALL support HTML source code editing
6. WHEN the administrator saves the form, THE Rich_Text_Editor SHALL serialize content to HTML format
7. WHEN CRUD_Modal closes, THE Admin_Interface SHALL properly destroy Rich_Text_Editor instance to prevent memory leaks

### Requirement 5: Page Builder Inline Block Create

**User Story:** As an administrator, I want to create content blocks inline within the Page Builder tab, so that I can add new blocks without leaving the page builder interface.

#### Acceptance Criteria

1. WHEN the administrator clicks "Add Block" button in Page_Builder_Tab, THE Admin_Interface SHALL display CRUD_Modal with block creation form
2. THE CRUD_Modal SHALL contain fields for block type, title, content, display order, and visibility settings
3. THE CRUD_Modal SHALL include Rich_Text_Editor for block content
4. WHEN the administrator submits the form with valid data, THE Backend_API SHALL create a new Content_Block record
5. WHEN the create operation succeeds, THE Admin_Interface SHALL add the new block to the blocks list and display Toast_Notification
6. WHEN the create operation succeeds, THE Admin_Interface SHALL position the new block according to its display order

### Requirement 6: Page Builder Inline Block Edit

**User Story:** As an administrator, I want to edit content blocks inline within the Page Builder tab, so that I can modify block content and settings without navigating away.

#### Acceptance Criteria

1. WHEN the administrator clicks "Edit" button on a Content_Block, THE Admin_Interface SHALL display CRUD_Modal with existing block data
2. THE CRUD_Modal SHALL load block content into Rich_Text_Editor
3. WHEN the administrator updates block fields and submits, THE Backend_API SHALL update the Content_Block record
4. WHEN the update operation succeeds, THE Admin_Interface SHALL update the block display and show Toast_Notification
5. WHILE the update operation is in progress, THE Admin_Interface SHALL display Loading_State

### Requirement 7: Page Builder Drag-and-Drop Reordering

**User Story:** As an administrator, I want to reorder content blocks by dragging and dropping, so that I can easily arrange page layout without manually editing order numbers.

#### Acceptance Criteria

1. WHEN the administrator hovers over a Content_Block, THE Admin_Interface SHALL display a drag handle indicator
2. WHEN the administrator drags a Content_Block, THE Admin_Interface SHALL display visual feedback showing the dragged item
3. WHEN the administrator drops a Content_Block in a new position, THE Backend_API SHALL update display order for affected blocks
4. WHEN the reorder operation succeeds, THE Admin_Interface SHALL update block positions and display Toast_Notification
5. WHEN the reorder operation fails, THE Admin_Interface SHALL revert blocks to original positions and display error Toast_Notification
6. WHILE the reorder operation is in progress, THE Admin_Interface SHALL display Loading_State on affected blocks

### Requirement 8: Page Builder Live Preview

**User Story:** As an administrator, I want to preview content blocks as I edit them, so that I can see how changes will appear before saving.

#### Acceptance Criteria

1. WHEN the administrator edits a Content_Block in CRUD_Modal, THE Admin_Interface SHALL display a preview panel
2. WHEN the administrator modifies block content, THE Admin_Interface SHALL update the preview panel in real-time
3. THE preview panel SHALL render content with the same styling as the public-facing site
4. WHEN the administrator toggles between edit and preview modes, THE Admin_Interface SHALL switch views without losing unsaved changes

### Requirement 9: Site Navigation Inline Menu Create

**User Story:** As an administrator, I want to create navigation menus inline within the Site Navigation tab, so that I can add new menus without navigating to a separate page.

#### Acceptance Criteria

1. WHEN the administrator clicks "Create Menu" button in Site_Navigation_Tab, THE Admin_Interface SHALL display CRUD_Modal with menu creation form
2. THE CRUD_Modal SHALL contain fields for menu name, location, and status
3. WHEN the administrator submits the form with valid data, THE Backend_API SHALL create a new Navigation_Menu record
4. WHEN the create operation succeeds, THE Admin_Interface SHALL add the menu to the list and display Toast_Notification
5. WHEN the create operation succeeds, THE Admin_Interface SHALL expand the new menu to show empty menu items area

### Requirement 10: Site Navigation Inline Menu Item Create

**User Story:** As an administrator, I want to add menu items inline within a navigation menu, so that I can build menu structure without leaving the navigation interface.

#### Acceptance Criteria

1. WHEN the administrator clicks "Add Item" button within a Navigation_Menu, THE Admin_Interface SHALL display CRUD_Modal with menu item form
2. THE CRUD_Modal SHALL contain fields for item label, URL, target, icon, and parent item selection
3. WHEN the administrator selects a parent item, THE CRUD_Modal SHALL create a Nested_Menu relationship
4. WHEN the administrator submits the form with valid data, THE Backend_API SHALL create a new Navigation_Menu_Item record
5. WHEN the create operation succeeds, THE Admin_Interface SHALL add the item to the menu tree and display Toast_Notification
6. WHEN creating a nested item, THE Admin_Interface SHALL indent the item visually to show hierarchy

### Requirement 11: Site Navigation Inline Menu Item Edit

**User Story:** As an administrator, I want to edit menu items inline, so that I can update links and labels without navigating away.

#### Acceptance Criteria

1. WHEN the administrator clicks "Edit" button on a Navigation_Menu_Item, THE Admin_Interface SHALL display CRUD_Modal with existing item data
2. THE CRUD_Modal SHALL allow changing parent item to restructure menu hierarchy
3. WHEN the administrator updates item fields and submits, THE Backend_API SHALL update the Navigation_Menu_Item record
4. WHEN the update operation succeeds, THE Admin_Interface SHALL update the menu tree display and show Toast_Notification
5. WHEN changing parent item, THE Admin_Interface SHALL move the item to the new position in the hierarchy

### Requirement 12: Site Navigation Drag-and-Drop Menu Reordering

**User Story:** As an administrator, I want to reorder menu items by dragging and dropping, so that I can easily arrange menu structure without manually editing order numbers.

#### Acceptance Criteria

1. WHEN the administrator hovers over a Navigation_Menu_Item, THE Admin_Interface SHALL display a drag handle
2. WHEN the administrator drags a Navigation_Menu_Item, THE Admin_Interface SHALL show visual feedback and valid drop zones
3. WHEN the administrator drops an item in a new position, THE Backend_API SHALL update display order and parent relationships
4. WHEN the administrator drops an item onto another item, THE Admin_Interface SHALL create a Nested_Menu relationship
5. WHEN the reorder operation succeeds, THE Admin_Interface SHALL update the menu tree and display Toast_Notification
6. WHEN the reorder operation fails, THE Admin_Interface SHALL revert to original structure and display error Toast_Notification

### Requirement 13: Client-Side Form Validation

**User Story:** As an administrator, I want immediate feedback on form errors, so that I can correct mistakes before submitting forms.

#### Acceptance Criteria

1. WHEN the administrator enters data in a required field, THE Form_Validator SHALL validate the input in real-time
2. WHEN the administrator leaves a required field empty, THE Form_Validator SHALL display Validation_Error below the field
3. WHEN the administrator enters invalid data format (e.g., invalid URL), THE Form_Validator SHALL display Validation_Error with format requirements
4. WHEN all form fields are valid, THE Form_Validator SHALL enable the submit button
5. WHEN any form field is invalid, THE Form_Validator SHALL disable the submit button
6. THE Validation_Error messages SHALL be clear and actionable (e.g., "Page title is required" not "Invalid input")

### Requirement 14: Server-Side Form Validation

**User Story:** As an administrator, I want server-side validation to catch errors that client-side validation missed, so that data integrity is maintained.

#### Acceptance Criteria

1. WHEN the administrator submits a form, THE Backend_API SHALL validate all input data
2. WHEN server validation fails, THE Backend_API SHALL return Validation_Error messages with HTTP 422 status
3. WHEN the Admin_Interface receives validation errors, THE Admin_Interface SHALL display Validation_Error messages next to corresponding form fields
4. WHEN the Admin_Interface receives validation errors, THE Admin_Interface SHALL keep CRUD_Modal open with user's input preserved
5. THE Backend_API SHALL validate business rules (e.g., unique slugs, valid parent relationships)
6. WHEN duplicate slug is detected, THE Backend_API SHALL return Validation_Error with suggestion for unique slug

### Requirement 15: AJAX Operations with Loading States

**User Story:** As an administrator, I want visual feedback during save operations, so that I know the system is processing my request.

#### Acceptance Criteria

1. WHEN the administrator submits a form, THE Admin_Interface SHALL display Loading_State on the submit button
2. WHILE an AJAX_Operation is in progress, THE Admin_Interface SHALL disable the submit button to prevent duplicate submissions
3. WHILE an AJAX_Operation is in progress, THE Admin_Interface SHALL display a loading spinner or progress indicator
4. WHEN an AJAX_Operation completes, THE Admin_Interface SHALL remove Loading_State
5. WHEN an AJAX_Operation times out, THE Admin_Interface SHALL display error Toast_Notification with retry option
6. THE Loading_State SHALL include text indicating the operation (e.g., "Saving..." instead of generic "Loading...")

### Requirement 16: Toast Notification System

**User Story:** As an administrator, I want clear notifications for operation results, so that I know whether my actions succeeded or failed.

#### Acceptance Criteria

1. WHEN a create operation succeeds, THE Admin_Interface SHALL display Toast_Notification with success message and created item name
2. WHEN an update operation succeeds, THE Admin_Interface SHALL display Toast_Notification with success message
3. WHEN a delete operation succeeds, THE Admin_Interface SHALL display Toast_Notification with success message
4. WHEN any operation fails, THE Admin_Interface SHALL display Toast_Notification with error message and failure reason
5. THE Toast_Notification SHALL auto-dismiss after 5 seconds for success messages
6. THE Toast_Notification SHALL remain visible until dismissed for error messages
7. THE Toast_Notification SHALL include a close button for manual dismissal
8. THE Toast_Notification SHALL be positioned consistently (e.g., top-right corner) and not obscure important content

### Requirement 17: Error Handling and Recovery

**User Story:** As an administrator, I want helpful error messages and recovery options when operations fail, so that I can resolve issues and complete my tasks.

#### Acceptance Criteria

1. WHEN a network error occurs during AJAX_Operation, THE Admin_Interface SHALL display Toast_Notification with "Network error" message and retry button
2. WHEN a server error (HTTP 500) occurs, THE Admin_Interface SHALL display Toast_Notification with "Server error" message and support contact information
3. WHEN an authorization error (HTTP 403) occurs, THE Admin_Interface SHALL display Toast_Notification explaining insufficient permissions
4. WHEN a validation error occurs, THE Admin_Interface SHALL keep the form open with user input preserved
5. WHEN the administrator clicks retry on a failed operation, THE Admin_Interface SHALL re-attempt the AJAX_Operation
6. WHEN multiple errors occur, THE Admin_Interface SHALL display them in a stacked notification format

### Requirement 18: Unsaved Changes Warning

**User Story:** As an administrator, I want warnings before losing unsaved changes, so that I don't accidentally discard my work.

#### Acceptance Criteria

1. WHEN the administrator modifies any form field in CRUD_Modal, THE Admin_Interface SHALL track unsaved changes
2. WHEN the administrator attempts to close CRUD_Modal with unsaved changes, THE Admin_Interface SHALL display Confirmation_Dialog
3. THE Confirmation_Dialog SHALL ask "You have unsaved changes. Are you sure you want to close?"
4. WHEN the administrator confirms closing, THE Admin_Interface SHALL close CRUD_Modal and discard changes
5. WHEN the administrator cancels closing, THE Admin_Interface SHALL keep CRUD_Modal open with changes preserved
6. WHEN the administrator successfully saves changes, THE Admin_Interface SHALL clear the unsaved changes flag

### Requirement 19: Accessibility Compliance

**User Story:** As an administrator using assistive technology, I want the interface to be fully accessible, so that I can manage pages, blocks, and navigation independently.

#### Acceptance Criteria

1. THE Admin_Interface SHALL support keyboard navigation for all interactive elements
2. WHEN the administrator opens CRUD_Modal, THE Admin_Interface SHALL move focus to the first form field
3. WHEN the administrator presses Escape key in CRUD_Modal, THE Admin_Interface SHALL close the modal (with unsaved changes warning if applicable)
4. THE Admin_Interface SHALL provide ARIA labels for all buttons, form fields, and interactive elements
5. THE Admin_Interface SHALL announce Toast_Notification messages to screen readers using ARIA live regions
6. THE Drag_Drop_Interface SHALL provide keyboard alternatives for reordering (e.g., up/down buttons)
7. THE Form_Validator SHALL associate Validation_Error messages with form fields using ARIA attributes
8. THE Admin_Interface SHALL maintain focus management when opening/closing modals and dialogs

### Requirement 20: Mobile Responsive Design

**User Story:** As an administrator using a mobile device, I want the interface to work well on small screens, so that I can manage content from any device.

#### Acceptance Criteria

1. WHEN the Admin_Interface is viewed on a screen width below 768px, THE Admin_Interface SHALL display CRUD_Modal as full-screen overlay
2. WHEN the Admin_Interface is viewed on mobile, THE Admin_Interface SHALL stack form fields vertically for better usability
3. WHEN the Admin_Interface is viewed on mobile, THE Admin_Interface SHALL provide touch-friendly button sizes (minimum 44x44px)
4. THE Drag_Drop_Interface SHALL support touch gestures for drag-and-drop on mobile devices
5. WHEN the Admin_Interface is viewed on mobile, THE Rich_Text_Editor SHALL display a mobile-optimized toolbar
6. THE Admin_Interface SHALL ensure all interactive elements are accessible via touch without requiring hover states

### Requirement 21: Slug Auto-Generation

**User Story:** As an administrator, I want page slugs to be automatically generated from titles, so that I don't have to manually create URL-friendly slugs.

#### Acceptance Criteria

1. WHEN the administrator enters a page title in the create form, THE Form_Validator SHALL automatically generate a slug
2. THE Form_Validator SHALL convert the title to lowercase, replace spaces with hyphens, and remove special characters
3. WHEN the administrator manually edits the slug field, THE Form_Validator SHALL stop auto-generation for that form session
4. WHEN the generated slug conflicts with an existing slug, THE Form_Validator SHALL append a numeric suffix (e.g., "about-us-2")
5. THE slug field SHALL display the auto-generated value in real-time as the administrator types the title

### Requirement 22: Bulk Delete Operations

**User Story:** As an administrator, I want to delete multiple items at once, so that I can efficiently clean up outdated content.

#### Acceptance Criteria

1. WHEN the administrator selects multiple items using checkboxes, THE Admin_Interface SHALL display a "Delete Selected" button
2. WHEN the administrator clicks "Delete Selected", THE Admin_Interface SHALL display Confirmation_Dialog with count of selected items
3. WHEN the administrator confirms bulk deletion, THE Backend_API SHALL delete all selected items
4. WHEN the bulk delete operation succeeds, THE Admin_Interface SHALL remove deleted items from the list and display Toast_Notification with count
5. WHEN some items fail to delete, THE Admin_Interface SHALL display Toast_Notification listing which items failed and why
6. THE Admin_Interface SHALL provide a "Select All" checkbox to quickly select all visible items

### Requirement 23: Search and Filter Functionality

**User Story:** As an administrator, I want to search and filter pages, blocks, and menu items, so that I can quickly find specific content in large lists.

#### Acceptance Criteria

1. WHEN the administrator enters text in the search field, THE Admin_Interface SHALL filter the list to show only matching items
2. THE Admin_Interface SHALL search across multiple fields (title, slug, content) for comprehensive results
3. WHEN the administrator selects a status filter, THE Admin_Interface SHALL display only items with that status
4. WHEN the administrator applies multiple filters, THE Admin_Interface SHALL combine them with AND logic
5. WHEN the administrator clears search/filters, THE Admin_Interface SHALL restore the full list
6. THE search operation SHALL be debounced to avoid excessive filtering during typing

### Requirement 24: Pagination for Large Lists

**User Story:** As an administrator, I want paginated lists for large datasets, so that the interface remains performant with hundreds of pages or menu items.

#### Acceptance Criteria

1. WHEN a list contains more than 25 items, THE Admin_Interface SHALL display pagination controls
2. THE Admin_Interface SHALL show page numbers, previous/next buttons, and total item count
3. WHEN the administrator clicks a page number, THE Backend_API SHALL load that page of results via AJAX_Operation
4. WHEN the administrator changes page, THE Admin_Interface SHALL maintain current search and filter settings
5. THE Admin_Interface SHALL display a loading indicator while fetching paginated results
6. THE Admin_Interface SHALL allow the administrator to change items per page (25, 50, 100)

### Requirement 25: Audit Trail for Changes

**User Story:** As an administrator, I want to see who made changes and when, so that I can track content modifications and maintain accountability.

#### Acceptance Criteria

1. WHEN the Backend_API creates a new record, THE Backend_API SHALL store the creating user ID and timestamp
2. WHEN the Backend_API updates a record, THE Backend_API SHALL store the updating user ID and timestamp
3. WHEN the administrator views item details, THE Admin_Interface SHALL display "Created by [user] on [date]" and "Last updated by [user] on [date]"
4. THE Admin_Interface SHALL format timestamps in a human-readable format (e.g., "2 hours ago", "January 15, 2025")
5. WHEN the administrator hovers over a relative timestamp, THE Admin_Interface SHALL display the absolute date and time in a tooltip

### Requirement 26: Draft and Publish Workflow

**User Story:** As an administrator, I want to save pages and blocks as drafts before publishing, so that I can work on content over time without making it public immediately.

#### Acceptance Criteria

1. WHEN the administrator creates or edits an item, THE CRUD_Modal SHALL provide "Save as Draft" and "Publish" buttons
2. WHEN the administrator clicks "Save as Draft", THE Backend_API SHALL save the item with status "draft"
3. WHEN the administrator clicks "Publish", THE Backend_API SHALL save the item with status "published"
4. WHEN viewing the list, THE Admin_Interface SHALL visually distinguish draft items from published items (e.g., badge or icon)
5. THE Admin_Interface SHALL provide a quick action to publish draft items directly from the list view
6. WHEN the administrator filters by status, THE Admin_Interface SHALL show draft and published items separately

### Requirement 27: Content Duplication

**User Story:** As an administrator, I want to duplicate existing pages or blocks, so that I can create similar content quickly without starting from scratch.

#### Acceptance Criteria

1. WHEN the administrator clicks "Duplicate" button on an item, THE Backend_API SHALL create a copy of the item
2. THE Backend_API SHALL append " (Copy)" to the duplicated item's title
3. THE Backend_API SHALL generate a unique slug for the duplicated item
4. THE Backend_API SHALL set the duplicated item's status to "draft"
5. WHEN the duplication succeeds, THE Admin_Interface SHALL display the duplicated item in the list and show Toast_Notification
6. WHEN the administrator duplicates an item, THE Admin_Interface SHALL optionally open CRUD_Modal with the duplicated item for immediate editing

### Requirement 28: Image Upload in Rich Text Editor

**User Story:** As an administrator, I want to upload images directly within the rich text editor, so that I can add visual content to pages and blocks without using external image URLs.

#### Acceptance Criteria

1. WHEN the administrator clicks the image button in Rich_Text_Editor, THE Admin_Interface SHALL display an image upload dialog
2. THE image upload dialog SHALL allow selecting files from the local device
3. WHEN the administrator selects an image file, THE Backend_API SHALL upload and store the image
4. WHEN the upload succeeds, THE Rich_Text_Editor SHALL insert the image at the cursor position
5. THE Backend_API SHALL validate image file types (JPEG, PNG, GIF, WebP) and reject invalid formats
6. THE Backend_API SHALL validate image file size (maximum 5MB) and reject oversized files
7. WHEN upload fails, THE Admin_Interface SHALL display Validation_Error with specific reason (file type, size, etc.)

### Requirement 29: Menu Item Icon Selection

**User Story:** As an administrator, I want to select icons for menu items, so that I can create visually enhanced navigation menus.

#### Acceptance Criteria

1. WHEN the administrator creates or edits a Navigation_Menu_Item, THE CRUD_Modal SHALL include an icon picker field
2. THE icon picker SHALL display a searchable list of available icons (Font Awesome, Heroicons, or similar)
3. WHEN the administrator selects an icon, THE CRUD_Modal SHALL display a preview of the selected icon
4. WHEN the administrator saves the menu item with an icon, THE Admin_Interface SHALL display the icon next to the menu item label
5. THE icon picker SHALL allow clearing the selected icon to create text-only menu items

### Requirement 30: Keyboard Shortcuts

**User Story:** As an administrator, I want keyboard shortcuts for common actions, so that I can work more efficiently without constantly using the mouse.

#### Acceptance Criteria

1. WHEN the administrator presses Ctrl+N (or Cmd+N on Mac) in Site_Pages_Tab, THE Admin_Interface SHALL open the create page modal
2. WHEN the administrator presses Ctrl+S (or Cmd+S on Mac) in CRUD_Modal, THE Admin_Interface SHALL submit the form
3. WHEN the administrator presses Escape in CRUD_Modal, THE Admin_Interface SHALL close the modal (with unsaved changes warning)
4. WHEN the administrator presses Ctrl+F (or Cmd+F on Mac), THE Admin_Interface SHALL focus the search field
5. THE Admin_Interface SHALL display a keyboard shortcuts help dialog when the administrator presses "?" key
6. THE keyboard shortcuts SHALL not conflict with browser default shortcuts
