# Requirements Document

## Introduction

The Admin Management Module provides a comprehensive database-driven interface for Super Admins to manage site-wide content that appears on the public portal. This module addresses the current limitation where the System Settings page shows "No system settings found" and requires manual seeder execution. The module includes three core management interfaces: System Settings Management (for site configuration), Subject Fields Management (for categories used in Browse by Subject and filtering), and Accreditation Management (for journal accreditation levels).

## Glossary

- **Admin_Management_Module**: The complete system comprising System Settings, Subject Fields, and Accreditation management interfaces
- **System_Settings_Interface**: The UI component for managing key-value configuration pairs
- **Subject_Fields_Interface**: The UI component for managing categories (also known as Browse by Category)
- **Accreditation_Interface**: The UI component for managing accreditation levels
- **Super_Admin**: A user with the highest privilege level who can access all admin management features
- **Category**: A subject field classification used for organizing journals (e.g., "Science & Technology")
- **Accreditation**: A quality certification level for journals (e.g., "SINTA 1", "Scopus")
- **Public_Portal**: The public-facing website where managed content is displayed
- **CRUD_Operation**: Create, Read, Update, Delete operations
- **Sort_Order**: An integer value determining display priority (lower values appear first)
- **Slug**: A URL-friendly identifier auto-generated from the name (e.g., "science-technology")
- **Font_Awesome_Icon**: An icon identifier from the Font Awesome 6 library (e.g., "fa-flask")
- **Color_Palette**: A predefined set of colors (blue, red, green, purple, amber, indigo, slate, orange, emerald, teal, cyan, gray)
- **Badge_Color**: A color for accreditation badges (amber, slate, blue, purple, green, red)
- **Active_Status**: A boolean flag indicating whether an item is visible on the public portal
- **Audit_Log**: A record of all administrative operations for compliance tracking
- **Cache**: A temporary storage mechanism that must be cleared when settings change
- **Stats_Base_Offset**: A numeric value added to actual statistics for display purposes
- **Drag_and_Drop_Reordering**: A UI interaction allowing users to change sort order by dragging items
- **Icon_Picker**: A UI component for selecting Font Awesome icons
- **Color_Picker**: A UI component for selecting colors from the predefined palette
- **Preview_Mode**: A display mode showing how content will appear on the public portal
- **Bulk_Operation**: An action applied to multiple items simultaneously
- **Concurrent_Edit**: Multiple users attempting to modify the same data simultaneously
- **XSS_Prevention**: Cross-Site Scripting attack prevention through input validation
- **SQL_Injection_Prevention**: Database attack prevention through parameterized queries
- **Backward_Compatibility**: Ensuring existing data and functionality continue to work after changes

## Requirements

### Requirement 1: System Settings Database Storage

**User Story:** As a Super Admin, I want system settings stored in a database table, so that I can manage them through a UI instead of running seeders.

#### Acceptance Criteria

1. THE System_Settings_Interface SHALL store all settings as key-value pairs in the database
2. WHEN the System Settings page is accessed, THE System_Settings_Interface SHALL retrieve all settings from the database
3. THE System_Settings_Interface SHALL support the following setting keys: site_name, site_description, contact_email, facebook_url, twitter_url, linkedin_url, instagram_url, analytics_code, meta_title, meta_description, meta_keywords, maintenance_mode, registration_enabled, email_from_address, email_from_name, cache_enabled, cache_ttl, stats_journals_offset, stats_articles_offset, stats_authors_offset, stats_downloads_offset
4. WHEN a setting does not exist in the database, THE System_Settings_Interface SHALL return a default value
5. THE System_Settings_Interface SHALL validate all setting values before storage to prevent XSS_Prevention violations

### Requirement 2: System Settings CRUD Operations

**User Story:** As a Super Admin, I want to create, read, update, and delete system settings, so that I can configure the site without database access.

#### Acceptance Criteria

1. WHEN a Super_Admin accesses the System Settings page, THE System_Settings_Interface SHALL display all existing settings grouped by category
2. WHEN a Super_Admin updates a setting value, THE System_Settings_Interface SHALL save the change to the database within 500ms
3. WHEN a Super_Admin creates a new setting, THE System_Settings_Interface SHALL validate the key is unique before saving
4. WHEN a Super_Admin deletes a setting, THE System_Settings_Interface SHALL prompt for confirmation before deletion
5. WHEN a setting is modified, THE System_Settings_Interface SHALL clear the Cache to ensure changes are visible immediately
6. WHEN a setting operation completes, THE System_Settings_Interface SHALL log the operation to the Audit_Log

### Requirement 3: System Settings Access Control

**User Story:** As a system administrator, I want only Super Admins to access system settings, so that unauthorized users cannot modify critical configuration.

#### Acceptance Criteria

1. WHEN a user who is not a Super_Admin attempts to access the System Settings page, THE System_Settings_Interface SHALL return a 403 Forbidden response
2. WHEN a user who is not a Super_Admin attempts to modify a setting via API, THE System_Settings_Interface SHALL reject the request with a 403 Forbidden response
3. THE System_Settings_Interface SHALL verify Super_Admin role on every request before processing
4. WHEN role verification fails, THE System_Settings_Interface SHALL log the unauthorized access attempt to the Audit_Log

### Requirement 4: Subject Fields Database Storage

**User Story:** As a Super Admin, I want subject categories stored in a database table, so that I can manage them through a UI instead of hardcoding them.

#### Acceptance Criteria

1. THE Subject_Fields_Interface SHALL store all categories in the categories table with the following fields: id, name, slug, description, icon, color, sort_order, is_active, journal_id, created_at, updated_at
2. WHEN a Category is created, THE Subject_Fields_Interface SHALL auto-generate the Slug from the name
3. WHEN a Category name contains special characters, THE Subject_Fields_Interface SHALL sanitize them in the Slug (e.g., "Science & Technology" becomes "science-technology")
4. THE Subject_Fields_Interface SHALL validate that Font_Awesome_Icon values match the pattern "fa-[a-z0-9-]+"
5. THE Subject_Fields_Interface SHALL validate that color values are from the Color_Palette
6. WHEN journal_id is NULL, THE Subject_Fields_Interface SHALL treat the Category as site-level (visible across all journals)

### Requirement 5: Subject Fields CRUD Operations

**User Story:** As a Super Admin, I want to create, read, update, and delete subject categories, so that I can organize journals by topic.

#### Acceptance Criteria

1. WHEN a Super_Admin creates a Category, THE Subject_Fields_Interface SHALL validate all required fields (name, icon, color) are provided
2. WHEN a Super_Admin updates a Category, THE Subject_Fields_Interface SHALL preserve the original Slug unless explicitly changed
3. WHEN a Super_Admin deletes a Category, THE Subject_Fields_Interface SHALL check if any journals are using it and warn before deletion
4. WHEN a Category is deleted, THE Subject_Fields_Interface SHALL remove all associations with journals
5. WHEN a Category operation completes, THE Subject_Fields_Interface SHALL log the operation to the Audit_Log
6. WHEN a Category is modified, THE Subject_Fields_Interface SHALL clear the Cache to ensure changes are visible on the Public_Portal

### Requirement 6: Subject Fields Drag-and-Drop Reordering

**User Story:** As a Super Admin, I want to reorder categories by dragging them, so that important categories appear first on the public portal.

#### Acceptance Criteria

1. WHEN a Super_Admin drags a Category to a new position, THE Subject_Fields_Interface SHALL update the Sort_Order values within 200ms
2. WHEN Sort_Order values are updated, THE Subject_Fields_Interface SHALL recalculate all Sort_Order values to maintain sequential ordering
3. WHEN Drag_and_Drop_Reordering is in progress, THE Subject_Fields_Interface SHALL provide visual feedback showing the new position
4. WHEN a reordering operation fails, THE Subject_Fields_Interface SHALL revert to the previous Sort_Order values
5. WHEN reordering completes, THE Subject_Fields_Interface SHALL clear the Cache to ensure the new order is visible on the Public_Portal

### Requirement 7: Subject Fields Icon and Color Selection

**User Story:** As a Super Admin, I want to choose icons and colors for categories using visual pickers, so that categories are visually appealing and distinct.

#### Acceptance Criteria

1. WHEN a Super_Admin clicks the icon field, THE Subject_Fields_Interface SHALL display an Icon_Picker with all Font Awesome 6 icons
2. WHEN a Super_Admin searches in the Icon_Picker, THE Subject_Fields_Interface SHALL filter icons by name within 100ms
3. WHEN a Super_Admin clicks the color field, THE Subject_Fields_Interface SHALL display a Color_Picker with the Color_Palette
4. WHEN a Super_Admin selects a color, THE Subject_Fields_Interface SHALL show a preview of how the Category will appear on the Public_Portal
5. THE Subject_Fields_Interface SHALL display the selected icon and color in real-time as the Super_Admin makes changes

### Requirement 8: Subject Fields Bulk Operations

**User Story:** As a Super Admin, I want to activate or deactivate multiple categories at once, so that I can efficiently manage category visibility.

#### Acceptance Criteria

1. WHEN a Super_Admin selects multiple categories, THE Subject_Fields_Interface SHALL display bulk action options
2. WHEN a Super_Admin clicks "Bulk Activate", THE Subject_Fields_Interface SHALL set is_active to true for all selected categories
3. WHEN a Super_Admin clicks "Bulk Deactivate", THE Subject_Fields_Interface SHALL set is_active to false for all selected categories
4. WHEN a Bulk_Operation completes, THE Subject_Fields_Interface SHALL display a success message showing the number of affected categories
5. WHEN a Bulk_Operation completes, THE Subject_Fields_Interface SHALL clear the Cache and log the operation to the Audit_Log

### Requirement 9: Subject Fields Preview Mode

**User Story:** As a Super Admin, I want to preview how a category will look on the public portal, so that I can verify appearance before saving.

#### Acceptance Criteria

1. WHEN a Super_Admin clicks the preview button, THE Subject_Fields_Interface SHALL display the Category in Preview_Mode matching the Public_Portal styling
2. THE Subject_Fields_Interface SHALL show the Category with the selected icon, color, name, and description in Preview_Mode
3. WHEN a Super_Admin changes any field, THE Subject_Fields_Interface SHALL update the Preview_Mode in real-time
4. THE Subject_Fields_Interface SHALL render the preview using the same CSS classes and HTML structure as the Public_Portal

### Requirement 10: Accreditation Database Storage

**User Story:** As a Super Admin, I want accreditations stored in a database table, so that I can manage them through a UI instead of hardcoding them.

#### Acceptance Criteria

1. THE Accreditation_Interface SHALL store all accreditations in the accreditations table with the following fields: id, name, slug, level, color, sort_order, is_active, created_at, updated_at
2. WHEN an Accreditation is created, THE Accreditation_Interface SHALL auto-generate the Slug from the name
3. WHEN an Accreditation name contains special characters, THE Accreditation_Interface SHALL sanitize them in the Slug (e.g., "SINTA 1" becomes "sinta-1")
4. THE Accreditation_Interface SHALL validate that color values are from the Badge_Color set
5. THE Accreditation_Interface SHALL validate that level values are 2-character codes (e.g., "S1", "S2", "SC", "DJ")

### Requirement 11: Accreditation CRUD Operations

**User Story:** As a Super Admin, I want to create, read, update, and delete accreditations, so that journals can be filtered by accreditation level.

#### Acceptance Criteria

1. WHEN a Super_Admin creates an Accreditation, THE Accreditation_Interface SHALL validate all required fields (name, level, color) are provided
2. WHEN a Super_Admin updates an Accreditation, THE Accreditation_Interface SHALL preserve the original Slug unless explicitly changed
3. WHEN a Super_Admin deletes an Accreditation, THE Accreditation_Interface SHALL check if any journals are using it and warn before deletion
4. WHEN an Accreditation is deleted, THE Accreditation_Interface SHALL remove all associations with journals
5. WHEN an Accreditation operation completes, THE Accreditation_Interface SHALL log the operation to the Audit_Log
6. WHEN an Accreditation is modified, THE Accreditation_Interface SHALL clear the Cache to ensure changes are visible on the Public_Portal

### Requirement 12: Accreditation Drag-and-Drop Reordering

**User Story:** As a Super Admin, I want to reorder accreditations by dragging them, so that higher levels appear first in filters.

#### Acceptance Criteria

1. WHEN a Super_Admin drags an Accreditation to a new position, THE Accreditation_Interface SHALL update the Sort_Order values within 200ms
2. WHEN Sort_Order values are updated, THE Accreditation_Interface SHALL recalculate all Sort_Order values to maintain sequential ordering
3. WHEN Drag_and_Drop_Reordering is in progress, THE Accreditation_Interface SHALL provide visual feedback showing the new position
4. WHEN a reordering operation fails, THE Accreditation_Interface SHALL revert to the previous Sort_Order values
5. WHEN reordering completes, THE Accreditation_Interface SHALL clear the Cache to ensure the new order is visible on the Public_Portal

### Requirement 13: Accreditation Badge Preview

**User Story:** As a Super Admin, I want to preview how an accreditation badge will look, so that I can verify appearance before saving.

#### Acceptance Criteria

1. WHEN a Super_Admin selects a Badge_Color, THE Accreditation_Interface SHALL display a preview of the badge with that color
2. THE Accreditation_Interface SHALL show the badge preview using the same styling as the Public_Portal
3. WHEN a Super_Admin changes the name or level, THE Accreditation_Interface SHALL update the badge preview in real-time
4. THE Accreditation_Interface SHALL render the preview using the same CSS classes and HTML structure as the Public_Portal

### Requirement 14: Accreditation Bulk Operations

**User Story:** As a Super Admin, I want to activate or deactivate multiple accreditations at once, so that I can efficiently manage accreditation visibility.

#### Acceptance Criteria

1. WHEN a Super_Admin selects multiple accreditations, THE Accreditation_Interface SHALL display bulk action options
2. WHEN a Super_Admin clicks "Bulk Activate", THE Accreditation_Interface SHALL set is_active to true for all selected accreditations
3. WHEN a Super_Admin clicks "Bulk Deactivate", THE Accreditation_Interface SHALL set is_active to false for all selected accreditations
4. WHEN a Bulk_Operation completes, THE Accreditation_Interface SHALL display a success message showing the number of affected accreditations
5. WHEN a Bulk_Operation completes, THE Accreditation_Interface SHALL clear the Cache and log the operation to the Audit_Log

### Requirement 15: Concurrent Edit Handling

**User Story:** As a system administrator, I want the system to handle concurrent edits gracefully, so that data integrity is maintained when multiple admins work simultaneously.

#### Acceptance Criteria

1. WHEN two Super_Admins attempt to modify the same setting simultaneously, THE Admin_Management_Module SHALL use optimistic locking to detect conflicts
2. WHEN a Concurrent_Edit conflict is detected, THE Admin_Management_Module SHALL notify the second user that the data has changed
3. WHEN a conflict notification is shown, THE Admin_Management_Module SHALL display the current values and allow the user to choose whether to overwrite or cancel
4. THE Admin_Management_Module SHALL use database transactions to ensure atomic updates
5. WHEN a transaction fails due to a conflict, THE Admin_Management_Module SHALL roll back all changes and preserve data integrity

### Requirement 16: Input Validation and Security

**User Story:** As a security administrator, I want all inputs validated and sanitized, so that the system is protected from XSS and SQL injection attacks.

#### Acceptance Criteria

1. WHEN a Super_Admin submits any form, THE Admin_Management_Module SHALL validate all inputs against expected data types and formats
2. THE Admin_Management_Module SHALL sanitize all text inputs to prevent XSS_Prevention violations
3. THE Admin_Management_Module SHALL use parameterized queries for all database operations to ensure SQL_Injection_Prevention
4. WHEN invalid input is detected, THE Admin_Management_Module SHALL reject the request and display a specific error message
5. THE Admin_Management_Module SHALL validate URL fields (social media links, analytics code) to ensure they are properly formatted
6. THE Admin_Management_Module SHALL limit text field lengths to prevent buffer overflow attacks (name: 255 chars, description: 1000 chars)

### Requirement 17: Audit Logging

**User Story:** As a compliance officer, I want all administrative operations logged, so that I can track who made what changes and when.

#### Acceptance Criteria

1. WHEN any CRUD_Operation is performed, THE Admin_Management_Module SHALL log the operation to the Audit_Log
2. THE Admin_Management_Module SHALL record the following in each Audit_Log entry: timestamp, user_id, user_email, action (create/update/delete), entity_type (setting/category/accreditation), entity_id, old_values, new_values, ip_address
3. WHEN a Bulk_Operation is performed, THE Admin_Management_Module SHALL create individual Audit_Log entries for each affected item
4. THE Admin_Management_Module SHALL retain Audit_Log entries for at least 365 days
5. WHEN an Audit_Log write fails, THE Admin_Management_Module SHALL not block the primary operation but SHALL log the failure to the application error log

### Requirement 18: Cache Management

**User Story:** As a Super Admin, I want changes to be visible immediately on the public portal, so that I can verify my changes without waiting for cache expiration.

#### Acceptance Criteria

1. WHEN any setting is modified, THE Admin_Management_Module SHALL clear all Cache entries related to system settings
2. WHEN any Category is modified, THE Admin_Management_Module SHALL clear all Cache entries related to categories and the Public_Portal homepage
3. WHEN any Accreditation is modified, THE Admin_Management_Module SHALL clear all Cache entries related to accreditations and journal filters
4. THE Admin_Management_Module SHALL clear Cache within 100ms of the database update completing
5. WHEN Cache clearing fails, THE Admin_Management_Module SHALL log the failure but SHALL NOT block the primary operation

### Requirement 19: Backward Compatibility

**User Story:** As a system administrator, I want the new admin module to work with existing data, so that I don't need to migrate or recreate content.

#### Acceptance Criteria

1. WHEN the Admin_Management_Module is deployed, THE Admin_Management_Module SHALL read existing categories from the categories table without requiring migration
2. WHEN the Admin_Management_Module is deployed, THE Admin_Management_Module SHALL read existing accreditations from the accreditations table without requiring migration
3. THE Admin_Management_Module SHALL maintain compatibility with existing Public_Portal queries and views
4. WHEN existing data lacks required fields (icon, color, sort_order), THE Admin_Management_Module SHALL provide sensible defaults
5. THE Admin_Management_Module SHALL not break any existing journal associations or filtering functionality

### Requirement 20: Performance Requirements

**User Story:** As a Super Admin, I want the admin interface to be responsive, so that I can work efficiently without waiting for slow operations.

#### Acceptance Criteria

1. WHEN a Super_Admin loads any admin page, THE Admin_Management_Module SHALL render the page within 1 second
2. WHEN a Super_Admin saves a setting, THE Admin_Management_Module SHALL complete the operation within 500ms
3. WHEN a Super_Admin performs Drag_and_Drop_Reordering, THE Admin_Management_Module SHALL update the Sort_Order within 200ms
4. WHEN a Super_Admin searches in the Icon_Picker, THE Admin_Management_Module SHALL filter results within 100ms
5. THE Admin_Management_Module SHALL use database indexes on frequently queried fields (slug, sort_order, is_active) to ensure query performance under 50ms

### Requirement 21: User Interface Responsiveness

**User Story:** As a Super Admin, I want the admin interface to work on different screen sizes, so that I can manage settings from my laptop or tablet.

#### Acceptance Criteria

1. WHEN a Super_Admin accesses the admin interface on a screen width below 768px, THE Admin_Management_Module SHALL display a mobile-optimized layout
2. WHEN a Super_Admin accesses the admin interface on a screen width above 768px, THE Admin_Management_Module SHALL display a desktop-optimized layout
3. THE Admin_Management_Module SHALL ensure all interactive elements (buttons, form fields, drag handles) are at least 44x44 pixels for touch accessibility
4. WHEN the screen width changes, THE Admin_Management_Module SHALL adapt the layout without requiring a page refresh
5. THE Admin_Management_Module SHALL ensure all text is readable at minimum font size of 14px on mobile devices

### Requirement 22: Error Handling and User Feedback

**User Story:** As a Super Admin, I want clear error messages and success confirmations, so that I know whether my actions succeeded or failed.

#### Acceptance Criteria

1. WHEN an operation succeeds, THE Admin_Management_Module SHALL display a success message for 3 seconds
2. WHEN an operation fails, THE Admin_Management_Module SHALL display an error message explaining what went wrong and how to fix it
3. WHEN a validation error occurs, THE Admin_Management_Module SHALL highlight the invalid field and display the validation message next to it
4. WHEN a network error occurs, THE Admin_Management_Module SHALL display a message indicating the connection issue and offer a retry option
5. WHEN a Concurrent_Edit conflict occurs, THE Admin_Management_Module SHALL display both the user's changes and the conflicting changes side-by-side

### Requirement 23: Data Export and Backup

**User Story:** As a system administrator, I want to export settings and categories, so that I can back them up or migrate them to another environment.

#### Acceptance Criteria

1. WHEN a Super_Admin clicks the export button, THE Admin_Management_Module SHALL generate a JSON file containing all settings, categories, and accreditations
2. THE Admin_Management_Module SHALL include metadata in the export file (export_date, exported_by, version)
3. WHEN a Super_Admin imports a JSON file, THE Admin_Management_Module SHALL validate the file format before processing
4. WHEN an import is performed, THE Admin_Management_Module SHALL prompt the user to choose between merge (keep existing) or replace (overwrite existing)
5. WHEN an import completes, THE Admin_Management_Module SHALL display a summary showing how many items were created, updated, or skipped

### Requirement 24: Search and Filtering

**User Story:** As a Super Admin, I want to search and filter settings, categories, and accreditations, so that I can quickly find what I need to edit.

#### Acceptance Criteria

1. WHEN a Super_Admin types in the search box, THE Admin_Management_Module SHALL filter the displayed items by name, description, or key within 200ms
2. WHEN a Super_Admin selects a filter option (active/inactive), THE Admin_Management_Module SHALL show only items matching that status
3. WHEN a Super_Admin clears the search, THE Admin_Management_Module SHALL restore the full list of items
4. THE Admin_Management_Module SHALL highlight the search term in the filtered results
5. WHEN no items match the search criteria, THE Admin_Management_Module SHALL display a message "No results found" with a button to clear the search

### Requirement 25: Stats Base Offset Management

**User Story:** As a Super Admin, I want to set base offsets for statistics, so that displayed numbers reflect marketing goals rather than actual counts.

#### Acceptance Criteria

1. THE System_Settings_Interface SHALL provide input fields for stats_journals_offset, stats_articles_offset, stats_authors_offset, and stats_downloads_offset
2. WHEN a Super_Admin enters an offset value, THE System_Settings_Interface SHALL validate it is a non-negative integer
3. WHEN an offset is saved, THE System_Settings_Interface SHALL clear the Cache to ensure the Public_Portal displays updated statistics
4. THE System_Settings_Interface SHALL display a preview showing "Actual: X, Displayed: X + offset"
5. WHEN an offset is set to 0, THE System_Settings_Interface SHALL display actual statistics without modification
