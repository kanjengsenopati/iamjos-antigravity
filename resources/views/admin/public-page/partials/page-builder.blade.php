<div class="bg-white rounded-lg shadow">
    <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-lg font-semibold text-gray-900">Page Builder</h2>
        <p class="text-sm text-gray-600 mt-1">Manage content blocks and site appearance</p>
    </div>
    
    <div class="p-6">
        @if($blocks->count() > 0)
            <div class="space-y-4">
                @foreach($blocksByCategory as $category => $categoryBlocks)
                    <div class="border border-gray-200 rounded-lg p-4">
                        <h3 class="font-semibold text-gray-900 mb-3 capitalize">{{ $category }}</h3>
                        <div class="space-y-2">
                            @foreach($categoryBlocks as $block)
                                <div class="flex items-center justify-between p-3 bg-gray-50 rounded">
                                    <div>
                                        <p class="font-medium text-gray-900">{{ $block->title }}</p>
                                        <p class="text-sm text-gray-600">{{ $block->description }}</p>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <label class="flex items-center">
                                            <input type="checkbox" {{ $block->is_active ? 'checked' : '' }} class="rounded" onchange="toggleBlock({{ $block->id }})">
                                            <span class="ml-2 text-sm text-gray-600">Active</span>
                                        </label>
                                        <a href="{{ route('admin.site.appearance.edit', $block) }}" class="text-teal-600 hover:text-teal-900 text-sm">Edit</a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-8">
                <svg class="w-12 h-12 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z" />
                </svg>
                <p class="text-gray-500">No content blocks available</p>
            </div>
        @endif
    </div>
</div>

<script>
function toggleBlock(blockId) {
    // Implementation for toggling blocks
    console.log('Toggle block:', blockId);
}
</script>
