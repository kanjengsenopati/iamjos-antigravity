<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-slate-800 flex items-center gap-3">
                <div class="w-10 h-10 bg-gradient-to-br from-orange-400 to-amber-500 rounded-xl flex items-center justify-center shadow-lg">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </div>
                Site Navigation
            </h2>
            <p class="text-slate-600 mt-1">Configure navigation menus and menu items</p>
        </div>
    </div>
    
    @if($menus->count() > 0)
        <!-- Menus Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            @foreach($menus as $menu)
                <div class="bg-white/80 backdrop-blur-sm rounded-xl border border-slate-200 shadow-sm hover:shadow-lg transition-all duration-300 overflow-hidden">
                    <!-- Menu Header -->
                    <div class="px-6 py-4 bg-gradient-to-r from-orange-50 to-amber-50 border-b border-orange-100">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-white rounded-lg flex items-center justify-center shadow-sm">
                                    <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        @if($menu->area_name === 'main')
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                        @elseif($menu->area_name === 'footer')
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        @else
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                                        @endif
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="font-bold text-slate-800">{{ $menu->title }}</h3>
                                    <p class="text-xs text-slate-600 capitalize">{{ $menu->area_name }} menu</p>
                                </div>
                            </div>
                            
                            <button onclick="openAddItemModal({{ $menu->id }})"
                                    class="inline-flex items-center gap-1 px-3 py-1.5 bg-gradient-to-r from-orange-500 to-amber-600 text-white text-sm rounded-lg hover:from-orange-600 hover:to-amber-700 transition-all duration-200 shadow-sm font-medium">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                                Add Item
                            </button>
                        </div>
                    </div>
                    
                    <!-- Menu Items -->
                    <div class="p-6">
                        @if($menu->items->count() > 0)
                            <ul class="space-y-2">
                                @foreach($menu->items as $item)
                                    <li class="group bg-slate-50 hover:bg-orange-50 rounded-lg p-4 transition-all duration-200 border border-slate-100 hover:border-orange-200">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center gap-3 flex-1">
                                                <!-- Drag Handle -->
                                                <div class="cursor-move text-slate-400 hover:text-slate-600">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16" />
                                                    </svg>
                                                </div>
                                                
                                                <!-- Item Info -->
                                                <div class="flex-1">
                                                    <div class="flex items-center gap-2">
                                                        <span class="font-semibold text-slate-800 group-hover:text-orange-600 transition-colors">
                                                            {{ $item->label }}
                                                        </span>
                                                        @if($item->is_external)
                                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700">
                                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                                                </svg>
                                                                External
                                                            </span>
                                                        @endif
                                                    </div>
                                                    <p class="text-xs text-slate-500 mt-1 flex items-center gap-1">
                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                                                        </svg>
                                                        {{ $item->url ?? $item->route_name }}
                                                    </p>
                                                </div>
                                            </div>
                                            
                                            <!-- Actions -->
                                            <div class="flex items-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                                <button onclick="editMenuItem({{ $item->id }})"
                                                        class="p-2 text-orange-600 hover:bg-orange-100 rounded-lg transition-colors">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                    </svg>
                                                </button>
                                                <form method="POST" action="{{ route('admin.site-navigation.items.destroy', $item) }}" 
                                                      class="inline"
                                                      onsubmit="return confirm('Delete this menu item?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                            class="p-2 text-red-600 hover:bg-red-100 rounded-lg transition-colors">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                        </svg>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                            
                            <!-- Item Count -->
                            <div class="mt-4 pt-4 border-t border-slate-100 text-center">
                                <p class="text-sm text-slate-600">
                                    <span class="font-semibold text-orange-600">{{ $menu->items->count() }}</span> menu items
                                </p>
                            </div>
                        @else
                            <!-- Empty Menu State -->
                            <div class="text-center py-8">
                                <div class="w-16 h-16 bg-slate-100 rounded-xl flex items-center justify-center mx-auto mb-4">
                                    <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                    </svg>
                                </div>
                                <p class="text-slate-500 text-sm mb-4">No menu items yet</p>
                                <button onclick="openAddItemModal({{ $menu->id }})"
                                        class="inline-flex items-center gap-2 px-4 py-2 bg-orange-50 text-orange-600 rounded-lg hover:bg-orange-100 transition-colors text-sm font-medium">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                    </svg>
                                    Add First Item
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
        
        <!-- Quick Links Section -->
        <div class="bg-gradient-to-br from-orange-50 to-amber-50 rounded-xl border border-orange-100 p-6">
            <h3 class="font-bold text-slate-800 mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
                Available Routes
            </h3>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                @foreach($availableRoutes as $route)
                    <div class="bg-white rounded-lg p-3 border border-orange-100 hover:border-orange-300 transition-colors cursor-pointer">
                        <p class="text-sm font-medium text-slate-800">{{ $route['label'] }}</p>
                        <p class="text-xs text-slate-500 mt-1">{{ $route['name'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    @else
        <!-- Empty State -->
        <div class="bg-white/80 backdrop-blur-sm rounded-2xl border-2 border-dashed border-slate-200 p-12 text-center">
            <div class="max-w-md mx-auto">
                <div class="w-20 h-20 bg-gradient-to-br from-orange-100 to-amber-100 rounded-2xl flex items-center justify-center mx-auto mb-6">
                    <svg class="w-10 h-10 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-slate-800 mb-2">No Navigation Menus</h3>
                <p class="text-slate-600">Navigation menus will appear here once they are configured.</p>
            </div>
        </div>
    @endif
</div>

<script>
function openAddItemModal(menuId) {
    // Implementation for opening add item modal
    console.log('Open add item modal for menu:', menuId);
    alert('Add menu item functionality - to be implemented with modal');
}

function editMenuItem(itemId) {
    // Implementation for editing menu item
    console.log('Edit menu item:', itemId);
    alert('Edit menu item functionality - to be implemented with modal');
}
</script>
