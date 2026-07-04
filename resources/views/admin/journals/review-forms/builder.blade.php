@extends('layouts.app')

@section('title', 'Form Builder - ' . ($journal->abbreviation ?? 'IAMJOS'))

@section('content')
    @php
        $isId = app()->getLocale() === 'id';
    @endphp

    <div x-data="formBuilder()" x-init="init()">
        <!-- Flash Messages -->
        @if (session('success'))
            <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 rounded-lg flex items-center gap-3"
                x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)">
                <i class="fa-solid fa-check-circle text-emerald-600"></i>
                <span class="text-sm text-emerald-800">{{ session('success') }}</span>
                <button @click="show = false" class="ml-auto text-emerald-600 hover:text-emerald-800">
                    <i class="fa-solid fa-times"></i>
                </button>
            </div>
        @endif

        <!-- Header -->
        <div class="mb-6">
            <div class="flex items-center gap-3 mb-2">
                <a href="{{ route('journal.settings.workflow.index', ['journal' => $journal->slug, 'tab' => 'review']) }}"
                    class="text-gray-400 hover:text-gray-600">
                    <i class="fa-solid fa-arrow-left"></i>
                </a>
                <h1 class="text-2xl font-bold text-gray-900">{{ $reviewForm->title }}</h1>
            </div>
            <p class="text-sm text-gray-500 ml-10">
                {{ $isId ? 'Kelola pertanyaan dan elemen formulir ulasan' : 'Manage review form questions and elements' }}
            </p>
        </div>

        <div class="grid grid-cols-12 gap-6">
            <!-- Left Sidebar - Element List -->
            <div class="col-span-12 lg:col-span-8">
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
                    <div class="p-6 border-b border-gray-200 flex items-center justify-between">
                        <h2 class="text-lg font-semibold text-gray-900">
                            {{ $isId ? 'Pertanyaan Formulir' : 'Form Questions' }}
                        </h2>
                        <button @click="showAddModal = true; resetForm()"
                            class="inline-flex items-center px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700">
                            <i class="fa-solid fa-plus mr-2"></i>
                            {{ $isId ? 'Tambah Pertanyaan' : 'Add Question' }}
                        </button>
                    </div>

                    <div class="p-6">
                        @if (count($reviewForm->elements) === 0)
                            <div class="text-center py-12">
                                <i class="fa-solid fa-list-check text-6xl text-gray-300 mb-4"></i>
                                <h3 class="text-lg font-medium text-gray-900 mb-2">
                                    {{ $isId ? 'Belum ada pertanyaan' : 'No questions yet' }}
                                </h3>
                                <p class="text-sm text-gray-500 mb-6">
                                    {{ $isId ? 'Mulai dengan menambahkan pertanyaan pertama untuk formulir ulasan Anda' : 'Start by adding the first question to your review form' }}
                                </p>
                                <button @click="showAddModal = true; resetForm()"
                                    class="inline-flex items-center px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700">
                                    <i class="fa-solid fa-plus mr-2"></i>
                                    {{ $isId ? 'Tambah Pertanyaan' : 'Add Question' }}
                                </button>
                            </div>
                        @else
                            <div class="space-y-4" x-ref="elementsList">
                                @foreach ($reviewForm->elements as $index => $element)
                                    <div class="border border-gray-200 rounded-lg p-4 hover:border-primary-300 transition-colors bg-white"
                                        data-element-id="{{ $element->id }}">
                                        <div class="flex items-start gap-4">
                                            <!-- Drag Handle -->
                                            <div class="flex-shrink-0 mt-1">
                                                <i class="fa-solid fa-grip-vertical text-gray-400 cursor-move"></i>
                                            </div>

                                            <!-- Content -->
                                            <div class="flex-1 min-w-0">
                                                <div class="flex items-start justify-between gap-4 mb-2">
                                                    <div class="flex-1">
                                                        <div class="flex items-center gap-2 mb-1">
                                                            <span
                                                                class="text-xs font-medium text-gray-500">{{ $index + 1 }}.</span>
                                                            <h4 class="text-sm font-medium text-gray-900">
                                                                {{ $element->question }}
                                                            </h4>
                                                            @if ($element->required)
                                                                <span class="text-xs text-red-600">*</span>
                                                            @endif
                                                        </div>
                                                        @if ($element->description)
                                                            <p class="text-xs text-gray-500 mt-1">
                                                                {{ $element->description }}
                                                            </p>
                                                        @endif
                                                    </div>
                                                    <span
                                                        class="flex-shrink-0 px-2 py-1 text-xs font-medium bg-blue-100 text-blue-700 rounded-full">
                                                        {{ $element->element_type->label() }}
                                                    </span>
                                                </div>

                                                <!-- Element Preview -->
                                                <div class="mt-3 p-3 bg-gray-50 rounded-lg border border-gray-100">
                                                    @include('admin.journals.review-forms.partials.element-preview', ['element' => $element])
                                                </div>

                                                <!-- Actions -->
                                                <div class="flex items-center gap-2 mt-3">
                                                    <button
                                                        @click="editElement({{ json_encode([
                                                            'id' => $element->id,
                                                            'element_type' => $element->element_type->value,
                                                            'question' => $element->question,
                                                            'description' => $element->description,
                                                            'required' => $element->required,
                                                            'options' => $element->options,
                                                        ]) }})"
                                                        class="text-xs text-blue-600 hover:text-blue-700 font-medium">
                                                        <i class="fa-solid fa-pencil mr-1"></i>
                                                        {{ $isId ? 'Edit' : 'Edit' }}
                                                    </button>
                                                    @if ($element->getResponseCount() === 0)
                                                        <button
                                                            onclick="submitForm('{{ route('journal.settings.workflow.review-forms.elements.destroy', ['journal' => $journal->slug, 'reviewForm' => $reviewForm->id, 'element' => $element->id]) }}', 'DELETE', '{{ $isId ? 'Hapus pertanyaan ini?' : 'Delete this question?' }}')"
                                                            class="text-xs text-red-600 hover:text-red-700 font-medium">
                                                            <i class="fa-solid fa-trash mr-1"></i>
                                                            {{ $isId ? 'Hapus' : 'Delete' }}
                                                        </button>
                                                    @else
                                                        <span class="text-xs text-gray-400">
                                                            <i class="fa-solid fa-lock mr-1"></i>
                                                            {{ $element->getResponseCount() }}
                                                            {{ $isId ? 'tanggapan' : 'responses' }}
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Right Sidebar - Info & Actions -->
            <div class="col-span-12 lg:col-span-4">
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 sticky top-6">
                    <h3 class="text-sm font-semibold text-gray-900 mb-4">
                        {{ $isId ? 'Informasi Formulir' : 'Form Information' }}
                    </h3>

                    <dl class="space-y-3 text-sm">
                        <div>
                            <dt class="text-gray-500">{{ $isId ? 'Status' : 'Status' }}</dt>
                            <dd class="mt-1">
                                @if ($reviewForm->is_active)
                                    <span
                                        class="px-2 py-1 text-xs font-medium bg-emerald-100 text-emerald-700 rounded-full">
                                        {{ $isId ? 'Aktif' : 'Active' }}
                                    </span>
                                @else
                                    <span class="px-2 py-1 text-xs font-medium bg-gray-100 text-gray-500 rounded-full">
                                        {{ $isId ? 'Tidak Aktif' : 'Inactive' }}
                                    </span>
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">{{ $isId ? 'Total Pertanyaan' : 'Total Questions' }}</dt>
                            <dd class="mt-1 font-medium text-gray-900">{{ count($reviewForm->elements) }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">{{ $isId ? 'Total Tanggapan' : 'Total Responses' }}</dt>
                            <dd class="mt-1 font-medium text-gray-900">{{ $reviewForm->response_count }}</dd>
                        </div>
                    </dl>

                    <div class="mt-6 pt-6 border-t border-gray-200">
                        <h4 class="text-sm font-semibold text-gray-900 mb-3">
                            {{ $isId ? 'Aksi' : 'Actions' }}
                        </h4>
                        <div class="space-y-2">
                            <a href="{{ route('journal.settings.workflow.review-forms.preview', ['journal' => $journal->slug, 'reviewForm' => $reviewForm->id]) }}"
                                class="block w-full text-center px-4 py-2 bg-white border border-gray-300 text-sm font-medium rounded-lg text-gray-700 hover:bg-gray-50">
                                <i class="fa-solid fa-eye mr-2"></i>
                                {{ $isId ? 'Pratinjau Formulir' : 'Preview Form' }}
                            </a>
                            <a href="{{ route('journal.settings.workflow.index', ['journal' => $journal->slug, 'tab' => 'review']) }}"
                                class="block w-full text-center px-4 py-2 bg-white border border-gray-300 text-sm font-medium rounded-lg text-gray-700 hover:bg-gray-50">
                                <i class="fa-solid fa-arrow-left mr-2"></i>
                                {{ $isId ? 'Kembali ke Pengaturan' : 'Back to Settings' }}
                            </a>
                        </div>
                    </div>

                    <div class="mt-6 pt-6 border-t border-gray-200">
                        <h4 class="text-sm font-semibold text-gray-900 mb-3">
                            {{ $isId ? 'Tipe Elemen Tersedia' : 'Available Element Types' }}
                        </h4>
                        <div class="space-y-2 text-xs">
                            @foreach ($elementTypes as $type)
                                <div class="flex items-start gap-2">
                                    <span class="px-2 py-0.5 bg-blue-100 text-blue-700 rounded-full font-medium">
                                        {{ $type['label'] }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add/Edit Element Modal -->
        <div x-show="showAddModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
            <div class="flex min-h-screen items-center justify-center p-4">
                <div class="fixed inset-0 bg-black/50" @click="showAddModal = false"></div>
                <div class="relative bg-white rounded-xl shadow-xl w-full max-w-2xl p-6">
                    <form
                        :action="editMode ? '{{ route('journal.settings.workflow.review-forms.elements.update', ['journal' => $journal->slug, 'reviewForm' => $reviewForm->id, 'element' => '__ELEMENT_ID__']) }}'.replace('__ELEMENT_ID__', formData.id) : '{{ route('journal.settings.workflow.review-forms.elements.store', ['journal' => $journal->slug, 'reviewForm' => $reviewForm->id]) }}'"
                        method="POST">
                        @csrf
                        <template x-if="editMode">
                            <input type="hidden" name="_method" value="PUT">
                        </template>

                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-lg font-semibold text-gray-900"
                                x-text="editMode ? '{{ $isId ? 'Edit Pertanyaan' : 'Edit Question' }}' : '{{ $isId ? 'Tambah Pertanyaan' : 'Add Question' }}'">
                            </h3>
                            <button type="button" @click="showAddModal = false" class="text-gray-400 hover:text-gray-600">
                                <i class="fa-solid fa-xmark text-lg"></i>
                            </button>
                        </div>

                        <div class="space-y-4 max-h-[70vh] overflow-y-auto pr-2">
                            <!-- Element Type -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    {{ $isId ? 'Tipe Elemen *' : 'Element Type *' }}
                                </label>
                                <select name="element_type" x-model="formData.element_type" required
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                                    @foreach ($elementTypes as $type)
                                        <option value="{{ $type['value'] }}">{{ $type['label'] }} -
                                            {{ $type['description'] }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Question -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    {{ $isId ? 'Pertanyaan *' : 'Question *' }}
                                </label>
                                <textarea name="question" x-model="formData.question" required rows="3"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                    placeholder="{{ $isId ? 'Contoh: Bagaimana originalitas penelitian ini?' : 'Example: How would you rate the originality of this research?' }}"></textarea>
                            </div>

                            <!-- Description -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    {{ $isId ? 'Deskripsi (Opsional)' : 'Description (Optional)' }}
                                </label>
                                <textarea name="description" x-model="formData.description" rows="2"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                    placeholder="{{ $isId ? 'Berikan panduan tambahan untuk pertanyaan ini...' : 'Provide additional guidance for this question...' }}"></textarea>
                            </div>

                            <!-- Required -->
                            <label class="flex items-center gap-3">
                                <input type="checkbox" name="required" value="1" x-model="formData.required"
                                    class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                                <span class="text-sm text-gray-700">{{ $isId ? 'Wajib diisi' : 'Required field' }}</span>
                            </label>

                            <!-- Options (for checkbox, radio, select) -->
                            <div x-show="['checkbox', 'radio', 'select'].includes(formData.element_type)">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    {{ $isId ? 'Pilihan' : 'Options' }}
                                </label>
                                <div class="space-y-2" id="options-container">
                                    <template x-for="(option, index) in formData.options" :key="index">
                                        <div class="flex gap-2">
                                            <input type="text" :name="'options[' + index + '][value]'"
                                                x-model="option.value"
                                                class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                                placeholder="{{ $isId ? 'Nilai' : 'Value' }}">
                                            <input type="text" :name="'options[' + index + '][label]'"
                                                x-model="option.label"
                                                class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                                placeholder="{{ $isId ? 'Label' : 'Label' }}">
                                            <button type="button" @click="removeOption(index)"
                                                class="px-3 py-2 text-red-600 hover:text-red-700">
                                                <i class="fa-solid fa-times"></i>
                                            </button>
                                        </div>
                                    </template>
                                </div>
                                <button type="button" @click="addOption"
                                    class="mt-2 text-sm text-primary-600 hover:text-primary-700 font-medium">
                                    <i class="fa-solid fa-plus mr-1"></i>
                                    {{ $isId ? 'Tambah Pilihan' : 'Add Option' }}
                                </button>
                            </div>
                        </div>

                        <div class="flex justify-end gap-3 mt-6 pt-4 border-t border-gray-200">
                            <button type="button" @click="showAddModal = false"
                                class="px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                                {{ $isId ? 'Batal' : 'Cancel' }}
                            </button>
                            <button type="submit"
                                class="px-4 py-2 text-white bg-primary-600 rounded-lg hover:bg-primary-700"
                                x-text="editMode ? '{{ $isId ? 'Simpan Perubahan' : 'Save Changes' }}' : '{{ $isId ? 'Tambah Pertanyaan' : 'Add Question' }}'">
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function formBuilder() {
            return {
                showAddModal: false,
                editMode: false,
                formData: {
                    id: '',
                    element_type: 'text',
                    question: '',
                    description: '',
                    required: false,
                    options: []
                },

                init() {
                    // Initialize Sortable if needed
                    console.log('Form builder initialized');
                },

                resetForm() {
                    this.editMode = false;
                    this.formData = {
                        id: '',
                        element_type: 'text',
                        question: '',
                        description: '',
                        required: false,
                        options: []
                    };
                },

                editElement(element) {
                    this.editMode = true;
                    this.formData = {
                        id: element.id,
                        element_type: element.element_type,
                        question: element.question,
                        description: element.description || '',
                        required: element.required,
                        options: element.options || []
                    };
                    this.showAddModal = true;
                },

                addOption() {
                    this.formData.options.push({
                        value: '',
                        label: ''
                    });
                },

                removeOption(index) {
                    this.formData.options.splice(index, 1);
                }
            }
        }
    </script>
@endsection
