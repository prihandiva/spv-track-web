<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
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
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
        .login-bg {
            background: linear-gradient(135deg, #102649 0%, #1a3c6e 40%, #0d5950 100%);
            position: relative;
            overflow: hidden;
        }
        .login-bg::before {
            content: '';
            position: absolute;
            top: -20%;
            left: -10%;
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(5, 158, 61, 0.22) 0%, rgba(5, 158, 61, 0) 70%);
            border-radius: 50%;
            pointer-events: none;
        }
        .login-bg::after {
            content: '';
            position: absolute;
            bottom: -20%;
            right: -10%;
            width: 650px;
            height: 650px;
            background: radial-gradient(circle, rgba(40, 84, 145, 0.35) 0%, rgba(40, 84, 145, 0) 70%);
            border-radius: 50%;
            pointer-events: none;
        }
        .glass-card {
            background: rgba(255, 255, 255, 0.96);
            backdrop-filter: blur(20px);
            box-shadow: 0 25px 50px -12px rgba(10, 25, 47, 0.35), 0 0 0 1px rgba(255, 255, 255, 0.2);
        }
    </style>
</head>
<body class="login-bg min-h-screen flex items-center justify-center p-4 sm:p-6 text-gray-800"
      x-data="{
          username: '{{ old('login', '') }}',
          password: '',
          showPassword: false,
          fillDemo(u, p) {
              this.username = u;
              this.password = p;
          }
      }">

    <div class="w-full max-w-md relative z-10 animate-fade-in my-8">
        
        <!-- Logo & Header -->
        <div class="text-center mb-7">
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl shadow-xl mb-3.5 transform hover:scale-105 transition-transform"
                 style="background: linear-gradient(135deg, #059e3d, #63c384); box-shadow: 0 10px 25px rgba(5, 158, 61, 0.4);">
                <i class="ph-fill ph-shipping-container text-3xl text-white"></i>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">SPV-Track</h1>
            <p class="text-xs sm:text-sm text-emerald-200/80 mt-1 font-medium">Logistic Monitoring & SOP Staging System</p>
            <p class="text-[11px] text-white/50">PT. South Pacific Viscose</p>
        </div>

        <!-- Notification Alerts -->
        @if(session('success'))
            <div class="mb-4 p-3.5 rounded-xl bg-emerald-500/20 border border-emerald-400/40 text-emerald-100 flex items-center gap-3 backdrop-blur-sm text-xs">
                <i class="ph-bold ph-check-circle text-lg shrink-0 text-emerald-300"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if($errors->has('login'))
            <div class="mb-4 p-3.5 rounded-xl bg-rose-500/20 border border-rose-400/40 text-rose-100 flex items-center gap-3 backdrop-blur-sm text-xs">
                <i class="ph-bold ph-warning-circle text-lg shrink-0 text-rose-300"></i>
                <span>{{ $errors->first('login') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-4 p-3.5 rounded-xl bg-rose-500/20 border border-rose-400/40 text-rose-100 flex items-center gap-3 backdrop-blur-sm text-xs">
                <i class="ph-bold ph-warning-circle text-lg shrink-0 text-rose-300"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <!-- Login Card -->
        <div class="glass-card rounded-3xl p-6 sm:p-8">
            <div class="mb-6">
                <h2 class="text-lg font-bold text-gray-900">Masuk ke Sistem</h2>
                <p class="text-xs text-gray-500 mt-0.5">Silakan masukkan kredensial akun Anda untuk melanjutkan.</p>
            </div>

            <form action="{{ route('login.post') }}" method="POST" class="space-y-4">
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
                               placeholder="superadmin / admin / operator"
                               class="w-full pl-10 pr-3.5 py-2.5 bg-gray-50/80 border border-gray-200 rounded-xl text-sm font-medium text-gray-800 placeholder-gray-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-spv-blue focus:border-transparent transition-all">
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
                               placeholder="••••••••"
                               class="w-full pl-10 pr-10 py-2.5 bg-gray-50/80 border border-gray-200 rounded-xl text-sm font-medium text-gray-800 placeholder-gray-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-spv-blue focus:border-transparent transition-all">
                        <button type="button"
                                @click="showPassword = !showPassword"
                                class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-gray-400 hover:text-gray-600 transition-colors"
                                tabindex="-1">
                            <i class="ph-bold" :class="showPassword ? 'ph-eye-slash' : 'ph-eye'"></i>
                        </button>
                    </div>
                </div>

                <!-- Remember Me -->
                <div class="flex items-center justify-between pt-1">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="remember" class="w-4 h-4 rounded border-gray-300 text-spv-blue focus:ring-spv-blue">
                        <span class="text-xs text-gray-600 font-medium select-none">Ingat saya di perangkat ini</span>
                    </label>
                </div>

                <!-- Submit Button -->
                <button type="submit"
                        class="w-full flex items-center justify-center gap-2 py-3 px-4 rounded-xl text-sm font-bold text-white shadow-lg transition-all duration-200 transform hover:-translate-y-0.5 active:translate-y-0 mt-2"
                        style="background: linear-gradient(135deg, #1a3c6e 0%, #285491 100%); box-shadow: 0 4px 14px rgba(40, 84, 145, 0.35);">
                    <i class="ph-bold ph-sign-in text-lg"></i>
                    <span>Masuk ke Dashboard</span>
                </button>
            </form>

            <!-- Quick Demo Credentials Selector -->
            <div class="mt-6 pt-5 border-t border-gray-100">
                <div class="flex items-center justify-between mb-2.5">
                    <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Akses Cepat Demo</span>
                    <span class="text-[10px] bg-emerald-100 text-emerald-800 font-bold px-2 py-0.5 rounded-full">Password: password</span>
                </div>
                <div class="grid grid-cols-3 gap-2">
                    <button type="button"
                            @click="fillDemo('superadmin', 'password')"
                            class="flex flex-col items-center justify-center p-2.5 rounded-xl border border-gray-200 bg-gray-50/70 hover:bg-emerald-50 hover:border-emerald-300 transition-all text-center group">
                        <span class="text-base mb-1 group-hover:scale-110 transition-transform">👑</span>
                        <span class="text-xs font-bold text-gray-800 group-hover:text-emerald-700 leading-tight">Superadmin</span>
                        <span class="text-[10px] text-gray-400 mt-0.5">Semua Akses</span>
                    </button>

                    <button type="button"
                            @click="fillDemo('admin', 'password')"
                            class="flex flex-col items-center justify-center p-2.5 rounded-xl border border-gray-200 bg-gray-50/70 hover:bg-blue-50 hover:border-blue-300 transition-all text-center group">
                        <span class="text-base mb-1 group-hover:scale-110 transition-transform">🛡️</span>
                        <span class="text-xs font-bold text-gray-800 group-hover:text-blue-700 leading-tight">Admin</span>
                        <span class="text-[10px] text-gray-400 mt-0.5">View & Download</span>
                    </button>

                    <button type="button"
                            @click="fillDemo('operator', 'password')"
                            class="flex flex-col items-center justify-center p-2.5 rounded-xl border border-gray-200 bg-gray-50/70 hover:bg-amber-50 hover:border-amber-300 transition-all text-center group">
                        <span class="text-base mb-1 group-hover:scale-110 transition-transform">📦</span>
                        <span class="text-xs font-bold text-gray-800 group-hover:text-amber-700 leading-tight">Operator</span>
                        <span class="text-[10px] text-gray-400 mt-0.5">Buat Shipment</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="text-center mt-6">
            <p class="text-[11px] text-white/50">
                &copy; {{ date('Y') }} PT. South Pacific Viscose &bull; All Rights Reserved
            </p>
        </div>

    </div>

</body>
</html>
