<x-layout :title="'Form Inspeksi Kontainer - ' . ($shipment->packing_list_no ?: $shipment->nomor_container_atau_plat)">
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
                    <span class="text-xs font-bold uppercase tracking-wider text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-200">
                        Halaman 2 / Form Checklist #1
                    </span>
                    <span class="text-xs text-gray-400">&bull;</span>
                    <span class="text-xs text-gray-500 font-mono">{{ $shipment->packing_list_no ?: $shipment->nomor_container_atau_plat }}</span>
                </div>
                <h1 class="text-xl sm:text-2xl font-extrabold text-gray-800 tracking-tight">Form Inspeksi Kondisi Kontainer</h1>
            </div>
        </div>

        <div class="hidden sm:flex items-center gap-2">
            <a href="{{ route('field-app.timeline', $shipment->id) }}"
               class="px-4 py-2 text-xs font-bold text-gray-600 hover:text-gray-800 bg-white border border-gray-200 rounded-xl hover:bg-gray-50 transition-all shadow-xs">
                Kembali ke Timeline
            </a>
        </div>
    </div>

    @if($errors->any())
    <div class="mb-6 p-4 rounded-2xl bg-red-50 border border-red-200 text-red-800 flex items-start gap-3 shadow-xs">
        <i class="ph-fill ph-warning-circle text-2xl text-red-500 shrink-0 mt-0.5"></i>
        <div>
            <h4 class="text-sm font-bold">Harap lengkapi formulir dengan benar:</h4>
            <ul class="text-xs list-disc list-inside mt-1 space-y-0.5 text-red-700">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    </div>
    @endif

    <style>
        .form-req:invalid:not(:placeholder-shown),
        .was-validated .form-req:invalid {
            border-color: #ef4444 !important;
            background-color: #fffbfa !important;
            box-shadow: 0 0 0 1px rgba(239, 68, 68, 0.2) !important;
        }
        .was-validated .form-req:invalid ~ .field-error-msg,
        .form-req:invalid:not(:placeholder-shown) ~ .field-error-msg {
            display: flex !important;
        }
    </style>

    <form id="containerForm" action="{{ route('field-app.container-inspection.save', $shipment->id) }}" method="POST"
          onsubmit="if(!this.checkValidity()){ event.preventDefault(); this.classList.add('was-validated'); const first = this.querySelector(':invalid'); if(first){ first.scrollIntoView({behavior:'smooth', block:'center'}); first.focus(); } return false; }">
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
                        SOP INSPEKSI KONTAINER
                    </span>
                    <p class="text-[11px] text-gray-400 mt-1">Standar Pemeriksaan Fisik Sebelum Loading / Stuffing</p>
                </div>
            </div>

            <!-- Loading Box & General Info Grid -->
            @php
                $minDate = (auth()->user()?->isOperator() ?? true) ? date('Y-m-d') : '';
            @endphp
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mt-5">
                <div>
                    <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1 flex items-center justify-between">
                        <span>Nomor Kontainer <span class="text-red-500">*</span></span>
                        <span class="text-[10px] text-emerald-600 font-semibold normal-case">Dari Shipment</span>
                    </label>
                    <input type="text" name="nomor_kontainer" value="{{ old('nomor_kontainer', $inspection->nomor_kontainer ?: $shipment->nomor_container_atau_plat) }}" required
                           placeholder="Contoh: MSKU1234567"
                           class="form-req w-full text-sm font-mono font-bold px-3.5 py-2.5 rounded-xl border border-gray-200 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all uppercase bg-slate-50/50">
                    <p class="field-error-msg hidden text-[11px] text-red-600 font-semibold mt-1 items-center gap-1">
                        <i class="ph-bold ph-warning-circle"></i> Nomor kontainer wajib diisi
                    </p>
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1 flex items-center justify-between">
                        <span>Nomor Seal <span class="text-red-500">*</span></span>
                        <span class="text-[10px] text-emerald-600 font-semibold normal-case">Dari Shipment</span>
                    </label>
                    <input type="text" name="nomor_seal" value="{{ old('nomor_seal', $inspection->nomor_seal ?: $shipment->nomor_seal) }}" required
                           placeholder="Contoh: LENZ123456"
                           class="form-req w-full text-sm font-mono font-bold px-3.5 py-2.5 rounded-xl border border-gray-200 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all uppercase">
                    <p class="field-error-msg hidden text-[11px] text-red-600 font-semibold mt-1 items-center gap-1">
                        <i class="ph-bold ph-warning-circle"></i> Nomor seal wajib diisi
                    </p>
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1 flex items-center justify-between">
                        <span>Tanggal Staging / Loading <span class="text-red-500">*</span></span>
                        <span class="text-[10px] text-emerald-600 font-semibold normal-case">Staging Date</span>
                    </label>
                    <input type="date" name="loading_date"
                           value="{{ old('loading_date', $inspection->loading_date ? $inspection->loading_date->format('Y-m-d') : ($shipment->tanggal_staging ? $shipment->tanggal_staging->format('Y-m-d') : now()->toDateString())) }}"
                           @if($minDate) min="{{ $minDate }}" @endif
                           required
                           class="form-req w-full text-sm font-bold px-3.5 py-2.5 rounded-xl border border-gray-200 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all">
                    <p class="field-error-msg hidden text-[11px] text-red-600 font-semibold mt-1 items-center gap-1">
                        <i class="ph-bold ph-warning-circle"></i> Tanggal staging / loading wajib diisi
                    </p>
                    @if($minDate)
                    <p class="text-[10px] text-emerald-700 font-semibold mt-1 flex items-center gap-1">
                        <i class="ph-bold ph-lock"></i> Tanggal tidak bisa backdate (minimal hari ini)
                    </p>
                    @endif
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1">
                        <span>Nama EMKL & Pelayaran <span class="text-red-500">*</span></span>
                    </label>
                    <input type="text" name="nama_emkl_pelayaran" value="{{ old('nama_emkl_pelayaran', $inspection->nama_emkl_pelayaran ?: $shipment->agen_forwarding) }}" required
                           placeholder="Nama forwarder / pelayaran"
                           class="form-req w-full text-sm px-3.5 py-2.5 rounded-xl border border-gray-200 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all">
                    <p class="field-error-msg hidden text-[11px] text-red-600 font-semibold mt-1 items-center gap-1">
                        <i class="ph-bold ph-warning-circle"></i> Nama EMKL & Pelayaran wajib diisi
                    </p>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1 flex items-center justify-between">
                        <span>Nama Petugas Inspeksi <span class="text-red-500">*</span></span>
                        <span class="text-[10px] text-emerald-600 font-semibold normal-case">Tabel Karyawan</span>
                    </label>
                    <select name="nama_petugas_inspeksi" required
                            class="form-req w-full text-sm font-semibold px-3.5 py-2.5 rounded-xl border border-gray-200 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all bg-white">
                        <option value="">-- Pilih Petugas Inspeksi (Dari Tabel Karyawan) --</option>
                        @foreach($karyawans as $k)
                            <option value="{{ $k->nama }}" {{ old('nama_petugas_inspeksi', $inspection->nama_petugas_inspeksi) === $k->nama ? 'selected' : '' }}>
                                {{ $k->nama }} ({{ $k->position_label }} - {{ $k->nomor_induk }})
                            </option>
                        @endforeach
                        @if(!empty($inspection->nama_petugas_inspeksi) && !$karyawans->contains('nama', $inspection->nama_petugas_inspeksi))
                            <option value="{{ $inspection->nama_petugas_inspeksi }}" selected>
                                {{ $inspection->nama_petugas_inspeksi }} (Tersimpan Sebelumnya)
                            </option>
                        @endif
                    </select>
                    <p class="field-error-msg hidden text-[11px] text-red-600 font-semibold mt-1 items-center gap-1">
                        <i class="ph-bold ph-warning-circle"></i> Petugas inspeksi wajib dipilih dari tabel karyawan
                    </p>
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

        <!-- Strict Warning Headline -->
        <div class="bg-amber-500 text-white rounded-2xl p-4 sm:p-5 mb-6 shadow-md shadow-amber-500/15 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-white/20 backdrop-blur-xs flex items-center justify-center text-2xl shrink-0">
                <i class="ph-bold ph-shield-warning"></i>
            </div>
            <div>
                <h3 class="text-sm sm:text-base font-extrabold tracking-tight uppercase">PASTIKAN KONDISI KONTAINER TIDAK:</h3>
                <p class="text-xs sm:text-sm text-amber-100 font-medium mt-0.5">
                    Berlubang / Retak, Berkarat, Berminyak / Kotor, Berbau / Wangi Menyengat, atau Ada Bagian Tajam / Paku / Lantai Lapuk.
                </p>
            </div>
        </div>

        <!-- 6 Titik Inspeksi Container Cards -->
        <div class="space-y-4 mb-6">
            <div class="flex items-center justify-between px-1">
                <h2 class="text-sm font-extrabold text-gray-700 uppercase tracking-wider flex items-center gap-2">
                    <i class="ph-bold ph-check-square-offset text-emerald-600 text-base"></i>
                    Daftar 6 Titik Pemeriksaan Fisik
                </h2>
                <span class="text-xs text-gray-400">Pilih "Ya" jika bersih & lolos kriteria</span>
            </div>

            @php
                $points = [
                    [
                        'key' => 'pintu',
                        'num' => 1,
                        'title' => 'PINTU (BAGIAN LUAR & DALAM)',
                        'desc' => 'Pintu luar: Tidak bolong / retak. Pintu dalam: Tidak bolong, karat, kotor / cat luntur, berdebu, berminyak, bau menyengat. Lakukan tes usap (swab test) pada pintu dalam & pastikan bersih.',
                        'default_note' => 'Pintu luar & dalam bersih, tidak bolong/retak, swab test bersih.',
                    ],
                    [
                        'key' => 'dinding_kanan',
                        'num' => 2,
                        'title' => 'DINDING KANAN KONTAINER (LUAR & DALAM)',
                        'desc' => 'Sisi kanan luar: Tidak bolong / retak. Sisi kanan dalam: Tidak bolong, karat, kotor, cat luntur, berdebu, ber-oli, bau menyengat. Lakukan tes usap pada dinding dalam & pastikan bersih.',
                        'default_note' => 'Dinding kanan bersih, tidak berkarat/kotor, swab test bersih.',
                    ],
                    [
                        'key' => 'dinding_kiri',
                        'num' => 3,
                        'title' => 'DINDING KIRI KONTAINER (LUAR & DALAM)',
                        'desc' => 'Sisi kiri luar: Tidak bolong / retak. Sisi kiri dalam: Tidak bolong, karat, kotor, cat luntur, berdebu, ber-oli, bau menyengat. Lakukan tes usap pada dinding dalam & pastikan bersih.',
                        'default_note' => 'Dinding kiri bersih, tidak berkarat/kotor, swab test bersih.',
                    ],
                    [
                        'key' => 'dinding_depan',
                        'num' => 4,
                        'title' => 'DINDING BAGIAN DEPAN (LUAR & DALAM)',
                        'desc' => 'Dinding depan luar: Tidak bolong / retak. Dinding depan dalam: Tidak bolong, karat, kotor / cat luntur, berdebu, berminyak, bau menyengat. Lakukan tes usap & pastikan bersih.',
                        'default_note' => 'Dinding depan bersih, tidak retak/bolong, swab test bersih.',
                    ],
                    [
                        'key' => 'atap',
                        'num' => 5,
                        'title' => 'ATAP KONTAINER',
                        'desc' => 'Pastikan tidak bolong, retak, bocor, karat, kotor, berminyak / ber-oli, bau / wangi menyengat. Pastikan dalam keadaan kering dan bersih sempurna.',
                        'default_note' => 'Atap bersih, kering, tidak berkarat/bocor.',
                    ],
                    [
                        'key' => 'lantai',
                        'num' => 6,
                        'title' => 'LANTAI KONTAINER',
                        'desc' => 'Pastikan lantai rata, bersih, kering, bebas noda oli/minyak & debu. Tidak ada benda tajam (paku menonjol) dan tidak lapuk yang dapat merusak packaging bale.',
                        'default_note' => 'Lantai bersih, kering, rata, bebas benda tajam & paku.',
                    ],
                ];
            @endphp

            @foreach($points as $pt)
            @php
                $statusField = $pt['key'] . '_status';
                $noteField = $pt['key'] . '_catatan';
                $currentStatus = old($statusField, $inspection->$statusField ?? true);
                $currentNote = old($noteField, $inspection->$noteField);
            @endphp
            <div x-data="{ status: {{ $currentStatus ? 1 : 0 }} }" 
                 class="bg-white rounded-2xl p-5 border transition-all"
                 :class="status === 1 ? 'border-gray-100 shadow-[0_1px_6px_rgba(40,84,145,0.05)]' : 'border-red-200 bg-red-50/20 shadow-sm'">
                
                <!-- Hidden input to reliably submit 1 or 0 -->
                <input type="hidden" name="{{ $statusField }}" :value="status">

                <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4">
                    <div class="flex items-start gap-3.5">
                        <div class="w-8 h-8 rounded-xl font-extrabold text-sm flex items-center justify-center shrink-0 mt-0.5 transition-all"
                             :class="status === 1 ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-red-100 text-red-700 border border-red-300'">
                            {{ $pt['num'] }}
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-gray-800 tracking-tight">{{ $pt['title'] }}</h3>
                            <p class="text-xs text-gray-500 leading-relaxed mt-1 max-w-2xl">{{ $pt['desc'] }}</p>
                        </div>
                    </div>

                    <!-- Button Toggle (Ya / Tidak) -->
                    <div class="flex items-center gap-1.5 shrink-0 self-end sm:self-start bg-slate-100 p-1 rounded-xl border border-slate-200">
                        <button type="button" @click="status = 1"
                                class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold transition-all cursor-pointer"
                                :class="status === 1 ? 'bg-emerald-600 text-white shadow-xs' : 'text-gray-600 hover:text-gray-900 bg-transparent'">
                            <i class="ph-bold ph-check text-xs"></i>
                            <span>Ya (Lolos)</span>
                        </button>
                        <button type="button" @click="status = 0"
                                class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold transition-all cursor-pointer"
                                :class="status === 0 ? 'bg-red-600 text-white shadow-xs' : 'text-gray-600 hover:text-gray-900 bg-transparent'">
                            <i class="ph-bold ph-x text-xs"></i>
                            <span>Tidak (Gagal)</span>
                        </button>
                    </div>
                </div>

                <!-- Input Catatan Pemeriksaan (Wajib Diisi untuk Ya maupun Tidak) -->
                <div class="mt-4 pt-3 border-t transition-colors" :class="status === 1 ? 'border-gray-100' : 'border-red-200'">
                    <div class="flex items-center justify-between gap-2 mb-1.5">
                        <label class="text-xs font-bold flex items-center gap-1.5 transition-colors"
                               :class="status === 1 ? 'text-gray-700' : 'text-red-700'">
                            <i class="ph-bold" :class="status === 1 ? 'ph-note-pencil text-emerald-600' : 'ph-warning text-red-600'"></i>
                            <span x-text="status === 1 ? 'Catatan Pemeriksaan (Kondisi Bersih / Lolos)' : 'Jelaskan Kondisi Gagal / Tidak Lolos (Laporkan ke Supervisor)'"></span>
                        </label>
                        <span class="text-[11px] font-extrabold px-2 py-0.5 rounded border tracking-wide uppercase transition-colors"
                              :class="status === 1 ? 'text-emerald-700 bg-emerald-50 border-emerald-200' : 'text-red-700 bg-red-50 border-red-200'">
                            * Wajib Diisi
                        </span>
                    </div>
                    <input type="text" name="{{ $noteField }}" 
                           value="{{ old($noteField, $currentNote ?: $pt['default_note']) }}"
                           required
                           :placeholder="status === 1 ? 'Contoh: Kondisi baik, bersih, swab test bersih' : 'Wajib diisi: Jelaskan detail kerusakan / noda / retak...'"
                           class="form-req w-full text-xs px-3.5 py-2.5 rounded-xl border font-medium transition-all"
                           :class="status === 1 ? 'border-gray-200 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 bg-slate-50/50' : 'border-red-300 focus:border-red-500 focus:ring-2 focus:ring-red-500/20 bg-white'">
                    <p class="field-error-msg hidden text-[11px] text-red-600 font-semibold mt-1 items-center gap-1">
                        <i class="ph-bold ph-warning-circle"></i> Catatan pemeriksaan titik ini wajib diisi
                    </p>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Kesimpulan & Catatan Akhir -->
        <div x-data="{ kesimpulan: '{{ old('kesimpulan', $inspection->kesimpulan ?? 'layak') }}' }"
             class="bg-white rounded-2xl sm:rounded-3xl p-5 sm:p-7 border border-gray-100 shadow-[0_1px_6px_rgba(40,84,145,0.05)] mb-6">
            <h2 class="text-sm font-extrabold text-gray-700 uppercase tracking-wider mb-4 flex items-center gap-2">
                <i class="ph-bold ph-clipboard-text text-spv-blue text-base"></i>
                Kesimpulan Kelayakan Kontainer
            </h2>

            <p class="text-xs text-gray-600 mb-3">
                Saya telah memastikan bahwa kontainer tersebut dalam keadaan untuk dilakukan proses loading/stuffing:
            </p>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-5">
                <label class="relative flex items-center gap-3 p-3.5 rounded-2xl border cursor-pointer transition-all"
                       :class="kesimpulan === 'layak' ? 'border-emerald-500 bg-emerald-50/40 ring-2 ring-emerald-500/20' : 'border-gray-200 hover:border-gray-300'">
                    <input type="radio" name="kesimpulan" value="layak" x-model="kesimpulan" class="text-emerald-600 focus:ring-emerald-500">
                    <div>
                        <div class="text-xs font-extrabold text-emerald-800">LAYAK</div>
                        <div class="text-[11px] text-gray-500">Kontainer bersih & siap dimuat</div>
                    </div>
                </label>

                <label class="relative flex items-center gap-3 p-3.5 rounded-2xl border cursor-pointer transition-all"
                       :class="kesimpulan === 'layak_dengan_catatan' ? 'border-amber-500 bg-amber-50/40 ring-2 ring-amber-500/20' : 'border-gray-200 hover:border-gray-300'">
                    <input type="radio" name="kesimpulan" value="layak_dengan_catatan" x-model="kesimpulan" class="text-amber-600 focus:ring-amber-500">
                    <div>
                        <div class="text-xs font-extrabold text-amber-800">LAYAK DENGAN CATATAN</div>
                        <div class="text-[11px] text-gray-500">Perlu tindakan penyesuaian minor</div>
                    </div>
                </label>

                <label class="relative flex items-center gap-3 p-3.5 rounded-2xl border cursor-pointer transition-all"
                       :class="kesimpulan === 'tidak_layak' ? 'border-red-500 bg-red-50/40 ring-2 ring-red-500/20' : 'border-gray-200 hover:border-gray-300'">
                    <input type="radio" name="kesimpulan" value="tidak_layak" x-model="kesimpulan" class="text-red-600 focus:ring-red-500">
                    <div>
                        <div class="text-xs font-extrabold text-red-800">TIDAK LAYAK</div>
                        <div class="text-[11px] text-gray-500">Ditolak / Ganti kontainer</div>
                    </div>
                </label>
            </div>

            <div class="mb-5">
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1 flex items-center gap-2">
                    <span>Catatan Tambahan Kondisi Kontainer</span>
                    <span class="text-red-600 font-extrabold text-[11px] bg-red-50 px-2 py-0.5 rounded border border-red-200">* Wajib Diisi</span>
                </label>
                <p class="text-[11px] text-gray-500 mb-2">
                    Tuliskan catatan kondisi kontainer secara ringkas meskipun tidak ada kendala (misal: "Kontainer dalam keadaan bersih, kering, tidak berbau, dan siap muat").
                </p>
                <textarea name="catatan" rows="3" required
                          placeholder="Wajib diisi: Tuliskan catatan kondisi kontainer..."
                          class="form-req w-full text-xs px-3.5 py-2.5 rounded-xl border border-gray-200 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 font-medium">{{ old('catatan', $inspection->catatan ?: 'Kontainer dalam keadaan bersih, kering, tidak berbau tajam, bebas benda tajam/paku, dan layak untuk stuffing.') }}</textarea>
                <p class="field-error-msg hidden text-[11px] text-red-600 font-semibold mt-1 items-center gap-1">
                    <i class="ph-bold ph-warning-circle"></i> Catatan tambahan kondisi kontainer wajib diisi
                </p>
            </div>

            <!-- Pengesahan / Inspektor -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-4 border-t border-gray-100">
                <div>
                    <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1 flex items-center justify-between">
                        <span>Nama Inspektor <span class="text-red-500">*</span></span>
                        <span class="text-[10px] text-emerald-600 font-semibold normal-case">Tabel Karyawan</span>
                    </label>
                    <select name="nama_inspektor" required
                            class="form-req w-full text-sm font-semibold px-3.5 py-2.5 rounded-xl border border-gray-200 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all bg-white">
                        <option value="">-- Pilih Inspektor (Dari Tabel Karyawan) --</option>
                        @foreach($karyawans as $k)
                            <option value="{{ $k->nama }}" {{ old('nama_inspektor', $inspection->nama_inspektor) === $k->nama ? 'selected' : '' }}>
                                {{ $k->nama }} ({{ $k->position_label }} - {{ $k->nomor_induk }})
                            </option>
                        @endforeach
                        @if(!empty($inspection->nama_inspektor) && !$karyawans->contains('nama', $inspection->nama_inspektor))
                            <option value="{{ $inspection->nama_inspektor }}" selected>
                                {{ $inspection->nama_inspektor }} (Tersimpan Sebelumnya)
                            </option>
                        @endif
                    </select>
                    <p class="field-error-msg hidden text-[11px] text-red-600 font-semibold mt-1 items-center gap-1">
                        <i class="ph-bold ph-warning-circle"></i> Nama inspektor wajib dipilih dari tabel karyawan
                    </p>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1">Tanggal Inspeksi <span class="text-red-500">*</span></label>
                    <input type="date" name="tanggal_inspeksi"
                           value="{{ old('tanggal_inspeksi', $inspection->tanggal_inspeksi ? $inspection->tanggal_inspeksi->format('Y-m-d') : now()->toDateString()) }}"
                           @if($minDate) min="{{ $minDate }}" @endif
                           required
                           class="form-req w-full text-sm px-3.5 py-2.5 rounded-xl border border-gray-200 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20">
                    <p class="field-error-msg hidden text-[11px] text-red-600 font-semibold mt-1 items-center gap-1">
                        <i class="ph-bold ph-warning-circle"></i> Tanggal inspeksi wajib diisi
                    </p>
                    @if($minDate)
                    <p class="text-[10px] text-emerald-700 font-semibold mt-1 flex items-center gap-1">
                        <i class="ph-bold ph-lock"></i> Tanggal tidak bisa backdate (minimal hari ini)
                    </p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sticky Submit Bar -->
        <div class="sticky bottom-4 bg-white/95 backdrop-blur-md p-4 rounded-2xl border border-gray-200 shadow-xl flex flex-col sm:flex-row items-center justify-between gap-3 z-30">
            <div class="flex items-center gap-2 text-xs text-gray-500">
                <i class="ph-bold ph-info text-spv-blue text-base"></i>
                <span>Data form kontainer ini akan tercetak di <strong>Halaman 2 Laporan PDF</strong>.</span>
            </div>

            <div class="flex items-center gap-2 w-full sm:w-auto">
                <button type="submit" name="next" value="timeline"
                        class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold transition-all shadow-xs">
                    Simpan & Ke Timeline
                </button>
                <button type="submit" name="next" value="bale"
                        class="w-full sm:w-auto flex items-center justify-center gap-1.5 px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition-all shadow-md shadow-emerald-600/25">
                    <span>Simpan & Lanjut Form Bale</span>
                    <i class="ph-bold ph-arrow-right text-xs"></i>
                </button>
            </div>
        </div>
    </form>

</div>
</x-layout>
