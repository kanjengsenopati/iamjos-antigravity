@extends('layouts.app')

@php
    $currentLoc = session('app_locale', app()->getLocale());
    $isId = in_array($currentLoc, ['id', 'id_ID']);
@endphp

@section('title', $isId ? 'Buat Pengguna' : 'Create User')

@section('content')
    <div class="mb-8">
        <nav class="flex mb-2" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li class="inline-flex items-center">
                    <span class="text-gray-400 text-sm font-medium">{{ $isId ? 'Manajer Jurnal' : 'Journal Manager' }}</span>
                </li>
                <li>
                    <div class="flex items-center">
                        <i class="fa-solid fa-chevron-right text-gray-300 mx-2 text-xs"></i>
                        <a href="{{ route($routePrefix . '.index', ['journal' => $journal->slug]) }}"
                            class="text-sm font-medium text-gray-500 hover:text-indigo-600">{{ $isId ? 'Pengguna' : 'Users' }}</a>
                    </div>
                </li>
                <li aria-current="page">
                    <div class="flex items-center">
                        <i class="fa-solid fa-chevron-right text-gray-300 mx-2 text-xs"></i>
                        <span class="text-sm font-medium text-indigo-600">{{ $isId ? 'Buat Pengguna' : 'Create User' }}</span>
                    </div>
                </li>
            </ol>
        </nav>
        <h1 class="text-2xl font-bold text-gray-900">{{ $isId ? 'Buat Pengguna Baru' : 'Create New User' }}</h1>
        <p class="text-sm text-gray-500 mt-1">{{ $isId ? 'Buat akun pengguna baru dan daftarkan mereka di' : 'Create a new user account and enroll them in' }}
            <strong>{{ $journal->name }}</strong>.</p>
    </div>

    <form action="{{ route($routePrefix . '.store', ['journal' => $journal->slug]) }}" method="POST"
        class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden max-w-5xl">
        @csrf

        {{-- Section 1: Identity --}}
        <div class="p-6 border-b border-gray-100">
            <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                <i class="fa-solid fa-id-card text-indigo-500 mr-2"></i> {{ $isId ? 'Identitas' : 'Identity' }}
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Given Name --}}
                <div>
                    <label for="given_name" class="block text-sm font-medium text-gray-700">{{ $isId ? 'Nama Depan' : 'Given Name' }} <span
                            class="text-red-500">*</span></label>
                    <input type="text" name="given_name" id="given_name" value="{{ old('given_name') }}" required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm border p-2">
                    @error('given_name')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Family Name --}}
                <div>
                    <label for="family_name" class="block text-sm font-medium text-gray-700">{{ $isId ? 'Nama Belakang' : 'Family Name' }}</label>
                    <input type="text" name="family_name" id="family_name" value="{{ old('family_name') }}"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm border p-2">
                    @error('family_name')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Preferred Public Name --}}
                <div class="md:col-span-2">
                    <label for="name" class="block text-sm font-medium text-gray-700">{{ $isId ? 'Nama Publik Pilihan' : 'Preferred Public Name' }}</label>
                    <p class="text-xs text-gray-500 mb-1">{{ $isId ? 'Bagaimana pengguna ingin disapa (misal "Dr. Jane Doe").' : 'How the user prefers to be addressed (e.g. "Dr. Jane Doe").' }}</p>
                    <input type="text" name="name" id="name" value="{{ old('name') }}"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm border p-2">
                    @error('name')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        {{-- Section 2: Contact --}}
        <div class="p-6 border-b border-gray-100 bg-gray-50/50">
            <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                <i class="fa-solid fa-address-book text-indigo-500 mr-2"></i> {{ $isId ? 'Kontak' : 'Contact' }}
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Email --}}
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">{{ $isId ? 'Surel' : 'Email' }} <span
                            class="text-red-500">*</span></label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm border p-2">
                    @error('email')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Phone --}}
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700">{{ $isId ? 'Telepon' : 'Phone' }}</label>
                    <input type="text" name="phone" id="phone" value="{{ old('phone') }}"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm border p-2">
                    @error('phone')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Affiliation --}}
                <div class="md:col-span-2">
                    <label for="affiliation" class="block text-sm font-medium text-gray-700">{{ $isId ? 'Afiliasi' : 'Affiliation' }}</label>
                    <input type="text" name="affiliation" id="affiliation" value="{{ old('affiliation') }}"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm border p-2">
                    @error('affiliation')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Mailing Address --}}
                <div class="md:col-span-2">
                    <label for="mailing_address" class="block text-sm font-medium text-gray-700">{{ $isId ? 'Alamat Surat' : 'Mailing Address' }}</label>
                    <textarea name="mailing_address" id="mailing_address" rows="3"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm border p-2">{{ old('mailing_address') }}</textarea>
                    @error('mailing_address')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Country --}}
                <div>
                    <label for="country" class="block text-sm font-medium text-gray-700">{{ $isId ? 'Negara' : 'Country' }}</label>
                    @php
                        $countries = [
                            'ID' => 'Indonesia',
                            'US' => 'United States',
                            'GB' => 'United Kingdom',
                            'AU' => 'Australia',
                            'CA' => 'Canada',
                            'MY' => 'Malaysia',
                            'SG' => 'Singapore',
                            'JP' => 'Japan',
                            'CN' => 'China',
                            'KR' => 'South Korea',
                            'IN' => 'India',
                            'TH' => 'Thailand',
                            'VN' => 'Vietnam',
                            'PH' => 'Philippines',
                        ];
                    @endphp
                    <select name="country" id="country"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm border p-2">
                        <option value="">{{ $isId ? 'Pilih Negara...' : 'Select Country...' }}</option>
                        @foreach ($countries as $code => $name)
                            <option value="{{ $code }}" {{ old('country') == $code ? 'selected' : '' }}>
                                {{ $name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        {{-- Section 3: Roles --}}
        <div class="p-6 border-b border-gray-100">
            <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                <i class="fa-solid fa-user-tag text-indigo-500 mr-2"></i> {{ $isId ? 'Peran' : 'Roles' }}
            </h2>
            <p class="text-sm text-gray-500 mb-4">{{ $isId ? 'Pilih peran untuk' : 'Select roles for' }} <strong>{{ $journal->name }}</strong>.</p>

            @error('roles')
                <p class="mb-3 text-xs text-red-500">{{ $message }}</p>
            @enderror

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                @php
                    $roleDescriptions = $isId ? [
                        'Journal Manager' => 'Akses administratif penuh ke jurnal',
                        'Editor' => 'Dapat mengelola pengajuan naskah dan membuat keputusan editorial',
                        'Section Editor' => 'Mengelola pengajuan naskah dalam bidang yang ditetapkan',
                        'Reviewer' => 'Dapat meninjau naskah dan memberikan rekomendasi',
                        'Author' => 'Dapat mengirimkan naskah ke jurnal',
                        'Reader' => 'Akses dasar ke konten yang diterbitkan',
                        'Admin' => 'Akses administratif di seluruh jurnal',
                    ] : [
                        'Journal Manager' => 'Full administrative access to the journal',
                        'Editor' => 'Can manage submissions and make editorial decisions',
                        'Section Editor' => 'Manages submissions within assigned sections',
                        'Reviewer' => 'Can review submissions and provide recommendations',
                        'Author' => 'Can submit manuscripts to the journal',
                        'Reader' => 'Basic access to published content',
                        'Admin' => 'Administrative access across all journals',
                    ];
                    $roleColors = [
                        'Journal Manager' => 'border-red-200 bg-red-50 hover:bg-red-100',
                        'Editor' => 'border-blue-200 bg-blue-50 hover:bg-blue-100',
                        'Section Editor' => 'border-blue-200 bg-blue-50 hover:bg-blue-100',
                        'Reviewer' => 'border-amber-200 bg-amber-50 hover:bg-amber-100',
                        'Author' => 'border-emerald-200 bg-emerald-50 hover:bg-emerald-100',
                        'Reader' => 'border-gray-200 bg-gray-50 hover:bg-gray-100',
                        'Admin' => 'border-purple-200 bg-purple-50 hover:bg-purple-100',
                    ];
                @endphp

                @foreach ($roles as $role)
                    <label
                        class="relative flex items-start p-4 rounded-lg border cursor-pointer transition-all {{ $roleColors[$role->name] ?? 'border-gray-200 bg-gray-50 hover:bg-gray-100' }} {{ in_array($role->id, old('roles', [])) ? 'ring-2 ring-indigo-500' : '' }}">
                        <div class="flex items-center h-5">
                            <input type="checkbox" name="roles[]" value="{{ $role->id }}"
                                {{ in_array($role->id, old('roles', [])) ? 'checked' : '' }}
                                class="h-4 w-4 rounded text-indigo-600 border-gray-300 focus:ring-indigo-500">
                        </div>
                        <div class="ml-3">
                            <span class="text-sm font-medium text-gray-900">
                                {{ $isId ? ($role->name === 'Journal Manager' ? 'Manajer Jurnal' : ($role->name === 'Section Editor' ? 'Editor Bagian' : ($role->name === 'Reviewer' ? 'Peninjau' : ($role->name === 'Author' ? 'Penulis' : ($role->name === 'Reader' ? 'Pembaca' : ($role->name === 'Admin' ? 'Administrator' : $role->name)))))) : $role->name }}
                            </span>
                            <p class="text-xs text-gray-500 mt-0.5">
                                {{ $roleDescriptions[$role->name] ?? ($isId ? 'Peran akses standar' : 'Standard access role') }}</p>
                        </div>
                    </label>
                @endforeach
            </div>
        </div>

        {{-- Section 4: Public Profile --}}
        <div class="p-6 border-b border-gray-100 bg-gray-50/50">
            <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                <i class="fa-solid fa-globe text-indigo-500 mr-2"></i> {{ $isId ? 'Profil Publik' : 'Public Profile' }}
            </h2>
            <div class="grid grid-cols-1 gap-6">
                {{-- ORCID --}}
                <div>
                    <label for="orcid_id" class="block text-sm font-medium text-gray-700">ORCID iD</label>
                    <div class="mt-1 flex rounded-md shadow-sm">
                        <span
                            class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 bg-gray-50 text-gray-500 sm:text-sm">
                            https://orcid.org/
                        </span>
                        <input type="text" name="orcid_id" id="orcid_id" value="{{ old('orcid_id') }}"
                            placeholder="0000-0000-0000-0000"
                            class="flex-1 min-w-0 block w-full px-3 py-2 rounded-none rounded-r-md border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm border">
                    </div>
                </div>

                {{-- Bio --}}
                <div>
                    <label for="bio" class="block text-sm font-medium text-gray-700">{{ $isId ? 'Pernyataan Bio' : 'Bio Statement' }}</label>
                    <textarea name="bio" id="bio" rows="4"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm border p-2">{{ old('bio') }}</textarea>
                </div>
            </div>
        </div>

        {{-- Section 5: Account --}}
        <div class="p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                <i class="fa-solid fa-lock text-indigo-500 mr-2"></i> {{ $isId ? 'Akses Akun' : 'Account Access' }}
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6" x-data="{
                username: '{{ old('username', '') }}',
                password: '',
                get passwordStrength() {
                    if (this.password.length === 0) {
                        return { score: 0, color: 'bg-gray-200 w-0', text: '', isStrong: false };
                    }
                    let score = 0;
                    let hasLower = /[a-z]/.test(this.password);
                    let hasUpper = /[A-Z]/.test(this.password);
                    let hasNumber = /[0-9]/.test(this.password);
                    let hasSpecial = /[^A-Za-z0-9]/.test(this.password);
                    
                    if (hasLower) score++;
                    if (hasUpper) score++;
                    if (hasNumber) score++;
                    if (hasSpecial) score++;
                    
                    if (this.password.length < 8) {
                        return { score: 1, color: 'bg-red-500 w-1/3', text: '{{ $isId ? 'Lemah' : 'Weak' }}', isStrong: false };
                    }
                    
                    if (score <= 2) {
                        return { score: 1, color: 'bg-red-500 w-1/3', text: '{{ $isId ? 'Lemah' : 'Weak' }}', isStrong: false };
                    } else if (score === 3) {
                        return { score: 2, color: 'bg-yellow-500 w-2/3', text: '{{ $isId ? 'Sedang' : 'Medium' }}', isStrong: false };
                    } else {
                        return { score: 3, color: 'bg-emerald-500 w-full', text: '{{ $isId ? 'Kuat' : 'Strong' }}', isStrong: true };
                    }
                }
            }">
                {{-- Username --}}
                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700">{{ $isId ? 'Nama Pengguna (Username)' : 'Username' }} <span
                            class="text-red-500">*</span></label>
                    <input type="text" name="username" id="username" x-model="username" @input="username = username.toLowerCase()" required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm border p-2">
                    @error('username')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Password Inputs (Always visible on Create) --}}
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">{{ $isId ? 'Kata Sandi' : 'Password' }} <span
                            class="text-red-500">*</span></label>
                    <div class="relative">
                        <input type="password" name="password" id="password" x-model="password" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm border p-2 pr-10">
                        <i class="fa-solid fa-circle-check text-emerald-500 text-lg absolute right-3 inset-y-0 my-auto h-fit pointer-events-none" x-show="passwordStrength.isStrong" x-cloak></i>
                    </div>
                    <div class="mt-1.5 w-full bg-gray-100 rounded-full h-1.5 overflow-hidden" x-show="password.length > 0" x-cloak>
                        <div class="h-full transition-all duration-300 rounded-full" :class="passwordStrength.color"></div>
                    </div>
                    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mt-1 gap-1">
                        <span class="text-xs font-semibold" :class="{'text-red-500': passwordStrength.score === 1, 'text-yellow-500': passwordStrength.score === 2, 'text-emerald-500': passwordStrength.score === 3}" x-text="passwordStrength.text" x-show="password.length > 0" x-cloak></span>
                    </div>
                    <span class="text-[11px] font-medium text-slate-500 flex items-center gap-1 mt-0.5"><i class="fa-solid fa-circle-info text-slate-400"></i> {{ $isId ? 'Kombinasi huruf kecil, huruf besar, angka, dan karakter khusus.' : 'Combination of lowercase, uppercase, numbers, and special characters.' }}</span>
                    @error('password')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700">{{ $isId ? 'Ulangi Kata Sandi' : 'Repeat Password' }}
                        <span class="text-red-500">*</span></label>
                    <input type="password" name="password_confirmation" id="password_confirmation" required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm border p-2">
                </div>
            </div>
        </div>

        <div class="bg-gray-50 px-6 py-4 flex justify-end gap-3 border-t border-gray-200">
            <a href="{{ route($routePrefix . '.index', ['journal' => $journal->slug]) }}"
                class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 text-sm font-medium">{{ $isId ? 'Batal' : 'Cancel' }}</a>
            <button type="submit"
                class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-sm font-medium shadow-sm transition-colors">
                {{ $isId ? 'Buat Pengguna' : 'Create User' }}
            </button>
        </div>
    </form>
@endsection
