@php $title = 'Contact'; @endphp

<x-layouts.public :journal="$journal" :settings="$settings" :title="$title">

    <section class="bg-white">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

            {{-- BREADCRUMB --}}
            <nav class="text-sm text-slate-500 mb-6">
                <a href="{{ route('journal.public.home', $journal->slug) }}"
                   class="hover:text-primary-600">
                    Home
                </a>
                <span class="mx-2">/</span>
                <span class="text-slate-700 font-medium">Contact</span>
            </nav>

            <h1 class="text-3xl font-bold text-gray-900 mb-8 border-b pb-4">Contact the Journal</h1>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                
                {{-- Left Column: Principal Contact --}}
                <div class="bg-slate-50 rounded-xl p-6 border border-slate-100">
                    <h2 class="text-xl font-bold text-slate-800 mb-4 flex items-center gap-2">
                        <i class="fa-solid fa-user-tie text-primary-600"></i>
                        Principal Contact
                    </h2>
                    
                    @php
                        $principal = $contactSettings['principal'] ?? [];
                    @endphp
                    
                    @if(!empty($principal['name']))
                        <div class="space-y-3 text-slate-600">
                            <div>
                                <span class="block text-xs font-semibold uppercase tracking-wider text-slate-400">Name</span>
                                <span class="text-slate-800 font-medium">{{ $principal['name'] }}</span>
                            </div>
                            
                            @if(!empty($principal['affiliation']))
                                <div>
                                    <span class="block text-xs font-semibold uppercase tracking-wider text-slate-400">Affiliation</span>
                                    <span>{{ $principal['affiliation'] }}</span>
                                </div>
                            @endif
                            
                            @if(!empty($principal['email']))
                                <div>
                                    <span class="block text-xs font-semibold uppercase tracking-wider text-slate-400">Email</span>
                                    <a href="mailto:{{ $principal['email'] }}" class="text-primary-600 hover:underline font-medium">
                                        {{ $principal['email'] }}
                                    </a>
                                </div>
                            @endif
                            
                            @if(!empty($principal['phone']))
                                <div>
                                    <span class="block text-xs font-semibold uppercase tracking-wider text-slate-400">Phone</span>
                                    <span>{{ $principal['phone'] }}</span>
                                </div>
                            @endif
                        </div>
                    @else
                        <p class="text-sm text-slate-400 italic">No principal contact configured.</p>
                    @endif
                </div>

                {{-- Right Column: Support Contact --}}
                <div class="bg-slate-50 rounded-xl p-6 border border-slate-100">
                    <h2 class="text-xl font-bold text-slate-800 mb-4 flex items-center gap-2">
                        <i class="fa-solid fa-headset text-primary-600"></i>
                        Support Contact
                    </h2>
                    
                    @php
                        $support = $contactSettings['support'] ?? [];
                    @endphp
                    
                    @if(!empty($support['name']))
                        <div class="space-y-3 text-slate-600">
                            <div>
                                <span class="block text-xs font-semibold uppercase tracking-wider text-slate-400">Name</span>
                                <span class="text-slate-800 font-medium">{{ $support['name'] }}</span>
                            </div>
                            
                            @if(!empty($support['email']))
                                <div>
                                    <span class="block text-xs font-semibold uppercase tracking-wider text-slate-400">Email</span>
                                    <a href="mailto:{{ $support['email'] }}" class="text-primary-600 hover:underline font-medium">
                                        {{ $support['email'] }}
                                    </a>
                                </div>
                            @endif
                            
                            @if(!empty($support['phone']))
                                <div>
                                    <span class="block text-xs font-semibold uppercase tracking-wider text-slate-400">Phone</span>
                                    <span>{{ $support['phone'] }}</span>
                                </div>
                            @endif
                        </div>
                    @else
                        <p class="text-sm text-slate-400 italic">No support contact configured.</p>
                    @endif
                </div>

                {{-- Full Width: Mailing Address --}}
                @if(!empty($contactSettings['mailing_address']))
                    <div class="col-span-1 md:col-span-2 bg-slate-50 rounded-xl p-6 border border-slate-100">
                        <h2 class="text-xl font-bold text-slate-800 mb-4 flex items-center gap-2">
                            <i class="fa-solid fa-envelope text-primary-600"></i>
                            Mailing Address
                        </h2>
                        <div class="prose prose-slate max-w-none text-slate-600">
                            {!! nl2br(e($contactSettings['mailing_address'])) !!}
                        </div>
                    </div>
                @endif
                
            </div>
            
        </div>
    </section>
</x-layouts.public>
