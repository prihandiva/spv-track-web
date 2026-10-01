<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SPV-Track Dashboard</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Fallback Tailwind CSS V4 (Browser/CDN) due to Node.js v21 incompatibility -->
    <script src="https://unpkg.com/@tailwindcss/browser@4"></script>
    <style type="text/tailwindcss">
        @theme {
            --font-sans: 'Poppins', ui-sans-serif, system-ui, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol', 'Noto Color Emoji';

            --color-spv-blue: #285491;
            --color-spv-green: #059e3d;
            --color-spv-dark-teal: #0d5950;
            --color-spv-light-green: #e1f8eb;
            --color-spv-grey-1: #c8c5c0;
            --color-spv-grey-2: #d7d7d5;
            --color-spv-grey-3: #aeacad;
            --color-spv-soft-green: #63c384;
        }
    </style>
    <!-- Phosphor Icons (Modern & Corporate) -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <!-- Alpine JS for Mobile Menu -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.3/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-50 text-gray-800 font-sans antialiased flex h-screen overflow-hidden" x-data="{ sidebarOpen: false }">

    <!-- Mobile Sidebar Overlay -->
    <div x-show="sidebarOpen" x-transition.opacity class="fixed inset-0 bg-black/50 z-40 lg:hidden" @click="sidebarOpen = false"></div>

    <!-- Sidebar -->
    <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'" class="fixed lg:static inset-y-0 left-0 w-72 bg-spv-blue text-white flex flex-col shadow-2xl lg:shadow-xl transition-transform duration-300 z-50">
        <!-- Brand -->
        <div class="h-20 flex items-center justify-between px-6 border-b border-white/10">
            <div class="flex items-center">
                <div class="w-10 h-10 rounded-xl bg-spv-green flex items-center justify-center mr-3 shadow-lg shadow-spv-green/40">
                    <i class="ph-bold ph-shipping-container text-2xl text-white"></i>
                </div>
                <span class="text-xl font-bold tracking-wide">SPV-Track</span>
            </div>
            <!-- Close Button (Mobile) -->
            <button @click="sidebarOpen = false" class="lg:hidden text-white/70 hover:text-white p-2">
                <i class="ph-bold ph-x text-2xl"></i>
            </button>
        </div>

        <!-- Nav Links -->
        <nav class="flex-1 overflow-y-auto py-6 px-4 space-y-2">
            
            <a href="{{ route('dashboard') }}" class="flex items-center px-4 py-3.5 rounded-xl transition-all duration-300 {{ request()->routeIs('dashboard') ? 'bg-spv-green text-white shadow-lg shadow-spv-green/30 font-bold' : 'text-white/70 hover:bg-white/10 hover:text-white group' }}">
                <i class="ph-fill ph-squares-four text-2xl mr-3 {{ request()->routeIs('dashboard') ? 'text-white' : 'text-spv-light-green/70 group-hover:text-spv-light-green' }}"></i>
                <span>Dashboard</span>
            </a>

            <a href="{{ route('shipments.index') }}" class="flex items-center px-4 py-3.5 rounded-xl transition-all duration-300 {{ request()->routeIs('shipments.*') ? 'bg-spv-green text-white shadow-lg shadow-spv-green/30 font-bold' : 'text-white/70 hover:bg-white/10 hover:text-white group' }}">
                <i class="ph-fill ph-package text-2xl mr-3 {{ request()->routeIs('shipments.*') ? 'text-white' : 'text-spv-light-green/70 group-hover:text-spv-light-green' }}"></i>
                <span>Shipments</span>
            </a>

            <a href="{{ route('karyawan.index') }}" class="flex items-center px-4 py-3.5 rounded-xl transition-all duration-300 {{ request()->routeIs('karyawan.*') ? 'bg-spv-green text-white shadow-lg shadow-spv-green/30 font-bold' : 'text-white/70 hover:bg-white/10 hover:text-white group' }}">
                <i class="ph-fill ph-users text-2xl mr-3 {{ request()->routeIs('karyawan.*') ? 'text-white' : 'text-spv-light-green/70 group-hover:text-spv-light-green' }}"></i>
                <span>Petugas</span>
            </a>

            <a href="{{ route('laporan.index') }}" class="flex items-center px-4 py-3.5 rounded-xl transition-all duration-300 {{ request()->routeIs('laporan.*') ? 'bg-spv-green text-white shadow-lg shadow-spv-green/30 font-bold' : 'text-white/70 hover:bg-white/10 hover:text-white group' }}">
                <i class="ph-fill ph-file-text text-2xl mr-3 {{ request()->routeIs('laporan.*') ? 'text-white' : 'text-spv-light-green/70 group-hover:text-spv-light-green' }}"></i>
                <span>Laporan</span>
            </a>
            
            <a href="{{ route('settings.index') }}" class="flex items-center px-4 py-3.5 rounded-xl transition-all duration-300 {{ request()->routeIs('settings.*') ? 'bg-spv-green text-white shadow-lg shadow-spv-green/30 font-bold' : 'text-white/70 hover:bg-white/10 hover:text-white group' }}">
                <i class="ph-fill ph-gear text-2xl mr-3 {{ request()->routeIs('settings.*') ? 'text-white' : 'text-spv-light-green/70 group-hover:text-spv-light-green' }}"></i>
                <span>Pengaturan</span>
            </a>
            
            <!-- Quick Link to Field App (Simulator) -->
            <div class="pt-6 mt-6 border-t border-white/10">
                <a href="{{ route('field-app.create') }}" target="_blank" class="flex items-center justify-center space-x-2 px-4 py-3.5 rounded-xl bg-spv-dark-teal/50 hover:bg-spv-dark-teal border border-spv-light-green/20 text-spv-light-green transition-all duration-300 shadow-inner">
                    <i class="ph-bold ph-device-mobile text-xl"></i>
                    <span class="font-bold text-sm">Buka Field App</span>
                </a>
            </div>

        </nav>

        <!-- User Profile (Bottom) -->
        <div class="p-5 border-t border-white/10 bg-black/10">
            <div class="flex items-center">
                <div class="w-12 h-12 rounded-full bg-spv-light-green flex items-center justify-center text-spv-dark-teal font-bold text-lg shadow-inner shrink-0">
                    AD
                </div>
                <div class="ml-3 overflow-hidden">
                    <p class="text-sm font-bold text-white truncate">Admin Warehouse</p>
                    <p class="text-xs text-white/70 truncate mt-0.5">admin@spvtrack.com</p>
                </div>
            </div>
        </div>
    </aside>

    <!-- Main Content Wrapper -->
    <div class="flex-1 flex flex-col h-screen overflow-hidden bg-gray-50/80">
        <!-- Header -->
        <header class="h-20 bg-white shadow-sm flex items-center justify-between px-4 lg:px-8 z-10">
            <div class="flex items-center">
                <!-- Burger Button (Mobile) -->
                <button @click="sidebarOpen = true" class="lg:hidden p-2 mr-3 text-gray-500 hover:text-spv-blue hover:bg-blue-50 rounded-lg transition-colors">
                    <i class="ph-bold ph-list text-2xl"></i>
                </button>
                <h1 class="text-xl lg:text-2xl font-bold text-gray-800">
                    @yield('title', 'Dashboard')
                </h1>
            </div>
            
            <div class="flex items-center space-x-3 lg:space-x-5">
                <button class="p-2.5 rounded-full bg-gray-50 hover:bg-gray-100 text-gray-500 transition-colors relative border border-gray-100 shadow-sm">
                    <i class="ph-bold ph-bell text-xl"></i>
                    <span class="absolute top-1.5 right-1.5 w-2.5 h-2.5 bg-red-500 rounded-full border-2 border-white"></span>
                </button>
                <div class="hidden lg:block h-8 w-px bg-gray-200"></div>
                <button class="flex items-center space-x-2 text-gray-600 hover:text-red-600 font-semibold transition-colors px-3 py-2 rounded-lg hover:bg-red-50">
                    <span class="text-sm hidden lg:inline">Log out</span>
                    <i class="ph-bold ph-sign-out text-xl"></i>
                </button>
            </div>
        </header>

        <!-- Main Content Area -->
        <main class="flex-1 overflow-x-hidden overflow-y-auto p-4 lg:p-8">
            {{ $slot }}
        </main>
    </div>

</body>
</html>
