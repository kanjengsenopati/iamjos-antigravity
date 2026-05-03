<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>419 - Sesi Berakhir | IamJOS</title>
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
            overflow: hidden;
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
        <div class="absolute -bottom-[20%] -right-[10%] w-[60vw] h-[60vw] bg-emerald-600/5 rounded-full blur-[120px] animate-float" style="animation-delay: -5s;"></div>
    </div>

    <div class="relative z-10 w-full max-w-[580px] glass-card rounded-[40px] p-12 md:p-16 text-center shadow-2xl transition-all duration-500 hover:shadow-blue-500/5">
        <!-- Logo Section -->
        <div class="mb-10 flex items-center justify-center">
            <span class="text-4xl font-extrabold tracking-tight bg-gradient-to-r from-blue-400 to-blue-600 bg-clip-text text-transparent">IamJOS</span>
        </div>

        <!-- Big 419 -->
        <div class="mb-6">
            <h1 class="text-[120px] font-black text-white leading-none tracking-tighter opacity-90">419</h1>
        </div>
        
        <!-- Main Message -->
        <div class="mb-10">
            <h2 class="text-2xl font-bold text-white mb-4">Sesi Telah Berakhir</h2>
            <p class="text-slate-400 text-lg leading-relaxed max-w-sm mx-auto">
                Maaf, sesi Anda telah habis karena tidak ada aktivitas dalam waktu lama. Silakan masuk kembali untuk melanjutkan pekerjaan Anda.
            </p>
        </div>

        <!-- Action Button -->
        <div class="mt-4 flex flex-col gap-3">
            <a href="{{ route('login') }}" 
                    class="group relative inline-flex items-center justify-center w-full py-4 px-8 bg-blue-600 hover:bg-blue-500 text-white font-bold text-lg rounded-2xl shadow-xl shadow-blue-900/40 transition-all active:scale-[0.98] overflow-hidden">
                <span class="relative z-10">Masuk Kembali</span>
                <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/10 to-transparent translate-x-[-100%] group-hover:translate-x-[100%] transition-transform duration-700"></div>
            </a>
            <a href="{{ url('/') }}" class="text-slate-500 hover:text-slate-300 transition-colors font-semibold py-2">
                Kembali ke Beranda
            </a>
        </div>

        <!-- Footer -->
        <div class="mt-12 pt-8 border-t border-white/5">
            <p class="text-xs text-slate-500 font-medium uppercase tracking-[0.3em]">
                &copy; {{ date('Y') }} {{ config('app.name', 'IAMJOS') }}
            </p>
        </div>
    </div>
</body>
</html>
