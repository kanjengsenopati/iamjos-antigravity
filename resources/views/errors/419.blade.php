@php
    $journal = null;
    try {
        if (function_exists('current_journal')) {
            $journal = current_journal();
        }
    } catch (\Throwable $e) {
        $journal = null;
    }

    $homeUrl = '/';
    $loginUrl = '/login';
    try {
        if ($journal && isset($journal->slug)) {
            $homeUrl = route('journal.public.home', $journal->slug);
            $loginUrl = route('journal.login', $journal->slug);
        } else {
            $homeUrl = url('/');
            $loginUrl = route('login');
        }
    } catch (\Throwable $e) {
        $homeUrl = '/';
        $loginUrl = '/login';
    }

    $siteName = $journal && !empty($journal->name) ? $journal->name : config('app.name', 'IAMJOS');
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>419 - Sesi Berakhir | {{ $siteName }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        :root {
            --primary: #2563EB;
            --surface: rgba(255, 255, 255, 0.05);
            --surface-border: rgba(255, 255, 255, 0.1);
        }
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            overflow-x: hidden;
        }
        .glass-card {
            background: var(--surface);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1px solid var(--surface-border);
        }
        @keyframes float {
            0%, 100% { transform: translate(0, 0) scale(1); }
            50% { transform: translate(20px, 20px) scale(1.05); }
        }
        .animate-float {
            animation: float 15s ease-in-out infinite;
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-6 selection:bg-blue-500/30">
    <!-- Abstract Background Elements -->
    <div class="fixed inset-0 z-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-[10%] -left-[10%] w-[50vw] h-[50vw] bg-blue-600/10 rounded-full blur-[120px] animate-float"></div>
        <div class="absolute -bottom-[20%] -right-[10%] w-[60vw] h-[60vw] bg-amber-500/10 rounded-full blur-[120px] animate-float" style="animation-delay: -5s;"></div>
    </div>

    <div class="relative z-10 w-full max-w-[580px] glass-card rounded-[40px] p-8 sm:p-12 md:p-16 text-center shadow-2xl transition-all duration-500">
        <!-- Logo Section -->
        <div class="mb-8 flex items-center justify-center">
            <span class="text-3xl sm:text-4xl font-extrabold tracking-tight bg-gradient-to-r from-blue-400 to-blue-600 bg-clip-text text-transparent">
                {{ $siteName }}
            </span>
        </div>

        <!-- Big 419 -->
        <div class="mb-4">
            <h1 class="text-[100px] sm:text-[120px] font-black text-white leading-none tracking-tighter opacity-90">419</h1>
        </div>
        
        <!-- Main Message -->
        <div class="mb-8">
            <h2 class="text-xl sm:text-2xl font-bold text-white mb-3">Sesi Telah Berakhir</h2>
            <p class="text-slate-300 text-sm sm:text-base leading-relaxed max-w-md mx-auto">
                Demi keamanan akun Anda, sesi penjelajahan telah kedaluwarsa karena tidak ada aktivitas dalam waktu lama.
            </p>
            <p class="text-slate-400 text-xs sm:text-sm mt-3 leading-relaxed">
                Silakan masuk kembali untuk melanjutkan pekerjaan atau aktivitas Anda.
            </p>
        </div>

        <!-- Action Buttons -->
        <div class="mt-4 flex flex-col gap-3">
            <a href="{{ $loginUrl }}" 
               class="group relative inline-flex items-center justify-center w-full py-3.5 px-6 bg-blue-600 hover:bg-blue-500 text-white font-bold text-base sm:text-lg rounded-2xl shadow-xl shadow-blue-900/40 transition-all active:scale-[0.98] overflow-hidden cursor-pointer">
                <span class="relative z-10 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                    </svg>
                    Masuk Kembali
                </span>
                <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/10 to-transparent translate-x-[-100%] group-hover:translate-x-[100%] transition-transform duration-700"></div>
            </a>

            <a href="{{ $homeUrl }}" 
               class="inline-flex items-center justify-center text-slate-400 hover:text-white transition-colors text-sm font-semibold py-2">
                Kembali ke Beranda &rarr;
            </a>
        </div>

        <!-- Footer -->
        <div class="mt-10 pt-6 border-t border-white/5">
            <p class="text-xs text-slate-500 font-medium uppercase tracking-[0.3em]">
                &copy; {{ date('Y') }} {{ $siteName }}
            </p>
        </div>
    </div>
</body>
</html>
