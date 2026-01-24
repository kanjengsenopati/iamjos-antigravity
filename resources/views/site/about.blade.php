@extends('layouts.portal')
@section('title', 'About Us')

@section('content')
{{-- Hero Header --}}
<div class="relative bg-[#002f6c] py-20 overflow-hidden">
    <div class="absolute inset-0 bg-gradient-to-br from-slate-900 via-blue-900 to-slate-900 opacity-90"></div>
    <div class="absolute inset-0 opacity-[0.05]" style="background-image: linear-gradient(#ffffff 1px, transparent 1px), linear-gradient(90deg, #ffffff 1px, transparent 1px); background-size: 30px 30px;"></div>
    
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-white">
        <h1 class="text-4xl md:text-5xl font-extrabold tracking-tight mb-6">About IAMJOS</h1>
        <p class="text-xl text-blue-100 max-w-3xl mx-auto font-light">
            Indonesian Academic Journal System - A platform dedicated to advancing scholarly communication.
        </p>
    </div>
</div>

{{-- Main Content --}}
<div class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
            
            {{-- Text Content --}}
            <div>
                <h2 class="text-3xl font-bold text-slate-900 mb-6">Empowering Research & Innovation</h2>
                <div class="prose prose-lg text-slate-600">
                    @if(!empty($settings['site_about']))
                        {!! $settings['site_about'] !!}
                    @else
                        <p>
                            IAMJOS is a comprehensive multi-journal platform designed to handle the entire editorial management workflow and publishing process for academic journals. 
                        </p>
                        <p>
                            Our system provides flexible, automated solutions for online manuscript submission, peer review, and publication. We aim to improve the quality of scholarly publishing in Indonesia by providing a standardized, efficient, and transparent platform for editors, authors, and reviewers.
                        </p>
                        <p>
                            By aggregating high-quality research from various institutions, IAMJOS facilitates better dissemination of knowledge and increases the visibility of Indonesian research on a global scale.
                        </p>
                    @endif
                </div>

                <div class="mt-8 grid grid-cols-1 sm:grid-cols-3 gap-6">
                    <div class="text-center p-4 bg-slate-50 rounded-xl border border-slate-100">
                        <div class="text-3xl font-bold text-[#002f6c] mb-1">{{ number_format($stats['total_journals'] ?? 0) }}</div>
                        <div class="text-xs uppercase tracking-wider text-slate-500 font-semibold">Journals</div>
                    </div>
                    <div class="text-center p-4 bg-slate-50 rounded-xl border border-slate-100">
                        <div class="text-3xl font-bold text-[#002f6c] mb-1">{{ number_format($stats['total_articles'] ?? 0) }}</div>
                        <div class="text-xs uppercase tracking-wider text-slate-500 font-semibold">Articles</div>
                    </div>
                    <div class="text-center p-4 bg-slate-50 rounded-xl border border-slate-100">
                        <div class="text-3xl font-bold text-[#002f6c] mb-1">{{ number_format($stats['total_authors'] ?? 0) }}</div>
                        <div class="text-xs uppercase tracking-wider text-slate-500 font-semibold">Authors</div>
                    </div>
                </div>
            </div>

            {{-- Visual / Image --}}
            <div class="relative">
                <div class="absolute -inset-4 bg-gradient-to-r from-blue-100 to-indigo-100 rounded-2xl transform rotate-2"></div>
                <div class="relative bg-white rounded-xl shadow-xl overflow-hidden border border-slate-100">
                     {{-- Use a polished placeholder or actual image if available in settings --}}
                    @if(!empty($settings['site_about_image']))
                         <img src="{{ Storage::url($settings['site_about_image']) }}" alt="About IAMJOS" class="w-full h-auto object-cover">
                    @else
                         {{-- Fallback illustrative SVG/Div --}}
                        <div class="aspect-video bg-gradient-to-br from-slate-50 to-slate-100 flex items-center justify-center">
                             <div class="text-center p-8">
                                <div class="w-20 h-20 mx-auto bg-blue-100 text-blue-600 rounded-full flex items-center justify-center mb-4">
                                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                                </div>
                                <h3 class="text-lg font-semibold text-slate-900">Academic Publishing Redefined</h3>
                                <p class="text-sm text-slate-500 mt-2">Connecting researchers, reviewers, and editors in one seamless ecosystem.</p>
                             </div>
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</div>

{{-- Contact / CTA Section --}}
<div class="bg-gray-50 py-16 border-t border-gray-200">
    <div class="max-w-4xl mx-auto px-4 text-center">
        <h2 class="text-2xl font-bold text-slate-900 mb-4">Have Questions or Want to Start a Journal?</h2>
        <p class="text-slate-600 mb-8">
            Our team is here to support institutions in setting up and managing their academic journals.
        </p>
        <div class="flex justify-center gap-4">
            <a href="#" class="px-6 py-3 bg-[#002f6c] hover:bg-[#001f4c] text-white font-medium rounded-lg transition-colors shadow-lg shadow-blue-900/20">
                Contact Support
            </a>
            <a href="{{ route('portal.journals') }}" class="px-6 py-3 bg-white text-slate-700 hover:text-slate-900 border border-slate-300 hover:border-slate-400 font-medium rounded-lg transition-all">
                Browse Journals
            </a>
        </div>
    </div>
</div>
@endsection
