@extends('layouts.admin')

@section('title', 'Portal Settings')

@section('content')
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center gap-2 text-sm text-gray-500 mb-2">
            <a href="{{ route('admin.site.index') }}" class="hover:text-gray-700">Site Administration</a>
            <i class="fas fa-chevron-right text-xs"></i>
            <span class="text-gray-900">Portal Settings</span>
        </div>
        <h1 class="text-2xl font-bold text-gray-900">Portal Landing Page Settings</h1>
        <p class="mt-1 text-gray-500">Customize the public landing page content and appearance.</p>
    </div>

    @if (session('success'))
        <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-xl text-green-700 flex items-center gap-3">
            <i class="fas fa-check-circle"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <form action="{{ route('admin.portal.update') }}" method="POST" class="space-y-8">
        @csrf
        @method('PUT')

        <!-- Hero Section -->
        <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden shadow-sm">
            <div class="p-6 border-b border-gray-100 bg-gradient-to-r from-purple-50 to-indigo-50">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-indigo-600 rounded-xl flex items-center justify-center">
                        <i class="fas fa-image text-white"></i>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-gray-900">Hero Section</h2>
                        <p class="text-sm text-gray-500">Main banner area with title and search</p>
                    </div>
                </div>
            </div>

            <div class="p-6 space-y-6">
                <!-- Hero Title -->
                <div>
                    <label for="hero_title" class="block text-sm font-medium text-gray-700 mb-2">
                        Title <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="hero_title" name="hero_title"
                        value="{{ old('hero_title', $hero['hero_title'] ?? '') }}"
                        class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 @error('hero_title') border-red-500 @enderror"
                        placeholder="e.g. Temukan Pengetahuan, Bagikan Inovasi" required>
                    @error('hero_title')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Hero Subtitle -->
                <div>
                    <label for="hero_subtitle" class="block text-sm font-medium text-gray-700 mb-2">
                        Subtitle <span class="text-red-500">*</span>
                    </label>
                    <textarea id="hero_subtitle" name="hero_subtitle" rows="3"
                        class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 @error('hero_subtitle') border-red-500 @enderror"
                        placeholder="Brief description about the platform" required>{{ old('hero_subtitle', $hero['hero_subtitle'] ?? '') }}</textarea>
                    @error('hero_subtitle')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Search Placeholder -->
                <div>
                    <label for="hero_search_placeholder" class="block text-sm font-medium text-gray-700 mb-2">
                        Search Placeholder Text
                    </label>
                    <input type="text" id="hero_search_placeholder" name="hero_search_placeholder"
                        value="{{ old('hero_search_placeholder', $hero['hero_search_placeholder'] ?? '') }}"
                        class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                        placeholder="e.g. Cari jurnal, artikel, atau penulis...">
                </div>

                <!-- Popular Tags -->
                <div>
                    <label for="hero_popular_tags" class="block text-sm font-medium text-gray-700 mb-2">
                        Popular Tags
                    </label>
                    <input type="text" id="hero_popular_tags" name="hero_popular_tags"
                        value="{{ old('hero_popular_tags', is_array($hero['hero_popular_tags'] ?? null) ? implode(', ', $hero['hero_popular_tags']) : '') }}"
                        class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                        placeholder="Kesehatan, Pendidikan, Teknologi (comma separated)">
                    <p class="mt-1 text-xs text-gray-500">Enter tags separated by commas. These will appear below the search bar.</p>
                </div>
            </div>
        </div>

        <!-- Featured Journals Section -->
        <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden shadow-sm">
            <div class="p-6 border-b border-gray-100 bg-gradient-to-r from-green-50 to-emerald-50">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl flex items-center justify-center">
                        <i class="fas fa-star text-white"></i>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-gray-900">Featured Journals</h2>
                        <p class="text-sm text-gray-500">Select journals to highlight on the landing page</p>
                    </div>
                </div>
            </div>

            <div class="p-6 space-y-6">
                <!-- Featured Title -->
                <div>
                    <label for="featured_title" class="block text-sm font-medium text-gray-700 mb-2">
                        Section Title
                    </label>
                    <input type="text" id="featured_title" name="featured_title"
                        value="{{ old('featured_title', $featured['featured_title'] ?? 'Jurnal Pilihan') }}"
                        class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-green-500"
                        placeholder="e.g. Featured Journals">
                </div>

                <!-- Featured Subtitle -->
                <div>
                    <label for="featured_subtitle" class="block text-sm font-medium text-gray-700 mb-2">
                        Section Subtitle
                    </label>
                    <input type="text" id="featured_subtitle" name="featured_subtitle"
                        value="{{ old('featured_subtitle', $featured['featured_subtitle'] ?? '') }}"
                        class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-green-500"
                        placeholder="e.g. Koleksi jurnal ilmiah terbaik">
                </div>

                <!-- Featured Journals Selection -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Select Featured Journals
                    </label>
                    <p class="text-sm text-gray-500 mb-4">Choose up to 6 journals to feature. If none selected, top journals by article count will be shown.</p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 max-h-80 overflow-y-auto border border-gray-200 rounded-xl p-4 bg-gray-50">
                        @forelse($journals as $journal)
                            <label class="flex items-center gap-3 p-3 bg-white rounded-lg border border-gray-200 cursor-pointer hover:border-green-300 hover:bg-green-50 transition-colors">
                                <input type="checkbox" name="featured_journal_ids[]" value="{{ $journal->id }}"
                                    {{ in_array($journal->id, (array)$featuredIds) ? 'checked' : '' }}
                                    class="w-5 h-5 text-green-600 border-gray-300 rounded focus:ring-green-500">
                                <div class="flex-1 min-w-0">
                                    <p class="font-medium text-gray-900 truncate">{{ $journal->name }}</p>
                                    <p class="text-xs text-gray-500">{{ $journal->abbreviation }}</p>
                                </div>
                            </label>
                        @empty
                            <p class="col-span-2 text-gray-500 text-center py-4">No journals available</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer Section -->
        <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden shadow-sm">
            <div class="p-6 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-slate-50">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-gray-600 to-slate-700 rounded-xl flex items-center justify-center">
                        <i class="fas fa-align-left text-white"></i>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-gray-900">Footer Content</h2>
                        <p class="text-sm text-gray-500">About section and contact information</p>
                    </div>
                </div>
            </div>

            <div class="p-6 space-y-6">
                <!-- Footer About -->
                <div>
                    <label for="footer_about" class="block text-sm font-medium text-gray-700 mb-2">
                        About Text
                    </label>
                    <textarea id="footer_about" name="footer_about" rows="4"
                        class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-gray-500 focus:border-gray-500"
                        placeholder="Brief description about your organization">{{ old('footer_about', $footer['footer_about'] ?? '') }}</textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Address -->
                    <div>
                        <label for="footer_address" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-map-marker-alt mr-1 text-gray-400"></i> Address
                        </label>
                        <input type="text" id="footer_address" name="footer_address"
                            value="{{ old('footer_address', $footer['footer_address'] ?? '') }}"
                            class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-gray-500 focus:border-gray-500"
                            placeholder="Jl. Example No. 123, Jakarta">
                    </div>

                    <!-- Phone -->
                    <div>
                        <label for="footer_phone" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-phone mr-1 text-gray-400"></i> Phone
                        </label>
                        <input type="text" id="footer_phone" name="footer_phone"
                            value="{{ old('footer_phone', $footer['footer_phone'] ?? '') }}"
                            class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-gray-500 focus:border-gray-500"
                            placeholder="+62 21 1234 5678">
                    </div>
                </div>

                <!-- Email -->
                <div>
                    <label for="footer_email" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-envelope mr-1 text-gray-400"></i> Email
                    </label>
                    <input type="email" id="footer_email" name="footer_email"
                        value="{{ old('footer_email', $footer['footer_email'] ?? '') }}"
                        class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-gray-500 focus:border-gray-500"
                        placeholder="info@example.com">
                    @error('footer_email')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Social Media Section -->
        <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden shadow-sm">
            <div class="p-6 border-b border-gray-100 bg-gradient-to-r from-blue-50 to-indigo-50">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center">
                        <i class="fas fa-share-alt text-white"></i>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-gray-900">Social Media Links</h2>
                        <p class="text-sm text-gray-500">Connect your social media accounts</p>
                    </div>
                </div>
            </div>

            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Facebook -->
                <div>
                    <label for="social_facebook" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fab fa-facebook text-blue-600 mr-1"></i> Facebook
                    </label>
                    <input type="url" id="social_facebook" name="social_facebook"
                        value="{{ old('social_facebook', $social['social_facebook'] ?? '') }}"
                        class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        placeholder="https://facebook.com/yourpage">
                    @error('social_facebook')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Twitter -->
                <div>
                    <label for="social_twitter" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fab fa-twitter text-sky-500 mr-1"></i> Twitter/X
                    </label>
                    <input type="url" id="social_twitter" name="social_twitter"
                        value="{{ old('social_twitter', $social['social_twitter'] ?? '') }}"
                        class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-sky-500 focus:border-sky-500"
                        placeholder="https://twitter.com/yourhandle">
                    @error('social_twitter')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Instagram -->
                <div>
                    <label for="social_instagram" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fab fa-instagram text-pink-600 mr-1"></i> Instagram
                    </label>
                    <input type="url" id="social_instagram" name="social_instagram"
                        value="{{ old('social_instagram', $social['social_instagram'] ?? '') }}"
                        class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-pink-500 focus:border-pink-500"
                        placeholder="https://instagram.com/yourhandle">
                    @error('social_instagram')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- YouTube -->
                <div>
                    <label for="social_youtube" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fab fa-youtube text-red-600 mr-1"></i> YouTube
                    </label>
                    <input type="url" id="social_youtube" name="social_youtube"
                        value="{{ old('social_youtube', $social['social_youtube'] ?? '') }}"
                        class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-red-500 focus:border-red-500"
                        placeholder="https://youtube.com/@yourchannel">
                    @error('social_youtube')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="flex items-center justify-end gap-4">
            <a href="{{ route('admin.site.index') }}"
                class="px-6 py-3 text-gray-600 hover:text-gray-800 font-medium transition-colors">
                Cancel
            </a>
            <button type="submit"
                class="inline-flex items-center gap-2 px-8 py-3 bg-gradient-to-r from-purple-600 to-indigo-600 text-white rounded-xl font-semibold shadow-lg shadow-purple-500/25 hover:shadow-purple-500/40 transition-all">
                <i class="fas fa-save"></i>
                Save Settings
            </button>
        </div>
    </form>
@endsection
