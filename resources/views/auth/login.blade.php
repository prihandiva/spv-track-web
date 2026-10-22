<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>Masuk — SPV-Track Logistic Monitoring</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Tailwind CSS (Offline Local Bundle + CDN Fallback) -->
    <script src="{{ asset('vendor/tailwind.min.js') }}"></script>
    <script>
        if (typeof tailwind === 'undefined') {
            document.write('<script src="https://cdn.tailwindcss.com"><\/script>');
        }
    </script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'spv-blue': '#285491',
                        'spv-blue-dark': '#1a3c6e',
                        'spv-green': '#059e3d',
                        'spv-soft-green': '#63c384',
                        'spv-dark-teal': '#0d5950',
                    },
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                    }
                }
            }
        };
    </script>

    <!-- Phosphor Icons -->
    <script src="{{ asset('vendor/phosphor.min.js') }}"></script>
    <!-- Alpine.js -->
    <script defer src="{{ asset('vendor/alpine.min.js') }}"></script>

    <style>
        html, body {
            min-height: 100%;
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #102649;
            -webkit-tap-highlight-color: transparent;
        }
        .login-canvas {
            min-height: 100vh;
            min-height: 100dvh;
            background: linear-gradient(135deg, #102649 0%, #1a3c6e 45%, #0d5950 100%);
            background-attachment: fixed;
        }
        .glass-card {
            background: rgba(255, 255, 255, 0.97);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            box-shadow: 0 20px 50px -10px rgba(10, 25, 47, 0.4), 0 0 0 1px rgba(255, 255, 255, 0.35);
        }
        .pb-safe {
            padding-bottom: max(1.5rem, env(safe-area-inset-bottom, 1.5rem));
        }
    </style>
</head>
<body class="login-canvas relative text-gray-800 antialiased selection:bg-emerald-500 selection:text-white"
      x-data="{
          username: '{{ old('login', '') }}',
          password: '',
          showPassword: false,
          isSubmitting: false
      }">

    <!-- Ambient Glow Spheres (Fixed in background so no overflow/cutoffs occur) -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none z-0">
        <div class="absolute -top-24 -left-24 w-72 h-72 sm:w-96 sm:h-96 md:w-[500px] md:h-[500px] rounded-full bg-emerald-500/20 blur-3xl"></div>
        <div class="absolute top-1/2 -right-24 w-80 h-80 sm:w-[450px] sm:h-[450px] md:w-[600px] md:h-[600px] rounded-full bg-spv-blue/35 blur-3xl"></div>
        <div class="absolute -bottom-24 left-1/3 w-72 h-72 sm:w-96 sm:h-96 rounded-full bg-teal-500/15 blur-3xl"></div>
    </div>

    <!-- Main Content Container -->
    <div class="relative z-10 min-h-screen min-h-[100dvh] flex flex-col justify-between items-center px-4 py-6 sm:py-10 pb-safe">
        
        <!-- Top Spacer for centering balance -->
        <div class="w-full max-w-md hidden sm:block shrink-0 h-2"></div>

        <!-- Central Card Area -->
        <div class="w-full max-w-md my-auto">
            
            <!-- Logo & System Branding -->
            <div class="text-center mb-6 sm:mb-8">
                <div class="inline-flex items-center justify-center w-14 h-14 sm:w-16 sm:h-16 rounded-2xl shadow-xl mb-3.5 transform hover:scale-105 transition-transform"
                     style="background: linear-gradient(135deg, #059e3d, #63c384); box-shadow: 0 10px 25px rgba(5, 158, 61, 0.4);">
                    <i class="ph-fill ph-shipping-container text-3xl sm:text-4xl text-white"></i>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">SPV-Track</h1>
                <p class="text-xs sm:text-sm text-emerald-200/90 mt-1 font-medium">Logistic Monitoring & SOP Staging System</p>
                <p class="text-[11px] text-white/50 mt-0.5 font-medium">PT. South Pacific Viscose</p>
            </div>

            <!-- Logout / Success Notification Alert -->
            @if(session('success') || request('logged_out'))
                <div x-data="{ show: true }"
                     x-show="show"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 -translate-y-2 scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95"
                     class="mb-4 sm:mb-5 p-3.5 sm:p-4 rounded-2xl bg-emerald-950/70 border border-emerald-400/60 text-emerald-50 flex items-start gap-3 shadow-xl shadow-emerald-950/40 backdrop-blur-md">
                    <div class="w-8 h-8 rounded-xl bg-emerald-500/25 border border-emerald-400/40 text-emerald-300 flex items-center justify-center shrink-0 mt-0.5">
                        <i class="ph-bold ph-check-circle text-xl text-emerald-300"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs sm:text-sm font-bold text-white leading-tight">Berhasil Logout</p>
                        <p class="text-xs text-emerald-200 mt-0.5 leading-relaxed">
                            {{ session('success') ?? 'Anda telah berhasil keluar dari sistem SPV-Track. Sesi Anda telah diamankan.' }}
                        </p>
                    </div>
                    <button type="button" @click="show = false" class="text-emerald-300/80 hover:text-white p-1 rounded-lg hover:bg-white/10 transition-colors" title="Tutup">
                        <i class="ph-bold ph-x text-sm"></i>
                    </button>
                </div>
            @endif

            <!-- Error Notifications -->
            @if($errors->has('login') || session('error'))
                <div x-data="{ show: true }"
                     x-show="show"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 -translate-y-2 scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                     class="mb-4 sm:mb-5 p-3.5 sm:p-4 rounded-2xl bg-rose-950/70 border border-rose-400/60 text-rose-50 flex items-start gap-3 shadow-xl shadow-rose-950/40 backdrop-blur-md">
                    <div class="w-8 h-8 rounded-xl bg-rose-500/25 border border-rose-400/40 text-rose-300 flex items-center justify-center shrink-0 mt-0.5">
                        <i class="ph-bold ph-warning-circle text-xl text-rose-300"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs sm:text-sm font-bold text-white leading-tight">Gagal Masuk</p>
                        <p class="text-xs text-rose-200 mt-0.5 leading-relaxed">
                            {{ $errors->first('login') ?? session('error') }}
                        </p>
                    </div>
                    <button type="button" @click="show = false" class="text-rose-300/80 hover:text-white p-1 rounded-lg hover:bg-white/10 transition-colors" title="Tutup">
                        <i class="ph-bold ph-x text-sm"></i>
                    </button>
                </div>
            @endif

            <!-- Login Card -->
            <div class="glass-card rounded-2xl sm:rounded-3xl p-5 sm:p-8">
                <div class="mb-5 sm:mb-6">
                    <h2 class="text-lg sm:text-xl font-bold text-gray-900 tracking-tight">Masuk ke Sistem</h2>
                    <p class="text-xs sm:text-sm text-gray-500 mt-1">Masukkan kredensial akun Anda untuk mengakses dashboard.</p>
                </div>

                <form action="{{ route('login.post') }}" method="POST" class="space-y-4" @submit="isSubmitting = true">
                    @csrf

                    <!-- Username / Email Field -->
                    <div>
                        <label for="login" class="block text-xs font-bold text-gray-700 mb-1.5">Username atau Email</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                                <i class="ph-bold ph-user text-base"></i>
                            </div>
                            <input type="text"
                                   name="login"
                                   id="login"
                                   x-model="username"
                                   required
                                   autocomplete="username"
                                   placeholder="Masukkan username atau email"
                                   class="w-full pl-10 pr-3.5 py-3 sm:py-2.5 bg-gray-50/80 border border-gray-200 rounded-xl text-base sm:text-sm font-medium text-gray-800 placeholder-gray-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-spv-blue focus:border-transparent transition-all">
                        </div>
                    </div>

                    <!-- Password Field -->
                    <div>
                        <label for="password" class="block text-xs font-bold text-gray-700 mb-1.5">Kata Sandi</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                                <i class="ph-bold ph-lock-key text-base"></i>
                            </div>
                            <input :type="showPassword ? 'text' : 'password'"
                                   name="password"
                                   id="password"
                                   x-model="password"
                                   required
                                   autocomplete="current-password"
                                   placeholder="Masukkan kata sandi"
                                   class="w-full pl-10 pr-11 py-3 sm:py-2.5 bg-gray-50/80 border border-gray-200 rounded-xl text-base sm:text-sm font-medium text-gray-800 placeholder-gray-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-spv-blue focus:border-transparent transition-all">
                            <button type="button"
                                    @click="showPassword = !showPassword"
                                    class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-gray-400 hover:text-gray-600 transition-colors focus:outline-none"
                                    tabindex="-1"
                                    aria-label="Toggle password visibility">
                                <i class="ph-bold text-lg" :class="showPassword ? 'ph-eye-slash' : 'ph-eye'"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Remember Me -->
                    <div class="flex items-center justify-between pt-0.5">
                        <label class="flex items-center gap-2.5 cursor-pointer py-1">
                            <input type="checkbox" name="remember" class="w-4 h-4 rounded border-gray-300 text-spv-blue focus:ring-spv-blue cursor-pointer">
                            <span class="text-xs text-gray-600 font-medium select-none">Ingat saya di perangkat ini</span>
                        </label>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit"
                            :disabled="isSubmitting"
                            class="w-full min-h-[46px] sm:min-h-[44px] flex items-center justify-center gap-2 py-3 px-4 rounded-xl text-sm font-bold text-white shadow-lg transition-all duration-200 transform hover:-translate-y-0.5 active:translate-y-0 mt-3 cursor-pointer disabled:opacity-75 disabled:cursor-not-allowed"
                            style="background: linear-gradient(135deg, #1a3c6e 0%, #285491 100%); box-shadow: 0 4px 14px rgba(40, 84, 145, 0.35);">
                        <template x-if="!isSubmitting">
                            <span class="inline-flex items-center gap-2">
                                <i class="ph-bold ph-sign-in text-lg"></i>
                                <span>Masuk ke Dashboard</span>
                            </span>
                        </template>
                        <template x-if="isSubmitting">
                            <span class="inline-flex items-center gap-2">
                                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span>Memverifikasi...</span>
                            </span>
                        </template>
                    </button>
                </form>
            </div>

        </div>

        <!-- Footer -->
        <div class="text-center mt-6 shrink-0">
            <p class="text-[11px] text-white/50 leading-relaxed font-medium">
                &copy; {{ date('Y') }} PT. South Pacific Viscose &bull; All Rights Reserved
            </p>
        </div>

    </div>

</body>
</html>
