<x-layout :title="'Dashboard'">

    <div x-data="dashboardAnalytics()" x-init="initCharts()" class="space-y-6">

        <!-- 1. BANNER UCAPAN SELAMAT DATANG (WELCOME BANNER) -->
        <div class="relative overflow-hidden rounded-3xl p-6 lg:p-8 text-white shadow-xl"
             style="background: linear-gradient(135deg, #132b4f 0%, #1a3c6e 40%, #285491 70%, #0d5950 100%);">
            
            <!-- Subtle background glowing effects & watermark -->
            <div class="absolute -right-12 -top-12 w-64 h-64 rounded-full bg-emerald-400/10 blur-3xl pointer-events-none"></div>
            <div class="absolute right-1/4 -bottom-16 w-80 h-80 rounded-full bg-blue-400/10 blur-3xl pointer-events-none"></div>
            <div class="absolute right-6 bottom-4 opacity-5 pointer-events-none hidden lg:block">
                <i class="ph-fill ph-shipping-container text-9xl"></i>
            </div>

            <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                <!-- User Greeting & Info -->
                <div class="max-w-3xl">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold bg-white/10 backdrop-blur-md border border-white/15 mb-3 text-emerald-300">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        <span>Sistem Monitoring Staging &bull; Live Online</span>
                    </div>

                    <h1 class="text-2xl lg:text-3xl font-extrabold tracking-tight leading-tight">
                        Selamat Datang, 
                        <span class="text-white underline decoration-emerald-400 decoration-2 underline-offset-4">
                            {{ $currentUser->nama_warehouse ?? 'Admin Warehouse' }}
                        </span>!
                    </h1>

                    <div class="flex flex-wrap items-center gap-2.5 mt-2">
                        <span class="inline-flex items-center gap-1.5 px-3 py-0.5 rounded-full text-xs font-bold uppercase tracking-wider bg-emerald-500/25 text-emerald-200 border border-emerald-400/30 backdrop-blur-sm">
                            <i class="ph-bold ph-shield-check text-sm"></i>
                            Role: {{ ucfirst($currentUser->role ?? 'Supervisor') }}
                        </span>
                        <span class="text-xs text-blue-100/70 hidden sm:inline">&bull;</span>
                        <span class="text-xs text-blue-100/90 font-medium flex items-center gap-1">
                            <i class="ph-fill ph-building-office text-emerald-300"></i> PT. South Pacific Viscose
                        </span>
                    </div>

                    <p class="text-xs lg:text-sm mt-3 leading-relaxed text-blue-100/80 max-w-2xl">
                        Selamat datang pada sistem <strong class="text-white">SPV-Track</strong>. Pantau capaian KPI staging logistik, kepatuhan 27 titik foto SOP, serta status armada kontainer & truk harian, bulanan, kuartalan, hingga tahunan.
                    </p>

                    <!-- Quick Action Buttons -->
                    <div class="flex flex-wrap items-center gap-3 mt-5">
                        <a href="{{ route('field-app.create') }}" target="_blank"
                           class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold bg-emerald-500 hover:bg-emerald-600 text-white shadow-lg shadow-emerald-900/30 transition-all transform hover:-translate-y-0.5">
                            <i class="ph-bold ph-device-mobile-camera text-base"></i>
                            <span>Buka Field App Mobile</span>
                            <i class="ph-bold ph-arrow-up-right text-xs opacity-75"></i>
                        </a>

                        <a href="{{ route('shipments.index') }}"
                           class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-semibold bg-white/10 hover:bg-white/20 text-white border border-white/20 backdrop-blur-sm transition-all transform hover:-translate-y-0.5">
                            <i class="ph-bold ph-package text-base"></i>
                            <span>Kelola Semua Shipment</span>
                        </a>

                        <a href="{{ route('shipments.batch-zip-preview') }}"
                           class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-semibold bg-white/10 hover:bg-white/20 text-white border border-white/20 backdrop-blur-sm transition-all transform hover:-translate-y-0.5">
                            <i class="ph-bold ph-file-zip text-base"></i>
                            <span>Unduh Batch ZIP Foto</span>
                        </a>
                    </div>
                </div>

                <!-- Today Summary Pill / Widget on Banner -->
                <div class="shrink-0 bg-white/10 backdrop-blur-md rounded-2xl p-4 lg:p-5 border border-white/15 min-w-[240px] shadow-lg">
                    <p class="text-[11px] font-semibold text-emerald-200/90 uppercase tracking-wider mb-2 flex items-center justify-between">
                        <span>Aktivitas Hari Ini</span>
                        <i class="ph-fill ph-calendar-check text-emerald-300 text-sm"></i>
                    </p>
                    <div class="flex items-baseline gap-2">
                        <span class="text-3xl font-extrabold text-white">{{ $todaySubmitted }}</span>
                        <span class="text-xs text-blue-100/75 font-medium">/ {{ $todayShipments }} Selesai Valid</span>
                    </div>
                    <div class="mt-2.5">
                        <div class="flex items-center justify-between text-[10px] text-blue-100/80 mb-1">
                            <span>Tingkat Sukses Total</span>
                            <span class="font-bold text-emerald-300">{{ $successRate }}%</span>
                        </div>
                        <div class="w-full bg-white/20 h-2 rounded-full overflow-hidden">
                            <div class="h-full bg-emerald-400 rounded-full transition-all duration-500" style="width: {{ min(100, $successRate) }}%;"></div>
                        </div>
                    </div>
                    <p class="text-[10px] text-blue-100/60 mt-2 text-right">
                        {{ \Carbon\Carbon::now('Asia/Jakarta')->translatedFormat('l, d M Y') }}
                    </p>
                </div>
            </div>
        </div>

        <!-- 2. QUICK STATS KPI CARDS -->
        <div class="grid grid-cols-2 lg:grid-cols-5 gap-3.5 lg:gap-4">
            
            <!-- Card 1: Total Shipment -->
            <div class="bg-white rounded-2xl p-4 lg:p-5 transition-all duration-300 hover:-translate-y-0.5"
                 style="border: 1px solid #e8edf3; box-shadow: 0 2px 10px rgba(40,84,145,0.04);">
                <div class="flex items-start justify-between mb-3">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0" style="background:#eff4fc;">
                        <i class="ph-fill ph-package text-xl" style="color:#285491;"></i>
                    </div>
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full flex items-center gap-1" style="background:#eff4fc; color:#285491;">
                        <i class="ph-bold ph-activity text-xs"></i>Total
                    </span>
                </div>
                <p class="text-[11px] font-medium text-gray-400 mb-0.5">Total Pengiriman</p>
                <div class="flex items-baseline gap-1.5">
                    <p class="text-2xl font-extrabold text-gray-800">{{ $totalShipments }}</p>
                    <span class="text-[11px] text-gray-400">staging</span>
                </div>
            </div>

            <!-- Card 2: Berhasil (Submitted) -->
            <div class="bg-white rounded-2xl p-4 lg:p-5 transition-all duration-300 hover:-translate-y-0.5"
                 style="border: 1px solid #e8edf3; box-shadow: 0 2px 10px rgba(40,84,145,0.04);">
                <div class="flex items-start justify-between mb-3">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0" style="background:#e1f8eb;">
                        <i class="ph-fill ph-check-circle text-xl" style="color:#059e3d;"></i>
                    </div>
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full flex items-center gap-1" style="background:#e1f8eb; color:#059e3d;">
                        <i class="ph-bold ph-shield-check text-xs"></i>Valid
                    </span>
                </div>
                <p class="text-[11px] font-medium text-gray-400 mb-0.5">Berhasil (Submitted)</p>
                <div class="flex items-baseline gap-1.5">
                    <p class="text-2xl font-extrabold text-emerald-600">{{ $totalSubmitted }}</p>
                    <span class="text-[11px] text-emerald-600 font-semibold">100% SOP</span>
                </div>
            </div>

            <!-- Card 3: Sedang Berjalan (Draft) -->
            <div class="bg-white rounded-2xl p-4 lg:p-5 transition-all duration-300 hover:-translate-y-0.5"
                 style="border: 1px solid #e8edf3; box-shadow: 0 2px 10px rgba(40,84,145,0.04);">
                <div class="flex items-start justify-between mb-3">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0" style="background:#fffbeb;">
                        <i class="ph-fill ph-clock-countdown text-xl" style="color:#d97706;"></i>
                    </div>
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full" style="background:#fffbeb; color:#d97706;">
                        Draft
                    </span>
                </div>
                <p class="text-[11px] font-medium text-gray-400 mb-0.5">Sedang Berjalan</p>
                <div class="flex items-baseline gap-1.5">
                    <p class="text-2xl font-extrabold text-amber-600">{{ $totalDraft }}</p>
                    <span class="text-[11px] text-gray-400">belum submit</span>
                </div>
            </div>

            <!-- Card 4: Capaian KPI (%) -->
            <div class="bg-white rounded-2xl p-4 lg:p-5 transition-all duration-300 hover:-translate-y-0.5"
                 style="border: 1px solid #e8edf3; box-shadow: 0 2px 10px rgba(40,84,145,0.04);">
                <div class="flex items-start justify-between mb-3">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0" style="background:#e8f5f3;">
                        <i class="ph-fill ph-chart-line-up text-xl" style="color:#0d5950;"></i>
                    </div>
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full" style="background:#e8f5f3; color:#0d5950;">
                        Target 100%
                    </span>
                </div>
                <p class="text-[11px] font-medium text-gray-400 mb-0.5">Capaian KPI Sukses</p>
                <div class="flex items-baseline gap-1.5">
                    <p class="text-2xl font-extrabold {{ $successRate >= 80 ? 'text-emerald-600' : ($successRate >= 50 ? 'text-blue-600' : 'text-amber-600') }}">
                        {{ $successRate }}%
                    </p>
                    <span class="text-[10px] text-gray-400">rasio submitted</span>
                </div>
            </div>

            <!-- Card 5: Petugas Aktif -->
            <a href="{{ route('karyawan.index') }}"
               class="col-span-2 lg:col-span-1 bg-white rounded-2xl p-4 lg:p-5 transition-all duration-300 hover:-translate-y-0.5 block group"
               style="border: 1px solid #e8edf3; box-shadow: 0 2px 10px rgba(40,84,145,0.04);"
               onmouseover="this.style.borderColor='#c5d4ea'"
               onmouseout="this.style.borderColor='#e8edf3'">
                <div class="flex items-start justify-between mb-3">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 transition-transform duration-300 group-hover:scale-110" style="background:#f3e8ff;">
                        <i class="ph-fill ph-users text-xl" style="color:#7e22ce;"></i>
                    </div>
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full" style="background:#f3e8ff; color:#7e22ce;">
                        Aktif
                    </span>
                </div>
                <p class="text-[11px] font-medium text-gray-400 mb-0.5">Petugas Staging</p>
                <div class="flex items-baseline justify-between">
                    <p class="text-2xl font-extrabold text-gray-800">{{ $totalPetugas }}</p>
                    <span class="text-xs font-semibold text-spv-blue group-hover:underline flex items-center gap-0.5">
                        Kelola <i class="ph-bold ph-caret-right text-xs"></i>
                    </span>
                </div>
            </a>

        </div>

        <!-- 3. CARD BANTUAN PENGGUNAAN SISTEM & PENJELASAN STATUS -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 lg:gap-5">
            
            <!-- Card Bantuan Penggunaan Sistem -->
            <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-[0_2px_12px_rgba(40,84,145,0.04)] relative overflow-hidden">
                <div class="flex items-center justify-between pb-3.5 mb-3.5 border-b border-gray-100">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center text-white" style="background: linear-gradient(135deg, #285491, #0d5950);">
                            <i class="ph-bold ph-question text-base"></i>
                        </div>
                        <div>
                            <h2 class="text-sm font-bold text-gray-800 leading-tight">Bantuan Alur Penggunaan Sistem</h2>
                            <p class="text-[10px] text-gray-400">4 langkah standar operasional staging SPV-Track</p>
                        </div>
                    </div>
                    <span class="text-[10px] font-bold px-2.5 py-1 rounded-full bg-blue-50 text-spv-blue border border-blue-100">
                        SOP Guide
                    </span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <!-- Step 1 -->
                    <div class="flex items-start gap-2.5 p-2.5 rounded-xl bg-slate-50/70 border border-slate-100">
                        <span class="w-6 h-6 rounded-lg bg-spv-blue text-white text-[11px] font-bold flex items-center justify-center shrink-0">1</span>
                        <div>
                            <p class="text-xs font-bold text-gray-800 leading-snug">Inisiasi & OCR Surat Jalan</p>
                            <p class="text-[10px] text-gray-500 mt-0.5 leading-relaxed">
                                Buka Field App, foto dokumen DO / Surat Jalan. Data kontainer & plat akan terisi otomatis.
                            </p>
                        </div>
                    </div>

                    <!-- Step 2 -->
                    <div class="flex items-start gap-2.5 p-2.5 rounded-xl bg-slate-50/70 border border-slate-100">
                        <span class="w-6 h-6 rounded-lg bg-emerald-600 text-white text-[11px] font-bold flex items-center justify-center shrink-0">2</span>
                        <div>
                            <p class="text-xs font-bold text-gray-800 leading-snug">27 Foto Bukti SOP</p>
                            <p class="text-[10px] text-gray-500 mt-0.5 leading-relaxed">
                                Operator mengambil 27 foto staging secara berurutan dengan stempel waktu otomatis (WIB).
                            </p>
                        </div>
                    </div>

                    <!-- Step 3 -->
                    <div class="flex items-start gap-2.5 p-2.5 rounded-xl bg-slate-50/70 border border-slate-100">
                        <span class="w-6 h-6 rounded-lg bg-teal-600 text-white text-[11px] font-bold flex items-center justify-center shrink-0">3</span>
                        <div>
                            <p class="text-xs font-bold text-gray-800 leading-snug">Validasi & Submit Final</p>
                            <p class="text-[10px] text-gray-500 mt-0.5 leading-relaxed">
                                Verifikasi kelengkapan seluruh foto. Klik submit untuk mengunci data sebelum armada berangkat.
                            </p>
                        </div>
                    </div>

                    <!-- Step 4 -->
                    <div class="flex items-start gap-2.5 p-2.5 rounded-xl bg-slate-50/70 border border-slate-100">
                        <span class="w-6 h-6 rounded-lg bg-purple-600 text-white text-[11px] font-bold flex items-center justify-center shrink-0">4</span>
                        <div>
                            <p class="text-xs font-bold text-gray-800 leading-snug">Monitoring & Laporan</p>
                            <p class="text-[10px] text-gray-500 mt-0.5 leading-relaxed">
                                Supervisor memantau KPI di dashboard ini, cetak PDF resmi, atau download Batch ZIP bukti foto.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="mt-3 pt-2.5 border-t border-gray-100 flex items-center justify-between">
                    <span class="text-[10px] text-gray-400">Butuh bantuan di lapangan? Hubungi PIC Supervisor Gudang</span>
                    <a href="{{ route('field-app.create') }}" target="_blank" class="text-xs font-bold text-spv-blue hover:text-spv-green flex items-center gap-1">
                        Buka Field App <i class="ph-bold ph-arrow-square-out text-xs"></i>
                    </a>
                </div>
            </div>

            <!-- Card Penjelasan Status & Kategori -->
            <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-[0_2px_12px_rgba(40,84,145,0.04)] relative overflow-hidden">
                <div class="flex items-center justify-between pb-3.5 mb-3.5 border-b border-gray-100">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center text-white" style="background: linear-gradient(135deg, #059e3d, #63c384);">
                            <i class="ph-bold ph-bookmarks text-base"></i>
                        </div>
                        <div>
                            <h2 class="text-sm font-bold text-gray-800 leading-tight">Penjelasan Status & Kategori</h2>
                            <p class="text-[10px] text-gray-400">Arti indikator status staging dan jenis pengiriman</p>
                        </div>
                    </div>
                    <span class="text-[10px] font-bold px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-100">
                        Kamus Status
                    </span>
                </div>

                <div class="space-y-2.5">
                    <!-- Status Draft -->
                    <div class="flex items-start gap-3 p-2 rounded-xl hover:bg-slate-50/70 transition-colors">
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200 shrink-0 mt-0.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Draft
                        </span>
                        <div>
                            <p class="text-xs font-bold text-gray-700">Dalam Pengerjaan Staging (Sedang Berjalan)</p>
                            <p class="text-[10px] text-gray-500 leading-relaxed">
                                Petugas sedang mengambil foto SOP 27 titik secara bertahap. Data masih dapat diubah dan belum disubmit ke server pusat.
                            </p>
                        </div>
                    </div>

                    <!-- Status Submitted -->
                    <div class="flex items-start gap-3 p-2 rounded-xl hover:bg-slate-50/70 transition-colors">
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-green-50 text-green-700 border border-green-200 shrink-0 mt-0.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-green-500 animate-pulse"></span> Submitted
                        </span>
                        <div>
                            <p class="text-xs font-bold text-gray-700">Berhasil & Selesai Valid (Terkunci)</p>
                            <p class="text-[10px] text-gray-500 leading-relaxed">
                                Seluruh 27 titik foto SOP, surat jalan, dan detail kontainer telah lengkap tervalidasi. Siap cetak laporan dan rilis armada.
                            </p>
                        </div>
                    </div>

                    <!-- Kategori Armada -->
                    <div class="pt-2 border-t border-gray-100 grid grid-cols-2 gap-2">
                        <div class="flex items-center gap-2 p-1.5 rounded-lg bg-gray-50/60 text-[11px]">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-purple-50 text-purple-700">Export</span>
                            <span class="text-gray-600 text-[10px]">Kontainer laut / pelabuhan</span>
                        </div>
                        <div class="flex items-center gap-2 p-1.5 rounded-lg bg-gray-50/60 text-[11px]">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-gray-200 text-gray-700">Lokal</span>
                            <span class="text-gray-600 text-[10px]">Armada truk distribusi darat</span>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- 4. CHART KPI CAPAIAN MULTI-PERIODE (HERO KPI CHART) -->
        <div class="bg-white rounded-3xl p-5 lg:p-6 border border-gray-100 shadow-[0_4px_20px_rgba(40,84,145,0.06)]">
            
            <!-- Chart Header & Interactive Controls -->
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 pb-5 border-b border-gray-100">
                <div>
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-xl bg-blue-50 text-spv-blue flex items-center justify-center font-bold">
                            <i class="ph-fill ph-chart-bar text-lg"></i>
                        </div>
                        <div>
                            <h2 class="text-base font-extrabold text-gray-800">
                                KPI Capaian Pengiriman Berhasil
                            </h2>
                            <p class="text-xs text-gray-400 mt-0.5">
                                Pantau seberapa banyak shipment yang telah berhasil diselesaikan (<strong class="text-emerald-600">Submitted</strong>) vs total pengerjaan
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Period Switcher Tabs & View Mode -->
                <div class="flex flex-wrap items-center gap-2">
                    
                    <!-- Period Tabs Buttons -->
                    <div class="inline-flex p-1 rounded-xl bg-gray-100 text-xs font-semibold">
                        <button type="button"
                                @click="switchPeriod('harian')"
                                :class="activePeriod === 'harian' ? 'bg-white text-spv-blue shadow-sm' : 'text-gray-500 hover:text-gray-800'"
                                class="px-3 py-1.5 rounded-lg transition-all flex items-center gap-1.5 font-bold">
                            <i class="ph-bold ph-calendar"></i>
                            <span>Harian</span>
                        </button>
                        <button type="button"
                                @click="switchPeriod('bulanan')"
                                :class="activePeriod === 'bulanan' ? 'bg-white text-spv-blue shadow-sm' : 'text-gray-500 hover:text-gray-800'"
                                class="px-3 py-1.5 rounded-lg transition-all flex items-center gap-1.5 font-bold">
                            <i class="ph-bold ph-calendar-blank"></i>
                            <span>Bulanan</span>
                        </button>
                        <button type="button"
                                @click="switchPeriod('kuartalan')"
                                :class="activePeriod === 'kuartalan' ? 'bg-white text-spv-blue shadow-sm' : 'text-gray-500 hover:text-gray-800'"
                                class="px-3 py-1.5 rounded-lg transition-all flex items-center gap-1.5 font-bold">
                            <i class="ph-bold ph-chart-pie-slice"></i>
                            <span>Kuartal</span>
                        </button>
                        <button type="button"
                                @click="switchPeriod('tahunan')"
                                :class="activePeriod === 'tahunan' ? 'bg-white text-spv-blue shadow-sm' : 'text-gray-500 hover:text-gray-800'"
                                class="px-3 py-1.5 rounded-lg transition-all flex items-center gap-1.5 font-bold">
                            <i class="ph-bold ph-trend-up"></i>
                            <span>Tahunan</span>
                        </button>
                    </div>

                    <!-- Chart Type Switcher: Bar vs Line -->
                    <div class="inline-flex p-1 rounded-xl bg-gray-100 text-xs font-semibold">
                        <button type="button"
                                @click="toggleChartType('bar')"
                                :class="chartType === 'bar' ? 'bg-white text-spv-blue shadow-sm' : 'text-gray-400 hover:text-gray-800'"
                                title="Tampilan Grafik Batang (Bar)"
                                class="p-1.5 rounded-lg transition-all">
                            <i class="ph-bold ph-chart-bar text-base"></i>
                        </button>
                        <button type="button"
                                @click="toggleChartType('line')"
                                :class="chartType === 'line' ? 'bg-white text-spv-blue shadow-sm' : 'text-gray-400 hover:text-gray-800'"
                                title="Tampilan Grafik Garis Tren (Line)"
                                class="p-1.5 rounded-lg transition-all">
                            <i class="ph-bold ph-chart-line text-base"></i>
                        </button>
                    </div>

                </div>
            </div>

            <!-- Active Period KPI Metric Ribbon (Dynamic via Alpine.js) -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 my-4 p-3.5 rounded-2xl bg-slate-50/80 border border-slate-100">
                <div class="px-2">
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Periode Aktif</p>
                    <p class="text-xs sm:text-sm font-bold text-gray-800 truncate mt-0.5" x-text="currentSummary.badge"></p>
                </div>
                <div class="px-2 border-l border-gray-200/60">
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Total Pengiriman</p>
                    <p class="text-sm sm:text-base font-extrabold text-gray-800 mt-0.5" x-text="currentSummary.total + ' Staging'"></p>
                </div>
                <div class="px-2 border-l border-gray-200/60">
                    <p class="text-[10px] font-semibold text-emerald-600 uppercase tracking-wider">Berhasil (Submitted)</p>
                    <p class="text-sm sm:text-base font-extrabold text-emerald-600 mt-0.5" x-text="currentSummary.submitted + ' Selesai'"></p>
                </div>
                <div class="px-2 border-l border-gray-200/60">
                    <p class="text-[10px] font-semibold text-spv-blue uppercase tracking-wider">Capaian KPI</p>
                    <div class="flex items-center gap-1.5 mt-0.5">
                        <span class="text-sm sm:text-base font-extrabold text-spv-blue" x-text="currentSummary.rate + '%'"></span>
                        <span class="text-[10px] px-1.5 py-0.2 rounded font-bold bg-emerald-100 text-emerald-800"
                              x-show="currentSummary.rate >= 80">Optimal</span>
                    </div>
                </div>
            </div>

            <!-- Canvas Container -->
            <div class="relative w-full h-[320px] lg:h-[360px] pt-2">
                <canvas id="kpiMainChart"></canvas>
            </div>

            <!-- Chart Footer Legend / Explanations -->
            <div class="flex flex-wrap items-center justify-between gap-3 mt-4 pt-4 border-t border-gray-100 text-xs text-gray-500">
                <div class="flex items-center gap-4">
                    <div class="flex items-center gap-1.5">
                        <span class="w-3 h-3 rounded-md bg-emerald-500 inline-block shadow-sm"></span>
                        <span class="font-medium text-gray-700">Shipment Berhasil (Submitted)</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="w-3 h-3 rounded-md bg-spv-blue inline-block shadow-sm"></span>
                        <span class="font-medium text-gray-700">Total Pengiriman</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="w-3 h-3 rounded-md bg-amber-400 inline-block shadow-sm"></span>
                        <span class="font-medium text-gray-700">Draft (Sedang Berjalan)</span>
                    </div>
                </div>
                <div class="text-[11px] text-gray-400 flex items-center gap-1">
                    <i class="ph-bold ph-info"></i>
                    <span>Klik pada tab periode untuk mengubah interval waktu analisis secara instan</span>
                </div>
            </div>

        </div>

        <!-- 5. GRID BERBAGAI CHART PENDUKUNG (STATUS, JENIS PENGIRIMAN, SHIFT OPERASIONAL) -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
            
            <!-- Chart 2: Komposisi Status Pengiriman (Doughnut Chart) -->
            <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-[0_2px_12px_rgba(40,84,145,0.04)] flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center gap-2">
                            <div class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
                                <i class="ph-fill ph-chart-pie text-base"></i>
                            </div>
                            <h3 class="text-sm font-bold text-gray-800">Distribusi Status</h3>
                        </div>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700">
                            {{ $successRate }}% Sukses
                        </span>
                    </div>
                    <p class="text-[11px] text-gray-400 mb-4">Perbandingan shipment selesai vs dalam pengerjaan</p>
                    
                    <div class="relative w-full h-48 flex items-center justify-center">
                        <canvas id="statusDonutChart"></canvas>
                        <!-- Center Donut Text -->
                        <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                            <span class="text-2xl font-extrabold text-gray-800 leading-none">{{ $totalShipments }}</span>
                            <span class="text-[10px] font-medium text-gray-400 uppercase tracking-wider mt-1">Total Staging</span>
                        </div>
                    </div>
                </div>

                <div class="pt-4 border-t border-gray-100 space-y-2 text-xs">
                    <div class="flex items-center justify-between">
                        <span class="flex items-center gap-2 text-gray-600">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span> Submitted (Selesai)
                        </span>
                        <span class="font-bold text-gray-800">{{ $totalSubmitted }} ({{ $totalShipments > 0 ? round(($totalSubmitted/$totalShipments)*100) : 0 }}%)</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="flex items-center gap-2 text-gray-600">
                            <span class="w-2.5 h-2.5 rounded-full bg-amber-400"></span> Draft (Berjalan)
                        </span>
                        <span class="font-bold text-gray-800">{{ $totalDraft }} ({{ $totalShipments > 0 ? round(($totalDraft/$totalShipments)*100) : 0 }}%)</span>
                    </div>
                </div>
            </div>

            <!-- Chart 3: Jenis Pengiriman & Produk (Grouped Bar Chart) -->
            <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-[0_2px_12px_rgba(40,84,145,0.04)] flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center gap-2">
                            <div class="w-7 h-7 rounded-lg bg-blue-50 text-spv-blue flex items-center justify-center font-bold">
                                <i class="ph-fill ph-truck text-base"></i>
                            </div>
                            <h3 class="text-sm font-bold text-gray-800">Jenis Armada & Produk</h3>
                        </div>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-blue-50 text-spv-blue">
                            Komposisi
                        </span>
                    </div>
                    <p class="text-[11px] text-gray-400 mb-4">Volume kontainer export vs truk lokal & komoditas</p>
                    
                    <div class="relative w-full h-48">
                        <canvas id="typeBarChart"></canvas>
                    </div>
                </div>

                <div class="pt-4 border-t border-gray-100 grid grid-cols-2 gap-2 text-xs">
                    <div class="p-2 rounded-xl bg-purple-50/70 border border-purple-100">
                        <p class="text-[10px] font-bold text-purple-700">Export Kontainer</p>
                        <p class="text-base font-extrabold text-purple-900">{{ $typeDistribution['export'] }}</p>
                    </div>
                    <div class="p-2 rounded-xl bg-blue-50/70 border border-blue-100">
                        <p class="text-[10px] font-bold text-spv-blue">Lokal Truk</p>
                        <p class="text-base font-extrabold text-blue-900">{{ $typeDistribution['lokal'] }}</p>
                    </div>
                </div>
            </div>

            <!-- Chart 4: Aktivitas Shift & Lokasi Gudang -->
            <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-[0_2px_12px_rgba(40,84,145,0.04)] flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center gap-2">
                            <div class="w-7 h-7 rounded-lg bg-teal-50 text-spv-dark-teal flex items-center justify-center font-bold">
                                <i class="ph-fill ph-sun-horizon text-base"></i>
                            </div>
                            <h3 class="text-sm font-bold text-gray-800">Shift & Lokasi Staging</h3>
                        </div>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-teal-50 text-teal-700">
                            Operasional
                        </span>
                    </div>
                    <p class="text-[11px] text-gray-400 mb-4">Distribusi waktu staging dan area warehouse</p>
                    
                    <div class="relative w-full h-48">
                        <canvas id="shiftBarChart"></canvas>
                    </div>
                </div>

                <div class="pt-4 border-t border-gray-100 grid grid-cols-3 gap-1.5 text-center text-xs">
                    <div class="p-1.5 rounded-lg bg-gray-50">
                        <p class="text-[10px] text-gray-400 font-medium">Siang</p>
                        <p class="font-extrabold text-gray-700">{{ $operationalDistribution['waktu']['siang'] }}</p>
                    </div>
                    <div class="p-1.5 rounded-lg bg-gray-50">
                        <p class="text-[10px] text-gray-400 font-medium">Sore</p>
                        <p class="font-extrabold text-gray-700">{{ $operationalDistribution['waktu']['sore'] }}</p>
                    </div>
                    <div class="p-1.5 rounded-lg bg-gray-50">
                        <p class="text-[10px] text-gray-400 font-medium">Malam</p>
                        <p class="font-extrabold text-gray-700">{{ $operationalDistribution['waktu']['malam'] }}</p>
                    </div>
                </div>
            </div>

        </div>

        <!-- 6. BOTTOM ROW: PETUGAS TERBAIK (LEADERBOARD) & TABEL SHIPMENT TERBARU -->
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-5">
            
            <!-- Left 2 Cols: Tabel Shipment Terbaru -->
            <div class="xl:col-span-2 bg-white rounded-2xl shadow-[0_2px_12px_rgba(40,84,145,0.04)] border border-gray-100 overflow-hidden">
                <div class="p-5 flex items-center justify-between border-b border-gray-100">
                    <div>
                        <h2 class="text-sm font-bold text-gray-800">Shipment Terbaru</h2>
                        <p class="text-xs text-gray-400 mt-0.5">Pantauan staging kontainer dan truk terkini</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <a href="{{ route('shipments.index') }}"
                           class="text-xs font-semibold px-3 py-1.5 rounded-xl border border-gray-200 text-gray-600 hover:text-spv-blue hover:border-spv-blue transition-all">
                            Lihat Semua
                        </a>
                        <a href="{{ route('field-app.create') }}" target="_blank"
                           class="text-xs font-bold px-3.5 py-1.5 rounded-xl text-white flex items-center gap-1.5 shadow-sm transition-all"
                           style="background: linear-gradient(135deg, #285491, #0d5950);">
                            <i class="ph-bold ph-plus text-xs"></i>
                            <span>Buat Staging</span>
                        </a>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left whitespace-nowrap">
                        <thead style="background:#fafbfd;">
                            <tr>
                                <th class="px-5 py-3 text-[10px] font-bold text-gray-400 uppercase tracking-wider">Identitas Armada</th>
                                <th class="px-5 py-3 text-[10px] font-bold text-gray-400 uppercase tracking-wider hidden sm:table-cell">Produk</th>
                                <th class="px-5 py-3 text-[10px] font-bold text-gray-400 uppercase tracking-wider hidden md:table-cell">Tipe</th>
                                <th class="px-5 py-3 text-[10px] font-bold text-gray-400 uppercase tracking-wider hidden lg:table-cell">Petugas</th>
                                <th class="px-5 py-3 text-[10px] font-bold text-gray-400 uppercase tracking-wider hidden lg:table-cell">Waktu</th>
                                <th class="px-5 py-3 text-[10px] font-bold text-gray-400 uppercase tracking-wider">Status</th>
                                <th class="px-5 py-3 text-[10px] font-bold text-gray-400 uppercase tracking-wider text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($shipments as $s)
                                <tr class="border-t border-gray-100 hover:bg-blue-50/20 transition-colors">
                                    <td class="px-5 py-3.5">
                                        <div class="flex items-center gap-2.5">
                                            <div class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0" style="background:#eff4fc;">
                                                <i class="ph-fill {{ $s->jenis_pengiriman === 'export' ? 'ph-shipping-container' : 'ph-truck' }} text-base" style="color:#285491;"></i>
                                            </div>
                                            <div>
                                                <a href="{{ route('shipments.show', $s->id) }}" class="text-xs font-bold text-gray-800 hover:text-spv-blue">
                                                    {{ $s->packing_list_no ?: ($s->nomor_container_atau_plat ?? 'SPV-' . $s->id) }}
                                                </a>
                                                <p class="text-[10px] text-gray-400">Cont: {{ $s->nomor_container_atau_plat ?? '-' }} &bull; Plat: {{ $s->plat_nomor ?? '—' }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-5 py-3.5 hidden sm:table-cell">
                                        <span class="text-xs font-medium text-gray-700 capitalize">{{ $s->jenis_produk }}</span>
                                    </td>
                                    <td class="px-5 py-3.5 hidden md:table-cell">
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-semibold capitalize {{ $s->jenis_pengiriman === 'export' ? 'bg-purple-50 text-purple-700 border border-purple-200' : 'bg-gray-100 text-gray-700' }}">
                                            {{ $s->jenis_pengiriman }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-3.5 hidden lg:table-cell">
                                        <p class="text-xs text-gray-600">{{ $s->karyawan?->nama ?? '-' }}</p>
                                    </td>
                                    <td class="px-5 py-3.5 hidden lg:table-cell">
                                        <p class="text-[10px] text-gray-400">{{ $s->created_at->format('d M, H:i') }}</p>
                                    </td>
                                    <td class="px-5 py-3.5">
                                        @if($s->status === 'submitted')
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>Submitted
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>Draft
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3.5 text-right">
                                        <div class="flex items-center justify-end gap-1">
                                            <a href="{{ route('shipments.show', $s->id) }}"
                                               title="Lihat Detail Shipment"
                                               class="p-1.5 rounded-lg text-gray-400 hover:text-spv-blue hover:bg-blue-50 transition-colors">
                                                <i class="ph-bold ph-eye text-base"></i>
                                            </a>
                                            <a href="{{ route('field-app.timeline', $s->id) }}"
                                               title="Timeline Bukti Foto (27 Titik SOP)"
                                               target="_blank"
                                               class="p-1.5 rounded-lg text-gray-400 hover:text-spv-green hover:bg-emerald-50 transition-colors">
                                                <i class="ph-bold ph-camera text-base"></i>
                                            </a>
                                            <a href="{{ route('shipments.download-pdf', $s->id) }}"
                                               title="Download PDF Laporan"
                                               class="p-1.5 rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50 transition-colors">
                                                <i class="ph-bold ph-file-pdf text-base"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-8 text-xs text-gray-400">Belum ada data shipment</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Right Col: Top Petugas Staging Leaderboard -->
            <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-[0_2px_12px_rgba(40,84,145,0.04)] flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between pb-3.5 border-b border-gray-100 mb-4">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
                                <i class="ph-fill ph-trophy text-lg"></i>
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-gray-800">Petugas Staging Teraktif</h3>
                                <p class="text-[10px] text-gray-400">Kontribusi penyelesaian shipment SOP</p>
                            </div>
                        </div>
                        <a href="{{ route('karyawan.index') }}" class="text-[11px] font-bold text-spv-blue hover:underline">
                            Semua
                        </a>
                    </div>

                    <div class="space-y-3">
                        @forelse($topPetugas as $index => $petugas)
                            @php
                                $rate = $petugas->total_shipments > 0 ? round(($petugas->submitted_shipments / $petugas->total_shipments) * 100) : 0;
                                $initials = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $petugas->nama) ?: 'PT', 0, 2));
                            @endphp
                            <div class="flex items-center justify-between p-2.5 rounded-xl hover:bg-slate-50 transition-colors">
                                <div class="flex items-center gap-3">
                                    <div class="relative">
                                        <div class="w-9 h-9 rounded-xl flex items-center justify-center text-xs font-bold text-white shrink-0 shadow-sm"
                                             style="background: {{ $index === 0 ? 'linear-gradient(135deg, #f59e0b, #d97706)' : ($index === 1 ? 'linear-gradient(135deg, #94a3b8, #64748b)' : 'linear-gradient(135deg, #285491, #0d5950)') }};">
                                            {{ $initials }}
                                        </div>
                                        @if($index === 0)
                                            <span class="absolute -top-1 -right-1 w-4 h-4 bg-amber-400 text-white rounded-full flex items-center justify-center text-[9px] font-bold shadow">★</span>
                                        @endif
                                    </div>
                                    <div>
                                        <p class="text-xs font-bold text-gray-800 leading-snug">{{ $petugas->nama }}</p>
                                        <p class="text-[10px] text-gray-400">NIK: {{ $petugas->nomor_induk ?? '-' }}</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="text-xs font-extrabold text-emerald-600">{{ $petugas->submitted_shipments }} <span class="text-[10px] font-normal text-gray-400">selesai</span></p>
                                    <p class="text-[10px] text-gray-400">{{ $petugas->total_shipments }} total</p>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-6 text-xs text-gray-400">
                                Belum ada data petugas aktif
                            </div>
                        @endforelse
                    </div>
                </div>

                <div class="mt-4 pt-3 border-t border-gray-100 flex items-center justify-between">
                    <span class="text-[11px] text-gray-500">Total Petugas Siap: <strong>{{ $totalPetugas }} orang</strong></span>
                    <a href="{{ route('karyawan.create') }}" class="text-xs font-bold text-spv-blue hover:underline">
                        + Tambah Petugas
                    </a>
                </div>
            </div>

        </div>

    </div>

    <!-- SCRIPT CHART.JS & ALPINE LOGIC -->
    <script>
        function dashboardAnalytics() {
            return {
                activePeriod: 'harian',
                chartType: 'bar',
                kpiData: @json($kpiData),
                statusDistribution: @json($statusDistribution),
                typeDistribution: @json($typeDistribution),
                operationalDistribution: @json($operationalDistribution),
                
                // Charts instances
                mainKpiChart: null,
                donutChart: null,
                typeChart: null,
                shiftChart: null,

                get currentSummary() {
                    if (!this.kpiData || !this.kpiData[this.activePeriod]) {
                        return { title: '', badge: '', total: 0, submitted: 0, draft: 0, rate: 0 };
                    }
                    const data = this.kpiData[this.activePeriod];
                    return {
                        title: data.title,
                        badge: data.badge,
                        total: data.summary.total,
                        submitted: data.summary.submitted,
                        draft: data.summary.draft,
                        rate: data.summary.rate
                    };
                },

                switchPeriod(periodKey) {
                    if (this.activePeriod === periodKey) return;
                    this.activePeriod = periodKey;
                    this.updateMainChart();
                },

                toggleChartType(type) {
                    if (this.chartType === type) return;
                    this.chartType = type;
                    this.renderMainChart();
                },

                initCharts() {
                    this.$nextTick(() => {
                        this.renderMainChart();
                        this.renderDonutChart();
                        this.renderTypeChart();
                        this.renderShiftChart();
                    });
                },

                renderMainChart() {
                    const canvas = document.getElementById('kpiMainChart');
                    if (!canvas || typeof Chart === 'undefined') return;

                    if (this.mainKpiChart) {
                        this.mainKpiChart.destroy();
                    }

                    const periodObj = this.kpiData[this.activePeriod] || { labels: [], submitted: [], totals: [], drafts: [] };
                    const ctx = canvas.getContext('2d');

                    // Gradient for Submitted Bar
                    const greenGradient = ctx.createLinearGradient(0, 0, 0, 300);
                    greenGradient.addColorStop(0, '#059e3d');
                    greenGradient.addColorStop(1, '#63c384');

                    // Gradient for Total Line/Bar
                    const blueGradient = ctx.createLinearGradient(0, 0, 0, 300);
                    blueGradient.addColorStop(0, '#285491');
                    blueGradient.addColorStop(1, '#eff4fc');

                    const datasets = [
                        {
                            label: 'Shipment Berhasil (Submitted)',
                            data: periodObj.submitted,
                            backgroundColor: this.chartType === 'bar' ? greenGradient : 'rgba(5, 158, 61, 0.15)',
                            borderColor: '#059e3d',
                            borderWidth: 2,
                            borderRadius: this.chartType === 'bar' ? 6 : 0,
                            fill: this.chartType === 'line',
                            tension: 0.35,
                            pointBackgroundColor: '#059e3d',
                            pointRadius: 4,
                            order: 1
                        },
                        {
                            label: 'Total Pengiriman',
                            data: periodObj.totals,
                            backgroundColor: this.chartType === 'bar' ? 'rgba(40, 84, 145, 0.2)' : 'rgba(40, 84, 145, 0.08)',
                            borderColor: '#285491',
                            borderWidth: 2,
                            borderRadius: this.chartType === 'bar' ? 6 : 0,
                            borderDash: this.chartType === 'line' ? [5, 5] : [],
                            fill: false,
                            tension: 0.35,
                            pointBackgroundColor: '#285491',
                            pointRadius: 4,
                            order: 2
                        }
                    ];

                    this.mainKpiChart = new Chart(ctx, {
                        type: this.chartType,
                        data: {
                            labels: periodObj.labels,
                            datasets: datasets
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            animation: {
                                duration: 600,
                                easing: 'easeOutQuart'
                            },
                            interaction: {
                                mode: 'index',
                                intersect: false
                            },
                            plugins: {
                                legend: {
                                    display: false
                                },
                                tooltip: {
                                    backgroundColor: '#1a3c6e',
                                    titleColor: '#ffffff',
                                    bodyColor: '#e1f8eb',
                                    padding: 12,
                                    cornerRadius: 10,
                                    callbacks: {
                                        afterBody: function(context) {
                                            const sub = context[0]?.raw || 0;
                                            const tot = context[1]?.raw || 0;
                                            const pct = tot > 0 ? Math.round((sub / tot) * 100) : 0;
                                            return `Capaian KPI: ${pct}%`;
                                        }
                                    }
                                }
                            },
                            scales: {
                                x: {
                                    grid: { display: false },
                                    ticks: {
                                        color: '#64748b',
                                        font: { size: 11, family: 'inherit', weight: '500' }
                                    }
                                },
                                y: {
                                    beginAtZero: true,
                                    grid: { color: '#f1f5f9' },
                                    ticks: {
                                        precision: 0,
                                        color: '#94a3b8',
                                        font: { size: 11, family: 'inherit' }
                                    }
                                }
                            }
                        }
                    });
                },

                updateMainChart() {
                    if (!this.mainKpiChart) {
                        this.renderMainChart();
                        return;
                    }

                    const periodObj = this.kpiData[this.activePeriod];
                    if (!periodObj) return;

                    this.mainKpiChart.data.labels = periodObj.labels;
                    this.mainKpiChart.data.datasets[0].data = periodObj.submitted;
                    this.mainKpiChart.data.datasets[1].data = periodObj.totals;
                    this.mainKpiChart.update();
                },

                renderDonutChart() {
                    const canvas = document.getElementById('statusDonutChart');
                    if (!canvas || typeof Chart === 'undefined') return;

                    const ctx = canvas.getContext('2d');
                    this.donutChart = new Chart(ctx, {
                        type: 'doughnut',
                        data: {
                            labels: ['Submitted (Selesai)', 'Draft (Berjalan)'],
                            datasets: [{
                                data: [this.statusDistribution.submitted, this.statusDistribution.draft],
                                backgroundColor: ['#059e3d', '#f59e0b'],
                                borderWidth: 0,
                                hoverOffset: 4
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            cutout: '74%',
                            plugins: {
                                legend: { display: false },
                                tooltip: {
                                    backgroundColor: '#1a3c6e',
                                    padding: 10,
                                    cornerRadius: 8
                                }
                            }
                        }
                    });
                },

                renderTypeChart() {
                    const canvas = document.getElementById('typeBarChart');
                    if (!canvas || typeof Chart === 'undefined') return;

                    const ctx = canvas.getContext('2d');
                    this.typeChart = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: ['Export', 'Lokal', 'Fiber', 'Sodium'],
                            datasets: [{
                                label: 'Jumlah Staging',
                                data: [
                                    this.typeDistribution.export,
                                    this.typeDistribution.lokal,
                                    this.typeDistribution.fiber,
                                    this.typeDistribution.sodium
                                ],
                                backgroundColor: ['#8b5cf6', '#285491', '#059e3d', '#0d5950'],
                                borderRadius: 6
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { display: false }
                            },
                            scales: {
                                x: {
                                    grid: { display: false },
                                    ticks: { font: { size: 10 } }
                                },
                                y: {
                                    beginAtZero: true,
                                    grid: { color: '#f1f5f9' },
                                    ticks: { precision: 0, font: { size: 10 } }
                                }
                            }
                        }
                    });
                },

                renderShiftChart() {
                    const canvas = document.getElementById('shiftBarChart');
                    if (!canvas || typeof Chart === 'undefined') return;

                    const ctx = canvas.getContext('2d');
                    const waktu = this.operationalDistribution.waktu;
                    const lokasi = this.operationalDistribution.lokasi;

                    this.shiftChart = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: ['Siang', 'Sore', 'Malam', 'Gudang Atas', 'Gudang Tengah', 'Gudang Bawah'],
                            datasets: [{
                                label: 'Aktivitas Staging',
                                data: [
                                    waktu.siang,
                                    waktu.sore,
                                    waktu.malam,
                                    lokasi.atas,
                                    lokasi.tengah,
                                    lokasi.bawah
                                ],
                                backgroundColor: '#0d5950',
                                borderRadius: 6
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { display: false }
                            },
                            scales: {
                                x: {
                                    grid: { display: false },
                                    ticks: { font: { size: 9 } }
                                },
                                y: {
                                    beginAtZero: true,
                                    grid: { color: '#f1f5f9' },
                                    ticks: { precision: 0, font: { size: 10 } }
                                }
                            }
                        }
                    });
                }
            };
        }
    </script>

</x-layout>
