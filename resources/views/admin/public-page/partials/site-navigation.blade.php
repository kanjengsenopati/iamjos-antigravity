<div class="bg-white rounded-lg shadow">
    <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-lg font-semibold text-gray-900">Site Navigation</h2>
        <p class="text-sm text-gray-600 mt-1">Manage navigation menus and links</p>
    </div>
    
    <div class="p-6">
        @if($menus->count() > 0)
            <div class="space-y-6">
                @foreach($menus as $menu)
                    <div class="border border-gray-200 rounded-lg p-4">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="font-semibold text-gray-900">{{ $menu->title }}</h3>
                            <button class="inline-flex items-center px-3 py-1 bg-teal-600 text-white text-sm rounded hover:bg-teal-700 transition-colors">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                                Add Item
                            </button>
                        </div>
                        
                        @if($menu->items->count() > 0)
                            <ul class="space-y-2">
                                @foreach($menu->items as $item)
                                    <li class="flex items-center justify-between p-2 bg-gray-50 rounded">
                                        <span class="text-gray-900">{{ $item->label }}</span>
                                        <div class="flex gap-2">
                                            <button class="text-teal-600 hover:text-teal-900 text-sm">Edit</button>
                                            <button class="text-red-600 hover:text-red-900 text-sm">Delete</button>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p class="text-gray-500 text-sm">No menu items yet</p>
                        @endif
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-8">
                <svg class="w-12 h-12 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
                <p class="text-gray-500">No navigation menus available</p>
            </div>
        @endif
    </div>
</div>
