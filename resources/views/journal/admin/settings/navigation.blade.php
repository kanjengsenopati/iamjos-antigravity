@extends('layouts.app')

@section('title', 'Navigation Manager - ' . $journal->name)

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8" x-data="{ 
    showMenuModal: false,
    showItemModal: false,
    showAssignModal: false,
    editingMenu: null,
    editingItem: null,
    selectedMenuId: null,
    itemType: 'custom'
}">

    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-8">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Navigation Manager</h1>
            <p class="mt-1 text-sm text-gray-500">Configure navigation menus and menu items for your journal. OJS 3.3 compatible.</p>
        </div>
        <div class="mt-4 sm:mt-0">
            <a href="{{ route('journal.settings.index', $journal->slug) }}"
                class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 text-sm font-medium rounded-lg text-gray-700 hover:bg-gray-50 shadow-sm transition-colors">
                <i class="fa-solid fa-arrow-left mr-2"></i>
                Back to Settings
            </a>
        </div>
    </div>

    {{-- Success Message --}}
    @if(session('success'))
    <div class="mb-6 bg-green-50 border border-green-200 rounded-lg p-4">
        <div class="flex items-center">
            <i class="fa-solid fa-check-circle text-green-500 mr-3"></i>
            <span class="text-green-800">{{ session('success') }}</span>
        </div>
    </div>
    @endif

    {{-- SECTION 1: NAVIGATION MENUS --}}
    <div class="bg-white border border-slate-200 shadow-sm rounded-xl p-6 mb-8">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h3 class="font-bold text-lg text-slate-800">Navigation Menus</h3>
                <p class="text-sm text-slate-500 mt-1">Create menus and assign them to theme areas.</p>
            </div>
            <button @click="showMenuModal = true; editingMenu = null"
                class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors">
                <i class="fa-solid fa-plus mr-2"></i>
                Add Menu
            </button>
        </div>

        @if($menus->isEmpty())
        <div class="text-center py-12 text-slate-400">
            <i class="fa-solid fa-bars text-4xl mb-3"></i>
            <p class="font-medium">No navigation menus yet</p>
            <p class="text-sm">Create a menu to get started.</p>
        </div>
        @else
        <table class="w-full text-sm text-left">
            <thead class="bg-slate-50 text-slate-500 uppercase font-bold text-xs rounded-lg">
                <tr>
                    <th class="px-4 py-3 rounded-l-lg">Title</th>
                    <th class="px-4 py-3">Assigned Area</th>
                    <th class="px-4 py-3">Items</th>
                    <th class="px-4 py-3 text-right rounded-r-lg">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach($menus as $menu)
                <tr class="hover:bg-slate-50 transition-colors">
                    <td class="px-4 py-3">
                        <button @click="selectedMenuId = '{{ $menu->id }}'; showAssignModal = true"
                            class="font-medium text-indigo-600 hover:text-indigo-800 hover:underline">
                            {{ $menu->title }}
                        </button>
                    </td>
                    <td class="px-4 py-3">
                        @if($menu->area_name === 'primary')
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-800">
                                Primary Navigation
                            </span>
                        @elseif($menu->area_name === 'user')
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-purple-100 text-purple-800">
                                User Navigation
                            </span>
                        @elseif($menu->area_name)
                            {{-- Legacy area (footer, etc) - show as deprecated --}}
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">
                                {{ ucfirst($menu->area_name) }} (deprecated)
                            </span>
                        @else
                            <span class="text-slate-400 italic text-xs">Unassigned</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-slate-600 text-xs max-w-xs truncate">
                        {{ $menu->items_preview }}
                    </td>
                    <td class="px-4 py-3 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <button @click="selectedMenuId = '{{ $menu->id }}'; showAssignModal = true"
                                class="p-2 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors"
                                title="Manage Items">
                                <i class="fa-solid fa-list-check"></i>
                            </button>
                            <button @click="editingMenu = {{ json_encode($menu) }}; showMenuModal = true"
                                class="p-2 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors"
                                title="Edit Menu">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </button>
                            <form action="{{ route('journal.settings.navigation.menus.destroy', [$journal->slug, $menu->id]) }}" method="POST" class="inline"
                                onsubmit="return confirm('Are you sure you want to delete this menu?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-2 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>

    {{-- SECTION 2: NAVIGATION MENU ITEMS --}}
    <div class="bg-white border border-slate-200 shadow-sm rounded-xl p-6">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h3 class="font-bold text-lg text-slate-800">Navigation Menu Items</h3>
                <p class="text-sm text-slate-500 mt-1">Create reusable menu items that can be assigned to any menu.</p>
            </div>
            <button @click="showItemModal = true; editingItem = null; itemType = 'custom'"
                class="inline-flex items-center px-4 py-2 bg-slate-600 hover:bg-slate-700 text-white text-sm font-medium rounded-lg transition-colors">
                <i class="fa-solid fa-plus mr-2"></i>
                Add Item
            </button>
        </div>

        @if($items->isEmpty())
        <div class="text-center py-12 text-slate-400">
            <i class="fa-solid fa-link text-4xl mb-3"></i>
            <p class="font-medium">No menu items yet</p>
            <p class="text-sm">Create items to add them to your menus.</p>
        </div>
        @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($items as $item)
            @php
                $isSystemItem = !empty($item->is_system) || str_starts_with((string)$item->id, 'system_');
            @endphp
            <div class="flex justify-between items-center p-4 {{ $isSystemItem ? 'bg-blue-50 border-blue-200' : 'bg-slate-50 border-slate-200' }} border rounded-xl hover:bg-slate-100 transition-colors group">
                <div class="flex items-center gap-3">
                    @if($item->icon)
                    <i class="{{ $item->icon }} {{ $isSystemItem ? 'text-blue-500' : 'text-slate-400' }}"></i>
                    @else
                    <i class="fa-solid fa-link text-slate-300"></i>
                    @endif
                    <div>
                        <span class="font-medium text-slate-700 block">
                            {{ $item->title }}
                            @if($isSystemItem)
                                <span class="ml-1 inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-700">System</span>
                            @endif
                        </span>
                        <span class="text-xs text-slate-400">
                            {{ ucfirst($item->type) }}
                            @if($item->type === 'route' && $item->route_name)
                                — {{ $item->route_name }}
                            @elseif($item->type === 'custom' && $item->url)
                                — {{ Str::limit($item->url, 30) }}
                            @endif
                        </span>
                    </div>
                </div>
                @if(!$isSystemItem)
                <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                    <button @click="editingItem = {{ json_encode($item) }}; itemType = '{{ $item->type }}'; showItemModal = true"
                        class="p-2 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors">
                        <i class="fa-solid fa-pen-to-square text-sm"></i>
                    </button>
                    <form action="{{ route('journal.settings.navigation.items.destroy', [$journal->slug, $item->id]) }}" method="POST" class="inline"
                        onsubmit="return confirm('Are you sure? This will remove the item from all menus.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="p-2 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                            <i class="fa-solid fa-trash text-sm"></i>
                        </button>
                    </form>
                </div>
                @endif
            </div>
            @endforeach
        </div>
        @endif
    </div>

    {{-- MODAL: CREATE/EDIT MENU --}}
    <div x-show="showMenuModal" x-cloak 
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100">
        <div class="bg-white w-full max-w-lg rounded-xl shadow-2xl overflow-hidden" @click.outside="showMenuModal = false">
            <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50">
                <h3 class="font-bold text-lg text-slate-800">
                    <i class="fa-solid fa-bars mr-2 text-indigo-600"></i>
                    <span x-text="editingMenu ? 'Edit Menu' : 'Create Menu'"></span>
                </h3>
                <button @click="showMenuModal = false" class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-200 rounded-lg transition-colors">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <form :action="editingMenu ? '{{ route('journal.settings.navigation.menus.update', [$journal->slug, '__MENU_ID__']) }}'.replace('__MENU_ID__', editingMenu.id) : '{{ route('journal.settings.navigation.menus.store', $journal->slug) }}'" method="POST">
                @csrf
                <input type="hidden" name="_method" :value="editingMenu ? 'PUT' : 'POST'">
                
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Menu Title *</label>
                        <input type="text" name="title" required
                            :value="editingMenu?.title || ''"
                            class="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500"
                            placeholder="e.g., Primary Navigation">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Active Theme Area</label>
                        <select name="area_name" class="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">-- Not Assigned --</option>
                            <option value="primary" :selected="editingMenu?.area_name === 'primary'">Primary Navigation Menu (Header)</option>
                            <option value="user" :selected="editingMenu?.area_name === 'user'">User Navigation Menu (Top Right)</option>
                        </select>
                        <p class="text-xs text-slate-500 mt-1">Only one menu can be assigned to each area.</p>
                    </div>
                </div>

                <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex justify-end gap-3">
                    <button type="button" @click="showMenuModal = false"
                        class="px-4 py-2 bg-white border border-slate-300 text-slate-700 text-sm font-medium rounded-lg hover:bg-slate-50 transition-colors">
                        Cancel
                    </button>
                    <button type="submit"
                        class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors">
                        <i class="fa-solid fa-check mr-2"></i>
                        <span x-text="editingMenu ? 'Save Changes' : 'Create Menu'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL: CREATE/EDIT ITEM --}}
    <div x-show="showItemModal" x-cloak 
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100">
        <div class="bg-white w-full max-w-lg rounded-xl shadow-2xl overflow-hidden" @click.outside="showItemModal = false">
            <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50">
                <h3 class="font-bold text-lg text-slate-800">
                    <i class="fa-solid fa-link mr-2 text-indigo-600"></i>
                    <span x-text="editingItem ? 'Edit Menu Item' : 'Create Menu Item'"></span>
                </h3>
                <button @click="showItemModal = false" class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-200 rounded-lg transition-colors">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <form :action="editingItem ? '{{ route('journal.settings.navigation.items.update', [$journal->slug, '__ITEM_ID__']) }}'.replace('__ITEM_ID__', editingItem.id) : '{{ route('journal.settings.navigation.items.store', $journal->slug) }}'" method="POST">
                @csrf
                <input type="hidden" name="_method" :value="editingItem ? 'PUT' : 'POST'">
                
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Title *</label>
                        <input type="text" name="title" required
                            :value="editingItem?.title || ''"
                            class="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500"
                            placeholder="e.g., About Us">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Navigation Page Type</label>
                        <select name="type" x-model="itemType" class="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="custom">Custom Link</option>
                            <option value="route">System Page</option>
                            <option value="page">Custom Page</option>
                        </select>
                    </div>
                    
                    <div x-show="itemType === 'custom'">
                        <label class="block text-sm font-medium text-slate-700 mb-1">URL</label>
                        <input type="text" name="url"
                            :value="editingItem?.url || ''"
                            class="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500"
                            placeholder="https://example.com">
                    </div>
                    
                    <div x-show="itemType === 'route'">
                        <label class="block text-sm font-medium text-slate-700 mb-1">System Page</label>
                        <select name="route_name" class="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">-- Select a page --</option>
                            @foreach($availableRoutes as $route)
                            <option value="{{ $route['name'] }}">{{ $route['label'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Icon (optional)</label>
                        <input type="text" name="icon"
                            :value="editingItem?.icon || ''"
                            class="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500"
                            placeholder="fa-solid fa-info-circle">
                        <p class="text-xs text-slate-500 mt-1">Font Awesome icon class.</p>
                    </div>
                    
                    <div>
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="target" value="_blank"
                                :checked="editingItem?.target === '_blank'"
                                class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                            <span class="text-sm text-slate-700">Open in new tab</span>
                        </label>
                    </div>
                </div>

                <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex justify-end gap-3">
                    <button type="button" @click="showItemModal = false"
                        class="px-4 py-2 bg-white border border-slate-300 text-slate-700 text-sm font-medium rounded-lg hover:bg-slate-50 transition-colors">
                        Cancel
                    </button>
                    <button type="submit"
                        class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors">
                        <i class="fa-solid fa-check mr-2"></i>
                        <span x-text="editingItem ? 'Save Changes' : 'Create Item'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL: ASSIGN ITEMS TO MENU --}}
    <div x-show="showAssignModal" x-cloak 
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100">
        <div class="bg-white w-full max-w-5xl rounded-xl shadow-2xl overflow-hidden max-h-[90vh] flex flex-col" @click.outside="showAssignModal = false">
            <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50">
                <h3 class="font-bold text-lg text-slate-800">
                    <i class="fa-solid fa-list-check mr-2 text-indigo-600"></i>
                    Manage Menu Items
                </h3>
                <button @click="showAssignModal = false" class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-200 rounded-lg transition-colors">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <div class="p-6 overflow-y-auto flex-1">
                @foreach($menus as $menu)
                <div x-show="selectedMenuId === '{{ $menu->id }}'" class="grid grid-cols-2 gap-0 border border-slate-300 rounded-xl overflow-hidden">
                    {{-- Left Column: Assigned Items --}}
                    <div class="border-r border-slate-300 p-4 bg-slate-50">
                        <h4 class="font-bold text-sm text-slate-700 mb-3 flex items-center gap-2">
                            <i class="fa-solid fa-check-circle text-green-500"></i>
                            Assigned Menu Items
                        </h4>
                        <ul class="space-y-2 min-h-[200px] max-h-[400px] overflow-y-auto">
                            @php
                                $assignments = $menu->assignments()->with('item')->orderBy('order')->get();
                            @endphp
                            @forelse($assignments as $assignment)
                            <li class="bg-white border border-slate-200 p-3 rounded-lg flex justify-between items-center shadow-sm hover:shadow transition-shadow">
                                <div class="flex items-center gap-2">
                                    @if($assignment->item?->icon)
                                    <i class="{{ $assignment->item->icon }} text-slate-400 text-sm"></i>
                                    @endif
                                    <span class="text-sm font-medium text-slate-700">{{ $assignment->item?->title ?? 'Unknown' }}</span>
                                </div>
                                <div class="flex items-center gap-1">
                                    <form action="{{ route('journal.settings.navigation.move-up', [$journal->slug, $assignment->id]) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="p-1.5 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 rounded transition-colors" title="Move Up">
                                            <i class="fa-solid fa-chevron-up text-xs"></i>
                                        </button>
                                    </form>
                                    <form action="{{ route('journal.settings.navigation.move-down', [$journal->slug, $assignment->id]) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="p-1.5 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 rounded transition-colors" title="Move Down">
                                            <i class="fa-solid fa-chevron-down text-xs"></i>
                                        </button>
                                    </form>
                                    <form action="{{ route('journal.settings.navigation.unassign', [$journal->slug, $assignment->id]) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 text-red-400 hover:text-red-600 hover:bg-red-50 rounded transition-colors ml-1" title="Remove from menu">
                                            <i class="fa-solid fa-times text-xs"></i>
                                        </button>
                                    </form>
                                </div>
                            </li>
                            @empty
                            <li class="text-center py-8 text-slate-400">
                                <i class="fa-solid fa-inbox text-2xl mb-2"></i>
                                <p class="text-xs">No items assigned.</p>
                                <p class="text-xs">Use the arrows to add items →</p>
                            </li>
                            @endforelse
                        </ul>
                    </div>

                    {{-- Right Column: Unassigned Items --}}
                    <div class="p-4 bg-white">
                        <h4 class="font-bold text-sm text-slate-700 mb-3 flex items-center gap-2">
                            <i class="fa-solid fa-list text-slate-400"></i>
                            Available Menu Items
                        </h4>
                        <ul class="space-y-2 min-h-[200px] max-h-[400px] overflow-y-auto">
                            @php
                                $assignedItemIds = $assignments->pluck('menu_item_id')->toArray();
                                $assignedRouteNames = $assignments->pluck('item.route_name')->filter()->toArray();
                                
                                $unassignedItems = $items->filter(function($item) use ($assignedItemIds, $assignedRouteNames) {
                                    $itemId = (string) $item->id;
                                    
                                    // If it's a real DB item, check by ID
                                    if (!str_starts_with($itemId, 'system_')) {
                                        return !in_array($item->id, $assignedItemIds);
                                    }
                                    
                                    // If it's a virtual system item, check by route_name
                                    return !in_array($item->route_name, $assignedRouteNames);
                                });
                            @endphp
                            @forelse($unassignedItems as $item)
                            @php
                                $isSystemItem = !empty($item->is_system);
                                $isVirtualItem = str_starts_with((string)$item->id, 'system_');
                            @endphp
                            <li class="flex items-center gap-3 p-3 border-b border-slate-100 last:border-0 hover:bg-slate-50 rounded-lg transition-colors">
                                <form action="{{ route('journal.settings.navigation.assign', $journal->slug) }}" method="POST" class="inline">
                                    @csrf
                                    <input type="hidden" name="menu_id" value="{{ $menu->id }}">
                                    <input type="hidden" name="menu_item_id" value="{{ $item->id }}">
                                    @if($isVirtualItem && $item->route_name)
                                    <input type="hidden" name="route_name" value="{{ $item->route_name }}">
                                    @endif
                                    <button type="submit" class="flex items-center gap-1 px-2.5 py-1.5 bg-indigo-100 hover:bg-indigo-200 text-indigo-700 text-xs font-medium rounded-lg transition-colors">
                                        <i class="fa-solid fa-arrow-left"></i>
                                        Add
                                    </button>
                                </form>
                                <div class="flex items-center gap-2 flex-1">
                                    @if($item->icon)
                                    <i class="{{ $item->icon }} {{ $isSystemItem ? 'text-blue-500' : 'text-slate-400' }} text-sm"></i>
                                    @endif
                                    <span class="text-sm text-slate-600">{{ $item->title }}</span>
                                    <span class="text-xs {{ $isSystemItem ? 'text-blue-500' : 'text-slate-400' }}">({{ $isSystemItem ? 'System' : ucfirst($item->type) }})</span>
                                </div>
                            </li>
                            @empty
                            <li class="text-center py-8 text-slate-400">
                                <i class="fa-solid fa-check-double text-2xl mb-2"></i>
                                <p class="text-xs">All items are assigned!</p>
                            </li>
                            @endforelse
                        </ul>
                    </div>
                </div>
                @endforeach
            </div>

            <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex justify-end">
                <button type="button" @click="showAssignModal = false"
                    class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors">
                    Done
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
