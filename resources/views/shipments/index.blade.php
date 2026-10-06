<x-layout :title="'Daftar Shipments'">
<div x-data="batchZipDownloadManager()">
    <!-- Header Section Card -->
    <div class="bg-white rounded-2xl sm:rounded-3xl p-4 sm:p-5 lg:p-6 border border-gray-100 shadow-[0_1px_6px_rgba(40,84,145,0.05)] mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold text-gray-800">Daftar Shipments</h1>
                <p class="text-xs text-gray-500 mt-1">Kelola dan pantau semua shipment dari lapangan.</p>
            </div>
            <div class="flex items-center gap-2.5 flex-wrap w-full sm:w-auto">
                <button type="button"
                        @click="openModal()"
                        class="flex items-center justify-center gap-2 bg-white hover:bg-emerald-50 text-emerald-700 hover:text-emerald-800 border border-emerald-200 hover:border-emerald-300 text-xs font-bold px-4 py-2.5 rounded-xl shadow-[0_2px_8px_rgba(5,158,61,0.08)] hover:-translate-y-0.5 transition-all group flex-1 sm:flex-none">
                    <i class="ph-bold ph-file-zip text-base text-emerald-600 group-hover:scale-110 transition-transform"></i>
                    <span>Download Batch ZIP</span>
                </button>
                <a href="{{ route('field-app.create') }}" target="_blank"
                   class="flex items-center justify-center gap-2 bg-spv-blue hover:bg-blue-800 text-white text-xs font-bold px-4 py-2.5 rounded-xl shadow-[0_4px_12px_rgba(40,84,145,0.25)] hover:-translate-y-0.5 transition-all flex-1 sm:flex-none">
                    <i class="ph-bold ph-plus text-sm"></i>
                    Buat Shipment
                </a>
            </div>
        </div>
    </div>

    <!-- Main Table Card -->
    <div class="bg-white rounded-2xl shadow-[0_1px_6px_rgba(40,84,145,0.05)] border border-gray-100 overflow-hidden">
        
        <!-- Toolbar & Search -->
        <form method="GET" action="{{ route('shipments.index') }}" class="p-5 flex flex-col md:flex-row md:items-center justify-between gap-4" style="border-bottom: 1px solid #f0f4f9;">
            <div class="relative w-full md:w-80">
                <i class="ph-bold ph-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari No. Packing List / Plat / Container / Sopir..." 
                       class="w-full pl-9 pr-4 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs focus:ring-2 focus:ring-spv-blue focus:border-spv-blue outline-none transition-all">
            </div>
            
            <div class="flex items-center gap-2 overflow-x-auto pb-1 md:pb-0">
                <a href="{{ route('shipments.index') }}" class="text-[10px] font-bold px-4 py-2 rounded-full whitespace-nowrap {{ !request('type') && !request('status') ? 'bg-spv-blue text-white shadow-sm' : 'text-gray-500 border border-gray-200 hover:bg-gray-50' }}">Semua</a>
                <a href="{{ route('shipments.index', ['type' => 'export']) }}" class="text-[10px] font-semibold px-4 py-2 rounded-full whitespace-nowrap {{ request('type') === 'export' ? 'bg-spv-blue text-white shadow-sm' : 'text-gray-500 border border-gray-200 hover:bg-gray-50' }}">Export</a>
                <a href="{{ route('shipments.index', ['type' => 'lokal']) }}" class="text-[10px] font-semibold px-4 py-2 rounded-full whitespace-nowrap {{ request('type') === 'lokal' ? 'bg-spv-blue text-white shadow-sm' : 'text-gray-500 border border-gray-200 hover:bg-gray-50' }}">Lokal</a>
                <a href="{{ route('shipments.index', ['status' => 'draft']) }}" class="text-[10px] font-semibold px-4 py-2 rounded-full whitespace-nowrap border {{ request('status') === 'draft' ? 'bg-amber-500 text-white border-amber-500 shadow-sm' : 'text-amber-600 border-amber-200 hover:bg-amber-50' }}">Draft</a>
                <a href="{{ route('shipments.index', ['status' => 'submitted']) }}" class="text-[10px] font-semibold px-4 py-2 rounded-full whitespace-nowrap border {{ request('status') === 'submitted' ? 'bg-green-600 text-white border-green-600 shadow-sm' : 'text-green-600 border-green-200 hover:bg-green-50' }}">Submitted</a>
            </div>
        </form>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-left whitespace-nowrap">
                <thead style="background:#fafbfd;">
                    <tr>
                        <th class="px-5 py-3 text-[10px] font-bold text-gray-400 uppercase tracking-wider">No. Packing List / Identitas</th>
                        <th class="px-5 py-3 text-[10px] font-bold text-gray-400 uppercase tracking-wider">Produk</th>
                        <th class="px-5 py-3 text-[10px] font-bold text-gray-400 uppercase tracking-wider">Tipe</th>
                        <th class="px-5 py-3 text-[10px] font-bold text-gray-400 uppercase tracking-wider">Petugas</th>
                        <th class="px-5 py-3 text-[10px] font-bold text-gray-400 uppercase tracking-wider">Waktu Staging</th>
                        <th class="px-5 py-3 text-[10px] font-bold text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="px-5 py-3 text-[10px] font-bold text-gray-400 uppercase tracking-wider text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($shipments as $shipment)
                        <tr style="border-top: 1px solid #f0f4f9;" onmouseover="this.style.background='#fafcff'" onmouseout="this.style.background='transparent'">
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0" style="background:#eff4fc;">
                                        <i class="ph-fill {{ $shipment->jenis_pengiriman === 'export' ? 'ph-shipping-container' : 'ph-truck' }} text-base" style="color:#285491;"></i>
                                    </div>
                                    <div>
                                        <a href="{{ route('shipments.show', $shipment->id) }}" class="text-xs font-bold text-gray-800 hover:text-spv-blue transition-colors">
                                            {{ $shipment->packing_list_no ?: ($shipment->nomor_container_atau_plat ?? 'SPV-' . $shipment->id) }}
                                        </a>
                                        <p class="text-[10px] text-gray-400">Cont: {{ $shipment->nomor_container_atau_plat ?? '-' }} &bull; Plat: {{ $shipment->plat_nomor ?? ($shipment->shipment_no ?? '—') }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-3.5"><p class="text-xs text-gray-600 capitalize font-medium">{{ $shipment->jenis_produk }}</p></td>
                            <td class="px-5 py-3.5">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold capitalize {{ $shipment->jenis_pengiriman === 'export' ? 'bg-purple-50 text-purple-700' : 'bg-gray-100 text-gray-600' }}">
                                    {{ $shipment->jenis_pengiriman }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5"><p class="text-xs text-gray-600">{{ $shipment->allKaryawans()->pluck('nama')->join(', ') ?: ($shipment->karyawan?->nama ?? '-') }}</p></td>
                            <td class="px-5 py-3.5"><p class="text-[10px] text-gray-500 font-medium">{{ $shipment->created_at->format('d M Y, H:i') }} WIB</p></td>
                            <td class="px-5 py-3.5">
                                @if($shipment->status === 'submitted')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-green-50 text-green-700 border border-green-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-green-500 animate-pulse"></span>
                                        Submitted
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                        Draft
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('shipments.show', $shipment->id) }}"
                                       title="Lihat Detail Seluruh Data"
                                       class="p-1.5 rounded-lg transition-all text-gray-400 hover:text-spv-blue hover:bg-blue-50">
                                       <i class="ph-bold ph-eye text-base"></i>
                                    </a>
                                    <a href="{{ route('shipments.download-evidence', $shipment->id) }}"
                                       title="Download Semua Foto (ZIP: {{ $shipment->packing_list_no ?: $shipment->nomor_container_atau_plat }}.zip)"
                                       class="p-1.5 rounded-lg transition-all text-gray-400 hover:text-emerald-600 hover:bg-emerald-50">
                                        <i class="ph-bold ph-file-zip text-base"></i>
                                    </a>
                                    <a href="{{ route('shipments.download-pdf', $shipment->id) }}"
                                       title="Download Laporan PDF Lengkap ({{ $shipment->packing_list_no ?: $shipment->nomor_container_atau_plat }}_Report.pdf)"
                                       class="p-1.5 rounded-lg transition-all text-gray-400 hover:text-red-600 hover:bg-red-50">
                                        <i class="ph-bold ph-file-pdf text-base"></i>
                                    </a>
                                    <a href="{{ route('field-app.timeline', $shipment->id) }}"
                                       title="Buka Loading Evidence"
                                       target="_blank"
                                       class="p-1.5 rounded-lg transition-all text-gray-400 hover:text-spv-green hover:bg-emerald-50">
                                        <i class="ph-bold ph-camera text-base"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-12">
                                <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-3 text-gray-400">
                                    <i class="ph-bold ph-package text-2xl"></i>
                                </div>
                                <p class="text-xs font-bold text-gray-700">Belum ada shipment terdaftar</p>
                                <p class="text-[11px] text-gray-400 mt-1">Gunakan tombol <strong>Buat Shipment</strong> di atas untuk membuat staging baru.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($shipments->hasPages())
            <div class="p-4 flex items-center justify-between border-t border-gray-100">
                <p class="text-[10px] text-gray-500 font-medium">
                    Menampilkan {{ $shipments->firstItem() ?? 0 }}-{{ $shipments->lastItem() ?? 0 }} dari {{ $shipments->total() }} shipments
                </p>
                <div class="flex items-center gap-1">
                    @if($shipments->onFirstPage())
                        <span class="w-7 h-7 flex items-center justify-center rounded-lg text-gray-300 border border-gray-100"><i class="ph-bold ph-caret-left"></i></span>
                    @else
                        <a href="{{ $shipments->previousPageUrl() }}" class="w-7 h-7 flex items-center justify-center rounded-lg text-gray-600 hover:bg-gray-100 border border-gray-200"><i class="ph-bold ph-caret-left"></i></a>
                    @endif

                    <span class="text-xs font-bold px-3 py-1 bg-spv-blue text-white rounded-lg">{{ $shipments->currentPage() }}</span>

                    @if($shipments->hasMorePages())
                        <a href="{{ $shipments->nextPageUrl() }}" class="w-7 h-7 flex items-center justify-center rounded-lg text-gray-600 hover:bg-gray-100 border border-gray-200"><i class="ph-bold ph-caret-right"></i></a>
                    @else
                        <span class="w-7 h-7 flex items-center justify-center rounded-lg text-gray-300 border border-gray-100"><i class="ph-bold ph-caret-right"></i></span>
                    @endif
                </div>
            </div>
        @else
            <div class="p-4 border-t border-gray-100 text-[10px] text-gray-400">
                Total {{ $shipments->total() }} shipment terdaftar
            </div>
        @endif
    </div>

    <!-- MODAL DOWNLOAD BATCH ZIP -->
    <div x-cloak
         x-show="isOpen"
         x-transition:enter="transition ease-out duration-250"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center px-4 py-6 sm:py-10"
         @keydown.escape.window="isOpen = false">

        <div class="bg-white rounded-3xl shadow-2xl border border-gray-100 w-full max-w-xl max-h-[86vh] sm:max-h-[84vh] flex flex-col overflow-hidden my-auto transform transition-all"
             x-transition:enter="transition ease-out duration-250"
             x-transition:enter-start="opacity-0 scale-95 translate-y-3"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
             x-transition:leave-end="opacity-0 scale-95 translate-y-3"
             @click.away="isOpen = false">

            <!-- Modal Header -->
            <div class="shrink-0 px-6 py-4 bg-gradient-to-r from-slate-50 via-white to-blue-50/40 border-b border-gray-100 flex items-center justify-between">
                <div class="flex items-center gap-3.5">
                    <div class="w-10 h-10 rounded-2xl bg-emerald-500/10 text-emerald-600 flex items-center justify-center text-lg sm:text-xl font-bold shadow-sm ring-1 ring-emerald-500/20 shrink-0">
                        <i class="ph-bold ph-archive"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-gray-800">Download Arsip Evidence (Batch ZIP)</h3>
                        <p class="text-[11px] text-gray-500 mt-0.5">Filter dan unduh kumpulan evidence terstruktur per folder tanggal & packing list.</p>
                    </div>
                </div>
                <button type="button" @click="isOpen = false" class="text-gray-400 hover:text-gray-600 p-2 rounded-xl hover:bg-gray-100 transition-colors">
                    <i class="ph-bold ph-x text-base"></i>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="flex-1 overflow-y-auto p-5 sm:p-6 space-y-4">

                <!-- 1. Pilihan Periode Mode Tabs -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1.5">Pilih Periode Download</label>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-1.5 p-1 bg-gray-100/80 rounded-2xl border border-gray-200/60">
                        <button type="button"
                                @click="setMode('harian')"
                                :class="mode === 'harian' ? 'bg-spv-blue text-white shadow-sm font-bold' : 'text-gray-600 hover:text-gray-800 font-medium'"
                                class="py-2 text-[11px] rounded-xl transition-all flex items-center justify-center gap-1.5">
                            <i class="ph-bold ph-calendar-check"></i>
                            <span>Harian</span>
                        </button>
                        <button type="button"
                                @click="setMode('range')"
                                :class="mode === 'range' ? 'bg-spv-blue text-white shadow-sm font-bold' : 'text-gray-600 hover:text-gray-800 font-medium'"
                                class="py-2 text-[11px] rounded-xl transition-all flex items-center justify-center gap-1.5">
                            <i class="ph-bold ph-calendar-blank"></i>
                            <span>Rentang</span>
                        </button>
                        <button type="button"
                                @click="setMode('bulanan')"
                                :class="mode === 'bulanan' ? 'bg-spv-blue text-white shadow-sm font-bold' : 'text-gray-600 hover:text-gray-800 font-medium'"
                                class="py-2 text-[11px] rounded-xl transition-all flex items-center justify-center gap-1.5">
                            <i class="ph-bold ph-calendar"></i>
                            <span>Bulanan</span>
                        </button>
                        <button type="button"
                                @click="setMode('tahunan')"
                                :class="mode === 'tahunan' ? 'bg-spv-blue text-white shadow-sm font-bold' : 'text-gray-600 hover:text-gray-800 font-medium'"
                                class="py-2 text-[11px] rounded-xl transition-all flex items-center justify-center gap-1.5">
                            <i class="ph-bold ph-calendar-star"></i>
                            <span>Tahunan</span>
                        </button>
                    </div>
                </div>

                <!-- 2. Form Kontrol Berdasarkan Mode -->
                <!-- A. MODE HARIAN -->
                <div x-show="mode === 'harian'" class="space-y-2.5 bg-slate-50/70 p-3.5 rounded-2xl border border-gray-200/70">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-bold text-gray-700 flex items-center gap-1.5">
                            <i class="ph-bold ph-clock text-spv-blue"></i>
                            Pilih Tanggal Spesifik
                        </span>
                        <div class="flex items-center gap-1.5">
                            <button type="button" @click="setToday()" class="text-[10px] font-bold px-2.5 py-1 rounded-lg bg-white border border-gray-200 hover:border-spv-blue hover:text-spv-blue text-gray-600 transition-all shadow-2xs">Hari Ini</button>
                            <button type="button" @click="setYesterday()" class="text-[10px] font-bold px-2.5 py-1 rounded-lg bg-white border border-gray-200 hover:border-spv-blue hover:text-spv-blue text-gray-600 transition-all shadow-2xs">Kemarin</button>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5">
                        <!-- Dropdown Tahun -->
                        <div>
                            <label class="block text-[10px] font-semibold text-gray-500 mb-1">Tahun</label>
                            <select x-model.number="year" @change="onDropdownDateChanged()" class="w-full bg-white border border-gray-200 rounded-xl px-3 py-2 text-xs font-semibold text-gray-700 focus:ring-2 focus:ring-spv-blue focus:border-spv-blue outline-none transition-all">
                                <template x-for="y in yearsList" :key="y">
                                    <option :value="y" x-text="y"></option>
                                </template>
                            </select>
                        </div>

                        <!-- Dropdown Bulan -->
                        <div>
                            <label class="block text-[10px] font-semibold text-gray-500 mb-1">Bulan</label>
                            <select x-model.number="month" @change="onDropdownDateChanged()" class="w-full bg-white border border-gray-200 rounded-xl px-3 py-2 text-xs font-semibold text-gray-700 focus:ring-2 focus:ring-spv-blue focus:border-spv-blue outline-none transition-all">
                                <template x-for="m in monthsList" :key="m.num">
                                    <option :value="m.num" x-text="m.name"></option>
                                </template>
                            </select>
                        </div>

                        <!-- Dropdown Hari (1 - 31) -->
                        <div>
                            <label class="block text-[10px] font-semibold text-gray-500 mb-1">Hari / Tanggal</label>
                            <select x-model.number="day" @change="onDropdownDateChanged()" class="w-full bg-white border border-gray-200 rounded-xl px-3 py-2 text-xs font-semibold text-gray-700 focus:ring-2 focus:ring-spv-blue focus:border-spv-blue outline-none transition-all">
                                <template x-for="d in daysInCurrentMonth()" :key="d">
                                    <option :value="d" x-text="d"></option>
                                </template>
                            </select>
                        </div>
                    </div>

                    <div class="pt-1">
                        <label class="block text-[10px] font-semibold text-gray-400 mb-1">Atau pilih via Date Picker langsung:</label>
                        <input type="date" x-model="date" @change="onDateInputChanged()"
                               class="w-full bg-white border border-gray-200 rounded-xl px-3 py-1.5 text-xs font-medium text-gray-700 focus:ring-2 focus:ring-spv-blue focus:border-spv-blue outline-none transition-all">
                    </div>
                </div>

                <!-- B. MODE RENTANG / WEEKS -->
                <div x-show="mode === 'range'" class="space-y-2.5 bg-slate-50/70 p-3.5 rounded-2xl border border-gray-200/70">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-bold text-gray-700 flex items-center gap-1.5">
                            <i class="ph-bold ph-calendar-blank text-spv-blue"></i>
                            Pilih Rentang Tanggal / Mingguan
                        </span>
                    </div>

                    <div class="flex items-center gap-1.5 flex-wrap">
                        <button type="button" @click="setThisWeek()" class="text-[10px] font-bold px-2.5 py-1 rounded-lg bg-white border border-gray-200 hover:border-spv-blue hover:text-spv-blue text-gray-600 transition-all shadow-2xs">
                            Senin - Jumat Minggu Ini
                        </button>
                        <button type="button" @click="setLast7Days()" class="text-[10px] font-bold px-2.5 py-1 rounded-lg bg-white border border-gray-200 hover:border-spv-blue hover:text-spv-blue text-gray-600 transition-all shadow-2xs">
                            7 Hari Terakhir
                        </button>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
                        <div>
                            <label class="block text-[10px] font-semibold text-gray-500 mb-1">Dari Tanggal (Mulai)</label>
                            <input type="date" x-model="startDate" @change="fetchPreview()"
                                   class="w-full bg-white border border-gray-200 rounded-xl px-3 py-2 text-xs font-semibold text-gray-700 focus:ring-2 focus:ring-spv-blue focus:border-spv-blue outline-none transition-all">
                        </div>
                        <div>
                            <label class="block text-[10px] font-semibold text-gray-500 mb-1">Sampai Tanggal (Selesai)</label>
                            <input type="date" x-model="endDate" @change="fetchPreview()"
                                   class="w-full bg-white border border-gray-200 rounded-xl px-3 py-2 text-xs font-semibold text-gray-700 focus:ring-2 focus:ring-spv-blue focus:border-spv-blue outline-none transition-all">
                        </div>
                    </div>
                </div>

                <!-- C. MODE BULANAN -->
                <div x-show="mode === 'bulanan'" class="space-y-2.5 bg-slate-50/70 p-3.5 rounded-2xl border border-gray-200/70">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-bold text-gray-700 flex items-center gap-1.5">
                            <i class="ph-bold ph-calendar text-spv-blue"></i>
                            Pilih Bulan dan Tahun
                        </span>
                        <button type="button" @click="setThisMonth()" class="text-[10px] font-bold px-2.5 py-1 rounded-lg bg-white border border-gray-200 hover:border-spv-blue hover:text-spv-blue text-gray-600 transition-all shadow-2xs">
                            Bulan Ini
                        </button>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[10px] font-semibold text-gray-500 mb-1">Tahun</label>
                            <select x-model.number="year" @change="fetchPreview()" class="w-full bg-white border border-gray-200 rounded-xl px-3 py-2 text-xs font-semibold text-gray-700 focus:ring-2 focus:ring-spv-blue focus:border-spv-blue outline-none transition-all">
                                <template x-for="y in yearsList" :key="y">
                                    <option :value="y" x-text="y"></option>
                                </template>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] font-semibold text-gray-500 mb-1">Bulan</label>
                            <select x-model.number="month" @change="fetchPreview()" class="w-full bg-white border border-gray-200 rounded-xl px-3 py-2 text-xs font-semibold text-gray-700 focus:ring-2 focus:ring-spv-blue focus:border-spv-blue outline-none transition-all">
                                <template x-for="m in monthsList" :key="m.num">
                                    <option :value="m.num" x-text="m.name"></option>
                                </template>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- D. MODE TAHUNAN -->
                <div x-show="mode === 'tahunan'" class="space-y-2.5 bg-slate-50/70 p-3.5 rounded-2xl border border-gray-200/70">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-bold text-gray-700 flex items-center gap-1.5">
                            <i class="ph-bold ph-calendar-star text-spv-blue"></i>
                            Pilih Tahun Penuh (Seluruh Bulan)
                        </span>
                        <button type="button" @click="setThisYear()" class="text-[10px] font-bold px-2.5 py-1 rounded-lg bg-white border border-gray-200 hover:border-spv-blue hover:text-spv-blue text-gray-600 transition-all shadow-2xs">
                            Tahun Ini
                        </button>
                    </div>

                    <div>
                        <label class="block text-[10px] font-semibold text-gray-500 mb-1">Tahun Arsip</label>
                        <select x-model.number="year" @change="fetchPreview()" class="w-full bg-white border border-gray-200 rounded-xl px-3 py-2 text-xs font-semibold text-gray-700 focus:ring-2 focus:ring-spv-blue focus:border-spv-blue outline-none transition-all">
                            <template x-for="y in yearsList" :key="y">
                                <option :value="y" x-text="y"></option>
                            </template>
                        </select>
                    </div>
                </div>

                <!-- 3. Filter Jenis Produk (Fiber / Sodium / Semua) -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1.5">Filter Jenis Produk</label>
                    <div class="grid grid-cols-3 gap-2">
                        <button type="button"
                                @click="product = 'all'; fetchPreview()"
                                :class="product === 'all' ? 'border-spv-blue bg-blue-50/50 text-spv-blue font-bold ring-1 ring-spv-blue' : 'border-gray-200 bg-white text-gray-600 hover:bg-gray-50 font-medium'"
                                class="p-2 rounded-xl border text-xs text-center transition-all flex flex-col items-center gap-1">
                            <i class="ph-bold ph-stack text-sm"></i>
                            <span>Semua Produk</span>
                        </button>

                        <button type="button"
                                @click="product = 'fiber'; fetchPreview()"
                                :class="product === 'fiber' ? 'border-spv-blue bg-blue-50/50 text-spv-blue font-bold ring-1 ring-spv-blue' : 'border-gray-200 bg-white text-gray-600 hover:bg-gray-50 font-medium'"
                                class="p-2 rounded-xl border text-xs text-center transition-all flex flex-col items-center gap-1">
                            <i class="ph-bold ph-cube text-sm"></i>
                            <span>Fiber</span>
                        </button>

                        <button type="button"
                                @click="product = 'sodium'; fetchPreview()"
                                :class="product === 'sodium' ? 'border-spv-blue bg-blue-50/50 text-spv-blue font-bold ring-1 ring-spv-blue' : 'border-gray-200 bg-white text-gray-600 hover:bg-gray-50 font-medium'"
                                class="p-2 rounded-xl border text-xs text-center transition-all flex flex-col items-center gap-1">
                            <i class="ph-bold ph-flask text-sm"></i>
                            <span>Sodium</span>
                        </button>
                    </div>
                </div>

                <!-- 4. Pilihan Kualitas / Kompresi Foto -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1.5">Kualitas File Bukti</label>
                    <div class="grid grid-cols-2 gap-2">
                        <label class="flex items-start gap-2.5 p-2 sm:p-2.5 rounded-xl border cursor-pointer transition-all"
                               :class="quality === 'compressed' ? 'border-emerald-500 bg-emerald-50/30 ring-1 ring-emerald-500' : 'border-gray-200 bg-white hover:bg-gray-50'">
                            <input type="radio" name="zip_quality" value="compressed" x-model="quality" @change="fetchPreview()" class="mt-0.5 text-emerald-600 focus:ring-emerald-500">
                            <div>
                                <span class="block text-xs font-bold text-gray-800">Terkonversi Cepat</span>
                                <span class="block text-[10px] text-gray-500">~5MB/shipment (Rekomendasi, hemat & cepat)</span>
                            </div>
                        </label>

                        <label class="flex items-start gap-2.5 p-2 sm:p-2.5 rounded-xl border cursor-pointer transition-all"
                               :class="quality === 'original' ? 'border-spv-blue bg-blue-50/30 ring-1 ring-spv-blue' : 'border-gray-200 bg-white hover:bg-gray-50'">
                            <input type="radio" name="zip_quality" value="original" x-model="quality" @change="fetchPreview()" class="mt-0.5 text-spv-blue focus:ring-spv-blue">
                            <div>
                                <span class="block text-xs font-bold text-gray-800">Resolusi Asli</span>
                                <span class="block text-[10px] text-gray-500">Ukuran asli mentah (~30-40MB/shipment)</span>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- 5. Visual Box Struktur Folder di Dalam ZIP -->
                <div class="rounded-2xl bg-slate-900 text-slate-200 p-3 sm:p-3.5 text-xs font-mono shadow-inner border border-slate-800">
                    <div class="flex items-center justify-between text-[11px] text-slate-400 pb-2 border-b border-slate-800 mb-2">
                        <span class="flex items-center gap-1.5 font-sans font-semibold text-slate-300">
                            <i class="ph-bold ph-tree-structure text-emerald-400"></i>
                            Struktur Folder di Dalam File ZIP:
                        </span>
                        <span class="text-[10px] text-slate-400 truncate max-w-[200px]" x-text="stats.filename"></span>
                    </div>
                    <div class="space-y-0.5 text-[11px] leading-relaxed">
                        <div class="text-amber-300">📁 <span x-text="stats.filename"></span></div>
                        <div class="pl-3.5 text-purple-300">└── 📁 <span x-text="previewFolderProduct()"></span>/</div>
                        <div class="pl-7 text-emerald-300">└── 📁 <span x-text="previewFolderYear()"></span>/</div>
                        <div class="pl-10.5 text-sky-300">└── 📁 <span x-text="previewFolderMonth()"></span>/</div>
                        <div class="pl-14 text-indigo-300">└── 📁 <span x-text="previewFolderDay()"></span>/</div>
                        <div class="pl-17.5 text-emerald-200">├── 🗜️ [No_PackingList_1].zip</div>
                        <div class="pl-17.5 text-emerald-200">└── 🗜️ [No_PackingList_2].zip</div>
                    </div>
                    <p class="text-[10px] text-slate-400 mt-2 pt-1.5 border-t border-slate-800/80 font-sans">
                        Setiap file ZIP di dalam folder hari berisi seluruh foto evidence shipment terkait.
                    </p>
                </div>

                <!-- 6. Indikator Status & Ketersediaan Data -->
                <div class="p-2.5 sm:p-3 rounded-2xl border transition-all"
                     :class="stats.count > 0 ? 'bg-emerald-50/70 border-emerald-200 text-emerald-800' : 'bg-amber-50/70 border-amber-200 text-amber-800'">
                    <div class="flex items-center justify-between gap-3">
                        <div class="flex items-center gap-2">
                            <template x-if="isLoading">
                                <span class="animate-spin text-sm text-gray-500"><i class="ph-bold ph-spinner"></i></span>
                            </template>
                            <template x-if="!isLoading && stats.count > 0">
                                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                            </template>
                            <template x-if="!isLoading && stats.count === 0">
                                <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                            </template>

                            <span class="text-xs font-semibold">
                                <template x-if="isLoading">
                                    <span>Memeriksa ketersediaan data shipment...</span>
                                </template>
                                <template x-if="!isLoading && stats.count > 0">
                                    <span>
                                        Ditemukan <strong class="font-bold text-emerald-900" x-text="stats.count"></strong> shipment siap diunduh.
                                    </span>
                                </template>
                                <template x-if="!isLoading && stats.count === 0">
                                    <span>Tidak ada shipment dengan bukti foto pada kriteria filter ini.</span>
                                </template>
                            </span>
                        </div>

                        <template x-if="!isLoading && stats.count > 0">
                            <span class="text-[11px] font-bold px-2 py-0.5 rounded-md bg-white border border-emerald-200 text-emerald-700 shadow-2xs whitespace-nowrap">
                                Estimasi <span x-text="stats.estimated_size"></span>
                            </span>
                        </template>
                    </div>
                </div>

            </div>

            <!-- Modal Footer -->
            <div class="shrink-0 px-6 py-3.5 bg-gray-50/80 border-t border-gray-100 flex items-center justify-between gap-3">
                <button type="button"
                        @click="isOpen = false"
                        class="px-4 py-2.5 rounded-xl border border-gray-200 bg-white hover:bg-gray-50 text-xs font-bold text-gray-600 transition-all shadow-2xs">
                    Batal
                </button>

                <button type="button"
                        @click="triggerDownload()"
                        :disabled="stats.count === 0 || isLoading || isDownloading"
                        :class="stats.count > 0 && !isDownloading ? 'bg-spv-blue hover:bg-blue-800 text-white shadow-[0_4px_12px_rgba(40,84,145,0.25)] hover:-translate-y-0.5' : 'bg-gray-200 text-gray-400 cursor-not-allowed'"
                        class="flex items-center gap-2 px-5 py-2.5 rounded-xl text-xs font-bold transition-all">
                    <template x-if="isDownloading">
                        <span class="flex items-center gap-2">
                            <span class="animate-spin"><i class="ph-bold ph-spinner text-sm"></i></span>
                            <span>Menyiapkan File ZIP...</span>
                        </span>
                    </template>
                    <template x-if="!isDownloading">
                        <span class="flex items-center gap-2">
                            <i class="ph-bold ph-download-simple text-sm"></i>
                            <span>Download File ZIP</span>
                        </span>
                    </template>
                </button>
            </div>

        </div>
    </div>
</div>

<script>
function batchZipDownloadManager() {
    const today = new Date();
    const curYear = today.getFullYear();
    const curMonth = today.getMonth() + 1;
    const curDay = today.getDate();
    const pad = (n) => String(n).padStart(2, '0');
    const todayStr = `${curYear}-${pad(curMonth)}-${pad(curDay)}`;

    // Hitung Senin & Jumat minggu ini
    const dayOfWeek = today.getDay();
    const diffToMon = (dayOfWeek === 0 ? -6 : 1) - dayOfWeek;
    const mon = new Date(today);
    mon.setDate(today.getDate() + diffToMon);
    const fri = new Date(mon);
    fri.setDate(mon.getDate() + 4);

    const monStr = `${mon.getFullYear()}-${pad(mon.getMonth() + 1)}-${pad(mon.getDate())}`;
    const friStr = `${fri.getFullYear()}-${pad(fri.getMonth() + 1)}-${pad(fri.getDate())}`;

    return {
        isOpen: false,
        isLoading: false,
        isDownloading: false,
        mode: 'harian',
        year: curYear,
        month: curMonth,
        day: curDay,
        date: todayStr,
        startDate: monStr,
        endDate: friStr,
        product: 'all',
        quality: 'compressed',

        yearsList: [curYear + 1, curYear, curYear - 1, curYear - 2, curYear - 3],
        monthsList: [
            { num: 1, name: 'Januari', code: 'JANUARI' },
            { num: 2, name: 'Februari', code: 'FEBRUARI' },
            { num: 3, name: 'Maret', code: 'MARET' },
            { num: 4, name: 'April', code: 'APRIL' },
            { num: 5, name: 'Mei', code: 'MEI' },
            { num: 6, name: 'Juni', code: 'JUNI' },
            { num: 7, name: 'Juli', code: 'JULI' },
            { num: 8, name: 'Agustus', code: 'AGUSTUS' },
            { num: 9, name: 'September', code: 'SEPTEMBER' },
            { num: 10, name: 'Oktober', code: 'OKTOBER' },
            { num: 11, name: 'November', code: 'NOVEMBER' },
            { num: 12, name: 'Desember', code: 'DESEMBER' }
        ],

        stats: {
            count: 0,
            sample_path: `${curYear}/SEPTEMBER/1/PL-SAMPLE.zip`,
            estimated_size: '0 MB',
            filename: `SPV_Evidence_Harian_${curYear}.zip`
        },

        previewTimer: null,

        init() {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('modal') === 'batch_zip' || urlParams.get('batch_zip') === '1') {
                this.openModal();
            }
        },

        openModal() {
            this.isOpen = true;
            this.fetchPreview();
        },

        setMode(newMode) {
            this.mode = newMode;
            this.fetchPreview();
        },

        daysInCurrentMonth() {
            const numDays = new Date(this.year, this.month, 0).getDate();
            const days = [];
            for (let i = 1; i <= numDays; i++) {
                days.push(i);
            }
            return days;
        },

        onDateInputChanged() {
            if (this.date) {
                const parts = this.date.split('-');
                if (parts.length === 3) {
                    this.year = parseInt(parts[0], 10);
                    this.month = parseInt(parts[1], 10);
                    this.day = parseInt(parts[2], 10);
                }
            }
            this.fetchPreview();
        },

        onDropdownDateChanged() {
            this.date = `${this.year}-${pad(this.month)}-${pad(this.day)}`;
            this.fetchPreview();
        },

        setToday() {
            this.date = todayStr;
            this.year = curYear;
            this.month = curMonth;
            this.day = curDay;
            this.mode = 'harian';
            this.fetchPreview();
        },

        setYesterday() {
            const yest = new Date(today);
            yest.setDate(today.getDate() - 1);
            this.date = `${yest.getFullYear()}-${pad(yest.getMonth() + 1)}-${pad(yest.getDate())}`;
            this.year = yest.getFullYear();
            this.month = yest.getMonth() + 1;
            this.day = yest.getDate();
            this.mode = 'harian';
            this.fetchPreview();
        },

        setThisWeek() {
            this.startDate = monStr;
            this.endDate = friStr;
            this.mode = 'range';
            this.fetchPreview();
        },

        setLast7Days() {
            const d = new Date(today);
            d.setDate(today.getDate() - 6);
            this.startDate = `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`;
            this.endDate = todayStr;
            this.mode = 'range';
            this.fetchPreview();
        },

        setThisMonth() {
            this.year = curYear;
            this.month = curMonth;
            this.mode = 'bulanan';
            this.fetchPreview();
        },

        setThisYear() {
            this.year = curYear;
            this.mode = 'tahunan';
            this.fetchPreview();
        },

        getQueryParams() {
            const params = new URLSearchParams();
            params.set('mode', this.mode);
            params.set('jenis_produk', this.product);
            params.set('quality', this.quality);

            if (this.mode === 'harian') {
                params.set('date', this.date);
                params.set('year', this.year);
                params.set('month', this.month);
                params.set('day', this.day);
            } else if (this.mode === 'range') {
                params.set('start_date', this.startDate);
                params.set('end_date', this.endDate);
            } else if (this.mode === 'bulanan') {
                params.set('year', this.year);
                params.set('month', this.month);
            } else if (this.mode === 'tahunan') {
                params.set('year', this.year);
            }

            return params;
        },

        fetchPreview() {
            clearTimeout(this.previewTimer);
            this.previewTimer = setTimeout(() => {
                this.isLoading = true;
                const params = this.getQueryParams();
                params.set('format', 'json');
                fetch(`{{ route('shipments.batch-zip-preview') }}?${params.toString()}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                    .then(res => res.json())
                    .then(data => {
                        this.stats = data;
                        this.isLoading = false;
                    })
                    .catch(() => {
                        this.isLoading = false;
                    });
            }, 120);
        },

        previewFolderProduct() {
            if (this.product === 'fiber') return 'FIBER';
            if (this.product === 'sodium') return 'SODIUM';
            return 'FIBER|SODIUM';
        },

        previewFolderYear() {
            return this.year || curYear;
        },

        previewFolderMonth() {
            if (this.mode === 'tahunan') {
                return '{BULAN}';
            }
            const m = this.monthsList.find(item => item.num === parseInt(this.month, 10));
            return m ? m.code : 'SEPTEMBER';
        },

        previewFolderDay() {
            if (this.mode === 'tahunan' || this.mode === 'bulanan') {
                return '{hari}';
            }
            return parseInt(this.day, 10) || '1';
        },

        triggerDownload() {
            if (this.isDownloading) return;
            this.isDownloading = true;

            const params = this.getQueryParams();
            const downloadUrl = `{{ route('shipments.download-batch-zip') }}?${params.toString()}`;

            window.location.href = downloadUrl;

            setTimeout(() => {
                this.isDownloading = false;
            }, 3000);
        }
    };
}
</script>
</x-layout>
