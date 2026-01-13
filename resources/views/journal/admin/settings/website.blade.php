@php
    $journal = current_journal();
    $journalSlug = $journal->slug;
@endphp

<x-app-layout :journal="$journal" :journalSlug="$journalSlug">
    <x-slot name="title">Website Settings - {{ $journal->name }}</x-slot>

    <div class="max-w-6xl mx-auto py-8 px-4 sm:px-6 lg:px-8" x-data="{ activeTab: 'appearance' }">
        {{-- Page Header --}}
        <div class="mb-8">
            <nav class="text-sm text-gray-500 mb-2">
                <a href="{{ route('journal.settings.index', $journalSlug) }}" class="hover:text-indigo-600">Settings</a>
                <span class="mx-2">/</span>
                <span class="text-gray-700">Website</span>
            </nav>
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Website Settings</h1>
                    <p class="text-gray-500 mt-1">Customize your journal's public homepage appearance</p>
                </div>
                <a href="{{ route('journal.public.home', $journalSlug) }}" target="_blank"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50">
                    <i class="fa-solid fa-external-link-alt mr-2"></i>
                    Preview
                </a>
            </div>
        </div>

        {{-- Success Message --}}
        @if (session('success'))
            <div class="mb-6 bg-green-50 border border-green-200 rounded-lg p-4">
                <div class="flex items-center">
                    <i class="fa-solid fa-check-circle text-green-500 mr-3"></i>
                    <span class="text-green-800">{{ session('success') }}</span>
                </div>
            </div>
        @endif

        {{-- Tabs Navigation --}}
        <div class="border-b border-gray-200 mb-6">
            <nav class="flex space-x-8">
                <button @click="activeTab = 'appearance'"
                    :class="activeTab === 'appearance' ? 'border-indigo-500 text-indigo-600' :
                        'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                    class="py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                    <i class="fa-solid fa-palette mr-2"></i>
                    Appearance
                </button>
                <button @click="activeTab = 'content'"
                    :class="activeTab === 'content' ? 'border-indigo-500 text-indigo-600' :
                        'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                    class="py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                    <i class="fa-solid fa-edit mr-2"></i>
                    Hero & Stats
                </button>
                <button @click="activeTab = 'sections'"
                    :class="activeTab === 'sections' ? 'border-indigo-500 text-indigo-600' :
                        'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                    class="py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                    <i class="fa-solid fa-th-large mr-2"></i>
                    Sections
                </button>
                <button @click="activeTab = 'footer'"
                    :class="activeTab === 'footer' ? 'border-indigo-500 text-indigo-600' :
                        'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                    class="py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                    <i class="fa-solid fa-shoe-prints mr-2"></i>
                    Footer & Social
                </button>
            </nav>
        </div>

        {{-- Form --}}
        <form action="{{ route('journal.settings.website.update', $journalSlug) }}" method="POST"
            enctype="multipart/form-data">
            @csrf
            @method('PUT')

            {{-- Tab: Appearance --}}
            <div x-show="activeTab === 'appearance'" x-cloak class="space-y-6">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6">Visual Appearance</h3>

                    {{-- Hero Image --}}
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Hero Background Image</label>
                        <div class="flex items-start gap-6">
                            @if (!empty($settings['hero_image']))
                                <div class="relative">
                                    <img src="{{ Storage::url($settings['hero_image']) }}" alt="Hero"
                                        class="w-48 h-28 object-cover rounded-lg border border-gray-200">
                                    <span
                                        class="absolute -top-2 -right-2 bg-green-500 text-white text-xs px-2 py-0.5 rounded-full">Current</span>
                                </div>
                            @endif
                            <div class="flex-1">
                                <input type="file" name="hero_image" accept="image/*"
                                    class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                                <p class="mt-2 text-xs text-gray-500">Recommended: 1920x600px, JPG or PNG</p>
                            </div>
                        </div>
                    </div>

                    {{-- Colors --}}
                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Primary Color</label>
                            <div class="flex items-center gap-3">
                                <input type="color" name="primary_color"
                                    value="{{ $settings['primary_color'] ?? '#4F46E5' }}"
                                    class="w-12 h-10 rounded border border-gray-300 cursor-pointer">
                                <input type="text" value="{{ $settings['primary_color'] ?? '#4F46E5' }}"
                                    class="flex-1 rounded-lg border-gray-300 text-sm" readonly>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Secondary Color</label>
                            <div class="flex items-center gap-3">
                                <input type="color" name="secondary_color"
                                    value="{{ $settings['secondary_color'] ?? '#7C3AED' }}"
                                    class="w-12 h-10 rounded border border-gray-300 cursor-pointer">
                                <input type="text" value="{{ $settings['secondary_color'] ?? '#7C3AED' }}"
                                    class="flex-1 rounded-lg border-gray-300 text-sm" readonly>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tab: Content --}}
            <div x-show="activeTab === 'content'" x-cloak class="space-y-6">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6">Hero Section</h3>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Hero Title</label>
                            <input type="text" name="hero_title" value="{{ $settings['hero_title'] ?? '' }}"
                                class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                                placeholder="Enter the main title">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tagline</label>
                            <input type="text" name="hero_tagline" value="{{ $settings['hero_tagline'] ?? '' }}"
                                class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                                placeholder="e.g., Peer-Reviewed • Open Access • Indexed">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Hero Description</label>
                            <textarea name="hero_description" rows="3"
                                class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                                placeholder="A brief description of your journal">{{ $settings['hero_description'] ?? '' }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6">Statistics Display</h3>
                    <p class="text-sm text-gray-500 mb-4">These stats appear in the hero section.</p>

                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Acceptance Rate</label>
                            <input type="text" name="stat_acceptance_rate"
                                value="{{ $settings['stat_acceptance_rate'] ?? '' }}"
                                class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                                placeholder="e.g., 25%">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Review Time</label>
                            <input type="text" name="stat_review_time"
                                value="{{ $settings['stat_review_time'] ?? '' }}"
                                class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                                placeholder="e.g., 4 Weeks">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Impact Factor</label>
                            <input type="text" name="stat_impact_factor"
                                value="{{ $settings['stat_impact_factor'] ?? '' }}"
                                class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                                placeholder="e.g., 4.5">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Citations</label>
                            <input type="text" name="stat_citations"
                                value="{{ $settings['stat_citations'] ?? '' }}"
                                class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                                placeholder="e.g., 1000+">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tab: Sections --}}
            <div x-show="activeTab === 'sections'" x-cloak class="space-y-6">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6">Section Visibility</h3>

                    <div class="space-y-4">
                        <label class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                            <div>
                                <span class="font-medium text-gray-900">Show Statistics</span>
                                <p class="text-sm text-gray-500">Display acceptance rate, review time, etc.</p>
                            </div>
                            <input type="checkbox" name="show_stats" value="1"
                                {{ !empty($settings['show_stats']) ? 'checked' : '' }}
                                class="w-5 h-5 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                        </label>

                        <label class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                            <div>
                                <span class="font-medium text-gray-900">Show Announcements</span>
                                <p class="text-sm text-gray-500">Display latest announcements section</p>
                            </div>
                            <input type="checkbox" name="show_announcements" value="1"
                                {{ !empty($settings['show_announcements']) ? 'checked' : '' }}
                                class="w-5 h-5 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                        </label>

                        <label class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                            <div>
                                <span class="font-medium text-gray-900">Show Editorial Team</span>
                                <p class="text-sm text-gray-500">Display editors with their roles</p>
                            </div>
                            <input type="checkbox" name="show_editorial_team" value="1"
                                {{ !empty($settings['show_editorial_team']) ? 'checked' : '' }}
                                class="w-5 h-5 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                        </label>

                        <label class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                            <div>
                                <span class="font-medium text-gray-900">Show Indexed In</span>
                                <p class="text-sm text-gray-500">Display indexing partner logos</p>
                            </div>
                            <input type="checkbox" name="show_indexed_in" value="1"
                                {{ !empty($settings['show_indexed_in']) ? 'checked' : '' }}
                                class="w-5 h-5 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                        </label>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Indexed In Logos</h3>
                    <p class="text-sm text-gray-500 mb-4">Upload logos of indexing databases (Scopus, Google Scholar,
                        etc.)</p>

                    @php
                        $val = $settings['indexed_in_images'] ?? [];
                        $indexedImages = is_array($val) ? $val : json_decode($val, true) ?? [];
                    @endphp

                    @if (count($indexedImages) > 0)
                        <div class="flex flex-wrap gap-4 mb-4">
                            @foreach ($indexedImages as $image)
                                <div class="relative group">
                                    <img src="{{ Storage::url($image) }}" alt="Indexer"
                                        class="h-16 w-auto object-contain bg-gray-50 rounded border border-gray-200 p-2">
                                    <button type="button"
                                        onclick="if(confirm('Remove this logo?')) { 
                                            fetch('{{ route('journal.settings.website.indexed-image.delete', $journalSlug) }}', {
                                                method: 'DELETE',
                                                headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json'},
                                                body: JSON.stringify({path: '{{ $image }}'})
                                            }).then(() => location.reload());
                                        }"
                                        class="absolute -top-2 -right-2 w-6 h-6 bg-red-500 text-white rounded-full opacity-0 group-hover:opacity-100 transition-opacity">
                                        <i class="fa-solid fa-times text-xs"></i>
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <input type="file" name="indexed_in_images[]" accept="image/*" multiple
                        class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                    <p class="mt-2 text-xs text-gray-500">You can select multiple files. Recommended: PNG with
                        transparent background.</p>
                </div>
            </div>

            {{-- Tab: Footer --}}
            <div x-show="activeTab === 'footer'" x-cloak class="space-y-6">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6">Footer Content</h3>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Footer Description</label>
                            <textarea name="footer_description" rows="3"
                                class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                                placeholder="A brief about the journal for the footer">{{ $settings['footer_description'] ?? '' }}</textarea>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Contact Email</label>
                                <input type="email" name="contact_email"
                                    value="{{ $settings['contact_email'] ?? '' }}"
                                    class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                                    placeholder="editor@journal.com">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Contact Phone</label>
                                <input type="text" name="contact_phone"
                                    value="{{ $settings['contact_phone'] ?? '' }}"
                                    class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                                    placeholder="+1 234 567 890">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Address</label>
                                <input type="text" name="contact_address"
                                    value="{{ $settings['contact_address'] ?? '' }}"
                                    class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                                    placeholder="City, Country">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6">Social Media Links</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fa-brands fa-facebook text-blue-600 mr-2"></i>Facebook
                            </label>
                            <input type="url" name="social_facebook"
                                value="{{ $settings['social_facebook'] ?? '' }}"
                                class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                                placeholder="https://facebook.com/yourjournal">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fa-brands fa-twitter text-sky-500 mr-2"></i>Twitter / X
                            </label>
                            <input type="url" name="social_twitter"
                                value="{{ $settings['social_twitter'] ?? '' }}"
                                class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                                placeholder="https://twitter.com/yourjournal">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fa-brands fa-linkedin text-blue-700 mr-2"></i>LinkedIn
                            </label>
                            <input type="url" name="social_linkedin"
                                value="{{ $settings['social_linkedin'] ?? '' }}"
                                class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                                placeholder="https://linkedin.com/company/yourjournal">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fa-brands fa-instagram text-pink-600 mr-2"></i>Instagram
                            </label>
                            <input type="url" name="social_instagram"
                                value="{{ $settings['social_instagram'] ?? '' }}"
                                class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                                placeholder="https://instagram.com/yourjournal">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Submit Button --}}
            <div class="mt-8 flex justify-end">
                <button type="submit"
                    class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-lg shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <i class="fa-solid fa-save mr-2"></i>
                    Save Settings
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
