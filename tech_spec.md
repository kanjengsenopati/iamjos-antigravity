IAMJOS - Project Technical Specification

1. Project Overview

Project Name: IAMJOS (Indonesian Academic Journal System) Type: SaaS / Multi-tenant Academic Journal Publishing Platform. Core Concept: A modern, clean-code clone of OJS (Open Journal Systems) 3.3. Goal: To replicate the deep functionality of OJS 3.3 (Submission, Review, Editorial Workflow, Publishing) but with a significantly improved, modern User Interface (UI) and User Experience (UX), strictly avoiding the outdated look of the original OJS. 2. Tech Stack (Strict Constraints)

The AI must strictly adhere to this stack. No suggestions for React/Vue SPA or Filament Admin.

    Framework: Laravel 12 (PHP 8.2+).

    Architecture: Monolithic MVC (Server-side Rendering).

    Frontend Templating: Laravel Blade (Custom Components).

    CSS Framework: Tailwind CSS (v3.x).

    Interactivity: Alpine.js (v3.x) - Used for Modals, Tabs, Wizards, Dropdowns, and Toggle Sidebar without page reloads.

    Database: Postgree

    Icons: FontAwesome (Free version).

    Asset Bundling: Vite.

3.  Core Architecture & Workflow
    A. Multi-Journal Structure

        The system supports multiple journals on a single installation.

        Context Switcher: Users can switch between journals via the Sidebar Dropdown.

        Data Isolation: Most data (Submissions, Settings, Sections) is scoped by journal_id.

B. User Roles & Permissions (OJS Parity)

Implemented using standard RBAC (Role-Based Access Control).

    Site Admin: Superuser, manages all journals.

    Journal Manager: Configures settings, users, and sections for a specific journal.

    Section Editor: Manages the workflow for assigned submissions.

    Reviewer: specialized role for peer-reviewing.

    Author: Can submit articles and track progress.

    Reader: View published content.

C. The Editorial Workflow (State Machine)

The submission lifecycle follows 4 distinct stages (Database column: stage_id):

    Submission (Stage 1): Author submits, Editor assigns Section Editor/Self. Decision: Send to Review or Decline.

    Review (Stage 2): Peer review rounds. Reviewers submit recommendations. Decision: Request Revisions, Resubmit, or Accept.

    Copyediting (Stage 3): Final metadata checks, copyedit discussions.

    Production (Stage 4): Galley creation, scheduling for publication in an Issue.

4.  Database Schema Key Concepts
    Journals Table

        id, slug (URL path), name, abbreviation, enabled.

        Settings: Stored either in dedicated columns (e.g., author_guidelines) or a journal_settings EAV table for flexibility.

Submissions Table

    id, journal_id, section_id, user_id (Submitter).

    stage_id (1=Submission, 2=Review, 3=Copyedit, 4=Production).

    status (1=Queued, 2=Published, 3=Declined).

    title, abstract.

Editorial Assignments

    Links submissions to users (Editors).

    Critical: A submission is "Unassigned" until a record exists here.

Discussions (OJS "Queries")

    Context-aware communication system.

    discussions table: submission_id, stage_id (Filtered by workflow stage).

    discussion_messages table: body, user_id.

5.  UI/UX Design System Guidelines
    Layout

        Dashboard Layout: Fixed Sidebar (Left) + Scrollable Content (Right).

        Sidebar: Collapsible (Expanded 256px, Collapsed 80px). Includes Journal Switcher.

        Mobile: Off-canvas sidebar with hamburger menu.

Components

    Cards: White background, thin gray border, soft shadow. Used for grouping settings.

    Tabs: Horizontal tabs using Alpine.js (x-show). No page reloads for switching setting groups.

    Modals: Alpine.js driven centered overlays for "Create/Edit" actions.

    Wizards: Step-by-step forms (e.g., Submission Process) with validation between steps.

    Tables: Clean rows, hover effects, distinct action buttons (Icons + Tooltips).

Styling

    Primary Color: Indigo/Blue (bg-indigo-600).

    Success: Emerald/Green.

    Danger: Red.

    Typography: Inter (Sans-serif).

6.  Route & Controller Structure

    Prefixing: Most admin routes are prefixed with the journal slug: /{journal}/dashboard, /{journal}/settings.

    Naming:

        JournalSettingsController (Masthead, Contact, Sections).

        WorkflowSettingsController (Submission, Review, Email).

        SubmissionController (CRUD for Authors).

        SubmissionWorkflowController (Editor decisions, Stage changes).

7.  Current Implementation Status (Context for AI)

    Done: Sidebar (Collapsible), Layout Shell, Journal Settings (Sections/Categories with Modals), Distribution Settings.

    In Progress: Submission Wizard (Step-by-step), Workflow Settings (Checklists, Review Forms).

    Next Steps: Full Workflow logic (Assignment -> Review -> Decision).
