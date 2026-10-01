<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
    <title>SPV-Track Field App</title>
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
</head>
<body class="bg-gray-50 text-gray-800 font-sans antialiased min-h-screen flex flex-col">

    <!-- Mobile Header -->
    <header class="h-16 bg-spv-blue text-white shadow-md flex items-center justify-between px-4 sticky top-0 z-50">
        <div class="flex items-center">
            <i class="ph-fill ph-shipping-container text-2xl text-spv-light-green mr-2"></i>
            <span class="text-lg font-bold tracking-wide">Field App</span>
        </div>
        <div class="flex items-center space-x-3">
            <div class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center text-sm font-bold">
                OP
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-1 w-full max-w-md mx-auto p-4 pb-24">
        {{ $slot }}
    </main>

</body>
</html>
