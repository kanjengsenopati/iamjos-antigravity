{{-- Dynamic Public Sidebar Component (OJS 3.3 Style) --}}
@props(['journal', 'sidebarBlocks' => collect()])

@php
$primaryColor = $journal->getWebsiteSettings()['primary_color'] ?? '#0369a1';
@endphp

<div class="space-y-6">
    @forelse($sidebarBlocks as $block)
        @if(($block->type ?? '') === 'custom')
            {{-- CASE 1: Custom HTML Block (RAW / NAKED STYLE) --}}
            <div class="mb-6 custom-block-{{ $block->id }}">
                @if($block->show_title ?? false)
                    <h3 class="font-bold text-slate-800 mb-2 border-b pb-2 uppercase tracking-wider text-xs">{{ $block->title }}</h3>
                @endif
                
                {{-- Render Content RAW --}}
                <div class="prose prose-sm max-w-none">
                    {!! $block->content !!}
                </div>
            </div>
        @else
            {{-- CASE 2: System Block (CARD STYLE) --}}
            <div class="bg-white rounded-lg shadow-sm border border-slate-200 overflow-hidden">
                {{-- Block Header --}}
                <div class="px-4 py-3 border-b border-slate-200 bg-slate-50">
                    <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider flex items-center gap-2">
                        @if($block->icon ?? false)
                            <i class="{{ $block->icon }}" style="color: {{ $primaryColor }};"></i>
                        @endif
                        {{ $block->title }}
                    </h3>
                </div>

                {{-- Block Content --}}
                <div class="p-4">
                    @if(($block->type ?? '') === 'system' && ($block->component_name ?? false))
                        {{-- Render System Component Dynamically --}}
                        @if(view()->exists('components.' . str_replace('.', '/', $block->component_name)) || \Illuminate\Support\Facades\Blade::getClassComponentPrefix($block->component_name))
                            <x-dynamic-component 
                                :component="$block->component_name" 
                                :journal="$journal" 
                                :block="$block" 
                            />
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
