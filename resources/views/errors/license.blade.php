<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aktivasi Lisensi Diperlukan — IAMJOS</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: 'Outfit', sans-serif;
            background: radial-gradient(circle at top right, #fef2f2 0%, #f5f5f7 100%);
        }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen p-5">
    <div class="w-full max-w-lg bg-white/80 backdrop-blur-md rounded-[24px] shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-red-100 p-8 text-center">
        <!-- Shield Icon with Red Accent -->
        <div class="mx-auto w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mb-6 text-red-600">
            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-shield-alert"><path d="M20 13c0 5-3.5 7.5-7.66 9.7a1 1 0 0 1-.68 0C7.5 20.5 4 18 4 13V6a1 1 0 0 1 .76-.97l8-2a1 1 0 0 1 .48 0l8 2A1 1 0 0 1 20 6v7z"/><line x1="12" x2="12" y1="9" y2="13"/><line x1="12" x2="12.01" y1="17" y2="17"/></svg>
        </div>

        <!-- Heading -->
        <h1 class="text-2xl font-bold text-slate-900 mb-2">Akses Layanan Ditangguhkan</h1>
        <p class="text-slate-500 text-sm mb-6">Sistem mendeteksi masalah pada lisensi ekosistem jurnal Anda.</p>

        <!-- License Status Details Box -->
        <div class="bg-red-50/50 rounded-2xl border border-red-100/60 p-5 mb-6 text-left">
            <div class="flex justify-between items-center mb-3">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Status Sistem</span>
                <span class="px-2.5 py-0.5 bg-red-100 text-red-700 text-xs font-semibold rounded-full uppercase">{{ $status }}</span>
            </div>
            <div class="text-sm text-slate-600 leading-relaxed">
                {{ $message }}
            </div>
            <div class="mt-4 pt-3 border-t border-red-100/40 flex justify-between items-center text-[11px] text-slate-400">
                <span>Instance ID: <code class="font-mono bg-slate-100 px-1.5 py-0.5 rounded">{{ $instanceId }}</code></span>
                <span>Kode Error: <code class="font-mono text-red-500 font-semibold">{{ $code }}</code></span>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row gap-3 justify-center">
            <a href="mailto:support@iamjos.id?subject=Masalah%20Lisensi%20Instance%20{{ $instanceId }}" 
               class="px-5 py-3 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-xl text-sm transition shadow-lg shadow-red-600/10 flex items-center justify-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-mail"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                Hubungi Dukungan
            </a>
            <a href="/" 
               class="px-5 py-3 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-xl text-sm transition flex items-center justify-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-refresh-cw"><path d="M21 12a9 9 0 0 0-9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/><path d="M3 12a9 9 0 0 0 9 9 9.75 9.75 0 0 0 6.74-2.74L21 16"/><path d="M16 16h5v5"/></svg>
                Coba Lagi
            </a>
        </div>

        <p class="mt-8 text-[11px] text-slate-400">
            &copy; 2026 IAMJOS. Hak Cipta Dilindungi Undang-Undang.
        </p>
    </div>
</body>
</html>
