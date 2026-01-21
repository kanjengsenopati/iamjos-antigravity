@extends('layouts.admin')

@section('title', 'Site Settings')

@section('content')
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900">Site Settings</h1>
        <p class="mt-1 text-gray-500">Configure global settings for your IAMJOS installation.</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Settings Form -->
        <div class="lg:col-span-2">
            <form action="{{ route('admin.site.settings.update') }}" method="POST"
                class="bg-white rounded-2xl border border-gray-200 overflow-hidden shadow-sm">
                @csrf

                <div class="p-6 border-b border-gray-100">
                    <h2 class="text-lg font-bold text-gray-900 mb-6">General Configuration</h2>

                    <div class="space-y-6">
                        <!-- Site Title -->
                        <div>
                            <label for="site_title" class="block text-sm font-medium text-gray-700 mb-2">
                                Site Title
                            </label>
                            <input type="text" id="site_title" name="site_title"
                                value="{{ old('site_title', $settings['site_title']) }}"
                                class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <p class="mt-1 text-xs text-gray-500">The main title displayed on the browser tab and meta tags.
                            </p>
                        </div>

                        <!-- Site Intro -->
                        <div>
                            <label for="site_intro" class="block text-sm font-medium text-gray-700 mb-2">
                                Site Introduction
                            </label>
                            <textarea id="site_intro" name="site_intro" rows="3"
                                class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">{{ old('site_intro', $settings['site_intro']) }}</textarea>
                            <p class="mt-1 text-xs text-gray-500">A brief description displayed on the portal homepage.</p>
                        </div>
                    </div>
                </div>

                <div class="p-6 border-b border-gray-100">
                    <h2 class="text-lg font-bold text-gray-900 mb-6">Security & Routing</h2>

                    <div class="space-y-6">
                        <!-- Min Password Length -->
                        <div>
                            <label for="min_password_length" class="block text-sm font-medium text-gray-700 mb-2">
                                Minimum Password Length
                            </label>
                            <input type="number" id="min_password_length" name="min_password_length" min="6"
                                max="32" value="{{ old('min_password_length', $settings['min_password_length']) }}"
                                class="w-full sm:w-1/2 px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        </div>

                        <!-- Redirect to Journal -->
                        <div class="flex items-start gap-3 p-4 bg-gray-50 rounded-xl">
                            <input type="checkbox" id="redirect_to_journal" name="redirect_to_journal" value="1"
                                {{ old('redirect_to_journal', $settings['redirect_to_journal']) ? 'checked' : '' }}
                                class="mt-1 w-5 h-5 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                            <label for="redirect_to_journal" class="cursor-pointer">
                                <span class="block text-sm font-medium text-gray-900">Redirect to Single Journal</span>
                                <span class="block text-xs text-gray-500 mt-1">
                                    If enabled and only one active journal exists, visitors to the portal home will be
                                    automatically redirected to that journal.
                                </span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="p-6 bg-gray-50 flex items-center justify-end">
                    <button type="submit"
                        class="inline-flex items-center gap-2 px-6 py-2.5 bg-indigo-600 text-white rounded-xl font-medium shadow-lg shadow-indigo-500/25 hover:bg-indigo-700 hover:shadow-indigo-500/40 transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Save Settings
                    </button>
                </div>
            </form>
        </div>

        <!-- Info Card -->
        <div class="lg:col-span-1 space-y-6">
            <div class="bg-blue-50 border border-blue-200 rounded-2xl p-6">
                <div class="flex items-start gap-4">
                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-blue-900">About Site Settings</h3>
                        <p class="mt-2 text-sm text-blue-800">
                            These settings apply globally to the entire IAMJOS installation. Individual journal settings can
                            be managed within each journal's administration area.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Site Appearance Quick Link -->
            <div class="bg-indigo-50 border border-indigo-200 rounded-2xl p-6">
                <div class="flex items-start gap-4">
                    <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center flex-shrink-0">
                        <i class="fa-solid fa-paintbrush text-indigo-600"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-indigo-900">Customize Portal</h3>
                        <p class="mt-2 text-sm text-indigo-800">
                            Want to change the look of the home page? Use the Drag & Drop Page Builder.
                        </p>
                        <a href="{{ route('admin.site.appearance.index') }}" class="inline-flex items-center mt-3 text-sm font-semibold text-indigo-700 hover:text-indigo-900">
                            Go to Page Builder <i class="fa-solid fa-arrow-right ml-2"></i>
                        </a>
                    </div>
                </div>
            </div>

            <div class="bg-white border border-gray-200 rounded-2xl p-6">
                <h3 class="font-bold text-gray-900 mb-4">Environment Config</h3>
                <div class="space-y-3">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">App Name</span>
                        <span class="font-mono text-gray-900">{{ config('app.name') }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Environment</span>
                        <span class="font-mono text-gray-900">{{ config('app.env') }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Debug Mode</span>
                        <span class="font-mono {{ config('app.debug') ? 'text-red-600' : 'text-emerald-600' }}">
                            {{ config('app.debug') ? 'Enabled' : 'Disabled' }}
                        </span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">URL</span>
                        <span class="font-mono text-gray-900 truncate max-w-[150px]"
                            title="{{ config('app.url') }}">{{ config('app.url') }}</span>
                    </div>
                </div>
                <div class="mt-4 pt-4 border-t border-gray-100">
                    <p class="text-xs text-gray-400">
                        Some settings are controlled via the <code>.env</code> file and cannot be changed here.
                    </p>
                </div>
            </div>
        </div>
    </div>
@endsection
