<x-app-layout>
    <x-slot name="title">Profile Settings</x-slot>

    <div class="min-h-screen bg-gray-50 -m-4 sm:-m-6 lg:-m-8 p-4 sm:p-6 lg:p-8">
        <div class="max-w-4xl mx-auto">
            <!-- Header -->
            <div class="mb-8">
                <a href="{{ route('dashboard') }}"
                    class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700 mb-4">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Back to Dashboard
                </a>
                <h1 class="text-3xl font-bold text-gray-900">Profile Settings</h1>
                <p class="mt-2 text-gray-600">Manage your account information and preferences</p>
            </div>

            <!-- Success/Error Messages -->
            @if (session('success'))
                <div class="mb-6 bg-green-50 border border-green-200 rounded-xl p-4 flex items-start">
                    <svg class="w-5 h-5 text-green-500 mr-3 flex-shrink-0 mt-0.5" fill="currentColor"
                        viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                            clip-rule="evenodd" />
                    </svg>
                    <span class="text-green-800">{{ session('success') }}</span>
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-6 bg-red-50 border border-red-200 rounded-xl p-4">
                    <div class="flex items-start">
                        <svg class="w-5 h-5 text-red-500 mr-3 flex-shrink-0 mt-0.5" fill="currentColor"
                            viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                clip-rule="evenodd" />
                        </svg>
                        <div>
                            <p class="font-medium text-red-800">Please fix the following errors:</p>
                            <ul class="mt-2 text-sm text-red-700 list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif

            <div class="space-y-8">
                <!-- Avatar Section -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="bg-gradient-to-r from-primary-600 to-primary-700 px-6 py-4">
                        <h2 class="text-lg font-semibold text-white">Profile Picture</h2>
                    </div>
                    <div class="p-6">
                        <div class="flex items-center space-x-6" x-data="{ previewUrl: null }">
                            <!-- Current Avatar -->
                            <div class="relative">
                                @if ($user->avatar_url)
                                    <img x-show="!previewUrl" src="{{ $user->avatar_url }}" alt="{{ $user->name }}"
                                        class="w-24 h-24 rounded-full object-cover border-4 border-white shadow-lg">
                                @else
                                    <div x-show="!previewUrl"
                                        class="w-24 h-24 rounded-full bg-gradient-to-br from-primary-500 to-primary-700 flex items-center justify-center border-4 border-white shadow-lg">
                                        <span class="text-white text-2xl font-bold">{{ $user->initials }}</span>
                                    </div>
                                @endif
                                <!-- Preview -->
                                <img x-show="previewUrl" x-cloak :src="previewUrl"
                                    class="w-24 h-24 rounded-full object-cover border-4 border-white shadow-lg">
                            </div>

                            <div class="flex-1">
                                <p class="text-sm text-gray-600 mb-4">
                                    Upload a new avatar. Max file size: 2MB. Allowed formats: JPG, PNG, WebP.
                                </p>
                                <div class="flex flex-wrap gap-3">
                                    <form action="{{ route('profile.avatar') }}" method="POST"
                                        enctype="multipart/form-data" class="flex items-center gap-3">
                                        @csrf
                                        @method('PATCH')
                                        <input type="file" name="avatar" id="avatar-input"
                                            accept="image/jpeg,image/png,image/jpg,image/webp"
                                            @change="if ($event.target.files[0]) { 
                                                if ($event.target.files[0].size > 2 * 1024 * 1024) { 
                                                    alert('File size must be less than 2MB'); 
                                                    $event.target.value = ''; 
                                                } else { 
                                                    previewUrl = URL.createObjectURL($event.target.files[0]); 
                                                }
                                            }"
                                            class="hidden">
                                        <label for="avatar-input"
                                            class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 cursor-pointer transition-colors">
                                            <svg class="w-4 h-4 mr-2 text-gray-500" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                            Choose Image
                                        </label>
                                        <button x-show="previewUrl" x-cloak type="submit"
                                            class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg text-sm font-medium transition-colors">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                                            </svg>
                                            Upload
                                        </button>
                                    </form>

                                    @if ($user->avatar)
                                        <form action="{{ route('profile.avatar.delete') }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="inline-flex items-center px-4 py-2 text-red-600 hover:bg-red-50 rounded-lg text-sm font-medium transition-colors"
                                                onclick="return confirm('Are you sure you want to remove your avatar?')">
                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                                Remove
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Profile Information -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="bg-gradient-to-r from-primary-600 to-primary-700 px-6 py-4">
                        <h2 class="text-lg font-semibold text-white">Profile Information</h2>
                    </div>
                    <form action="{{ route('profile.update') }}" method="POST" class="p-6">
                        @csrf
                        @method('PATCH')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Name -->
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                                    Full Name <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="name" id="name"
                                    value="{{ old('name', $user->name) }}" required
                                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 @error('name') border-red-500 @enderror">
                                @error('name')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Email -->
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                                    Email Address <span class="text-red-500">*</span>
                                </label>
                                <input type="email" name="email" id="email"
                                    value="{{ old('email', $user->email) }}" required
                                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 @error('email') border-red-500 @enderror">
                                @error('email')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Affiliation -->
                            <div>
                                <label for="affiliation" class="block text-sm font-medium text-gray-700 mb-2">
                                    Affiliation / Institution
                                </label>
                                <input type="text" name="affiliation" id="affiliation"
                                    value="{{ old('affiliation', $user->affiliation) }}"
                                    placeholder="e.g., University of Indonesia"
                                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                            </div>

                            <!-- Country -->
                            <div>
                                <label for="country" class="block text-sm font-medium text-gray-700 mb-2">
                                    Country
                                </label>
                                <input type="text" name="country" id="country"
                                    value="{{ old('country', $user->country) }}" placeholder="e.g., Indonesia"
                                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                            </div>

                            <!-- Phone -->
                            <div>
                                <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">
                                    Phone Number
                                </label>
                                <input type="tel" name="phone" id="phone"
                                    value="{{ old('phone', $user->phone) }}" placeholder="e.g., +62 812 3456 7890"
                                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                            </div>

                            <!-- Role (Read-only) -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Role
                                </label>
                                <div class="flex flex-wrap gap-2">
                                    @foreach ($user->roles as $role)
                                        <span
                                            class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium bg-primary-100 text-primary-800">
                                            {{ $role->name }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Bio -->
                            <div class="md:col-span-2">
                                <label for="bio" class="block text-sm font-medium text-gray-700 mb-2">
                                    Short Biography
                                </label>
                                <textarea name="bio" id="bio" rows="4"
                                    placeholder="Tell us about yourself, your research interests, and expertise..."
                                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">{{ old('bio', $user->bio) }}</textarea>
                                <p class="mt-1 text-xs text-gray-500">Maximum 2000 characters</p>
                            </div>
                        </div>

                        <div class="mt-6 flex justify-end">
                            <button type="submit"
                                class="inline-flex items-center px-6 py-2.5 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7" />
                                </svg>
                                Save Changes
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Change Password -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="bg-gradient-to-r from-gray-700 to-gray-800 px-6 py-4">
                        <h2 class="text-lg font-semibold text-white">Change Password</h2>
                    </div>
                    <form action="{{ route('profile.password') }}" method="POST" class="p-6">
                        @csrf
                        @method('PATCH')

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <!-- Current Password -->
                            <div>
                                <label for="current_password" class="block text-sm font-medium text-gray-700 mb-2">
                                    Current Password <span class="text-red-500">*</span>
                                </label>
                                <input type="password" name="current_password" id="current_password" required
                                    autocomplete="current-password"
                                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 @error('current_password') border-red-500 @enderror">
                                @error('current_password')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- New Password -->
                            <div>
                                <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                                    New Password <span class="text-red-500">*</span>
                                </label>
                                <input type="password" name="password" id="password" required
                                    autocomplete="new-password"
                                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 @error('password') border-red-500 @enderror">
                                @error('password')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                <p class="mt-1 text-xs text-gray-500">Min 8 characters, mixed case, numbers</p>
                            </div>

                            <!-- Confirm Password -->
                            <div>
                                <label for="password_confirmation"
                                    class="block text-sm font-medium text-gray-700 mb-2">
                                    Confirm Password <span class="text-red-500">*</span>
                                </label>
                                <input type="password" name="password_confirmation" id="password_confirmation"
                                    required autocomplete="new-password"
                                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                            </div>
                        </div>

                        <div class="mt-6 flex justify-end">
                            <button type="submit"
                                class="inline-flex items-center px-6 py-2.5 bg-gray-800 hover:bg-gray-900 text-white font-medium rounded-lg transition-colors">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                                Update Password
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Account Stats -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900">Account Information</h2>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                            <div class="text-center p-4 bg-gray-50 rounded-xl">
                                <p class="text-sm text-gray-500 mb-1">Member Since</p>
                                <p class="text-lg font-semibold text-gray-900">
                                    {{ $user->created_at->format('M d, Y') }}</p>
                            </div>
                            <div class="text-center p-4 bg-gray-50 rounded-xl">
                                <p class="text-sm text-gray-500 mb-1">Email Verified</p>
                                <p
                                    class="text-lg font-semibold {{ $user->email_verified_at ? 'text-green-600' : 'text-yellow-600' }}">
                                    {{ $user->email_verified_at ? 'Verified' : 'Pending' }}
                                </p>
                            </div>
                            <div class="text-center p-4 bg-gray-50 rounded-xl">
                                <p class="text-sm text-gray-500 mb-1">Total Submissions</p>
                                <p class="text-lg font-semibold text-gray-900">{{ $user->submissions()->count() }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
