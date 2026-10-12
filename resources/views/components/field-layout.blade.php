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
        ::-webkit-scrollbar { width: 3px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #c8c5c0; border-radius: 3px; }

        /* Flatpickr 24-Hour Custom Modern Theme */
        .flatpickr-calendar {
            background: #ffffff !important;
            border-radius: 16px !important;
            box-shadow: 0 16px 36px -4px rgba(40, 84, 145, 0.22), 0 0 0 1px rgba(40, 84, 145, 0.1) !important;
            border: none !important;
            font-family: inherit !important;
            overflow: hidden;
            width: 320px !important;
        }
        .flatpickr-months {
            background: linear-gradient(135deg, #1a3c6e, #285491) !important;
            color: #ffffff !important;
            padding: 8px 6px !important;
            border-top-left-radius: 16px;
            border-top-right-radius: 16px;
        }
        .flatpickr-months .flatpickr-month {
            color: #ffffff !important;
            fill: #ffffff !important;
            height: 36px !important;
        }
        .flatpickr-current-month {
            font-size: 110% !important;
            font-weight: 700 !important;
            color: #ffffff !important;
            padding-top: 4px !important;
        }
        .flatpickr-current-month .cur-month {
            font-weight: 700 !important;
            color: #ffffff !important;
        }
        .flatpickr-current-month input.cur-year {
            font-weight: 700 !important;
            color: #ffffff !important;
        }
        .flatpickr-months .flatpickr-prev-month, 
        .flatpickr-months .flatpickr-next-month {
            color: #ffffff !important;
            fill: #ffffff !important;
            padding: 6px 10px !important;
            border-radius: 8px;
            transition: all 0.2s;
        }
        .flatpickr-months .flatpickr-prev-month:hover, 
        .flatpickr-months .flatpickr-next-month:hover {
            background: rgba(255, 255, 255, 0.2) !important;
        }
        .flatpickr-weekdays {
            background: #f1f5f9 !important;
            border-bottom: 1px solid #e2e8f0;
            padding: 4px 0 !important;
        }
        span.flatpickr-weekday {
            color: #475569 !important;
            font-weight: 700 !important;
            font-size: 11px !important;
        }
        .flatpickr-day {
            border-radius: 10px !important;
            font-size: 13px !important;
            font-weight: 500 !important;
            color: #1e293b !important;
            height: 36px !important;
            line-height: 36px !important;
            transition: all 0.15s;
        }
        .flatpickr-day:hover {
            background: #eff4fc !important;
            color: #285491 !important;
        }
        .flatpickr-day.today {
            border-color: #285491 !important;
            color: #285491 !important;
            font-weight: 700 !important;
        }
        .flatpickr-day.selected, 
        .flatpickr-day.selected:hover {
            background: linear-gradient(135deg, #285491, #0d5950) !important;
            border-color: transparent !important;
            color: #ffffff !important;
            font-weight: 700 !important;
            box-shadow: 0 4px 10px rgba(40, 84, 145, 0.35);
        }
        /* Time picker 24-hr styling */
        .flatpickr-calendar.hasTime .flatpickr-time {
            background: #f8fafc !important;
            border-top: 1.5px solid #e2e8f0 !important;
            padding: 6px 12px !important;
            height: 52px !important;
            max-height: 52px !important;
            line-height: 40px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
        }
        .flatpickr-time .numInputWrapper {
            flex: 0 0 68px !important;
            max-width: 68px !important;
            height: 38px !important;
        }
        .flatpickr-time input.flatpickr-hour,
        .flatpickr-time input.flatpickr-minute {
            font-size: 19px !important;
            font-weight: 800 !important;
            color: #1e293b !important;
            background: #ffffff !important;
            border: 1.5px solid #cbd5e1 !important;
            border-radius: 8px !important;
            height: 38px !important;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            text-align: center;
        }
        .flatpickr-time input.flatpickr-hour:focus,
        .flatpickr-time input.flatpickr-minute:focus {
            border-color: #285491 !important;
            background: #ffffff !important;
        }
        .flatpickr-time-separator {
            font-size: 22px !important;
            font-weight: 800 !important;
            color: #475569 !important;
            margin: 0 8px !important;
        }
        .flatpickr-time .arrowUp, .flatpickr-time .arrowDown {
            padding: 0 4px !important;
        }
        .flatpickr-custom-input {
            cursor: pointer !important;
            background-color: #ffffff !important;
        }

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
            position: relative;
        }
        .section-card-header {
            padding: 12px 16px;
            display: flex;
            align-items: center;
            gap: 10px;
            border-bottom: 1px solid #f0f4f9;
            border-top-left-radius: 15px;
            border-top-right-radius: 15px;
            cursor: pointer;
            user-select: none;
        }
        .section-card-body { padding: 14px 16px; }

        @media (max-width: 640px) {
            .field-app-main {
                padding: 10px 8px 90px !important;
            }
            .section-card-body {
                padding: 14px 12px !important;
            }
        }

        body, input, button, select, textarea, span, p, label {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        }

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
        <div style="display: flex; align-items: center; gap: 8px;">
            <x-server-clock variant="dark" />
            <div style="width: 32px; height: 32px; border-radius: 50%; background: rgba(255,255,255,0.15); display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 700; cursor: pointer;">
                OP
            </div>
        </div>
    </header>

    <!-- Main -->
    <main style="flex: 1; width: 100%; max-width: 860px; margin: 0 auto; padding: 14px 16px 80px; display: flex; flex-direction: column; gap: 14px;" class="field-app-main animate-in">
        {{ $slot }}
    </main>

</body>
</html>
