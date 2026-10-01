<x-layout :title="'Detail Shipment: ' . ($shipment->nomor_container_atau_plat ?? 'SPV-' . $shipment->id)">

    <!-- Top Navigation & Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div class="flex items-center gap-3">
            <a href="{{ route('shipments.index') }}"
               class="flex items-center justify-center w-9 h-9 rounded-xl bg-white border border-gray-200 text-gray-600 hover:text-spv-blue hover:border-spv-blue transition-all shadow-sm">
                <i class="ph-bold ph-arrow-left text-base"></i>
            </a>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-xl font-bold text-gray-800 leading-tight">
                        {{ $shipment->nomor_container_atau_plat ?? 'Shipment #' . $shipment->id }}
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
                <p class="text-xs text-gray-500 mt-0.5">
                    Dibuat pada {{ $shipment->created_at->format('d M Y, H:i') }} WIB &bull; Petugas: <strong class="text-gray-700">{{ $shipment->karyawan?->nama ?? '-' }}</strong>
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <button onclick="window.print()" type="button"
                    class="flex items-center gap-2 bg-white border border-gray-200 text-gray-700 hover:text-spv-blue hover:border-spv-blue text-xs font-bold px-3.5 py-2.5 rounded-xl shadow-sm transition-all">
                <i class="ph-bold ph-printer text-base"></i>
                <span class="hidden sm:inline">Cetak Dokumen</span>
            </button>
            <a href="{{ route('field-app.timeline', $shipment->id) }}" target="_blank"
               class="flex items-center gap-2 bg-spv-blue hover:bg-blue-800 text-white text-xs font-bold px-4 py-2.5 rounded-xl shadow-[0_4px_12px_rgba(40,84,145,0.25)] hover:-translate-y-0.5 transition-all">
                <i class="ph-bold ph-device-mobile text-base"></i>
                <span>Buka di Field App</span>
            </a>
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
            <p class="text-sm font-bold text-gray-800 mt-1 capitalize">Warehouse {{ $shipment->warehouse_lokasi }}</p>
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
                    <span class="text-gray-400 block text-[11px] mb-0.5">Petugas Pelaksana:</span>
                    <span class="font-bold text-gray-800">{{ $shipment->karyawan?->nama ?? '-' }} ({{ $shipment->karyawan?->nomor_induk ?? '-' }})</span>
                </div>
                <div>
                    <span class="text-gray-400 block text-[11px] mb-0.5">Jenis Produk:</span>
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold capitalize {{ $shipment->jenis_produk === 'fiber' ? 'bg-blue-50 text-spv-blue' : 'bg-emerald-50 text-emerald-700' }}">
                        {{ $shipment->jenis_produk }}
                    </span>
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
                    <span class="font-semibold text-gray-700 capitalize">Gudang {{ $shipment->warehouse_lokasi }}</span>
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
                    <span class="text-gray-400 block text-[11px] mb-0.5">Waktu Kedatangan Kontainer:</span>
                    <span class="font-semibold text-gray-700">{{ $shipment->waktu_kedatangan_container ? $shipment->waktu_kedatangan_container->format('d M Y, H:i') : '—' }}</span>
                </div>
                <div>
                    <span class="text-gray-400 block text-[11px] mb-0.5">Waktu Keberangkatan:</span>
                    <span class="font-semibold text-gray-700">{{ $shipment->waktu_keberangkatan_container ? $shipment->waktu_keberangkatan_container->format('d M Y, H:i') : '—' }}</span>
                </div>
            </div>
        </div>

    </div>

    <!-- Section 3: Galeri Bukti Foto SOP (27 Titik Inspeksi) -->
    <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-[0_1px_6px_rgba(40,84,145,0.05)] mb-8" x-data="{ modalOpen: false, modalSrc: '', modalTitle: '', modalTs: '' }">
        <div class="flex items-center justify-between pb-4 border-b border-gray-100 mb-5">
            <div>
                <h2 class="text-sm font-bold text-gray-800 flex items-center gap-2">
                    <i class="ph-fill ph-camera text-spv-blue text-base"></i>
                    Galeri Bukti Foto SOP (27 Titik Inspeksi)
                </h2>
                <p class="text-xs text-gray-400 mt-0.5">Setiap foto dilengkapi stempel timestamp waktu nyata saat foto diambil.</p>
            </div>
            <span class="text-xs font-bold px-3 py-1 rounded-full bg-blue-50 text-spv-blue border border-blue-100">
                {{ $shipment->evidenceItems->count() }} / {{ count($points) }} Titik Selesai
            </span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-4">
            @foreach($points as $point)
                @php
                    $evidence = $shipment->evidenceItems->firstWhere('sop_photo_point_id', $point->id);
                @endphp

                <div class="rounded-xl border {{ $evidence ? 'border-gray-200 bg-white shadow-sm' : 'border-dashed border-gray-200 bg-gray-50/50' }} overflow-hidden flex flex-col transition-all hover:shadow-md">
                    <!-- Photo Header / Point Label -->
                    <div class="p-3 border-b border-gray-100 flex items-center justify-between bg-white">
                        <div class="flex items-center gap-2">
                            <span class="w-5 h-5 rounded-full bg-blue-50 text-spv-blue font-bold text-[10px] flex items-center justify-center shrink-0">
                                {{ $point->urutan }}
                            </span>
                            <p class="text-xs font-bold text-gray-800 truncate" title="{{ $point->nama_titik }}">{{ $point->nama_titik }}</p>
                        </div>
                        @if($point->wajib)
                            <span class="text-[9px] font-bold text-red-500 bg-red-50 px-1.5 py-0.5 rounded shrink-0">Wajib</span>
                        @endif
                    </div>

                    <!-- Photo Container -->
                    <div class="relative aspect-video bg-gray-900 overflow-hidden flex items-center justify-center group">
                        @if($evidence)
                            <img src="{{ asset('storage/' . $evidence->file_path) }}"
                                 alt="{{ $point->nama_titik }}"
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                            
                            <!-- Timestamp Badge (Prominent on Bottom of Image) -->
                            <div class="absolute bottom-2 left-2 right-2 bg-black/75 backdrop-blur-sm text-green-400 px-2 py-1 rounded text-[10px] font-mono font-bold flex items-center justify-between border border-white/10">
                                <span class="flex items-center gap-1 truncate">
                                    <i class="ph-bold ph-calendar-check text-xs"></i>
                                    {{ $evidence->captured_at ? $evidence->captured_at->format('Y-m-d H:i:s') . ' WIB' : 'Waktu terekam' }}
                                </span>
                                <i class="ph-bold ph-magnifying-glass-plus text-xs text-white/80"></i>
                            </div>

                            <!-- Click to Zoom Button -->
                            <button type="button"
                                    @click="modalSrc = '{{ asset('storage/' . $evidence->file_path) }}'; modalTitle = '{{ $point->urutan }}. {{ addslashes($point->nama_titik) }}'; modalTs = '{{ $evidence->captured_at ? $evidence->captured_at->format('Y-m-d H:i:s \W\I\B') : '-' }}'; modalOpen = true"
                                    class="absolute inset-0 bg-transparent cursor-pointer">
                            </button>
                        @else
                            <div class="text-center p-4">
                                <i class="ph-bold ph-image text-2xl text-gray-300 mb-1 block"></i>
                                <span class="text-[10px] font-semibold text-gray-400">Belum diunggah</span>
                            </div>
                        @endif
                    </div>

                    <!-- Photo Details Footer -->
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

        <!-- Lightbox Modal for Photo Zoom -->
        <div x-cloak x-show="modalOpen" x-transition.opacity class="fixed inset-0 bg-black/80 backdrop-blur-md z-50 flex items-center justify-center p-4" @click="modalOpen = false">
            <div class="bg-gray-900 border border-gray-700 rounded-2xl max-w-4xl w-full overflow-hidden shadow-2xl" @click.stop>
                <div class="p-4 border-b border-gray-800 flex items-center justify-between text-white">
                    <div>
                        <h3 class="text-sm font-bold" x-text="modalTitle"></h3>
                        <p class="text-xs text-green-400 font-mono mt-0.5 flex items-center gap-1.5">
                            <i class="ph-bold ph-clock"></i> Timestamp Pengambilan: <span x-text="modalTs"></span>
                        </p>
                    </div>
                    <button @click="modalOpen = false" class="text-gray-400 hover:text-white p-2 rounded-lg bg-gray-800">
                        <i class="ph-bold ph-x text-lg"></i>
                    </button>
                </div>
                <div class="p-2 bg-black flex items-center justify-center max-h-[75vh]">
                    <img :src="modalSrc" class="max-h-[70vh] w-auto max-w-full object-contain rounded-lg">
                </div>
                <div class="p-3 bg-gray-900 border-t border-gray-800 flex justify-end">
                    <a :href="modalSrc" target="_blank" class="text-xs font-semibold text-spv-blue bg-white px-3 py-1.5 rounded-lg flex items-center gap-1.5">
                        <i class="ph-bold ph-arrow-square-out"></i> Buka Gambar Penuh
                    </a>
                </div>
            </div>
        </div>
    </div>

</x-layout>
