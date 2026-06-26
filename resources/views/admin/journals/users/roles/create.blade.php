@extends('layouts.app')

@section('title', 'Create Role')

@section('content')
<div class="mb-8">
    <x-text.h1>Create New Role</x-text.h1>
    <x-text.body class="text-slate-500 mt-1">Define properties, permission levels, and workflow access for this role.</x-text.body>
</div>

<form action="{{ route($routePrefix . '.roles.store', ['journal' => $journal->slug]) }}" method="POST"
    class="pb-24 space-y-6" x-data="{ selectedLevel: 5, activeTab: 'identity' }">
    @csrf

    <!-- Main Card Container -->
    <div class="bg-white rounded-[24px] shadow-[0_8px_30px_rgb(0,0,0,0.04)] overflow-hidden">
        <!-- Tab Navigation -->
        <div class="border-b border-slate-100 bg-slate-50/50">
            <nav class="flex overflow-x-auto px-6" aria-label="Tabs">
                <button type="button" @click="activeTab = 'identity'"
                    :class="activeTab === 'identity' ? 'border-primary-500 text-primary-600 font-semibold' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'"
                    class="flex-shrink-0 px-6 py-4 text-sm font-medium border-b-2 transition-all whitespace-nowrap flex items-center gap-2">
                    <i class="fa-solid fa-id-card text-base transition-colors" :class="activeTab === 'identity' ? 'text-primary-600' : 'text-slate-400'"></i>
                    Role Identity
                </button>
                <button type="button" @click="activeTab = 'permission'"
                    :class="activeTab === 'permission' ? 'border-primary-500 text-primary-600 font-semibold' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'"
                    class="flex-shrink-0 px-6 py-4 text-sm font-medium border-b-2 transition-all whitespace-nowrap flex items-center gap-2">
                    <i class="fa-solid fa-shield-halved text-base transition-colors" :class="activeTab === 'permission' ? 'text-primary-600' : 'text-slate-400'"></i>
                    Permission Level
                </button>
                <button type="button" @click="activeTab = 'stages'"
                    :class="activeTab === 'stages' ? 'border-primary-500 text-primary-600 font-semibold' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'"
                    class="flex-shrink-0 px-6 py-4 text-sm font-medium border-b-2 transition-all whitespace-nowrap flex items-center gap-2">
                    <i class="fa-solid fa-route text-base transition-colors" :class="activeTab === 'stages' ? 'text-primary-600' : 'text-slate-400'"></i>
                    Workflow Stages
                </button>
                <button type="button" @click="activeTab = 'options'"
                    :class="activeTab === 'options' ? 'border-primary-500 text-primary-600 font-semibold' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'"
                    class="flex-shrink-0 px-6 py-4 text-sm font-medium border-b-2 transition-all whitespace-nowrap flex items-center gap-2">
                    <i class="fa-solid fa-sliders text-base transition-colors" :class="activeTab === 'options' ? 'text-primary-600' : 'text-slate-400'"></i>
                    Role Options
                </button>
            </nav>
        </div>

        <!-- Tab Contents -->
        <div class="p-6 md:p-8">
            <!-- TAB 1: IDENTITY -->
            <div x-show="activeTab === 'identity'" x-cloak x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0">
                
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-10 h-10 bg-primary-50 rounded-xl flex items-center justify-center">
                        <i class="fa-solid fa-id-card text-primary-600 text-lg"></i>
                    </div>
                    <div>
                        <x-text.h2>1. Role Identity</x-text.h2>
                        <x-text.body class="text-slate-500 text-xs mt-0.5">Basic information and visual identification for this role.</x-text.body>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-4">
                        <div>
                            <label for="name" class="block text-sm font-medium text-slate-700">Role Name</label>
                            <input type="text" name="name" id="name" placeholder="e.g. Guest Editor"
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm border p-2.5"
                                required>
                        </div>
                        <div>
                            <label for="abbreviation" class="block text-sm font-medium text-slate-700">Abbreviation</label>
                            <input type="text" name="abbreviation" id="abbreviation" placeholder="e.g. GE" maxlength="5"
                                class="mt-1 block w-24 rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm border p-2.5">
                            <x-text.caption class="mt-1 block">Max 5 characters.</x-text.caption>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Role Color</label>
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
                        <x-text.caption class="mt-2 block">Used for badges and identification in the grid.</x-text.caption>
                    </div>
                </div>
            </div>

            <!-- TAB 2: PERMISSION LEVEL -->
            <div x-show="activeTab === 'permission'" x-cloak x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0">
                
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-10 h-10 bg-primary-50 rounded-xl flex items-center justify-center">
                        <i class="fa-solid fa-shield-halved text-primary-600 text-lg"></i>
                    </div>
                    <div>
                        <x-text.h2>2. Permission Level</x-text.h2>
                        <x-text.body class="text-slate-500 text-xs mt-0.5">Determines the core capabilities and access scope of this role.</x-text.body>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @php
                    $levels = [
                    [
                    'val' => 1,
                    'label' => 'Journal Manager',
                    'desc' => 'Full access to all settings, users, and submissions within this journal.',
                    'icon' => 'fa-screwdriver-wrench',
                    ],
                    [
                    'val' => 2,
                    'label' => 'Section Editor',
                    'desc' => 'Can edit assigned submissions and make editorial decisions.',
                    'icon' => 'fa-pen-to-square',
                    ],
                    [
                    'val' => 3,
                    'label' => 'Assistant',
                    'desc' => 'Restricted access. Can only work on specific workflow stages of assigned items.',
                    'icon' => 'fa-hand-holding-hand',
                    ],
                    [
                    'val' => 4,
                    'label' => 'Reviewer',
                    'desc' => 'Can only access and perform reviews on assigned submissions.',
                    'icon' => 'fa-magnifying-glass',
                    ],
                    [
                    'val' => 5,
                    'label' => 'Author',
                    'desc' => 'Can submit articles and track their own progress only.',
                    'icon' => 'fa-user-pen',
                    ],
                    [
                    'val' => 6,
                    'label' => 'Reader',
                    'desc' => 'Read-only access to published content.',
                    'icon' => 'fa-book-open',
                    ],
                    ];
                    @endphp

                    @foreach ($levels as $level)
                    <div class="relative flex flex-col h-full">
                        <label class="relative flex flex-col p-4 bg-white border rounded-xl cursor-pointer hover:border-primary-500 hover:bg-primary-50/30 transition-all group h-full"
                            :class="selectedLevel == {{ $level['val'] }} ? 'border-primary-600 ring-1 ring-primary-600 bg-primary-50/20' : 'border-gray-200'">

                            <input type="radio" name="permission_level" value="{{ $level['val'] }}"
                                x-model="selectedLevel" class="sr-only">

                            <div class="flex items-center gap-3 mb-2">
                                <div class="w-10 h-10 rounded-lg flex items-center justify-center transition-colors"
                                    :class="selectedLevel == {{ $level['val'] }} ? 'bg-primary-600 text-white' : 'bg-gray-100 text-gray-500 group-hover:bg-primary-100 group-hover:text-primary-600'">
                                    <i class="fa-solid {{ $level['icon'] }} text-lg"></i>
                                </div>
                                <span class="font-semibold text-gray-900"
                                    :class="selectedLevel == {{ $level['val'] }} ? 'text-primary-700 font-bold' : ''">
                                    {{ $level['label'] }}
                                </span>
                            </div>
                            <x-text.body class="text-xs text-slate-500 leading-relaxed">{{ $level['desc'] }}</x-text.body>

                            <!-- Checkmark Badge -->
                            <div class="absolute top-3 right-3" x-show="selectedLevel == {{ $level['val'] }}" x-transition>
                                <i class="fa-solid fa-circle-check text-primary-600 text-lg"></i>
                            </div>
                        </label>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- TAB 3: WORKFLOW STAGE ASSIGNMENT -->
            <div x-show="activeTab === 'stages'" x-cloak x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0">
                
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-10 h-10 bg-primary-50 rounded-xl flex items-center justify-center">
                        <i class="fa-solid fa-route text-primary-600 text-lg"></i>
                    </div>
                    <div>
                        <x-text.h2>3. Workflow Stage Assignment</x-text.h2>
                        <x-text.body class="text-slate-500 text-xs mt-0.5">Configure which stages of the editorial workflow this role can access.</x-text.body>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                    <label
                        class="flex items-start p-3 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors cursor-pointer">
                        <div class="flex items-center h-5">
                            <input type="checkbox" name="stages[]" value="submission"
                                class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500">
                        </div>
                        <div class="ml-3 text-sm">
                            <span class="font-medium text-gray-900">Submission</span>
                            <x-text.caption class="text-slate-500 text-xs mt-0.5 block">Initial checks & assignment</x-text.caption>
                        </div>
                    </label>

                    <label
                        class="flex items-start p-3 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors cursor-pointer">
                        <div class="flex items-center h-5">
                            <input type="checkbox" name="stages[]" value="review"
                                class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500">
                        </div>
                        <div class="ml-3 text-sm">
                            <span class="font-medium text-gray-900">Review</span>
                            <x-text.caption class="text-slate-500 text-xs mt-0.5 block">Peer review management</x-text.caption>
                        </div>
                    </label>

                    <label
                        class="flex items-start p-3 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors cursor-pointer">
                        <div class="flex items-center h-5">
                            <input type="checkbox" name="stages[]" value="copyediting"
                                class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500">
                        </div>
                        <div class="ml-3 text-sm">
                            <span class="font-medium text-gray-900">Copyediting</span>
                            <x-text.caption class="text-slate-500 text-xs mt-0.5 block">Grammar & formatting</x-text.caption>
                        </div>
                    </label>

                    <label
                        class="flex items-start p-3 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors cursor-pointer">
                        <div class="flex items-center h-5">
                            <input type="checkbox" name="stages[]" value="production"
                                class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500">
                        </div>
                        <div class="ml-3 text-sm">
                            <span class="font-medium text-gray-900">Production</span>
                            <x-text.caption class="text-slate-500 text-xs mt-0.5 block">Final galley creation</x-text.caption>
                        </div>
                    </label>
                </div>
            </div>

            <!-- TAB 4: ROLE OPTIONS -->
            <div x-show="activeTab === 'options'" x-cloak x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0">
                
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-10 h-10 bg-primary-50 rounded-xl flex items-center justify-center">
                        <i class="fa-solid fa-sliders text-primary-600 text-lg"></i>
                    </div>
                    <div>
                        <x-text.h2>4. Role Options</x-text.h2>
                        <x-text.body class="text-slate-500 text-xs mt-0.5">Additional configuration for visibility and user capabilities.</x-text.body>
                    </div>
                </div>

                <div class="divide-y divide-slate-100">
                    {{-- OPTION 1: Self Registration --}}
                    <div class="flex items-center justify-between py-4 group">
                        <div class="flex flex-col pr-8 max-w-2xl">
                            <label for="allow_registration" class="text-sm font-semibold text-slate-800 cursor-pointer group-hover:text-primary-600 transition-colors">
                                Allow user self-registration
                            </label>
                            <x-text.caption class="text-slate-500 mt-1 block">
                                Users can select this role when registering an account. Useful for Authors and Reviewers.
                            </x-text.caption>
                        </div>

                        <div class="flex-shrink-0 ml-4">
                            <select name="allow_registration" id="allow_registration"
                                class="block w-28 rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm py-2 px-3">
                                <option value="1">Yes</option>
                                <option value="0" selected>No</option>
                            </select>
                        </div>
                    </div>

                    {{-- OPTION 2: Contributor List --}}
                    <div class="flex items-center justify-between py-4 group">
                        <div class="flex flex-col pr-8 max-w-2xl">
                            <label for="show_contributor" class="text-sm font-semibold text-slate-800 cursor-pointer group-hover:text-primary-600 transition-colors">
                                Show role title in contributor list
                            </label>
                            <x-text.caption class="text-slate-500 mt-1 block">
                                Displays the role name next to the user's name in publication details.
                            </x-text.caption>
                        </div>

                        <div class="flex-shrink-0 ml-4">
                            <select name="show_contributor" id="show_contributor"
                                class="block w-28 rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm py-2 px-3">
                                <option value="1" selected>Yes</option>
                                <option value="0">No</option>
                            </select>
                        </div>
                    </div>

                    {{-- OPTION 3: Make Submissions --}}
                    <div class="flex items-center justify-between py-4 group">
                        <div class="flex flex-col pr-8 max-w-2xl">
                            <label for="allow_submission" class="text-sm font-semibold text-slate-800 cursor-pointer group-hover:text-primary-600 transition-colors">
                                Allow this role to make new submissions
                            </label>
                            <x-text.caption class="text-slate-500 mt-1 block">
                                Users with this role can start the submission wizard. Typically enabled for Authors.
                            </x-text.caption>
                        </div>

                        <div class="flex-shrink-0 ml-4">
                            <select name="allow_submission" id="allow_submission"
                                class="block w-28 rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm py-2 px-3">
                                <option value="1">Yes</option>
                                <option value="0" selected>No</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Form Actions -->
    <div class="flex justify-end gap-3 pt-6">
        <a href="{{ route($routePrefix . '.roles', ['journal' => $journal->slug]) }}"
            class="px-5 py-2.5 bg-white border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 text-sm font-medium transition-colors">
            Cancel
        </a>
        <button type="submit"
            class="px-5 py-2.5 bg-primary-600 text-white rounded-lg hover:bg-primary-700 text-sm font-medium shadow-sm transition-colors flex items-center gap-2">
            <i class="fa-solid fa-save"></i>
            Save Role
        </button>
    </div>
</form>
@endsection