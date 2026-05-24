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
            <nav class="flex space-x-8 border-b border-gray-200 overflow-x-auto" role="tablist" aria-label="Public page management tabs">
                <!-- Site Pages Tab -->
                <button @click="switchTab('pages')"
                        :class="tab === 'pages' ? 'border-teal-500 text-teal-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2"
                        role="tab"
                        :aria-selected="tab === 'pages'"
                        aria-controls="tab-panel-pages"
                        id="tab-pages">
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
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2"
                        role="tab"
                        :aria-selected="tab === 'builder'"
                        aria-controls="tab-panel-builder"
                        id="tab-builder">
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
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2"
                        role="tab"
                        :aria-selected="tab === 'nav'"
                        aria-controls="tab-panel-nav"
                        id="tab-nav">
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
             aria-labelledby="tab-pages"
             tabindex="0">
            @include('admin.public-page.partials.site-pages', ['pages' => $sitePagesData['pages']])
        </div>
        
        <!-- Page Builder Panel -->
        <div x-show="tab === 'builder'"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             role="tabpanel" 
             id="tab-panel-builder"
             aria-labelledby="tab-builder"
             tabindex="0">
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
             aria-labelledby="tab-nav"
             tabindex="0">
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
        tabs: ['pages', 'builder', 'nav'],
        
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
        },
        
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
