@extends('layouts.app')

@section('title', 'Navigation Manager - ' . $journal->name)

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8" x-data="navigationManager()">
    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-8">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Navigation Manager</h1>
            <p class="mt-1 text-sm text-gray-500">Customize your journal's navigation menus and links.</p>
        </div>
        <div class="mt-4 sm:mt-0">
            <a href="{{ route('journal.settings.index', $journal->slug) }}"
                class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 text-sm font-medium rounded-lg text-gray-700 hover:bg-gray-50 shadow-sm transition-colors">
                <i class="fa-solid fa-arrow-left mr-2"></i>
                Back to Settings
            </a>
        </div>
    </div>

    {{-- Menu Tabs --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="border-b border-gray-200">
            <nav class="flex overflow-x-auto" aria-label="Menu Tabs">
                @foreach($menus as $index => $menu)
                <button @click="activeMenu = '{{ $menu->id }}'"
                    :class="activeMenu === '{{ $menu->id }}' ? 'border-primary-500 text-primary-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                    class="flex-shrink-0 px-6 py-4 text-sm font-medium border-b-2 transition-colors whitespace-nowrap">
                    <i class="fa-solid {{ $menu->location === 'primary' ? 'fa-bars' : ($menu->location === 'user_top' ? 'fa-user' : ($menu->location === 'footer' ? 'fa-shoe-prints' : 'fa-columns')) }} mr-2"></i>
                    {{ $menu->name }}
                </button>
                @endforeach
            </nav>
        </div>

        {{-- Menu Content --}}
        @foreach($menus as $menu)
        <div x-show="activeMenu === '{{ $menu->id }}'" x-cloak class="p-6">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- Left: Add Menu Item Panel --}}
                <div class="lg:col-span-1">
                    <div class="bg-gray-50 rounded-xl p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">
                            <i class="fa-solid fa-plus-circle mr-2 text-primary-600"></i>
                            Add Menu Item
                        </h3>

                        <form @submit.prevent="addItem('{{ $menu->id }}')">
                            {{-- Item Type --}}
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                                <select x-model="newItem.type" class="w-full rounded-lg border-gray-300">
                                    <option value="custom">Custom Link</option>
                                    <option value="route">Route (Page)</option>
                                    <option value="divider">Divider</option>
                                </select>
                            </div>

                            {{-- Label --}}
                            <div class="mb-4" x-show="newItem.type !== 'divider'">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Label *</label>
                                <input type="text" x-model="newItem.label" class="w-full rounded-lg border-gray-300"
                                    placeholder="Menu item text">
                            </div>

                            {{-- Custom URL --}}
                            <div class="mb-4" x-show="newItem.type === 'custom'">
                                <label class="block text-sm font-medium text-gray-700 mb-1">URL</label>
                                <input type="text" x-model="newItem.url" class="w-full rounded-lg border-gray-300"
                                    placeholder="https://example.com">
                            </div>

                            {{-- Route Selector --}}
                            <div class="mb-4" x-show="newItem.type === 'route'">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Page</label>
                                <select x-model="newItem.route_name" class="w-full rounded-lg border-gray-300">
                                    <option value="">Select a page...</option>
                                    @foreach($availableRoutes as $route)
                                    <option value="{{ $route['name'] }}">{{ $route['label'] }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Icon --}}
                            <div class="mb-4" x-show="newItem.type !== 'divider'">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Icon (optional)</label>
                                <input type="text" x-model="newItem.icon" class="w-full rounded-lg border-gray-300"
                                    placeholder="fa-solid fa-home">
                                <p class="mt-1 text-xs text-gray-500">Font Awesome icon class</p>
                            </div>

                            {{-- Target --}}
                            <div class="mb-4" x-show="newItem.type !== 'divider'">
                                <label class="flex items-center gap-2">
                                    <input type="checkbox" x-model="newItem.openNewTab"
                                        class="rounded border-gray-300 text-primary-600">
                                    <span class="text-sm text-gray-700">Open in new tab</span>
                                </label>
                            </div>

                            <button type="submit"
                                class="w-full px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors">
                                <i class="fa-solid fa-plus mr-2"></i>
                                Add Item
                            </button>
                        </form>
                    </div>
                </div>

                {{-- Right: Menu Structure --}}
                <div class="lg:col-span-2">
                    <div class="bg-white border border-gray-200 rounded-xl">
                        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-gray-900">
                                <i class="fa-solid fa-list mr-2 text-gray-600"></i>
                                Menu Structure
                            </h3>
                            <span class="text-xs text-gray-500">Drag items to reorder</span>
                        </div>

                        <div class="p-4 min-h-[300px]"
                            x-ref="menuList_{{ $menu->id }}"
                            x-init="initSortable($refs['menuList_{{ $menu->id }}'], '{{ $menu->id }}')">

                            @if($menu->items->isEmpty())
                            <div class="text-center py-12 text-gray-400">
                                <i class="fa-solid fa-inbox text-4xl mb-3"></i>
                                <p>No menu items yet</p>
                                <p class="text-sm">Add items using the form on the left</p>
                            </div>
                            @else
                            @foreach($menu->items->whereNull('parent_id')->sortBy('order') as $item)
                            <div class="menu-item bg-gray-50 rounded-lg p-3 mb-2 cursor-move hover:bg-gray-100 transition-colors"
                                data-id="{{ $item->id }}">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <i class="fa-solid fa-grip-vertical text-gray-400"></i>
                                        @if($item->icon)
                                        <i class="{{ $item->icon }} text-gray-600"></i>
                                        @endif
                                        <span class="font-medium text-gray-900">{{ $item->label }}</span>
                                        @if($item->type === 'divider')
                                        <span class="px-2 py-0.5 text-xs bg-gray-200 text-gray-600 rounded">Divider</span>
                                        @endif
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <button type="button" @click="editItem({{ json_encode($item) }})"
                                            class="p-1.5 text-gray-400 hover:text-primary-600 hover:bg-primary-50 rounded transition-colors">
                                            <i class="fa-solid fa-pen-to-square text-sm"></i>
                                        </button>
                                        <button type="button" @click="deleteItem('{{ $item->id }}')"
                                            class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded transition-colors">
                                            <i class="fa-solid fa-trash text-sm"></i>
                                        </button>
                                    </div>
                                </div>

                                {{-- Nested children --}}
                                @if($item->children->isNotEmpty())
                                <div class="ml-8 mt-2 border-l-2 border-gray-200 pl-4">
                                    @foreach($item->children->sortBy('order') as $child)
                                    <div class="menu-item bg-white rounded-lg p-2 mb-1.5 cursor-move hover:bg-gray-50 transition-colors"
                                        data-id="{{ $child->id }}">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center gap-2">
                                                <i class="fa-solid fa-grip-vertical text-gray-300 text-sm"></i>
                                                @if($child->icon)
                                                <i class="{{ $child->icon }} text-gray-500 text-sm"></i>
                                                @endif
                                                <span class="text-sm text-gray-700">{{ $child->label }}</span>
                                            </div>
                                            <div class="flex items-center gap-1">
                                                <button type="button" @click="editItem({{ json_encode($child) }})"
                                                    class="p-1 text-gray-400 hover:text-primary-600 rounded">
                                                    <i class="fa-solid fa-pen-to-square text-xs"></i>
                                                </button>
                                                <button type="button" @click="deleteItem('{{ $child->id }}')"
                                                    class="p-1 text-gray-400 hover:text-red-600 rounded">
                                                    <i class="fa-solid fa-trash text-xs"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                                @endif
                            </div>
                            @endforeach
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Edit Item Modal --}}
    <div x-show="showEditModal" x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex min-h-screen items-center justify-center p-4">
            <div class="fixed inset-0 bg-black/50" @click="showEditModal = false"></div>
            <div class="relative bg-white rounded-xl shadow-xl w-full max-w-md p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-semibold text-gray-900">Edit Menu Item</h3>
                    <button type="button" @click="showEditModal = false" class="text-gray-400 hover:text-gray-600">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>

                <form @submit.prevent="updateItem()">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Label</label>
                            <input type="text" x-model="editingItem.label" class="w-full rounded-lg border-gray-300">
                        </div>
                        <div x-show="editingItem.type === 'custom'">
                            <label class="block text-sm font-medium text-gray-700 mb-1">URL</label>
                            <input type="text" x-model="editingItem.url" class="w-full rounded-lg border-gray-300">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Icon</label>
                            <input type="text" x-model="editingItem.icon" class="w-full rounded-lg border-gray-300">
                        </div>
                        <div>
                            <label class="flex items-center gap-2">
                                <input type="checkbox" x-model="editingItem.is_active"
                                    class="rounded border-gray-300 text-primary-600">
                                <span class="text-sm text-gray-700">Active</span>
                            </label>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 mt-6 pt-4 border-t border-gray-200">
                        <button type="button" @click="showEditModal = false"
                            class="px-4 py-2 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50">
                            Cancel
                        </button>
                        <button type="submit"
                            class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg">
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
    function navigationManager() {
        return {
            activeMenu: '{{ $menus->first()->id ?? '
            ' }}',
            showEditModal: false,
            newItem: {
                type: 'custom',
                label: '',
                url: '',
                route_name: '',
                icon: '',
                openNewTab: false
            },
            editingItem: {},

            initSortable(el, menuId) {
                if (!el) return;
                new Sortable(el, {
                    animation: 150,
                    handle: '.fa-grip-vertical',
                    ghostClass: 'opacity-50',
                    onEnd: (evt) => {
                        this.saveOrder(menuId, el);
                    }
                });
            },

            async saveOrder(menuId, container) {
                const items = container.querySelectorAll('.menu-item');
                const order = Array.from(items).map((item, index) => ({
                    id: item.dataset.id,
                    order: index,
                    parent_id: null // Simplified - no nested reorder support in this version
                }));

                try {
                    const response = await fetch('{{ route("journal.settings.navigation.items.reorder", $journal->slug) }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            items: order
                        })
                    });
                    const data = await response.json();
                    if (!data.success) throw new Error(data.message);
                } catch (error) {
                    console.error('Reorder failed:', error);
                }
            },

            async addItem(menuId) {
                const payload = {
                    menu_id: menuId,
                    type: this.newItem.type,
                    label: this.newItem.type === 'divider' ? '---' : this.newItem.label,
                    url: this.newItem.type === 'custom' ? this.newItem.url : null,
                    route_name: this.newItem.type === 'route' ? this.newItem.route_name : null,
                    icon: this.newItem.icon || null,
                    target: this.newItem.openNewTab ? '_blank' : '_self'
                };

                try {
                    const response = await fetch('{{ route("journal.settings.navigation.items.store", $journal->slug) }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify(payload)
                    });
                    const data = await response.json();
                    if (data.success) {
                        location.reload();
                    } else {
                        alert(data.message || 'Failed to add item');
                    }
                } catch (error) {
                    console.error('Add failed:', error);
                    alert('Failed to add item');
                }
            },

            editItem(item) {
                this.editingItem = {
                    ...item
                };
                this.showEditModal = true;
            },

            async updateItem() {
                try {
                    const response = await fetch(`{{ url($journal->slug . '/settings/navigation/items') }}/${this.editingItem.id}`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify(this.editingItem)
                    });
                    const data = await response.json();
                    if (data.success) {
                        location.reload();
                    } else {
                        alert(data.message || 'Failed to update item');
                    }
                } catch (error) {
                    console.error('Update failed:', error);
                    alert('Failed to update item');
                }
            },

            async deleteItem(itemId) {
                if (!confirm('Are you sure you want to delete this menu item?')) return;

                try {
                    const response = await fetch(`{{ url($journal->slug . '/settings/navigation/items') }}/${itemId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    });
                    const data = await response.json();
                    if (data.success) {
                        location.reload();
                    } else {
                        alert(data.message || 'Failed to delete item');
                    }
                } catch (error) {
                    console.error('Delete failed:', error);
                    alert('Failed to delete item');
                }
            }
        };
    }
</script>
@endpush