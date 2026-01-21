{{-- Information Block - System Component --}}
@props(['journal', 'block'])

<ul class="space-y-2.5 text-sm">
    <li>
        <a href="{{ route('journal.public.about', $journal->slug) }}"
            class="flex items-center gap-2 text-gray-600 hover:text-gray-900 transition-colors">
            <i class="fa-solid fa-book-open w-4 text-gray-400"></i>
            About the Journal
        </a>
    </li>
    <li>
        <a href="{{ route('journal.public.editorial-team', $journal->slug) }}"
            class="flex items-center gap-2 text-gray-600 hover:text-gray-900 transition-colors">
            <i class="fa-solid fa-users w-4 text-gray-400"></i>
            Editorial Team
        </a>
    </li>
    <li>
        <a href="{{ route('journal.public.author-guidelines', $journal->slug) }}"
            class="flex items-center gap-2 text-gray-600 hover:text-gray-900 transition-colors">
            <i class="fa-solid fa-file-alt w-4 text-gray-400"></i>
            Author Guidelines
        </a>
    </li>
    @if($journal->issn_online)
    <li class="flex items-center gap-2 text-gray-600 pt-2 mt-2 border-t border-gray-100">
        <i class="fa-solid fa-barcode w-4 text-gray-400"></i>
        <span class="text-xs">ISSN: {{ $journal->issn_online }}</span>
    </li>
    @endif
</ul>
