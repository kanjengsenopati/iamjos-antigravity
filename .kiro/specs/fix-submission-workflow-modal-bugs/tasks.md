# Implementation Plan: Fix Submission Workflow Modal Bugs

## Overview

This implementation plan addresses the modal display bugs where "+ Assign" buttons in PARTICIPANTS sections incorrectly open the Editor Assignment modal instead of stage-appropriate Participant Assignment modals. The fix involves creating a new reusable modal component, adding Alpine.js functions, updating button handlers, and ensuring backend integration for each workflow stage.

## Tasks

- [x] 1. Create Participant Assignment Modal Component
  - Create `resources/views/submissions/partials/modal-assign-participant.blade.php`
  - Base structure on existing `modal-assign-editor.blade.php` for visual consistency
  - Make modal dynamic to accept stage parameter ('review', 'copyediting', 'production')
  - Implement stage-specific configuration (titles, icons, role filters)
  - Add search input for filtering participants by name or email
  - Add role filter dropdown with stage-appropriate roles
  - Implement participant list with selection functionality
  - Add footer with Cancel and Assign action buttons
  - Ensure bilingual support (English/Indonesian)
  - _Requirements: 2.1, 2.2, 2.3, 2.4, 2.5, 2.6, 2.7, 2.8, 7.1, 7.2, 7.3, 7.6_

- [x] 2. Update Alpine.js Component with Modal State and Functions
  - [x] 2.1 Add new state variables to submissionWorkflow component
    - Add `participantModalOpen: false`
    - Add `participantModalStage: null`
    - Add `participantSearch: ''`
    - Add `participantRoleFilter: ''`
    - Add `allParticipants: config.potentialParticipants || {}`
    - Add `selectedParticipant: null`
    - _Requirements: 3.6, 3.7_
  
  - [x] 2.2 Implement openParticipantModal(stage) function
    - Accept stage parameter ('review', 'copyediting', 'production')
    - Set `participantModalStage` to provided stage
    - Call `resetParticipantModal()`
    - Set `participantModalOpen` to true
    - _Requirements: 3.1, 3.2, 3.3, 3.4_
  
  - [x] 2.3 Implement resetParticipantModal() function
    - Reset `selectedParticipant` to null
    - Reset `participantSearch` to empty string
    - Set `participantRoleFilter` to default for current stage
    - _Requirements: 3.3_
  
  - [x] 2.4 Implement helper functions
    - Implement `getDefaultRoleFilter(stage)` to return default role for each stage
    - Implement `selectParticipant(participant)` to set selected participant
    - Implement `getParticipantsForStage(stage)` to return stage-appropriate participants
    - _Requirements: 3.3, 3.4_
  
  - [x] 2.5 Implement filteredParticipants computed property
    - Get participants for current stage
    - Convert object to array if needed
    - Apply text search filter (name or email contains search term)
    - Apply role filter
    - Return filtered array
    - _Requirements: 2.2, 2.3_

- [x] 3. Update Button Click Handlers in Blade Template
  - [x] 3.1 Update Review stage "+ Assign" button (line ~1269)
    - Change `@click="openAssignEditorModal()"` to `@click="openParticipantModal('review')"`
    - Verify button styling remains unchanged
    - _Requirements: 1.2, 4.2_
  
  - [x] 3.2 Update Copyediting stage "+ Assign" button (line ~1773)
    - Change `@click="openAssignEditorModal()"` to `@click="openParticipantModal('copyediting')"`
    - Verify button styling remains unchanged
    - _Requirements: 1.3, 4.3_
  
  - [x] 3.3 Update Production stage "+ Assign" button (line ~2193)
    - Change `@click="openAssignEditorModal()"` to `@click="openParticipantModal('production')"`
    - Verify button styling remains unchanged
    - _Requirements: 1.4, 4.4_
  
  - [x] 3.4 Verify Submission stage "+ Assign" button unchanged (line ~499)
    - Confirm button still calls `openAssignEditorModal()`
    - Ensure no accidental modifications
    - _Requirements: 1.1, 4.1, 6.1_

- [x] 4. Include Participant Assignment Modal in Main View
  - Add `@include('submissions.partials.modal-assign-participant')` after existing modals
  - Ensure modal receives necessary data from controller
  - Pass stage configuration array to modal
  - _Requirements: 2.9_

- [x] 5. Update SubmissionWorkflowController to Provide Participant Data
  - [x] 5.1 Load potential reviewers for Review stage
    - Query users with 'Reviewer' role in current journal
    - Exclude already assigned reviewers for current review round
    - Format user data with id, name, email, role_names
    - _Requirements: 2.2, 5.1_
  
  - [x] 5.2 Load potential copyeditors for Copyediting stage
    - Query users with 'Copyeditor' or 'Layout Editor' roles
    - Exclude already assigned copyeditors
    - Format user data consistently
    - _Requirements: 2.2, 5.2_
  
  - [x] 5.3 Load potential production staff for Production stage
    - Query users with 'Layout Editor' or 'Proofreader' roles
    - Exclude already assigned production staff
    - Format user data consistently
    - _Requirements: 2.2, 5.3_
  
  - [x] 5.4 Pass participant data to view
    - Create `potentialParticipants` array with stage keys
    - Pass array to view via compact() or array
    - _Requirements: 2.2_

- [x] 6. Checkpoint - Test Modal Display and Interaction
  - Ensure all tests pass, verify modals open correctly for each stage, ask the user if questions arise.

- [ ]* 7. Create Backend Route and Controller for Copyeditor Assignment
  - [ ]* 7.1 Add route in routes/web.php
    - Add POST route `/{submission}/assign-copyeditor`
    - Map to `SubmissionWorkflowController::assignCopyeditor`
    - _Requirements: 5.2, 5.7_
  
  - [ ]* 7.2 Implement assignCopyeditor method in SubmissionWorkflowController
    - Validate request data (copyeditor_id, submission context)
    - Check for duplicate assignment
    - Create CopyeditingAssignment record
    - Send notification to assigned copyeditor
    - Log the assignment action
    - Return success response with redirect
    - _Requirements: 5.2, 5.4, 5.5, 8.4_

- [ ]* 8. Create Backend Route and Controller for Production Staff Assignment
  - [ ]* 8.1 Add route in routes/web.php
    - Add POST route `/{submission}/assign-production`
    - Map to `SubmissionWorkflowController::assignProduction`
    - _Requirements: 5.3, 5.7_
  
  - [ ]* 8.2 Implement assignProduction method in SubmissionWorkflowController
    - Validate request data (production_user_id, role, submission context)
    - Check for duplicate assignment
    - Create ProductionAssignment record
    - Send notification to assigned production staff
    - Log the assignment action
    - Return success response with redirect
    - _Requirements: 5.3, 5.4, 5.5, 8.4_

- [ ]* 9. Add Duplicate Assignment Prevention
  - [ ]* 9.1 Add duplicate check for reviewer assignment
    - Query existing ReviewAssignment for same submission and review round
    - Exclude cancelled and declined assignments
    - Throw ValidationException if duplicate found
    - _Requirements: 8.4_
  
  - [ ]* 9.2 Add duplicate check for copyeditor assignment
    - Query existing CopyeditingAssignment for same submission
    - Exclude cancelled assignments
    - Throw ValidationException if duplicate found
    - _Requirements: 8.4_
  
  - [ ]* 9.3 Add duplicate check for production assignment
    - Query existing ProductionAssignment for same submission and role
    - Exclude cancelled assignments
    - Throw ValidationException if duplicate found
    - _Requirements: 8.4_

- [ ]* 10. Add Error Handling and Validation
  - [ ]* 10.1 Add validation rules for participant assignment
    - Validate participant_id is required and exists in users table
    - Validate stage parameter is one of: review, copyediting, production
    - Add stage-specific validation rules
    - _Requirements: 5.4, 8.3_
  
  - [ ]* 10.2 Add permission checks
    - Verify user has journal manager or section editor role
    - Return 403 error if unauthorized
    - _Requirements: 5.4_
  
  - [ ]* 10.3 Add error logging
    - Log validation failures
    - Log duplicate assignment attempts
    - Log permission failures
    - _Requirements: 8.5_
  
  - [ ]* 10.4 Display validation errors in modal
    - Add error display section in modal
    - Style errors with red background and icon
    - List all validation errors
    - _Requirements: 8.3_

- [ ]* 11. Write Unit Tests for Backend Controllers
  - [ ]* 11.1 Test reviewer assignment
    - Test successful assignment creates ReviewAssignment
    - Test validation for missing reviewer_id
    - Test duplicate assignment prevention
    - Test permission checks
    - _Requirements: 5.1, 8.4_
  
  - [ ]* 11.2 Test copyeditor assignment
    - Test successful assignment creates CopyeditingAssignment
    - Test validation for missing copyeditor_id
    - Test duplicate assignment prevention
    - Test permission checks
    - _Requirements: 5.2, 8.4_
  
  - [ ]* 11.3 Test production staff assignment
    - Test successful assignment creates ProductionAssignment
    - Test validation for missing user_id
    - Test duplicate assignment prevention
    - Test permission checks
    - _Requirements: 5.3, 8.4_

- [ ]* 12. Write Browser Tests for Modal Interactions
  - [ ]* 12.1 Test modal opens correctly
    - Test clicking Review stage "+ Assign" opens participant modal
    - Test modal displays "Assign Reviewer" title
    - Test clicking Copyediting stage "+ Assign" opens participant modal
    - Test modal displays "Assign Copyeditor" title
    - Test clicking Production stage "+ Assign" opens participant modal
    - Test modal displays "Assign Production Staff" title
    - _Requirements: 1.2, 1.3, 1.4, 2.1_
  
  - [ ]* 12.2 Test search and filter functionality
    - Test search input filters participants by name
    - Test search input filters participants by email
    - Test role filter dropdown filters by role
    - Test combined search and filter
    - _Requirements: 2.3, 2.4_
  
  - [ ]* 12.3 Test participant selection
    - Test clicking participant row selects participant
    - Test selected participant row is highlighted
    - Test "Assign" button is disabled when no participant selected
    - Test "Assign" button is enabled when participant selected
    - _Requirements: 2.6, 2.8_
  
  - [ ]* 12.4 Test form submission
    - Test clicking "Assign" submits form to correct route
    - Test form includes participant_id
    - Test form includes stage parameter
    - Test form includes CSRF token
    - _Requirements: 5.4, 5.5, 5.6_
  
  - [ ]* 12.5 Test modal close behavior
    - Test clicking "Cancel" closes modal
    - Test clicking background overlay closes modal
    - Test clicking X button closes modal
    - _Requirements: 2.7_

- [ ]* 13. Write Integration Tests for End-to-End Workflow
  - [ ]* 13.1 Test reviewer assignment workflow
    - Login as journal manager
    - Navigate to submission in Review stage
    - Click "+ Assign" in PARTICIPANTS section
    - Search for and select reviewer
    - Submit assignment
    - Verify success message displayed
    - Verify reviewer appears in PARTICIPANTS section
    - _Requirements: 1.2, 5.1_
  
  - [ ]* 13.2 Test copyeditor assignment workflow
    - Login as journal manager
    - Navigate to submission in Copyediting stage
    - Click "+ Assign" in PARTICIPANTS section
    - Search for and select copyeditor
    - Submit assignment
    - Verify success message displayed
    - Verify copyeditor appears in PARTICIPANTS section
    - _Requirements: 1.3, 5.2_
  
  - [ ]* 13.3 Test production staff assignment workflow
    - Login as journal manager
    - Navigate to submission in Production stage
    - Click "+ Assign" in PARTICIPANTS section
    - Search for and select production staff
    - Submit assignment
    - Verify success message displayed
    - Verify production staff appears in PARTICIPANTS section
    - _Requirements: 1.4, 5.3_
  
  - [ ]* 13.4 Test duplicate assignment error
    - Attempt to assign same reviewer twice
    - Verify error message displayed
    - Verify assignment not duplicated
    - _Requirements: 8.4_

- [ ]* 14. Write Regression Tests for Existing Modals
  - [ ]* 14.1 Test Editor Assignment modal from Submission stage
    - Click "+ Assign" in Submission stage PARTICIPANTS section
    - Verify Editor Assignment modal opens (not Participant modal)
    - Test search and selection functionality
    - Test successful assignment
    - _Requirements: 6.1_
  
  - [ ]* 14.2 Test Editor Assignment from warning banner
    - Click "Assign Editor" button in warning banner
    - Verify Editor Assignment modal opens
    - Test functionality unchanged
    - _Requirements: 6.1_
  
  - [ ]* 14.3 Test file upload modals
    - Test "+ Upload File" button opens file modal
    - Test "Upload/Select Files" button opens draft files modal
    - Test "Upload Copyedited File" button opens copyedited file modal
    - Verify all modals function correctly
    - _Requirements: 6.2, 6.3, 6.4_
  
  - [ ]* 14.4 Verify no breaking changes
    - Run full test suite
    - Verify all existing tests pass
    - Check for any Alpine.js console errors
    - _Requirements: 6.5, 6.6_

- [x] 15. Final Checkpoint - Complete Testing and Deployment Preparation
  - Ensure all tests pass, verify no regressions, test bilingual support (English/Indonesian), test responsive design on mobile/tablet, clear view cache, ask the user if questions arise.

## Notes

- Tasks marked with `*` are optional and can be skipped for faster MVP (backend routes may already exist, tests can be added later)
- Each task references specific requirements for traceability
- Checkpoints ensure incremental validation at key milestones
- The "+ Assign" button in Submission stage must remain unchanged (opens Editor Assignment modal)
- The "+ Assign" buttons in Review, Copyediting, and Production stages must open Participant Assignment modal
- Modal component must be reusable across all three stages (review, copyediting, production)
- Preserve all existing working modals and functions
- Maintain visual consistency with existing modals
- Support bilingual display (English and Indonesian)
