<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? (trim($__env->yieldContent('title')) ? $__env->yieldContent('title') . ' — SPV-Track' : 'SPV-Track Dashboard') }}</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
    
    <!-- Tailwind CSS (Offline Local Bundle + CDN Fallback) -->
    <script src="{{ asset('vendor/tailwind.min.js') }}"></script>
    <script>
        if (typeof tailwind === 'undefined') {
            document.write('<script src="https://cdn.tailwindcss.com"><\/script>');
        }
    </script>
    
    <!-- Flatpickr 24-Hour Datetime Picker (Offline Local Bundle + CDN Fallback) -->
    <link rel="stylesheet" href="{{ asset('vendor/flatpickr.min.css') }}">
    <script src="{{ asset('vendor/flatpickr.min.js') }}"></script>
    <script src="{{ asset('vendor/flatpickr_id.js') }}"></script>
    <script>
        if (typeof flatpickr === 'undefined') {
            document.write('<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">');
            document.write('<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.js"><\/script>');
            document.write('<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/id.js"><\/script>');
        }
    </script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'spv-blue': '#285491',
                        'spv-blue-light': '#3568b0',
                        'spv-blue-dark': '#1a3c6e',
                        'spv-green': '#059e3d',
                        'spv-dark-teal': '#0d5950',
                        'spv-light-green': '#e1f8eb',
                        'spv-soft-green': '#63c384',
                        'spv-grey-1': '#c8c5c0',
                        'spv-grey-2': '#d7d7d5',
                        'spv-grey-3': '#aeacad',
                        'spv-bg': '#f0f4f9',
                    },
                    fontFamily: {
                        sans: ['-apple-system', 'BlinkMacSystemFont', '"SF Pro Display"', '"SF Pro Text"', '"Helvetica Neue"', 'Helvetica', 'Arial', 'sans-serif'],
                    }
                }
            }
        };
    </script>
    
    <style>
        [x-cloak] { display: none !important; }
        ::-webkit-scrollbar { width: 4px; height: 4px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #c8c5c0; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #285491; }
        
        @keyframes fadeSlideIn {
            from { opacity: 0; transform: translateY(8px); }
            to { opacity: 1; transform: none; }
        }
        .animate-fade-in { animation: fadeSlideIn 0.35s ease forwards; }
        
        @keyframes pulseGlow {
            0%, 100% { box-shadow: 0 0 0 0 rgba(5,158,61,0.35); }
            50% { box-shadow: 0 0 0 6px rgba(5,158,61,0); }
        }
        .active-glow { animation: pulseGlow 2.5s infinite; }
        
        .nav-item { transition: all 0.2s ease; }
        .nav-item .nav-icon { transition: transform 0.2s ease; }
        .nav-item:hover .nav-icon { transform: scale(1.15); }
        .nav-item.inactive:hover {
            background: rgba(255,255,255,0.09) !important;
            color: #ffffff !important;
        }
    </style>

    <!-- Chart.js (Offline Local Bundle + CDN Fallback) -->
    <script src="{{ asset('vendor/chart.umd.min.js') }}"></script>
    <script>
        if (typeof Chart === 'undefined') {
            document.write('<script src="https://cdn.jsdelivr.net/npm/chart.js"><\/script>');
        }
    </script>

    <!-- Phosphor Icons -->
    <script src="{{ asset('vendor/phosphor.min.js') }}"></script>
    <!-- Alpine.js -->
    <script defer src="{{ asset('vendor/alpine.min.js') }}"></script>
</head>
<body class="bg-spv-bg text-gray-800 font-sans antialiased flex h-screen overflow-hidden"
      x-data="{ sidebarOpen: false }">

    <!-- Mobile Backdrop -->
    <div x-cloak
         x-show="sidebarOpen"
         x-transition.opacity
         class="fixed inset-0 bg-black/60 backdrop-blur-sm z-40 lg:hidden"
         @click="sidebarOpen = false"></div>

    <!-- SIDEBAR -->
    <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
           class="fixed lg:static inset-y-0 left-0 w-64 -translate-x-full lg:translate-x-0 flex flex-col shadow-2xl transition-transform duration-300 ease-out z-50 shrink-0"
           style="background: linear-gradient(165deg, #1a3c6e 0%, #285491 50%, #0d5950 100%);">

        <!-- Brand & Close Button -->
        <div class="h-[68px] flex items-center justify-between px-5 shrink-0" style="border-bottom: 1px solid rgba(255,255,255,0.08);">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0"
                     style="background: linear-gradient(135deg, #059e3d, #63c384); box-shadow: 0 4px 14px rgba(5,158,61,0.45);">
                    <i class="ph-fill ph-shipping-container text-xl text-white"></i>
                </div>
                <div>
                    <p class="text-white font-bold text-sm leading-tight tracking-wide">SPV-Track</p>
                    <p class="text-[10px] font-medium" style="color: rgba(255,255,255,0.45);">Logistic Monitoring</p>
                </div>
            </div>
            <!-- Close button for mobile -->
            <button type="button" @click="sidebarOpen = false" class="lg:hidden p-2 rounded-xl text-white/70 hover:text-white hover:bg-white/10 transition-colors" aria-label="Tutup Menu">
                <i class="ph-bold ph-x text-xl"></i>
            </button>
        </div>

        <nav class="flex-1 overflow-y-auto py-4 px-3">
            <p class="px-3 pt-1 pb-2 text-[9px] font-bold uppercase tracking-[0.15em]" style="color: rgba(255,255,255,0.35);">Utama</p>

            <a href="{{ route('dashboard') }}"
               @click="sidebarOpen = false"
               class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl mb-0.5 {{ request()->routeIs('dashboard') ? 'active-glow' : 'inactive' }}"
               style="{{ request()->routeIs('dashboard') ? 'background: rgba(5,158,61,0.85); color: white;' : 'color: rgba(255,255,255,0.65);' }}">
                <i class="ph-fill ph-squares-four nav-icon text-xl shrink-0"></i>
                <span class="text-sm font-medium">Dashboard</span>
                @if(request()->routeIs('dashboard'))
                    <div class="ml-auto w-1.5 h-1.5 rounded-full bg-white/80"></div>
                @endif
            </a>

            <a href="{{ route('shipments.index') }}"
               @click="sidebarOpen = false"
               class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl mb-0.5 {{ request()->routeIs('shipments.*') ? 'active-glow' : 'inactive' }}"
               style="{{ request()->routeIs('shipments.*') ? 'background: rgba(5,158,61,0.85); color: white;' : 'color: rgba(255,255,255,0.65);' }}">
                <i class="ph-fill ph-package nav-icon text-xl shrink-0"></i>
                <span class="text-sm font-medium">Shipments</span>
                <span class="ml-auto text-[9px] font-bold px-1.5 py-0.5 rounded-full" style="background: rgba(255,255,255,0.15); color: rgba(255,255,255,0.85);">42</span>
            </a>

            @if(auth()->user()?->isSuperadmin() || auth()->user()?->isAdmin())
            <a href="{{ route('karyawan.index') }}"
               @click="sidebarOpen = false"
               class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl mb-0.5 {{ request()->routeIs('karyawan.*') ? 'active-glow' : 'inactive' }}"
               style="{{ request()->routeIs('karyawan.*') ? 'background: rgba(5,158,61,0.85); color: white;' : 'color: rgba(255,255,255,0.65);' }}">
                <i class="ph-fill ph-users nav-icon text-xl shrink-0"></i>
                <span class="text-sm font-medium">Petugas</span>
            </a>
            @endif

            @if(auth()->user()?->isSuperadmin() || auth()->user()?->isAdmin())
            <div class="my-3 mx-1" style="border-top: 1px solid rgba(255,255,255,0.07);"></div>
            <p class="px-3 pb-2 text-[9px] font-bold uppercase tracking-[0.15em]" style="color: rgba(255,255,255,0.35);">Sistem</p>

            <a href="{{ route('laporan.index') }}"
               @click="sidebarOpen = false"
               class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl mb-0.5 {{ request()->routeIs('laporan.*') ? 'active-glow' : 'inactive' }}"
               style="{{ request()->routeIs('laporan.*') ? 'background: rgba(5,158,61,0.85); color: white;' : 'color: rgba(255,255,255,0.65);' }}">
                <i class="ph-fill ph-file-text nav-icon text-xl shrink-0"></i>
                <span class="text-sm font-medium">Laporan</span>
            </a>
            @endif

            <!-- Pengaturan: HANYA SUPERADMIN -->
            @if(auth()->user()?->isSuperadmin())
            <div class="mb-0.5" x-data="{ expanded: {{ request()->routeIs('settings.*') ? 'true' : 'false' }} }">
                <button type="button"
                        @click="expanded = !expanded"
                        class="w-full nav-item flex items-center justify-between px-3 py-2.5 rounded-xl transition-all {{ request()->routeIs('settings.*') ? 'active-glow' : 'inactive' }}"
                        style="{{ request()->routeIs('settings.*') ? 'background: rgba(5,158,61,0.85); color: white;' : 'color: rgba(255,255,255,0.65);' }}">
                    <div class="flex items-center gap-3">
                        <i class="ph-fill ph-gear nav-icon text-xl shrink-0"></i>
                        <span class="text-sm font-medium">Pengaturan</span>
                    </div>
                    <i class="ph-bold ph-caret-down text-xs transition-transform duration-200"
                       :class="expanded ? 'rotate-180 text-white' : 'text-white/50'"></i>
                </button>

                <!-- Sub Menu Items Dropdown -->
                <div x-show="expanded"
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0 -translate-y-1"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-100"
                     x-transition:leave-start="opacity-100 translate-y-0"
                     x-transition:leave-end="opacity-0 -translate-y-1"
                     class="mt-1 ml-4 pl-3.5 space-y-0.5 relative py-1"
                     style="border-left: 1.5px solid rgba(255,255,255,0.18);">

                    <a href="{{ route('settings.index') }}"
                       @click="sidebarOpen = false"
                       class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-lg text-xs transition-all {{ request()->routeIs('settings.index') ? 'text-white font-bold bg-white/20 shadow-sm' : 'text-white/60 hover:text-white hover:bg-white/10' }}">
                        <i class="ph-bold ph-squares-four text-sm shrink-0"></i>
                        <span>Ringkasan</span>
                    </a>

                    <a href="{{ route('settings.users.index') }}"
                       @click="sidebarOpen = false"
                       class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-lg text-xs transition-all {{ request()->routeIs('settings.users.*') ? 'text-white font-bold bg-white/20 shadow-sm' : 'text-white/60 hover:text-white hover:bg-white/10' }}">
                        <i class="ph-bold ph-users text-sm shrink-0"></i>
                        <span>Pengguna</span>
                    </a>

                    <a href="{{ route('settings.roles.index') }}"
                       @click="sidebarOpen = false"
                       class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-lg text-xs transition-all {{ request()->routeIs('settings.roles.*') ? 'text-white font-bold bg-white/20 shadow-sm' : 'text-white/60 hover:text-white hover:bg-white/10' }}">
                        <i class="ph-bold ph-shield-check text-sm shrink-0"></i>
                        <span>Role & Akses</span>
                    </a>

                    <a href="{{ route('settings.sop.index') }}"
                       @click="sidebarOpen = false"
                       class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-lg text-xs transition-all {{ request()->routeIs('settings.sop.*') ? 'text-white font-bold bg-white/20 shadow-sm' : 'text-white/60 hover:text-white hover:bg-white/10' }}">
                        <i class="ph-bold ph-list-checks text-sm shrink-0"></i>
                        <span class="truncate">Master 27 SOP</span>
                        <span class="ml-auto text-[9px] px-1.5 py-0.2 rounded-full font-bold bg-white/20 text-white/90">27</span>
                    </a>

                    <a href="{{ route('settings.system.index') }}"
                       @click="sidebarOpen = false"
                       class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-lg text-xs transition-all {{ request()->routeIs('settings.system.*') ? 'text-white font-bold bg-white/20 shadow-sm' : 'text-white/60 hover:text-white hover:bg-white/10' }}">
                        <i class="ph-bold ph-sliders text-sm shrink-0"></i>
                        <span>Parameter Sistem</span>
                    </a>
                </div>
            </div>
            @endif

            <div class="pt-4 px-1">
                @if(auth()->user()?->canCreateShipment())
                <a href="{{ route('field-app.create') }}" target="_blank"
                   class="nav-item flex items-center gap-2.5 px-3 py-3 rounded-xl border transition-all duration-300"
                   style="border-color: rgba(99,195,132,0.3); background: rgba(13,89,80,0.4); color: #63c384;"
                   onmouseover="this.style.background='rgba(13,89,80,0.7)'; this.style.borderColor='rgba(99,195,132,0.6)'"
                   onmouseout="this.style.background='rgba(13,89,80,0.4)'; this.style.borderColor='rgba(99,195,132,0.3)'">
                    <i class="ph-bold ph-device-mobile-camera text-lg nav-icon shrink-0"></i>
                    <div>
                        <p class="text-xs font-bold leading-none">Field App</p>
                        <p class="text-[9px] mt-0.5" style="color: rgba(99,195,132,0.6);">Input Pengiriman</p>
                    </div>
                    <i class="ph-bold ph-arrow-square-out text-sm ml-auto opacity-60"></i>
                </a>
                @else
                <div class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl border opacity-50 cursor-not-allowed select-none"
                     style="border-color: rgba(255,255,255,0.1); background: rgba(255,255,255,0.04); color: rgba(255,255,255,0.4);"
                     title="Hanya akun Operator & Superadmin yang dapat membuat shipment">
                    <i class="ph-bold ph-device-mobile-camera text-lg shrink-0"></i>
                    <div>
                        <p class="text-xs font-bold leading-none">Field App</p>
                        <p class="text-[9px] mt-0.5 text-amber-300/70">Khusus Operator</p>
                    </div>
                    <i class="ph-bold ph-lock-key text-xs ml-auto text-amber-300/70"></i>
                </div>
                @endif
            </div>
        </nav>

        @php
            $authUser = auth()->user();
            $sidebarName = $authUser?->nama_warehouse ?? 'Pengguna';
            $sidebarRole = $authUser?->role_display_name ?? 'Operator';
            $sidebarUsername = $authUser?->username ?? 'user';
            $sidebarRoleBadge = match($authUser?->role) {
                'superadmin' => 'bg-emerald-400/20 text-emerald-300 border-emerald-400/30',
                'admin' => 'bg-blue-400/20 text-blue-300 border-blue-400/30',
                default => 'bg-amber-400/20 text-amber-300 border-amber-400/30',
            };
            $sidebarInitials = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $sidebarName) ?: 'SP', 0, 2));
        @endphp
        <div class="shrink-0 p-3" style="border-top: 1px solid rgba(255,255,255,0.07); background: rgba(0,0,0,0.15);">
            <div class="flex items-center gap-3 p-2 rounded-xl hover:bg-white/5 transition-all">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center text-xs font-bold shrink-0 shadow-sm"
                     style="background: linear-gradient(135deg, #e1f8eb, #63c384); color: #0d5950;">{{ $sidebarInitials }}</div>
                <div class="overflow-hidden flex-1 min-w-0">
                    <p class="text-xs font-bold text-white truncate leading-tight">{{ $sidebarName }}</p>
                    <div class="flex items-center gap-1.5 mt-1">
                        <span class="text-[9px] font-bold px-1.5 py-0.2 rounded border {{ $sidebarRoleBadge }}">{{ $sidebarRole }}</span>
                        <span class="text-[10px] text-white/40 truncate font-mono">{{ '@' . $sidebarUsername }}</span>
                    </div>
                </div>
            </div>
        </div>
    </aside>

    <!-- MAIN -->
    <div class="flex-1 flex flex-col h-screen overflow-hidden">

        <header class="h-[64px] sm:h-[68px] bg-white flex items-center justify-between px-3 sm:px-4 lg:px-7 shrink-0 gap-2 sm:gap-4"
                style="border-bottom: 1px solid #e8edf3; box-shadow: 0 1px 6px rgba(40,84,145,0.06);">
            <div class="flex items-center gap-2 sm:gap-3 min-w-0">
                <button type="button" @click="sidebarOpen = true" class="lg:hidden p-1.5 sm:p-2 rounded-xl text-gray-500 hover:text-spv-blue hover:bg-gray-100 transition-all shrink-0" aria-label="Buka Menu">
                    <i class="ph-bold ph-list text-xl sm:text-2xl"></i>
                </button>
                <div class="min-w-0">
                    <h1 class="text-xs sm:text-sm lg:text-base font-bold text-gray-800 leading-tight truncate">
                        {{ $title ?? (trim($__env->yieldContent('title')) ? $__env->yieldContent('title') : 'Dashboard') }}
                    </h1>
                    <p class="text-[10px] text-gray-400 hidden lg:block truncate">PT. South Pacific Viscose &mdash; Monitoring Staging</p>
                </div>
            </div>

            <!-- Server Time (WIB) -->
            <div class="flex items-center justify-center shrink-0">
                <x-server-clock />
            </div>

            <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                <div class="hidden md:flex items-center gap-2 pl-3 border-l border-gray-200">
                    <div class="text-right">
                        <p class="text-xs font-bold text-gray-800 leading-tight">{{ auth()->user()?->nama_warehouse }}</p>
                        <p class="text-[10px] text-gray-400 font-medium">{{ auth()->user()?->role_display_name }}</p>
                    </div>
                </div>
                <form action="{{ route('logout') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit"
                            onclick="return confirm('Apakah Anda yakin ingin logout dari sistem?')"
                            class="flex items-center gap-1.5 text-gray-600 hover:text-red-600 hover:bg-red-50 text-xs font-bold px-2.5 sm:px-3 py-1.5 sm:py-2 rounded-xl border border-gray-200 hover:border-red-200 transition-all cursor-pointer shadow-sm"
                            title="Keluar / Logout">
                        <i class="ph-bold ph-sign-out text-base text-red-500"></i>
                        <span class="hidden sm:inline">Keluar</span>
                    </button>
                </form>
            </div>
        </header>

        <main class="flex-1 overflow-x-hidden overflow-y-auto p-3 sm:p-4 lg:p-6 animate-fade-in">
            <!-- Toast / Flash Notifications -->
            @if(session('success'))
                <div x-data="{ show: true }" x-show="show" x-transition.duration.300ms class="mb-5 flex items-center justify-between p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-900 shadow-sm">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-emerald-600 text-white flex items-center justify-center shrink-0 shadow-sm">
                            <i class="ph-bold ph-check text-lg"></i>
                        </div>
                        <div>
                            <p class="text-xs font-bold leading-tight text-emerald-900">Berhasil</p>
                            <p class="text-xs text-emerald-700 mt-0.5">{{ session('success') }}</p>
                        </div>
                    </div>
                    <button type="button" @click="show = false" class="text-emerald-500 hover:text-emerald-800 p-1.5 rounded-lg transition-colors" aria-label="Tutup">
                        <i class="ph-bold ph-x text-base"></i>
                    </button>
                </div>
            @endif

            @if(session('error'))
                <div x-data="{ show: true }" x-show="show" x-transition.duration.300ms class="mb-5 flex items-center justify-between p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-900 shadow-sm">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-rose-600 text-white flex items-center justify-center shrink-0 shadow-sm">
                            <i class="ph-bold ph-warning-circle text-lg"></i>
                        </div>
                        <div>
                            <p class="text-xs font-bold leading-tight text-rose-900">Perhatian</p>
                            <p class="text-xs text-rose-700 mt-0.5">{{ session('error') }}</p>
                        </div>
                    </div>
                    <button type="button" @click="show = false" class="text-rose-500 hover:text-rose-800 p-1.5 rounded-lg transition-colors" aria-label="Tutup">
                        <i class="ph-bold ph-x text-base"></i>
                    </button>
                </div>
            @endif

            {{ $slot }}
        </main>

    </div>

</body>
</html>
