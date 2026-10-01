<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
    <meta name="theme-color" content="#285491">
    <title>SPV-Track Field App</title>
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
        ::-webkit-scrollbar { width: 3px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #c8c5c0; border-radius: 3px; }

        /* Input focus styles */
        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: #285491 !important;
            box-shadow: 0 0 0 3px rgba(40,84,145,0.1) !important;
        }

        /* Field label */
        .field-label {
            display: block;
            font-size: 11px;
            font-weight: 600;
            color: #6b7280;
            margin-bottom: 5px;
            letter-spacing: 0.02em;
        }

        /* Field input */
        .field-input {
            width: 100%;
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            padding: 9px 12px;
            font-size: 13px;
            font-family: -apple-system, BlinkMacSystemFont, 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #1f2937;
            transition: all 0.2s;
        }
        .field-input:hover { border-color: #285491; }

        /* Section card */
        .section-card {
            background: white;
            border-radius: 16px;
            border: 1px solid #e8edf3;
            box-shadow: 0 1px 4px rgba(40,84,145,0.05);
            overflow: hidden;
        }
        .section-card-header {
            padding: 12px 16px;
            display: flex;
            align-items: center;
            gap: 10px;
            border-bottom: 1px solid #f0f4f9;
            cursor: pointer;
            user-select: none;
        }
        .section-card-body { padding: 14px 16px; }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(6px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-in { animation: fadeIn 0.3s ease forwards; }
    </style>
    <!-- Phosphor Icons -->
    <script src="{{ asset('vendor/phosphor.min.js') }}"></script>
    <!-- Alpine Collapse plugin must load BEFORE Alpine -->
    <script defer src="{{ asset('vendor/alpine_collapse.min.js') }}"></script>
    <script defer src="{{ asset('vendor/alpine.min.js') }}"></script>
</head>
<body style="background: #f0f4f9; font-family: -apple-system, BlinkMacSystemFont, 'Helvetica Neue', Helvetica, Arial, sans-serif; min-height: 100vh; display: flex; flex-direction: column;">

    <!-- Header -->
    <header style="background: linear-gradient(135deg, #1a3c6e 0%, #285491 60%, #0d5950 100%); color: white; padding: 0 16px; height: 56px; display: flex; align-items: center; justify-content: space-between; position: sticky; top: 0; z-index: 100; box-shadow: 0 2px 12px rgba(40,84,145,0.3);">
        <div style="display: flex; align-items: center; gap: 12px;">
            <a href="javascript:history.back()" style="display:flex; align-items:center; justify-content:center; width:32px; height:32px; border-radius:10px; background:rgba(255,255,255,0.15); color:white; text-decoration:none; transition:background 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.25)'" onmouseout="this.style.background='rgba(255,255,255,0.15)'">
                <i class="ph-bold ph-arrow-left" style="font-size: 16px;"></i>
            </a>
            <div style="width: 32px; height: 32px; border-radius: 10px; background: linear-gradient(135deg, #059e3d, #63c384); display: flex; align-items: center; justify-content: center; box-shadow: 0 3px 10px rgba(5,158,61,0.4);">
                <i class="ph-fill ph-shipping-container" style="font-size: 17px; color: white;"></i>
            </div>
            <div>
                <p style="font-weight: 700; font-size: 14px; line-height: 1.1;">Field App</p>
                <p style="font-size: 9px; color: rgba(255,255,255,0.5); font-weight: 400;">SPV-Track Monitoring</p>
            </div>
        </div>
        <div style="width: 32px; height: 32px; border-radius: 50%; background: rgba(255,255,255,0.15); display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 700; cursor: pointer;">
            OP
        </div>
    </header>

    <!-- Main -->
    <main style="flex: 1; width: 100%; max-width: 720px; margin: 0 auto; padding: 16px 16px 80px; display: flex; flex-direction: column; gap: 12px;" class="animate-in">
        {{ $slot }}
    </main>

</body>
</html>
