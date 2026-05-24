# Requirements Document

## Introduction

The IAMJOS system currently contains 5 demo journals with seeded data visible in production. This feature removes all demo/seeded journals and related data while preserving essential system infrastructure, resulting in a "Fresh OJS" state where administrators can create their first real journal. The cleanup maintains OJS compliance for workflow logic, business rules, and Google Scholar indexing capabilities.

## Glossary

- **Demo_Data**: Journals, users, submissions, issues, and related entities created by DemoSeeder
- **System_Infrastructure**: RBAC matrix, email templates, notification templates, system settings, site content
- **Cleanup_Migration**: Database migration that removes demo data with proper foreign key handling
- **Super_Admin**: Administrator account defined in .env (SUPER_ADMIN_EMAIL)
- **Fresh_OJS_State**: Clean installation state with zero journals, ready for first real journal creation
- **OJS_Compliance**: Adherence to Open Journal Systems workflow logic, business rules, and database structure
- **Google_Scholar_Compliance**: Preservation of meta tag generation, structured data, and indexing capabilities
- **Demo_Journal**: Journal entity created by DemoSeeder (5 journals: JIT, MSJ, JBE, EAS, IAMJOS)
- **Demo_User**: User account created by DemoSeeder (admin@demo, editor@demo, reviewer@demo, author@demo)
- **Foreign_Key_Cascade**: Database constraint handling for dependent record deletion

## Requirements

### Requirement 1: Remove Demo Journals

**User Story:** As a system administrator, I want to remove all demo journals from production, so that the system presents a clean slate for real journal creation.

#### Acceptance Criteria

1. THE Cleanup_Migration SHALL delete all Demo_Journal records from the journals table
2. THE Cleanup_Migration SHALL delete all sections associated with Demo_Journal records
3. THE Cleanup_Migration SHALL delete all issues associated with Demo_Journal records
4. THE Cleanup_Migration SHALL delete all journal_settings associated with Demo_Journal records
5. THE Cleanup_Migration SHALL identify Demo_Journal records by matching slug values ('jit', 'medika', 'jbe', 'eas', 'iamjos')

### Requirement 2: Remove Demo Submissions and Publications

**User Story:** As a system administrator, I want to remove all submissions and publications linked to demo journals, so that no demo content appears in the system.

#### Acceptance Criteria

1. THE Cleanup_Migration SHALL delete all submissions associated with Demo_Journal records
2. THE Cleanup_Migration SHALL delete all publications associated with Demo_Journal submissions
3. THE Cleanup_Migration SHALL delete all publication_galleys associated with Demo_Journal publications
4. THE Cleanup_Migration SHALL delete all submission_files associated with Demo_Journal submissions
5. THE Cleanup_Migration SHALL delete all submission_authors associated with Demo_Journal submissions
6. THE Cleanup_Migration SHALL delete all keywords associated with Demo_Journal submissions via submission_keyword pivot table

### Requirement 3: Remove Demo Workflow Data

**User Story:** As a system administrator, I want to remove all workflow-related data for demo journals, so that no demo review or editorial data remains.

#### Acceptance Criteria

1. THE Cleanup_Migration SHALL delete all review_assignments associated with Demo_Journal submissions
2. THE Cleanup_Migration SHALL delete all review_rounds associated with Demo_Journal submissions
3. THE Cleanup_Migration SHALL delete all editorial_assignments associated with Demo_Journal records
4. THE Cleanup_Migration SHALL delete all discussions associated with Demo_Journal submissions
5. THE Cleanup_Migration SHALL delete all discussion_messages associated with Demo_Journal discussions
6. THE Cleanup_Migration SHALL delete all discussion_files associated with Demo_Journal discussions
7. THE Cleanup_Migration SHALL delete all discussion_participants associated with Demo_Journal discussions

### Requirement 4: Remove Demo Users

**User Story:** As a system administrator, I want to remove demo user accounts, so that only real users exist in the production system.

#### Acceptance Criteria

1. THE Cleanup_Migration SHALL delete all Demo_User records from the users table
2. THE Cleanup_Migration SHALL identify Demo_User records by email pattern matching '@demo.iamjos.id'
3. THE Cleanup_Migration SHALL preserve the Super_Admin user account
4. THE Cleanup_Migration SHALL delete all role assignments for Demo_User records via model_has_roles table
5. THE Cleanup_Migration SHALL delete all journal_user_roles assignments for Demo_User records

### Requirement 5: Remove Demo Journal-Specific Content

**User Story:** As a system administrator, I want to remove journal-specific content for demo journals, so that no demo navigation or announcements remain.

#### Acceptance Criteria

1. THE Cleanup_Migration SHALL delete all navigation_menus where journal_id matches Demo_Journal records
2. THE Cleanup_Migration SHALL delete all navigation_items associated with Demo_Journal navigation menus
3. THE Cleanup_Migration SHALL delete all announcements where journal_id matches Demo_Journal records
4. THE Cleanup_Migration SHALL delete all sidebar_blocks where journal_id matches Demo_Journal records
5. THE Cleanup_Migration SHALL delete all notification_templates where journal_id matches Demo_Journal records

### Requirement 6: Remove Demo Metrics and Logs

**User Story:** As a system administrator, I want to remove metrics and logs for demo journals, so that analytics start fresh for real journals.

#### Acceptance Criteria

1. THE Cleanup_Migration SHALL delete all article_metrics records associated with Demo_Journal publications
2. THE Cleanup_Migration SHALL delete all submission_logs associated with Demo_Journal submissions
3. THE Cleanup_Migration SHALL delete all submission_log_files associated with Demo_Journal submission logs
4. THE Cleanup_Migration SHALL delete all submission_notes associated with Demo_Journal submissions
5. THE Cleanup_Migration SHALL delete all crossref_logs where journal_id matches Demo_Journal records

### Requirement 7: Preserve System Infrastructure

**User Story:** As a system administrator, I want essential system infrastructure preserved, so that the system remains functional after cleanup.

#### Acceptance Criteria

1. THE Cleanup_Migration SHALL preserve all records in the roles table
2. THE Cleanup_Migration SHALL preserve all records in the permissions table
3. THE Cleanup_Migration SHALL preserve all records in the role_has_permissions table
4. THE Cleanup_Migration SHALL preserve all email_templates records
5. THE Cleanup_Migration SHALL preserve all notification_templates records where journal_id is NULL
6. THE Cleanup_Migration SHALL preserve all site_settings records
7. THE Cleanup_Migration SHALL preserve all site_contents records
8. THE Cleanup_Migration SHALL preserve all site_content_blocks records
9. THE Cleanup_Migration SHALL preserve all site_pages records
10. THE Cleanup_Migration SHALL preserve the Super_Admin user record

### Requirement 8: Preserve Site-Level Navigation

**User Story:** As a system administrator, I want site-level navigation preserved, so that the portal homepage remains functional.

#### Acceptance Criteria

1. THE Cleanup_Migration SHALL preserve all navigation_menus records where journal_id is NULL
2. THE Cleanup_Migration SHALL preserve all navigation_items associated with site-level navigation menus
3. THE Cleanup_Migration SHALL preserve all sidebar_blocks records where journal_id is NULL

### Requirement 9: Handle Foreign Key Constraints

**User Story:** As a system administrator, I want the cleanup to handle foreign key constraints properly, so that the migration executes without database errors.

#### Acceptance Criteria

1. THE Cleanup_Migration SHALL delete child records before parent records to respect Foreign_Key_Cascade constraints
2. THE Cleanup_Migration SHALL delete records in this order: metrics/logs, workflow data, publications, submissions, journal content, journals, users
3. WHEN a foreign key constraint would be violated, THE Cleanup_Migration SHALL delete the dependent record first
4. THE Cleanup_Migration SHALL use database transactions to ensure atomic execution
5. IF any deletion fails, THEN THE Cleanup_Migration SHALL roll back all changes

### Requirement 10: Prevent Future Demo Data in Production

**User Story:** As a system administrator, I want DemoSeeder to refuse execution in production, so that demo data cannot be accidentally created.

#### Acceptance Criteria

1. THE DemoSeeder SHALL check the application environment before execution
2. WHEN the environment is production, THE DemoSeeder SHALL refuse to execute
3. WHEN the environment is production, THE DemoSeeder SHALL display an error message
4. WHEN the environment is local or staging, THE DemoSeeder SHALL execute normally
5. THE DemoSeeder SHALL use app()->isProduction() to determine the environment

### Requirement 11: Maintain OJS Compliance

**User Story:** As a system administrator, I want OJS workflow logic preserved, so that the system remains compliant with OJS standards.

#### Acceptance Criteria

1. THE Cleanup_Migration SHALL preserve all database table structures
2. THE Cleanup_Migration SHALL preserve all database indexes
3. THE Cleanup_Migration SHALL preserve all database foreign key constraints
4. THE Cleanup_Migration SHALL preserve all application code for submission workflow
5. THE Cleanup_Migration SHALL preserve all application code for review workflow
6. THE Cleanup_Migration SHALL preserve all application code for editorial workflow
7. THE Cleanup_Migration SHALL preserve all application code for publication workflow

### Requirement 12: Maintain Google Scholar Compliance

**User Story:** As a system administrator, I want Google Scholar indexing capabilities preserved, so that future published articles can be indexed.

#### Acceptance Criteria

1. THE Cleanup_Migration SHALL preserve all application code for meta tag generation
2. THE Cleanup_Migration SHALL preserve all application code for Highwire Press tags
3. THE Cleanup_Migration SHALL preserve all application code for Dublin Core metadata
4. THE Cleanup_Migration SHALL preserve all application code for citation metadata
5. THE Cleanup_Migration SHALL preserve all application code for OAI-PMH endpoints
6. THE Cleanup_Migration SHALL preserve all application code for JSON-LD structured data

### Requirement 13: Verify Fresh OJS State

**User Story:** As a system administrator, I want to verify the system is in Fresh_OJS_State after cleanup, so that I can confirm successful execution.

#### Acceptance Criteria

1. WHEN the Cleanup_Migration completes, THE System SHALL have zero journals in the journals table
2. WHEN the Cleanup_Migration completes, THE System SHALL have zero submissions in the submissions table
3. WHEN the Cleanup_Migration completes, THE System SHALL have zero Demo_User records in the users table
4. WHEN the Cleanup_Migration completes, THE System SHALL have the Super_Admin user in the users table
5. WHEN the Cleanup_Migration completes, THE System SHALL have all System_Infrastructure records intact

### Requirement 14: Provide Rollback Capability

**User Story:** As a system administrator, I want the ability to rollback the cleanup migration, so that I can restore demo data if needed for testing.

#### Acceptance Criteria

1. THE Cleanup_Migration SHALL implement a down() method for rollback
2. WHEN the migration is rolled back, THE Cleanup_Migration SHALL display a warning message
3. WHEN the migration is rolled back, THE Cleanup_Migration SHALL recommend running DemoSeeder
4. THE Cleanup_Migration SHALL not attempt to restore deleted data in the down() method
5. THE down() method SHALL be idempotent and safe to execute multiple times

### Requirement 15: Log Cleanup Operations

**User Story:** As a system administrator, I want cleanup operations logged, so that I can audit what was removed.

#### Acceptance Criteria

1. THE Cleanup_Migration SHALL log the count of Demo_Journal records deleted
2. THE Cleanup_Migration SHALL log the count of Demo_User records deleted
3. THE Cleanup_Migration SHALL log the count of submissions deleted
4. THE Cleanup_Migration SHALL log the count of publications deleted
5. THE Cleanup_Migration SHALL log the count of workflow records deleted
6. WHEN the migration completes successfully, THE Cleanup_Migration SHALL display a success summary
7. IF the migration fails, THEN THE Cleanup_Migration SHALL display an error message with details
