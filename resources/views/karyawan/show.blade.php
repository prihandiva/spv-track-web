<x-layout :title="'Detail Petugas: ' . $karyawan->nama">

    <!-- Breadcrumb & Header -->
    <div class="mb-6">
        <div class="flex items-center gap-2 text-xs text-gray-400 mb-2">
            <a href="{{ route('dashboard') }}" class="hover:text-spv-blue transition-colors">Dashboard</a>
            <i class="ph-bold ph-caret-right text-[10px]"></i>
            <a href="{{ route('karyawan.index') }}" class="hover:text-spv-blue transition-colors">Petugas</a>
            <i class="ph-bold ph-caret-right text-[10px]"></i>
            <span class="text-gray-700 font-semibold">{{ $karyawan->nama }}</span>
        </div>

        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold text-gray-800">Detail & Riwayat Kinerja Petugas</h1>
                <p class="text-xs text-gray-500 mt-1">Pantau seluruh catatan pengiriman dan pemenuhan SOP oleh petugas ini.</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('karyawan.edit', $karyawan->id) }}"
                   class="inline-flex items-center gap-1.5 bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold px-4 py-2.5 rounded-xl shadow-sm transition-all">
                    <i class="ph-bold ph-pencil-simple text-sm"></i>
                    Edit Petugas
                </a>
                <a href="{{ route('karyawan.index') }}"
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-gray-200 text-xs font-semibold text-gray-600 hover:bg-gray-100 transition-colors">
                    <i class="ph-bold ph-arrow-left text-sm"></i>
                    Kembali
                </a>
            </div>
        </div>
    </div>

    <!-- Officer Profile Banner -->
    <div class="bg-white rounded-2xl border border-gray-100 p-6 shadow-[0_1px_6px_rgba(40,84,145,0.05)] mb-6">
        @php
            $initials = collect(explode(' ', $karyawan->nama))
                ->map(fn($segment) => mb_substr($segment, 0, 1))
                ->take(2)
                ->implode('');
        @endphp
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-5">
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 rounded-2xl flex items-center justify-center font-extrabold text-xl shadow-sm shrink-0"
                     style="background: linear-gradient(135deg, {{ $karyawan->status === 'aktif' ? '#e1f8eb, #63c384' : '#f3f4f6, #d1d5db' }}); color: {{ $karyawan->status === 'aktif' ? '#0d5950' : '#4b5563' }};">
                    {{ strtoupper($initials) }}
                </div>
                <div>
                    <div class="flex items-center gap-2.5 flex-wrap">
                        <h2 class="text-lg font-bold text-gray-800">{{ $karyawan->nama }}</h2>
                        @if($karyawan->status === 'aktif')
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                Aktif Bertugas
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-gray-100 text-gray-600 border border-gray-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span>
                                Nonaktif
                            </span>
                        @endif
                    </div>
                    <div class="flex items-center gap-3 text-xs text-gray-400 mt-1.5 flex-wrap">
                        <span class="inline-flex items-center gap-1 font-mono font-semibold text-gray-700 bg-gray-100 px-2 py-0.5 rounded">
                            <i class="ph-bold ph-identification-badge"></i>
                            {{ $karyawan->nomor_induk }}
                        </span>
                        <span>&bull;</span>
                        <span>Terdaftar: {{ $karyawan->created_at ? $karyawan->created_at->format('d M Y, H:i') . ' WIB' : '—' }}</span>
                    </div>
                </div>
            </div>

            <!-- Toggle Status Action -->
            <form action="{{ route('karyawan.toggle-status', $karyawan->id) }}" method="POST" class="shrink-0">
                @csrf
                @method('PATCH')
                <button type="submit"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold border transition-colors {{ $karyawan->status === 'aktif' ? 'border-amber-200 text-amber-700 bg-amber-50 hover:bg-amber-100' : 'border-emerald-200 text-emerald-700 bg-emerald-50 hover:bg-emerald-100' }}">
                    <i class="ph-bold ph-arrows-clockwise text-sm"></i>
                    {{ $karyawan->status === 'aktif' ? 'Nonaktifkan Petugas' : 'Aktifkan Petugas' }}
                </button>
            </form>
        </div>
    </div>

    <!-- Quick Stats Cards for this Officer -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-[0_1px_6px_rgba(40,84,145,0.04)] flex items-center gap-3.5">
            <div class="w-10 h-10 rounded-xl bg-blue-50 text-spv-blue flex items-center justify-center shrink-0">
                <i class="ph-fill ph-package text-xl"></i>
            </div>
            <div>
                <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Total Staging</p>
                <p class="text-xl font-extrabold text-gray-800">{{ $karyawan->shipments_count }}</p>
            </div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-[0_1px_6px_rgba(40,84,145,0.04)] flex items-center gap-3.5">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                <i class="ph-fill ph-check-circle text-xl"></i>
            </div>
            <div>
                <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Submitted / Selesai</p>
                <p class="text-xl font-extrabold text-emerald-600">{{ $totalSubmitted }}</p>
            </div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-[0_1px_6px_rgba(40,84,145,0.04)] flex items-center gap-3.5">
            <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                <i class="ph-fill ph-clock-countdown text-xl"></i>
            </div>
            <div>
                <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Draft / Dalam Proses</p>
                <p class="text-xl font-extrabold text-amber-600">{{ $totalDraft }}</p>
            </div>
        </div>
    </div>

    <!-- Shipments Handled Section -->
    <div class="bg-white rounded-2xl shadow-[0_1px_6px_rgba(40,84,145,0.05)] border border-gray-100 overflow-hidden">
        <div class="p-5 border-b border-gray-100 flex items-center justify-between" style="background:#fafbfd;">
            <div>
                <h3 class="text-sm font-bold text-gray-800">Riwayat Staging yang Ditangani</h3>
                <p class="text-[10px] text-gray-400 mt-0.5">Daftar pengiriman barang dengan petugas terkait</p>
            </div>
            <span class="text-xs font-bold text-spv-blue bg-blue-50 px-3 py-1 rounded-full">
                {{ $shipments->total() }} Pengiriman
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left whitespace-nowrap">
                <thead style="background:#fafbfd;">
                    <tr>
                        <th class="px-5 py-3 text-[10px] font-bold text-gray-400 uppercase tracking-wider">No. Packing List / Container</th>
                        <th class="px-5 py-3 text-[10px] font-bold text-gray-400 uppercase tracking-wider">Jenis Produk</th>
                        <th class="px-5 py-3 text-[10px] font-bold text-gray-400 uppercase tracking-wider">Tipe</th>
                        <th class="px-5 py-3 text-[10px] font-bold text-gray-400 uppercase tracking-wider">Tanggal & Waktu</th>
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
                            <td class="px-5 py-3.5"><p class="text-[11px] text-gray-500 font-medium">{{ $shipment->created_at->format('d M Y, H:i') }} WIB</p></td>
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
                                       title="Lihat Detail Shipment"
                                       class="p-1.5 rounded-lg transition-all text-gray-400 hover:text-spv-blue hover:bg-blue-50">
                                        <i class="ph-bold ph-eye text-base"></i>
                                    </a>
                                    <a href="{{ route('field-app.timeline', $shipment->id) }}"
                                       title="Buka Foto Evidence"
                                       target="_blank"
                                       class="p-1.5 rounded-lg transition-all text-gray-400 hover:text-spv-green hover:bg-emerald-50">
                                        <i class="ph-bold ph-camera text-base"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-10">
                                <div class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-2 text-gray-400">
                                    <i class="ph-bold ph-package text-xl"></i>
                                </div>
                                <p class="text-xs font-bold text-gray-700">Belum ada staging yang ditangani oleh petugas ini</p>
                                <p class="text-[10px] text-gray-400 mt-0.5">Staging yang di-assign ke petugas ini akan otomatis tampil di sini.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($shipments->hasPages())
            <div class="p-4 border-t border-gray-100 flex items-center justify-between">
                <p class="text-[11px] text-gray-500">Menampilkan {{ $shipments->firstItem() }}-{{ $shipments->lastItem() }} dari {{ $shipments->total() }} staging</p>
                {{ $shipments->links() }}
            </div>
        @endif
    </div>

</x-layout>
