# Implementation Plan: Unified Public Page Menu

## Overview

This implementation consolidates three separate admin sidebar menu items (Page Builder, Site Pages, and Site Navigation) into a single "Public Page" menu with a tabbed interface. The solution uses Alpine.js for client-side tab switching, maintains backward compatibility with existing routes, and preserves all existing functionality.

## Tasks

- [ ] 1. Create PublicPageController and route infrastructure
  - [-] 1.1 Create PublicPageController with index method and data fetching logic
    - Create `app/Http/Controllers/Admin/PublicPageController.php`
    - Implement `index()` method that determines active tab and fetches data for all three tabs
    - Implement `determineActiveTab()` method with query parameter and route context logic
    - Implement `getSitePagesData()`, `getPageBuilderData()`, and `getSiteNavigationData()` methods
    - Implement `getAvailableRoutes()` helper method for site navigation
    - Add error handling with try-catch blocks and logging for data fetch failures
    - _Requirements: 1.4, 2.5, 3.1, 3.2, 3.3, 5.1, 5.2, 5.3_
  
  - [ ] 1.2 Add new route for unified public page interface
    - Add `GET /admin/public-page` route in `routes/web.php`
    - Name the route `admin.public-page.index`
    - Apply existing admin authentication and permission middleware
    - _Requirements: 1.4, 9.2_
  
  - [ ]* 1.3 Write unit tests for PublicPageController
    - Test `determineActiveTab()` with query parameter, route context, and default behavior
    - Test invalid tab parameter handling (should default to 'pages')
    - Test all three data fetching methods return correct structure
    - Test error handling when models throw exceptions
    - Test route mapping logic for all three feature routes
    - _Requirements: 5.1, 5.2, 5.3, 10.2_

- [ ] 2. Create unified view template with tab interface
  - [~] 2.1 Create main unified view template
    - Create `resources/views/admin/public-page/index.blade.php`
    - Implement page header with title and description
    - Implement tab navigation with three tabs (Site Pages, Page Builder, Site Nav)
    - Add Alpine.js `x-data` directive with `publicPageTabs()` component
    - Implement tab buttons with proper ARIA roles and attributes
    - Add visual indicators for active tab (border color, text color)
    - Include icons for each tab using SVG
    - Implement tab panels with `x-show` directives for content switching
    - Add smooth transitions for tab switching (200ms fade)
    - _Requirements: 1.1, 1.4, 2.1, 2.2, 2.3, 2.4, 2.5, 4.1, 4.2, 4.3, 4.4, 4.5, 7.1, 7.2, 7.3, 7.4, 7.5, 8.1, 8.2_
  
  - [~] 2.2 Create Alpine.js tab management component
    - Add `@push('scripts')` section with `publicPageTabs()` JavaScript function
    - Implement `switchTab()` method that updates active tab and URL
    - Implement URL update logic using `window.history.pushState()`
    - Implement `announceTabChange()` method for screen reader announcements
    - Add `popstate` event listener for browser back/forward button support
    - Implement keyboard navigation support (Arrow keys, Home, End)
    - Add `handleKeydown()` method for keyboard event handling
    - _Requirements: 4.1, 4.2, 4.3, 4.4, 4.5, 5.4, 5.5, 8.3, 8.4, 8.5, 10.1_
  
  - [~] 2.3 Implement responsive tab layout
    - Add responsive classes for desktop (>= 768px) horizontal layout
    - Add responsive classes for tablet (640-768px) with reduced spacing
    - Add responsive classes for mobile (< 640px) with horizontal scroll
    - Ensure touch targets are at least 44x44px for mobile
    - Add `overflow-x-auto` for mobile tab scrolling
    - Test tab layout at all three breakpoints
    - _Requirements: 6.1, 6.2, 6.3, 6.4, 6.5_

- [ ] 3. Create tab content partial views
  - [~] 3.1 Extract Site Pages content into partial
    - Create `resources/views/admin/public-page/partials/site-pages.blade.php`
    - Extract content from existing `admin.site-pages.index` view
    - Remove `@extends` and `@section` wrappers, keep only content HTML
    - Ensure all existing functionality (create, edit, delete, reorder) is preserved
    - Pass `$pages` variable to partial
    - _Requirements: 3.1, 3.4, 3.5_
  
  - [~] 3.2 Extract Page Builder content into partial
    - Create `resources/views/admin/public-page/partials/page-builder.blade.php`
    - Extract content from existing `admin.site.appearance.index` view
    - Remove `@extends` and `@section` wrappers, keep only content HTML
    - Ensure all existing functionality (enable/disable, reorder, edit, upload) is preserved
    - Pass `$blocks`, `$blocksByCategory`, and `$journals` variables to partial
    - _Requirements: 3.2, 3.4, 3.5_
  
  - [~] 3.3 Extract Site Navigation content into partial
    - Create `resources/views/admin/public-page/partials/site-navigation.blade.php`
    - Extract content from existing `admin.site-navigation.index` view
    - Remove `@extends` and `@section` wrappers, keep only content HTML
    - Ensure all existing functionality (create, edit, delete, reorder menu items) is preserved
    - Pass `$menus`, `$availableRoutes`, and `$sitePages` variables to partial
    - _Requirements: 3.3, 3.4, 3.5_

- [~] 4. Checkpoint - Verify unified page renders correctly
  - Ensure all tests pass, ask the user if questions arise.

- [ ] 5. Update sidebar menu and add backward compatibility
  - [~] 5.1 Update admin layout sidebar menu
    - Locate the sidebar menu section in `resources/views/layouts/admin.blade.php`
    - Remove the three separate menu items: "Page Builder", "Site Pages", "Site Navigation"
    - Add single "Public Page" menu item with appropriate icon
    - Implement active state logic that highlights when any of the three feature routes are accessed
    - Use route helper `route('admin.public-page.index')` for menu link
    - _Requirements: 1.1, 1.2, 1.3, 1.5, 7.1, 7.2, 7.3, 7.4, 7.5_
  
  - [~] 5.2 Add route redirects for backward compatibility
    - Add redirect from `/admin/site-pages` to `/admin/public-page?tab=pages` in `routes/web.php`
    - Add redirect from `/admin/site-appearance` to `/admin/public-page?tab=builder` in `routes/web.php`
    - Add redirect from `/admin/site-navigation` to `/admin/public-page?tab=nav` in `routes/web.php`
    - _Requirements: 9.1, 9.4_
  
  - [~] 5.3 Update form submission redirects in existing controllers
    - Update `SitePageController` store/update methods to redirect to `admin.public-page.index?tab=pages`
    - Update `SiteAppearanceController` store/update methods to redirect to `admin.public-page.index?tab=builder`
    - Update `SiteNavigationController` store/update methods to redirect to `admin.public-page.index?tab=nav`
    - Ensure success/error messages are preserved in redirects
    - _Requirements: 9.1, 9.2, 9.3_

- [ ] 6. Implement accessibility features
  - [~] 6.1 Add ARIA attributes to tab interface
    - Add `role="tablist"` to tab navigation container
    - Add `role="tab"` to each tab button
    - Add `role="tabpanel"` to each content panel
    - Add `aria-selected` attribute to tab buttons (true/false based on active state)
    - Add `aria-controls` attribute linking tabs to their panels
    - Add `aria-labelledby` attribute linking panels to their tabs
    - Add `aria-label` to tab navigation for context
    - _Requirements: 8.1, 8.2_
  
  - [~] 6.2 Implement keyboard navigation
    - Add `@keydown` event listener to tab buttons
    - Implement Arrow Left/Right navigation between tabs
    - Implement Home key to jump to first tab
    - Implement End key to jump to last tab
    - Ensure Tab key moves focus between tab buttons
    - Ensure Enter/Space activates focused tab
    - _Requirements: 8.3_
  
  - [~] 6.3 Add focus indicators and screen reader support
    - Add visible focus indicator styles using Tailwind's `focus:ring-2 focus:ring-blue-500`
    - Add `tabindex="0"` to tab panels for keyboard scrolling
    - Implement screen reader announcement using `aria-live="polite"` region
    - Ensure all icons have accompanying text labels
    - Test focus management (focus remains on tab button after activation)
    - _Requirements: 8.4, 8.5_

- [ ] 7. Write feature tests for unified page
  - [ ]* 7.1 Write tests for page rendering and access control
    - Test unified page loads successfully for authenticated admin
    - Test all three tab contents are present in HTML response
    - Test correct tab is marked as active based on URL parameter
    - Test page includes Alpine.js tab switching code
    - Test unauthenticated user is redirected to login
    - Test user without admin permissions gets 403 error
    - _Requirements: 1.4, 2.1, 2.2, 2.3, 2.4, 4.4, 9.5_
  
  - [ ]* 7.2 Write tests for backward compatibility
    - Test old route `/admin/site-pages` redirects to unified page with `tab=pages`
    - Test old route `/admin/site-appearance` redirects to unified page with `tab=builder`
    - Test old route `/admin/site-navigation` redirects to unified page with `tab=nav`
    - Test form submissions redirect back to unified page with correct tab
    - Test all existing CRUD operations still work within tabs
    - _Requirements: 9.1, 9.2, 9.3, 9.4_
  
  - [ ]* 7.3 Write tests for tab switching and URL updates
    - Test URL parameter `?tab=pages` activates Site Pages tab
    - Test URL parameter `?tab=builder` activates Page Builder tab
    - Test URL parameter `?tab=nav` activates Site Nav tab
    - Test invalid tab parameter defaults to 'pages'
    - Test route context determines active tab when no query parameter
    - _Requirements: 5.1, 5.2, 5.3, 5.4, 5.5_

- [ ] 8. Performance optimization and testing
  - [~] 8.1 Optimize data fetching with eager loading
    - Add eager loading for navigation menu items: `$menus->load(['items' => fn($q) => $q->orderBy('order')])`
    - Use selective column queries: `Journal::select('id', 'name', 'abbreviation', 'slug', 'logo_path')`
    - Verify no N+1 query issues using Laravel Debugbar or Telescope
    - _Requirements: 10.2, 10.3_
  
  - [ ]* 8.2 Write performance tests
    - Test initial page load time is within acceptable range (< 200ms increase)
    - Test with varying data sizes (10, 50, 100 pages/blocks/menu items)
    - Measure and verify tab switching occurs within 100ms
    - Test that data is not re-fetched when switching between tabs
    - Monitor for layout shifts or flickering during transitions
    - _Requirements: 10.1, 10.2, 10.3, 10.4, 10.5_

- [~] 9. Final checkpoint and documentation
  - Ensure all tests pass, ask the user if questions arise.

## Notes

- Tasks marked with `*` are optional and can be skipped for faster MVP
- The design uses PHP/Laravel with Blade templates and Alpine.js (already in the stack)
- No new database models are required - uses existing SitePage, SiteContentBlock, NavigationMenu models
- All existing functionality is preserved within tab content areas
- Backward compatibility is maintained through route redirects
- The feature is primarily a UI/UX reorganization with no business logic changes
- Alpine.js is already included in the admin layout, so no additional JavaScript libraries are needed
- All existing permission checks and middleware remain in place

## Task Dependency Graph

```json
{
  "waves": [
    { "id": 0, "tasks": ["1.1", "1.2"] },
    { "id": 1, "tasks": ["1.3", "2.1"] },
    { "id": 2, "tasks": ["2.2", "2.3", "3.1", "3.2", "3.3"] },
    { "id": 3, "tasks": ["5.1", "5.2", "5.3"] },
    { "id": 4, "tasks": ["6.1", "6.2", "6.3"] },
    { "id": 5, "tasks": ["7.1", "7.2", "7.3", "8.1"] },
    { "id": 6, "tasks": ["8.2"] }
  ]
}
```
