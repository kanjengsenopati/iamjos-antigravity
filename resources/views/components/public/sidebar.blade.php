{{-- Dynamic Public Sidebar Component (OJS 3.3 Style) --}}
@props(['journal', 'sidebarBlocks' => collect()])

@php
    $primaryColor = $journal->getWebsiteSettings()['primary_color'] ?? '#0369a1';
@endphp

<div class="space-y-6">
    @forelse($sidebarBlocks as $block)
        @php
            // Normalize type: 'custom' is treated as 'block'
            $type = $block->type ?? 'block';
            if ($type === 'custom') {
                $type = 'block';
            }
        @endphp

        @if ($type === 'page' && $block->slug)
            {{-- CASE 3: Custom Page Link + Teaser Content --}}
            {{-- CASE 3: Custom Page Link + Teaser Content (Clickable Card) --}}
            <a href="{{ route('journal.custom-page', ['journal' => $journal->slug, 'path' => $block->slug]) }}"
                class="block mb-6 custom-page-{{ $block->id }} bg-white border border-slate-200 rounded-lg shadow-sm hover:shadow-md hover:border-indigo-300 transition-all group overflow-hidden">

                @if ($block->show_title)
                    <div class="px-4 py-3 border-b border-slate-200 group-hover:bg-indigo-50 transition-colors">
                        <h3
                            class="font-bold text-slate-800 uppercase tracking-wider text-xs group-hover:text-indigo-700 transition-colors">
                            {{ __($block->title) }}
                        </h3>
                    </div>
                @endif

                @php
                    $teaserContent = $block->parsed_sidebar_content;
                    if (str_contains($teaserContent, 'KODE_STATCOUNTER')) {
                        $teaserContent = str_replace('KODE_STATCOUNTER', $journal->custom_headers ?? '', $teaserContent);
                    }
                    if (app()->getLocale() === 'id') {
                        $replacements = [
                            'About This Journal' => 'Tentang Jurnal Ini',
                            'About the Journal' => 'Tentang Jurnal Ini',
                            'History' => 'Sejarah',
                            'Focus & Scope' => 'Fokus & Ruang Lingkup',
                            'Peer Review Process' => 'Proses Mitra Bestari',
                            'Publication Ethics' => 'Etika Publikasi',
                            'Open Access Policy' => 'Kebijakan Akses Terbuka',
                            'Open Access Statement' => 'Pernyataan Akses Terbuka',
                            'Repository Policy' => 'Kebijakan Repositori',
                            'Indexing' => 'Indeksasi',
                            'Archive Policy' => 'Kebijakan Pengarsipan',
                            'Journal License' => 'Lisensi Jurnal',
                            'Policy of Plagiarism' => 'Kebijakan Plagiarisme',
                            'Article Processing Charge' => 'Biaya Pemrosesan Artikel',
                            'People' => 'Pengelola',
                            'Peer-Reviewers' => 'Mitra Bestari',
                            'Publisher' => 'Penerbit',
                            'Contact' => 'Kontak',
                            'For Author' => 'Untuk Penulis',
                            'Publication Frequency' => 'Frekuensi Publikasi',
                            'Template' => 'Templat',
                            'Contact Us' => 'Hubungi Kami',
                            'Visitors' => 'Pengunjung',
                            'Author Guidelines' => 'Panduan Penulis',
                            'Kontak Us' => 'Hubungi Kami',
                            'Stat Counter' => 'Statistik Pengunjung',
                        ];
                        $teaserContent = str_replace(array_keys($replacements), array_values($replacements), $teaserContent);
                    }
                @endphp

                {{-- Render Sidebar Content (Teaser) --}}
                <div class="p-4 prose prose-sm max-w-none text-slate-600 group-hover:text-slate-800 transition-colors">
                    {!! $teaserContent !!}
                </div>
            </a>
        @elseif ($type === 'block')
            {{-- CASE 1: Custom HTML Block (RAW / NAKED STYLE) --}}
            <div class="mb-6 custom-block-{{ $block->id }}">
                @if ($block->show_title)
                    <h3 class="font-bold text-slate-800 mb-2 border-b pb-2 uppercase tracking-wider text-xs">
                        {{ __($block->title) }}</h3>
                @endif

                @php
                    $blockContent = $block->parsed_content;
                    if (str_contains($blockContent, 'KODE_STATCOUNTER')) {
                        $blockContent = str_replace('KODE_STATCOUNTER', $journal->custom_headers ?? '', $blockContent);
                    }
                    if (app()->getLocale() === 'id') {
                        $replacements = [
                            'About This Journal' => 'Tentang Jurnal Ini',
                            'About the Journal' => 'Tentang Jurnal Ini',
                            'History' => 'Sejarah',
                            'Focus & Scope' => 'Fokus & Ruang Lingkup',
                            'Peer Review Process' => 'Proses Mitra Bestari',
                            'Publication Ethics' => 'Etika Publikasi',
                            'Open Access Policy' => 'Kebijakan Akses Terbuka',
                            'Open Access Statement' => 'Pernyataan Akses Terbuka',
                            'Repository Policy' => 'Kebijakan Repositori',
                            'Indexing' => 'Indeksasi',
                            'Archive Policy' => 'Kebijakan Pengarsipan',
                            'Journal License' => 'Lisensi Jurnal',
                            'Policy of Plagiarism' => 'Kebijakan Plagiarisme',
                            'Article Processing Charge' => 'Biaya Pemrosesan Artikel',
                            'People' => 'Pengelola',
                            'Peer-Reviewers' => 'Mitra Bestari',
                            'Publisher' => 'Penerbit',
                            'Contact' => 'Kontak',
                            'For Author' => 'Untuk Penulis',
                            'Publication Frequency' => 'Frekuensi Publikasi',
                            'Template' => 'Templat',
                            'Contact Us' => 'Hubungi Kami',
                            'Visitors' => 'Pengunjung',
                            'Author Guidelines' => 'Panduan Penulis',
                            'Kontak Us' => 'Hubungi Kami',
                            'Stat Counter' => 'Statistik Pengunjung',
                        ];
                        $blockContent = str_replace(array_keys($replacements), array_values($replacements), $blockContent);
                    }
                @endphp

                {{-- Render Content RAW --}}
                <div class="prose prose-sm max-w-none">
                    {!! $blockContent !!}
                </div>
            </div>
        @elseif ($type === 'system')
            {{-- CASE 2: System Block (CARD STYLE) --}}
            <div class="bg-white rounded-lg shadow-sm border border-slate-200 overflow-hidden mb-6">
                {{-- Block Header --}}
                <div class="px-4 py-3 border-b border-slate-200 bg-slate-50">
                    <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider flex items-center gap-2">
                        @if ($block->icon ?? false)
                            <i class="{{ $block->icon }}" style="color: {{ $primaryColor }};"></i>
                        @endif
                        {{ __($block->title) }}
                    </h3>
                </div>

                {{-- Block Content --}}
                <div class="p-4">
                    @if ($block->component_name ?? false)
                        {{-- Render System Component Dynamically --}}
                        @if (view()->exists('components.' . $block->component_name) ||
                                view()->exists('components.' . str_replace('.', '/', $block->component_name)))
                            <x-dynamic-component :component="$block->component_name" :journal="$journal" :block="$block" />
                        @else
                            <p class="text-sm text-slate-500 italic">Component not available</p>
                        @endif
                    @endif
                </div>
            </div>
        @endif
    @empty
        {{-- Default Sidebar Blocks when none configured --}}



    @endforelse
</div>
