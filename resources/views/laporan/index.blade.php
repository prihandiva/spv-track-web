<x-layout :title="'Laporan & Audit Staging'">

    <div x-data="{
        mode: '{{ request('mode', 'all') }}',
        activeTab: '{{ $activeTab }}',
        exportDropdownOpen: false,
        filterExpanded: {{ request()->hasAny(['jenis_produk', 'jenis_pengiriman', 'karyawan_id', 'status', 'compliance', 'search']) ? 'true' : 'false' }}
    }" class="space-y-6">

        <!-- 1. HEADER SECTION & EXPORT ACTIONS -->
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2.5">
                    <h1 class="text-2xl font-black text-gray-900 tracking-tight">Laporan & Audit Staging</h1>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-blue-50 text-spv-blue border border-blue-200">
                        <i class="ph-bold ph-calendar text-xs"></i>
                        <span>{{ $periodLabel }}</span>
                    </span>
                </div>
                <p class="text-xs text-gray-500 mt-1">
                    Rekapitulasi logistik berkala, evaluasi kepatuhan 27 titik foto SOP, dan pusat ekspor dokumen resmi warehouse.
                </p>
            </div>

            <!-- Action Buttons (Export Center) -->
            <div class="flex items-center gap-2.5 flex-wrap">
                <!-- Dropdown Ekspor Excel / CSV -->
                <div class="relative" @click.away="exportDropdownOpen = false">
                    <button type="button"
                            @click="exportDropdownOpen = !exportDropdownOpen"
                            class="flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold px-4 py-2.5 rounded-xl shadow-sm hover:shadow transition-all">
                        <i class="ph-bold ph-file-xls text-base"></i>
                        <span>Ekspor Excel / CSV</span>
                        <i class="ph-bold ph-caret-down text-xs transition-transform" :class="exportDropdownOpen ? 'rotate-180' : ''"></i>
                    </button>

                    <div x-cloak
                         x-show="exportDropdownOpen"
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-100"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95"
                         class="absolute right-0 mt-2 w-56 bg-white rounded-2xl shadow-xl border border-gray-100 py-2 z-50">
                        <div class="px-3 py-1.5 text-[10px] font-bold uppercase tracking-wider text-gray-400">Pilih Format Ekspor</div>
                        <a href="{{ route('laporan.export-excel', array_merge(request()->query(), ['format' => 'csv'])) }}"
                           class="flex items-center gap-2.5 px-3.5 py-2 text-xs text-gray-700 hover:bg-emerald-50 hover:text-emerald-800 transition-colors">
                            <i class="ph-bold ph-file-csv text-base text-emerald-600"></i>
                            <div>
                                <p class="font-bold">Format CSV (UTF-8)</p>
                                <p class="text-[10px] text-gray-400">Kompatibel Excel, Google Sheets, ERP</p>
                            </div>
                        </a>
                        <a href="{{ route('laporan.export-excel', array_merge(request()->query(), ['format' => 'xls'])) }}"
                           class="flex items-center gap-2.5 px-3.5 py-2 text-xs text-gray-700 hover:bg-emerald-50 hover:text-emerald-800 transition-colors">
                            <i class="ph-bold ph-file-xls text-base text-emerald-600"></i>
                            <div>
                                <p class="font-bold">Format Excel Sheet (.xls)</p>
                                <p class="text-[10px] text-gray-400">Tabel rapi siap cetak & analitik</p>
                            </div>
                        </a>
                    </div>
                </div>

                <!-- Cetak Berita Acara (Print / PDF) -->
                <a href="{{ route('laporan.print-rekap', request()->query()) }}" target="_blank"
                   class="flex items-center gap-2 bg-white hover:bg-blue-50 text-spv-blue border border-blue-200 text-xs font-bold px-4 py-2.5 rounded-xl shadow-sm hover:shadow transition-all">
                    <i class="ph-bold ph-printer text-base"></i>
                    <span>Cetak Berita Acara</span>
                </a>

                <!-- Unduh Batch PDF -->
                <a href="{{ route('laporan.download-rekap-pdf', request()->query()) }}"
                   class="flex items-center gap-2 bg-spv-blue hover:bg-blue-800 text-white text-xs font-bold px-4 py-2.5 rounded-xl shadow-sm hover:shadow transition-all">
                    <i class="ph-bold ph-file-pdf text-base"></i>
                    <span>Download PDF</span>
                </a>

                <!-- Shortcut Batch ZIP Evidence -->
                <a href="{{ route('shipments.download-batch-zip', [
                    'mode' => request('mode') === 'all' ? 'tahunan' : (request('mode', 'harian')),
                    'date' => request('date'),
                    'month' => request('month'),
                    'year' => request('year'),
                    'start_date' => request('start_date'),
                    'end_date' => request('end_date'),
                    'jenis_produk' => request('jenis_produk', 'all'),
                ]) }}"
                   class="flex items-center gap-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold px-3 py-2.5 rounded-xl transition-all"
                   title="Unduh berkas arsip seluruh foto evidence periode ini">
                    <i class="ph-bold ph-file-zip text-base text-amber-600"></i>
                    <span>Batch ZIP</span>
                </a>
            </div>
        </div>

        <!-- 2. REPORTING CONTROL BAR (FILTER CARD) -->
        <div class="bg-white rounded-2xl shadow-[0_1px_6px_rgba(40,84,145,0.05)] border border-gray-100 p-5">
            <form method="GET" action="{{ route('laporan.index') }}" id="reportFilterForm" class="space-y-4">
                <input type="hidden" name="tab" :value="activeTab">

                <!-- Mode Periode Tabs -->
                <div class="flex items-center justify-between flex-wrap gap-3">
                    <div class="flex items-center gap-1.5 p-1 bg-gray-100 rounded-xl overflow-x-auto max-w-full">
                        <label class="cursor-pointer">
                            <input type="radio" name="mode" value="all" x-model="mode" class="sr-only">
                            <span class="inline-block px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all"
                                  :class="mode === 'all' ? 'bg-white text-spv-blue shadow-sm' : 'text-gray-600 hover:text-gray-900'">
                                Semua
                            </span>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="mode" value="harian" x-model="mode" class="sr-only">
                            <span class="inline-block px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all"
                                  :class="mode === 'harian' ? 'bg-white text-spv-blue shadow-sm' : 'text-gray-600 hover:text-gray-900'">
                                Harian
                            </span>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="mode" value="mingguan" x-model="mode" class="sr-only">
                            <span class="inline-block px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all"
                                  :class="mode === 'mingguan' ? 'bg-white text-spv-blue shadow-sm' : 'text-gray-600 hover:text-gray-900'">
                                Mingguan
                            </span>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="mode" value="bulanan" x-model="mode" class="sr-only">
                            <span class="inline-block px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all"
                                  :class="mode === 'bulanan' ? 'bg-white text-spv-blue shadow-sm' : 'text-gray-600 hover:text-gray-900'">
                                Bulanan
                            </span>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="mode" value="tahunan" x-model="mode" class="sr-only">
                            <span class="inline-block px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all"
                                  :class="mode === 'tahunan' ? 'bg-white text-spv-blue shadow-sm' : 'text-gray-600 hover:text-gray-900'">
                                Tahunan
                            </span>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="mode" value="range" x-model="mode" class="sr-only">
                            <span class="inline-block px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all"
                                  :class="mode === 'range' ? 'bg-white text-spv-blue shadow-sm' : 'text-gray-600 hover:text-gray-900'">
                                Rentang Tanggal
                            </span>
                        </label>
                    </div>

                    <button type="button"
                            @click="filterExpanded = !filterExpanded"
                            class="inline-flex items-center gap-1.5 text-xs font-semibold text-spv-blue hover:text-blue-800 transition-colors">
                        <i class="ph-bold ph-funnel text-sm"></i>
                        <span x-text="filterExpanded ? 'Sembunyikan Filter Lanjutan' : 'Filter Kriteria Lanjutan'"></span>
                        <i class="ph-bold ph-caret-down text-xs transition-transform" :class="filterExpanded ? 'rotate-180' : ''"></i>
                    </button>
                </div>

                <!-- Input Tanggal Dinamis Sesuai Mode -->
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3 pt-2 border-t border-gray-100">
                    <!-- Harian -->
                    <div x-show="mode === 'harian'" class="space-y-1">
                        <label class="text-[11px] font-bold text-gray-500 uppercase tracking-wider">Tanggal Staging</label>
                        <input type="date" name="date" value="{{ request('date', now()->toDateString()) }}"
                               class="w-full text-xs bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 outline-none focus:ring-2 focus:ring-spv-blue">
                    </div>

                    <!-- Bulanan: Bulan & Tahun -->
                    <div x-show="mode === 'bulanan'" class="space-y-1">
                        <label class="text-[11px] font-bold text-gray-500 uppercase tracking-wider">Pilih Bulan</label>
                        <select name="month" class="w-full text-xs bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 outline-none focus:ring-2 focus:ring-spv-blue">
                            @foreach([
                                1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                                5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                                9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                            ] as $mNum => $mText)
                                <option value="{{ $mNum }}" {{ (int)request('month', now()->month) === $mNum ? 'selected' : '' }}>
                                    {{ $mText }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div x-show="mode === 'bulanan' || mode === 'tahunan'" class="space-y-1">
                        <label class="text-[11px] font-bold text-gray-500 uppercase tracking-wider">Pilih Tahun</label>
                        <select name="year" class="w-full text-xs bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 outline-none focus:ring-2 focus:ring-spv-blue">
                            @for($y = now()->year; $y >= now()->year - 3; $y--)
                                <option value="{{ $y }}" {{ (int)request('year', now()->year) === $y ? 'selected' : '' }}>
                                    Tahun {{ $y }}
                                </option>
                            @endfor
                        </select>
                    </div>

                    <!-- Mingguan / Range: Start & End Date -->
                    <div x-show="mode === 'mingguan' || mode === 'range'" class="space-y-1">
                        <label class="text-[11px] font-bold text-gray-500 uppercase tracking-wider">Mulai Tanggal</label>
                        <input type="date" name="start_date" value="{{ request('start_date', now()->startOfWeek()->toDateString()) }}"
                               class="w-full text-xs bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 outline-none focus:ring-2 focus:ring-spv-blue">
                    </div>
                    <div x-show="mode === 'mingguan' || mode === 'range'" class="space-y-1">
                        <label class="text-[11px] font-bold text-gray-500 uppercase tracking-wider">Sampai Tanggal</label>
                        <input type="date" name="end_date" value="{{ request('end_date', now()->endOfWeek()->toDateString()) }}"
                               class="w-full text-xs bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 outline-none focus:ring-2 focus:ring-spv-blue">
                    </div>

                    <!-- Search Input -->
                    <div class="space-y-1 {{ request('mode') === 'all' ? 'sm:col-span-2 md:col-span-3' : '' }}">
                        <label class="text-[11px] font-bold text-gray-500 uppercase tracking-wider">Cari Kata Kunci</label>
                        <div class="relative">
                            <i class="ph-bold ph-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                            <input type="text" name="search" value="{{ request('search') }}"
                                   placeholder="No. PL, Container, Plat, Sopir..."
                                   class="w-full text-xs bg-gray-50 border border-gray-200 rounded-xl pl-9 pr-3 py-2 outline-none focus:ring-2 focus:ring-spv-blue">
                        </div>
                    </div>

                    <!-- Submit & Reset Buttons -->
                    <div class="flex items-end gap-2">
                        <button type="submit"
                                class="flex-1 bg-spv-blue hover:bg-blue-800 text-white text-xs font-bold py-2 px-4 rounded-xl shadow transition-all">
                            Terapkan
                        </button>
                        <a href="{{ route('laporan.index') }}"
                           class="bg-gray-100 hover:bg-gray-200 text-gray-600 text-xs font-semibold py-2 px-3 rounded-xl transition-colors"
                           title="Reset Semua Filter">
                            <i class="ph-bold ph-arrow-counter-clockwise"></i>
                        </a>
                    </div>
                </div>

                <!-- Filter Lanjutan (Accordion) -->
                <div x-show="filterExpanded" x-cloak class="pt-3 border-t border-gray-100 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-3">
                    <!-- Produk -->
                    <div class="space-y-1">
                        <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Jenis Produk</label>
                        <select name="jenis_produk" class="w-full text-xs bg-gray-50 border border-gray-200 rounded-xl px-2.5 py-1.5 outline-none focus:ring-2 focus:ring-spv-blue">
                            <option value="">Semua Produk</option>
                            <option value="fiber" {{ request('jenis_produk') === 'fiber' ? 'selected' : '' }}>Fiber</option>
                            <option value="sodium" {{ request('jenis_produk') === 'sodium' ? 'selected' : '' }}>Sodium</option>
                        </select>
                    </div>

                    <!-- Pengiriman -->
                    <div class="space-y-1">
                        <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Jenis Pengiriman</label>
                        <select name="jenis_pengiriman" class="w-full text-xs bg-gray-50 border border-gray-200 rounded-xl px-2.5 py-1.5 outline-none focus:ring-2 focus:ring-spv-blue">
                            <option value="">Semua Tipe</option>
                            <option value="export" {{ request('jenis_pengiriman') === 'export' ? 'selected' : '' }}>Export</option>
                            <option value="lokal" {{ request('jenis_pengiriman') === 'lokal' ? 'selected' : '' }}>Lokal</option>
                        </select>
                    </div>

                    <!-- Petugas -->
                    <div class="space-y-1">
                        <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Petugas Lapangan</label>
                        <select name="karyawan_id" class="w-full text-xs bg-gray-50 border border-gray-200 rounded-xl px-2.5 py-1.5 outline-none focus:ring-2 focus:ring-spv-blue">
                            <option value="">Semua Petugas</option>
                            @foreach($allPetugas as $p)
                                <option value="{{ $p->id }}" {{ (string)request('karyawan_id') === (string)$p->id ? 'selected' : '' }}>
                                    {{ $p->nama }} ({{ $p->nomor_induk }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Kepatuhan Foto SOP -->
                    <div class="space-y-1">
                        <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Kepatuhan 27 SOP</label>
                        <select name="compliance" class="w-full text-xs bg-gray-50 border border-gray-200 rounded-xl px-2.5 py-1.5 outline-none focus:ring-2 focus:ring-spv-blue">
                            <option value="">Semua Kondisi</option>
                            <option value="lengkap" {{ request('compliance') === 'lengkap' ? 'selected' : '' }}>Lengkap 27 Titik (100%)</option>
                            <option value="kurang" {{ request('compliance') === 'kurang' ? 'selected' : '' }}>Foto Belum Lengkap (&lt;27)</option>
                        </select>
                    </div>

                    <!-- Status -->
                    <div class="space-y-1">
                        <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Status Dokumen</label>
                        <select name="status" class="w-full text-xs bg-gray-50 border border-gray-200 rounded-xl px-2.5 py-1.5 outline-none focus:ring-2 focus:ring-spv-blue">
                            <option value="">Semua Status</option>
                            <option value="submitted" {{ request('status') === 'submitted' ? 'selected' : '' }}>Submitted (Selesai)</option>
                            <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft (Proses)</option>
                        </select>
                    </div>
                </div>
            </form>
        </div>

        <!-- 3. EXECUTIVE KPI & SUMMARY CARDS -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- 1. Total Armada -->
            <div class="bg-white rounded-2xl p-5 shadow-[0_1px_6px_rgba(40,84,145,0.05)] border border-gray-100 flex flex-col justify-between relative overflow-hidden">
                <div class="absolute -right-4 -bottom-4 w-20 h-20 rounded-full bg-blue-50 pointer-events-none"></div>
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Total Armada Staging</span>
                        <div class="w-8 h-8 rounded-xl bg-blue-50 text-spv-blue flex items-center justify-center">
                            <i class="ph-fill ph-shipping-container text-lg"></i>
                        </div>
                    </div>
                    <div class="flex items-baseline gap-2">
                        <span class="text-3xl font-black text-gray-900">{{ $summary->total_shipments }}</span>
                        <span class="text-xs text-gray-500 font-medium">Kontainer / Truk</span>
                    </div>
                </div>
                <div class="mt-4 pt-3 border-t border-gray-100 flex items-center justify-between text-[11px]">
                    <span class="text-gray-500">Fiber: <strong class="text-gray-800">{{ $summary->total_fiber }}</strong> &bull; Sodium: <strong class="text-gray-800">{{ $summary->total_sodium }}</strong></span>
                    <span class="text-spv-blue font-bold">{{ $summary->total_export }} Export</span>
                </div>
            </div>

            <!-- 2. Kepatuhan SOP Foto -->
            <div class="bg-white rounded-2xl p-5 shadow-[0_1px_6px_rgba(40,84,145,0.05)] border border-gray-100 flex flex-col justify-between relative overflow-hidden">
                <div class="absolute -right-4 -bottom-4 w-20 h-20 rounded-full bg-emerald-50 pointer-events-none"></div>
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Kepatuhan 27 Titik SOP</span>
                        <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                            <i class="ph-fill ph-shield-check text-lg"></i>
                        </div>
                    </div>
                    <div class="flex items-baseline gap-2">
                        <span class="text-3xl font-black text-emerald-600">{{ $summary->avg_compliance }}%</span>
                        <span class="text-xs text-gray-500 font-medium">Rata-rata Kelengkapan</span>
                    </div>
                </div>
                <div class="mt-4 pt-3 border-t border-gray-100">
                    <div class="w-full bg-gray-100 h-1.5 rounded-full overflow-hidden mb-1.5">
                        <div class="bg-emerald-500 h-1.5 rounded-full" style="width: {{ min(100, $summary->avg_compliance) }}%;"></div>
                    </div>
                    <div class="flex items-center justify-between text-[10px] text-gray-500">
                        <span><strong class="text-emerald-700 font-bold">{{ $summary->total_lengkap }}</strong> Lengkap (27/27)</span>
                        <span><strong class="text-amber-600 font-bold">{{ $summary->total_kurang }}</strong> Belum Lengkap</span>
                    </div>
                </div>
            </div>

            <!-- 3. Validasi OCR Fisik Kontainer -->
            <div class="bg-white rounded-2xl p-5 shadow-[0_1px_6px_rgba(40,84,145,0.05)] border border-gray-100 flex flex-col justify-between relative overflow-hidden">
                <div class="absolute -right-4 -bottom-4 w-20 h-20 rounded-full bg-purple-50 pointer-events-none"></div>
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Validasi OCR Kontainer</span>
                        <div class="w-8 h-8 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center">
                            <i class="ph-fill ph-scan text-lg"></i>
                        </div>
                    </div>
                    <div class="flex items-baseline gap-2">
                        <span class="text-3xl font-black text-gray-900">{{ $summary->ocr_valid }}</span>
                        <span class="text-xs text-gray-500 font-medium">/ {{ $summary->total_shipments }} Cocok Fisik</span>
                    </div>
                </div>
                <div class="mt-4 pt-3 border-t border-gray-100 flex items-center justify-between text-[11px]">
                    <span class="text-gray-500">Tingkat Akurasi AI:</span>
                    <span class="font-bold text-purple-700">
                        {{ $summary->total_shipments > 0 ? round(($summary->ocr_valid / $summary->total_shipments) * 100, 1) : 0 }}% Terverifikasi
                    </span>
                </div>
            </div>

            <!-- 4. Personil Petugas Lapangan -->
            <div class="bg-white rounded-2xl p-5 shadow-[0_1px_6px_rgba(40,84,145,0.05)] border border-gray-100 flex flex-col justify-between relative overflow-hidden">
                <div class="absolute -right-4 -bottom-4 w-20 h-20 rounded-full bg-teal-50 pointer-events-none"></div>
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Personil Lapangan</span>
                        <div class="w-8 h-8 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center">
                            <i class="ph-fill ph-users-three text-lg"></i>
                        </div>
                    </div>
                    <div class="flex items-baseline gap-2">
                        <span class="text-3xl font-black text-gray-900">{{ $summary->unique_petugas_count }}</span>
                        <span class="text-xs text-gray-500 font-medium">Petugas Bertugas</span>
                    </div>
                </div>
                <div class="mt-4 pt-3 border-t border-gray-100 flex items-center justify-between text-[11px]">
                    <span class="text-gray-500">Status Staging:</span>
                    <span class="font-bold text-emerald-600">{{ $summary->total_submitted }} Submitted</span>
                </div>
            </div>
        </div>

        <!-- 4. TAB NAVIGATION -->
        <div class="flex items-center gap-2 border-b border-gray-200">
            <button type="button"
                    @click="activeTab = 'rekap'"
                    class="flex items-center gap-2 pb-3 px-3 text-sm font-bold border-b-2 transition-all"
                    :class="activeTab === 'rekap' ? 'border-spv-blue text-spv-blue' : 'border-transparent text-gray-400 hover:text-gray-700'">
                <i class="ph-bold ph-table text-base"></i>
                <span>I. Rekapitulasi Pengiriman (Log Sheet)</span>
                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full"
                      :class="activeTab === 'rekap' ? 'bg-blue-100 text-spv-blue' : 'bg-gray-100 text-gray-500'">
                    {{ $paginatedShipments->total() }}
                </span>
            </button>

            <button type="button"
                    @click="activeTab = 'audit'"
                    class="flex items-center gap-2 pb-3 px-3 text-sm font-bold border-b-2 transition-all"
                    :class="activeTab === 'audit' ? 'border-spv-blue text-spv-blue' : 'border-transparent text-gray-400 hover:text-gray-700'">
                <i class="ph-bold ph-check-square-offset text-base"></i>
                <span>II. Audit Kepatuhan 27 Titik SOP</span>
                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full"
                      :class="activeTab === 'audit' ? 'bg-blue-100 text-spv-blue' : 'bg-gray-100 text-gray-500'">
                    27 Titik
                </span>
            </button>

            <button type="button"
                    @click="activeTab = 'petugas'"
                    class="flex items-center gap-2 pb-3 px-3 text-sm font-bold border-b-2 transition-all"
                    :class="activeTab === 'petugas' ? 'border-spv-blue text-spv-blue' : 'border-transparent text-gray-400 hover:text-gray-700'">
                <i class="ph-bold ph-identification-card text-base"></i>
                <span>III. Produktivitas Petugas Lapangan</span>
                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full"
                      :class="activeTab === 'petugas' ? 'bg-blue-100 text-spv-blue' : 'bg-gray-100 text-gray-500'">
                    {{ $petugasProductivity->count() }} Orang
                </span>
            </button>
        </div>

        <!-- 5. TAB 1: REKAPITULASI PENGIRIMAN (LOG SHEET) -->
        <div x-show="activeTab === 'rekap'" class="space-y-4">
            <div class="bg-white rounded-2xl shadow-[0_1px_6px_rgba(40,84,145,0.05)] border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left whitespace-nowrap">
                        <thead style="background:#fafbfd;">
                            <tr>
                                <th class="px-5 py-3.5 text-[10px] font-bold text-gray-400 uppercase tracking-wider">No</th>
                                <th class="px-5 py-3.5 text-[10px] font-bold text-gray-400 uppercase tracking-wider">No. Packing List / Identitas</th>
                                <th class="px-5 py-3.5 text-[10px] font-bold text-gray-400 uppercase tracking-wider">Waktu Staging</th>
                                <th class="px-5 py-3.5 text-[10px] font-bold text-gray-400 uppercase tracking-wider">Produk & Tipe</th>
                                <th class="px-5 py-3.5 text-[10px] font-bold text-gray-400 uppercase tracking-wider">Sopir & Tujuan</th>
                                <th class="px-5 py-3.5 text-[10px] font-bold text-gray-400 uppercase tracking-wider">Petugas</th>
                                <th class="px-5 py-3.5 text-[10px] font-bold text-gray-400 uppercase tracking-wider">Kepatuhan 27 SOP</th>
                                <th class="px-5 py-3.5 text-[10px] font-bold text-gray-400 uppercase tracking-wider">Validasi OCR</th>
                                <th class="px-5 py-3.5 text-[10px] font-bold text-gray-400 uppercase tracking-wider">Status</th>
                                <th class="px-5 py-3.5 text-[10px] font-bold text-gray-400 uppercase tracking-wider text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($paginatedShipments as $index => $shipment)
                                <tr style="border-top: 1px solid #f0f4f9;" onmouseover="this.style.background='#fafcff'" onmouseout="this.style.background='transparent'">
                                    <td class="px-5 py-4 text-xs font-bold text-gray-400">
                                        {{ ($paginatedShipments->currentPage() - 1) * $paginatedShipments->perPage() + $index + 1 }}
                                    </td>
                                    <td class="px-5 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0" style="background:#eff4fc;">
                                                <i class="ph-fill {{ $shipment->jenis_pengiriman === 'export' ? 'ph-shipping-container' : 'ph-truck' }} text-lg" style="color:#285491;"></i>
                                            </div>
                                            <div>
                                                <a href="{{ route('shipments.show', $shipment->id) }}" class="text-xs font-bold text-gray-900 hover:text-spv-blue transition-colors">
                                                    {{ $shipment->packing_list_no ?: ($shipment->nomor_container_atau_plat ?? 'SPV-' . $shipment->id) }}
                                                </a>
                                                <p class="text-[10px] text-gray-400">
                                                    Cont: <span class="font-mono text-gray-600">{{ $shipment->nomor_container_atau_plat ?: '-' }}</span> &bull; 
                                                    Plat: <span class="font-mono text-gray-600">{{ $shipment->plat_nomor ?: ($shipment->shipment_no ?: '—') }}</span>
                                                </p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-5 py-4">
                                        <p class="text-xs font-semibold text-gray-800">
                                            {{ $shipment->tanggal_staging ? $shipment->tanggal_staging->format('d M Y') : ($shipment->created_at ? $shipment->created_at->format('d M Y') : '-') }}
                                        </p>
                                        <p class="text-[10px] text-gray-400">
                                            Shift: {{ ucfirst((string)$shipment->waktu) }} &bull; Lokasi: {{ ucfirst((string)$shipment->warehouse_lokasi) }}
                                        </p>
                                    </td>
                                    <td class="px-5 py-4">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider {{ $shipment->jenis_produk === 'fiber' ? 'bg-blue-50 text-blue-700' : 'bg-amber-50 text-amber-700' }}">
                                            {{ $shipment->jenis_produk }}
                                        </span>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider {{ $shipment->jenis_pengiriman === 'export' ? 'bg-purple-50 text-purple-700' : 'bg-gray-100 text-gray-600' }} ml-1">
                                            {{ $shipment->jenis_pengiriman }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-4">
                                        <p class="text-xs text-gray-700 font-medium">{{ $shipment->nama_sopir ?: '—' }}</p>
                                        <p class="text-[10px] text-gray-400 truncate max-w-[140px]">{{ $shipment->tujuan_pengiriman ?: ($shipment->agen_forwarding ?: '-') }}</p>
                                    </td>
                                    <td class="px-5 py-4">
                                        <p class="text-xs text-gray-700 font-semibold">
                                            {{ $shipment->allKaryawans()->pluck('nama')->join(', ') ?: ($shipment->karyawan?->nama ?? '-') }}
                                        </p>
                                    </td>
                                    <td class="px-5 py-4">
                                        <div class="flex items-center gap-2">
                                            @if($shipment->is_complete)
                                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                    <i class="ph-bold ph-check text-xs"></i>
                                                    27 / 27 (100%)
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                                    <i class="ph-bold ph-warning text-xs"></i>
                                                    {{ $shipment->points_count }} / 27 ({{ $shipment->compliance_rate }}%)
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-5 py-4">
                                        @if($shipment->ocr_match)
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-green-50 text-green-700 border border-green-200">
                                                <i class="ph-bold ph-check-circle"></i> Cocok
                                            </span>
                                        @elseif($shipment->ocr_checked)
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                                <i class="ph-bold ph-warning-circle"></i> Mismatch
                                            </span>
                                        @else
                                            <span class="text-[10px] text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4">
                                        @if($shipment->status === 'submitted')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-green-50 text-green-700 border border-green-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-green-500 animate-pulse"></span>
                                                Submitted
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                                Draft
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4 text-right">
                                        <div class="flex items-center justify-end gap-1.5">
                                            <a href="{{ route('shipments.show', $shipment->id) }}"
                                               title="Lihat Detail Transaksi & Foto"
                                               class="p-1.5 rounded-lg text-gray-400 hover:text-spv-blue hover:bg-blue-50 transition-colors">
                                                <i class="ph-bold ph-eye text-base"></i>
                                            </a>
                                            <a href="{{ route('shipments.report', $shipment->id) }}" target="_blank"
                                               title="Cetak Laporan PDF Satuan"
                                               class="p-1.5 rounded-lg text-gray-400 hover:text-emerald-600 hover:bg-emerald-50 transition-colors">
                                                <i class="ph-bold ph-printer text-base"></i>
                                            </a>
                                            <a href="{{ route('shipments.download-evidence', $shipment->id) }}"
                                               title="Unduh Berkas Evidence ZIP"
                                               class="p-1.5 rounded-lg text-gray-400 hover:text-amber-600 hover:bg-amber-50 transition-colors">
                                                <i class="ph-bold ph-file-zip text-base"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="px-5 py-12 text-center text-gray-400">
                                        <div class="w-12 h-12 rounded-2xl bg-gray-100 text-gray-400 flex items-center justify-center mx-auto mb-3">
                                            <i class="ph-bold ph-file-dashed text-2xl"></i>
                                        </div>
                                        <p class="text-xs font-bold text-gray-700">Tidak ada data shipment pada filter yang dipilih</p>
                                        <p class="text-[11px] text-gray-400 mt-1">Silakan sesuaikan filter periode waktu, produk, atau petugas.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination Links -->
                @if($paginatedShipments->hasPages())
                    <div class="p-4 border-t border-gray-100 flex items-center justify-between">
                        {{ $paginatedShipments->links() }}
                    </div>
                @endif
            </div>
        </div>

        <!-- 6. TAB 2: AUDIT KEPATUHAN 27 TITIK SOP (COMPLIANCE MATRIX) -->
        <div x-show="activeTab === 'audit'" x-cloak class="space-y-4">
            <div class="bg-white rounded-2xl shadow-[0_1px_6px_rgba(40,84,145,0.05)] border border-gray-100 p-6">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6 pb-4 border-b border-gray-100">
                    <div>
                        <h2 class="text-base font-bold text-gray-900">Matriks Evaluasi Kepatuhan 27 Titik SOP</h2>
                        <p class="text-xs text-gray-500 mt-0.5">Analisis keterisian bukti foto pada setiap titik inspeksi untuk total {{ $summary->total_shipments }} kontainer.</p>
                    </div>
                    <div class="flex items-center gap-3 text-xs">
                        <span class="inline-flex items-center gap-1.5 text-emerald-700 font-bold">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span> &ge; 95% Sempurna
                        </span>
                        <span class="inline-flex items-center gap-1.5 text-amber-700 font-bold">
                            <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span> 80 - 94% Baik
                        </span>
                        <span class="inline-flex items-center gap-1.5 text-rose-700 font-bold">
                            <span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span> &lt; 80% Perlu Perhatian
                        </span>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left whitespace-nowrap">
                        <thead style="background:#fafbfd;">
                            <tr>
                                <th class="px-4 py-3 text-[10px] font-bold text-gray-400 uppercase tracking-wider w-12 text-center">#</th>
                                <th class="px-4 py-3 text-[10px] font-bold text-gray-400 uppercase tracking-wider">Nama Titik SOP</th>
                                <th class="px-4 py-3 text-[10px] font-bold text-gray-400 uppercase tracking-wider">Tipe</th>
                                <th class="px-4 py-3 text-[10px] font-bold text-gray-400 uppercase tracking-wider">Keterisian Armada</th>
                                <th class="px-4 py-3 text-[10px] font-bold text-gray-400 uppercase tracking-wider w-44">Tingkat Kepatuhan</th>
                                <th class="px-4 py-3 text-[10px] font-bold text-gray-400 uppercase tracking-wider text-center">Status</th>
                                <th class="px-4 py-3 text-[10px] font-bold text-gray-400 uppercase tracking-wider">Armada Kurang Titik</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($sopAuditMatrix as $audit)
                                <tr style="border-top: 1px solid #f0f4f9;" onmouseover="this.style.background='#fafcff'" onmouseout="this.style.background='transparent'">
                                    <td class="px-4 py-3.5 text-center font-bold text-xs text-gray-500">
                                        {{ $audit->urutan }}
                                    </td>
                                    <td class="px-4 py-3.5">
                                        <p class="text-xs font-bold text-gray-900">{{ $audit->nama_titik }}</p>
                                        @if($audit->deskripsi)
                                            <p class="text-[10px] text-gray-400 max-w-md truncate">{{ $audit->deskripsi }}</p>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3.5">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold uppercase bg-gray-100 text-gray-600">
                                            {{ $audit->tipe_item }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3.5">
                                        <span class="text-xs font-bold text-gray-800">{{ $audit->covered_count }}</span>
                                        <span class="text-xs text-gray-400">/ {{ $summary->total_shipments }} Kontainer</span>
                                    </td>
                                    <td class="px-4 py-3.5">
                                        <div class="flex items-center gap-2">
                                            <div class="flex-1 bg-gray-100 h-2 rounded-full overflow-hidden">
                                                <div class="h-2 rounded-full {{ $audit->percentage >= 95 ? 'bg-emerald-500' : ($audit->percentage >= 80 ? 'bg-amber-500' : 'bg-rose-500') }}"
                                                     style="width: {{ $audit->percentage }}%;"></div>
                                            </div>
                                            <span class="text-xs font-black {{ $audit->percentage >= 95 ? 'text-emerald-700' : ($audit->percentage >= 80 ? 'text-amber-700' : 'text-rose-700') }}">
                                                {{ $audit->percentage }}%
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3.5 text-center">
                                        @if($audit->percentage >= 95)
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                <i class="ph-bold ph-check text-xs"></i> Sempurna
                                            </span>
                                        @elseif($audit->percentage >= 80)
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                                <i class="ph-bold ph-warning text-xs"></i> Baik
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                                <i class="ph-bold ph-x text-xs"></i> Perhatian
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3.5">
                                        @if($audit->missing_count === 0)
                                            <span class="text-[11px] text-emerald-600 font-bold">&check; Semua Lengkap</span>
                                        @else
                                            <div class="flex items-center gap-1.5 flex-wrap max-w-xs">
                                                @foreach($audit->missing_shipments->take(3) as $ms)
                                                    <a href="{{ route('shipments.show', $ms->id) }}"
                                                       class="text-[10px] font-mono font-semibold px-1.5 py-0.5 rounded bg-gray-100 hover:bg-rose-50 hover:text-rose-700 text-gray-700 transition-colors">
                                                        {{ $ms->packing_list_no ?: ($ms->nomor_container_atau_plat ?: 'SPV-' . $ms->id) }}
                                                    </a>
                                                @endforeach
                                                @if($audit->missing_shipments->count() > 3)
                                                    <span class="text-[10px] text-gray-400 font-medium">+{{ $audit->missing_shipments->count() - 3 }} lainnya</span>
                                                @endif
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- 7. TAB 3: PRODUKTIVITAS PETUGAS LAPANGAN -->
        <div x-show="activeTab === 'petugas'" x-cloak class="space-y-4">
            <div class="bg-white rounded-2xl shadow-[0_1px_6px_rgba(40,84,145,0.05)] border border-gray-100 p-6">
                <div class="mb-6 pb-4 border-b border-gray-100">
                    <h2 class="text-base font-bold text-gray-900">Rekap Kinerja & Aktivitas Petugas Staging</h2>
                    <p class="text-xs text-gray-500 mt-0.5">Statistik jumlah kontainer yang ditangani serta kepatuhan foto SOP per personil lapangan.</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left whitespace-nowrap">
                        <thead style="background:#fafbfd;">
                            <tr>
                                <th class="px-5 py-3 text-[10px] font-bold text-gray-400 uppercase tracking-wider">Petugas Lapangan</th>
                                <th class="px-5 py-3 text-[10px] font-bold text-gray-400 uppercase tracking-wider">NIK / No. Induk</th>
                                <th class="px-5 py-3 text-[10px] font-bold text-gray-400 uppercase tracking-wider text-center">Total Ditangani</th>
                                <th class="px-5 py-3 text-[10px] font-bold text-gray-400 uppercase tracking-wider text-center">Foto Lengkap (27/27)</th>
                                <th class="px-5 py-3 text-[10px] font-bold text-gray-400 uppercase tracking-wider w-40">Rata-rata Kepatuhan</th>
                                <th class="px-5 py-3 text-[10px] font-bold text-gray-400 uppercase tracking-wider">Produk (Fiber / Sodium)</th>
                                <th class="px-5 py-3 text-[10px] font-bold text-gray-400 uppercase tracking-wider">Tipe (Export / Lokal)</th>
                                <th class="px-5 py-3 text-[10px] font-bold text-gray-400 uppercase tracking-wider text-center">Status Akun</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($petugasProductivity as $stat)
                                <tr style="border-top: 1px solid #f0f4f9;" onmouseover="this.style.background='#fafcff'" onmouseout="this.style.background='transparent'">
                                    <td class="px-5 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-9 h-9 rounded-xl bg-spv-blue text-white flex items-center justify-center font-bold text-xs">
                                                {{ strtoupper(substr($stat->petugas->nama, 0, 2)) }}
                                            </div>
                                            <div>
                                                <p class="text-xs font-bold text-gray-900">{{ $stat->petugas->nama }}</p>
                                                <p class="text-[10px] text-gray-400">Petugas Staging</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-5 py-4 font-mono text-xs text-gray-600">
                                        {{ $stat->petugas->nomor_induk }}
                                    </td>
                                    <td class="px-5 py-4 text-center">
                                        <span class="text-sm font-black text-gray-900">{{ $stat->total_handled }}</span>
                                        <span class="text-[10px] text-gray-400">Kontainer</span>
                                    </td>
                                    <td class="px-5 py-4 text-center">
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            {{ $stat->complete_count }} Unit
                                        </span>
                                    </td>
                                    <td class="px-5 py-4">
                                        <div class="flex items-center gap-2">
                                            <div class="flex-1 bg-gray-100 h-2 rounded-full overflow-hidden">
                                                <div class="bg-emerald-500 h-2 rounded-full" style="width: {{ $stat->avg_compliance }}%;"></div>
                                            </div>
                                            <span class="text-xs font-black text-emerald-700">{{ $stat->avg_compliance }}%</span>
                                        </div>
                                    </td>
                                    <td class="px-5 py-4">
                                        <span class="text-xs text-gray-700">Fiber: <strong>{{ $stat->fiber_count }}</strong></span> &bull; 
                                        <span class="text-xs text-gray-700">Sodium: <strong>{{ $stat->sodium_count }}</strong></span>
                                    </td>
                                    <td class="px-5 py-4">
                                        <span class="text-xs text-gray-700">Export: <strong>{{ $stat->export_count }}</strong></span> &bull; 
                                        <span class="text-xs text-gray-700">Lokal: <strong>{{ $stat->lokal_count }}</strong></span>
                                    </td>
                                    <td class="px-5 py-4 text-center">
                                        @if($stat->petugas->status === 'aktif')
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-green-50 text-green-700 border border-green-200">
                                                Aktif
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-gray-100 text-gray-500">
                                                Nonaktif
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-5 py-10 text-center text-gray-400 text-xs">
                                        Belum ada data aktivitas petugas pada periode ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

</x-layout>
