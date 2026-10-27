<x-layout :title="'Detail Shipment: ' . ($shipment->packing_list_no ?: ($shipment->nomor_container_atau_plat ?? 'SPV-' . $shipment->id))">
<div x-data="{ deleteModalOpen: false }">

    <!-- Top Navigation & Actions Card -->
    <div class="bg-white rounded-2xl sm:rounded-3xl p-4 sm:p-5 lg:p-6 border border-gray-100 shadow-[0_1px_6px_rgba(40,84,145,0.05)] mb-6">
        <div class="flex flex-col xl:flex-row xl:items-center justify-between gap-4">
            <div class="flex items-start sm:items-center gap-3 min-w-0">
                <a href="{{ route('shipments.index') }}"
                   class="flex items-center justify-center w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-slate-50 hover:bg-blue-50 border border-slate-200 hover:border-spv-blue text-gray-600 hover:text-spv-blue transition-all shadow-2xs shrink-0 mt-0.5 sm:mt-0"
                   title="Kembali ke Daftar Shipment">
                    <i class="ph-bold ph-arrow-left text-base sm:text-lg"></i>
                </a>
                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-2 flex-wrap">
                        <h1 class="text-lg sm:text-xl font-extrabold text-gray-800 leading-tight">
                            {{ $shipment->packing_list_no ?: ($shipment->nomor_container_atau_plat ?? 'Shipment #' . $shipment->id) }}
                        </h1>
                        @if($shipment->status === 'submitted')
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-green-50 text-green-700 border border-green-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-green-500 animate-pulse"></span>
                                Submitted
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                Draft
                            </span>
                        @endif
                    </div>
                    <div class="text-[11px] sm:text-xs text-gray-500 mt-1.5 leading-relaxed flex flex-wrap items-center gap-x-2 gap-y-0.5">
                        <span>No. Container: <strong class="text-gray-700 font-mono">{{ $shipment->nomor_container_atau_plat ?? '-' }}</strong></span>
                        <span class="text-gray-300 hidden sm:inline">&bull;</span>
                        <span>Plat: <strong class="text-gray-700 font-mono">{{ $shipment->plat_nomor ?? '-' }}</strong></span>
                        <span class="text-gray-300 hidden sm:inline">&bull;</span>
                        <span>Dibuat: <span class="text-gray-700 font-medium">{{ $shipment->created_at->format('d M Y, H:i') }} WIB</span></span>
                        <span class="text-gray-300 hidden sm:inline">&bull;</span>
                        <span>Petugas: <strong class="text-gray-700">{{ $shipment->allKaryawans()->pluck('nama')->join(', ') ?: ($shipment->karyawan?->nama ?? '-') }}</strong></span>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 xl:flex xl:items-center gap-2.5 w-full xl:w-auto pt-3.5 xl:pt-0 border-t xl:border-t-0 border-gray-100">
                @if($shipment->status === 'draft')
                    <a href="{{ route('field-app.edit', $shipment->id) }}"
                       class="order-first xl:order-last flex items-center justify-center gap-2 bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold px-4 py-2.5 rounded-xl shadow-[0_4px_12px_rgba(245,158,11,0.25)] hover:shadow-lg transition-all col-span-1 sm:col-span-2 xl:col-auto whitespace-nowrap"
                       title="Periksa & edit data sebelum mengambil foto evidence">
                        <i class="ph-bold ph-pencil-simple-line text-base"></i>
                        <span>Lanjutkan Pengisian Draft</span>
                    </a>
                @else
                    <a href="{{ route('field-app.timeline', $shipment->id) }}" target="_blank"
                       class="order-first xl:order-last flex items-center justify-center gap-2 bg-spv-blue hover:bg-blue-800 text-white text-xs font-bold px-4 py-2.5 rounded-xl shadow-[0_4px_12px_rgba(40,84,145,0.25)] hover:shadow-lg transition-all col-span-1 sm:col-span-2 xl:col-auto whitespace-nowrap">
                        <i class="ph-bold ph-camera text-base"></i>
                        <span>Buka Loading Evidence</span>
                    </a>

                    @if(auth()->user()?->canEditShipment($shipment))
                    <a href="{{ route('field-app.edit', $shipment->id) }}"
                       class="flex items-center justify-center gap-1.5 bg-blue-50 hover:bg-blue-100 text-spv-blue border border-blue-200 text-xs font-bold px-3 py-2.5 rounded-xl transition-all shadow-xs"
                       title="Edit Formulir & Dokumen Shipment">
                        <i class="ph-bold ph-pencil-simple text-base"></i>
                        <span>Edit Data</span>
                    </a>
                    @endif
                @endif

                @if(auth()->user()?->isSuperadmin())
                    <button type="button" @click="deleteModalOpen = true"
                            class="flex items-center justify-center gap-1.5 bg-red-50 hover:bg-red-100 text-red-600 border border-red-200 text-xs font-bold px-3 py-2.5 rounded-xl transition-all shadow-xs"
                            title="Hapus shipment & bukti foto permanen (Hanya Superadmin)">
                        <i class="ph-bold ph-trash text-base"></i>
                        <span>Hapus</span>
                    </button>
                @endif
                
                @if(auth()->user()?->canDownloadReports())
                <div class="grid grid-cols-3 sm:flex sm:items-center gap-2 col-span-1 sm:col-span-2 xl:col-auto">
                    <a href="{{ route('shipments.download-evidence', $shipment->id) }}"
                       class="flex items-center justify-center gap-1.5 sm:gap-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold px-2.5 sm:px-3.5 py-2.5 rounded-xl shadow-sm hover:shadow transition-all text-center"
                       title="Download seluruh bukti foto dalam file ZIP ({{ $shipment->packing_list_no ?: $shipment->nomor_container_atau_plat }}.zip)">
                        <i class="ph-bold ph-file-zip text-base shrink-0"></i>
                        <span class="truncate"><span class="hidden sm:inline">Download </span>ZIP</span>
                    </a>
                    <a href="{{ route('shipments.download-pdf', $shipment->id) }}"
                       class="flex items-center justify-center gap-1.5 sm:gap-2 bg-red-600 hover:bg-red-700 text-white text-xs font-bold px-2.5 sm:px-3.5 py-2.5 rounded-xl shadow-sm hover:shadow transition-all text-center"
                       title="Download Laporan Dokumen Lengkap & 27 Foto Evidence (.PDF)">
                        <i class="ph-bold ph-file-pdf text-base shrink-0"></i>
                        <span class="truncate"><span class="hidden sm:inline">Download </span>PDF</span>
                    </a>
                    <a href="{{ route('shipments.report', $shipment->id) }}" target="_blank"
                       class="flex items-center justify-center gap-1.5 sm:gap-2 bg-white border border-gray-200 text-gray-700 hover:text-spv-blue hover:border-spv-blue text-xs font-bold px-2.5 sm:px-3.5 py-2.5 rounded-xl shadow-sm transition-all text-center"
                       title="Buka Pratinjau & Cetak Dokumen Laporan">
                        <i class="ph-bold ph-printer text-base shrink-0"></i>
                        <span>Cetak</span>
                    </a>
                </div>
                @else
                <div class="grid grid-cols-3 sm:flex sm:items-center gap-2 col-span-1 sm:col-span-2 xl:col-auto opacity-60">
                    <button type="button" disabled class="flex items-center justify-center gap-1.5 bg-gray-100 text-gray-400 border border-gray-200 text-xs font-bold px-2.5 py-2.5 rounded-xl cursor-not-allowed select-none" title="Download ZIP hanya tersedia untuk Admin dan Superadmin">
                        <i class="ph-bold ph-file-zip text-base"></i>
                        <span>ZIP</span>
                    </button>
                    <button type="button" disabled class="flex items-center justify-center gap-1.5 bg-gray-100 text-gray-400 border border-gray-200 text-xs font-bold px-2.5 py-2.5 rounded-xl cursor-not-allowed select-none" title="Download PDF hanya tersedia untuk Admin dan Superadmin">
                        <i class="ph-bold ph-file-pdf text-base"></i>
                        <span>PDF</span>
                    </button>
                    <button type="button" disabled class="flex items-center justify-center gap-1.5 bg-gray-100 text-gray-400 border border-gray-200 text-xs font-bold px-2.5 py-2.5 rounded-xl cursor-not-allowed select-none" title="Cetak Laporan hanya tersedia untuk Admin dan Superadmin">
                        <i class="ph-bold ph-printer text-base"></i>
                        <span>Cetak</span>
                    </button>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Overview Statistics Bar -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6">
        <div class="bg-white rounded-2xl p-4 border border-gray-100 shadow-[0_1px_6px_rgba(40,84,145,0.05)]">
            <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Produk & Tipe</p>
            <p class="text-sm font-bold text-gray-800 mt-1 capitalize">{{ $shipment->jenis_produk }} &bull; {{ $shipment->jenis_pengiriman }}</p>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-gray-100 shadow-[0_1px_6px_rgba(40,84,145,0.05)]">
            <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Lokasi Staging</p>
            <p class="text-sm font-bold text-gray-800 mt-1">{{ $shipment->warehouse_label }}</p>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-gray-100 shadow-[0_1px_6px_rgba(40,84,145,0.05)]">
            <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Foto SOP Terunggah</p>
            <p class="text-sm font-bold text-spv-green mt-1">{{ $shipment->evidenceItems->count() }} / {{ count($points) }} Titik</p>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-gray-100 shadow-[0_1px_6px_rgba(40,84,145,0.05)]">
            <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Waktu Submit Final</p>
            <p class="text-sm font-bold text-gray-800 mt-1">
                {{ $shipment->submitted_at ? $shipment->submitted_at->format('d M Y, H:i') : 'Belum Submit' }}
            </p>
        </div>
    </div>

    <!-- Grid Detail Informasi -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        
        <!-- Card 1: Data Pengiriman & Staging -->
        <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-[0_1px_6px_rgba(40,84,145,0.05)]">
            <div class="flex items-center gap-2.5 pb-3 border-b border-gray-100 mb-4">
                <div class="w-8 h-8 rounded-xl bg-blue-50 text-spv-blue flex items-center justify-center">
                    <i class="ph-bold ph-truck text-base"></i>
                </div>
                <h2 class="text-sm font-bold text-gray-800">Informasi Pengiriman & Lapangan</h2>
            </div>

            <div class="grid grid-cols-2 gap-4 text-xs">
                <div>
                    <span class="text-gray-400 block text-[11px] mb-0.5">No. Container / Plat:</span>
                    <span class="font-bold text-gray-800 text-sm">{{ $shipment->nomor_container_atau_plat ?? '-' }}</span>
                </div>
                <div>
                    <span class="text-gray-400 block text-[11px] mb-0.5">Jenis Produk:</span>
                    @php
                        $prodLower = strtolower($shipment->jenis_produk ?? '');
                        $prodBadgeClass = match($prodLower) {
                            'fiber' => 'bg-blue-50 text-spv-blue border border-blue-200',
                            'sodium' => 'bg-emerald-50 text-emerald-700 border border-emerald-200',
                            'sliver' => 'bg-amber-50 text-amber-700 border border-amber-200',
                            'yarn' => 'bg-purple-50 text-purple-700 border border-purple-200',
                            default => 'bg-indigo-50 text-indigo-700 border border-indigo-200',
                        };
                    @endphp
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold uppercase tracking-wider {{ $prodBadgeClass }}">
                        {{ strtoupper((string) $shipment->jenis_produk) }}
                    </span>
                </div>
                <div class="col-span-2">
                    <span class="text-gray-400 block text-[11px] mb-1">Petugas Pelaksana:</span>
                    @php
                        $assignedKaryawans = $shipment->allKaryawans();
                    @endphp
                    @if($assignedKaryawans->isNotEmpty())
                        <div class="flex flex-wrap gap-1.5 mt-0.5">
                            @foreach($assignedKaryawans as $petugas)
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-blue-50/80 border border-blue-200/60 text-spv-blue text-xs font-semibold shadow-xs">
                                    <i class="ph-bold ph-user text-[11px]"></i>
                                    <span>{{ $petugas->nama }}</span>
                                    <span class="text-[10px] text-blue-500/80 font-mono font-normal">({{ $petugas->nomor_induk }})</span>
                                    <span class="text-[9px] font-bold uppercase tracking-wider px-1.5 py-0.5 rounded {{ ($petugas->position ?? '') === 'checker' ? 'bg-purple-100 text-purple-700' : (($petugas->position ?? '') === 'helper' ? 'bg-teal-100 text-teal-700' : 'bg-blue-100 text-blue-800') }}">
                                        {{ $petugas->position_label ?? ucfirst($petugas->position ?? 'Operator') }}
                                    </span>
                                </span>
                            @endforeach
                        </div>
                    @else
                        <span class="font-bold text-gray-800">-</span>
                    @endif
                </div>
                <div>
                    <span class="text-gray-400 block text-[11px] mb-0.5">Jenis Pengiriman:</span>
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold capitalize {{ $shipment->jenis_pengiriman === 'export' ? 'bg-purple-50 text-purple-700' : 'bg-gray-100 text-gray-700' }}">
                        {{ $shipment->jenis_pengiriman }}
                    </span>
                </div>
                <div>
                    <span class="text-gray-400 block text-[11px] mb-0.5">Kondisi Cuaca:</span>
                    <span class="font-semibold text-gray-700 capitalize">☀️ {{ $shipment->cuaca }}</span>
                </div>
                <div>
                    <span class="text-gray-400 block text-[11px] mb-0.5">Shift Waktu:</span>
                    <span class="font-semibold text-gray-700 capitalize">🕒 {{ $shipment->waktu }}</span>
                </div>
                <div>
                    <span class="text-gray-400 block text-[11px] mb-0.5">Tanggal Staging:</span>
                    <span class="font-semibold text-gray-700">{{ $shipment->tanggal_staging ? $shipment->tanggal_staging->format('d F Y') : '-' }}</span>
                </div>
                <div>
                    <span class="text-gray-400 block text-[11px] mb-0.5">Lokasi Gudang:</span>
                    <span class="font-semibold text-gray-700">{{ $shipment->warehouse_label }}</span>
                </div>
            </div>
        </div>

        <!-- Card 2: Data Ekstraksi Dokumen & Surat Jalan (OCR) -->
        <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-[0_1px_6px_rgba(40,84,145,0.05)]">
            <div class="flex items-center gap-2.5 pb-3 border-b border-gray-100 mb-4">
                <div class="w-8 h-8 rounded-xl bg-emerald-50 text-spv-green flex items-center justify-center">
                    <i class="ph-bold ph-file-text text-base"></i>
                </div>
                <h2 class="text-sm font-bold text-gray-800">Dokumen & Ekstraksi OCR</h2>
            </div>

            <div class="grid grid-cols-2 gap-4 text-xs">
                <div>
                    <span class="text-gray-400 block text-[11px] mb-0.5">Shipment Group:</span>
                    <span class="font-bold text-gray-800">{{ $shipment->shipment_group ?? '—' }}</span>
                </div>
                <div>
                    <span class="text-gray-400 block text-[11px] mb-0.5">Shipment No:</span>
                    <span class="font-bold text-gray-800">{{ $shipment->shipment_no ?? '—' }}</span>
                </div>
                <div>
                    <span class="text-gray-400 block text-[11px] mb-0.5">Plat Nomor Truk:</span>
                    <span class="font-bold text-gray-800">{{ $shipment->plat_nomor ?? '—' }}</span>
                </div>
                <div>
                    <span class="text-gray-400 block text-[11px] mb-0.5">Nama Sopir:</span>
                    <span class="font-bold text-gray-800">{{ $shipment->nama_sopir ?? '—' }}</span>
                </div>
                <div>
                    <span class="text-gray-400 block text-[11px] mb-0.5">Nomor Seal (Segel):</span>
                    <span class="font-bold text-gray-800 font-mono">{{ $shipment->nomor_seal ?? '—' }}</span>
                </div>
                <div>
                    <span class="text-gray-400 block text-[11px] mb-0.5">No. Packing List:</span>
                    <span class="font-bold text-spv-blue">{{ $shipment->packing_list_no ?? '—' }}</span>
                </div>
                <div>
                    <span class="text-gray-400 block text-[11px] mb-0.5">Agen Forwarding:</span>
                    <span class="font-bold text-gray-800">{{ $shipment->agen_forwarding ?? '—' }}</span>
                </div>
                <div class="col-span-2">
                    <span class="text-gray-400 block text-[11px] mb-0.5">Tujuan Pengiriman:</span>
                    <span class="font-medium text-gray-800 bg-gray-50 p-2 rounded-lg block">{{ $shipment->tujuan_pengiriman ?? '—' }}</span>
                </div>
                <div>
                    <span class="text-gray-400 block text-[11px] mb-0.5">Waktu Mulai Staging:</span>
                    <span class="font-semibold text-gray-700">{{ $shipment->waktu_kedatangan_container ? $shipment->waktu_kedatangan_container->format('d M Y, H:i') : '—' }}</span>
                </div>
                <div>
                    <span class="text-gray-400 block text-[11px] mb-0.5">Waktu Akhir Staging:</span>
                    <span class="font-semibold text-gray-700">{{ $shipment->waktu_keberangkatan_container ? $shipment->waktu_keberangkatan_container->format('d M Y, H:i') : '—' }}</span>
                </div>
                <div>
                    <span class="text-gray-400 block text-[11px] mb-0.5">Lead Time (Durasi Staging):</span>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold {{ $shipment->lead_time_minutes !== null ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-gray-100 text-gray-600' }}">
                        <i class="ph-bold ph-timer text-emerald-600"></i>
                        {{ $shipment->lead_time_formatted }}
                    </span>
                </div>
            </div>
        </div>

    </div>

    <!-- Section: 2 Formulir Inspeksi Fisik (Lenzing Standards - Halaman 2 & 3 PDF) -->
    <div class="mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-2 mb-4 gap-2">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center font-bold text-base">
                    <i class="ph-bold ph-clipboard-text"></i>
                </div>
                <div>
                    <h2 class="text-sm font-bold text-gray-800">Formulir Inspeksi Fisik (Lenzing Standards)</h2>
                    <p class="text-[11px] text-gray-400">Dicetak resmi di <strong>Halaman 2 (Kontainer)</strong> dan <strong>Halaman 3 (Bale)</strong> pada berkas Laporan PDF.</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-slate-100 text-slate-700 border border-slate-200">
                    2 Dokumen Inspeksi
                </span>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- 1. Card Form Inspeksi Kontainer -->
            @php $cInsp = $shipment->containerInspection; @endphp
            <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-[0_1px_6px_rgba(40,84,145,0.05)] flex flex-col justify-between">
                <div>
                    <div class="flex items-start justify-between gap-3 pb-3 border-b border-gray-100 mb-4">
                        <div class="flex items-center gap-2.5">
                            <div class="w-9 h-9 rounded-xl bg-emerald-600 text-white flex items-center justify-center font-black text-sm shadow-xs">
                                L
                            </div>
                            <div>
                                <h3 class="text-xs font-extrabold text-gray-800 uppercase tracking-tight">Form Inspeksi Kondisi Kontainer</h3>
                                <p class="text-[10px] text-gray-400">Pemeriksaan 6 Titik &amp; Swab Test Kebersihan</p>
                            </div>
                        </div>
                        @if($cInsp)
                            @php
                                $cBadge = match($cInsp->kesimpulan) {
                                    'layak' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                    'layak_dengan_catatan' => 'bg-amber-50 text-amber-700 border-amber-200',
                                    default => 'bg-red-50 text-red-700 border-red-200',
                                };
                            @endphp
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11px] font-bold border {{ $cBadge }}">
                                <i class="ph-bold ph-check-circle"></i>
                                {{ strtoupper($cInsp->kesimpulan_label) }}
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                <i class="ph-bold ph-warning"></i>
                                BELUM DIISI
                            </span>
                        @endif
                    </div>

                    @if($cInsp)
                    <div class="grid grid-cols-2 gap-3 text-xs mb-4">
                        <div>
                            <span class="text-gray-400 block text-[10px]">No. Kontainer:</span>
                            <span class="font-bold text-gray-800 font-mono">{{ $cInsp->nomor_kontainer ?: '-' }}</span>
                        </div>
                        <div>
                            <span class="text-gray-400 block text-[10px]">Nomor Seal:</span>
                            <span class="font-bold text-gray-800 font-mono">{{ $cInsp->nomor_seal ?: '-' }}</span>
                        </div>
                        <div>
                            <span class="text-gray-400 block text-[10px]">Petugas Inspeksi:</span>
                            <span class="font-semibold text-gray-800">{{ $cInsp->nama_petugas_inspeksi ?: '-' }}</span>
                        </div>
                        <div>
                            <span class="text-gray-400 block text-[10px]">Waktu Loading:</span>
                            <span class="font-semibold text-gray-700">{{ ($shipment->waktu_kedatangan_container ? $shipment->waktu_kedatangan_container->format('H:i') : ($cInsp->loading_start_time ?: '-')) }} s/d {{ ($shipment->waktu_keberangkatan_container ? $shipment->waktu_keberangkatan_container->format('H:i') : ($cInsp->loading_finish_time ?: '-')) }}</span>
                        </div>
                    </div>

                    <!-- 6 Titik Status Pills -->
                    <div class="mb-4">
                        <span class="text-gray-400 block text-[10px] mb-1.5 uppercase font-bold tracking-wider">Hasil 6 Titik Pemeriksaan:</span>
                        <div class="grid grid-cols-3 gap-1.5 text-[10px]">
                            @foreach([
                                'Pintu' => $cInsp->pintu_status,
                                'Dinding Kanan' => $cInsp->dinding_kanan_status,
                                'Dinding Kiri' => $cInsp->dinding_kiri_status,
                                'Dinding Depan' => $cInsp->dinding_depan_status,
                                'Atap' => $cInsp->atap_status,
                                'Lantai' => $cInsp->lantai_status,
                            ] as $pName => $pStatus)
                            <div class="px-2 py-1 rounded-md border flex items-center justify-between {{ $pStatus ? 'bg-emerald-50/60 border-emerald-200 text-emerald-800 font-semibold' : 'bg-red-50 border-red-200 text-red-800 font-bold' }}">
                                <span class="truncate">{{ $pName }}</span>
                                <span>{{ $pStatus ? '✓' : '✗' }}</span>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    @if($cInsp->catatan)
                    <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-200 text-[11px] text-gray-600 mb-4">
                        <strong class="text-gray-700">Catatan Inspektor:</strong> {{ $cInsp->catatan }}
                    </div>
                    @endif
                    @else
                    <div class="p-4 rounded-xl bg-slate-50 border border-dashed border-gray-200 text-center text-xs text-gray-400 mb-4">
                        Formulir Inspeksi Kontainer belum dilengkapi oleh operator.
                    </div>
                    @endif
                </div>

                <div class="flex items-center gap-2 pt-3 border-t border-gray-100 flex-wrap">
                    @if($cInsp)
                        <a href="{{ route('shipments.container-inspection.print', $shipment->id) }}" target="_blank"
                           class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold transition-all">
                            <i class="ph-bold ph-printer"></i>
                            <span>Cetak Form</span>
                        </a>
                        <a href="{{ route('shipments.container-inspection.pdf', $shipment->id) }}"
                           class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 text-xs font-bold transition-all">
                            <i class="ph-bold ph-download-simple"></i>
                            <span>PDF Form</span>
                        </a>
                    @endif

                    @if(auth()->user()?->canEditShipment($shipment))
                        <a href="{{ route('field-app.container-inspection', $shipment->id) }}"
                           class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-spv-blue hover:bg-blue-800 text-white text-xs font-bold transition-all ml-auto">
                            <i class="ph-bold ph-pencil-simple"></i>
                            <span>{{ $cInsp ? 'Edit Form' : 'Isi Form Sekarang' }}</span>
                        </a>
                    @endif
                </div>
            </div>

            <!-- 2. Card Form Inspeksi Bale (Loading) -->
            @php $bInsp = $shipment->baleInspection; @endphp
            <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-[0_1px_6px_rgba(40,84,145,0.05)] flex flex-col justify-between">
                <div>
                    <div class="flex items-start justify-between gap-3 pb-3 border-b border-gray-100 mb-4">
                        <div class="flex items-center gap-2.5">
                            <div class="w-9 h-9 rounded-xl bg-spv-blue text-white flex items-center justify-center font-black text-sm shadow-xs">
                                B
                            </div>
                            <div>
                                <h3 class="text-xs font-extrabold text-gray-800 uppercase tracking-tight">Form Inspeksi Kondisi Bale (Loading)</h3>
                                <p class="text-[10px] text-gray-400">8 Cek Ketidaksesuaian &amp; Verifikasi 4-Eyes Principle</p>
                            </div>
                        </div>
                        @if($bInsp)
                            @php
                                $bBadge = match($bInsp->kesimpulan_akhir) {
                                    'layak' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                    'layak_dengan_catatan' => 'bg-amber-50 text-amber-700 border-amber-200',
                                    default => 'bg-red-50 text-red-700 border-red-200',
                                };
                            @endphp
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11px] font-bold border {{ $bBadge }}">
                                <i class="ph-bold ph-seal-check"></i>
                                {{ strtoupper($bInsp->kesimpulan_label) }}
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                <i class="ph-bold ph-warning"></i>
                                BELUM DIISI
                            </span>
                        @endif
                    </div>

                    @if($bInsp)
                    <div class="grid grid-cols-2 gap-3 text-xs mb-4">
                        <div>
                            <span class="text-gray-400 block text-[10px]">No. Surat Jalan:</span>
                            <span class="font-bold text-spv-blue font-mono">{{ $bInsp->nomor_surat_jalan ?: '-' }}</span>
                        </div>
                        <div>
                            <span class="text-gray-400 block text-[10px]">Jumlah Bale:</span>
                            <span class="font-bold text-gray-800">{{ $bInsp->jumlah_bale ? $bInsp->jumlah_bale . ' Bale' : '-' }}</span>
                        </div>
                        <div>
                            <span class="text-gray-400 block text-[10px]">Pemeriksa 1 (First Eye):</span>
                            <span class="font-semibold text-gray-800">{{ $bInsp->first_eye_nama ?: '-' }}</span>
                        </div>
                        <div>
                            <span class="text-gray-400 block text-[10px]">Verifikator (Second Eye):</span>
                            <span class="font-semibold text-gray-800">{{ $bInsp->second_eye_nama ?: '-' }}</span>
                        </div>
                    </div>

                    <!-- Ketidaksesuaian summary -->
                    @php $defectCount = $bInsp->countIssues(); @endphp
                    <div class="mb-4">
                        <div class="flex items-center justify-between text-xs p-2.5 rounded-xl border {{ $defectCount > 0 ? 'bg-amber-50 border-amber-200 text-amber-800' : 'bg-emerald-50 border-emerald-200 text-emerald-800' }}">
                            <span class="font-semibold">Temuan Cacat / Ketidaksesuaian:</span>
                            <span class="font-bold">{{ $defectCount > 0 ? "{$defectCount} Masalah Ditemukan" : "0 Temuan (Bale Mulus & Bersih)" }}</span>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 text-[11px] text-gray-500 mb-4 flex-wrap">
                        <span>2 Rubber Cone: <strong class="text-gray-700">{{ $bInsp->stagging_2_rubber_cone ? 'Ya' : 'Tidak' }}</strong></span>
                        <span>&bull;</span>
                        <span>Sumber: <strong class="text-gray-700">{{ ucfirst(str_replace('_', ' ', $bInsp->sumber_bale ?? '-')) }}</strong></span>
                        <span>&bull;</span>
                        <span>Lokasi: <strong class="text-gray-700">{{ $bInsp->lokasi_stagging ?: '-' }}</strong></span>
                    </div>
                    @else
                    <div class="p-4 rounded-xl bg-slate-50 border border-dashed border-gray-200 text-center text-xs text-gray-400 mb-4">
                        Formulir Inspeksi Kondisi Bale belum dilengkapi oleh operator.
                    </div>
                    @endif
                </div>

                <div class="flex items-center gap-2 pt-3 border-t border-gray-100 flex-wrap">
                    @if($bInsp)
                        <a href="{{ route('shipments.bale-inspection.print', $shipment->id) }}" target="_blank"
                           class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold transition-all">
                            <i class="ph-bold ph-printer"></i>
                            <span>Cetak Form</span>
                        </a>
                        <a href="{{ route('shipments.bale-inspection.pdf', $shipment->id) }}"
                           class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-blue-50 hover:bg-blue-100 text-spv-blue border border-blue-200 text-xs font-bold transition-all">
                            <i class="ph-bold ph-download-simple"></i>
                            <span>PDF Form</span>
                        </a>
                    @endif

                    @if(auth()->user()?->canEditShipment($shipment))
                        <a href="{{ route('field-app.bale-inspection', $shipment->id) }}"
                           class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-spv-blue hover:bg-blue-800 text-white text-xs font-bold transition-all ml-auto">
                            <i class="ph-bold ph-pencil-simple"></i>
                            <span>{{ $bInsp ? 'Edit Form' : 'Isi Form Sekarang' }}</span>
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Section: Timeline Riwayat Aktivitas & Perubahan Dokumen (Audit Trail) -->
    <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-[0_1px_6px_rgba(40,84,145,0.05)] mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-3 border-b border-gray-100 mb-4 gap-2">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-xl bg-blue-50 text-spv-blue flex items-center justify-center">
                    <i class="ph-bold ph-clock-counter-clockwise text-base"></i>
                </div>
                <div>
                    <h2 class="text-sm font-bold text-gray-800">Riwayat Dokumen &amp; Audit Trail Perubahan</h2>
                    <p class="text-[11px] text-gray-400">Pencatatan riwayat siapa yang mengunggah, mengubah data, dan waktu timestamp (WIB).</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-blue-50 text-spv-blue border border-blue-100">
                    <i class="ph-bold ph-git-commit"></i>
                    {{ $shipment->activities->count() }} Aktivitas Terekam
                </span>
            </div>
        </div>

        @if($shipment->activities->isNotEmpty())
            <div class="relative pl-6 sm:pl-8 space-y-6 before:absolute before:left-3 sm:before:left-4 before:top-2 before:bottom-2 before:w-0.5 before:bg-slate-200">
                @foreach($shipment->activities as $act)
                    @php
                        $badgeBg = match($act->user_role) {
                            'superadmin' => 'bg-purple-50 text-purple-700 border-purple-200',
                            'admin' => 'bg-blue-50 text-blue-700 border-blue-200',
                            'operator' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                            default => 'bg-gray-100 text-gray-700 border-gray-200',
                        };
                        $dotColor = match($act->action) {
                            'created' => 'bg-emerald-500 ring-emerald-100',
                            'submitted' => 'bg-spv-blue ring-blue-100',
                            'updated' => 'bg-amber-500 ring-amber-100',
                            default => 'bg-gray-400 ring-gray-100',
                        };
                        $iconClass = match($act->action) {
                            'created' => 'ph-file-plus',
                            'submitted' => 'ph-check-circle',
                            'updated' => 'ph-pencil-line',
                            default => 'ph-info',
                        };
                    @endphp
                    <div class="relative group">
                        <!-- Dot on timeline -->
                        <div class="absolute -left-6 sm:-left-8 top-1 w-6 h-6 sm:w-8 sm:h-8 rounded-full bg-white border-2 border-slate-300 flex items-center justify-center shadow-xs">
                            <span class="w-2.5 h-2.5 rounded-full {{ $dotColor }} ring-4"></span>
                        </div>

                        <!-- Card Content -->
                        <div class="bg-slate-50/70 hover:bg-slate-50 border border-slate-200/80 rounded-xl p-3.5 sm:p-4 transition-all">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1.5 mb-2">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="font-bold text-gray-800 text-xs flex items-center gap-1.5">
                                        <i class="ph-bold {{ $iconClass }} text-sm text-spv-blue"></i>
                                        {{ $act->title }}
                                    </span>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold border uppercase tracking-wider {{ $badgeBg }}">
                                        {{ $act->user_role }}
                                    </span>
                                </div>
                                <div class="flex items-center gap-1.5 text-[11px] text-gray-500 font-mono">
                                    <i class="ph-bold ph-calendar text-gray-400"></i>
                                    <span>{{ $act->created_at->format('d M Y, H:i:s') }} WIB</span>
                                    <span class="text-gray-300">&bull;</span>
                                    <span class="text-gray-400 font-sans text-[10px]">({{ $act->created_at->diffForHumans() }})</span>
                                </div>
                            </div>

                            <div class="text-xs text-gray-600 mb-2">
                                <span class="font-medium text-gray-700">Pelaksana:</span>
                                <strong class="text-gray-900">{{ $act->user_name }}</strong>
                                @if($act->description)
                                    <span class="text-gray-400 mx-1">&bull;</span>
                                    <span>{{ $act->description }}</span>
                                @endif
                            </div>

                            @if(!empty($act->changes) && is_array($act->changes))
                                <div class="mt-3 pt-2.5 border-t border-slate-200/70" x-data="{ expanded: false }">
                                    <div class="flex items-center justify-between">
                                        <span class="text-[11px] font-bold text-gray-700 flex items-center gap-1">
                                            <i class="ph-bold ph-arrows-clockwise text-spv-blue"></i>
                                            Detail Perubahan / Nilai Kolom:
                                        </span>
                                        <button type="button" @click="expanded = !expanded" class="text-[10px] text-spv-blue hover:underline font-semibold cursor-pointer">
                                            <span x-text="expanded ? 'Sembunyikan Rincian' : 'Lihat Rincian ({{ count($act->changes) }} kolom)'"></span>
                                        </button>
                                    </div>
                                    <div x-show="expanded" x-collapse class="mt-2 grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs">
                                        @foreach($act->changes as $key => $change)
                                            @if(is_array($change) && isset($change['new']))
                                                <div class="p-2 bg-white rounded-lg border border-slate-200">
                                                    <span class="block text-[10px] font-bold text-gray-500 uppercase">{{ $change['label'] ?? $key }}</span>
                                                    <div class="flex items-center gap-2 mt-1 text-[11px]">
                                                        <span class="text-red-500 line-through truncate max-w-[120px]">{{ $change['old'] ?? '-' }}</span>
                                                        <i class="ph-bold ph-arrow-right text-[10px] text-gray-400"></i>
                                                        <span class="text-emerald-700 font-bold truncate max-w-[160px]">{{ $change['new'] ?? '-' }}</span>
                                                    </div>
                                                </div>
                                            @else
                                                <div class="p-2 bg-white rounded-lg border border-slate-200">
                                                    <span class="block text-[10px] font-bold text-gray-500 uppercase">{{ $key }}</span>
                                                    <span class="text-gray-800 font-semibold">{{ is_array($change) ? json_encode($change) : $change }}</span>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <!-- Fallback jika dokumen dibuat sebelum sistem audit trail shipment_activities aktif -->
            <div class="bg-slate-50/70 rounded-xl p-4 border border-slate-200/80 text-xs text-gray-600 flex items-start gap-3">
                <i class="ph-bold ph-info text-spv-blue text-lg shrink-0 mt-0.5"></i>
                <div>
                    <p class="font-bold text-gray-800">Dokumen Awal Terdaftar</p>
                    <p class="text-gray-500 text-[11px] mt-0.5">
                        Dibuat pada <strong class="text-gray-700">{{ $shipment->created_at->format('d M Y, H:i:s') }} WIB</strong> 
                        oleh <strong class="text-gray-700">{{ $shipment->user?->nama_warehouse ?? 'Operator Lapangan' }}</strong>.
                    </p>
                    @if($shipment->status === 'submitted')
                        <p class="text-emerald-700 text-[11px] font-semibold mt-1">
                            Disubmit final pada {{ $shipment->submitted_at ? $shipment->submitted_at->format('d M Y, H:i:s') . ' WIB' : '-' }}.
                        </p>
                    @endif
                </div>
            </div>
        @endif
    </div>

    <!-- Section 3: Galeri Bukti Foto & Video SOP (27 Titik Inspeksi) -->
    @php
        $galleryItems = [];
        foreach ($points as $p) {
            $photo = $shipment->photos->firstWhere('point_no', $p->urutan);
            $evidence = $photo ?: $shipment->evidenceItems->firstWhere('sop_photo_point_id', $p->id);
            if ($evidence) {
                $filePath = $photo ? ($photo->path ?? $photo->stamped_path) : ($evidence->file_path ?? '');
                $fileName = $photo ? '' : ($evidence->file_name ?? '');
                $mime = $photo ? ($photo->mime ?? '') : '';
                $isVideo = ($p->tipe_item === 'video'
                    || ($evidence && ($evidence->tipe_item ?? null) === 'video')
                    || preg_match('/\.(mp4|mov|webm|3gp|avi|m4v|ogg)$/i', (string)($filePath . ' ' . $fileName))
                    || str_starts_with($mime, 'video/'));

                $mediaUrl = $photo ? $photo->stamped_url : asset('storage/' . $evidence->file_path);
                $origUrl = $photo ? $photo->original_url : asset('storage/' . $evidence->file_path);

                $galleryItems[] = [
                    'id' => 'point-' . $p->urutan,
                    'point_no' => (int) $p->urutan,
                    'is_video' => (bool) $isVideo,
                    'title' => 'Titik ' . $p->urutan . ': ' . $p->nama_titik,
                    'ts' => $photo ? $photo->stamped_at?->format('Y-m-d H:i:s \W\I\B') : ($evidence->captured_at ? $evidence->captured_at->format('Y-m-d H:i:s \W\I\B') : '-'),
                    'src' => $mediaUrl,
                    'orig' => $origUrl,
                    'thumb' => $photo ? $photo->thumbnail_url : asset('storage/' . $evidence->file_path),
                ];
            }
        }
        foreach ($shipment->extraPhotos as $extra) {
            $filePath = $extra->path ?? $extra->stamped_path ?? '';
            $isVideo = preg_match('/\.(mp4|mov|webm|3gp|avi|m4v|ogg)$/i', (string)$filePath) || str_starts_with($extra->mime ?? '', 'video/');
            $galleryItems[] = [
                'id' => 'extra-' . $extra->id,
                'point_no' => null,
                'is_video' => (bool) $isVideo,
                'title' => 'Foto Ekstra: ' . $extra->point_label,
                'ts' => $extra->stamped_at?->format('Y-m-d H:i:s \W\I\B') ?? '-',
                'src' => $extra->stamped_url,
                'orig' => $extra->original_url,
                'thumb' => $extra->thumbnail_url,
            ];
        }
    @endphp

    <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-[0_1px_6px_rgba(40,84,145,0.05)] mb-8"
         x-data="{
             modalOpen: false,
             currentIndex: 0,
             gallery: {{ Js::from($galleryItems) }},
             
             // Zoom & Pan Inspection State (khusus foto)
             zoom: 1,
             minZoom: 1,
             maxZoom: 4,
             panX: 0,
             panY: 0,
             isDragging: false,
             dragStartX: 0,
             dragStartY: 0,

             resetZoom() {
                 this.zoom = 1;
                 this.panX = 0;
                 this.panY = 0;
                 this.isDragging = false;
             },

             pauseVideo() {
                 this.$nextTick(() => {
                     const videos = document.querySelectorAll('.lightbox-video-player');
                     videos.forEach(v => {
                         try { v.pause(); } catch (e) {}
                     });
                 });
             },

             zoomIn() {
                 this.zoom = Math.min(this.maxZoom, Math.round((this.zoom + 0.35) * 100) / 100);
             },

             zoomOut() {
                 this.zoom = Math.max(this.minZoom, Math.round((this.zoom - 0.35) * 100) / 100);
                 if (this.zoom <= 1) {
                     this.resetZoom();
                 }
             },

             toggleZoom() {
                 if (this.zoom > 1) {
                     this.resetZoom();
                 } else {
                     this.zoom = 2;
                 }
             },

             handleWheel(e) {
                 const delta = e.deltaY < 0 ? 0.25 : -0.25;
                 const newZoom = Math.min(this.maxZoom, Math.max(this.minZoom, Math.round((this.zoom + delta) * 100) / 100));
                 this.zoom = newZoom;
                 if (this.zoom <= 1) {
                     this.resetZoom();
                 }
             },

             startDrag(e) {
                 if (this.zoom <= 1) return;
                 this.isDragging = true;
                 this.dragStartX = e.clientX - this.panX;
                 this.dragStartY = e.clientY - this.panY;
             },

             onDrag(e) {
                 if (!this.isDragging) return;
                 this.panX = e.clientX - this.dragStartX;
                 this.panY = e.clientY - this.dragStartY;
             },

             stopDrag() {
                 this.isDragging = false;
             },

             startTouch(e) {
                 if (this.zoom <= 1 || e.touches.length !== 1) return;
                 this.isDragging = true;
                 this.dragStartX = e.touches[0].clientX - this.panX;
                 this.dragStartY = e.touches[0].clientY - this.panY;
             },

             onTouch(e) {
                 if (!this.isDragging || e.touches.length !== 1) return;
                 this.panX = e.touches[0].clientX - this.dragStartX;
                 this.panY = e.touches[0].clientY - this.dragStartY;
             },

             stopTouch() {
                 this.isDragging = false;
             },

             openModal(idx) {
                 if (idx >= 0 && idx < this.gallery.length) {
                     this.resetZoom();
                     this.pauseVideo();
                     this.currentIndex = idx;
                     this.modalOpen = true;
                 }
             },
             closeModal() {
                 this.pauseVideo();
                 this.modalOpen = false;
             },
             openByPointNo(pointNo) {
                 const idx = this.gallery.findIndex(g => g.point_no === pointNo);
                 if (idx !== -1) {
                     this.openModal(idx);
                 }
             },
             openByExtraId(extraId) {
                 const idx = this.gallery.findIndex(g => g.id === 'extra-' + extraId);
                 if (idx !== -1) {
                     this.openModal(idx);
                 }
             },
             next() {
                 if (this.gallery.length <= 1) return;
                 this.pauseVideo();
                 this.resetZoom();
                 this.currentIndex = (this.currentIndex + 1) % this.gallery.length;
             },
             prev() {
                 if (this.gallery.length <= 1) return;
                 this.pauseVideo();
                 this.resetZoom();
                 this.currentIndex = (this.currentIndex - 1 + this.gallery.length) % this.gallery.length;
             },
             get currentItem() {
                 return this.gallery[this.currentIndex] || {};
             }
         }"
         @keydown.window.arrow-right="modalOpen && next()"
         @keydown.window.arrow-left="modalOpen && prev()"
         @keydown.window.escape="closeModal()"
         @keydown.window.plus="modalOpen && zoomIn()"
         @keydown.window.equal="modalOpen && zoomIn()"
         @keydown.window.minus="modalOpen && zoomOut()"
         @keydown.window.digit0="modalOpen && resetZoom()">

        <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 border-b border-gray-100 mb-5 gap-3">
            <div>
                <h2 class="text-sm font-bold text-gray-800 flex items-center gap-2">
                    <i class="ph-fill ph-camera text-spv-blue text-base"></i>
                    Galeri Loading Evidence (27 Titik SOP Inspeksi)
                </h2>
                <p class="text-xs text-gray-400 mt-0.5">Bukti foto & video dilengkapi stempel timestamp waktu nyata server (WIB) dan tersimpan secara permanen.</p>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                @php
                    $totalPhotosCount = count($galleryItems);
                    $extraPhotosCount = $shipment->extraPhotos->count();
                @endphp
                @if($totalPhotosCount > 0)
                    <a href="{{ route('shipments.download-evidence', $shipment->id) }}"
                       class="inline-flex items-center gap-1.5 text-xs font-bold px-3 py-1.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100 transition-colors shadow-xs"
                       title="Download seluruh bukti foto dalam format ZIP">
                        <i class="ph-bold ph-file-zip text-sm"></i>
                        <span>Download ZIP</span>
                    </a>
                    <a href="{{ route('shipments.download-pdf', $shipment->id) }}"
                       class="inline-flex items-center gap-1.5 text-xs font-bold px-3 py-1.5 rounded-full bg-red-50 text-red-700 border border-red-200 hover:bg-red-100 transition-colors shadow-xs"
                       title="Download dokumen laporan lengkap 27 foto (.PDF)">
                        <i class="ph-bold ph-file-pdf text-sm"></i>
                        <span>Download PDF</span>
                    </a>
                @endif
                <span class="text-xs font-bold px-3 py-1 rounded-full bg-blue-50 text-spv-blue border border-blue-100">
                    {{ $totalPhotosCount }} Bukti Tersimpan
                </span>
                @if($extraPhotosCount > 0)
                    <span class="text-xs font-bold px-2.5 py-1 rounded-full bg-purple-50 text-purple-700 border border-purple-100">
                        +{{ $extraPhotosCount }} Ekstra
                    </span>
                @endif
            </div>
        </div>

        <!-- 27 Titik SOP Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-4">
            @foreach($points as $point)
                @php
                    $photo = $shipment->photos->firstWhere('point_no', $point->urutan);
                    $evidence = $photo ?: $shipment->evidenceItems->firstWhere('sop_photo_point_id', $point->id);
                    $mediaUrl = $photo ? $photo->thumbnail_url : ($evidence ? asset('storage/' . $evidence->file_path) : null);
                    $stampedTime = $photo ? $photo->stamped_at?->format('d/m/Y H:i:s \W\I\B') : ($evidence?->captured_at ? $evidence->captured_at->format('Y-m-d H:i:s \W\I\B') : null);

                    $filePath = $photo ? ($photo->path ?? $photo->stamped_path) : ($evidence->file_path ?? '');
                    $fileName = $photo ? '' : ($evidence->file_name ?? '');
                    $mime = $photo ? ($photo->mime ?? '') : '';
                    $isVideo = ($point->tipe_item === 'video'
                        || ($evidence && ($evidence->tipe_item ?? null) === 'video')
                        || preg_match('/\.(mp4|mov|webm|3gp|avi|m4v|ogg)$/i', (string)($filePath . ' ' . $fileName))
                        || str_starts_with($mime, 'video/'));
                @endphp

                <div class="rounded-xl border {{ $evidence ? 'border-gray-200 bg-white shadow-sm' : 'border-dashed border-gray-200 bg-gray-50/50' }} overflow-hidden flex flex-col transition-all hover:shadow-md">
                    <!-- Photo Header / Point Label -->
                    <div class="p-3 border-b border-gray-100 flex items-center justify-between bg-white">
                        <div class="flex items-center gap-2">
                            <span class="w-5 h-5 rounded-full {{ $isVideo ? 'bg-rose-50 text-rose-600' : 'bg-blue-50 text-spv-blue' }} font-bold text-[10px] flex items-center justify-center shrink-0">
                                {{ $point->urutan }}
                            </span>
                            <p class="text-xs font-bold text-gray-800 truncate" title="{{ $point->nama_titik }}">{{ $point->nama_titik }}</p>
                        </div>
                        <div class="flex items-center gap-1.5 shrink-0">
                            @if($isVideo)
                                <span class="text-[9px] font-bold text-rose-600 bg-rose-50 border border-rose-200 px-1.5 py-0.5 rounded flex items-center gap-1">
                                    <i class="ph-fill ph-video-camera"></i> Video
                                </span>
                            @endif
                            @if($point->wajib)
                                <span class="text-[9px] font-bold text-red-500 bg-red-50 px-1.5 py-0.5 rounded shrink-0">Wajib</span>
                            @endif
                        </div>
                    </div>

                    <!-- Photo / Video Container -->
                    <div class="relative aspect-video bg-gray-950 overflow-hidden flex items-center justify-center group">
                        @if($evidence && $mediaUrl)
                            @if($isVideo)
                                <!-- Video Player Preview (First Frame) -->
                                <video src="{{ $mediaUrl }}#t=0.001"
                                       preload="metadata"
                                       muted
                                       playsinline
                                       class="w-full h-full object-cover pointer-events-none">
                                </video>

                                <!-- Play Badge Overlay -->
                                <div class="absolute inset-0 flex items-center justify-center pointer-events-none bg-black/20 group-hover:bg-black/40 transition-colors">
                                    <div class="w-12 h-12 rounded-full bg-black/60 backdrop-blur-md border border-white/30 text-white flex items-center justify-center shadow-2xl group-hover:scale-110 group-hover:bg-rose-600 group-hover:border-rose-500 transition-all">
                                        <i class="ph-fill ph-play text-xl translate-x-0.5 text-white"></i>
                                    </div>
                                </div>
                            @else
                                <img src="{{ $mediaUrl }}"
                                     alt="{{ $point->nama_titik }}"
                                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                            @endif
                            
                            <!-- Timestamp Badge (Prominent on Bottom of Media) -->
                            <div class="absolute bottom-2 left-2 right-2 bg-black/75 backdrop-blur-sm text-amber-300 px-2 py-1 rounded text-[10px] font-mono font-bold flex items-center justify-between border border-white/10 pointer-events-none">
                                <span class="flex items-center gap-1 truncate">
                                    <i class="ph-bold ph-calendar-check text-xs"></i>
                                    {{ $stampedTime ?: 'Waktu terekam' }}
                                </span>
                                @if($isVideo)
                                    <span class="flex items-center gap-1 text-white/90 text-[10px] font-sans font-semibold">
                                        <i class="ph-bold ph-play-circle text-xs"></i> Putar
                                    </span>
                                @else
                                    <i class="ph-bold ph-magnifying-glass-plus text-xs text-white/80"></i>
                                @endif
                            </div>

                            <!-- Click to Open Lightbox Modal / Player -->
                            <button type="button"
                                    id="btn-open-point-{{ $point->urutan }}"
                                    @click="openByPointNo({{ $point->urutan }})"
                                    class="absolute inset-0 w-full h-full bg-transparent cursor-pointer z-10"
                                    title="{{ $isVideo ? 'Klik untuk memutar video' : 'Klik untuk membuka viewer foto' }}">
                            </button>
                        @else
                            <div class="text-center p-4">
                                @if($isVideo)
                                    <i class="ph-bold ph-video-camera text-2xl text-gray-300 mb-1 block"></i>
                                    <span class="text-[10px] font-semibold text-gray-400">Video belum diunggah</span>
                                @else
                                    <i class="ph-bold ph-image text-2xl text-gray-300 mb-1 block"></i>
                                    <span class="text-[10px] font-semibold text-gray-400">Belum diunggah</span>
                                @endif
                            </div>
                        @endif
                    </div>

                    <!-- Photo/Video Details Footer -->
                    <div class="p-2.5 bg-gray-50/70 border-t border-gray-100 mt-auto text-[10px] text-gray-500 flex items-center justify-between">
                        <span class="truncate" title="{{ $point->deskripsi }}">{{ $point->deskripsi }}</span>
                        @if($evidence)
                            <span class="text-spv-green font-bold shrink-0 flex items-center gap-1">
                                <i class="ph-fill ph-check-circle"></i> Terverifikasi
                            </span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Section Foto Ekstra jika ada -->
        @if($shipment->extraPhotos->count() > 0)
            <div class="mt-8 pt-6 border-t border-gray-100">
                <h3 class="text-xs font-bold text-gray-700 uppercase tracking-wider mb-4 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-purple-500"></span>
                    Foto Bukti Ekstra / Tambahan ({{ $shipment->extraPhotos->count() }} Foto)
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-4">
                    @foreach($shipment->extraPhotos as $extra)
                        @php
                            $extraFilePath = $extra->path ?? $extra->stamped_path ?? '';
                            $isExtraVideo = preg_match('/\.(mp4|mov|webm|3gp|avi|m4v|ogg)$/i', (string)$extraFilePath) || str_starts_with($extra->mime ?? '', 'video/');
                        @endphp
                        <div class="rounded-xl border border-purple-100 bg-white shadow-sm overflow-hidden flex flex-col transition-all hover:shadow-md">
                            <div class="p-3 border-b border-gray-100 flex items-center justify-between bg-purple-50/30">
                                <div class="flex items-center gap-2">
                                    <span class="w-5 h-5 rounded-full bg-purple-100 text-purple-700 font-bold text-[10px] flex items-center justify-center shrink-0">+</span>
                                    <p class="text-xs font-bold text-gray-800 truncate">{{ $extra->point_label }}</p>
                                </div>
                                <span class="text-[9px] font-bold text-purple-600 bg-purple-50 px-1.5 py-0.5 rounded">Ekstra</span>
                            </div>

                            <div class="relative aspect-video bg-gray-950 overflow-hidden flex items-center justify-center group">
                                @if($isExtraVideo)
                                    <video src="{{ $extra->stamped_url }}#t=0.001" preload="metadata" muted playsinline class="w-full h-full object-cover pointer-events-none"></video>
                                    <div class="absolute inset-0 flex items-center justify-center pointer-events-none bg-black/20 group-hover:bg-black/40 transition-colors">
                                        <div class="w-12 h-12 rounded-full bg-black/60 backdrop-blur-md border border-white/30 text-white flex items-center justify-center shadow-xl group-hover:scale-110 group-hover:bg-rose-600 transition-all">
                                            <i class="ph-fill ph-play text-xl translate-x-0.5 text-white"></i>
                                        </div>
                                    </div>
                                @else
                                    <img src="{{ $extra->thumbnail_url }}" alt="{{ $extra->point_label }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                @endif
                                <div class="absolute bottom-2 left-2 right-2 bg-black/75 backdrop-blur-sm text-amber-300 px-2 py-1 rounded text-[10px] font-mono font-bold flex items-center justify-between border border-white/10 pointer-events-none">
                                    <span class="truncate">{{ $extra->stamped_at?->format('d/m/Y H:i:s \W\I\B') }}</span>
                                    <i class="ph-bold {{ $isExtraVideo ? 'ph-play-circle' : 'ph-magnifying-glass-plus' }} text-xs text-white/80"></i>
                                </div>
                                <button type="button"
                                        @click="openByExtraId({{ $extra->id }})"
                                        class="absolute inset-0 bg-transparent cursor-pointer z-10"
                                        title="{{ $isExtraVideo ? 'Klik untuk memutar video' : 'Klik untuk membuka viewer foto' }}">
                                </button>
                            </div>

                            <div class="p-2.5 bg-gray-50/70 border-t border-gray-100 mt-auto text-[10px] text-gray-500 flex items-center justify-between">
                                <span>{{ number_format($extra->size / 1024, 1) }} KB</span>
                                <span class="text-spv-green font-bold flex items-center gap-1">
                                    <i class="ph-fill ph-check-circle"></i> Terverifikasi
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Lightbox Modal Gallery Viewer with Video Player & Interactive Zoom / Pan for Photos -->
        <template x-teleport="body">
            <div x-cloak x-show="modalOpen" x-transition.opacity class="fixed inset-0 bg-black/90 backdrop-blur-md z-[9999] flex items-center justify-center p-2 sm:p-4 md:p-6" @click="closeModal()">
                <div class="bg-gray-950 border border-gray-800 rounded-2xl max-w-6xl w-full h-[92vh] max-h-[92vh] overflow-hidden shadow-2xl flex flex-col" @click.stop>
                    
                    <!-- Modal Header -->
                    <div class="px-4 py-3 border-b border-gray-800/80 bg-gray-900/95 flex items-center justify-between text-white shrink-0">
                        <div class="flex items-center gap-3 min-w-0 pr-3">
                            <span class="px-2.5 py-1 rounded-full bg-blue-500/20 text-blue-400 border border-blue-500/30 text-xs font-mono font-bold shrink-0"
                                  x-text="gallery.length > 0 ? (currentIndex + 1) + ' / ' + gallery.length : '0/0'">
                            </span>
                            <div class="truncate">
                                <h3 class="text-sm font-bold text-white truncate flex items-center gap-2">
                                    <template x-if="currentItem.is_video">
                                        <i class="ph-fill ph-video-camera text-rose-500 text-base shrink-0"></i>
                                    </template>
                                    <span x-text="currentItem.title"></span>
                                </h3>
                                <p class="text-xs text-amber-400 font-mono mt-0.5 flex items-center gap-1.5 truncate">
                                    <i class="ph-bold ph-clock"></i>
                                    <span>Timestamp: </span>
                                    <span x-text="currentItem.ts" class="font-semibold"></span>
                                </p>
                            </div>
                        </div>

                        <!-- Header Controls: Zoom Tools (khusus foto) & Close Button -->
                        <div class="flex items-center gap-2 shrink-0">
                            <template x-if="currentItem.is_video">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-rose-500/20 text-rose-400 border border-rose-500/30 text-xs font-bold">
                                    <i class="ph-fill ph-video-camera text-sm"></i>
                                    <span>Pemutar Video</span>
                                </span>
                            </template>

                            <template x-if="!currentItem.is_video">
                                <!-- Quick Zoom Controls in Header -->
                                <div class="flex items-center bg-gray-800/90 border border-gray-700/80 rounded-xl p-1 shadow-inner">
                                    <button type="button"
                                            @click="zoomOut()"
                                            :disabled="zoom <= minZoom"
                                            class="w-7 h-7 rounded-lg flex items-center justify-center text-gray-300 hover:text-white hover:bg-gray-700 active:scale-95 disabled:opacity-40 disabled:hover:bg-transparent transition-all"
                                            title="Perkecil Zoom (-)">
                                        <i class="ph-bold ph-minus text-xs"></i>
                                    </button>
                                    
                                    <button type="button"
                                            @click="resetZoom()"
                                            class="px-2 py-0.5 rounded text-xs font-mono font-bold text-amber-300 hover:bg-gray-700/80 transition-colors"
                                            title="Klik untuk reset zoom (100% Fit)">
                                        <span x-text="Math.round(zoom * 100) + '%'"></span>
                                    </button>

                                    <button type="button"
                                            @click="zoomIn()"
                                            :disabled="zoom >= maxZoom"
                                            class="w-7 h-7 rounded-lg flex items-center justify-center text-gray-300 hover:text-white hover:bg-gray-700 active:scale-95 disabled:opacity-40 disabled:hover:bg-transparent transition-all"
                                            title="Perbesar Zoom (+)">
                                        <i class="ph-bold ph-plus text-xs"></i>
                                    </button>

                                    <div class="w-px h-4 bg-gray-700 mx-1"></div>

                                    <button type="button"
                                            @click="resetZoom()"
                                            class="px-2 py-1 rounded-lg text-[11px] font-semibold text-gray-300 hover:text-white hover:bg-gray-700 active:scale-95 transition-all flex items-center gap-1"
                                            title="Reset Tampilan (Fit)">
                                        <i class="ph-bold ph-arrows-in text-xs"></i>
                                        <span class="hidden sm:inline">Fit</span>
                                    </button>
                                </div>
                            </template>

                            <!-- Keyboard shortcut hint -->
                            <span class="hidden lg:inline-flex text-[11px] text-gray-400 font-mono items-center gap-1 bg-gray-800/70 px-2 py-1.5 rounded-lg border border-gray-700/60">
                                <kbd class="text-gray-300">←</kbd> <kbd class="text-gray-300">→</kbd> panah
                            </span>

                            <button @click="closeModal()" class="text-gray-400 hover:text-white p-2 rounded-xl bg-gray-800 hover:bg-gray-700 border border-gray-700 transition-colors" title="Tutup (Esc)">
                                <i class="ph-bold ph-x text-lg"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Image / Video Area with Floating Left / Right Buttons -->
                    <div class="relative bg-black flex-1 min-h-0 min-w-0 overflow-hidden select-none">
                        
                        <!-- Floating Tombol Navigasi KIRI (Prev) -->
                        <button type="button"
                                x-show="gallery.length > 1"
                                @click.stop="prev()"
                                class="absolute left-3 sm:left-5 top-1/2 -translate-y-1/2 z-30 w-11 h-11 sm:w-13 sm:h-13 rounded-full bg-black/60 hover:bg-black/90 active:scale-90 text-white flex items-center justify-center border border-white/20 shadow-2xl backdrop-blur-md transition-all hover:scale-110 group/btn"
                                title="Sebelumnya (Panah Kiri)">
                            <i class="ph-bold ph-caret-left text-2xl text-white group-hover/btn:-translate-x-0.5 transition-transform"></i>
                        </button>

                        <!-- Pan & Zoom Canvas Container (Guaranteed 100% Fit without Crop) -->
                        <div class="absolute inset-0 p-3 sm:p-5 flex items-center justify-center overflow-hidden"
                             @wheel.prevent="!currentItem.is_video && handleWheel($event)"
                             @mousedown="!currentItem.is_video && startDrag($event)"
                             @mousemove="!currentItem.is_video && onDrag($event)"
                             @mouseup="!currentItem.is_video && stopDrag()"
                             @mouseleave="!currentItem.is_video && stopDrag()"
                             @touchstart="!currentItem.is_video && startTouch($event)"
                             @touchmove="!currentItem.is_video && onTouch($event)"
                             @touchend="!currentItem.is_video && stopTouch()"
                             :class="{
                                 'cursor-grab': !currentItem.is_video && zoom > 1 && !isDragging,
                                 'cursor-grabbing': !currentItem.is_video && isDragging,
                                 'cursor-zoom-in': !currentItem.is_video && zoom === 1
                             }">
                            
                            <!-- Video Player (When current item is a video) -->
                            <template x-if="currentItem.is_video">
                                <div class="w-full h-full flex flex-col items-center justify-center p-2 sm:p-4 z-20" @click.stop>
                                    <div class="relative max-w-4xl w-full flex items-center justify-center">
                                        <video :src="currentItem.src"
                                               controls
                                               autoplay
                                               playsinline
                                               class="lightbox-video-player max-w-full max-h-[66vh] sm:max-h-[70vh] rounded-2xl shadow-2xl bg-black border border-gray-800 outline-none">
                                            Browser Anda tidak mendukung pemutar video HTML5.
                                        </video>
                                    </div>
                                    <p class="text-xs text-gray-400 mt-2.5 flex items-center gap-1.5 font-sans">
                                        <i class="ph-fill ph-video-camera text-rose-500 text-sm"></i>
                                        <span>Video loading / inspeksi SOP armada PT. South Pacific Viscose.</span>
                                    </p>
                                </div>
                            </template>

                            <!-- Photo Viewer with Pan & Zoom (When current item is a photo) -->
                            <template x-if="!currentItem.is_video">
                                <img :src="currentItem.src"
                                     :alt="currentItem.title"
                                     @dblclick="toggleZoom()"
                                     draggable="false"
                                     :style="`transform: translate3d(${panX}px, ${panY}px, 0) scale(${zoom}); transition: ${isDragging ? 'none' : 'transform 0.15s cubic-bezier(0.2, 0, 0, 1)'};`"
                                     class="max-w-full max-h-full w-auto h-auto object-contain rounded-lg shadow-2xl select-none pointer-events-auto origin-center">
                            </template>
                        </div>

                        <!-- Floating Tombol Navigasi KANAN (Next) -->
                        <button type="button"
                                x-show="gallery.length > 1"
                                @click.stop="next()"
                                class="absolute right-3 sm:right-5 top-1/2 -translate-y-1/2 z-30 w-11 h-11 sm:w-13 sm:h-13 rounded-full bg-black/60 hover:bg-black/90 active:scale-90 text-white flex items-center justify-center border border-white/20 shadow-2xl backdrop-blur-md transition-all hover:scale-110 group/btn"
                                title="Selanjutnya (Panah Kanan)">
                            <i class="ph-bold ph-caret-right text-2xl text-white group-hover/btn:translate-x-0.5 transition-transform"></i>
                        </button>
                    </div>

                    <!-- Modal Footer -->
                    <div class="px-4 py-2.5 bg-gray-900/95 border-t border-gray-800/80 flex flex-wrap items-center justify-between gap-3 shrink-0">
                        <div class="flex items-center gap-2">
                            <template x-if="currentItem.orig">
                                <a :href="currentItem.orig" target="_blank"
                                   class="text-xs font-semibold text-gray-300 hover:text-white bg-gray-800 hover:bg-gray-700 px-3 py-1.5 rounded-lg inline-flex items-center gap-1.5 border border-gray-700 transition-colors">
                                    <i class="ph-bold ph-file-arrow-down text-sm"></i>
                                    <span x-text="currentItem.is_video ? 'Unduh Video Asli' : 'Unduh File Asli'"></span>
                                </a>
                            </template>

                            <a href="{{ route('shipments.download-evidence', $shipment->id) }}"
                               class="text-xs font-semibold text-emerald-300 hover:text-white bg-emerald-950/60 hover:bg-emerald-900 px-3 py-1.5 rounded-lg inline-flex items-center gap-1.5 border border-emerald-700/60 transition-colors"
                               title="Download seluruh bukti foto dalam format ZIP ({{ $shipment->packing_list_no ?: $shipment->nomor_container_atau_plat }}.zip)">
                                <i class="ph-bold ph-file-zip text-sm"></i>
                                <span>Unduh Semua (.ZIP)</span>
                            </a>

                            <template x-if="!currentItem.is_video">
                                <span class="hidden md:inline-flex items-center gap-1.5 text-[11px] text-gray-400 bg-gray-800/50 px-2.5 py-1 rounded-md border border-gray-800">
                                    <i class="ph-bold ph-hand-pointing text-xs text-amber-400"></i>
                                    <span>Tip: Dobel-klik atau scroll mouse untuk Zoom, geser kursor untuk inspeksi detail</span>
                                </span>
                            </template>
                        </div>

                        <!-- Center Navigation Controls -->
                        <div class="flex items-center gap-2" x-show="gallery.length > 1">
                            <button type="button"
                                    @click="prev()"
                                    class="px-3 py-1.5 rounded-lg bg-gray-800 hover:bg-gray-700 text-white text-xs font-semibold flex items-center gap-1.5 border border-gray-700 active:scale-95 transition-all">
                                <i class="ph-bold ph-caret-left text-sm"></i> Sebelumnya
                            </button>

                            <div class="px-3 py-1 rounded-md bg-black/50 border border-gray-800 text-xs font-mono text-gray-300">
                                <span x-text="currentIndex + 1"></span> / <span x-text="gallery.length"></span>
                            </div>

                            <button type="button"
                                    @click="next()"
                                    class="px-3 py-1.5 rounded-lg bg-gray-800 hover:bg-gray-700 text-white text-xs font-semibold flex items-center gap-1.5 border border-gray-700 active:scale-95 transition-all">
                                Selanjutnya <i class="ph-bold ph-caret-right text-sm"></i>
                            </button>
                        </div>

                        <div class="flex items-center gap-2">
                            <a :href="currentItem.src" target="_blank"
                               class="text-xs font-semibold text-white bg-spv-blue hover:bg-blue-700 px-3.5 py-1.5 rounded-lg inline-flex items-center gap-1.5 shadow-sm transition-colors">
                                <i class="ph-bold ph-arrow-square-out text-sm"></i>
                                <span x-text="currentItem.is_video ? 'Buka Video di Tab Baru' : 'Buka Gambar Penuh'"></span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>

    <!-- Modal Konfirmasi Hapus Shipment (Superadmin Only) -->
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
                Apakah Anda yakin ingin menghapus dokumen shipment <strong class="text-gray-700">{{ $shipment->packing_list_no ?: ($shipment->nomor_container_atau_plat ?: 'Shipment #' . $shipment->id) }}</strong>?
                Seluruh berkas bukti foto evidence dan riwayat dokumen ini akan dihapus secara permanen.
            </p>

            <form method="POST" action="{{ route('shipments.destroy', $shipment->id) }}" class="mt-6 flex items-center gap-3">
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
</x-layout>
