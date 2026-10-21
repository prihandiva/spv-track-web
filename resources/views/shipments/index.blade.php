<x-layout :title="'Daftar Shipments'">
<div x-data="batchZipDownloadManager()">
    <!-- Header Section Card -->
    <div class="bg-white rounded-2xl sm:rounded-3xl p-4 sm:p-5 lg:p-6 border border-gray-100 shadow-[0_1px_6px_rgba(40,84,145,0.05)] mb-4 sm:mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3.5 sm:gap-4">
            <div>
                <h1 class="text-lg sm:text-xl font-bold text-gray-800">Daftar Shipments</h1>
                <p class="text-xs text-gray-500 mt-0.5 sm:mt-1">Kelola dan pantau semua shipment dari lapangan.</p>
            </div>
            <div class="flex flex-col-reverse sm:flex-row sm:items-center gap-2 sm:gap-2.5 w-full sm:w-auto">
                @if(auth()->user()?->canDownloadReports())
                <button type="button"
                        @click="openModal()"
                        class="flex items-center justify-center gap-2 bg-white hover:bg-emerald-50 text-emerald-700 hover:text-emerald-800 border border-emerald-200 hover:border-emerald-300 text-xs font-bold px-4 py-2.5 rounded-xl shadow-[0_2px_8px_rgba(5,158,61,0.08)] hover:-translate-y-0.5 transition-all group w-full sm:w-auto">
                    <i class="ph-bold ph-file-zip text-base text-emerald-600 group-hover:scale-110 transition-transform"></i>
                    <span>Download Batch ZIP</span>
                </button>
                @else
                <button type="button" disabled
                        class="flex items-center justify-center gap-2 bg-gray-100 text-gray-400 border border-gray-200 text-xs font-bold px-4 py-2.5 rounded-xl cursor-not-allowed opacity-60 w-full sm:w-auto select-none"
                        title="Download Batch ZIP hanya tersedia untuk role Admin dan Superadmin.">
                    <i class="ph-bold ph-file-zip text-base"></i>
                    <span>Download Batch ZIP</span>
                </button>
                @endif

                @if(auth()->user()?->canCreateShipment())
                <a href="{{ route('field-app.create') }}" target="_blank"
                   class="flex items-center justify-center gap-2 bg-spv-blue hover:bg-blue-800 text-white text-xs font-bold px-4 py-2.5 rounded-xl shadow-[0_4px_12px_rgba(40,84,145,0.25)] hover:-translate-y-0.5 transition-all w-full sm:w-auto">
                    <i class="ph-bold ph-plus text-sm"></i>
                    <span>Buat Shipment</span>
                </a>
                @else
                <button type="button" disabled
                        class="flex items-center justify-center gap-2 bg-gray-200 text-gray-400 border border-gray-300 text-xs font-bold px-4 py-2.5 rounded-xl cursor-not-allowed opacity-60 w-full sm:w-auto select-none"
                        title="Hanya akun Operator dan Superadmin yang dapat membuat shipment baru.">
                    <i class="ph-bold ph-lock-key text-sm text-gray-400"></i>
                    <span>Buat Shipment</span>
                </button>
                @endif
            </div>
        </div>
    </div>

    <!-- Live Staging Monitoring Section (Real-time Ongoing Staging) -->
    @if(isset($activeStagingShipments) && $activeStagingShipments->isNotEmpty())
        @php
            $exportDraftCount = $activeStagingShipments->where('jenis_pengiriman', 'export')->count();
            $lokalDraftCount = $activeStagingShipments->where('jenis_pengiriman', 'lokal')->count();
        @endphp
        <div class="bg-white rounded-2xl sm:rounded-3xl border border-rose-100 shadow-[0_4px_24px_rgba(225,29,72,0.06)] p-3.5 sm:p-5 lg:p-6 mb-4 sm:mb-6 relative overflow-hidden"
             x-data="{ showCards: true }">
            
            <!-- Ambient Background Glow -->
            <div class="absolute -top-10 -right-10 w-44 h-44 bg-rose-50 rounded-full blur-3xl pointer-events-none opacity-70"></div>
            
            <!-- Section Header -->
            <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-3">
                <!-- Header Info (Left) -->
                <div>
                    <!-- Mobile Top Row: Live badge + Unit Count + Mobile Toggle -->
                    <div class="flex items-center justify-between gap-2 mb-2 sm:mb-1.5">
                        <div class="flex items-center gap-2">
                            <!-- Red Blinking LIVE Badge -->
                            <div class="inline-flex items-center gap-1.5 sm:gap-2 px-2.5 sm:px-3 py-1 sm:py-1.5 rounded-full bg-rose-50 border border-rose-200/90 shadow-2xs shrink-0">
                                <span class="relative flex h-2 sm:h-2.5 w-2 sm:w-2.5">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-500 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-2 sm:h-2.5 w-2 sm:w-2.5 bg-rose-600"></span>
                                </span>
                                <span class="text-[10px] sm:text-[11px] font-black tracking-wider text-rose-600 uppercase">LIVE STAGING</span>
                            </div>

                            <span class="text-[10px] sm:text-[11px] font-bold px-2.5 py-0.5 rounded-full bg-rose-100 text-rose-700 whitespace-nowrap">
                                {{ $activeStagingShipments->count() }} Unit Aktif
                            </span>
                        </div>

                        <!-- Mobile-only Collapse Button -->
                        <button type="button" 
                                @click="showCards = !showCards"
                                class="md:hidden p-1.5 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-xl transition-colors shrink-0"
                                :title="showCards ? 'Sembunyikan Kartu Staging' : 'Tampilkan Kartu Staging'">
                            <i class="ph-bold text-base transition-transform duration-200" :class="showCards ? 'ph-caret-up' : 'ph-caret-down'"></i>
                        </button>
                    </div>

                    <h2 class="text-base sm:text-lg font-bold text-gray-800 leading-snug">Staging Sedang Berlangsung</h2>
                    <p class="text-xs text-gray-500 mt-0.5 leading-relaxed">Armada aktif yang sedang dalam proses loading, inspeksi, dan upload bukti SOP 27 titik di warehouse.</p>
                </div>

                <!-- Right Side Summary & Quick Controls -->
                <div class="flex items-center justify-between md:justify-end gap-2.5 flex-wrap pt-2 md:pt-0 border-t border-rose-100/60 md:border-0">
                    <div class="hidden sm:flex items-center gap-2 text-xs text-gray-600 bg-gray-50 border border-gray-200 px-3 py-1.5 rounded-xl">
                        <span class="font-semibold text-purple-700 flex items-center gap-1">
                            <i class="ph-fill ph-shipping-container"></i> {{ $exportDraftCount }} Export
                        </span>
                        <span class="text-gray-300">&bull;</span>
                        <span class="font-semibold text-spv-blue flex items-center gap-1">
                            <i class="ph-fill ph-truck"></i> {{ $lokalDraftCount }} Lokal
                        </span>
                    </div>

                    <a href="{{ route('shipments.index', ['status' => 'draft']) }}" 
                       class="inline-flex items-center justify-center gap-1.5 text-xs font-semibold px-3 py-1.5 rounded-xl bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-200 transition-colors w-full sm:w-auto"
                       title="Saring tabel master di bawah hanya menampilkan armada draft">
                        <i class="ph-bold ph-funnel"></i>
                        <span>Filter Draft di Tabel</span>
                    </a>

                    <!-- Desktop Collapse Button -->
                    <button type="button" 
                            @click="showCards = !showCards"
                            class="hidden md:inline-flex p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-xl transition-colors shrink-0"
                            :title="showCards ? 'Sembunyikan Kartu Staging' : 'Tampilkan Kartu Staging'">
                        <i class="ph-bold text-base transition-transform duration-200" :class="showCards ? 'ph-caret-up' : 'ph-caret-down'"></i>
                    </button>
                </div>
            </div>

            <!-- Horizontal Cards Reel -->
            <div x-show="showCards" x-transition.opacity.duration.200ms class="mt-3.5 pt-3.5 border-t border-rose-100/60">
                <div class="overflow-x-auto pb-2 -mx-3.5 px-3.5 sm:mx-0 sm:px-0 flex gap-3 sm:gap-3.5 scrollbar-thin snap-x snap-mandatory">
                    @foreach($activeStagingShipments as $staging)
                        @php
                            $evidenceDone = $staging->evidenceItems->count();
                            $percent = round(($evidenceDone / 27) * 100);
                            $officers = $staging->allKaryawans()->pluck('nama')->toArray();
                            $primaryOfficer = count($officers) > 0 ? $officers[0] : ($staging->karyawan?->nama ?? 'Belum Ditugaskan');
                            $extraCount = count($officers) > 1 ? count($officers) - 1 : 0;
                            $identifier = $staging->packing_list_no ?: ($staging->nomor_container_atau_plat ?? 'SPV-' . $staging->id);
                        @endphp
                        <div class="w-[82vw] max-w-[280px] sm:w-[285px] sm:min-w-[285px] shrink-0 snap-start bg-white rounded-2xl border border-gray-200/80 p-3.5 hover:shadow-md hover:border-spv-blue/40 transition-all flex flex-col justify-between group">
                            <div>
                                <!-- Card Header -->
                                <div class="flex items-start justify-between gap-2 mb-2">
                                    <div class="flex items-center gap-2.5 min-w-0">
                                        <div class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0 {{ $staging->jenis_pengiriman === 'export' ? 'bg-purple-50 text-purple-700' : 'bg-blue-50 text-blue-700' }}">
                                            <i class="ph-fill {{ $staging->jenis_pengiriman === 'export' ? 'ph-shipping-container' : 'ph-truck' }} text-base"></i>
                                        </div>
                                        <div class="min-w-0">
                                            <a href="{{ route('shipments.show', $staging->id) }}" class="text-xs font-bold text-gray-800 hover:text-spv-blue transition-colors truncate block max-w-[140px] sm:max-w-[150px]" title="{{ $identifier }}">
                                                {{ $identifier }}
                                            </a>
                                            <span class="text-[10px] text-gray-400 capitalize block leading-tight truncate">
                                                {{ $staging->jenis_produk }} &bull; {{ $staging->jenis_pengiriman }}
                                            </span>
                                        </div>
                                    </div>
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[9px] font-bold bg-amber-50 text-amber-700 border border-amber-200 shrink-0">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                        Draft
                                    </span>
                                </div>

                                <!-- Card Meta Details -->
                                <div class="space-y-1.5 py-2 my-1 border-y border-gray-100 text-[11px] text-gray-600">
                                    <div class="flex items-center justify-between gap-2">
                                        <span class="text-gray-400 shrink-0">Plat / Sopir:</span>
                                        <span class="font-medium text-gray-700 text-right truncate max-w-[150px]" title="{{ $staging->plat_nomor ?? '-' }} ({{ $staging->nama_sopir ?? '-' }})">
                                            {{ $staging->plat_nomor ?? '-' }} &bull; {{ $staging->nama_sopir ?? '-' }}
                                        </span>
                                    </div>
                                    <div class="flex items-center justify-between gap-2">
                                        <span class="text-gray-400 shrink-0">Lokasi &amp; Shift:</span>
                                        <span class="font-medium text-gray-700 text-right truncate max-w-[150px]">
                                            {{ $staging->warehouse_label }} &bull; {{ ucfirst($staging->waktu ?? 'Siang') }}
                                        </span>
                                    </div>
                                    <div class="flex items-center justify-between gap-2">
                                        <span class="text-gray-400 shrink-0">Petugas:</span>
                                        <span class="font-medium text-gray-700 truncate max-w-[150px] text-right" title="{{ implode(', ', $officers) }}">
                                            {{ $primaryOfficer }}{{ $extraCount > 0 ? " +{$extraCount}" : '' }}
                                        </span>
                                    </div>
                                </div>

                                <!-- SOP Progress Meter -->
                                <div class="mt-2.5">
                                    <div class="flex items-center justify-between text-[10px] mb-1">
                                        <span class="text-gray-500 font-medium">Progress SOP Foto</span>
                                        <span class="font-bold {{ $evidenceDone > 0 ? 'text-spv-blue' : 'text-gray-400' }}">
                                            {{ $evidenceDone }}/27 ({{ $percent }}%)
                                        </span>
                                    </div>
                                    <div class="w-full bg-gray-100 rounded-full h-1.5 overflow-hidden">
                                        <div class="h-full rounded-full transition-all duration-500 {{ $percent > 0 ? 'bg-spv-blue' : 'bg-gray-200' }}" style="width: {{ max($percent, 4) }}%"></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="flex items-center gap-1.5 mt-3 pt-2.5 border-t border-gray-100">
                                <a href="{{ route('field-app.timeline', $staging->id) }}" target="_blank"
                                   class="flex-1 flex items-center justify-center gap-1.5 py-2 px-2.5 rounded-xl text-xs font-bold text-white bg-spv-blue hover:bg-blue-800 shadow-sm transition-all text-center">
                                    <i class="ph-bold ph-camera text-sm"></i>
                                    <span>Ambil Foto</span>
                                </a>
                                <a href="{{ route('field-app.edit', $staging->id) }}"
                                   class="w-8 h-8 rounded-xl border border-gray-200 text-gray-600 hover:text-spv-blue hover:bg-blue-50 transition-colors flex items-center justify-center shrink-0"
                                   title="Edit Data Staging">
                                    <i class="ph-bold ph-pencil-simple text-sm"></i>
                                </a>
                                <a href="{{ route('shipments.show', $staging->id) }}"
                                   class="w-8 h-8 rounded-xl border border-gray-200 text-gray-600 hover:text-spv-blue hover:bg-blue-50 transition-colors flex items-center justify-center shrink-0"
                                   title="Lihat Detail">
                                    <i class="ph-bold ph-eye text-sm"></i>
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <!-- Main Table Card -->
    <div class="bg-white rounded-2xl shadow-[0_1px_6px_rgba(40,84,145,0.05)] border border-gray-100 overflow-hidden">
        
        <!-- Toolbar & Search -->
        <form method="GET" action="{{ route('shipments.index') }}" class="p-3.5 sm:p-5 flex flex-col md:flex-row md:items-center justify-between gap-3 sm:gap-4" style="border-bottom: 1px solid #f0f4f9;">
            <div class="relative w-full md:w-80">
                <i class="ph-bold ph-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari No. PL / Plat / Container / Sopir..." 
                       class="w-full pl-9 pr-4 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs focus:ring-2 focus:ring-spv-blue focus:border-spv-blue outline-none transition-all">
            </div>
            
            <div class="flex items-center gap-1.5 sm:gap-2 overflow-x-auto pb-1 md:pb-0 scrollbar-thin -mx-1 px-1">
                <a href="{{ route('shipments.index') }}" class="text-[10px] sm:text-xs font-bold px-3 sm:px-4 py-1.5 sm:py-2 rounded-full whitespace-nowrap {{ !request('type') && !request('status') ? 'bg-spv-blue text-white shadow-sm' : 'text-gray-500 border border-gray-200 hover:bg-gray-50' }}">Semua</a>
                <a href="{{ route('shipments.index', ['type' => 'export']) }}" class="text-[10px] sm:text-xs font-semibold px-3 sm:px-4 py-1.5 sm:py-2 rounded-full whitespace-nowrap {{ request('type') === 'export' ? 'bg-spv-blue text-white shadow-sm' : 'text-gray-500 border border-gray-200 hover:bg-gray-50' }}">Export</a>
                <a href="{{ route('shipments.index', ['type' => 'lokal']) }}" class="text-[10px] sm:text-xs font-semibold px-3 sm:px-4 py-1.5 sm:py-2 rounded-full whitespace-nowrap {{ request('type') === 'lokal' ? 'bg-spv-blue text-white shadow-sm' : 'text-gray-500 border border-gray-200 hover:bg-gray-50' }}">Lokal</a>
                <a href="{{ route('shipments.index', ['status' => 'draft']) }}" class="text-[10px] sm:text-xs font-semibold px-3 sm:px-4 py-1.5 sm:py-2 rounded-full whitespace-nowrap border {{ request('status') === 'draft' ? 'bg-amber-500 text-white border-amber-500 shadow-sm' : 'text-amber-600 border-amber-200 hover:bg-amber-50' }}">Draft</a>
                <a href="{{ route('shipments.index', ['status' => 'submitted']) }}" class="text-[10px] sm:text-xs font-semibold px-3 sm:px-4 py-1.5 sm:py-2 rounded-full whitespace-nowrap border {{ request('status') === 'submitted' ? 'bg-green-600 text-white border-green-600 shadow-sm' : 'text-green-600 border-green-200 hover:bg-green-50' }}">Submitted</a>
            </div>
        </form>

        <!-- Desktop / Tablet Table (>= md) -->
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left whitespace-nowrap">
                <thead style="background:#fafbfd;">
                    <tr>
                        <th class="px-3.5 py-2.5 text-[10px] font-bold text-gray-400 uppercase tracking-wider">No. Packing List / Identitas</th>
                        <th class="px-3 py-2.5 text-[10px] font-bold text-gray-400 uppercase tracking-wider">Produk</th>
                        <th class="px-2.5 py-2.5 text-[10px] font-bold text-gray-400 uppercase tracking-wider">Tipe</th>
                        <th class="px-3 py-2.5 text-[10px] font-bold text-gray-400 uppercase tracking-wider">Petugas</th>
                        <th class="px-3 py-2.5 text-[10px] font-bold text-gray-400 uppercase tracking-wider">Waktu Staging</th>
                        <th class="px-2.5 py-2.5 text-[10px] font-bold text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="px-3.5 py-2.5 text-[10px] font-bold text-gray-400 uppercase tracking-wider text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($shipments as $shipment)
                        <tr style="border-top: 1px solid #f0f4f9;" onmouseover="this.style.background='#fafcff'" onmouseout="this.style.background='transparent'">
                            <td class="px-3.5 py-2.5">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0" style="background:#eff4fc;">
                                        <i class="ph-fill {{ $shipment->jenis_pengiriman === 'export' ? 'ph-shipping-container' : 'ph-truck' }} text-sm" style="color:#285491;"></i>
                                    </div>
                                    <div>
                                        <a href="{{ route('shipments.show', $shipment->id) }}" class="text-xs font-bold text-gray-800 hover:text-spv-blue transition-colors">
                                            {{ $shipment->packing_list_no ?: ($shipment->nomor_container_atau_plat ?? 'SPV-' . $shipment->id) }}
                                        </a>
                                        <p class="text-[10px] text-gray-400 leading-tight">Cont: {{ $shipment->nomor_container_atau_plat ?? '-' }} &bull; Plat: {{ $shipment->plat_nomor ?? ($shipment->shipment_no ?? '—') }}@if($shipment->nomor_seal) &bull; Seal: {{ $shipment->nomor_seal }}@endif</p>
                                    </div>
                                </div>
                            <td class="px-3 py-2.5">
                                @php
                                    $pLower = strtolower($shipment->jenis_produk ?? '');
                                    $pClass = match($pLower) {
                                        'fiber' => 'bg-blue-50 text-spv-blue border border-blue-200',
                                        'sodium' => 'bg-emerald-50 text-emerald-700 border border-emerald-200',
                                        'sliver' => 'bg-amber-50 text-amber-700 border border-amber-200',
                                        'yarn' => 'bg-purple-50 text-purple-700 border border-purple-200',
                                        default => 'bg-indigo-50 text-indigo-700 border border-indigo-200',
                                    };
                                @endphp
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider {{ $pClass }}">
                                    {{ strtoupper((string) $shipment->jenis_produk) }}
                                </span>
                            </td>
                            <td class="px-2.5 py-2.5">
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold capitalize {{ $shipment->jenis_pengiriman === 'export' ? 'bg-purple-50 text-purple-700' : 'bg-gray-100 text-gray-600' }}">
                                    {{ $shipment->jenis_pengiriman }}
                                </span>
                            </td>
                            <td class="px-3 py-2.5"><p class="text-xs text-gray-600">{{ $shipment->allKaryawans()->pluck('nama')->join(', ') ?: ($shipment->karyawan?->nama ?? '-') }}</p></td>
                            <td class="px-3 py-2.5"><p class="text-[10px] text-gray-500 font-medium">{{ $shipment->created_at->format('d M Y, H:i') }} WIB</p></td>
                            <td class="px-2.5 py-2.5">
                                @if($shipment->status === 'submitted')
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-green-50 text-green-700 border border-green-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-green-500 animate-pulse"></span>
                                        Submitted
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                        Draft
                                    </span>
                                @endif
                            </td>
                            <td class="px-3.5 py-2.5 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('shipments.show', $shipment->id) }}"
                                       title="Lihat Detail Seluruh Data"
                                       class="p-1 rounded-lg transition-all text-gray-400 hover:text-spv-blue hover:bg-blue-50">
                                       <i class="ph-bold ph-eye text-sm"></i>
                                    </a>
                                    <a href="{{ route('shipments.download-evidence', $shipment->id) }}"
                                       title="Download Semua Foto (ZIP: {{ $shipment->packing_list_no ?: $shipment->nomor_container_atau_plat }}.zip)"
                                       class="p-1 rounded-lg transition-all text-gray-400 hover:text-emerald-600 hover:bg-emerald-50">
                                        <i class="ph-bold ph-file-zip text-sm"></i>
                                    </a>
                                    <a href="{{ route('shipments.download-pdf', $shipment->id) }}"
                                       title="Download Laporan PDF Lengkap ({{ $shipment->packing_list_no ?: $shipment->nomor_container_atau_plat }}_Report.pdf)"
                                       class="p-1 rounded-lg transition-all text-gray-400 hover:text-red-600 hover:bg-red-50">
                                        <i class="ph-bold ph-file-pdf text-sm"></i>
                                    </a>

                                    @if($shipment->status === 'draft')
                                        <a href="{{ route('field-app.edit', $shipment->id) }}"
                                           title="Lanjutkan Pengisian Draft Staging (Sebelum Ambil Foto)"
                                           class="p-1 rounded-lg transition-all text-amber-500 hover:text-amber-600 hover:bg-amber-50">
                                            <i class="ph-bold ph-pencil-simple-line text-sm"></i>
                                        </a>
                                    @else
                                        <a href="{{ route('field-app.timeline', $shipment->id) }}"
                                           title="Buka Loading Evidence Foto SOP"
                                           target="_blank"
                                           class="p-1 rounded-lg transition-all text-gray-400 hover:text-spv-green hover:bg-emerald-50">
                                            <i class="ph-bold ph-camera text-sm"></i>
                                        </a>

                                        @if(auth()->user()?->canEditShipment($shipment))
                                            <a href="{{ route('field-app.edit', $shipment->id) }}"
                                               title="Edit Dokumen Shipment"
                                               class="p-1 rounded-lg transition-all text-gray-400 hover:text-spv-blue hover:bg-blue-50">
                                                <i class="ph-bold ph-pencil-simple text-sm"></i>
                                            </a>
                                        @endif
                                    @endif

                                    @if(auth()->user()?->isSuperadmin())
                                        <button type="button"
                                                @click="confirmDelete('{{ route('shipments.destroy', $shipment->id) }}', '{{ $shipment->packing_list_no ?: ($shipment->nomor_container_atau_plat ?: 'Shipment #' . $shipment->id) }}')"
                                                title="Hapus Shipment (Superadmin)"
                                                class="p-1 rounded-lg transition-all text-gray-400 hover:text-red-600 hover:bg-red-50 cursor-pointer">
                                            <i class="ph-bold ph-trash text-sm"></i>
                                        </button>
                                    @endif
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

        <!-- Mobile Cards List (< md) -->
        <div class="block md:hidden divide-y divide-gray-100">
            @forelse($shipments as $shipment)
                @php
                    $officers = $shipment->allKaryawans()->pluck('nama')->toArray();
                    $primaryOfficer = count($officers) > 0 ? implode(', ', $officers) : ($shipment->karyawan?->nama ?? '-');
                    $identifier = $shipment->packing_list_no ?: ($shipment->nomor_container_atau_plat ?? 'SPV-' . $shipment->id);
                @endphp
                <div class="p-3.5 hover:bg-slate-50/60 transition-colors">
                    <!-- Top row: ID / Type & Status -->
                    <div class="flex items-start justify-between gap-2">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <div class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0 {{ $shipment->jenis_pengiriman === 'export' ? 'bg-purple-50 text-purple-700' : 'bg-blue-50 text-blue-700' }}">
                                <i class="ph-fill {{ $shipment->jenis_pengiriman === 'export' ? 'ph-shipping-container' : 'ph-truck' }} text-base"></i>
                            </div>
                            <div class="min-w-0">
                                <a href="{{ route('shipments.show', $shipment->id) }}" class="text-xs font-bold text-gray-800 hover:text-spv-blue transition-colors truncate block" title="{{ $identifier }}">
                                    {{ $identifier }}
                                </a>
                                <p class="text-[10px] text-gray-400 leading-tight truncate">
                                    Cont: {{ $shipment->nomor_container_atau_plat ?? '-' }} &bull; Plat: {{ $shipment->plat_nomor ?? ($shipment->shipment_no ?? '—') }}@if($shipment->nomor_seal) &bull; Seal: {{ $shipment->nomor_seal }}@endif
                                </p>
                            </div>
                        </div>

                        <!-- Status Badge -->
                        @if($shipment->status === 'submitted')
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-green-50 text-green-700 border border-green-200 shrink-0">
                                <span class="w-1.5 h-1.5 rounded-full bg-green-500 animate-pulse"></span>
                                Submitted
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200 shrink-0">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                Draft
                            </span>
                        @endif
                    </div>

                    <!-- Meta specs row -->
                    <div class="mt-2.5 pt-2 border-t border-gray-100 grid grid-cols-2 gap-2 text-[11px]">
                        <div>
                            <span class="text-gray-400 text-[10px] block">Produk &amp; Tipe:</span>
                            <div class="flex items-center gap-1 mt-0.5">
                                <span class="font-semibold text-gray-700 capitalize">{{ $shipment->jenis_produk }}</span>
                                <span class="text-gray-300">&bull;</span>
                                <span class="px-1.5 py-0.2 rounded text-[10px] font-semibold capitalize {{ $shipment->jenis_pengiriman === 'export' ? 'bg-purple-50 text-purple-700' : 'bg-gray-100 text-gray-600' }}">
                                    {{ $shipment->jenis_pengiriman }}
                                </span>
                            </div>
                        </div>
                        <div>
                            <span class="text-gray-400 text-[10px] block">Waktu Staging:</span>
                            <span class="font-medium text-gray-600 mt-0.5 block truncate">{{ $shipment->created_at->format('d M Y, H:i') }} WIB</span>
                        </div>
                    </div>

                    <div class="mt-1.5 text-[11px] flex items-center gap-1.5 text-gray-500">
                        <i class="ph-bold ph-user text-gray-400 text-xs shrink-0"></i>
                        <span class="text-gray-400 text-[10px] shrink-0">Petugas:</span>
                        <span class="font-medium text-gray-700 truncate">{{ $primaryOfficer }}</span>
                    </div>

                    <!-- Actions Bar -->
                    <div class="flex items-center gap-1.5 mt-3 pt-2.5 border-t border-gray-100">
                        <a href="{{ route('shipments.show', $shipment->id) }}"
                           class="flex-1 flex items-center justify-center gap-1.5 py-1.5 px-2.5 rounded-xl text-xs font-bold text-spv-blue bg-blue-50/70 hover:bg-blue-100 transition-colors">
                            <i class="ph-bold ph-eye text-sm"></i>
                            <span>Detail</span>
                        </a>

                        <a href="{{ route('shipments.download-evidence', $shipment->id) }}"
                           title="Download ZIP"
                           class="w-8 h-8 rounded-xl border border-gray-200 text-gray-500 hover:text-emerald-600 hover:bg-emerald-50 transition-colors flex items-center justify-center shrink-0">
                            <i class="ph-bold ph-file-zip text-sm"></i>
                        </a>

                        <a href="{{ route('shipments.download-pdf', $shipment->id) }}"
                           title="Download PDF"
                           class="w-8 h-8 rounded-xl border border-gray-200 text-gray-500 hover:text-red-600 hover:bg-red-50 transition-colors flex items-center justify-center shrink-0">
                            <i class="ph-bold ph-file-pdf text-sm"></i>
                        </a>

                        @if($shipment->status === 'draft')
                            <a href="{{ route('field-app.edit', $shipment->id) }}"
                               title="Lanjutkan Draft"
                               class="w-8 h-8 rounded-xl border border-amber-200 text-amber-600 bg-amber-50 hover:bg-amber-100 transition-colors flex items-center justify-center shrink-0">
                                <i class="ph-bold ph-pencil-simple-line text-sm"></i>
                            </a>
                        @else
                            <a href="{{ route('field-app.timeline', $shipment->id) }}"
                               title="Foto SOP"
                               target="_blank"
                               class="w-8 h-8 rounded-xl border border-emerald-200 text-emerald-600 bg-emerald-50 hover:bg-emerald-100 transition-colors flex items-center justify-center shrink-0">
                                <i class="ph-bold ph-camera text-sm"></i>
                            </a>

                            @if(auth()->user()?->canEditShipment($shipment))
                                <a href="{{ route('field-app.edit', $shipment->id) }}"
                                   title="Edit"
                                   class="w-8 h-8 rounded-xl border border-gray-200 text-gray-500 hover:text-spv-blue hover:bg-blue-50 transition-colors flex items-center justify-center shrink-0">
                                    <i class="ph-bold ph-pencil-simple text-sm"></i>
                                </a>
                            @endif
                        @endif

                        @if(auth()->user()?->isSuperadmin())
                            <button type="button"
                                    @click="confirmDelete('{{ route('shipments.destroy', $shipment->id) }}', '{{ $identifier }}')"
                                    title="Hapus"
                                    class="w-8 h-8 rounded-xl border border-gray-200 text-gray-400 hover:text-red-600 hover:bg-red-50 transition-colors flex items-center justify-center shrink-0 cursor-pointer">
                                <i class="ph-bold ph-trash text-sm"></i>
                            </button>
                        @endif
                    </div>
                </div>
            @empty
                <div class="text-center py-10 px-4">
                    <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-3 text-gray-400">
                        <i class="ph-bold ph-package text-2xl"></i>
                    </div>
                    <p class="text-xs font-bold text-gray-700">Belum ada shipment terdaftar</p>
                    <p class="text-[11px] text-gray-400 mt-1">Gunakan tombol <strong>Buat Shipment</strong> di atas untuk membuat staging baru.</p>
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if($shipments->hasPages())
            <div class="p-3.5 sm:p-4 flex flex-col sm:flex-row items-center justify-between gap-2.5 border-t border-gray-100">
                <p class="text-[10px] text-gray-500 font-medium text-center sm:text-left">
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
            <div class="p-3.5 sm:p-4 border-t border-gray-100 text-[10px] text-gray-400 text-center sm:text-left">
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
                    <div class="grid grid-cols-2 sm:grid-cols-5 gap-2">
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

                        <button type="button"
                                @click="product = 'sliver'; fetchPreview()"
                                :class="product === 'sliver' ? 'border-spv-blue bg-blue-50/50 text-spv-blue font-bold ring-1 ring-spv-blue' : 'border-gray-200 bg-white text-gray-600 hover:bg-gray-50 font-medium'"
                                class="p-2 rounded-xl border text-xs text-center transition-all flex flex-col items-center gap-1">
                            <i class="ph-bold ph-spiral text-sm"></i>
                            <span>SLIVER</span>
                        </button>

                        <button type="button"
                                @click="product = 'yarn'; fetchPreview()"
                                :class="product === 'yarn' ? 'border-spv-blue bg-blue-50/50 text-spv-blue font-bold ring-1 ring-spv-blue' : 'border-gray-200 bg-white text-gray-600 hover:bg-gray-50 font-medium'"
                                class="p-2 rounded-xl border text-xs text-center transition-all flex flex-col items-center gap-1">
                            <i class="ph-bold ph-yarn text-sm"></i>
                            <span>YARN</span>
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

    <!-- Modal Konfirmasi Hapus Shipment (Superadmin) -->
    @if(auth()->user()?->isSuperadmin())
    <div x-cloak
         x-show="deleteModalOpen"
         class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4"
         @keydown.escape.window="deleteModalOpen = false">
        <div class="bg-white rounded-3xl shadow-2xl border border-gray-100 max-w-md w-full p-6 text-center"
             @click.away="deleteModalOpen = false">
            <div class="w-14 h-14 rounded-2xl bg-red-50 text-red-600 flex items-center justify-center text-3xl mx-auto mb-4 ring-8 ring-red-50/50">
                <i class="ph-bold ph-trash"></i>
            </div>
            <h3 class="text-base font-bold text-gray-800">Hapus Dokumen Shipment?</h3>
            <p class="text-xs text-gray-500 mt-2 leading-relaxed">
                Apakah Anda yakin ingin menghapus shipment <strong class="text-gray-700" x-text="deleteShipmentName"></strong>?
                Seluruh berkas bukti foto evidence dan data terkait akan dihapus permanen dari server.
            </p>

            <form method="POST" :action="deleteActionUrl" class="mt-6 flex items-center gap-3">
                @csrf
                @method('DELETE')
                <button type="button" @click="deleteModalOpen = false"
                        class="flex-1 py-2.5 rounded-xl border border-gray-200 text-xs font-bold text-gray-600 hover:bg-gray-50 transition-colors">
                    Batal
                </button>
                <button type="submit"
                        class="flex-1 py-2.5 rounded-xl bg-red-600 hover:bg-red-700 text-white text-xs font-bold shadow-md hover:shadow-lg transition-all flex items-center justify-center gap-2">
                    <i class="ph-bold ph-trash"></i>
                    <span>Ya, Hapus</span>
                </button>
            </form>
        </div>
    </div>
    @endif
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
        deleteModalOpen: false,
        deleteActionUrl: '',
        deleteShipmentName: '',
        confirmDelete(url, name) {
            this.deleteActionUrl = url;
            this.deleteShipmentName = name;
            this.deleteModalOpen = true;
        },
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
            if (this.product && this.product !== 'all') return this.product.toUpperCase();
            return 'PRODUK';
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
