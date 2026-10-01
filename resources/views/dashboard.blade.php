<x-layout :title="'Dashboard'">

    <!-- Quick Stats -->
    <div class="grid grid-cols-2 xl:grid-cols-4 gap-4 mb-5">

        <div class="bg-white rounded-2xl p-4 lg:p-5 group cursor-default transition-all duration-300 hover:-translate-y-0.5"
             style="border: 1px solid #e8edf3; box-shadow: 0 1px 6px rgba(40,84,145,0.05);"
             onmouseover="this.style.boxShadow='0 8px 24px rgba(40,84,145,0.12)'; this.style.borderColor='#c5d4ea'"
             onmouseout="this.style.boxShadow='0 1px 6px rgba(40,84,145,0.05)'; this.style.borderColor='#e8edf3'">
            <div class="flex items-start justify-between mb-3">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center transition-transform duration-300 group-hover:scale-110" style="background:#e1f8eb;">
                    <i class="ph-fill ph-package text-xl" style="color:#059e3d;"></i>
                </div>
                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full flex items-center gap-1" style="background:#e1f8eb; color:#059e3d;">
                    <i class="ph-bold ph-trend-up text-xs"></i>Live
                </span>
            </div>
            <p class="text-xs font-medium text-gray-400 mb-0.5">Total Shipments</p>
            <p class="text-2xl font-bold text-gray-800">{{ $totalShipments ?? \App\Models\Shipment::count() }}</p>
        </div>

        <div class="bg-white rounded-2xl p-4 lg:p-5 group cursor-default transition-all duration-300 hover:-translate-y-0.5"
             style="border: 1px solid #e8edf3; box-shadow: 0 1px 6px rgba(40,84,145,0.05);"
             onmouseover="this.style.boxShadow='0 8px 24px rgba(40,84,145,0.12)'; this.style.borderColor='#c5d4ea'"
             onmouseout="this.style.boxShadow='0 1px 6px rgba(40,84,145,0.05)'; this.style.borderColor='#e8edf3'">
            <div class="flex items-start justify-between mb-3">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center transition-transform duration-300 group-hover:scale-110" style="background:#eff4fc;">
                    <i class="ph-fill ph-truck text-xl" style="color:#285491;"></i>
                </div>
                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full flex items-center gap-1" style="background:#eff4fc; color:#285491;">
                    <i class="ph-bold ph-check-circle text-xs"></i>Selesai
                </span>
            </div>
            <p class="text-xs font-medium text-gray-400 mb-0.5">Submitted</p>
            <p class="text-2xl font-bold text-gray-800">{{ $totalSubmitted ?? \App\Models\Shipment::where('status','submitted')->count() }}</p>
        </div>

        <div class="bg-white rounded-2xl p-4 lg:p-5 group cursor-default transition-all duration-300 hover:-translate-y-0.5"
             style="border: 1px solid #e8edf3; box-shadow: 0 1px 6px rgba(40,84,145,0.05);"
             onmouseover="this.style.boxShadow='0 8px 24px rgba(40,84,145,0.12)'; this.style.borderColor='#c5d4ea'"
             onmouseout="this.style.boxShadow='0 1px 6px rgba(40,84,145,0.05)'; this.style.borderColor='#e8edf3'">
            <div class="flex items-start justify-between mb-3">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center transition-transform duration-300 group-hover:scale-110" style="background:#fffbeb;">
                    <i class="ph-fill ph-clock-countdown text-xl" style="color:#d97706;"></i>
                </div>
                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full" style="background:#fffbeb; color:#d97706;">
                    Draft
                </span>
            </div>
            <p class="text-xs font-medium text-gray-400 mb-0.5">Sedang Berjalan</p>
            <p class="text-2xl font-bold text-gray-800">{{ $totalDraft ?? \App\Models\Shipment::where('status','draft')->count() }}</p>
        </div>

        <div class="bg-white rounded-2xl p-4 lg:p-5 group cursor-default transition-all duration-300 hover:-translate-y-0.5"
             style="border: 1px solid #e8edf3; box-shadow: 0 1px 6px rgba(40,84,145,0.05);"
             onmouseover="this.style.boxShadow='0 8px 24px rgba(40,84,145,0.12)'; this.style.borderColor='#c5d4ea'"
             onmouseout="this.style.boxShadow='0 1px 6px rgba(40,84,145,0.05)'; this.style.borderColor='#e8edf3'">
            <div class="flex items-start justify-between mb-3">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center transition-transform duration-300 group-hover:scale-110" style="background:#e8f5f3;">
                    <i class="ph-fill ph-users text-xl" style="color:#0d5950;"></i>
                </div>
                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full" style="background:#e8f5f3; color:#0d5950;">Aktif</span>
            </div>
            <p class="text-xs font-medium text-gray-400 mb-0.5">Petugas Staging</p>
            <p class="text-2xl font-bold text-gray-800">{{ $totalPetugas ?? \App\Models\Karyawan::count() }}</p>
        </div>

    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-5">

        <!-- Recent Shipments Table (Left 2 cols) -->
        <div class="xl:col-span-2 bg-white rounded-2xl shadow-[0_1px_6px_rgba(40,84,145,0.05)] border border-gray-100 overflow-hidden">
            <div class="p-5 flex items-center justify-between" style="border-bottom: 1px solid #f0f4f9;">
                <div>
                    <h2 class="text-sm font-bold text-gray-800">Shipment Terbaru</h2>
                    <p class="text-xs text-gray-400 mt-0.5">Pantauan staging kontainer dan truk terkini</p>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('shipments.index') }}"
                       class="text-xs font-semibold px-3 py-1.5 rounded-xl border border-gray-200 text-gray-600 hover:text-spv-blue hover:border-spv-blue transition-all"
                       onmouseover="this.style.background='#eff4fc'" onmouseout="this.style.background='transparent'">
                        Lihat Semua
                    </a>
                    <a href="{{ route('field-app.create') }}" target="_blank"
                       class="text-xs font-bold px-3.5 py-1.5 rounded-xl text-white flex items-center gap-1.5 shadow-sm transition-all"
                       style="background: linear-gradient(135deg, #285491, #0d5950);"
                       onmouseover="this.style.opacity='0.9'" onmouseout="this.style.opacity='1'">
                        <i class="ph-bold ph-plus text-xs"></i>
                        <span>Buat Staging</span>
                    </a>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left whitespace-nowrap">
                    <thead style="background:#fafbfd;">
                        <tr>
                            <th class="px-5 py-3 text-[10px] font-bold text-gray-400 uppercase tracking-wider">Identitas</th>
                            <th class="px-5 py-3 text-[10px] font-bold text-gray-400 uppercase tracking-wider hidden sm:table-cell">Produk</th>
                            <th class="px-5 py-3 text-[10px] font-bold text-gray-400 uppercase tracking-wider hidden md:table-cell">Tipe</th>
                            <th class="px-5 py-3 text-[10px] font-bold text-gray-400 uppercase tracking-wider hidden lg:table-cell">Petugas</th>
                            <th class="px-5 py-3 text-[10px] font-bold text-gray-400 uppercase tracking-wider hidden lg:table-cell">Waktu</th>
                            <th class="px-5 py-3 text-[10px] font-bold text-gray-400 uppercase tracking-wider">Status</th>
                            <th class="px-5 py-3 text-[10px] font-bold text-gray-400 uppercase tracking-wider text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($shipments ?? \App\Models\Shipment::with('karyawan')->latest()->take(5)->get() as $s)
                            <tr style="border-top: 1px solid #f0f4f9;" onmouseover="this.style.background='#fafcff'" onmouseout="this.style.background='transparent'">
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0" style="background:#eff4fc;">
                                            <i class="ph-fill {{ $s->jenis_pengiriman === 'export' ? 'ph-shipping-container' : 'ph-truck' }} text-sm" style="color:#285491;"></i>
                                        </div>
                                        <div>
                                            <a href="{{ route('shipments.show', $s->id) }}" class="text-xs font-bold text-gray-800 hover:text-spv-blue">
                                                {{ $s->nomor_container_atau_plat ?? 'SPV-' . $s->id }}
                                            </a>
                                            <p class="text-[10px] text-gray-400">{{ $s->plat_nomor ?? '—' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-3.5 hidden sm:table-cell"><p class="text-xs text-gray-600 capitalize">{{ $s->jenis_produk }}</p></td>
                                <td class="px-5 py-3.5 hidden md:table-cell">
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold capitalize {{ $s->jenis_pengiriman === 'export' ? 'bg-purple-50 text-purple-700' : 'bg-gray-100 text-gray-700' }}">
                                        {{ $s->jenis_pengiriman }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 hidden lg:table-cell"><p class="text-xs text-gray-600">{{ $s->karyawan?->nama ?? '-' }}</p></td>
                                <td class="px-5 py-3.5 hidden lg:table-cell"><p class="text-[10px] text-gray-400">{{ $s->created_at->format('d M, H:i') }}</p></td>
                                <td class="px-5 py-3.5">
                                    @if($s->status === 'submitted')
                                        <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-green-50 text-green-700 border border-green-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-green-500 animate-pulse"></span>Submitted
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>Draft
                                        </span>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5 text-right">
                                    <div class="flex items-center justify-end gap-1">
                                        <a href="{{ route('shipments.show', $s->id) }}"
                                           title="Lihat Detail (Read)"
                                           class="p-1.5 rounded-lg transition-all text-gray-400 hover:text-spv-blue hover:bg-blue-50">
                                            <i class="ph-bold ph-eye text-base"></i>
                                        </a>
                                        <a href="{{ route('field-app.timeline', $s->id) }}"
                                           title="Buka Timeline"
                                           target="_blank"
                                           class="p-1.5 rounded-lg transition-all text-gray-400 hover:text-spv-green hover:bg-emerald-50">
                                            <i class="ph-bold ph-device-mobile text-base"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-8 text-xs text-gray-400">Belum ada shipment</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Right Side: Field App Simulator Card & Info -->
        <div class="flex flex-col gap-5">

            <!-- Field App Mobile Simulator Promotion Card -->
            <div class="rounded-2xl p-5 text-white relative overflow-hidden shadow-lg"
                 style="background: linear-gradient(145deg, #1a3c6e 0%, #285491 55%, #0d5950 100%); box-shadow: 0 8px 24px rgba(40,84,145,0.25);">
                <div class="relative z-10">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center mb-3"
                         style="background: linear-gradient(135deg, #059e3d, #63c384); box-shadow: 0 4px 14px rgba(5,158,61,0.4);">
                        <i class="ph-fill ph-device-mobile text-xl text-white"></i>
                    </div>
                    <h3 class="text-base font-bold leading-snug">Field App Simulator</h3>
                    <p class="text-xs mt-1" style="color: rgba(255,255,255,0.7);">
                        Aplikasi mobile staging untuk operator di lapangan. Dilengkapi fitur <strong>OCR Surat Jalan</strong>, stempel <strong>Timestamp otomatis</strong>, dan <strong>27 Titik SOP Foto</strong>.
                    </p>
                    <div class="mt-4 flex flex-col sm:flex-row gap-2">
                        <a href="{{ route('field-app.create') }}" target="_blank"
                           class="flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl font-bold text-xs transition-all shadow-md"
                           style="background: white; color: #285491;"
                           onmouseover="this.style.background='#f0f4f9'" onmouseout="this.style.background='white'">
                            <i class="ph-bold ph-arrow-square-out text-sm"></i>
                            Buka Field App
                        </a>
                        <a href="{{ route('shipments.index') }}"
                           class="flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl font-semibold text-xs border transition-all text-white"
                           style="border-color: rgba(255,255,255,0.25);"
                           onmouseover="this.style.background='rgba(255,255,255,0.1)'" onmouseout="this.style.background='transparent'">
                            Semua Data
                        </a>
                    </div>
                </div>
            </div>

            <!-- Quick Status Guide -->
            <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-[0_1px_6px_rgba(40,84,145,0.05)]">
                <h3 class="text-xs font-bold text-gray-800 mb-3">Panduan Status Staging</h3>
                <div class="space-y-2.5">
                    <div class="flex items-start gap-2.5">
                        <span class="w-2 h-2 rounded-full bg-amber-500 mt-1.5 shrink-0"></span>
                        <div>
                            <p class="text-xs font-bold text-gray-700">Draft / Berjalan</p>
                            <p class="text-[11px] text-gray-400">Petugas sedang mengunggah 27 bukti foto SOP dan belum submit final.</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-2.5">
                        <span class="w-2 h-2 rounded-full bg-green-500 mt-1.5 shrink-0"></span>
                        <div>
                            <p class="text-xs font-bold text-gray-700">Submitted / Selesai</p>
                            <p class="text-[11px] text-gray-400">Seluruh foto SOP dan data surat jalan telah divalidasi dan di-submit.</p>
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </div>

</x-layout>
