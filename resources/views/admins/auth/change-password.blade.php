<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password | {{ config('app.name', 'IAMJOS') }}</title>
    <meta name="description" content="Create new password for your IAMJOS account">
    <meta name="robots" content="noindex, nofollow">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700|eb-garamond:400,500,600,700,800&display=swap" rel="stylesheet" />

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />

    <!-- Tailwind CSS via CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'system-ui', 'sans-serif'],
                        serif: ['EB Garamond', 'serif'],
                    },
                }
            }
        }
    </script>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        .academic-overlay {
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.95) 0%, rgba(30, 41, 59, 0.8) 50%, rgba(15, 23, 42, 0.95) 100%);
            backdrop-blur: 2px;
        }
        .paper-texture {
            background-image: url("https://www.transparenttextures.com/patterns/natural-paper.png");
            opacity: 0.05;
        }
    </style>
</head>

<body class="font-sans antialiased bg-gray-50">
    <x-language-switcher />
    <div class="min-h-screen flex">
        <!-- Left Side - Brand Panel -->
        <div class="hidden lg:flex lg:w-1/2 relative overflow-hidden bg-slate-900"
            style="background-image: url('{{ asset('assets/images/academic-bg.png') }}'); background-size: cover; background-position: center;">
            
            <!-- Background Gradient Overlay -->
            <div class="absolute inset-0 academic-overlay"></div>
            
            <!-- Paper Texture Overlay -->
            <div class="absolute inset-0 paper-texture"></div>

            <!-- Legacy Borders -->
            <div class="absolute inset-12 border border-white/10 pointer-events-none"></div>
            <div class="absolute inset-14 border border-white/5 pointer-events-none"></div>

            <!-- Content -->
            <div class="relative z-10 flex flex-col justify-center px-16 xl:px-24 w-full">
                <!-- Logo -->
                <div class="mb-12">
                    <div class="flex items-center gap-4">
                        <div class="w-14 h-14 bg-white/10 rounded-xl flex items-center justify-center backdrop-blur-md border border-white/20">
                            <i class="fas fa-book-open text-2xl text-white"></i>
                        </div>
                        <div class="flex flex-col">
                            <span class="text-2xl font-bold text-white tracking-widest uppercase">
                                {{ $branding['acronym'] ?? 'IAMJOS' }}
                            </span>
                            <span class="text-[10px] text-indigo-300 font-bold tracking-[0.3em] uppercase">Academic Publishing</span>
                        </div>
                    </div>
                </div>

                <!-- Heading -->
                <h1 class="text-4xl xl:text-6xl font-serif font-bold text-white leading-[1.1] mb-8">
                    Advance Your<br>
                    <span class="text-indigo-300 italic">Academic Research</span>
                </h1>

                <!-- Tagline -->
                <div class="relative mb-12">
                    <div class="absolute -left-6 top-0 bottom-0 w-1 bg-indigo-500/50"></div>
                    <p class="text-xl font-medium text-gray-300 leading-relaxed max-w-md italic font-serif opacity-90">
                        "{{ $branding['tagline'] ?? 'A modern platform for managing academic journal submissions, peer reviews, and publications with streamlined workflows.' }}"
                    </p>
                </div>

                <!-- Features -->
                <div class="space-y-4">
                    <div class="flex items-center gap-4 text-indigo-100">
                        <div class="w-10 h-10 bg-white/10 rounded-lg flex items-center justify-center">
                            <i class="fas fa-paper-plane text-sm"></i>
                        </div>
                        <span class="text-sm">Streamlined Submission Process</span>
                    </div>
                    <div class="flex items-center gap-4 text-indigo-100">
                        <div class="w-10 h-10 bg-white/10 rounded-lg flex items-center justify-center">
                            <i class="fas fa-users text-sm"></i>
                        </div>
                        <span class="text-sm">Collaborative Peer Review</span>
                    </div>
                    <div class="flex items-center gap-4 text-indigo-100">
                        <div class="w-10 h-10 bg-white/10 rounded-lg flex items-center justify-center">
                            <i class="fas fa-chart-line text-sm"></i>
                        </div>
                        <span class="text-sm">Editorial Workflow Management</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Side - Form -->
        <div class="w-full lg:w-1/2 flex items-center justify-center p-8 lg:p-12 bg-white">
            <div class="w-full max-w-md" x-data="{ showPassword: false, showConfirmPassword: false }">
                <!-- Mobile Logo -->
                <div class="lg:hidden mb-10 text-center">
                    <div class="inline-flex items-center gap-3 justify-center mb-2">
                        <div class="w-10 h-10 bg-slate-900 rounded-lg flex items-center justify-center">
                            <i class="fas fa-book-open text-white text-lg"></i>
                        </div>
                        <span class="text-xl font-bold tracking-wider text-slate-900 uppercase">IAMJOS</span>
                    </div>
                    <p class="text-xs text-slate-500 uppercase tracking-widest font-bold">Academic Publishing</p>
                </div>

                <div class="mb-8">
                    <h2 class="text-3xl font-serif font-bold text-slate-900 mb-2">Reset Password</h2>
                    <p class="text-slate-600 text-sm">Please enter your new password below</p>
                </div>

                <!-- Alert Messages -->
                @if ($errors->any())
                    <div class="mb-6 p-4 bg-red-50 border border-red-100 rounded-lg">
                        <div class="flex items-start gap-3">
                            <i class="fas fa-exclamation-circle text-red-500 mt-0.5"></i>
                            <div>
                                <ul class="list-disc list-inside text-sm text-red-600">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                @endif

                @if (session('error'))
                    <div class="mb-6 p-4 bg-red-50 border border-red-100 rounded-lg flex gap-3 text-sm text-red-800">
                        <i class="fas fa-exclamation-circle mt-0.5 text-red-500"></i>
                        <p class="text-sm text-red-700">{{ session('error') }}</p>
                    </div>
                @endif

                <form action="{{ route('reset-password') }}" method="POST" class="space-y-6">
                    @csrf
                    <input type="text" name="token" hidden value="{{ request()->token }}" required>
                    <input type="text" name="type" hidden value="{{ $type }}" required>

                    <!-- New Password Field -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-slate-700 mb-1.5">
                            New Password
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-lock text-slate-400 text-sm"></i>
                            </div>
                            <input :type="showPassword ? 'text' : 'password'" id="password" name="password"
                                placeholder="Enter your new password"
                                class="block w-full pl-10 pr-12 py-2.5 border border-slate-300 rounded-lg text-slate-900 placeholder-slate-400 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                                required>
                            <button type="button" @click="showPassword = !showPassword"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 transition-colors">
                                <i class="fas" :class="showPassword ? 'fa-eye-slash' : 'fa-eye'"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Confirm Password Field -->
                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-slate-700 mb-1.5">
                            Confirm New Password
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-lock text-slate-400 text-sm"></i>
                            </div>
                            <input :type="showConfirmPassword ? 'text' : 'password'" id="password_confirmation" name="password_confirmation"
                                placeholder="Confirm your new password"
                                class="block w-full pl-10 pr-12 py-2.5 border border-slate-300 rounded-lg text-slate-900 placeholder-slate-400 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                                required>
                            <button type="button" @click="showConfirmPassword = !showConfirmPassword"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 transition-colors">
                                <i class="fas" :class="showConfirmPassword ? 'fa-eye-slash' : 'fa-eye'"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div>
                        <button type="submit"
                            class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-lg shadow-sm transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 flex items-center justify-center gap-2">
                            Reset Password
                        </button>
                    </div>

                    <div class="mt-6 text-center">
                        <a href="{{ route('login') }}"
                            class="font-semibold text-blue-600 hover:text-blue-500 text-sm flex items-center justify-center gap-2">
                            <i class="fas fa-arrow-left text-xs"></i> Back to Login
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @if (session('success'))
    <script>
        Swal.fire({
            title: "Password Reset Successfully",
            text: "{{ session('success') }}",
            icon: "success",
            buttonsStyling: false,
            confirmButtonText: "OK",
            customClass: {
                confirmButton: "bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-6 rounded-lg transition-colors cursor-pointer"
            }
        }).then(function(result) {
            if (result.isConfirmed) {
                window.location.href = "{{ route('login') }}";
            }
        });
    </script>
    @endif
</body>

</html>
