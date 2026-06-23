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
            'email'        => 'Email & SMTP Server',
            'pagination'   => 'Pagination & Display Limits',
            'uploads'      => 'File Upload Constraints',
            'reviewer'     => 'Reviewer Reminders',
            'integrations' => 'External Integrations',
            'app'          => 'Application',
        ];

        $groupIcons = [
            'email'        => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />',
            'pagination'   => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />',
            'uploads'      => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />',
            'reviewer'     => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />',
            'integrations' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />',
            'app'          => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />',
        ];

        // URL-like keys that should use type="url"
        $urlKeys = ['crossref_deposit_url_live', 'crossref_deposit_url_test', 'crossref_api_base_url', 'recaptcha_verify_url', 'google_scholar_search_url'];
    @endphp

    <div x-data="{ activeTab: localStorage.getItem('system_settings_active_tab') || 'email' }">
        @if ($settings->isNotEmpty())
            <div class="flex flex-wrap gap-2.5 mb-8 pb-4 border-b border-gray-100">
                @foreach ($settings as $group => $groupSettings)
                    @php
                        $tabLabel = $groupLabels[$group] ?? ucfirst($group);
                        $tabIcon  = $groupIcons[$group] ?? '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4" />';
                    @endphp
                    <button
                        type="button"
                        @click="activeTab = '{{ $group }}'; localStorage.setItem('system_settings_active_tab', '{{ $group }}')"
                        class="px-4 py-2.5 rounded-xl text-sm font-medium transition-all cursor-pointer inline-flex items-center gap-2 border"
                        :class="activeTab === '{{ $group }}' 
                            ? 'bg-blue-600 border-blue-600 text-white shadow-md shadow-blue-500/20' 
                            : 'bg-white border-slate-100 text-slate-600 hover:bg-slate-50 hover:text-slate-800 shadow-sm'"
                    >
                        <svg class="w-4 h-4 flex-shrink-0" :class="activeTab === '{{ $group }}' ? 'text-white' : 'text-slate-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="stroke-width: 2px;">
                            {!! $tabIcon !!}
                        </svg>
                        <span>{{ $tabLabel }}</span>
                    </button>
                @endforeach
            </div>
        @endif

        <div class="space-y-8 max-w-5xl">
            @forelse ($settings as $group => $groupSettings)
                @php
                    $label = $groupLabels[$group] ?? ucfirst($group);
                    $icon  = $groupIcons[$group] ?? '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 4a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />';
                    $isTwoCol = in_array($group, ['email', 'integrations', 'pagination', 'reviewer', 'uploads']);
                @endphp

                <div 
                    x-show="activeTab === '{{ $group }}'"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 translate-y-2"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    class="bg-white rounded-[24px] shadow-[0_8px_30px_rgb(0,0,0,0.04)] overflow-hidden"
                >
                    <!-- Card Header -->
                    <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-3">
                        <div class="w-9 h-9 bg-indigo-50 rounded-lg flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                {!! $icon !!}
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-[16px] font-semibold text-slate-800">{{ $label }}</h2>
                            <p class="text-xs text-gray-500 mt-0.5">{{ $groupSettings->count() }} setting{{ $groupSettings->count() !== 1 ? 's' : '' }}</p>
                        </div>
                    </div>

                    <!-- Form -->
                    <form action="{{ route('admin.system-settings.update') }}" method="POST">
                        @csrf

                        <div class="{{ $isTwoCol ? 'p-6 grid grid-cols-1 md:grid-cols-2 gap-6' : 'divide-y divide-gray-50' }}">
                            @foreach ($groupSettings as $setting)
                                <div class="{{ $isTwoCol ? ($setting->type === 'json' ? 'md:col-span-2 space-y-1.5' : 'space-y-1.5') : 'px-6 py-5' }}">
                                    @if ($setting->key === 'mail_mailer')
                                    {{-- Custom Mailer Dropdown --}}
                                    <label for="{{ $setting->key }}" class="block text-sm font-medium text-gray-700 mb-1.5">
                                        {{ ucwords(str_replace('_', ' ', $setting->key)) }}
                                        <span class="ml-1 text-xs font-normal text-gray-400 font-mono">({{ $setting->key }})</span>
                                    </label>
                                    <select
                                        id="{{ $setting->key }}"
                                        name="{{ $setting->key }}"
                                        class="w-full {{ $isTwoCol ? '' : 'sm:w-64' }} px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-white"
                                    >
                                        <option value="smtp" {{ old($setting->key, $setting->value) === 'smtp' ? 'selected' : '' }}>SMTP Server (Recommended)</option>
                                        <option value="phpmail" {{ old($setting->key, $setting->value) === 'phpmail' ? 'selected' : '' }}>PHP mail() Function</option>
                                        <option value="log" {{ old($setting->key, $setting->value) === 'log' ? 'selected' : '' }}>Log (Dev/Testing only)</option>
                                    </select>
                                    @if ($setting->description)
                                        <p class="mt-1.5 text-xs text-gray-500">{{ $setting->description }}</p>
                                    @endif

                                @elseif ($setting->key === 'mail_encryption')
                                    {{-- Custom Encryption Dropdown --}}
                                    <label for="{{ $setting->key }}" class="block text-sm font-medium text-gray-700 mb-1.5">
                                        {{ ucwords(str_replace('_', ' ', $setting->key)) }}
                                        <span class="ml-1 text-xs font-normal text-gray-400 font-mono">({{ $setting->key }})</span>
                                    </label>
                                    <select
                                        id="{{ $setting->key }}"
                                        name="{{ $setting->key }}"
                                        class="w-full {{ $isTwoCol ? '' : 'sm:w-64' }} px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-white"
                                    >
                                        <option value="none" {{ old($setting->key, $setting->value) === 'none' ? 'selected' : '' }}>None (Plain text)</option>
                                        <option value="tls" {{ old($setting->key, $setting->value) === 'tls' ? 'selected' : '' }}>TLS (STARTTLS - port 587)</option>
                                        <option value="ssl" {{ old($setting->key, $setting->value) === 'ssl' ? 'selected' : '' }}>SSL (SMTPS - port 465)</option>
                                    </select>
                                    @if ($setting->description)
                                        <p class="mt-1.5 text-xs text-gray-500">{{ $setting->description }}</p>
                                    @endif

                                @elseif ($setting->key === 'mail_queue_connection')
                                    {{-- Custom Queue Connection Dropdown --}}
                                    <label for="{{ $setting->key }}" class="block text-sm font-medium text-gray-700 mb-1.5">
                                        Mail Queue Connection
                                        <span class="ml-1 text-xs font-normal text-gray-400 font-mono">({{ $setting->key }})</span>
                                    </label>
                                    <select
                                        id="{{ $setting->key }}"
                                        name="{{ $setting->key }}"
                                        class="w-full {{ $isTwoCol ? '' : 'sm:w-64' }} px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-white"
                                    >
                                        <option value="sync" {{ old($setting->key, $setting->value) === 'sync' ? 'selected' : '' }}>Direct Send (No Server Setup / cPanel)</option>
                                        <option value="database" {{ old($setting->key, $setting->value) === 'database' ? 'selected' : '' }}>Database Queue (Recommended with Cron)</option>
                                        <option value="redis" {{ old($setting->key, $setting->value) === 'redis' ? 'selected' : '' }}>Redis Queue (High Performance / Supervisor)</option>
                                    </select>
                                    @if ($setting->description)
                                        <p class="mt-1.5 text-xs text-gray-500">{{ $setting->description }}</p>
                                    @endif

                                @elseif ($setting->key === 'mail_password')
                                    {{-- Custom Password Field with Show/Hide Toggle --}}
                                    <label for="{{ $setting->key }}" class="block text-sm font-medium text-gray-700 mb-1.5">
                                        {{ ucwords(str_replace('_', ' ', $setting->key)) }}
                                        <span class="ml-1 text-xs font-normal text-gray-400 font-mono">({{ $setting->key }})</span>
                                    </label>
                                    <div class="relative w-full {{ $isTwoCol ? '' : 'sm:w-80' }}" x-data="{ show: false }">
                                        <input
                                            :type="show ? 'text' : 'password'"
                                            id="{{ $setting->key }}"
                                            name="{{ $setting->key }}"
                                            value="{{ old($setting->key, $setting->value) }}"
                                            class="w-full px-4 py-2.5 pr-10 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                        >
                                        <button
                                            type="button"
                                            @click="show = !show"
                                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 focus:outline-none"
                                        >
                                            <i class="fa-solid" :class="show ? 'fa-eye-slash' : 'fa-eye'"></i>
                                        </button>
                                    </div>
                                    @if ($setting->description)
                                        <p class="mt-1.5 text-xs text-gray-500">{{ $setting->description }}</p>
                                    @endif

                                @elseif ($setting->type === 'boolean')
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
                                        class="w-full {{ $isTwoCol ? '' : 'sm:w-64' }} px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
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

                        @if ($group === 'email')
                            {{-- Test Connection Section --}}
                            <div class="px-6 py-6 bg-slate-50 border-t border-gray-100">
                                <h3 class="text-sm font-semibold text-slate-800 mb-1">Test SMTP Configuration</h3>
                                <p class="text-xs text-slate-500 mb-4">Send a test email to verify that your SMTP server is configured correctly. Save your settings before testing.</p>
                                
                                <div class="flex flex-col sm:flex-row gap-3" x-data="testEmailHandler()">
                                    <input 
                                        type="email" 
                                        x-model="email" 
                                        placeholder="recipient@example.com" 
                                        class="px-4 py-2.5 border border-gray-200 rounded-xl text-sm w-full sm:w-80 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                    >
                                    <button 
                                        type="button" 
                                        @click="sendTestEmail()"
                                        :disabled="loading || !email"
                                        class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-slate-800 hover:bg-slate-900 disabled:bg-slate-400 text-white text-sm font-medium rounded-xl shadow-sm transition-all cursor-pointer"
                                    >
                                        <svg x-show="loading" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        <span x-text="loading ? 'Sending...' : 'Send Test Email'">Send Test Email</span>
                                    </button>
                                </div>
                                
                                <div x-show="statusMessage" class="mt-4 p-4 rounded-xl text-sm" :class="statusType === 'success' ? 'bg-emerald-50 text-emerald-800 border border-emerald-100' : 'bg-red-50 text-red-800 border border-red-100'" style="display: none;">
                                    <div class="flex items-start gap-2.5">
                                        <div class="flex-shrink-0 mt-0.5">
                                            <i class="fa-solid" :class="statusType === 'success' ? 'fa-circle-check text-emerald-500' : 'fa-circle-exclamation text-red-500'"></i>
                                        </div>
                                        <div class="flex-1" x-text="statusMessage"></div>
                                    </div>
                                </div>
                            </div>
                        @endif
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
            <div class="bg-white rounded-[24px] shadow-[0_8px_30px_rgb(0,0,0,0.04)] p-12 text-center max-w-2xl mx-auto">
                <div class="w-16 h-16 bg-blue-50 text-blue-600 rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </div>
                
                <h3 class="text-lg font-semibold text-slate-800 mb-2">No System Settings Found</h3>
                <p class="text-slate-500 text-sm mb-8 leading-relaxed">
                    The system configuration table is currently empty. Initialize the database with default settings to configure email/SMTP, file constraints, integrations, and other parameters.
                </p>

                <form action="{{ route('admin.system-settings.seed') }}" method="POST" class="inline-block">
                    @csrf
                    <button
                        type="submit"
                        class="inline-flex items-center gap-2 px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-xl shadow-lg shadow-blue-500/20 hover:shadow-blue-500/35 transition-all cursor-pointer"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                        Initialize Default Settings
                    </button>
                </form>

                <div class="mt-8 pt-6 border-t border-gray-100">
                    <p class="text-gray-400 text-xs">Alternative CLI Method:</p>
                    <code class="block mt-2 px-3 py-2 bg-slate-50 border border-slate-100 rounded-lg text-slate-600 text-xs font-mono select-all">php artisan db:seed --class=SystemSettingsSeeder</code>
                </div>
            </div>
        @endforelse
    </div>
</div>
@endsection

@push('scripts')
<script>
    function testEmailHandler() {
        return {
            email: '',
            loading: false,
            statusMessage: '',
            statusType: '',

            sendTestEmail() {
                if (!this.email) return;
                this.loading = true;
                this.statusMessage = '';
                this.statusType = '';

                fetch("{{ route('admin.system-settings.test-email') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ email: this.email })
                })
                .then(response => response.json().then(data => ({ status: response.status, body: data })))
                .then(res => {
                    this.loading = false;
                    this.statusMessage = res.body.message || (res.status === 200 ? 'Test email sent successfully!' : 'Failed to send test email.');
                    if (res.status === 200 && res.body.success) {
                        this.statusType = 'success';
                    } else {
                        this.statusType = 'error';
                    }
                })
                .catch(error => {
                    this.loading = false;
                    this.statusType = 'error';
                    this.statusMessage = 'An unexpected error occurred: ' + error.message;
                });
            }
        };
    }
</script>
@endpush
