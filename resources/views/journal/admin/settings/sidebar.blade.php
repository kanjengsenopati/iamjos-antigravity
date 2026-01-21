@extends('layouts.app')

@section('title', 'Sidebar Manager - ' . $journal->name)

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8" x-data="sidebarManager()">
    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-8">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Sidebar Manager</h1>
            <p class="mt-1 text-sm text-gray-500">Customize sidebar widgets for your journal's public pages.</p>
        </div>
        <div class="mt-4 sm:mt-0 flex gap-2">
            <button @click="openAddModal()"
                class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors">
                <i class="fa-solid fa-plus mr-2"></i>
                Add Custom Block
            </button>
            <a href="{{ route('journal.settings.index', $journal->slug) }}"
                class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 text-sm font-medium rounded-lg text-gray-700 hover:bg-gray-50 shadow-sm transition-colors">
                <i class="fa-solid fa-arrow-left mr-2"></i>
                Back to Settings
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Left Column: Available System Blocks --}}
        <div class="lg:col-span-1">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h3 class="text-lg font-semibold text-gray-900">
                        <i class="fa-solid fa-puzzle-piece mr-2 text-indigo-600"></i>
                        Available Blocks
                    </h3>
                    <p class="mt-1 text-sm text-gray-500">Click to add to sidebar</p>
                </div>
                <div class="p-4 space-y-2">
                    @forelse($availableSystemBlocks as $key => $block)
                    <button @click="addSystemBlock('{{ $key }}')"
                        class="w-full flex items-center gap-3 p-3 bg-gray-50 hover:bg-indigo-50 rounded-lg transition-colors text-left group">
                        <div class="w-10 h-10 bg-white rounded-lg shadow-sm flex items-center justify-center group-hover:bg-indigo-100">
                            <i class="{{ $block['icon'] }} text-gray-600 group-hover:text-indigo-600"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-medium text-gray-900 text-sm">{{ $block['name'] }}</p>
                            <p class="text-xs text-gray-500 truncate">{{ $block['description'] }}</p>
                        </div>
                        <i class="fa-solid fa-plus text-gray-400 group-hover:text-indigo-600"></i>
                    </button>
                    @empty
                    <div class="text-center py-8 text-gray-400">
                        <i class="fa-solid fa-check-circle text-3xl mb-2 text-green-500"></i>
                        <p class="text-sm">All system blocks are active</p>
                    </div>
                    @endforelse

                    {{-- Inactive Custom Blocks --}}
                    @if($inactiveBlocks->isNotEmpty())
                    <div class="pt-4 mt-4 border-t border-gray-200">
                        <p class="text-xs text-gray-500 uppercase tracking-wide font-medium mb-2">Inactive Blocks</p>
                        @foreach($inactiveBlocks as $block)
                        <div class="flex items-center gap-3 p-3 bg-gray-100 rounded-lg mb-2">
                            <div class="w-8 h-8 bg-gray-200 rounded-lg flex items-center justify-center">
                                <i class="{{ $block->icon ?? 'fa-solid fa-cube' }} text-gray-400 text-sm"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="font-medium text-gray-600 text-sm">{{ $block->title }}</p>
                            </div>
                            <button @click="toggleBlock('{{ $block->id }}')"
                                class="p-1.5 text-gray-400 hover:text-green-600 hover:bg-green-50 rounded">
                                <i class="fa-solid fa-toggle-off text-lg"></i>
                            </button>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Right Column: Active Sidebar Blocks --}}
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-indigo-50 to-purple-50">
                    <h3 class="text-lg font-semibold text-gray-900">
                        <i class="fa-solid fa-columns mr-2 text-indigo-600"></i>
                        Active Sidebar Blocks
                    </h3>
                    <p class="mt-1 text-sm text-gray-500">Drag to reorder • Click toggle to enable/disable</p>
                </div>

                <div class="p-4 min-h-[400px]" x-ref="activeBlocks" x-init="initSortable($refs.activeBlocks)">
                    @forelse($activeBlocks as $block)
                    <div class="sidebar-block bg-white border border-gray-200 rounded-xl p-4 mb-3 hover:shadow-md transition-all cursor-move"
                        data-id="{{ $block->id }}">
                        <div class="flex items-center gap-4">
                            {{-- Drag Handle --}}
                            <div class="flex-shrink-0">
                                <i class="fa-solid fa-grip-vertical text-gray-400 cursor-move"></i>
                            </div>

                            {{-- Icon --}}
                            <div class="w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0
                                {{ $block->is_system ? 'bg-gradient-to-br from-indigo-100 to-purple-100' : 'bg-gradient-to-br from-amber-100 to-orange-100' }}">
                                <i class="{{ $block->icon ?? 'fa-solid fa-cube' }} 
                                    {{ $block->is_system ? 'text-indigo-600' : 'text-orange-600' }}"></i>
                            </div>

                            {{-- Content --}}
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <h4 class="font-semibold text-gray-900">{{ $block->title }}</h4>
                                    @if($block->is_system)
                                    <span class="px-2 py-0.5 text-xs bg-indigo-100 text-indigo-700 rounded-full">System</span>
                                    @else
                                    <span class="px-2 py-0.5 text-xs bg-orange-100 text-orange-700 rounded-full">Custom</span>
                                    @endif
                                </div>
                                <p class="text-sm text-gray-500 mt-0.5">
                                    @if($block->is_system)
                                    {{ $block->component_name }}
                                    @else
                                    HTML Block
                                    @endif
                                </p>
                            </div>

                            {{-- Actions --}}
                            <div class="flex items-center gap-2 flex-shrink-0">
                                @if(!$block->is_system)
                                <button @click="editBlock({{ json_encode($block) }})"
                                    class="p-2 text-gray-400 hover:text-primary-600 hover:bg-primary-50 rounded-lg transition-colors">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                                @endif
                                <button @click="toggleBlock('{{ $block->id }}')"
                                    class="p-2 text-green-500 hover:text-gray-400 hover:bg-gray-50 rounded-lg transition-colors">
                                    <i class="fa-solid fa-toggle-on text-lg"></i>
                                </button>
                                <button @click="deleteBlock('{{ $block->id }}')"
                                    class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-16 text-gray-400">
                        <i class="fa-solid fa-inbox text-5xl mb-4"></i>
                        <p class="text-lg font-medium">No active sidebar blocks</p>
                        <p class="text-sm mt-1">Add blocks from the left panel to customize your sidebar</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- Add Custom Block Modal --}}
    <div x-show="showAddCustomModal" x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex min-h-screen items-center justify-center p-4">
            <div class="fixed inset-0 bg-black/50" @click="showAddCustomModal = false"></div>
            <div class="relative bg-white rounded-xl shadow-xl w-full max-w-2xl p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-semibold text-gray-900">
                        <i class="fa-solid fa-code mr-2 text-orange-600"></i>
                        Add Custom HTML Block
                    </h3>
                    <button type="button" @click="showAddCustomModal = false" class="text-gray-400 hover:text-gray-600">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>

                <form @submit.prevent="addCustomBlock()">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Block Title *</label>
                            <input type="text" x-model="newBlock.title" class="w-full rounded-lg border-gray-300"
                                placeholder="e.g., Sponsors, Quick Links">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Icon (optional)</label>
                            <input type="text" x-model="newBlock.icon" class="w-full rounded-lg border-gray-300"
                                placeholder="fa-solid fa-star">
                            <p class="mt-1 text-xs text-gray-500">Font Awesome icon class</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">HTML Content *</label>
                            <textarea id="new-block-content" x-model="newBlock.content" rows="8" class="w-full rounded-lg border-gray-300 font-mono text-sm"
                                placeholder="<p>Your custom HTML content here...</p>"></textarea>
                            <p class="mt-1 text-xs text-gray-500">You can use HTML tags for formatting</p>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 mt-6 pt-4 border-t border-gray-200">
                        <button type="button" @click="showAddCustomModal = false"
                            class="px-4 py-2 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50">
                            Cancel
                        </button>
                        <button type="submit"
                            class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg">
                            Add Block
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Edit Block Modal --}}
    <div x-show="showEditModal" x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex min-h-screen items-center justify-center p-4">
            <div class="fixed inset-0 bg-black/50" @click="showEditModal = false"></div>
            <div class="relative bg-white rounded-xl shadow-xl w-full max-w-2xl p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-semibold text-gray-900">Edit Block</h3>
                    <button type="button" @click="showEditModal = false" class="text-gray-400 hover:text-gray-600">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>

                <form @submit.prevent="updateBlock()">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Block Title</label>
                            <input type="text" x-model="editingBlock.title" class="w-full rounded-lg border-gray-300">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Icon</label>
                            <input type="text" x-model="editingBlock.icon" class="w-full rounded-lg border-gray-300">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">HTML Content</label>
                            <textarea id="edit-block-content" x-model="editingBlock.content" rows="8" class="w-full rounded-lg border-gray-300 font-mono text-sm"></textarea>
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
    <script src="{{ asset('assets/js/vendors/plugins/tinymce/tinymce.min.js') }}"></script>
<script>
    function sidebarManager() {
        return {
            showAddCustomModal: false,
            showEditModal: false,
            newBlock: {
                title: '',
                icon: '',
                content: ''
            },
            editingBlock: {},

            initSortable(el) {
                if (!el) return;
                new Sortable(el, {
                    animation: 150,
                    handle: '.fa-grip-vertical',
                    ghostClass: 'opacity-50',
                    onEnd: () => this.saveOrder(el)
                });
            },

            // TinyMCE Initialization with Image Upload
            initTinyMCE(selector, initialContent = '') {
                // Remove existing instance if any
                if (tinymce.get(selector)) {
                    tinymce.get(selector).remove();
                }

                tinymce.init({
                    selector: '#' + selector,
                    height: 300,
                    menubar: false,
                    branding: false,
                    license_key: 'gpl',
                    plugins: 'advlist autolink lists link image charmap preview anchor searchreplace visualblocks code fullscreen insertdatetime media table code help wordcount',
                    toolbar: 'undo redo | blocks | bold italic backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | image link code',
                    content_style: 'body { font-family:Inter,sans-serif; font-size:14px }',
                    setup: function(editor) {
                        editor.on('init', function() {
                            editor.setContent(initialContent);
                        });
                    },
                    // Image Upload Handler
                    images_upload_handler: (blobInfo, progress) => new Promise((resolve, reject) => {
                        const xhr = new XMLHttpRequest();
                        xhr.withCredentials = false;
                        xhr.open('POST', '{{ route('journal.upload.image', $journal->slug) }}');
                        xhr.setRequestHeader('X-CSRF-TOKEN', '{{ csrf_token() }}');

                        xhr.upload.onprogress = (e) => {
                            progress(e.loaded / e.total * 100);
                        };

                        xhr.onload = () => {
                            if (xhr.status === 403) {
                                reject({
                                    message: 'HTTP Error: ' + xhr.status,
                                    remove: true
                                });
                                return;
                            }
                            if (xhr.status < 200 || xhr.status >= 300) {
                                reject('HTTP Error: ' + xhr.status);
                                return;
                            }

                            const json = JSON.parse(xhr.responseText);
                            if (!json || typeof json.location != 'string') {
                                reject('Invalid JSON: ' + xhr.responseText);
                                return;
                            }
                            resolve(json.location);
                        };

                        xhr.onerror = () => {
                            reject('Image upload failed due to a XHR Transport error. Code: ' + xhr.status);
                        };

                        const formData = new FormData();
                        formData.append('file', blobInfo.blob(), blobInfo.filename());
                        xhr.send(formData);
                    })
                });
            },

            async saveOrder(container) {
                const blocks = container.querySelectorAll('.sidebar-block');
                const order = Array.from(blocks).map((block, index) => ({
                    id: block.dataset.id,
                    order: index,
                    is_active: true
                }));

                try {
                    await fetch('{{ route("journal.settings.sidebar.reorder", $journal->slug) }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            blocks: order
                        })
                    });
                } catch (error) {
                    console.error('Reorder failed:', error);
                }
            },

            async addSystemBlock(blockKey) {
                try {
                    const response = await fetch('{{ route("journal.settings.sidebar.system-block", $journal->slug) }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            block_key: blockKey
                        })
                    });
                    const data = await response.json();
                    if (data.success) {
                        location.reload();
                    } else {
                        alert(data.message || 'Failed to add block');
                    }
                } catch (error) {
                    console.error('Add failed:', error);
                }
            },

            openAddModal() {
                this.showAddCustomModal = true;
                this.newBlock = {
                    title: '',
                    icon: '',
                    content: ''
                };
                // Small delay to ensure modal is rendered
                setTimeout(() => this.initTinyMCE('new-block-content', ''), 50);
            },

            async addCustomBlock() {
                // Get content from TinyMCE
                const content = tinymce.get('new-block-content') ? tinymce.get('new-block-content').getContent() : this.newBlock.content;

                try {
                    const response = await fetch('{{ route("journal.settings.sidebar.store", $journal->slug) }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            type: 'custom',
                            title: this.newBlock.title,
                            icon: this.newBlock.icon || 'fa-solid fa-cube',
                            content: content
                        })
                    });
                    const data = await response.json();
                    if (data.success) {
                        location.reload();
                    } else {
                        alert(data.message || 'Failed to add block');
                    }
                } catch (error) {
                    console.error('Add failed:', error);
                }
            },

            editBlock(block) {
                this.editingBlock = {
                    ...block
                };
                this.showEditModal = true;
                // Init TinyMCE with content
                setTimeout(() => this.initTinyMCE('edit-block-content', block.content || ''), 50);
            },

            async updateBlock() {
                // Get content from TinyMCE
                const content = tinymce.get('edit-block-content') ? tinymce.get('edit-block-content').getContent() : this.editingBlock.content;

                try {
                    const response = await fetch(`{{ url($journal->slug . '/settings/sidebar') }}/${this.editingBlock.id}`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            ...this.editingBlock,
                            content: content
                        })
                    });
                    const data = await response.json();
                    if (data.success) {
                        location.reload();
                    } else {
                        alert(data.message || 'Failed to update block');
                    }
                } catch (error) {
                    console.error('Update failed:', error);
                }
            },

            async toggleBlock(blockId) {
                try {
                    const response = await fetch(`{{ url($journal->slug . '/settings/sidebar') }}/${blockId}/toggle`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    });
                    const data = await response.json();
                    if (data.success) {
                        location.reload();
                    }
                } catch (error) {
                    console.error('Toggle failed:', error);
                }
            },

            async deleteBlock(blockId) {
                if (!confirm('Are you sure you want to delete this block?')) return;

                try {
                    const response = await fetch(`{{ url($journal->slug . '/settings/sidebar') }}/${blockId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    });
                    const data = await response.json();
                    if (data.success) {
                        location.reload();
                    }
                } catch (error) {
                    console.error('Delete failed:', error);
                }
            }
        };
    }
</script>
@endpush