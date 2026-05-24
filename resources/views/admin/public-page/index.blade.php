@extends('layouts.admin')

@section('title', 'Public Page Management')

@section('content')
<div x-data="publicPageTabs('{{ $activeTab }}')" class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50">
    <!-- Page Header with Glassmorphism -->
    <div class="bg-white/80 backdrop-blur-xl border-b border-white/20 shadow-lg sticky top-0 z-10">
        <div class="px-6 py-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent">
                        Public Page Management
                    </h1>
                    <p class="text-sm text-slate-600 mt-2 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                        Manage your portal's public-facing content with ease
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs font-medium">
                        ● Live
                    </span>
                </div>
            </div>
        </div>
        
        <!-- Seamless Door-Style Tab Navigation -->
        <div class="px-6 relative">
            <div class="relative">
                <!-- Sliding Door Indicator -->
                <div class="absolute bottom-0 h-1 bg-gradient-to-r transition-all duration-500 ease-out rounded-t-lg"
                     :class="{
                         'from-emerald-400 to-teal-500': tab === 'pages',
                         'from-purple-400 to-pink-500': tab === 'builder',
                         'from-orange-400 to-amber-500': tab === 'nav'
                     }"
                     :style="{
                         'left': tab === 'pages' ? '0%' : (tab === 'builder' ? '33.33%' : '66.66%'),
                         'width': '33.33%'
                     }"></div>
                
                <nav class="flex relative" role="tablist" aria-label="Public page management tabs">
                    <!-- Site Pages Tab -->
                    <button @click="switchTab('pages')"
                            @keydown="handleKeydown($event)"
                            class="flex-1 group relative overflow-hidden transition-all duration-300"
                            role="tab"
                            :aria-selected="tab === 'pages'"
                            aria-controls="tab-panel-pages"
                            id="tab-pages">
                        <div class="relative px-6 py-4 flex items-center justify-center gap-3 transition-all duration-300"
                             :class="tab === 'pages' ? 'text-emerald-600' : 'text-slate-500 hover:text-slate-700'">
                            <!-- Background Glow -->
                            <div class="absolute inset-0 bg-gradient-to-br from-emerald-50 to-teal-50 opacity-0 group-hover:opacity-100 transition-opacity duration-300"
                                 :class="{'opacity-100': tab === 'pages'}"></div>
                            
                            <!-- Icon with Animation -->
                            <div class="relative transform transition-transform duration-300"
                                 :class="{'scale-110 rotate-3': tab === 'pages'}">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                            
                            <!-- Label -->
                            <span class="relative font-semibold text-sm tracking-wide">Site Pages</span>
                            
                            <!-- Active Badge -->
                            <span x-show="tab === 'pages'" 
                                  x-transition:enter="transition ease-out duration-200"
                                  x-transition:enter-start="opacity-0 scale-50"
                                  x-transition:enter-end="opacity-100 scale-100"
                                  class="relative w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></span>
                        </div>
                    </button>
                    
                    <!-- Page Builder Tab -->
                    <button @click="switchTab('builder')"
                            @keydown="handleKeydown($event)"
                            class="flex-1 group relative overflow-hidden transition-all duration-300"
                            role="tab"
                            :aria-selected="tab === 'builder'"
                            aria-controls="tab-panel-builder"
                            id="tab-builder">
                        <div class="relative px-6 py-4 flex items-center justify-center gap-3 transition-all duration-300"
                             :class="tab === 'builder' ? 'text-purple-600' : 'text-slate-500 hover:text-slate-700'">
                            <!-- Background Glow -->
                            <div class="absolute inset-0 bg-gradient-to-br from-purple-50 to-pink-50 opacity-0 group-hover:opacity-100 transition-opacity duration-300"
                                 :class="{'opacity-100': tab === 'builder'}"></div>
                            
                            <!-- Icon with Animation -->
                            <div class="relative transform transition-transform duration-300"
                                 :class="{'scale-110 -rotate-3': tab === 'builder'}">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z" />
                                </svg>
                            </div>
                            
                            <!-- Label -->
                            <span class="relative font-semibold text-sm tracking-wide">Page Builder</span>
                            
                            <!-- Active Badge -->
                            <span x-show="tab === 'builder'" 
                                  x-transition:enter="transition ease-out duration-200"
                                  x-transition:enter-start="opacity-0 scale-50"
                                  x-transition:enter-end="opacity-100 scale-100"
                                  class="relative w-2 h-2 bg-purple-500 rounded-full animate-pulse"></span>
                        </div>
                    </button>
                    
                    <!-- Site Navigation Tab -->
                    <button @click="switchTab('nav')"
                            @keydown="handleKeydown($event)"
                            class="flex-1 group relative overflow-hidden transition-all duration-300"
                            role="tab"
                            :aria-selected="tab === 'nav'"
                            aria-controls="tab-panel-nav"
                            id="tab-nav">
                        <div class="relative px-6 py-4 flex items-center justify-center gap-3 transition-all duration-300"
                             :class="tab === 'nav' ? 'text-orange-600' : 'text-slate-500 hover:text-slate-700'">
                            <!-- Background Glow -->
                            <div class="absolute inset-0 bg-gradient-to-br from-orange-50 to-amber-50 opacity-0 group-hover:opacity-100 transition-opacity duration-300"
                                 :class="{'opacity-100': tab === 'nav'}"></div>
                            
                            <!-- Icon with Animation -->
                            <div class="relative transform transition-transform duration-300"
                                 :class="{'scale-110 rotate-3': tab === 'nav'}">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 6h16M4 12h16M4 18h16" />
                                </svg>
                            </div>
                            
                            <!-- Label -->
                            <span class="relative font-semibold text-sm tracking-wide">Site Nav</span>
                            
                            <!-- Active Badge -->
                            <span x-show="tab === 'nav'" 
                                  x-transition:enter="transition ease-out duration-200"
                                  x-transition:enter-start="opacity-0 scale-50"
                                  x-transition:enter-end="opacity-100 scale-100"
                                  class="relative w-2 h-2 bg-orange-500 rounded-full animate-pulse"></span>
                        </div>
                    </button>
                </nav>
            </div>
        </div>
    </div>
    
    <!-- Tab Panels with Sliding Door Animation -->
    <div class="px-6 py-8 relative overflow-hidden">
        <!-- Site Pages Panel -->
        <div x-show="tab === 'pages'" 
             x-transition:enter="transition ease-out duration-500 transform"
             x-transition:enter-start="opacity-0 translate-x-full"
             x-transition:enter-end="opacity-100 translate-x-0"
             x-transition:leave="transition ease-in duration-300 transform"
             x-transition:leave-start="opacity-100 translate-x-0"
             x-transition:leave-end="opacity-0 -translate-x-full"
             role="tabpanel" 
             id="tab-panel-pages"
             aria-labelledby="tab-pages"
             tabindex="0"
             class="absolute inset-0 px-6 py-8">
            @include('admin.public-page.partials.site-pages', ['pages' => $sitePagesData['pages']])
        </div>
        
        <!-- Page Builder Panel -->
        <div x-show="tab === 'builder'"
             x-transition:enter="transition ease-out duration-500 transform"
             x-transition:enter-start="opacity-0 translate-x-full"
             x-transition:enter-end="opacity-100 translate-x-0"
             x-transition:leave="transition ease-in duration-300 transform"
             x-transition:leave-start="opacity-100 translate-x-0"
             x-transition:leave-end="opacity-0 -translate-x-full"
             role="tabpanel" 
             id="tab-panel-builder"
             aria-labelledby="tab-builder"
             tabindex="0"
             class="absolute inset-0 px-6 py-8">
            @include('admin.public-page.partials.page-builder', [
                'blocks' => $pageBuilderData['blocks'],
                'blocksByCategory' => $pageBuilderData['blocksByCategory'],
                'journals' => $pageBuilderData['journals']
            ])
        </div>
        
        <!-- Site Navigation Panel -->
        <div x-show="tab === 'nav'"
             x-transition:enter="transition ease-out duration-500 transform"
             x-transition:enter-start="opacity-0 translate-x-full"
             x-transition:enter-end="opacity-100 translate-x-0"
             x-transition:leave="transition ease-in duration-300 transform"
             x-transition:leave-start="opacity-100 translate-x-0"
             x-transition:leave-end="opacity-0 -translate-x-full"
             role="tabpanel" 
             id="tab-panel-nav"
             aria-labelledby="tab-nav"
             tabindex="0"
             class="absolute inset-0 px-6 py-8">
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
