<div class="space-y-6" x-data="{ activeCategory: 'all' }">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-slate-800 flex items-center gap-3">
                <div class="w-10 h-10 bg-gradient-to-br from-purple-400 to-pink-500 rounded-xl flex items-center justify-center shadow-lg">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z" />
                    </svg>
                </div>
                Page Builder
            </h2>
            <p class="text-slate-600 mt-1">Customize your portal's appearance with content blocks</p>
        </div>
    </div>
    
    @if($blocks->count() > 0)
        <!-- Category Filter -->
        <div class="flex flex-wrap gap-2">
            <button @click="activeCategory = 'all'"
                    :class="activeCategory === 'all' ? 'bg-purple-600 text-white' : 'bg-white text-slate-700 hover:bg-purple-50'"
                    class="px-4 py-2 rounded-lg font-medium text-sm transition-all duration-200 border border-purple-200">
                All Blocks
            </button>
            @foreach($blocksByCategory->keys() as $category)
                <button @click="activeCategory = '{{ $category }}'"
                        :class="activeCategory === '{{ $category }}' ? 'bg-purple-600 text-white' : 'bg-white text-slate-700 hover:bg-purple-50'"
                        class="px-4 py-2 rounded-lg font-medium text-sm transition-all duration-200 border border-purple-200 capitalize">
                    {{ $category }}
                </button>
            @endforeach
        </div>
        
        <!-- Blocks Grid -->
        <div class="space-y-6">
            @foreach($blocksByCategory as $category => $categoryBlocks)
                <div x-show="activeCategory === 'all' || activeCategory === '{{ $category }}'"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 transform scale-95"
                     x-transition:enter-end="opacity-100 transform scale-100">
                    
                    <div class="mb-4">
                        <h3 class="text-lg font-bold text-slate-800 capitalize flex items-center gap-2">
                            <span class="w-1 h-6 bg-gradient-to-b from-purple-500 to-pink-500 rounded-full"></span>
                            {{ $category }}
                            <span class="text-sm font-normal text-slate-500">({{ $categoryBlocks->count() }} blocks)</span>
                        </h3>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($categoryBlocks as $block)
                            <div class="group bg-white/80 backdrop-blur-sm rounded-xl border border-slate-200 hover:border-purple-300 shadow-sm hover:shadow-lg transition-all duration-300 overflow-hidden">
                                <div class="p-6">
                                    <div class="flex items-start justify-between mb-4">
                                        <div class="flex-1">
                                            <div class="flex items-center gap-3 mb-2">
                                                <div class="w-10 h-10 bg-gradient-to-br from-purple-100 to-pink-100 rounded-lg flex items-center justify-center">
                                                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5z" />
                                                    </svg>
                                                </div>
                                                <h4 class="font-bold text-slate-800 group-hover:text-purple-600 transition-colors">
                                                    {{ $block->title }}
                                                </h4>
                                            </div>
                                            <p class="text-sm text-slate-600 line-clamp-2">{{ $block->description }}</p>
                                        </div>
                                        
                                        <!-- Toggle Switch -->
                                        <label class="relative inline-flex items-center cursor-pointer ml-4">
                                            <input type="checkbox" 
                                                   {{ $block->is_active ? 'checked' : '' }} 
                                                   class="sr-only peer"
                                                   onchange="toggleBlock({{ $block->id }}, this.checked)">
                                            <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-purple-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-gradient-to-r peer-checked:from-purple-500 peer-checked:to-pink-500"></div>
                                        </label>
                                    </div>
                                    
                                    <!-- Block Info -->
                                    <div class="flex items-center gap-4 text-xs text-slate-500 mb-4">
                                        <span class="flex items-center gap-1">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" />
                                            </svg>
                                            Order: {{ $block->order }}
                                        </span>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full {{ $block->is_active ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-700' }}">
                                            {{ $block->is_active ? '● Active' : '○ Inactive' }}
                                        </span>
                                    </div>
                                    
                                    <!-- Actions -->
                                    <div class="flex items-center gap-2 pt-4 border-t border-slate-100">
                                        <a href="{{ route('admin.site.appearance.edit', $block) }}" 
                                           class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2 bg-purple-50 text-purple-600 rounded-lg hover:bg-purple-100 transition-colors text-sm font-medium">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                            Edit Content
                                        </a>
                                        <a href="{{ route('admin.site.appearance.config', $block) }}" 
                                           class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-slate-50 text-slate-600 rounded-lg hover:bg-slate-100 transition-colors text-sm font-medium">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            </svg>
                                            Config
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <!-- Empty State -->
        <div class="bg-white/80 backdrop-blur-sm rounded-2xl border-2 border-dashed border-slate-200 p-12 text-center">
            <div class="max-w-md mx-auto">
                <div class="w-20 h-20 bg-gradient-to-br from-purple-100 to-pink-100 rounded-2xl flex items-center justify-center mx-auto mb-6">
                    <svg class="w-10 h-10 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-slate-800 mb-2">No Content Blocks</h3>
                <p class="text-slate-600">Content blocks will appear here once they are configured.</p>
            </div>
        </div>
    @endif
</div>

<script>
function toggleBlock(blockId, isActive) {
    fetch(`/admin/site-appearance/${blockId}/toggle`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ is_active: isActive })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success notification
            console.log('Block toggled successfully');
        }
    })
    .catch(error => console.error('Error:', error));
}
</script>
