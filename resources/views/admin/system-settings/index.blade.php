@extends('layouts.admin')

@section('title', 'System Settings')

@section('content')
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900">System Settings</h1>
        <p class="mt-1 text-gray-500">Manage application-wide technical configuration. Changes take effect immediately.</p>
    </div>

    @if (session('success'))
        <div class="mb-6 flex items-center gap-3 px-4 py-3 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-700 text-sm">
            <svg class="w-5 h-5 flex-shrink-0 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-6 flex items-start gap-3 px-4 py-3 bg-red-50 border border-red-200 rounded-xl text-red-700 text-sm">
            <svg class="w-5 h-5 flex-shrink-0 text-red-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <ul class="list-disc list-inside space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @php
        $groupLabels = [
            'pagination'   => 'Pagination & Display Limits',
            'uploads'      => 'File Upload Constraints',
            'reviewer'     => 'Reviewer Reminders',
            'integrations' => 'External Integrations',
            'app'          => 'Application',
        ];

        $groupIcons = [
            'pagination'   => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />',
            'uploads'      => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />',
            'reviewer'     => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />',
            'integrations' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />',
            'app'          => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />',
        ];

        // URL-like keys that should use type="url"
        $urlKeys = ['crossref_deposit_url_live', 'crossref_deposit_url_test', 'crossref_api_base_url', 'recaptcha_verify_url', 'google_scholar_search_url'];
    @endphp

    <div class="space-y-8 max-w-5xl">
        @forelse ($settings as $group => $groupSettings)
            @php
                $label = $groupLabels[$group] ?? ucfirst($group);
                $icon  = $groupIcons[$group] ?? '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 4a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />';
            @endphp

            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                <!-- Card Header -->
                <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-3">
                    <div class="w-9 h-9 bg-indigo-50 rounded-lg flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            {!! $icon !!}
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-base font-semibold text-gray-900">{{ $label }}</h2>
                        <p class="text-xs text-gray-500 mt-0.5">{{ $groupSettings->count() }} setting{{ $groupSettings->count() !== 1 ? 's' : '' }}</p>
                    </div>
                </div>

                <!-- Form -->
                <form action="{{ route('admin.system-settings.update') }}" method="POST">
                    @csrf

                    <div class="divide-y divide-gray-50">
                        @foreach ($groupSettings as $setting)
                            <div class="px-6 py-5">
                                @if ($setting->type === 'boolean')
                                    {{-- Boolean: checkbox toggle --}}
                                    <div class="flex items-start gap-4">
                                        <div class="flex items-center h-6 mt-0.5">
                                            <input
                                                type="checkbox"
                                                id="{{ $setting->key }}"
                                                name="{{ $setting->key }}"
                                                value="1"
                                                {{ $setting->typed_value ? 'checked' : '' }}
                                                class="w-5 h-5 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500 cursor-pointer"
                                            >
                                        </div>
                                        <label for="{{ $setting->key }}" class="cursor-pointer flex-1">
                                            <span class="block text-sm font-medium text-gray-900">
                                                {{ ucwords(str_replace('_', ' ', $setting->key)) }}
                                            </span>
                                            @if ($setting->description)
                                                <span class="block text-xs text-gray-500 mt-0.5">{{ $setting->description }}</span>
                                            @endif
                                            <span class="block text-xs text-gray-400 mt-1 font-mono">{{ $setting->key }}</span>
                                        </label>
                                    </div>

                                @elseif ($setting->type === 'integer')
                                    {{-- Integer: number input --}}
                                    <label for="{{ $setting->key }}" class="block text-sm font-medium text-gray-700 mb-1.5">
                                        {{ ucwords(str_replace('_', ' ', $setting->key)) }}
                                        <span class="ml-1 text-xs font-normal text-gray-400 font-mono">({{ $setting->key }})</span>
                                    </label>
                                    <input
                                        type="number"
                                        id="{{ $setting->key }}"
                                        name="{{ $setting->key }}"
                                        value="{{ old($setting->key, $setting->value) }}"
                                        class="w-full sm:w-64 px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                    >
                                    @if ($setting->description)
                                        <p class="mt-1.5 text-xs text-gray-500">{{ $setting->description }}</p>
                                    @endif

                                @elseif ($setting->type === 'json')
                                    {{-- JSON: textarea --}}
                                    <label for="{{ $setting->key }}" class="block text-sm font-medium text-gray-700 mb-1.5">
                                        {{ ucwords(str_replace('_', ' ', $setting->key)) }}
                                        <span class="ml-1 text-xs font-normal text-gray-400 font-mono">({{ $setting->key }})</span>
                                    </label>
                                    <textarea
                                        id="{{ $setting->key }}"
                                        name="{{ $setting->key }}"
                                        rows="4"
                                        class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm font-mono focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                    >{{ old($setting->key, $setting->value) }}</textarea>
                                    @if ($setting->description)
                                        <p class="mt-1.5 text-xs text-gray-500">{{ $setting->description }}</p>
                                    @endif

                                @else
                                    {{-- String: text or url input --}}
                                    @php
                                        $inputType = in_array($setting->key, $urlKeys) ? 'url' : 'text';
                                    @endphp
                                    <label for="{{ $setting->key }}" class="block text-sm font-medium text-gray-700 mb-1.5">
                                        {{ ucwords(str_replace('_', ' ', $setting->key)) }}
                                        <span class="ml-1 text-xs font-normal text-gray-400 font-mono">({{ $setting->key }})</span>
                                    </label>
                                    <input
                                        type="{{ $inputType }}"
                                        id="{{ $setting->key }}"
                                        name="{{ $setting->key }}"
                                        value="{{ old($setting->key, $setting->value) }}"
                                        class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                    >
                                    @if ($setting->description)
                                        <p class="mt-1.5 text-xs text-gray-500">{{ $setting->description }}</p>
                                    @endif
                                @endif
                            </div>
                        @endforeach
                    </div>

                    <!-- Save Button -->
                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-end">
                        <button
                            type="submit"
                            class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 text-white text-sm font-medium rounded-xl shadow-sm shadow-indigo-500/25 hover:bg-indigo-700 hover:shadow-indigo-500/40 transition-all"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Save {{ $label }}
                        </button>
                    </div>
                </form>
            </div>
        @empty
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-12 text-center">
                <svg class="w-12 h-12 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <p class="text-gray-500 text-sm">No system settings found. Run the seeder to populate defaults.</p>
                <p class="text-gray-400 text-xs mt-1 font-mono">php artisan db:seed --class=SystemSettingsSeeder</p>
            </div>
        @endforelse
    </div>
@endsection
