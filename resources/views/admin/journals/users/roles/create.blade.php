@extends('layouts.app')

@section('title', 'Create Role')

@section('content')
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900">Create New Role</h1>
        <p class="text-sm text-gray-500 mt-1">Define properties, permission levels, and workflow access for this role.</p>
    </div>

    <form action="{{ route($routePrefix . '.roles.store', ['journal' => $journal->slug]) }}" method="POST"
        class="pb-24 space-y-8">
        @csrf

        <!-- Section 1: Identity -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="p-6 border-b border-gray-100">
                <h2 class="text-lg font-semibold text-gray-900">1. Role Identity</h2>
                <p class="text-sm text-gray-500">Basic information and visual identification for this role.</p>
            </div>
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-4">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">Role Name</label>
                        <input type="text" name="name" id="name" placeholder="e.g. Guest Editor"
                            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm border p-2.5"
                            required>
                    </div>
                    <div>
                        <label for="abbreviation" class="block text-sm font-medium text-gray-700">Abbreviation</label>
                        <input type="text" name="abbreviation" id="abbreviation" placeholder="e.g. GE" maxlength="5"
                            class="mt-1 block w-24 rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm border p-2.5">
                        <p class="mt-1 text-xs text-gray-500">Max 5 characters.</p>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Role Color</label>
                    <div class="flex flex-wrap gap-3" x-data="{ selectedColor: 'blue' }">
                        <!-- Hidden Input -->
                        <input type="hidden" name="color" x-model="selectedColor">

                        @php
                            $colors = [
                                'red' => 'bg-red-500',
                                'orange' => 'bg-orange-500',
                                'amber' => 'bg-amber-500',
                                'green' => 'bg-emerald-500',
                                'teal' => 'bg-teal-500',
                                'blue' => 'bg-blue-500',
                                'indigo' => 'bg-indigo-500',
                                'purple' => 'bg-purple-500',
                                'pink' => 'bg-pink-500',
                                'gray' => 'bg-gray-500',
                            ];
                        @endphp

                        @foreach ($colors as $name => $class)
                            <button type="button" @click="selectedColor = '{{ $name }}'"
                                class="w-10 h-10 rounded-full {{ $class }} flex items-center justify-center transition-transform hover:scale-110 focus:outline-none ring-offset-2"
                                :class="{ 'ring-2 ring-gray-400': selectedColor === '{{ $name }}' }">
                                <i x-show="selectedColor === '{{ $name }}'"
                                    class="fa-solid fa-check text-white text-sm"></i>
                            </button>
                        @endforeach
                    </div>
                    <p class="mt-2 text-xs text-gray-500">Used for badges and identification in the grid.</p>
                </div>
            </div>
        </div>

        <!-- Section 2: Permission Level -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="p-6 border-b border-gray-100">
                <h2 class="text-lg font-semibold text-gray-900">2. Permission Level</h2>
                <p class="text-sm text-gray-500">Determines the core capabilities and access scope of this role.</p>
            </div>
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @php
                    $levels = [
                        [
                            'val' => 'manager',
                            'label' => 'Journal Manager',
                            'desc' => 'Full access to all settings, users, and submissions within this journal.',
                            'icon' => 'fa-screwdriver-wrench',
                        ],
                        [
                            'val' => 'editor',
                            'label' => 'Section Editor',
                            'desc' => 'Can edit assigned submissions and make editorial decisions.',
                            'icon' => 'fa-pen-to-square',
                        ],
                        [
                            'val' => 'assistant',
                            'label' => 'Assistant',
                            'desc' => 'Restricted access. Can only work on specific workflow stages of assigned items.',
                            'icon' => 'fa-hand-holding-hand',
                        ],
                        [
                            'val' => 'reviewer',
                            'label' => 'Reviewer',
                            'desc' => 'Can only access and perform reviews on assigned submissions.',
                            'icon' => 'fa-magnifying-glass',
                        ],
                        [
                            'val' => 'author',
                            'label' => 'Author',
                            'desc' => 'Can submit articles and track their own progress only.',
                            'icon' => 'fa-user-pen',
                        ],
                        [
                            'val' => 'reader',
                            'label' => 'Reader',
                            'desc' => 'Read-only access to published content.',
                            'icon' => 'fa-book-open',
                        ],
                    ];
                @endphp

                @foreach ($levels as $level)
                    <label
                        class="relative flex flex-col p-4 bg-white border border-gray-200 rounded-xl cursor-pointer hover:border-indigo-500 hover:bg-indigo-50/30 transition-all group">
                        <input type="radio" name="permission_level" value="{{ $level['val'] }}" class="peer sr-only">
                        <div class="flex items-center gap-3 mb-2">
                            <div
                                class="w-8 h-8 rounded-lg bg-gray-100 text-gray-500 flex items-center justify-center peer-checked:bg-indigo-600 peer-checked:text-white group-hover:bg-indigo-100 group-hover:text-indigo-600 transition-colors">
                                <i class="fa-solid {{ $level['icon'] }}"></i>
                            </div>
                            <span
                                class="font-semibold text-gray-900 peer-checked:text-indigo-700">{{ $level['label'] }}</span>
                        </div>
                        <p class="text-xs text-gray-500 leading-relaxed">{{ $level['desc'] }}</p>

                        <!-- Ring effect when checked -->
                        <div
                            class="absolute inset-0 border-2 border-transparent peer-checked:border-indigo-600 rounded-xl pointer-events-none">
                        </div>
                    </label>
                @endforeach
            </div>
        </div>

        <!-- Section 3: Workflow Stage Access -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="p-6 border-b border-gray-100">
                <h2 class="text-lg font-semibold text-gray-900">3. Workflow Stage Access</h2>
                <p class="text-sm text-gray-500">Configure which stages of the editorial workflow this role can access.</p>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                    <label
                        class="flex items-start p-3 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors cursor-pointer">
                        <div class="flex items-center h-5">
                            <input type="checkbox" name="stages[]" value="submission"
                                class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                        </div>
                        <div class="ml-3 text-sm">
                            <span class="font-medium text-gray-900">Submission</span>
                            <p class="text-gray-500 text-xs mt-0.5">Initial checks & assignment</p>
                        </div>
                    </label>

                    <label
                        class="flex items-start p-3 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors cursor-pointer">
                        <div class="flex items-center h-5">
                            <input type="checkbox" name="stages[]" value="review"
                                class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                        </div>
                        <div class="ml-3 text-sm">
                            <span class="font-medium text-gray-900">Review</span>
                            <p class="text-gray-500 text-xs mt-0.5">Peer review management</p>
                        </div>
                    </label>

                    <label
                        class="flex items-start p-3 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors cursor-pointer">
                        <div class="flex items-center h-5">
                            <input type="checkbox" name="stages[]" value="copyediting"
                                class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                        </div>
                        <div class="ml-3 text-sm">
                            <span class="font-medium text-gray-900">Copyediting</span>
                            <p class="text-gray-500 text-xs mt-0.5">Grammar & formatting</p>
                        </div>
                    </label>

                    <label
                        class="flex items-start p-3 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors cursor-pointer">
                        <div class="flex items-center h-5">
                            <input type="checkbox" name="stages[]" value="production"
                                class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                        </div>
                        <div class="ml-3 text-sm">
                            <span class="font-medium text-gray-900">Production</span>
                            <p class="text-gray-500 text-xs mt-0.5">Final galley creation</p>
                        </div>
                    </label>
                </div>
            </div>
        </div>

        <!-- Section 4: Role Options -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="p-6 border-b border-gray-100">
                <h2 class="text-lg font-semibold text-gray-900">4. Role Options</h2>
                <p class="text-sm text-gray-500">Additional settings for user interaction and visibility.</p>
            </div>
            <div class="p-6 space-y-5">
                <div class="flex items-center justify-between">
                    <div>
                        <label for="self_register" class="text-sm font-medium text-gray-900">Allow user
                            self-registration</label>
                        <p class="text-xs text-gray-500">Users can select this role when registering an account.</p>
                    </div>
                    <button type="button" x-data="{ on: false }" @click="on = !on"
                        :class="{ 'bg-indigo-600': on, 'bg-gray-200': !on }"
                        class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:ring-offset-2">
                        <span aria-hidden="true" :class="{ 'translate-x-5': on, 'translate-x-0': !on }"
                            class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"></span>
                        <input type="checkbox" name="allow_self_registration" x-model="on" class="hidden">
                    </button>
                </div>
                <hr class="border-gray-100">

                <div class="flex items-center justify-between">
                    <div>
                        <label for="show_title" class="text-sm font-medium text-gray-900">Show role title in contributor
                            list</label>
                        <p class="text-xs text-gray-500">Displays the role name next to the user's name in publication
                            details.</p>
                    </div>
                    <button type="button" x-data="{ on: true }" @click="on = !on"
                        :class="{ 'bg-indigo-600': on, 'bg-gray-200': !on }"
                        class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:ring-offset-2">
                        <span aria-hidden="true" :class="{ 'translate-x-5': on, 'translate-x-0': !on }"
                            class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"></span>
                        <input type="checkbox" name="show_in_contributors" x-model="on" class="hidden">
                    </button>
                </div>
                <hr class="border-gray-100">

                <div class="flex items-center justify-between">
                    <div>
                        <label for="allow_submission" class="text-sm font-medium text-gray-900">Allow this role to make
                            new submissions</label>
                        <p class="text-xs text-gray-500">Users with this role can start the submission wizard.</p>
                    </div>
                    <button type="button" x-data="{ on: false }" @click="on = !on"
                        :class="{ 'bg-indigo-600': on, 'bg-gray-200': !on }"
                        class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:ring-offset-2">
                        <span aria-hidden="true" :class="{ 'translate-x-5': on, 'translate-x-0': !on }"
                            class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"></span>
                        <input type="checkbox" name="allow_submission" x-model="on" class="hidden">
                    </button>
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="flex justify-end gap-3 pt-6 border-t border-gray-100">
            <a href="{{ route($routePrefix . '.roles', ['journal' => $journal->slug]) }}"
                class="px-5 py-2.5 bg-white border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 text-sm font-medium transition-colors">
                Cancel
            </a>
            <button type="submit"
                class="px-5 py-2.5 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-sm font-medium shadow-sm transition-colors flex items-center gap-2">
                <i class="fa-solid fa-save"></i>
                Save Role
            </button>
        </div>
    </form>
@endsection
