@php
    $isId = app()->getLocale() === 'id';

    // Helper untuk menerjemahkan label
    $translateLabel = function($label) use ($isId) {
        if (!$isId) return $label;
        $labels = [
            'Article Title' => 'Judul Artikel',
            'Abstract' => 'Abstrak',
            'Authors' => 'Penulis',
            'Authors Affiliation' => 'Afiliasi Penulis',
            'Author Names' => 'Nama Penulis',
            'Keywords' => 'Kata Kunci',
            'References & Citations' => 'Referensi & Sitasi',
            'Galley (PDF)' => 'Galley (PDF)',
            'Publication Date' => 'Tanggal Publikasi'
        ];
        return $labels[$label] ?? $label;
    };

    // Helper untuk menerjemahkan message
    $translateMessage = function($msg) use ($isId) {
        if (!$isId) return $msg;

        // Title Check
        if ($msg === 'Title is empty.') return 'Judul kosong.';
        if ($msg === 'Title should not be in all capital letters.') return 'Judul tidak boleh menggunakan huruf kapital semua.';
        if (preg_match('/Title is too short \((\d+) words\)\. Aim for 10-20 words\./', $msg, $matches)) {
            return "Judul terlalu pendek ({$matches[1]} kata). Targetkan 10-20 kata.";
        }
        if (preg_match('/Title is quite long \((\d+) words\)\. Optimal is 10-20 words\./', $msg, $matches)) {
            return "Judul cukup panjang ({$matches[1]} kata). Optimalnya adalah 10-20 kata.";
        }
        if ($msg === 'Perfect title length and formatting.') return 'Panjang judul dan pemformatan sempurna.';

        // Abstract Check
        if ($msg === 'Abstract is missing.') return 'Abstrak tidak ditemukan.';
        if (preg_match('/Abstract is too short \((\d+) words\)\. Minimum 100 words required\./', $msg, $matches)) {
            return "Abstrak terlalu pendek ({$matches[1]} kata). Diperlukan minimal 100 kata.";
        }
        if (preg_match('/Abstract is a bit long \((\d+) words\)\. Recommended maximum is 300 words\./', $msg, $matches)) {
            return "Abstrak agak panjang ({$matches[1]} kata). Rekomendasi maksimum adalah 300 kata.";
        }
        if ($msg === 'Abstract length is optimal.') return 'Panjang abstrak optimal.';

        // Authors Check
        if ($msg === 'No authors listed.') return 'Tidak ada penulis yang terdaftar.';
        if (preg_match('/Missing affiliation for: (.*?)\./', $msg, $matches)) {
            return "Afiliasi tidak ditemukan untuk: {$matches[1]}.";
        }
        if (preg_match('/Some authors have single names \((.*?)\)\. "First Last" format is preferred\./', $msg, $matches)) {
            return "Beberapa penulis hanya memiliki satu nama ({$matches[1]}). Format \"Nama Depan Nama Belakang\" lebih disukai.";
        }
        if ($msg === 'All authors have valid names and affiliations.') return 'Semua penulis memiliki nama dan afiliasi yang valid.';

        // Keywords Check
        if (preg_match('/Too few keywords \((\d+)\)\. Minimum 3 required\./', $msg, $matches)) {
            return "Kata kunci terlalu sedikit ({$matches[1]}). Diperlukan minimal 3 kata kunci.";
        }
        if (preg_match('/Too many keywords \((\d+)\)\. Recommended maximum is 6\./', $msg, $matches)) {
            return "Kata kunci terlalu banyak ({$matches[1]}). Rekomendasi maksimum adalah 6.";
        }
        if ($msg === 'Keyword count is optimal.') return 'Jumlah kata kunci optimal.';

        // References Check
        if ($msg === 'No references provided. Google Scholar requires citations to trace the citation graph.') {
            return 'Tidak ada referensi yang disediakan. Google Scholar memerlukan sitasi untuk melacak grafik kutipan.';
        }
        if (preg_match('/Reference count is low \((\d+)\)\. Google Scholar prefers at least 10\+ robust citations\./', $msg, $matches)) {
            return "Jumlah referensi rendah ({$matches[1]}). Google Scholar menyukai setidaknya 10+ sitasi yang kuat.";
        }
        if (preg_match('/Good number of references found \((\d+)\)\./', $msg, $matches)) {
            return "Jumlah referensi yang baik ditemukan ({$matches[1]}).";
        }

        // Galleys Check
        if ($msg === 'No publication galleys found. At least one PDF galley is required.') {
            return 'Tidak ada galley publikasi yang ditemukan. Setidaknya diperlukan satu galley PDF.';
        }
        if ($msg === 'Galley files are available.') return 'File galley tersedia.';

        // Publication Date Check
        if ($msg === 'Publication date is not set.') return 'Tanggal publikasi belum diatur.';
        if ($msg === 'Publication date is set.') return 'Tanggal publikasi telah diatur.';

        return $msg;
    };
@endphp
<div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden h-full">
    {{-- Header --}}
    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex justify-between items-center">
        <h3 class="font-bold text-gray-900 flex items-center gap-2">
            <i class="fa-brands fa-google-scholar text-blue-500 text-lg"></i>
            {{ $isId ? 'Peramal Google Scholar' : 'Google Scholar Forecaster' }}
        </h3>

        @php
            $statusColor = match ($analysis['status']) {
                'good' => 'text-green-600 bg-green-50 border-green-200',
                'warning' => 'text-amber-600 bg-amber-50 border-amber-200',
                'bad' => 'text-red-600 bg-red-50 border-red-200',
                default => 'text-gray-600 bg-gray-50 border-gray-200',
            };

            $scoreColor = match ($analysis['status']) {
                'good' => 'text-green-500',
                'warning' => 'text-amber-500',
                'bad' => 'text-red-500',
                default => 'text-gray-500',
            };

            $progressColor = match ($analysis['status']) {
                'good' => 'text-green-500',
                'warning' => 'text-amber-500',
                'bad' => 'text-red-500',
                default => 'text-gray-500',
            };
        @endphp

        <span class="px-3 py-1 rounded-full text-xs font-bold border {{ $statusColor }}">
            {{ $isId ? ['good' => 'Baik', 'warning' => 'Peringatan', 'bad' => 'Buruk'][$analysis['status']] ?? ucfirst($analysis['status']) : ucfirst($analysis['status']) }}
        </span>
    </div>

    <div class="p-6">
        {{-- Score Section --}}
        <div class="flex flex-col items-center justify-center mb-10">
            {{-- Circular Progress --}}
            <div class="relative w-32 h-32">
                <svg class="w-full h-full transform -rotate-90" viewBox="0 0 100 100">
                    <circle class="text-gray-100" stroke-width="8" stroke="currentColor" fill="transparent" r="40"
                        cx="50" cy="50" />
                    <circle class="{{ $progressColor }} transition-all duration-1000 ease-out" stroke-width="8"
                        stroke-linecap="round" stroke="currentColor" fill="transparent" r="40" cx="50"
                        cy="50" stroke-dasharray="251.2"
                        stroke-dashoffset="{{ 251.2 - (251.2 * $analysis['score']) / 100 }}" />
                </svg>
                <div class="absolute inset-0 flex items-center justify-center">
                    <span class="text-4xl font-extrabold {{ $scoreColor }}">{{ $analysis['score'] }}</span>
                </div>
            </div>
            <div class="mt-2 text-center">
                <span class="text-xs text-gray-500 uppercase font-bold tracking-wider">{{ $isId ? 'Skor Persyaratan Pengindeksan' : 'Indexing Requirement Score' }}</span>
            </div>
        </div>

        {{-- Checklist --}}
        <div class="space-y-4">
            @foreach ($analysis['checks'] as $check)
                <div
                    class="flex items-start gap-3 p-3 rounded-lg hover:bg-gray-50 transition-colors border border-transparent hover:border-gray-100">
                    <div class="flex-shrink-0 mt-0.5">
                        @if ($check['status'] === true)
                            <div class="w-5 h-5 rounded-full bg-green-100 flex items-center justify-center">
                                <i class="fa-solid fa-check text-green-600 text-xs"></i>
                            </div>
                        @elseif($check['status'] === 'warning')
                            <div class="w-5 h-5 rounded-full bg-amber-100 flex items-center justify-center">
                                <i class="fa-solid fa-exclamation text-amber-600 text-xs"></i>
                            </div>
                        @else
                            <div class="w-5 h-5 rounded-full bg-red-100 flex items-center justify-center">
                                <i class="fa-solid fa-xmark text-red-600 text-xs"></i>
                            </div>
                        @endif
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-gray-900">{{ $translateLabel($check['label']) }}</p>
                        <p class="text-xs text-gray-600 mt-0.5">{{ $translateMessage($check['message']) }}</p>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-6 pt-4 border-t border-gray-100">
            <p class="text-xs text-center text-gray-400">
                <i class="fa-solid fa-circle-info mr-1"></i>
                {{ $isId ? 'Berdasarkan Panduan Pengindeksan Google Scholar' : 'Based on Google Scholar Indexing Guidelines' }}
            </p>
        </div>
    </div>
</div>
