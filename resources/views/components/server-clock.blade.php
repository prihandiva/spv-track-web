@props([
    'variant' => 'light',
])

@php
    $nowWib = \Carbon\Carbon::now('Asia/Jakarta');
    $initialTimestampMs = $nowWib->getTimestampMs();
    $initialDateFormatted = $nowWib->locale('id')->isoFormat('dddd, DD MMMM YYYY');
    $initialShortDateFormatted = $nowWib->locale('id')->isoFormat('ddd, DD MMM YYYY');
    $initialTimeFormatted = $nowWib->format('H:i:s');
@endphp

@if($variant === 'dark')
    <!-- Dark / Gradient Header Variant -->
    <div x-data="serverClockComponent({{ $initialTimestampMs }})"
         class="inline-flex items-center gap-2 sm:gap-2.5 px-2.5 sm:px-3 py-1 sm:py-1.5 rounded-xl bg-white/10 hover:bg-white/15 border border-white/20 backdrop-blur-sm transition-all duration-200 text-white select-none shadow-xs"
         title="Waktu Server PT. South Pacific Viscose (WIB - Asia/Jakarta)">
        <div class="flex items-center gap-1.5 shrink-0">
            <span class="relative flex h-2 w-2" title="Server Aktif">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-400"></span>
            </span>
            <i class="ph-fill ph-clock text-emerald-300 text-sm"></i>
        </div>
        <div class="flex flex-col sm:flex-row sm:items-center gap-0.5 sm:gap-2 leading-tight">
            <span class="font-medium text-white/90 text-[10px] sm:text-xs whitespace-nowrap" x-text="dateStr">
                {{ $initialDateFormatted }}
            </span>
            <span class="hidden sm:inline text-white/30">•</span>
            <div class="flex items-center gap-1.5 whitespace-nowrap">
                <span class="font-mono font-bold text-white text-xs sm:text-[13px] tracking-tight tabular-nums" x-text="timeStr">
                    {{ $initialTimeFormatted }}
                </span>
                <span class="text-[9px] font-bold px-1.5 py-0.5 rounded bg-emerald-500/30 text-emerald-200 border border-emerald-400/40 tracking-wider leading-none">
                    WIB
                </span>
            </div>
        </div>
    </div>
@else
    <!-- Light / White Header Variant (Default) -->
    <div x-data="serverClockComponent({{ $initialTimestampMs }})"
         class="inline-flex items-center gap-2 sm:gap-3 px-2.5 sm:px-3.5 py-1 sm:py-1.5 rounded-xl bg-gradient-to-r from-slate-50 via-white to-blue-50/30 border border-slate-200/90 shadow-[0_1px_3px_rgba(40,84,145,0.06)] hover:border-spv-blue/40 transition-all duration-200 select-none group"
         title="Waktu Server PT. South Pacific Viscose (Waktu Indonesia Barat - Asia/Jakarta)">
        <!-- Live Server Pulse & Clock Icon -->
        <div class="flex items-center gap-1.5 shrink-0">
            <span class="relative flex h-2 w-2" title="Server Aktif Terhubung">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
            </span>
            <i class="ph-fill ph-clock text-spv-blue text-sm sm:text-base transition-transform group-hover:rotate-12 duration-300"></i>
        </div>

        <!-- Date & Running Clock -->
        <div class="flex flex-col sm:flex-row sm:items-center gap-0.5 sm:gap-2 leading-tight">
            <!-- Hari, Tanggal Bulan Tahun -->
            <span class="text-[10px] sm:text-xs font-semibold text-gray-700 tracking-tight whitespace-nowrap"
                  x-text="dateStr">
                {{ $initialDateFormatted }}
            </span>

            <span class="hidden sm:inline-block text-gray-300 font-light text-xs">•</span>

            <!-- Jam : Menit : Detik -->
            <div class="flex items-center gap-1.5 whitespace-nowrap">
                <span class="font-mono font-bold text-xs sm:text-[13px] text-spv-blue tracking-tight tabular-nums"
                      x-text="timeStr">
                    {{ $initialTimeFormatted }}
                </span>
                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-bold bg-emerald-100/80 text-emerald-800 border border-emerald-300/70 leading-none tracking-wider shadow-2xs">
                    WIB
                </span>
            </div>
        </div>
    </div>
@endif

@once
<script>
(function() {
    function registerServerClock() {
        if (!window.Alpine) return;
        if (Alpine.data('serverClockComponent')) return;

        Alpine.data('serverClockComponent', (initialEpochMs) => ({
            serverEpoch: Number(initialEpochMs),
            clientStart: Date.now(),
            dateStr: @json($initialDateFormatted),
            shortDateStr: @json($initialShortDateFormatted),
            timeStr: @json($initialTimeFormatted),
            days: ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'],
            shortDays: ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'],
            months: [
                'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
            ],
            shortMonths: [
                'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun',
                'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'
            ],
            intervalId: null,

            init() {
                this.tick();

                // Synchronize ticking precisely with the start of the next wall second
                const delay = 1000 - (Date.now() % 1000);
                setTimeout(() => {
                    this.tick();
                    this.intervalId = setInterval(() => this.tick(), 1000);
                }, delay);

                // Auto resynchronize with server if browser tab is backgrounded / woken from sleep
                document.addEventListener('visibilitychange', () => {
                    if (document.visibilityState === 'visible') {
                        this.syncWithServer();
                    }
                });

                // Periodic server re-sync every 5 minutes
                setInterval(() => this.syncWithServer(), 300000);
            },

            tick() {
                const elapsed = Date.now() - this.clientStart;
                const currentMs = this.serverEpoch + elapsed;

                // Asia/Jakarta (WIB) is UTC + 7 hours (25,200,000 ms)
                const wibTime = new Date(currentMs + 25200000);

                const dayName = this.days[wibTime.getUTCDay()];
                const shortDayName = this.shortDays[wibTime.getUTCDay()];
                const dayNum = String(wibTime.getUTCDate()).padStart(2, '0');
                const monthName = this.months[wibTime.getUTCMonth()];
                const shortMonthName = this.shortMonths[wibTime.getUTCMonth()];
                const year = wibTime.getUTCFullYear();

                const hours = String(wibTime.getUTCHours()).padStart(2, '0');
                const minutes = String(wibTime.getUTCMinutes()).padStart(2, '0');
                const seconds = String(wibTime.getUTCSeconds()).padStart(2, '0');

                this.dateStr = `${dayName}, ${dayNum} ${monthName} ${year}`;
                this.shortDateStr = `${shortDayName}, ${dayNum} ${shortMonthName} ${year}`;
                this.timeStr = `${hours}:${minutes}:${seconds}`;
            },

            async syncWithServer() {
                try {
                    const response = await fetch('{{ route('api.server-time') }}', {
                        headers: { 'Accept': 'application/json' },
                        cache: 'no-store'
                    });
                    if (response.ok) {
                        const data = await response.json();
                        if (data && data.timestamp) {
                            this.serverEpoch = Number(data.timestamp);
                            this.clientStart = Date.now();
                            this.tick();
                        }
                    }
                } catch (e) {
                    // Gracefully continue with client elapsed counter if offline
                }
            }
        }));
    }

    if (window.Alpine) {
        registerServerClock();
    } else {
        document.addEventListener('alpine:init', registerServerClock);
    }
})();
</script>
@endonce
