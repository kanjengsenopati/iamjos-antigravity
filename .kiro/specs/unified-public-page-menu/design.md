# Design Document: Unified Public Page Menu

## Overview

This design consolidates three separate admin sidebar menu items (Page Builder, Site Pages, and Site Navigation) into a single "Public Page" menu with a tabbed interface. The solution uses client-side tab switching for performance, route-based tab selection for deep linking, and maintains full backward compatibility with existing routes and functionality.

### Key Design Decisions

1. **Client-Side Tab Switching**: Use Alpine.js (already in the stack) for instant tab switching without page reloads
2. **Route-Based Tab Selection**: Preserve existing routes and use them to determine the active tab on page load
3. **Lazy Loading**: Load all tab content on initial page load but hide inactive tabs with CSS (simple, no AJAX complexity)
4. **Single Controller**: Create a new `PublicPageController` that delegates to existing controllers for data fetching
5. **Backward Compatibility**: Keep all existing routes and controllers intact, add redirects where needed

## Architecture

### Component Structure

```
┌─────────────────────────────────────────────────────────┐
│ Admin Layout (Sidebar)                                  │
│  └─ "Public Page" Menu Item                            │
│     └─ Routes to: admin.public-page.index              │
└─────────────────────────────────────────────────────────┘
                          │
                          ▼
┌─────────────────────────────────────────────────────────┐
│ PublicPageController                                     │
│  └─ index(Request $request)                            │
│     ├─ Determines active tab from route/query param    │
│     ├─ Fetches data for all three tabs                 │
│     └─ Returns unified view with tab data              │
└─────────────────────────────────────────────────────────┘
                          │
                          ▼
┌─────────────────────────────────────────────────────────┐
│ Unified View: admin.public-page.index                   │
│                                                          │
│  ┌────────────────────────────────────────────────┐   │
│  │ Tab Navigation (Alpine.js)                      │   │
│  │  [Site Pages] [Page Builder] [Site Nav]       │   │
│  └────────────────────────────────────────────────┘   │
│                                                          │
│  ┌────────────────────────────────────────────────┐   │
│  │ Tab Content Area                                │   │
│  │  • Site Pages Content (x-show="tab === 'pages'")│   │
│  │  • Page Builder Content (x-show="tab === 'builder'")│
│  │  • Site Nav Content (x-show="tab === 'nav'")   │   │
│  └────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────┘
```

### Route Architecture

**New Routes:**
- `GET /admin/public-page?tab={pages|builder|nav}` → `PublicPageController@index` (name: `admin.public-page.index`)

**Existing Routes (Preserved):**
- All existing routes for Site Pages, Page Builder, and Site Navigation remain unchanged
- Sub-routes (create, edit, update, delete) continue to work as before
- After form submissions, redirect back to unified page with appropriate tab active

**Route Mapping:**
```php
// Route pattern → Active tab
'admin.site-pages.*'        → 'pages'
'admin.site.appearance.*'   → 'builder'
'admin.site-navigation.*'   → 'nav'
```

## Components and Interfaces

### 1. PublicPageController

**Location:** `app/Http/Controllers/Admin/PublicPageController.php`

**Responsibilities:**
- Determine active tab from request (query param or route context)
- Fetch data for all three tabs by delegating to existing controllers
- Return unified view with all necessary data

**Methods:**

```php
class PublicPageController extends Controller
{
    /**
     * Display the unified public page management interface
     * 
     * @param Request $request
     * @return View
     */
    public function index(Request $request): View
    {
        // Determine active tab
        $activeTab = $this->determineActiveTab($request);
        
        // Fetch data for all tabs
        $sitePagesData = $this->getSitePagesData();
        $pageBuilderData = $this->getPageBuilderData();
        $siteNavData = $this->getSiteNavigationData();
        
        return view('admin.public-page.index', [
            'activeTab' => $activeTab,
            'sitePagesData' => $sitePagesData,
            'pageBuilderData' => $pageBuilderData,
            'siteNavData' => $siteNavData,
        ]);
    }
    
    /**
     * Determine which tab should be active
     */
    protected function determineActiveTab(Request $request): string
    {
        // Priority 1: Query parameter
        if ($request->has('tab')) {
            return $request->query('tab');
        }
        
        // Priority 2: Route context (for backward compatibility)
        if ($request->routeIs('admin.site-pages.*')) {
            return 'pages';
        }
        if ($request->routeIs('admin.site.appearance.*')) {
            return 'builder';
        }
        if ($request->routeIs('admin.site-navigation.*')) {
            return 'nav';
        }
        
        // Default: Site Pages
        return 'pages';
    }
    
    /**
     * Get data for Site Pages tab
     */
    protected function getSitePagesData(): array
    {
        return [
            'pages' => SitePage::ordered()->get(),
        ];
    }
    
    /**
     * Get data for Page Builder tab
     */
    protected function getPageBuilderData(): array
    {
        $blocks = SiteContentBlock::ordered()->get();
        $blocksByCategory = $blocks->groupBy('category');
        $journals = Journal::select('id', 'name', 'abbreviation', 'slug', 'logo_path')
            ->where('enabled', true)
            ->orderBy('name')
            ->get();
            
        return [
            'blocks' => $blocks,
            'blocksByCategory' => $blocksByCategory,
            'journals' => $journals,
        ];
    }
    
    /**
     * Get data for Site Navigation tab
     */
    protected function getSiteNavigationData(): array
    {
        $menus = collect();
        foreach (NavigationMenu::getLocations() as $location => $name) {
            $menu = NavigationMenu::firstOrCreate(
                ['journal_id' => null, 'area_name' => $location],
                ['title' => $name, 'is_active' => true]
            );
            $menus->push($menu->load(['items' => fn($q) => $q->orderBy('order')]));
        }
        
        $availableRoutes = $this->getAvailableRoutes();
        $sitePages = SitePage::published()->ordered()->get(['id', 'title', 'slug']);
        
        return [
            'menus' => $menus,
            'availableRoutes' => $availableRoutes,
            'sitePages' => $sitePages,
        ];
    }
    
    /**
     * Get available routes for site menu items
     */
    protected function getAvailableRoutes(): array
    {
        return [
            ['name' => 'portal.home', 'label' => 'Homepage', 'params' => []],
            ['name' => 'portal.journals', 'label' => 'Journals', 'params' => []],
            ['name' => 'portal.about', 'label' => 'About', 'params' => []],
            ['name' => 'portal.contact', 'label' => 'Contact', 'params' => []],
            ['name' => 'login', 'label' => 'Login', 'params' => []],
            ['name' => 'register', 'label' => 'Register', 'params' => []],
        ];
    }
}
```

### 2. Unified View Template

**Location:** `resources/views/admin/public-page/index.blade.php`

**Structure:**

```blade
@extends('layouts.admin')

@section('title', 'Public Page Management')

@section('content')
<div x-data="publicPageTabs('{{ $activeTab }}')" class="space-y-6">
    <!-- Page Header -->
    <div class="bg-white border-b border-gray-200">
        <div class="px-6 py-4">
            <h1 class="text-2xl font-bold text-gray-900">Public Page Management</h1>
            <p class="text-sm text-gray-500 mt-1">Manage your portal's public-facing content</p>
        </div>
        
        <!-- Tab Navigation -->
        <div class="px-6">
            <nav class="flex space-x-8 border-b border-gray-200" role="tablist" aria-label="Public page management tabs">
                <!-- Site Pages Tab -->
                <button @click="switchTab('pages')"
                        :class="tab === 'pages' ? 'border-teal-500 text-teal-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors duration-200"
                        role="tab"
                        :aria-selected="tab === 'pages'"
                        aria-controls="tab-panel-pages">
                    <span class="flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Site Pages
                    </span>
                </button>
                
                <!-- Page Builder Tab -->
                <button @click="switchTab('builder')"
                        :class="tab === 'builder' ? 'border-purple-500 text-purple-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors duration-200"
                        role="tab"
                        :aria-selected="tab === 'builder'"
                        aria-controls="tab-panel-builder">
                    <span class="flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z" />
                        </svg>
                        Page Builder
                    </span>
                </button>
                
                <!-- Site Navigation Tab -->
                <button @click="switchTab('nav')"
                        :class="tab === 'nav' ? 'border-orange-500 text-orange-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors duration-200"
                        role="tab"
                        :aria-selected="tab === 'nav'"
                        aria-controls="tab-panel-nav">
                    <span class="flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                        Site Nav
                    </span>
                </button>
            </nav>
        </div>
    </div>
    
    <!-- Tab Panels -->
    <div class="px-6">
        <!-- Site Pages Panel -->
        <div x-show="tab === 'pages'" 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             role="tabpanel" 
             id="tab-panel-pages"
             aria-labelledby="tab-pages">
            @include('admin.public-page.partials.site-pages', ['pages' => $sitePagesData['pages']])
        </div>
        
        <!-- Page Builder Panel -->
        <div x-show="tab === 'builder'"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             role="tabpanel" 
             id="tab-panel-builder"
             aria-labelledby="tab-builder">
            @include('admin.public-page.partials.page-builder', [
                'blocks' => $pageBuilderData['blocks'],
                'blocksByCategory' => $pageBuilderData['blocksByCategory'],
                'journals' => $pageBuilderData['journals']
            ])
        </div>
        
        <!-- Site Navigation Panel -->
        <div x-show="tab === 'nav'"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             role="tabpanel" 
             id="tab-panel-nav"
             aria-labelledby="tab-nav">
            @include('admin.public-page.partials.site-navigation', [
                'menus' => $siteNavData['menus'],
                'availableRoutes' => $siteNavData['availableRoutes'],
                'sitePages' => $siteNavData['sitePages']
            ])
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function publicPageTabs(initialTab) {
    return {
        tab: initialTab || 'pages',
        
        switchTab(newTab) {
            this.tab = newTab;
            
            // Update URL without page reload
            const url = new URL(window.location);
            url.searchParams.set('tab', newTab);
            window.history.pushState({}, '', url);
            
            // Announce to screen readers
            this.announceTabChange(newTab);
        },
        
        announceTabChange(tabName) {
            const tabLabels = {
                'pages': 'Site Pages',
                'builder': 'Page Builder',
                'nav': 'Site Navigation'
            };
            
            const announcement = document.createElement('div');
            announcement.setAttribute('role', 'status');
            announcement.setAttribute('aria-live', 'polite');
            announcement.className = 'sr-only';
            announcement.textContent = `${tabLabels[tabName]} tab selected`;
            document.body.appendChild(announcement);
            
            setTimeout(() => announcement.remove(), 1000);
        }
    };
}

// Handle browser back/forward buttons
window.addEventListener('popstate', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const tab = urlParams.get('tab') || 'pages';
    
    // Trigger Alpine.js to update
    const event = new CustomEvent('tab-change', { detail: { tab } });
    window.dispatchEvent(event);
});
</script>
@endpush
```

### 3. Tab Content Partials

Create three partial views that extract the content from existing views:

**Location:** `resources/views/admin/public-page/partials/`

- `site-pages.blade.php` - Extract content from `admin.site-pages.index`
- `page-builder.blade.php` - Extract content from `admin.site-appearance.index`
- `site-navigation.blade.php` - Extract content from `admin.site-navigation.index`

These partials will contain the exact same HTML/Blade content as the current views, just without the `@extends` and `@section` wrappers.

### 4. Sidebar Menu Update

**Location:** `resources/views/layouts/admin.blade.php`

Replace the three separate menu items with a single unified menu item:

```blade
<!-- Public Page Management (Unified) -->
<a href="{{ route('admin.public-page.index') }}"
    class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200
           {{ request()->routeIs('admin.public-page.*') || request()->routeIs('admin.site-pages.*') || request()->routeIs('admin.site.appearance.*') || request()->routeIs('admin.site-navigation.*') 
              ? 'bg-blue-500/20 text-blue-400' 
              : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
    </svg>
    <span>Public Page</span>
</a>
```

## Data Models

No new models required. The feature uses existing models:

- `SitePage` - For Site Pages tab
- `SiteContentBlock` - For Page Builder tab
- `NavigationMenu`, `NavigationMenuItem`, `NavigationMenuItemAssignment` - For Site Navigation tab
- `Journal` - For Page Builder's featured journals

## Error Handling

### Client-Side Errors

1. **Invalid Tab Parameter**: If URL contains invalid `?tab=` value, default to 'pages'
2. **JavaScript Disabled**: Tabs will still be navigable via URL parameters (graceful degradation)
3. **Browser History Issues**: Fallback to current tab state if popstate event fails

### Server-Side Errors

1. **Data Fetch Failures**: Wrap each data fetch in try-catch, return empty arrays on failure
2. **Missing Models**: Handle cases where models don't exist (e.g., no site pages created yet)
3. **Permission Errors**: Existing middleware and permission checks remain in place

```php
protected function getSitePagesData(): array
{
    try {
        return [
            'pages' => SitePage::ordered()->get(),
        ];
    } catch (\Exception $e) {
        \Log::error('Failed to fetch site pages data: ' . $e->getMessage());
        return ['pages' => collect()];
    }
}
```

## Testing Strategy

### Unit Tests

**Test File:** `tests/Unit/Controllers/PublicPageControllerTest.php`

Test cases:
1. **Tab determination logic**
   - Test `determineActiveTab()` with query parameter
   - Test `determineActiveTab()` with route context
   - Test default tab when no context provided
   - Test invalid tab parameter handling

2. **Data fetching methods**
   - Test `getSitePagesData()` returns correct structure
   - Test `getPageBuilderData()` returns correct structure
   - Test `getSiteNavigationData()` returns correct structure
   - Test error handling when models throw exceptions

3. **Route mapping**
   - Test that `admin.site-pages.*` routes map to 'pages' tab
   - Test that `admin.site.appearance.*` routes map to 'builder' tab
   - Test that `admin.site-navigation.*` routes map to 'nav' tab

### Feature Tests

**Test File:** `tests/Feature/Admin/PublicPageTest.php`

Test cases:
1. **Page rendering**
   - Test unified page loads successfully
   - Test all three tab contents are present in HTML
   - Test correct tab is marked as active based on URL parameter
   - Test page includes Alpine.js tab switching code

2. **Route access**
   - Test authenticated admin can access unified page
   - Test unauthenticated user is redirected to login
   - Test user without admin permissions gets 403

3. **Backward compatibility**
   - Test old routes still work (site-pages.index, site.appearance.index, site-navigation.index)
   - Test redirects from old routes to unified page with correct tab
   - Test form submissions redirect back to unified page

4. **Tab switching**
   - Test URL updates when tab is switched (via JavaScript simulation)
   - Test browser back/forward navigation works correctly

### Browser/E2E Tests (Optional)

**Test File:** `tests/Browser/PublicPageTabsTest.php` (using Laravel Dusk)

Test cases:
1. **Tab interaction**
   - Click each tab and verify content switches
   - Verify active tab styling updates
   - Verify URL parameter updates

2. **Keyboard navigation**
   - Tab key moves focus between tab buttons
   - Enter/Space activates focused tab
   - Arrow keys navigate between tabs

3. **Accessibility**
   - Screen reader announces tab changes
   - ARIA attributes are correct
   - Focus indicators are visible

4. **Responsive behavior**
   - Tabs display correctly on desktop (>= 768px)
   - Tabs remain usable on tablet (640-768px)
   - Tabs remain usable on mobile (< 640px)

### Integration Tests

Test that existing functionality within each tab continues to work:

1. **Site Pages tab**
   - Create, edit, delete pages
   - Toggle page published status
   - Reorder pages via drag-and-drop

2. **Page Builder tab**
   - Enable/disable blocks
   - Reorder blocks
   - Edit block configuration
   - Upload images

3. **Site Navigation tab**
   - Create, edit, delete menu items
   - Reorder menu items
   - Assign pages to menu items

### Performance Tests

1. **Initial page load time**
   - Measure time to first contentful paint
   - Verify load time increase is < 200ms compared to separate pages
   - Test with varying amounts of data (10, 50, 100 pages/blocks/menu items)

2. **Tab switching performance**
   - Measure time from click to content display
   - Verify switching time is < 100ms
   - Test with large datasets

3. **Memory usage**
   - Monitor browser memory with all tabs loaded
   - Verify no memory leaks during repeated tab switching

## Accessibility Implementation

### ARIA Roles and Attributes

```html
<!-- Tab List -->
<nav role="tablist" aria-label="Public page management tabs">
    
    <!-- Tab Button -->
    <button role="tab"
            aria-selected="true|false"
            aria-controls="tab-panel-pages"
            id="tab-pages">
        Site Pages
    </button>
    
</nav>

<!-- Tab Panel -->
<div role="tabpanel"
     id="tab-panel-pages"
     aria-labelledby="tab-pages"
     tabindex="0">
    <!-- Content -->
</div>
```

### Keyboard Navigation

Implement keyboard support in Alpine.js component:

```javascript
function publicPageTabs(initialTab) {
    return {
        tab: initialTab || 'pages',
        tabs: ['pages', 'builder', 'nav'],
        
        handleKeydown(event) {
            const currentIndex = this.tabs.indexOf(this.tab);
            
            switch(event.key) {
                case 'ArrowLeft':
                    event.preventDefault();
                    const prevIndex = currentIndex > 0 ? currentIndex - 1 : this.tabs.length - 1;
                    this.switchTab(this.tabs[prevIndex]);
                    break;
                    
                case 'ArrowRight':
                    event.preventDefault();
                    const nextIndex = currentIndex < this.tabs.length - 1 ? currentIndex + 1 : 0;
                    this.switchTab(this.tabs[nextIndex]);
                    break;
                    
                case 'Home':
                    event.preventDefault();
                    this.switchTab(this.tabs[0]);
                    break;
                    
                case 'End':
                    event.preventDefault();
                    this.switchTab(this.tabs[this.tabs.length - 1]);
                    break;
            }
        },
        
        // ... rest of component
    };
}
```

### Focus Management

- Tab buttons receive visible focus indicator (Tailwind's `focus:ring-2 focus:ring-blue-500`)
- When tab is activated, focus remains on tab button (don't auto-focus content)
- Tab panels have `tabindex="0"` to allow keyboard users to scroll content

### Screen Reader Announcements

- Use `aria-live="polite"` region to announce tab changes
- Include descriptive labels for all interactive elements
- Ensure all icons have text labels (not icon-only buttons)

## Responsive Design

### Desktop (>= 768px)

- Horizontal tab layout with full labels
- Tab buttons have adequate spacing (space-x-8)
- Content area uses full width

### Tablet (640px - 768px)

- Horizontal tab layout maintained
- Slightly reduced spacing (space-x-4)
- Font sizes remain readable

### Mobile (< 640px)

- Horizontal scrollable tab layout
- Use `overflow-x-auto` on tab container
- Ensure touch targets are at least 44x44px
- Consider stacking tabs vertically if horizontal scroll is problematic

```blade
<!-- Responsive Tab Navigation -->
<div class="px-6 overflow-x-auto">
    <nav class="flex space-x-4 sm:space-x-8 border-b border-gray-200 min-w-max" 
         role="tablist" 
         aria-label="Public page management tabs">
        <!-- Tabs -->
    </nav>
</div>
```

## Backward Compatibility Strategy

### Route Redirects

Add redirects in `routes/web.php` to maintain old bookmarks:

```php
// Redirect old routes to unified page with appropriate tab
Route::redirect('/admin/site-pages', '/admin/public-page?tab=pages');
Route::redirect('/admin/site-appearance', '/admin/public-page?tab=builder');
Route::redirect('/admin/site-navigation', '/admin/public-page?tab=nav');
```

### Form Submission Redirects

Update existing controllers to redirect to unified page after form submissions:

```php
// In SitePageController@store
return redirect()->route('admin.public-page.index', ['tab' => 'pages'])
    ->with('success', 'Page created successfully.');

// In SiteAppearanceController@update
return redirect()->route('admin.public-page.index', ['tab' => 'builder'])
    ->with('success', 'Block updated successfully.');

// In SiteNavigationController@storeItem
return response()->json([
    'success' => true,
    'redirect' => route('admin.public-page.index', ['tab' => 'nav']),
    'message' => 'Menu item created successfully.',
]);
```

### Preserving Existing Routes

All existing routes remain functional:
- `admin.site-pages.create` - Still works, redirects back to unified page
- `admin.site-pages.edit` - Still works, redirects back to unified page
- `admin.site.appearance.edit` - Still works, redirects back to unified page
- All AJAX endpoints remain unchanged

## Performance Optimizations

### Initial Load

1. **Eager Loading**: Use Eloquent eager loading to prevent N+1 queries
   ```php
   $menus->load(['items' => fn($q) => $q->orderBy('order')]);
   ```

2. **Query Optimization**: Select only needed columns
   ```php
   Journal::select('id', 'name', 'abbreviation', 'slug', 'logo_path')
   ```

3. **Caching**: Leverage existing cache mechanisms (e.g., `SiteContentBlock::clearCache()`)

### Tab Switching

1. **CSS-Only Hiding**: Use `x-show` which toggles `display: none` (no DOM manipulation)
2. **Transitions**: Use CSS transitions for smooth visual feedback (200ms)
3. **No Re-rendering**: Content is loaded once, not re-fetched on tab switch

### Asset Loading

1. **Alpine.js**: Already included in admin layout, no additional JS needed
2. **Minimal Custom JS**: Only ~50 lines of JavaScript for tab management
3. **No Additional CSS**: Use existing Tailwind classes

## Migration Path

### Phase 1: Create New Components (Non-Breaking)

1. Create `PublicPageController`
2. Create unified view and partials
3. Add new route `admin.public-page.index`
4. Test new unified page in isolation

### Phase 2: Update Sidebar (Breaking Change)

1. Update `layouts/admin.blade.php` to show unified menu item
2. Add route redirects for backward compatibility
3. Update form submission redirects in existing controllers

### Phase 3: Deprecation (Optional Future)

1. Add deprecation notices to old routes
2. Monitor usage of old routes via logging
3. Eventually remove old routes after transition period

## Security Considerations

1. **Authorization**: Reuse existing middleware and permission checks
2. **CSRF Protection**: All forms maintain existing CSRF tokens
3. **XSS Prevention**: Blade escaping remains in place
4. **Input Validation**: Existing validation rules unchanged

## Monitoring and Logging

1. **Error Tracking**: Log any data fetch failures in `PublicPageController`
2. **Performance Monitoring**: Track page load times and tab switch times
3. **Usage Analytics**: Monitor which tabs are most frequently accessed
4. **User Feedback**: Collect feedback on new unified interface

## Future Enhancements

1. **Tab State Persistence**: Remember last active tab in session/localStorage
2. **Keyboard Shortcuts**: Add Ctrl+1/2/3 to switch tabs quickly
3. **Tab Badges**: Show counts (e.g., "Site Pages (12)")
4. **Search Across Tabs**: Global search that works across all three features
5. **Bulk Operations**: Select items across tabs for bulk actions
