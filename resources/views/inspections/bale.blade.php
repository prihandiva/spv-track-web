<x-layout :title="'Form Inspeksi Kondisi Bale - ' . ($shipment->packing_list_no ?: $shipment->nomor_container_atau_plat)">
<div class="max-w-5xl mx-auto pb-16">

    <!-- Top Breadcrumb & Action Header -->
    <div class="flex items-center justify-between gap-3 mb-6">
        <div class="flex items-center gap-3">
            <a href="{{ route('field-app.timeline', $shipment->id) }}" 
               class="flex items-center justify-center w-10 h-10 rounded-xl bg-white border border-gray-200 text-gray-600 hover:text-spv-blue hover:border-spv-blue shadow-xs transition-all">
                <i class="ph-bold ph-arrow-left text-lg"></i>
            </a>
            <div>
                <div class="flex items-center gap-2">
                    <span class="text-xs font-bold uppercase tracking-wider text-spv-blue bg-blue-50 px-2 py-0.5 rounded-md border border-blue-200">
                        Halaman 3 / Form Checklist #2
                    </span>
                    <span class="text-xs text-gray-400">&bull;</span>
                    <span class="text-xs text-gray-500 font-mono">{{ $shipment->packing_list_no ?: $shipment->nomor_container_atau_plat }}</span>
                </div>
                <h1 class="text-xl sm:text-2xl font-extrabold text-gray-800 tracking-tight">Form Inspeksi Kondisi Bale (Loading)</h1>
            </div>
        </div>

        <div class="hidden sm:flex items-center gap-2">
            <a href="{{ route('field-app.container-inspection', $shipment->id) }}"
               class="px-4 py-2 text-xs font-bold text-gray-600 hover:text-gray-800 bg-white border border-gray-200 rounded-xl hover:bg-gray-50 transition-all shadow-xs">
                Ke Form Kontainer
            </a>
            <a href="{{ route('field-app.timeline', $shipment->id) }}"
               class="px-4 py-2 text-xs font-bold text-spv-blue hover:text-blue-800 bg-blue-50 border border-blue-200 rounded-xl hover:bg-blue-100 transition-all shadow-xs">
                Timeline Evidence
            </a>
        </div>
    </div>

    @if($errors->any())
    <div class="mb-6 p-4 rounded-2xl bg-red-50 border border-red-200 text-red-800 flex items-start gap-3 shadow-xs">
        <i class="ph-fill ph-warning-circle text-2xl text-red-500 shrink-0 mt-0.5"></i>
        <div>
            <h4 class="text-sm font-bold">Harap periksa kembali isian formulir:</h4>
            <ul class="text-xs list-disc list-inside mt-1 space-y-0.5 text-red-700">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    </div>
    @endif

    <form action="{{ route('field-app.bale-inspection.save', $shipment->id) }}" method="POST">
        @csrf

        <!-- Official Header Card (Lenzing Brand) -->
        <div class="bg-white rounded-2xl sm:rounded-3xl p-5 sm:p-7 border border-gray-100 shadow-[0_1px_6px_rgba(40,84,145,0.05)] mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-5 border-b border-gray-100">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-2xl bg-emerald-600 text-white flex items-center justify-center font-black text-xl shadow-md shadow-emerald-500/20">
                        L
                    </div>
                    <div>
                        <div class="text-emerald-700 font-extrabold text-lg tracking-tight leading-none">Lenzing</div>
                        <div class="text-[11px] font-semibold text-gray-400 tracking-wider uppercase mt-1">Innovative by nature</div>
                    </div>
                </div>

                <div class="text-left sm:text-right">
                    <span class="inline-block px-3 py-1 bg-slate-100 text-slate-700 font-mono text-xs font-bold rounded-lg border border-slate-200">
                        FORM INSPEKSI KONDISI BALE (LOADING)
                    </span>
                    <p class="text-[11px] text-gray-400 mt-1">Standar Kualitas & 4-Eyes Principle Verification</p>
                </div>
            </div>

            <!-- Loading Times Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-5">
                <div>
                    <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1 flex items-center justify-between">
                        <span>Tanggal Loading</span>
                        <span class="text-[10px] text-emerald-600 font-semibold normal-case">Dari Shipment</span>
                    </label>
                    <input type="date" name="loading_date" value="{{ old('loading_date', $inspection->loading_date ? $inspection->loading_date->format('Y-m-d') : ($shipment->tanggal_staging ? $shipment->tanggal_staging->format('Y-m-d') : now()->toDateString())) }}"
                           class="w-full text-sm px-3.5 py-2.5 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition-all">
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1 flex items-center justify-between">
                        <span>Nama Petugas Loading</span>
                        <span class="text-[10px] text-emerald-600 font-semibold normal-case">Petugas Staging</span>
                    </label>
                    <input type="text" name="nama_petugas_loading" value="{{ old('nama_petugas_loading', $inspection->nama_petugas_loading ?: ($shipment->allKaryawans()->pluck('nama')->first() ?: ($shipment->karyawan?->nama ?? auth()->user()?->name))) }}"
                           placeholder="Nama operator"
                           class="w-full text-sm font-semibold px-3.5 py-2.5 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition-all">
                </div>
            </div>

            <!-- Jam Pelaksanaan Otomatis Badge -->
            <div class="mt-4 p-3.5 rounded-xl bg-blue-50/70 border border-blue-100 flex items-center justify-between gap-3 text-xs text-blue-900">
                <div class="flex items-center gap-2.5">
                    <div class="w-7 h-7 rounded-lg bg-blue-100 text-spv-blue flex items-center justify-center shrink-0">
                        <i class="ph-bold ph-clock text-base"></i>
                    </div>
                    <div>
                        <p class="font-bold text-gray-800">Waktu Pelaksanaan (Jam Start &amp; Finish)</p>
                        <p class="text-[11px] text-gray-500">Otomatis diambil dari <strong>Waktu Awal &amp; Akhir Staging</strong> yang diisi pada card sebelum submit di halaman Timeline. Tidak perlu diisi berulang di sini.</p>
                    </div>
                </div>
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[10px] font-bold bg-white text-emerald-700 border border-emerald-200 shrink-0">
                    <i class="ph-bold ph-check"></i> Otomatis Terhubung
                </span>
            </div>
        </div>

        <!-- Section A: Informasi Umum -->
        <div class="bg-white rounded-2xl sm:rounded-3xl p-5 sm:p-7 border border-gray-100 shadow-[0_1px_6px_rgba(40,84,145,0.05)] mb-6">
            <h2 class="text-sm font-extrabold text-gray-700 uppercase tracking-wider mb-4 flex items-center gap-2">
                <span class="w-6 h-6 rounded-lg bg-spv-blue text-white flex items-center justify-center text-xs font-black">A</span>
                Informasi Umum
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-5">
                <div>
                    <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1">Nomor Surat Jalan <span class="text-red-500">*</span></label>
                    <input type="text" name="nomor_surat_jalan" value="{{ old('nomor_surat_jalan', $inspection->nomor_surat_jalan ?: $shipment->packing_list_no) }}" required
                           class="w-full text-sm font-mono font-bold px-3.5 py-2.5 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 uppercase bg-slate-50/50">
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1">Nama Pelanggan (Customer)</label>
                    <input type="text" name="nama_pelanggan" value="{{ old('nama_pelanggan', $inspection->nama_pelanggan ?: $shipment->tujuan_pengiriman) }}"
                           class="w-full text-sm px-3.5 py-2.5 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20">
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1">Jumlah Bale</label>
                    <input type="number" name="jumlah_bale" value="{{ old('jumlah_bale', $inspection->jumlah_bale) }}" placeholder="Contoh: 120" min="0"
                           class="w-full text-sm font-bold px-3.5 py-2.5 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20">
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1">No. Kontainer / Truk Lokal</label>
                    <input type="text" name="nomor_kontainer_truk" value="{{ old('nomor_kontainer_truk', $inspection->nomor_kontainer_truk ?: ($shipment->nomor_container_atau_plat ?: $shipment->plat_nomor)) }}"
                           class="w-full text-sm font-mono font-bold px-3.5 py-2.5 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 uppercase">
                </div>
            </div>

            @php
                $selectedJenisBale = (array) old('jenis_bale', $inspection->jenis_bale ?? ['tx_ecovero']);
                $selectedJenisLabel = (array) old('jenis_label', $inspection->jenis_label ?? ['tanpa_label']);
            @endphp
            <style>
                .checkbox-pill {
                    border-color: #e2e8f0;
                    color: #334155;
                    background-color: transparent;
                    transition: border-color 0.15s ease, background-color 0.15s ease, color 0.15s ease, box-shadow 0.15s ease;
                }
                .checkbox-pill:hover {
                    background-color: #f8fafc;
                }
                .checkbox-pill:has(input:checked) {
                    border-color: #285491 !important;
                    background-color: rgba(239, 244, 252, 0.75) !important;
                    color: #285491 !important;
                    box-shadow: 0 0 0 1px rgba(40, 84, 145, 0.25) !important;
                }
            </style>
            <!-- Checkboxes: Jenis Bale & Jenis Sticker (Reactive via Alpine.js & CSS :has) -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 pt-4 border-t border-gray-100"
                 x-data="{
                     jenisBale: {{ Js::from(array_values($selectedJenisBale)) }},
                     jenisLabel: {{ Js::from(array_values($selectedJenisLabel)) }},
                     toggleSticker(val) {
                         if (val === 'tanpa_label') {
                             if (this.jenisLabel.includes('tanpa_label')) {
                                 this.jenisLabel = ['tanpa_label'];
                             }
                         } else {
                             this.jenisLabel = this.jenisLabel.filter(i => i !== 'tanpa_label');
                         }
                     }
                 }">
                <div>
                    <label class="block text-[11px] font-bold text-gray-600 uppercase tracking-wider mb-2">Jenis Bale (Centang pilihan):</label>
                    <div class="flex flex-wrap gap-2">
                        @foreach(['tx_ecovero' => 'TX ECOVERO™', 'tx_non_ecovero' => 'TX Non-ECOVERO™', 'nw' => 'NW (Non-Woven)'] as $val => $lbl)
                        <label class="checkbox-pill flex items-center gap-2 px-3 py-2 rounded-xl border cursor-pointer text-xs font-semibold"
                               :class="jenisBale.includes('{{ $val }}') ? 'border-spv-blue bg-blue-50/50 text-spv-blue ring-1 ring-spv-blue/20' : 'border-gray-200 text-gray-700'">
                            <input type="checkbox" name="jenis_bale[]" value="{{ $val }}"
                                   x-model="jenisBale"
                                   class="rounded text-spv-blue focus:ring-spv-blue">
                            <span>{{ $lbl }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-gray-600 uppercase tracking-wider mb-2">Jenis Sticker (Centang pilihan):</label>
                    <div class="flex flex-wrap gap-2">
                        @foreach(['tanpa_label' => 'Tanpa Sticker', 'fsc' => 'FSC', 'isi' => 'ISI', 'n' => 'N', 'o' => 'O', 'a' => 'A'] as $val => $lbl)
                        <label class="checkbox-pill flex items-center gap-2 px-3 py-2 rounded-xl border cursor-pointer text-xs font-semibold"
                               :class="jenisLabel.includes('{{ $val }}') ? 'border-spv-blue bg-blue-50/50 text-spv-blue ring-1 ring-spv-blue/20' : 'border-gray-200 text-gray-700'">
                            <input type="checkbox" name="jenis_label[]" value="{{ $val }}"
                                   x-model="jenisLabel"
                                   @change="toggleSticker('{{ $val }}')"
                                   class="rounded text-spv-blue focus:ring-spv-blue">
                            <span>{{ $lbl }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Section B: Pemeriksaan Kondisi Bale – Pemeriksa Pertama -->
        <div class="bg-white rounded-2xl sm:rounded-3xl p-5 sm:p-7 border border-gray-100 shadow-[0_1px_6px_rgba(40,84,145,0.05)] mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-4">
                <h2 class="text-sm font-extrabold text-gray-700 uppercase tracking-wider flex items-center gap-2">
                    <span class="w-6 h-6 rounded-lg bg-spv-blue text-white flex items-center justify-center text-xs font-black">B</span>
                    Pemeriksaan Kondisi Bale – Pemeriksa Pertama
                </h2>
                <div class="text-[11px] font-bold text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-lg border border-emerald-200 inline-block self-start">
                    Kaidah: Bersih, Mulus, dan Tidak Cacat
                </div>
            </div>
            <p class="text-xs text-gray-500 mb-4">
                Pastikan bale <strong>TIDAK</strong> mengalami kondisi berikut. Tandai "Ada" dan isi rincian jika ada temuan cacat/ketidaksesuaian:
            </p>

            <div class="space-y-3">
                @foreach($defaultChecklist as $idx => $label)
                @php
                    $item = $items->get($idx);
                    $ada = old("items.{$idx}.ada", $item?->ada_ketidaksesuaian ?? false);
                    $jml = old("items.{$idx}.jumlah", $item?->jumlah_bale);
                    $tindakan = old("items.{$idx}.tindakan", $item?->tindakan_perbaikan);
                    $tindakanLain = old("items.{$idx}.tindakan_lainnya", $item?->tindakan_lainnya);
                    $baleAwal = old("items.{$idx}.bale_awal", $item?->nomor_bale_awal);
                    $baleGanti = old("items.{$idx}.bale_pengganti", $item?->nomor_bale_pengganti);
                @endphp
                <div x-data="{ ada: {{ $ada ? 1 : 0 }} }"
                     class="rounded-2xl p-4 border transition-all"
                     :class="ada === 1 ? 'bg-amber-50/40 border-amber-300 shadow-xs' : 'bg-slate-50/50 border-gray-200'">
                    
                    <!-- Hidden input to reliably submit 0 or 1 -->
                    <input type="hidden" name="items[{{ $idx }}][ada]" :value="ada">

                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <span class="w-7 h-7 rounded-lg font-bold text-xs flex items-center justify-center shrink-0"
                                  :class="ada === 1 ? 'bg-amber-500 text-white' : 'bg-white border border-gray-200 text-gray-600'">
                                {{ $idx }}
                            </span>
                            <span class="text-xs sm:text-sm font-bold text-gray-800">{{ $label }}</span>
                        </div>

                        <!-- Ada / Tidak Toggle Buttons -->
                        <div class="flex items-center gap-1.5 self-end sm:self-auto bg-white p-1 rounded-xl border border-gray-200">
                            <button type="button" @click="ada = 0"
                                    class="flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-bold transition-all cursor-pointer"
                                    :class="ada === 0 ? 'bg-emerald-600 text-white shadow-xs' : 'text-gray-600 hover:text-gray-900 bg-transparent'">
                                <i class="ph-bold ph-check text-xs"></i>
                                <span>Tidak (Aman)</span>
                            </button>
                            <button type="button" @click="ada = 1"
                                    class="flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-bold transition-all cursor-pointer"
                                    :class="ada === 1 ? 'bg-amber-600 text-white shadow-xs' : 'text-gray-600 hover:text-gray-900 bg-transparent'">
                                <i class="ph-bold ph-warning text-xs"></i>
                                <span>Ada Temuan</span>
                            </button>
                        </div>
                    </div>

                    <!-- Detail Isian Jika Ada Ketidaksesuaian -->
                    <div x-show="ada === 1" x-cloak class="mt-4 pt-3 border-t border-amber-200/70 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                        <div>
                            <label class="block text-[11px] font-bold text-gray-600 mb-1">Jumlah Bale Cacat:</label>
                            <input type="number" name="items[{{ $idx }}][jumlah]" value="{{ $jml }}" placeholder="Qty" min="1"
                                   class="w-full text-xs px-3 py-2 rounded-xl border border-gray-300 focus:border-amber-500 bg-white">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-gray-600 mb-1">Tindakan Perbaikan:</label>
                            <select name="items[{{ $idx }}][tindakan]" class="w-full text-xs px-3 py-2 rounded-xl border border-gray-300 focus:border-amber-500 bg-white">
                                <option value="">-- Pilih Tindakan --</option>
                                <option value="diganti" {{ $tindakan === 'diganti' ? 'selected' : '' }}>Diganti</option>
                                <option value="diperbaiki" {{ $tindakan === 'diperbaiki' ? 'selected' : '' }}>Diperbaiki / ditambal / dibersihkan</option>
                                <option value="ganti_label" {{ $tindakan === 'ganti_label' ? 'selected' : '' }}>Ganti label (EV / Non-EV)</option>
                                <option value="lainnya" {{ $tindakan === 'lainnya' ? 'selected' : '' }}>Lainnya</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-gray-600 mb-1">No. Bale Awal:</label>
                            <input type="text" name="items[{{ $idx }}][bale_awal]" value="{{ $baleAwal }}" placeholder="Nomor bale reject"
                                   class="w-full text-xs px-3 py-2 rounded-xl border border-gray-300 focus:border-amber-500 bg-white font-mono">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-gray-600 mb-1">No. Bale Pengganti:</label>
                            <input type="text" name="items[{{ $idx }}][bale_pengganti]" value="{{ $baleGanti }}" placeholder="Nomor bale baru"
                                   class="w-full text-xs px-3 py-2 rounded-xl border border-gray-300 focus:border-amber-500 bg-white font-mono">
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Section C: Verifikasi 4-Eyes Principle -->
        <div class="bg-white rounded-2xl sm:rounded-3xl p-5 sm:p-7 border border-gray-100 shadow-[0_1px_6px_rgba(40,84,145,0.05)] mb-6">
            <h2 class="text-sm font-extrabold text-gray-700 uppercase tracking-wider mb-4 flex items-center gap-2">
                <span class="w-6 h-6 rounded-lg bg-spv-blue text-white flex items-center justify-center text-xs font-black">C</span>
                Verifikasi 4-Eyes Principle
            </h2>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- 1. Pemeriksa Pertama -->
                <div class="p-5 rounded-2xl bg-slate-50 border border-slate-200">
                    <div class="flex items-center gap-2 font-bold text-xs text-gray-800 uppercase tracking-wide mb-2">
                        <span class="w-5 h-5 rounded-full bg-blue-600 text-white flex items-center justify-center text-[10px]">1</span>
                        Pemeriksa Pertama (First Eye)
                    </div>
                    <p class="text-[11px] text-gray-500 italic mb-4">
                        "Saya menyatakan bahwa inspeksi kondisi bale telah dilakukan secara menyeluruh sesuai prosedur yang berlaku."
                    </p>
                    <div class="space-y-3">
                        <div>
                            <label class="block text-[11px] font-bold text-gray-600 mb-1">Nama Petugas <span class="text-red-500">*</span></label>
                            <input type="text" name="first_eye_nama" value="{{ old('first_eye_nama', $inspection->first_eye_nama) }}" required
                                   class="w-full text-xs font-semibold px-3 py-2 rounded-xl border border-gray-200 bg-white">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-gray-600 mb-1">Jabatan / Unit</label>
                            <input type="text" name="first_eye_jabatan" value="{{ old('first_eye_jabatan', $inspection->first_eye_jabatan) }}"
                                   class="w-full text-xs px-3 py-2 rounded-xl border border-gray-200 bg-white">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-gray-600 mb-1">Tanggal Verifikasi <span class="text-red-500">*</span></label>
                            <input type="date" name="first_eye_tanggal" value="{{ old('first_eye_tanggal', $inspection->first_eye_tanggal ? $inspection->first_eye_tanggal->format('Y-m-d') : now()->toDateString()) }}" required
                                   class="w-full text-xs px-3 py-2 rounded-xl border border-gray-200 bg-white">
                        </div>
                    </div>
                </div>

                <!-- 2. Pemeriksa Kedua (Verifikator) -->
                <div class="p-5 rounded-2xl bg-blue-50/50 border border-blue-200">
                    <div class="flex items-center gap-2 font-bold text-xs text-spv-blue uppercase tracking-wide mb-2">
                        <span class="w-5 h-5 rounded-full bg-spv-blue text-white flex items-center justify-center text-[10px]">2</span>
                        Pemeriksa Kedua (Second Eye / Verifikator)
                    </div>
                    <p class="text-[11px] text-gray-500 italic mb-3">
                        "Saya telah melakukan verifikasi independen terhadap hasil inspeksi dan menyetujui / menolak hasil serta tindakan perbaikan."
                    </p>

                    <div class="mb-4">
                        <label class="block text-[11px] font-bold text-gray-600 mb-1.5">Status Verifikasi:</label>
                        <div class="space-y-1.5">
                            <label class="flex items-center gap-2 text-xs font-bold text-gray-700 cursor-pointer">
                                <input type="radio" name="second_eye_status" value="disetujui" {{ old('second_eye_status', $inspection->second_eye_status) === 'disetujui' ? 'checked' : '' }} class="text-spv-blue focus:ring-spv-blue">
                                <span>Disetujui</span>
                            </label>
                            <label class="flex items-center gap-2 text-xs font-bold text-gray-700 cursor-pointer">
                                <input type="radio" name="second_eye_status" value="perlu_perbaikan" {{ old('second_eye_status', $inspection->second_eye_status) === 'perlu_perbaikan' ? 'checked' : '' }} class="text-amber-600 focus:ring-amber-500">
                                <span>Perlu Perbaikan Tambahan</span>
                            </label>
                            <label class="flex items-center gap-2 text-xs font-bold text-gray-700 cursor-pointer">
                                <input type="radio" name="second_eye_status" value="tidak_disetujui" {{ old('second_eye_status', $inspection->second_eye_status) === 'tidak_disetujui' ? 'checked' : '' }} class="text-red-600 focus:ring-red-500">
                                <span>Tidak Disetujui (Gagal Verifikasi)</span>
                            </label>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <div>
                            <label class="block text-[11px] font-bold text-gray-600 mb-1">Catatan Verifikator:</label>
                            <textarea name="second_eye_catatan" rows="2"
                                      placeholder="Masukkan catatan verifikator (dapat diisi untuk semua opsi verifikasi)..."
                                      class="w-full text-xs px-3 py-2 rounded-xl border border-blue-200 bg-white focus:border-spv-blue focus:ring-2 focus:ring-spv-blue/20 transition-all font-medium">{{ old('second_eye_catatan', $inspection->second_eye_catatan) }}</textarea>
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-gray-600 mb-1">Nama Verifikator</label>
                            <input type="text" name="second_eye_nama" value="{{ old('second_eye_nama', $inspection->second_eye_nama) }}"
                                   class="w-full text-xs font-semibold px-3 py-2 rounded-xl border border-blue-200 bg-white">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-gray-600 mb-1">Jabatan / Unit</label>
                            <input type="text" name="second_eye_jabatan" value="{{ old('second_eye_jabatan', $inspection->second_eye_jabatan) }}"
                                   class="w-full text-xs px-3 py-2 rounded-xl border border-blue-200 bg-white">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-gray-600 mb-1">Tanggal Verifikasi</label>
                            <input type="date" name="second_eye_tanggal" value="{{ old('second_eye_tanggal', $inspection->second_eye_tanggal ? $inspection->second_eye_tanggal->format('Y-m-d') : now()->toDateString()) }}"
                                   class="w-full text-xs px-3 py-2 rounded-xl border border-blue-200 bg-white">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section D, E, F: Sumber Bale, Stagging & Kondisi Loading -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <!-- D. Sumber Bale -->
            <div class="bg-white rounded-2xl sm:rounded-3xl p-5 border border-gray-100 shadow-[0_1px_6px_rgba(40,84,145,0.05)]">
                <h2 class="text-sm font-extrabold text-gray-700 uppercase tracking-wider mb-3 flex items-center gap-2">
                    <span class="w-5 h-5 rounded-md bg-spv-blue text-white flex items-center justify-center text-xs font-black">D</span>
                    Sumber Bale
                </h2>
                <div class="space-y-2">
                    <label class="flex items-start gap-2 text-xs font-semibold text-gray-700 cursor-pointer">
                        <input type="radio" name="sumber_bale" value="inspeksi_join_checking" {{ old('sumber_bale', $inspection->sumber_bale) === 'inspeksi_join_checking' ? 'checked' : '' }} class="mt-0.5 text-spv-blue focus:ring-spv-blue">
                        <span>Hasil inspeksi & join checking (FGW-TCS – QPD – PDP)</span>
                    </label>
                    <label class="flex items-center gap-2 text-xs font-semibold text-gray-700 cursor-pointer">
                        <input type="radio" name="sumber_bale" value="produksi_baru" {{ old('sumber_bale', $inspection->sumber_bale) === 'produksi_baru' ? 'checked' : '' }} class="text-spv-blue focus:ring-spv-blue">
                        <span>Bale produksi baru</span>
                    </label>
                    <label class="flex items-center gap-2 text-xs font-semibold text-gray-700 cursor-pointer">
                        <input type="radio" name="sumber_bale" value="lainnya" {{ old('sumber_bale', $inspection->sumber_bale) === 'lainnya' ? 'checked' : '' }} class="text-spv-blue focus:ring-spv-blue">
                        <span>Lainnya</span>
                    </label>
                </div>
            </div>

            <!-- E. Lokasi Stagging -->
            <div class="bg-white rounded-2xl sm:rounded-3xl p-5 border border-gray-100 shadow-[0_1px_6px_rgba(40,84,145,0.05)]">
                <h2 class="text-sm font-extrabold text-gray-700 uppercase tracking-wider mb-3 flex items-center gap-2">
                    <span class="w-5 h-5 rounded-md bg-spv-blue text-white flex items-center justify-center text-xs font-black">E</span>
                    Lokasi Stagging
                </h2>
                <p class="text-[11px] text-gray-500 mb-2">Bale dikumpulkan pada 1 lokasi staging dengan 2 Rubber Cone?</p>
                <div class="flex items-center gap-4 mb-3">
                    <label class="flex items-center gap-1.5 text-xs font-bold text-gray-700 cursor-pointer">
                        <input type="radio" name="stagging_2_rubber_cone" value="1" {{ old('stagging_2_rubber_cone', $inspection->stagging_2_rubber_cone) ? 'checked' : '' }} class="text-emerald-600 focus:ring-emerald-500">
                        <span>Ya (Ada 2 Cone)</span>
                    </label>
                    <label class="flex items-center gap-1.5 text-xs font-bold text-gray-700 cursor-pointer">
                        <input type="radio" name="stagging_2_rubber_cone" value="0" {{ !old('stagging_2_rubber_cone', $inspection->stagging_2_rubber_cone) ? 'checked' : '' }} class="text-red-600 focus:ring-red-500">
                        <span>Tidak</span>
                    </label>
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-gray-600 mb-1">Nama / Kode Lokasi Stagging:</label>
                    <input type="text" name="lokasi_stagging" value="{{ old('lokasi_stagging', $inspection->lokasi_stagging) }}" placeholder="Contoh: Bay 3 Atas"
                           class="w-full text-xs px-3 py-2 rounded-xl border border-gray-200 focus:border-blue-500">
                </div>
            </div>

            <!-- F. Kondisi Proses Loading -->
            <div class="bg-white rounded-2xl sm:rounded-3xl p-5 border border-gray-100 shadow-[0_1px_6px_rgba(40,84,145,0.05)]">
                <h2 class="text-sm font-extrabold text-gray-700 uppercase tracking-wider mb-3 flex items-center gap-2">
                    <span class="w-5 h-5 rounded-md bg-spv-blue text-white flex items-center justify-center text-xs font-black">F</span>
                    Kondisi Pemuatan
                </h2>
                <div class="mb-3">
                    <label class="block text-[11px] font-bold text-gray-600 mb-1">Cuaca:</label>
                    <div class="grid grid-cols-2 gap-1.5">
                        @foreach(['kering' => 'Kering', 'mendung' => 'Mendung', 'gerimis' => 'Gerimis', 'hujan' => 'Hujan'] as $cVal => $cLbl)
                        <label class="flex items-center gap-1.5 text-xs font-medium text-gray-700 cursor-pointer">
                            <input type="radio" name="cuaca" value="{{ $cVal }}" {{ old('cuaca', $inspection->cuaca) === $cVal ? 'checked' : '' }} class="text-spv-blue focus:ring-spv-blue">
                            <span>{{ $cLbl }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-gray-600 mb-1">Waktu:</label>
                    <div class="flex items-center gap-3">
                        @foreach(['siang' => 'Siang', 'sore' => 'Sore', 'malam' => 'Malam'] as $wVal => $wLbl)
                        <label class="flex items-center gap-1.5 text-xs font-medium text-gray-700 cursor-pointer">
                            <input type="radio" name="waktu" value="{{ $wVal }}" {{ old('waktu', $inspection->waktu) === $wVal ? 'checked' : '' }} class="text-spv-blue focus:ring-spv-blue">
                            <span>{{ $wLbl }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Kesimpulan Akhir -->
        <div x-data="{ kesimpulan_akhir: '{{ old('kesimpulan_akhir', $inspection->kesimpulan_akhir ?? 'layak') }}' }"
             class="bg-white rounded-2xl sm:rounded-3xl p-5 sm:p-7 border border-gray-100 shadow-[0_1px_6px_rgba(40,84,145,0.05)] mb-6">
            <h2 class="text-sm font-extrabold text-gray-700 uppercase tracking-wider mb-2 flex items-center gap-2">
                <i class="ph-bold ph-seal-check text-emerald-600 text-base"></i>
                Kesimpulan Akhir Inspeksi & Verifikasi 4-Eyes
            </h2>
            <p class="text-xs text-gray-600 mb-4">
                Berdasarkan hasil inspeksi dan verifikasi 4 eyes, seluruh bale yang tercantum dalam surat jalan dinyatakan:
            </p>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <label class="flex items-center gap-3 p-3.5 rounded-2xl border cursor-pointer transition-all"
                       :class="kesimpulan_akhir === 'layak' ? 'border-emerald-500 bg-emerald-50/40 ring-2 ring-emerald-500/20' : 'border-gray-200 hover:border-gray-300'">
                    <input type="radio" name="kesimpulan_akhir" value="layak" x-model="kesimpulan_akhir" class="text-emerald-600 focus:ring-emerald-500">
                    <div>
                        <div class="text-xs font-extrabold text-emerald-800">LAYAK UNTUK PROSES LOADING</div>
                        <div class="text-[11px] text-gray-500">Seluruh bale siap dimasukkan ke kontainer</div>
                    </div>
                </label>

                <label class="flex items-center gap-3 p-3.5 rounded-2xl border cursor-pointer transition-all"
                       :class="kesimpulan_akhir === 'layak_dengan_catatan' ? 'border-amber-500 bg-amber-50/40 ring-2 ring-amber-500/20' : 'border-gray-200 hover:border-gray-300'">
                    <input type="radio" name="kesimpulan_akhir" value="layak_dengan_catatan" x-model="kesimpulan_akhir" class="text-amber-600 focus:ring-amber-500">
                    <div>
                        <div class="text-xs font-extrabold text-amber-800">LAYAK DENGAN CATATAN</div>
                        <div class="text-[11px] text-gray-500">Lihat rincian perbaikan di bagian C.2</div>
                    </div>
                </label>

                <label class="flex items-center gap-3 p-3.5 rounded-2xl border cursor-pointer transition-all"
                       :class="kesimpulan_akhir === 'tidak_layak' ? 'border-red-500 bg-red-50/40 ring-2 ring-red-500/20' : 'border-gray-200 hover:border-gray-300'">
                    <input type="radio" name="kesimpulan_akhir" value="tidak_layak" x-model="kesimpulan_akhir" class="text-red-600 focus:ring-red-500">
                    <div>
                        <div class="text-xs font-extrabold text-red-800">TIDAK LAYAK (DITUNDA / DIGANTI)</div>
                        <div class="text-[11px] text-gray-500">Loading dibatalkan / ditunda</div>
                    </div>
                </label>
            </div>
        </div>

        <!-- Sticky Bottom Bar -->
        <div class="sticky bottom-4 bg-white/95 backdrop-blur-md p-4 rounded-2xl border border-gray-200 shadow-xl flex flex-col sm:flex-row items-center justify-between gap-3 z-30">
            <div class="flex items-center gap-2 text-xs text-gray-500">
                <i class="ph-bold ph-info text-spv-blue text-base"></i>
                <span>Data form ini akan tercetak di <strong>Halaman 3 Laporan PDF</strong>.</span>
            </div>

            <div class="flex items-center gap-2 w-full sm:w-auto">
                <a href="{{ route('field-app.container-inspection', $shipment->id) }}"
                   class="w-full sm:w-auto px-4 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold transition-all text-center">
                    Ke Form Kontainer
                </a>
                <button type="submit"
                        class="w-full sm:w-auto flex items-center justify-center gap-1.5 px-6 py-2.5 rounded-xl bg-spv-blue hover:bg-blue-800 text-white text-xs font-bold transition-all shadow-md shadow-blue-900/20">
                    <i class="ph-bold ph-floppy-disk text-xs"></i>
                    <span>Simpan & Kembali ke Timeline</span>
                </button>
            </div>
        </div>
    </form>

</div>
</x-layout>
