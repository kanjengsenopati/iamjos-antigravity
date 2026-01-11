<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Journal - {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    colors: {
                        primary: {
                            50: '#f0f9ff',
                            100: '#e0f2fe',
                            200: '#bae6fd',
                            300: '#7dd3fc',
                            400: '#38bdf8',
                            500: '#0ea5e9',
                            600: '#0284c7',
                            700: '#0369a1',
                            800: '#075985',
                            900: '#0c4a6e',
                        }
                    }
                }
            }
        }
    </script>
</head>

<body class="font-sans antialiased bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 min-h-screen">
    <!-- Background Pattern -->
    <div class="fixed inset-0 opacity-5">
        <div class="absolute inset-0"
            style="background-image: url('data:image/svg+xml,%3Csvg width=\"60\" height=\"60\" viewBox=\"0 0 60 60\" xmlns=\"http://www.w3.org/2000/svg\"%3E%3Cg fill=\"none\" fill-rule=\"evenodd\"%3E%3Cg fill=\"%23ffffff\" fill-opacity=\"1\"%3E%3Ccircle cx=\"3\" cy=\"3\" r=\"2\"/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');">
        </div>
    </div>

    <div class="relative min-h-screen flex flex-col items-center justify-center px-4 py-12">
        <!-- Header -->
        <div class="text-center mb-12">
            <div
                class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br from-primary-500 to-primary-700 rounded-2xl shadow-lg shadow-primary-500/30 mb-6">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
            </div>
            <h1 class="text-3xl sm:text-4xl font-bold text-white mb-3">Select Your Journal</h1>
            <p class="text-lg text-gray-400 max-w-md mx-auto">
                Choose a journal to access its dashboard and manage content
            </p>
        </div>

        <!-- User Info -->
        <div
            class="flex items-center gap-3 mb-8 px-5 py-3 bg-white/10 backdrop-blur-sm rounded-xl border border-white/10">
            <div
                class="w-10 h-10 bg-gradient-to-br from-primary-500 to-primary-700 rounded-full flex items-center justify-center">
                <span class="text-white text-sm font-semibold">{{ auth()->user()->initials }}</span>
            </div>
            <div>
                <p class="text-white font-medium">{{ auth()->user()->name }}</p>
                <p class="text-gray-400 text-sm">{{ auth()->user()->email }}</p>
            </div>
            <form action="{{ route('logout') }}" method="POST" class="ml-4">
                @csrf
                <button type="submit" class="text-gray-400 hover:text-white transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                </button>
            </form>
        </div>

        <!-- Journals Grid -->
        <div class="w-full max-w-5xl">
            @if ($journals->isEmpty())
                <div class="text-center py-16 bg-white/5 backdrop-blur-sm rounded-2xl border border-white/10">
                    <svg class="w-16 h-16 text-gray-500 mx-auto mb-4" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                    <h3 class="text-xl font-semibold text-white mb-2">No Journals Available</h3>
                    <p class="text-gray-400">Contact your administrator to set up journals.</p>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach ($journals as $journal)
                        <a href="{{ route('journal.dashboard', ['journal' => $journal->slug]) }}"
                            class="group relative bg-white/5 backdrop-blur-sm rounded-2xl border border-white/10 p-6 hover:bg-white/10 hover:border-primary-500/50 transition-all duration-300 hover:shadow-lg hover:shadow-primary-500/10">

                            <!-- Journal Logo/Icon -->
                            <div class="flex items-start justify-between mb-4">
                                @if ($journal->logo_path)
                                    <img src="{{ Storage::url($journal->logo_path) }}" alt="{{ $journal->name }}"
                                        class="w-16 h-16 rounded-xl object-contain bg-white p-2">
                                @else
                                    <div
                                        class="w-16 h-16 bg-gradient-to-br from-primary-500 to-primary-700 rounded-xl flex items-center justify-center">
                                        <span
                                            class="text-2xl font-bold text-white">{{ strtoupper(substr($journal->abbreviation ?? $journal->name, 0, 2)) }}</span>
                                    </div>
                                @endif
                                <span
                                    class="px-2 py-1 text-xs font-medium rounded-full {{ $journal->enabled ? 'bg-green-500/20 text-green-400' : 'bg-gray-500/20 text-gray-400' }}">
                                    {{ $journal->enabled ? 'Active' : 'Inactive' }}
                                </span>
                            </div>

                            <!-- Journal Info -->
                            <h3
                                class="text-lg font-semibold text-white mb-2 group-hover:text-primary-400 transition-colors">
                                {{ $journal->name }}
                            </h3>
                            @if ($journal->abbreviation)
                                <p class="text-sm text-primary-400 mb-2">{{ $journal->abbreviation }}</p>
                            @endif
                            <p class="text-sm text-gray-400 line-clamp-2 mb-4">
                                {{ $journal->description ?? 'No description available.' }}
                            </p>

                            <!-- Stats -->
                            <div class="flex items-center gap-4 text-xs text-gray-500">
                                @if ($journal->issn_print || $journal->issn_online)
                                    <span class="flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                        </svg>
                                        ISSN
                                    </span>
                                @endif
                                <span class="flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    {{ $journal->submissions()->count() }} articles
                                </span>
                            </div>

                            <!-- Arrow Icon -->
                            <div class="absolute right-4 bottom-4 opacity-0 group-hover:opacity-100 transition-opacity">
                                <svg class="w-6 h-6 text-primary-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5l7 7-7 7" />
                                </svg>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Footer -->
        <div class="mt-12 text-center text-sm text-gray-500">
            <p>© {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
        </div>
    </div>
</body>

</html>
