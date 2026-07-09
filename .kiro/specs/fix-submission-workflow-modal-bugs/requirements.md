# Requirements Document

## Introduction

This document specifies the requirements for fixing modal display bugs in the submission workflow page. Currently, several "+ Assign" buttons in the PARTICIPANTS sidebar sections incorrectly open the Editor Assignment modal instead of opening an appropriate Participant Assignment modal for their respective workflow stages (Submission, Review, Copyediting, Production).

## Glossary

- **Submission_Workflow_Page**: The main page (`resources/views/submissions/show.blade.php`) displaying the editorial workflow for a manuscript submission
- **Editor_Assignment_Modal**: Modal for assigning journal editors or section editors to oversee the submission
- **Participant_Assignment_Modal**: Modal for assigning stage-specific participants (reviewers, copyeditors, layout editors, proofreaders) to workflow stages
- **Alpine_Component**: The `submissionWorkflow` Alpine.js component managing modal state and interactions
- **PARTICIPANTS_Section**: The sidebar section displaying assigned participants for each workflow stage
- **Workflow_Stage**: A phase in the editorial process (Submission, Review, Copyediting, Production)

## Requirements

### Requirement 1: Identify Correct Modal Behavior

**User Story:** As a journal editor, I want the "+ Assign" button in PARTICIPANTS sections to open the appropriate assignment modal for each workflow stage, so that I can assign the correct type of participant to each stage.

#### Acceptance Criteria

1. WHEN a user clicks "+ Assign" in the Submission stage PARTICIPANTS section, THE System SHALL open the Editor_Assignment_Modal
2. WHEN a user clicks "+ Assign" in the Review stage PARTICIPANTS section, THE System SHALL open the Participant_Assignment_Modal for reviewer assignment
3. WHEN a user clicks "+ Assign" in the Copyediting stage PARTICIPANTS section, THE System SHALL open the Participant_Assignment_Modal for copyeditor assignment
4. WHEN a user clicks "+ Assign" in the Production stage PARTICIPANTS section, THE System SHALL open the Participant_Assignment_Modal for production role assignment
5. THE System SHALL distinguish between editor assignment (workflow oversight) and participant assignment (stage-specific tasks)

### Requirement 2: Create Participant Assignment Modal

**User Story:** As a journal editor, I want a dedicated modal for assigning stage-specific participants, so that I can assign reviewers, copyeditors, and production staff to their respective workflow stages.

#### Acceptance Criteria

1. THE System SHALL provide a Participant_Assignment_Modal component
2. THE Participant_Assignment_Modal SHALL display available users filtered by stage-appropriate roles
3. THE Participant_Assignment_Modal SHALL include search functionality for finding users by name or email
4. THE Participant_Assignment_Modal SHALL include role filter dropdown for stage-appropriate roles
5. THE Participant_Assignment_Modal SHALL display user information including name, email, and role badges
6. THE Participant_Assignment_Modal SHALL allow selection of a single user for assignment
7. THE Participant_Assignment_Modal SHALL include cancel and assign action buttons
8. THE Participant_Assignment_Modal SHALL disable the assign button when no user is selected
9. THE Participant_Assignment_Modal SHALL integrate with existing participant assignment backend routes

### Requirement 3: Implement Alpine.js Modal Functions

**User Story:** As a developer, I want properly named modal control functions in the Alpine.js component, so that each button calls the correct modal function.

#### Acceptance Criteria

1. THE Alpine_Component SHALL provide an `openParticipantModal(stage)` function
2. THE `openParticipantModal` function SHALL accept a stage parameter indicating the workflow stage
3. THE `openParticipantModal` function SHALL set modal state to open the Participant_Assignment_Modal
4. THE `openParticipantModal` function SHALL store the stage context for backend submission
5. THE Alpine_Component SHALL maintain the existing `openAssignEditorModal()` function unchanged
6. THE Alpine_Component SHALL manage `participantModalOpen` state variable
7. THE Alpine_Component SHALL manage `participantModalStage` state variable for tracking current stage

### Requirement 4: Update Button Click Handlers

**User Story:** As a journal editor, I want each "+ Assign" button to call the correct modal function, so that the appropriate assignment modal opens when I click it.

#### Acceptance Criteria

1. WHEN the Submission stage "+ Assign" button is rendered, THE System SHALL bind it to `openAssignEditorModal()`
2. WHEN the Review stage "+ Assign" button is rendered, THE System SHALL bind it to `openParticipantModal('review')`
3. WHEN the Copyediting stage "+ Assign" button is rendered, THE System SHALL bind it to `openParticipantModal('copyediting')`
4. WHEN the Production stage "+ Assign" button is rendered, THE System SHALL bind it to `openParticipantModal('production')`
5. THE System SHALL preserve all existing working button handlers unchanged

### Requirement 5: Backend Integration

**User Story:** As a developer, I want the Participant Assignment modal to submit to the correct backend route, so that participant assignments are properly persisted.

#### Acceptance Criteria

1. WHEN submitting from Review stage, THE Participant_Assignment_Modal SHALL POST to the reviewer assignment route
2. WHEN submitting from Copyediting stage, THE Participant_Assignment_Modal SHALL POST to the copyeditor assignment route
3. WHEN submitting from Production stage, THE Participant_Assignment_Modal SHALL POST to the production participant assignment route
4. THE Participant_Assignment_Modal SHALL include CSRF token in form submission
5. THE Participant_Assignment_Modal SHALL include selected user ID in form submission
6. THE Participant_Assignment_Modal SHALL include stage context in form submission
7. IF a backend route does not exist for a stage, THE System SHALL create placeholder route handlers

### Requirement 6: Preserve Existing Functionality

**User Story:** As a journal editor, I want all currently working modals to continue functioning correctly, so that fixing the bug does not break existing features.

#### Acceptance Criteria

1. THE Editor_Assignment_Modal SHALL continue to open from the "Assign Editor" button in the warning banner
2. THE file upload modal SHALL continue to open from "+ Upload File" buttons
3. THE draft files modal SHALL continue to open from "Upload/Select Files" button
4. THE copyedited file upload modal SHALL continue to open from "Upload Copyedited File" button
5. THE System SHALL maintain all existing Alpine.js state variables and functions
6. THE System SHALL maintain backward compatibility with existing editor assignment workflow

### Requirement 7: Visual Consistency

**User Story:** As a journal editor, I want the new Participant Assignment modal to match the visual design of existing modals, so that the interface feels consistent and professional.

#### Acceptance Criteria

1. THE Participant_Assignment_Modal SHALL use the same styling classes as Editor_Assignment_Modal
2. THE Participant_Assignment_Modal SHALL use the same animation transitions as Editor_Assignment_Modal
3. THE Participant_Assignment_Modal SHALL use the same color scheme as Editor_Assignment_Modal
4. THE Participant_Assignment_Modal SHALL maintain responsive design for mobile and desktop viewports
5. THE Participant_Assignment_Modal SHALL include appropriate icon for modal header based on stage type
6. THE Participant_Assignment_Modal SHALL support bilingual display (English and Indonesian)

### Requirement 8: Error Handling

**User Story:** As a journal editor, I want clear error messages when participant assignment fails, so that I can understand and resolve issues.

#### Acceptance Criteria

1. IF no users are available for assignment, THE Participant_Assignment_Modal SHALL display an empty state message
2. IF search returns no results, THE Participant_Assignment_Modal SHALL display a no results message
3. IF form submission fails, THE System SHALL display validation errors
4. IF a user is already assigned to the stage, THE System SHALL prevent duplicate assignment and display an error message
5. THE System SHALL log assignment errors for debugging purposes
