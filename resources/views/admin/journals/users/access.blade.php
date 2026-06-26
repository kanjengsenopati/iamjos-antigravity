@extends('layouts.app')

@section('title', 'Site Access Options')

@section('content')
    <!-- Header -->
    @include('admin.journals.users._header', ['activeTab' => 'access'])

    <!-- Flash Messages -->
    @if (session('success'))
    <div class="mb-6 p-4 rounded-[24px] bg-emerald-50 border border-emerald-200 text-emerald-800 shadow-custom">
        <div class="flex items-center gap-2">
            <i class="fa-solid fa-circle-check text-emerald-600"></i>
            <x-text.body class="font-medium text-emerald-800">{{ session('success') }}</x-text.body>
        </div>
    </div>
    @endif

    <!-- Configuration Form -->
    <form action="{{ route($routePrefix . '.access.update', ['journal' => $journal->slug]) }}" method="POST"
        class="relative pb-20">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Kolom Kiri: Akses & Validasi -->
            <div class="space-y-6">
                <!-- Site Access Options -->
                <div class="bg-white rounded-[24px] shadow-custom p-6">
                    <div class="mb-6 flex items-center gap-2">
                        <i class="fa-solid fa-lock text-primary-600"></i>
                        <x-text.h2 class="text-gray-900 font-semibold">Site Access</x-text.h2>
                    </div>
                    
                    <div class="space-y-5">
                        <div class="flex items-start">
                            <div class="flex items-center h-5">
                                <input id="restrict_site_access" name="restrict_site_access" type="checkbox" value="1"
                                    class="focus:ring-primary-500 h-4.5 w-4.5 text-primary-600 border-slate-300 rounded transition-all cursor-pointer"
                                    {{ ($journal->settings['restrict_site_access'] ?? false) ? 'checked' : '' }}>
                            </div>
                            <div class="ml-3 text-sm">
                                <label for="restrict_site_access" class="font-medium text-slate-700 cursor-pointer">
                                    <x-text.body class="text-slate-700 font-medium">Users must be registered and log in to view the journal site.</x-text.body>
                                </label>
                            </div>
                        </div>

                        <div class="flex items-start">
                            <div class="flex items-center h-5">
                                <input id="restrict_article_access" name="restrict_article_access" type="checkbox" value="1"
                                    class="focus:ring-primary-500 h-4.5 w-4.5 text-primary-600 border-slate-300 rounded transition-all cursor-pointer"
                                    {{ ($journal->settings['restrict_article_access'] ?? false) ? 'checked' : '' }}>
                            </div>
                            <div class="ml-3 text-sm">
                                <label for="restrict_article_access" class="font-medium text-slate-700 cursor-pointer">
                                    <x-text.body class="text-slate-700 font-medium">Users must be registered and log in to view open access content.</x-text.body>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Validation Options -->
                <div class="bg-white rounded-[24px] shadow-custom p-6">
                    <div class="mb-6 flex items-center gap-2">
                        <i class="fa-solid fa-check-double text-primary-600"></i>
                        <x-text.h2 class="text-gray-900 font-semibold">Validation</x-text.h2>
                    </div>
                    
                    <div class="flex items-start">
                        <div class="flex items-center h-5">
                            <input id="require_validation" name="require_validation" type="checkbox" value="1"
                                class="focus:ring-primary-500 h-4.5 w-4.5 text-primary-600 border-slate-300 rounded transition-all cursor-pointer"
                                {{ ($journal->settings['require_validation'] ?? false) ? 'checked' : '' }}>
                        </div>
                        <div class="ml-3 text-sm">
                            <label for="require_validation" class="font-medium text-slate-700 cursor-pointer">
                                <x-text.body class="text-slate-700 font-medium block">Require email validation</x-text.body>
                                <x-text.caption class="text-slate-400 mt-1 block">Users will not be able to log in until they verify their email address.</x-text.caption>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Kolom Kanan: Pendaftaran & Peran -->
            <div class="space-y-6">
                <!-- User Registration Options -->
                <div class="bg-white rounded-[24px] shadow-custom p-6">
                    <div class="mb-6 flex items-center gap-2">
                        <i class="fa-solid fa-user-plus text-primary-600"></i>
                        <x-text.h2 class="text-gray-900 font-semibold">User Registration</x-text.h2>
                    </div>
                    
                    <div class="space-y-5">
                        <div class="flex items-start">
                            <div class="flex items-center h-5">
                                <input id="reg_open" name="registration_mode" type="radio" value="open"
                                    class="focus:ring-primary-500 h-4.5 w-4.5 text-primary-600 border-slate-300 transition-all cursor-pointer"
                                    {{ ($journal->settings['registration_mode'] ?? 'open') === 'open' ? 'checked' : '' }}>
                            </div>
                            <div class="ml-3 text-sm">
                                <label for="reg_open" class="font-medium text-slate-700 cursor-pointer">
                                    <x-text.body class="text-slate-700 font-medium">Visitors can register a user account with the journal.</x-text.body>
                                </label>
                            </div>
                        </div>

                        <div class="flex items-start">
                            <div class="flex items-center h-5">
                                <input id="reg_disabled" name="registration_mode" type="radio" value="disabled"
                                    class="focus:ring-primary-500 h-4.5 w-4.5 text-primary-600 border-slate-300 transition-all cursor-pointer"
                                    {{ ($journal->settings['registration_mode'] ?? 'open') === 'disabled' ? 'checked' : '' }}>
                            </div>
                            <div class="ml-3 text-sm">
                                <label for="reg_disabled" class="font-medium text-slate-700 cursor-pointer">
                                    <x-text.body class="text-slate-700 font-medium">The Journal Manager will register all user accounts. Editors or Section Editors may register user accounts for reviewers.</x-text.body>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Role Registration Options -->
                <div class="bg-white rounded-[24px] shadow-custom p-6">
                    <div class="mb-4 flex items-center gap-2">
                        <i class="fa-solid fa-id-badge text-primary-600"></i>
                        <x-text.h2 class="text-gray-900 font-semibold">Role Registration</x-text.h2>
                    </div>
                    <x-text.body class="text-slate-400 mb-6 block">Select which roles users can self-register for (if registration is open).</x-text.body>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Reader -->
                        <div class="relative flex items-start p-3 border border-slate-100 rounded-xl hover:bg-slate-50 transition-colors">
                            <div class="flex items-center h-5">
                                <input id="role_reader" type="checkbox"
                                    class="focus:ring-primary-500 h-4 w-4 text-primary-600 border-slate-300 rounded cursor-not-allowed" checked disabled>
                                <input type="hidden" name="allow_roles[]" value="reader">
                            </div>
                            <div class="ml-3 text-sm">
                                <label for="role_reader" class="font-medium text-slate-700">
                                    <x-text.body class="text-slate-700 font-semibold block">Reader</x-text.body>
                                    <x-text.caption class="text-slate-400 block mt-0.5">Default role.</x-text.caption>
                                </label>
                            </div>
                        </div>

                        <!-- Author -->
                        <div class="relative flex items-start p-3 border border-slate-100 rounded-xl hover:bg-slate-50 transition-colors cursor-pointer">
                            <div class="flex items-center h-5">
                                <input id="role_author" name="allow_roles[]" value="author" type="checkbox"
                                    class="focus:ring-primary-500 h-4 w-4 text-primary-600 border-slate-300 rounded cursor-pointer"
                                    {{ in_array('author', $journal->settings['allow_roles'] ?? ['reader', 'author']) ? 'checked' : '' }}>
                            </div>
                            <div class="ml-3 text-sm">
                                <label for="role_author" class="font-medium text-slate-700 cursor-pointer">
                                    <x-text.body class="text-slate-700 font-semibold block">Author</x-text.body>
                                    <x-text.caption class="text-slate-400 block mt-0.5">Submit articles.</x-text.caption>
                                </label>
                            </div>
                        </div>

                        <!-- Reviewer -->
                        <div class="relative flex items-start p-3 border border-slate-100 rounded-xl hover:bg-slate-50 transition-colors cursor-pointer col-span-1 sm:col-span-2">
                            <div class="flex items-center h-5">
                                <input id="role_reviewer" name="allow_roles[]" value="reviewer" type="checkbox"
                                    class="focus:ring-primary-500 h-4 w-4 text-primary-600 border-slate-300 rounded cursor-pointer"
                                    {{ in_array('reviewer', $journal->settings['allow_roles'] ?? []) ? 'checked' : '' }}>
                            </div>
                            <div class="ml-3 text-sm">
                                <label for="role_reviewer" class="font-medium text-slate-700 cursor-pointer">
                                    <x-text.body class="text-slate-700 font-semibold block">Reviewer</x-text.body>
                                    <x-text.caption class="text-slate-400 block mt-0.5">Allows users to be selected for peer review.</x-text.caption>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sticky Footer -->
        <div class="fixed bottom-0 left-0 right-0 z-20 bg-white border-t border-slate-100 p-4 lg:pl-[300px] shadow-custom-top backdrop-blur-sm bg-white/90">
            <div class="max-w-7xl mx-auto flex justify-end gap-3">
                <a href="{{ route($routePrefix . '.index', ['journal' => $journal->slug]) }}"
                    class="px-5 py-2.5 text-sm font-medium text-slate-700 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors flex items-center justify-center">
                    Cancel
                </a>
                <button type="submit"
                    class="inline-flex items-center justify-center px-5 py-2.5 text-sm font-semibold text-white bg-primary-600 border border-transparent rounded-lg hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 shadow-sm hover:shadow-md transition-all">
                    Save Changes
                </button>
            </div>
        </div>
    </form>

    <style>
        .shadow-custom-top {
            box-shadow: 0 -4px 6px -1px rgba(0, 0, 0, 0.02), 0 -2px 4px -1px rgba(0, 0, 0, 0.01);
        }

        @media (min-width: 1024px) {
            .fixed.bottom-0.left-0.right-0.lg\:pl-\[300px\] {
                padding-left: calc(var(--sidebar-width) + 1.5rem);
            }
        }
    </style>
@endsection
