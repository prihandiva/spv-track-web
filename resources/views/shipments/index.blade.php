<x-layout :title="'Daftar Shipments'">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-xl font-bold text-gray-800">Daftar Shipments</h1>
            <p class="text-xs text-gray-500 mt-1">Kelola dan pantau semua shipment dari lapangan.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('field-app.create') }}" target="_blank"
               class="flex items-center gap-2 bg-spv-blue hover:bg-blue-800 text-white text-xs font-bold px-4 py-2.5 rounded-xl shadow-[0_4px_12px_rgba(40,84,145,0.25)] hover:-translate-y-0.5 transition-all">
                <i class="ph-bold ph-plus text-sm"></i>
                Buat Shipment
            </a>
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
</x-layout>
