@extends('layouts.admin')

@section('title', 'System Information')

@section('content')
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900">System & Maintenance</h1>
        <p class="mt-1 text-gray-500">View server status and perform maintenance tasks.</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        <!-- Extended System Information -->
        <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden shadow-sm">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-bold text-gray-900">System Information</h2>
                    <p class="text-sm text-gray-500">Server and environment details</p>
                </div>
                <div class="w-10 h-10 bg-slate-100 rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01" />
                    </svg>
                </div>
            </div>

            <div class="p-6 space-y-4">
                <!-- Server Environment -->
                <div class="space-y-3">
                    <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Server Environment</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="bg-gray-50 rounded-xl p-4">
                            <p class="text-xs text-gray-500 mb-1">Operating System</p>
                            <p class="font-mono text-sm font-medium text-gray-900 truncate"
                                title="{{ $systemInfo['operating_system'] }}">
                                {{ $systemInfo['operating_system'] }}
                            </p>
                        </div>
                        <div class="bg-gray-50 rounded-xl p-4">
                            <p class="text-xs text-gray-500 mb-1">PHP Version</p>
                            <p class="font-mono text-sm font-medium text-gray-900">{{ $systemInfo['php_version'] }}</p>
                        </div>
                    </div>
                </div>

                <!-- Database -->
                <div class="space-y-3">
                    <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Database</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="bg-gray-50 rounded-xl p-4">
                            <p class="text-xs text-gray-500 mb-1">Driver / Version</p>
                            <p class="font-mono text-sm font-medium text-gray-900">
                                {{ ucfirst($systemInfo['database_driver']) }}
                                <span
                                    class="text-xs text-gray-500 block sm:inline">v{{ $systemInfo['database_version'] }}</span>
                            </p>
                        </div>
                        <div class="bg-gray-50 rounded-xl p-4">
                            <p class="text-xs text-gray-500 mb-1">Laravel Version</p>
                            <p class="font-mono text-sm font-medium text-gray-900">{{ $systemInfo['laravel_version'] }}</p>
                        </div>
                    </div>
                </div>

                <!-- Limits -->
                <div class="space-y-3">
                    <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider">PHP Configuration</h3>
                    <div class="grid grid-cols-3 gap-4">
                        <div class="bg-gray-50 rounded-xl p-4">
                            <p class="text-xs text-gray-500 mb-1">Memory Limit</p>
                            <p class="font-mono text-sm font-medium text-gray-900">{{ $systemInfo['memory_limit'] }}</p>
                        </div>
                        <div class="bg-gray-50 rounded-xl p-4">
                            <p class="text-xs text-gray-500 mb-1">Max Execution</p>
                            <p class="font-mono text-sm font-medium text-gray-900">{{ $systemInfo['max_execution_time'] }}s
                            </p>
                        </div>
                        <div class="bg-gray-50 rounded-xl p-4">
                            <p class="text-xs text-gray-500 mb-1">Upload Size</p>
                            <p class="font-mono text-sm font-medium text-gray-900">{{ $systemInfo['upload_max_filesize'] }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Extensions -->
                <div class="space-y-3">
                    <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Key Extensions</h3>
                    <div class="flex flex-wrap gap-2">
                        @php
                            $extensions = [
                                'GD Library' => extension_loaded('gd'),
                                'Phar' => extension_loaded('phar'),
                                'Intl' => extension_loaded('intl'),
                                'Zip' => extension_loaded('zip'),
                                'Curl' => extension_loaded('curl'),
                                'OpenSSL' => extension_loaded('openssl'),
                                'Fileinfo' => extension_loaded('fileinfo'),
                                'Mbstring' => extension_loaded('mbstring'),
                            ];
                        @endphp
                        @foreach ($extensions as $name => $loaded)
                            <span
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-medium
                                         {{ $loaded ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-700' }}">
                                @if ($loaded)
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 13l4 4L19 7" />
                                    </svg>
                                @else
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                @endif
                                {{ $name }}
                            </span>
                        @endforeach
                    </div>
                </div>

                <!-- Application Info -->
                <div class="pt-4 border-t border-gray-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs text-gray-500">Application Version</p>
                            <p class="font-mono text-sm font-medium text-gray-900">{{ $systemInfo['version'] }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-xs text-gray-500">Server Time</p>
                            <p class="font-mono text-sm font-medium text-gray-900">{{ $systemInfo['server_date'] }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Administrative Tools -->
        <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden shadow-sm">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-bold text-gray-900">Administrative Tools</h2>
                    <p class="text-sm text-gray-500">Maintenance and system actions</p>
                </div>
                <div class="w-10 h-10 bg-red-50 rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
            </div>

            <div class="p-6 space-y-4 h-full flex flex-col">
                <!-- Malware Guard -->
                <div class="bg-gray-50 rounded-xl p-4">
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-semibold text-gray-900">Malware Guard</h3>
                            <p class="text-sm text-gray-500 mt-1">Check system integrity and scan for suspicious files.</p>
                            <a href="{{ route('admin.malware.index') }}"
                                class="inline-block mt-3 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors">
                                Open Scanner
                            </a>
                        </div>
                    </div>
                </div>
                <!-- Expire Sessions -->
                <div class="bg-gray-50 rounded-xl p-4">
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-semibold text-gray-900">Expire User Sessions</h3>
                            <p class="text-sm text-gray-500 mt-1">Log out all users immediately. They will need to sign in
                                again.</p>
                            <form action="{{ route('admin.site.expire-sessions') }}" method="POST" class="mt-3">
                                @csrf
                                <button type="submit"
                                    onclick="return confirm('This will log out ALL users immediately. Are you sure?')"
                                    class="px-4 py-2 bg-orange-600 text-white text-sm font-medium rounded-lg hover:bg-orange-700 transition-colors">
                                    Expire Sessions
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Clear Data Cache -->
                <div class="bg-gray-50 rounded-xl p-4">
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4" />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-semibold text-gray-900">Clear Data Cache</h3>
                            <p class="text-sm text-gray-500 mt-1">Clear database query cache and application cache.</p>
                            <form action="{{ route('admin.site.clear-cache') }}" method="POST" class="mt-3">
                                @csrf
                                <button type="submit"
                                    class="px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 transition-colors">
                                    Clear Cache
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Clear Template Cache -->
                <div class="bg-gray-50 rounded-xl p-4">
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 bg-amber-100 rounded-lg flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6z" />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-semibold text-gray-900">Clear Template Cache</h3>
                            <p class="text-sm text-gray-500 mt-1">Force re-compilation of all Blade templates.</p>
                            <form action="{{ route('admin.site.clear-templates') }}" method="POST" class="mt-3">
                                @csrf
                                <button type="submit"
                                    class="px-4 py-2 bg-amber-600 text-white text-sm font-medium rounded-lg hover:bg-amber-700 transition-colors">
                                    Clear Templates
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Clear Scheduled Task Logs -->
                <div class="bg-gray-50 rounded-xl p-4">
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 bg-slate-200 rounded-lg flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-semibold text-gray-900">Clear Scheduled Task Logs</h3>
                            <p class="text-sm text-gray-500 mt-1">Remove old execution logs from scheduled tasks.</p>
                            <form action="{{ route('admin.site.clear-logs') }}" method="POST" class="mt-3">
                                @csrf
                                <button type="submit"
                                    class="px-4 py-2 bg-slate-600 text-white text-sm font-medium rounded-lg hover:bg-slate-700 transition-colors">
                                    Clear Logs
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection
