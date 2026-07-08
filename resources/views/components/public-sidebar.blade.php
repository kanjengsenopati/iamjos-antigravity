{{-- Public Sidebar Component with Dynamic Blocks --}}
@props(['journal', 'settings' => [], 'sidebarBlocks' => collect()])

@php
$primaryColor = $settings['primary_color'] ?? '#4F46E5';
@endphp

<aside {{ $attributes->merge(['class' => 'space-y-6']) }}>
    @forelse($sidebarBlocks as $block)
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        {{-- Block Header --}}
        <div class="px-4 py-3 border-b border-gray-100 bg-gray-50">
            <h3 class="text-sm font-semibold text-gray-900 flex items-center gap-2">
                @if($block->icon)
                <i class="{{ $block->icon }}" style="color: {{ $primaryColor }};"></i>
                @endif
                {{ __($block->title) }}
            </h3>
        </div>

        {{-- Block Content --}}
        <div class="p-4">
            @if($block->is_system && $block->component_name)
            {{-- Render System Component --}}
            @try
            <x-dynamic-component :component="$block->component_name" :journal="$journal" :settings="$settings" :block="$block" />
            @catch (\Exception $e)
            <p class="text-sm text-gray-500 italic">Component not available</p>
            @endtry
            @else
            {{-- Render Custom HTML Content --}}
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
            <div class="prose prose-sm max-w-none">
                {!! $blockContent !!}
            </div>
            @endif
        </div>
    </div>
    @empty
    {{-- Default Sidebar Content when no blocks configured --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-100 bg-gray-50">
            <h3 class="text-sm font-semibold text-gray-900">
                <i class="fa-solid fa-info-circle mr-2" style="color: {{ $primaryColor }};"></i>
                {{ __('Information') }}
            </h3>
        </div>
        <div class="p-4">
            <ul class="space-y-2 text-sm">
                <li class="flex items-center gap-2 text-gray-600">
                    <i class="fa-solid fa-book-open w-4 text-gray-400"></i>
                    <a href="{{ route('journal.public.about', $journal->slug) }}" class="hover:text-gray-900">{{ __('About the Journal') }}</a>
                </li>
                <li class="flex items-center gap-2 text-gray-600">
                    <i class="fa-solid fa-users w-4 text-gray-400"></i>
                    <a href="{{ route('journal.public.editorial-team', $journal->slug) }}" class="hover:text-gray-900">{{ __('Editorial Team') }}</a>
                </li>
                <li class="flex items-center gap-2 text-gray-600">
                    <i class="fa-solid fa-file-alt w-4 text-gray-400"></i>
                    <a href="#" class="hover:text-gray-900">{{ __('Submission Guidelines') }}</a>
                </li>
            </ul>
        </div>
    </div>
    @endforelse
</aside>