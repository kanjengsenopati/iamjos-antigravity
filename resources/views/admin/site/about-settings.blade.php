@extends('layouts.admin')

@section('title', 'About Page Settings')

@section('content')
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center gap-2 text-sm text-gray-500 mb-2">
            <a href="{{ route('admin.site.index') }}" class="hover:text-gray-700">Site Administration</a>
            <i class="fas fa-chevron-right text-xs"></i>
            <span class="text-gray-900">About Page Settings</span>
        </div>
        <h1 class="text-2xl font-bold text-gray-900">About Page Settings</h1>
        <p class="mt-1 text-gray-500">Customize the public "About Us" page content.</p>
    </div>

    @if (session('success'))
        <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-xl text-green-700 flex items-center gap-3">
            <i class="fas fa-check-circle"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <form action="{{ route('admin.about.update') }}" method="POST" class="space-y-8">
        @csrf
        @method('PUT')

        <!-- Page Title Section -->
        <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden shadow-sm">
            <div class="p-6 border-b border-gray-100 bg-gradient-to-r from-indigo-50 to-purple-50">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center">
                        <i class="fas fa-heading text-white"></i>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-gray-900">Page Header</h2>
                        <p class="text-sm text-gray-500">The main title displayed in the hero section</p>
                    </div>
                </div>
            </div>

            <div class="p-6 space-y-6">
                <!-- About Title -->
                <div>
                    <label for="about_title" class="block text-sm font-medium text-gray-700 mb-2">
                        Page Title <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="about_title" name="about_title"
                        value="{{ old('about_title', $content['about_title'] ?? 'Empowering Knowledge Sharing Worldwide') }}"
                        class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('about_title') border-red-500 @enderror"
                        placeholder="e.g. Empowering Knowledge Sharing Worldwide" required>
                    @error('about_title')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                    <p class="mt-2 text-xs text-gray-500">This title appears in the hero section with a serif font styling.</p>
                </div>
            </div>
        </div>

        <!-- Page Content Section -->
        <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden shadow-sm">
            <div class="p-6 border-b border-gray-100 bg-gradient-to-r from-emerald-50 to-teal-50">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-xl flex items-center justify-center">
                        <i class="fas fa-align-left text-white"></i>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-gray-900">Page Content</h2>
                        <p class="text-sm text-gray-500">The main content of the About page (supports HTML)</p>
                    </div>
                </div>
            </div>

            <div class="p-6 space-y-6">
                <!-- Content Editor -->
                <div>
                    <label for="about_content" class="block text-sm font-medium text-gray-700 mb-2">
                        Content <span class="text-red-500">*</span>
                    </label>
                    
                    <!-- Formatting Help -->
                    <div class="mb-4 p-4 bg-gray-50 rounded-xl border border-gray-200">
                        <p class="text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-info-circle text-indigo-500 mr-1"></i>
                            HTML Formatting Guide
                        </p>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-xs text-gray-600">
                            <div><code class="bg-gray-200 px-1 rounded">&lt;h2&gt;</code> Heading 2</div>
                            <div><code class="bg-gray-200 px-1 rounded">&lt;h3&gt;</code> Heading 3</div>
                            <div><code class="bg-gray-200 px-1 rounded">&lt;p&gt;</code> Paragraph</div>
                            <div><code class="bg-gray-200 px-1 rounded">&lt;ul&gt;&lt;li&gt;</code> List</div>
                            <div><code class="bg-gray-200 px-1 rounded">&lt;strong&gt;</code> Bold</div>
                            <div><code class="bg-gray-200 px-1 rounded">&lt;em&gt;</code> Italic</div>
                            <div><code class="bg-gray-200 px-1 rounded">&lt;a href=""&gt;</code> Link</div>
                            <div><code class="bg-gray-200 px-1 rounded">&lt;blockquote&gt;</code> Quote</div>
                        </div>
                    </div>

                    <textarea id="about_content" name="about_content" rows="20"
                        class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 font-mono text-sm @error('about_content') border-red-500 @enderror"
                        placeholder="Enter your About page content here. You can use HTML for formatting." required>{{ old('about_content', $content['about_content'] ?? '') }}</textarea>
                    @error('about_content')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Preview Toggle -->
                <div x-data="{ showPreview: false }">
                    <button type="button" @click="showPreview = !showPreview"
                        class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-indigo-600 hover:text-indigo-700 transition-colors">
                        <i class="fas" :class="showPreview ? 'fa-eye-slash' : 'fa-eye'"></i>
                        <span x-text="showPreview ? 'Hide Preview' : 'Show Preview'"></span>
                    </button>

                    <div x-show="showPreview" x-collapse class="mt-4 p-6 bg-gray-50 rounded-xl border border-gray-200">
                        <p class="text-sm font-medium text-gray-700 mb-4">
                            <i class="fas fa-desktop text-gray-400 mr-1"></i>
                            Content Preview
                        </p>
                        <div class="prose-content bg-white p-6 rounded-lg border border-gray-100">
                            <div x-html="document.getElementById('about_content').value"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Default Content Template -->
        <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden shadow-sm">
            <div class="p-6 border-b border-gray-100 bg-gradient-to-r from-amber-50 to-orange-50">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-amber-500 to-orange-600 rounded-xl flex items-center justify-center">
                        <i class="fas fa-magic text-white"></i>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-gray-900">Quick Templates</h2>
                        <p class="text-sm text-gray-500">Load a pre-made template to get started quickly</p>
                    </div>
                </div>
            </div>

            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <button type="button" onclick="loadTemplate('default')"
                        class="p-4 text-left border border-gray-200 rounded-xl hover:border-indigo-300 hover:bg-indigo-50 transition-colors group">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="w-8 h-8 bg-indigo-100 rounded-lg flex items-center justify-center group-hover:bg-indigo-200 transition-colors">
                                <i class="fas fa-file-alt text-indigo-600"></i>
                            </div>
                            <span class="font-semibold text-gray-900">Default Template</span>
                        </div>
                        <p class="text-sm text-gray-500">Standard about page with vision, mission, and features</p>
                    </button>

                    <button type="button" onclick="loadTemplate('minimal')"
                        class="p-4 text-left border border-gray-200 rounded-xl hover:border-emerald-300 hover:bg-emerald-50 transition-colors group">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="w-8 h-8 bg-emerald-100 rounded-lg flex items-center justify-center group-hover:bg-emerald-200 transition-colors">
                                <i class="fas fa-minus text-emerald-600"></i>
                            </div>
                            <span class="font-semibold text-gray-900">Minimal Template</span>
                        </div>
                        <p class="text-sm text-gray-500">Simple and clean about page structure</p>
                    </button>
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
                class="inline-flex items-center gap-2 px-8 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-xl font-semibold shadow-lg shadow-indigo-500/25 hover:shadow-indigo-500/40 transition-all">
                <i class="fas fa-save"></i>
                Save Settings
            </button>
        </div>
    </form>
@endsection

@push('scripts')
<script>
    const templates = {
        default: `<h2>Selamat Datang di IAMJOS</h2>
<p>IAMJOS (Indonesian Academic Journal System) adalah platform publikasi jurnal akademik modern yang dirancang untuk mendukung ekosistem penelitian di Indonesia. Kami berkomitmen untuk menyediakan akses terbuka ke pengetahuan berkualitas tinggi dari berbagai disiplin ilmu.</p>

<h3>Visi Kami</h3>
<p>Menjadi platform jurnal akademik terdepan di Indonesia yang menghubungkan peneliti, akademisi, dan institusi pendidikan dalam berbagi pengetahuan dan inovasi untuk kemajuan bangsa.</p>

<h3>Misi Kami</h3>
<ul>
    <li>Menyediakan platform publikasi jurnal yang modern, mudah digunakan, dan sesuai standar internasional</li>
    <li>Mendorong praktik open access untuk memperluas jangkauan dan dampak penelitian Indonesia</li>
    <li>Memfasilitasi proses peer review yang transparan dan berkualitas</li>
    <li>Mendukung akreditasi jurnal nasional dan indexing internasional</li>
    <li>Memberdayakan peneliti Indonesia untuk berkontribusi dalam kancah akademik global</li>
</ul>

<h3>Mengapa Memilih IAMJOS?</h3>
<p>Platform kami dibangun dengan teknologi modern dan mengikuti best practices dalam pengelolaan jurnal akademik:</p>

<ul>
    <li><strong>Antarmuka Modern</strong> - Desain yang bersih dan intuitif untuk pengalaman pengguna yang optimal</li>
    <li><strong>Workflow Lengkap</strong> - Dari submission hingga publikasi dengan sistem peer review terintegrasi</li>
    <li><strong>Open Access</strong> - Mendukung akses terbuka untuk memperluas dampak penelitian</li>
    <li><strong>Standar Internasional</strong> - Mengikuti protokol OAI-PMH dan standar metadata global</li>
    <li><strong>Multi-Journal</strong> - Satu platform untuk mengelola banyak jurnal</li>
</ul>

<blockquote>
    "Pengetahuan adalah kekuatan. Dengan berbagi pengetahuan, kita membangun masa depan yang lebih baik untuk Indonesia."
</blockquote>

<h3>Bergabung Bersama Kami</h3>
<p>Apakah Anda seorang peneliti, akademisi, atau pengelola jurnal? Bergabunglah dengan komunitas IAMJOS untuk mengakses dan berbagi karya ilmiah berkualitas. Daftarkan jurnal Anda atau mulai berkontribusi sebagai penulis hari ini.</p>`,

        minimal: `<h2>Tentang Platform Kami</h2>
<p>IAMJOS adalah platform jurnal akademik yang menyediakan akses terbuka ke penelitian berkualitas dari Indonesia.</p>

<h3>Apa yang Kami Tawarkan</h3>
<ul>
    <li>Publikasi jurnal dengan standar internasional</li>
    <li>Proses peer review yang transparan</li>
    <li>Akses terbuka untuk semua artikel</li>
</ul>

<h3>Hubungi Kami</h3>
<p>Untuk informasi lebih lanjut, silakan hubungi tim kami melalui email atau media sosial yang tersedia.</p>`
    };

    function loadTemplate(type) {
        if (confirm('This will replace the current content. Are you sure?')) {
            document.getElementById('about_content').value = templates[type];
        }
    }
</script>
@endpush
