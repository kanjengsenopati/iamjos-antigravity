# Implementation Plan: Production Database Cleanup

## Overview

This plan implements a Laravel database migration that removes all demo/seeded data from production while preserving system infrastructure. The migration handles complex foreign key relationships across 30+ tables, executes atomically within a transaction, and provides comprehensive logging.

## Tasks

- [ ] 1. Create migration file structure
  - Create migration file: `database/migrations/YYYY_MM_DD_HHMMSS_cleanup_production_demo_data.php`
  - Set up class structure with `up()` and `down()` methods
  - Add configuration validation for `SUPER_ADMIN_EMAIL`
  - Add transaction wrapper in `up()` method
  - _Requirements: 9.4, 9.5, 14.1_

- [ ] 2. Implement identification methods
  - [ ] 2.1 Implement `identifyDemoJournals()` method
    - Query journals table for slugs: ['jit', 'medika', 'jbe', 'eas', 'iamjos']
    - Return collection of journal IDs
    - _Requirements: 1.5_
  
  - [ ] 2.2 Implement `identifyDemoUsers()` method
    - Query users table for emails matching '%@demo.iamjos.id'
    - Exclude super admin email from results
    - Return collection of user IDs
    - _Requirements: 4.2, 4.3_
  
  - [ ] 2.3 Add submission ID collection logic
    - Query submissions table for journal_id in demo journal IDs
    - Store submission IDs for use in deletion phases
    - _Requirements: 2.1_

- [ ] 3. Implement Phase 1: Delete metrics and logs
  - [ ] 3.1 Implement `deleteMetricsAndLogs()` method
    - Delete article_metrics by submission_id
    - Delete submission_log_files by submission_log_id
    - Delete submission_logs by submission_id
    - Delete submission_notes by submission_id
    - Delete crossref_logs by journal_id
    - Return deletion counts array
    - _Requirements: 6.1, 6.2, 6.3, 6.4, 6.5_
  
  - [ ]* 3.2 Write unit tests for metrics and logs deletion
    - Test article_metrics deletion
    - Test submission_logs cascade deletion
    - Test crossref_logs deletion
    - _Requirements: 6.1, 6.2, 6.3, 6.4, 6.5_

- [ ] 4. Implement Phase 2: Delete workflow data
  - [ ] 4.1 Implement `deleteWorkflowData()` method
    - Get discussion IDs from submissions
    - Delete discussion_files by discussion_id
    - Delete discussion_messages by discussion_id
    - Delete discussion_participants by discussion_id
    - Delete discussions by submission_id
    - Delete review_assignments by submission_id
    - Delete review_rounds by submission_id
    - Delete editorial_assignments by submission_id
    - Return deletion counts array
    - _Requirements: 3.1, 3.2, 3.3, 3.4, 3.5, 3.6, 3.7_
  
  - [ ]* 4.2 Write unit tests for workflow data deletion
    - Test discussion cascade deletion
    - Test review_assignments deletion
    - Test editorial_assignments deletion
    - _Requirements: 3.1, 3.2, 3.3, 3.4, 3.5, 3.6, 3.7_

- [ ] 5. Implement Phase 3: Delete publication data
  - [ ] 5.1 Implement `deletePublicationData()` method
    - Get publication IDs from submissions
    - Delete publication_galleys by publication_id
    - Delete submission_authors by publication_id
    - Delete publications by submission_id
    - Return deletion counts array
    - _Requirements: 2.2, 2.3, 2.5_
  
  - [ ]* 5.2 Write unit tests for publication data deletion
    - Test publication_galleys deletion
    - Test submission_authors deletion
    - Test publications deletion
    - _Requirements: 2.2, 2.3, 2.5_

- [ ] 6. Implement Phase 4: Delete submission data
  - [ ] 6.1 Implement `deleteSubmissions()` method
    - Delete submission_keyword pivot records by submission_id
    - Delete submission_files by submission_id
    - Delete remaining submission_authors by submission_id (where publication_id is null)
    - Delete submissions by id
    - Return deletion counts array
    - _Requirements: 2.1, 2.4, 2.5, 2.6_
  
  - [ ]* 6.2 Write unit tests for submission data deletion
    - Test submission_keyword pivot deletion
    - Test submission_files deletion
    - Test submissions deletion
    - _Requirements: 2.1, 2.4, 2.5, 2.6_

- [ ] 7. Implement Phase 5: Delete journal content
  - [ ] 7.1 Implement `deleteJournalContent()` method
    - Get navigation_menu IDs for demo journals
    - Delete navigation_items by navigation_menu_id
    - Delete navigation_menus by journal_id
    - Delete sidebar_blocks by journal_id
    - Delete announcements by journal_id
    - Delete notification_templates by journal_id (where not null)
    - Delete sections by journal_id
    - Delete issues by journal_id
    - Delete journal_settings by journal_id
    - Return deletion counts array
    - _Requirements: 1.2, 1.3, 1.4, 5.1, 5.2, 5.3, 5.4, 5.5_
  
  - [ ]* 7.2 Write unit tests for journal content deletion
    - Test navigation cascade deletion
    - Test sections deletion
    - Test issues deletion
    - Test journal_settings deletion
    - _Requirements: 1.2, 1.3, 1.4, 5.1, 5.2, 5.3, 5.4, 5.5_

- [ ] 8. Implement Phase 6: Delete journals
  - [ ] 8.1 Implement `deleteJournals()` method
    - Delete journals by id
    - Return deletion count
    - _Requirements: 1.1_
  
  - [ ]* 8.2 Write unit tests for journals deletion
    - Test journals deletion
    - Verify all demo journals removed
    - _Requirements: 1.1_

- [ ] 9. Implement Phase 7: Delete demo users
  - [ ] 9.1 Implement `deleteDemoUsers()` method
    - Delete journal_user_roles by user_id
    - Delete model_has_roles by model_id (Spatie permissions)
    - Delete users by id
    - Return deletion count
    - _Requirements: 4.1, 4.4, 4.5_
  
  - [ ]* 9.2 Write unit tests for demo users deletion
    - Test user role cascade deletion
    - Test users deletion
    - Verify super admin preserved
    - _Requirements: 4.1, 4.4, 4.5_

- [ ] 10. Implement logging functionality
  - [ ] 10.1 Implement `logCleanupSummary()` method
    - Calculate total records deleted
    - Log to Laravel log with structured data
    - Output formatted console summary with emojis
    - Display super admin preservation confirmation
    - _Requirements: 15.1, 15.2, 15.3, 15.4, 15.5, 15.6_
  
  - [ ] 10.2 Add phase-level logging
    - Log identification phase results
    - Log each deletion phase completion
    - Log any warnings (e.g., no demo data found)
    - _Requirements: 15.1, 15.2, 15.3, 15.4, 15.5_
  
  - [ ]* 10.3 Write unit tests for logging
    - Test log output format
    - Test console output format
    - Test deletion counts accuracy
    - _Requirements: 15.1, 15.2, 15.3, 15.4, 15.5, 15.6_

- [ ] 11. Implement rollback method
  - [ ] 11.1 Implement `down()` method
    - Display rollback warning message
    - Show instructions for manual restoration via DemoSeeder
    - Log rollback request
    - Do NOT attempt to restore data
    - _Requirements: 14.1, 14.2, 14.3, 14.4, 14.5_
  
  - [ ]* 11.2 Write unit tests for rollback
    - Test down() method displays warning
    - Test down() method is idempotent
    - Test no database changes occur
    - _Requirements: 14.1, 14.2, 14.3, 14.4, 14.5_

- [ ] 12. Implement error handling
  - [ ] 12.1 Add configuration validation
    - Check SUPER_ADMIN_EMAIL is configured
    - Throw descriptive exception if missing
    - Add validation before transaction starts
    - _Requirements: 4.3_
  
  - [ ] 12.2 Add transaction error handling
    - Wrap all operations in try-catch
    - Log errors with full context
    - Re-throw exceptions to mark migration as failed
    - _Requirements: 9.4, 9.5_
  
  - [ ] 12.3 Handle empty demo data scenario
    - Check if journal and user IDs are empty
    - Log info message and return early
    - Ensure migration completes successfully
    - _Requirements: 15.6_
  
  - [ ]* 12.4 Write unit tests for error handling
    - Test missing SUPER_ADMIN_EMAIL throws exception
    - Test transaction rollback on failure
    - Test empty demo data handling
    - _Requirements: 9.4, 9.5_

- [ ] 13. Add DemoSeeder production guard
  - [ ] 13.1 Implement production environment check in DemoSeeder
    - Check app()->isProduction() before seeding
    - Display error message if production
    - Return early without seeding
    - _Requirements: 10.1, 10.2, 10.3, 10.4, 10.5_
  
  - [ ]* 13.2 Write unit tests for DemoSeeder guard
    - Test seeder refuses execution in production
    - Test seeder runs normally in local/staging
    - Test no journals created when refused
    - _Requirements: 10.1, 10.2, 10.3, 10.4, 10.5_

- [ ] 14. Checkpoint - Ensure all tests pass
  - Ensure all tests pass, ask the user if questions arise.

- [ ] 15. Create property-based tests
  - [ ]* 15.1 Write property test for Fresh OJS State
    - **Property 1: Fresh OJS State Verification**
    - **Validates: Requirements 1.1, 1.2, 1.3, 1.4, 2.1, 2.2, 2.3, 2.4, 2.5, 2.6, 3.1-3.7, 4.1, 5.1-5.5, 6.1-6.5, 13.1, 13.2, 13.3**
    - Generate random demo data configurations
    - Run migration
    - Verify zero demo journals, users, submissions
    - Run 100 iterations
  
  - [ ]* 15.2 Write property test for System Infrastructure Preservation
    - **Property 2: System Infrastructure Preservation**
    - **Validates: Requirements 7.1-7.10, 8.1-8.3, 13.5**
    - Capture infrastructure counts before migration
    - Run migration
    - Verify all infrastructure counts unchanged
    - Run 100 iterations
  
  - [ ]* 15.3 Write property test for Transaction Atomicity
    - **Property 3: Transaction Atomicity**
    - **Validates: Requirements 9.4, 9.5**
    - Simulate random failure points
    - Verify complete rollback
    - Run 100 iterations
  
  - [ ]* 15.4 Write property test for Super Admin Preservation
    - **Property 4: Super Admin Preservation**
    - **Validates: Requirements 4.3, 7.10, 13.4**
    - Test with different super admin configurations
    - Verify super admin always preserved with roles
    - Run 100 iterations
  
  - [ ]* 15.5 Write property test for No Schema Modification
    - **Property 5: No Schema Modification**
    - **Validates: Requirements 11.1-11.7, 12.1-12.6**
    - Capture schema, indexes, constraints before migration
    - Run migration
    - Verify schema unchanged
    - Run 100 iterations

- [ ] 16. Create integration test
  - [ ]* 16.1 Write full integration test
    - Seed complete demo dataset
    - Run migration
    - Verify all demo data removed
    - Verify infrastructure preserved
    - Verify super admin preserved
    - Test creating new journal after cleanup
    - _Requirements: All requirements_

- [ ] 17. Create documentation
  - [ ] 17.1 Add inline code comments
    - Document each private method's purpose
    - Document deletion order rationale
    - Document foreign key constraint handling
    - _Requirements: 9.1, 9.2, 9.3_
  
  - [ ] 17.2 Create migration execution guide
    - Document pre-execution checklist
    - Document execution commands
    - Document post-execution verification steps
    - Document rollback procedure
    - _Requirements: 14.1, 14.2, 14.3_

- [ ] 18. Final checkpoint - Verify complete implementation
  - Ensure all tests pass, ask the user if questions arise.

## Notes

- Tasks marked with `*` are optional and can be skipped for faster MVP
- Each task references specific requirements for traceability
- Checkpoints ensure incremental validation
- Property tests validate universal correctness properties
- Unit tests validate specific examples and edge cases
- The migration uses PHP/Laravel as the implementation language
- All deletion operations execute within a single database transaction
- Deletion order is critical to respect foreign key constraints
- Super admin preservation is enforced at multiple levels
