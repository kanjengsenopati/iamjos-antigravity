<div class="space-y-6">
    <!-- Header with Actions -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-slate-800 flex items-center gap-3">
                <div class="w-10 h-10 bg-gradient-to-br from-emerald-400 to-teal-500 rounded-xl flex items-center justify-center shadow-lg">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                Site Pages
            </h2>
            <p class="text-slate-600 mt-1">Create and manage custom pages for your portal</p>
        </div>
        <a href="{{ route('admin.site-pages.create') }}" 
           class="group inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-emerald-500 to-teal-600 text-white rounded-xl hover:from-emerald-600 hover:to-teal-700 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
            <svg class="w-5 h-5 transition-transform group-hover:rotate-90 duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            <span class="font-semibold">Create New Page</span>
        </a>
    </div>
    
    @if($pages->count() > 0)
        <!-- Stats Overview -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-white/80 backdrop-blur-sm rounded-xl p-4 border border-emerald-100 shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-emerald-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-slate-800">{{ $pages->count() }}</p>
                        <p class="text-sm text-slate-600">Total Pages</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white/80 backdrop-blur-sm rounded-xl p-4 border border-green-100 shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-slate-800">{{ $pages->where('published', true)->count() }}</p>
                        <p class="text-sm text-slate-600">Published</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white/80 backdrop-blur-sm rounded-xl p-4 border border-slate-100 shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-slate-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-slate-800">{{ $pages->where('published', false)->count() }}</p>
                        <p class="text-sm text-slate-600">Drafts</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Pages Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($pages as $page)
                <div class="group bg-white/80 backdrop-blur-sm rounded-xl border border-slate-200 hover:border-emerald-300 shadow-sm hover:shadow-xl transition-all duration-300 overflow-hidden transform hover:-translate-y-1">
                    <!-- Card Header -->
                    <div class="p-6 border-b border-slate-100">
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex-1">
                                <h3 class="font-bold text-lg text-slate-800 group-hover:text-emerald-600 transition-colors line-clamp-2">
                                    {{ $page->title }}
                                </h3>
                                <p class="text-sm text-slate-500 mt-1 flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                                    </svg>
                                    /{{ $page->slug }}
                                </p>
                            </div>
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $page->published ? 'bg-green-100 text-green-700 ring-1 ring-green-200' : 'bg-slate-100 text-slate-700 ring-1 ring-slate-200' }}">
                                @if($page->published)
                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                    Published
                                @else
                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8 7a1 1 0 00-1 1v4a1 1 0 001 1h4a1 1 0 001-1V8a1 1 0 00-1-1H8z" clip-rule="evenodd" />
                                    </svg>
                                    Draft
                                @endif
                            </span>
                        </div>
                        
                        @if($page->content)
                            <p class="text-sm text-slate-600 line-clamp-2">
                                {{ Str::limit(strip_tags($page->content), 100) }}
                            </p>
                        @endif
                    </div>
                    
                    <!-- Card Footer with Actions -->
                    <div class="px-6 py-4 bg-slate-50/50 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <a href="{{ route('admin.site-pages.edit', $page) }}" 
                               class="inline-flex items-center gap-1 px-3 py-1.5 bg-white text-emerald-600 rounded-lg hover:bg-emerald-50 transition-colors text-sm font-medium border border-emerald-200 hover:border-emerald-300">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                                Edit
                            </a>
                            
                            @if($page->published)
                                <a href="{{ route('site.page', $page->slug) }}" 
                                   target="_blank"
                                   class="inline-flex items-center gap-1 px-3 py-1.5 bg-white text-blue-600 rounded-lg hover:bg-blue-50 transition-colors text-sm font-medium border border-blue-200 hover:border-blue-300">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    View
                                </a>
                            @endif
                        </div>
                        
                        <form method="POST" action="{{ route('admin.site-pages.destroy', $page) }}" 
                              class="inline" 
                              onsubmit="return confirm('Are you sure you want to delete this page?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" 
                                    class="inline-flex items-center gap-1 px-3 py-1.5 bg-white text-red-600 rounded-lg hover:bg-red-50 transition-colors text-sm font-medium border border-red-200 hover:border-red-300">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                                Delete
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <!-- Empty State -->
        <div class="bg-white/80 backdrop-blur-sm rounded-2xl border-2 border-dashed border-slate-200 p-12 text-center">
            <div class="max-w-md mx-auto">
                <div class="w-20 h-20 bg-gradient-to-br from-emerald-100 to-teal-100 rounded-2xl flex items-center justify-center mx-auto mb-6">
                    <svg class="w-10 h-10 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-slate-800 mb-2">No Pages Yet</h3>
                <p class="text-slate-600 mb-6">Get started by creating your first custom page for your portal.</p>
                <a href="{{ route('admin.site-pages.create') }}" 
                   class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-emerald-500 to-teal-600 text-white rounded-xl hover:from-emerald-600 hover:to-teal-700 transition-all duration-300 shadow-lg hover:shadow-xl font-semibold">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Create Your First Page
                </a>
            </div>
        </div>
    @endif
</div>
