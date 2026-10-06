<x-layout :title="'Unduh Batch ZIP Foto Evidence'">

    <div x-data="batchZipPageManager()" class="max-w-4xl mx-auto space-y-6">

        <!-- Breadcrumb & Header Card -->
        <div class="bg-white rounded-2xl sm:rounded-3xl p-4 sm:p-5 lg:p-6 border border-gray-100 shadow-[0_1px_6px_rgba(40,84,145,0.05)]">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2 text-xs text-gray-500 mb-2">
                        <a href="{{ route('dashboard') }}" class="hover:text-spv-blue transition-colors flex items-center gap-1">
                            <i class="ph-bold ph-house"></i>
                            <span>Dashboard</span>
                        </a>
                        <span>/</span>
                        <a href="{{ route('shipments.index') }}" class="hover:text-spv-blue transition-colors">
                            <span>Shipments</span>
                        </a>
                        <span>/</span>
                        <span class="text-spv-blue font-bold">Unduh Batch ZIP</span>
                    </div>
                    <h1 class="text-xl lg:text-2xl font-black text-gray-800 tracking-tight flex items-center gap-2.5">
                        <span class="w-9 h-9 rounded-xl flex items-center justify-center text-white bg-amber-500 shadow-sm shrink-0">
                            <i class="ph-bold ph-file-zip text-xl"></i>
                        </span>
                        <span>Pusat Unduh Batch ZIP Foto Evidence</span>
                    </h1>
                    <p class="text-xs text-gray-500 mt-1.5">Unduh seluruh berkas arsip foto dan dokumen staging terorganisir per folder tahun, bulan, hari, dan packing list.</p>
                </div>

                <div class="flex items-center gap-2 w-full sm:w-auto">
                    <a href="{{ route('dashboard') }}"
                       class="w-full sm:w-auto justify-center px-4 py-2.5 rounded-xl border border-gray-200 bg-white hover:bg-gray-50 text-xs font-bold text-gray-600 transition-all flex items-center gap-1.5 shadow-2xs">
                        <i class="ph-bold ph-arrow-left text-sm"></i>
                        <span>Kembali ke Dashboard</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Main Card Form -->
        <div class="bg-white rounded-3xl shadow-[0_1px_6px_rgba(40,84,145,0.05)] border border-gray-100 overflow-hidden">

            <!-- Card Header Notice -->
            <div class="p-5 sm:p-6 bg-gradient-to-r from-amber-500/10 via-amber-500/5 to-transparent border-b border-gray-100 flex items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-amber-500 text-white flex items-center justify-center shrink-0 shadow-sm">
                        <i class="ph-fill ph-archive text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-gray-900">Arsip Digital & Bukti Audit Pengiriman</h3>
                        <p class="text-xs text-gray-500">File ZIP master akan mengemas setiap shipment ke dalam folder sub-direktori otomatis.</p>
                    </div>
                </div>
            </div>

            <div class="p-5 sm:p-7 space-y-6">

                <!-- 1. Mode Periode Selector -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-2">Pilih Mode Periode Pengunduhan</label>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5">
                        <button type="button"
                                @click="setMode('harian')"
                                :class="mode === 'harian' ? 'border-spv-blue bg-blue-50/50 text-spv-blue ring-1 ring-spv-blue' : 'border-gray-200 bg-white text-gray-600 hover:bg-gray-50'"
                                class="p-3 rounded-2xl border text-xs text-center font-bold transition-all flex flex-col items-center gap-1.5 shadow-2xs">
                            <i class="ph-bold ph-calendar text-lg text-spv-blue"></i>
                            <span>Harian</span>
                        </button>

                        <button type="button"
                                @click="setMode('range')"
                                :class="mode === 'range' ? 'border-spv-blue bg-blue-50/50 text-spv-blue ring-1 ring-spv-blue' : 'border-gray-200 bg-white text-gray-600 hover:bg-gray-50'"
                                class="p-3 rounded-2xl border text-xs text-center font-bold transition-all flex flex-col items-center gap-1.5 shadow-2xs">
                            <i class="ph-bold ph-calendar-plus text-lg text-spv-blue"></i>
                            <span>Rentang Tanggal</span>
                        </button>

                        <button type="button"
                                @click="setMode('bulanan')"
                                :class="mode === 'bulanan' ? 'border-spv-blue bg-blue-50/50 text-spv-blue ring-1 ring-spv-blue' : 'border-gray-200 bg-white text-gray-600 hover:bg-gray-50'"
                                class="p-3 rounded-2xl border text-xs text-center font-bold transition-all flex flex-col items-center gap-1.5 shadow-2xs">
                            <i class="ph-bold ph-calendar-blank text-lg text-spv-blue"></i>
                            <span>Bulanan</span>
                        </button>

                        <button type="button"
                                @click="setMode('tahunan')"
                                :class="mode === 'tahunan' ? 'border-spv-blue bg-blue-50/50 text-spv-blue ring-1 ring-spv-blue' : 'border-gray-200 bg-white text-gray-600 hover:bg-gray-50'"
                                class="p-3 rounded-2xl border text-xs text-center font-bold transition-all flex flex-col items-center gap-1.5 shadow-2xs">
                            <i class="ph-bold ph-calendar-star text-lg text-spv-blue"></i>
                            <span>Tahunan</span>
                        </button>
                    </div>
                </div>

                <!-- 2. Form Input Berdasarkan Mode -->
                <!-- A. MODE HARIAN -->
                <div x-show="mode === 'harian'" class="space-y-3 bg-slate-50/80 p-4 sm:p-5 rounded-2xl border border-gray-200/70">
                    <div class="flex items-center justify-between flex-wrap gap-2">
                        <span class="text-xs font-bold text-gray-700 flex items-center gap-1.5">
                            <i class="ph-bold ph-calendar text-spv-blue"></i>
                            Pilih Tanggal Pengiriman
                        </span>
                        <div class="flex items-center gap-1.5">
                            <button type="button" @click="setToday()" class="text-[11px] font-bold px-3 py-1 rounded-xl bg-white border border-gray-200 hover:border-spv-blue hover:text-spv-blue text-gray-600 transition-all shadow-2xs">
                                Hari Ini
                            </button>
                            <button type="button" @click="setYesterday()" class="text-[11px] font-bold px-3 py-1 rounded-xl bg-white border border-gray-200 hover:border-spv-blue hover:text-spv-blue text-gray-600 transition-all shadow-2xs">
                                Kemarin
                            </button>
                        </div>
                    </div>

                    <div>
                        <input type="date" x-model="date" @change="onDateInputChanged()"
                               class="w-full bg-white border border-gray-200 rounded-xl px-3.5 py-2.5 text-xs font-semibold text-gray-700 focus:ring-2 focus:ring-spv-blue focus:border-spv-blue outline-none transition-all">
                    </div>
                </div>

                <!-- B. MODE RENTANG TANGGAL -->
                <div x-show="mode === 'range'" class="space-y-3 bg-slate-50/80 p-4 sm:p-5 rounded-2xl border border-gray-200/70">
                    <div class="flex items-center justify-between flex-wrap gap-2">
                        <span class="text-xs font-bold text-gray-700 flex items-center gap-1.5">
                            <i class="ph-bold ph-calendar-plus text-spv-blue"></i>
                            Pilih Rentang Tanggal Kustom
                        </span>
                        <div class="flex items-center gap-1.5 flex-wrap">
                            <button type="button" @click="setThisWeek()" class="text-[11px] font-bold px-3 py-1 rounded-xl bg-white border border-gray-200 hover:border-spv-blue hover:text-spv-blue text-gray-600 transition-all shadow-2xs">
                                Senin - Jumat Minggu Ini
                            </button>
                            <button type="button" @click="setLast7Days()" class="text-[11px] font-bold px-3 py-1 rounded-xl bg-white border border-gray-200 hover:border-spv-blue hover:text-spv-blue text-gray-600 transition-all shadow-2xs">
                                7 Hari Terakhir
                            </button>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
                        <div>
                            <label class="block text-[11px] font-semibold text-gray-500 mb-1">Mulai Tanggal</label>
                            <input type="date" x-model="startDate" @change="fetchPreview()"
                                   class="w-full bg-white border border-gray-200 rounded-xl px-3.5 py-2.5 text-xs font-semibold text-gray-700 focus:ring-2 focus:ring-spv-blue focus:border-spv-blue outline-none transition-all">
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-gray-500 mb-1">Sampai Tanggal</label>
                            <input type="date" x-model="endDate" @change="fetchPreview()"
                                   class="w-full bg-white border border-gray-200 rounded-xl px-3.5 py-2.5 text-xs font-semibold text-gray-700 focus:ring-2 focus:ring-spv-blue focus:border-spv-blue outline-none transition-all">
                        </div>
                    </div>
                </div>

                <!-- C. MODE BULANAN -->
                <div x-show="mode === 'bulanan'" class="space-y-3 bg-slate-50/80 p-4 sm:p-5 rounded-2xl border border-gray-200/70">
                    <div class="flex items-center justify-between flex-wrap gap-2">
                        <span class="text-xs font-bold text-gray-700 flex items-center gap-1.5">
                            <i class="ph-bold ph-calendar-blank text-spv-blue"></i>
                            Pilih Bulan dan Tahun
                        </span>
                        <button type="button" @click="setThisMonth()" class="text-[11px] font-bold px-3 py-1 rounded-xl bg-white border border-gray-200 hover:border-spv-blue hover:text-spv-blue text-gray-600 transition-all shadow-2xs">
                            Bulan Ini
                        </button>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[11px] font-semibold text-gray-500 mb-1">Tahun</label>
                            <select x-model.number="year" @change="fetchPreview()"
                                    class="w-full bg-white border border-gray-200 rounded-xl px-3.5 py-2.5 text-xs font-semibold text-gray-700 focus:ring-2 focus:ring-spv-blue focus:border-spv-blue outline-none transition-all">
                                <template x-for="y in yearsList" :key="y">
                                    <option :value="y" x-text="y"></option>
                                </template>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-gray-500 mb-1">Bulan</label>
                            <select x-model.number="month" @change="fetchPreview()"
                                    class="w-full bg-white border border-gray-200 rounded-xl px-3.5 py-2.5 text-xs font-semibold text-gray-700 focus:ring-2 focus:ring-spv-blue focus:border-spv-blue outline-none transition-all">
                                <template x-for="m in monthsList" :key="m.num">
                                    <option :value="m.num" x-text="m.name"></option>
                                </template>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- D. MODE TAHUNAN -->
                <div x-show="mode === 'tahunan'" class="space-y-3 bg-slate-50/80 p-4 sm:p-5 rounded-2xl border border-gray-200/70">
                    <div class="flex items-center justify-between flex-wrap gap-2">
                        <span class="text-xs font-bold text-gray-700 flex items-center gap-1.5">
                            <i class="ph-bold ph-calendar-star text-spv-blue"></i>
                            Pilih Tahun Arsip Penuh
                        </span>
                        <button type="button" @click="setThisYear()" class="text-[11px] font-bold px-3 py-1 rounded-xl bg-white border border-gray-200 hover:border-spv-blue hover:text-spv-blue text-gray-600 transition-all shadow-2xs">
                            Tahun Ini
                        </button>
                    </div>

                    <div>
                        <label class="block text-[11px] font-semibold text-gray-500 mb-1">Tahun Arsip</label>
                        <select x-model.number="year" @change="fetchPreview()"
                                class="w-full bg-white border border-gray-200 rounded-xl px-3.5 py-2.5 text-xs font-semibold text-gray-700 focus:ring-2 focus:ring-spv-blue focus:border-spv-blue outline-none transition-all">
                            <template x-for="y in yearsList" :key="y">
                                <option :value="y" x-text="y"></option>
                            </template>
                        </select>
                    </div>
                </div>

                <!-- 3. Filter Jenis Produk -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-2">Filter Jenis Komoditas Produk</label>
                    <div class="grid grid-cols-3 gap-2.5">
                        <button type="button"
                                @click="product = 'all'; fetchPreview()"
                                :class="product === 'all' ? 'border-spv-blue bg-blue-50/50 text-spv-blue font-bold ring-1 ring-spv-blue' : 'border-gray-200 bg-white text-gray-600 hover:bg-gray-50 font-medium'"
                                class="p-3 rounded-2xl border text-xs text-center transition-all flex flex-col items-center gap-1.5 shadow-2xs">
                            <i class="ph-bold ph-stack text-base"></i>
                            <span>Semua Produk</span>
                        </button>

                        <button type="button"
                                @click="product = 'fiber'; fetchPreview()"
                                :class="product === 'fiber' ? 'border-spv-blue bg-blue-50/50 text-spv-blue font-bold ring-1 ring-spv-blue' : 'border-gray-200 bg-white text-gray-600 hover:bg-gray-50 font-medium'"
                                class="p-3 rounded-2xl border text-xs text-center transition-all flex flex-col items-center gap-1.5 shadow-2xs">
                            <i class="ph-bold ph-cube text-base"></i>
                            <span>Fiber Saja</span>
                        </button>

                        <button type="button"
                                @click="product = 'sodium'; fetchPreview()"
                                :class="product === 'sodium' ? 'border-spv-blue bg-blue-50/50 text-spv-blue font-bold ring-1 ring-spv-blue' : 'border-gray-200 bg-white text-gray-600 hover:bg-gray-50 font-medium'"
                                class="p-3 rounded-2xl border text-xs text-center transition-all flex flex-col items-center gap-1.5 shadow-2xs">
                            <i class="ph-bold ph-flask text-base"></i>
                            <span>Sodium Saja</span>
                        </button>
                    </div>
                </div>

                <!-- 4. Pilihan Kualitas / Kompresi Foto -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-2">Kualitas & Ukuran Berkas Arsip</label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <label class="flex items-start gap-3 p-3.5 rounded-2xl border cursor-pointer transition-all"
                               :class="quality === 'compressed' ? 'border-emerald-500 bg-emerald-50/30 ring-1 ring-emerald-500' : 'border-gray-200 bg-white hover:bg-gray-50'">
                            <input type="radio" name="zip_quality" value="compressed" x-model="quality" @change="fetchPreview()" class="mt-0.5 text-emerald-600 focus:ring-emerald-500">
                            <div>
                                <span class="block text-xs font-bold text-gray-900">Terkonversi Cepat (~5MB/shipment)</span>
                                <span class="block text-[11px] text-gray-500 mt-0.5 leading-relaxed">Rekomendasi terbaik. Cepat diunduh, hemat kuota bandwidth, dan resolusi tetap jelas untuk audit.</span>
                            </div>
                        </label>

                        <label class="flex items-start gap-3 p-3.5 rounded-2xl border cursor-pointer transition-all"
                               :class="quality === 'original' ? 'border-spv-blue bg-blue-50/30 ring-1 ring-spv-blue' : 'border-gray-200 bg-white hover:bg-gray-50'">
                            <input type="radio" name="zip_quality" value="original" x-model="quality" @change="fetchPreview()" class="mt-0.5 text-spv-blue focus:ring-spv-blue">
                            <div>
                                <span class="block text-xs font-bold text-gray-900">Resolusi Mentah Penuh (~35MB/shipment)</span>
                                <span class="block text-[11px] text-gray-500 mt-0.5 leading-relaxed">Ukuran byte file asli kamera tanpa re-encoding. Cocok untuk pembesaran digital ekstrem.</span>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- 5. Visual Box Struktur Folder di Dalam ZIP -->
                <div class="rounded-2xl bg-slate-900 text-slate-200 p-4 sm:p-5 text-xs font-mono shadow-inner border border-slate-800">
                    <div class="flex items-center justify-between text-xs text-slate-400 pb-2.5 border-b border-slate-800 mb-2.5">
                        <span class="flex items-center gap-2 font-sans font-bold text-slate-300">
                            <i class="ph-bold ph-tree-structure text-emerald-400"></i>
                            Struktur Hierarki Folder File ZIP:
                        </span>
                        <span class="text-[11px] text-slate-400 font-bold truncate max-w-[280px]" x-text="stats.filename"></span>
                    </div>
                    <div class="space-y-1 text-xs leading-relaxed">
                        <div class="text-amber-300 font-bold">📁 <span x-text="stats.filename"></span></div>
                        <div class="pl-4 text-purple-300">└── 📁 <span x-text="previewFolderProduct()"></span>/</div>
                        <div class="pl-8 text-emerald-300">└── 📁 <span x-text="previewFolderYear()"></span>/</div>
                        <div class="pl-12 text-sky-300">└── 📁 <span x-text="previewFolderMonth()"></span>/</div>
                        <div class="pl-16 text-indigo-300">└── 📁 <span x-text="previewFolderDay()"></span>/</div>
                        <div class="pl-20 text-emerald-200">├── 🗜️ [Packing_List_1].zip</div>
                        <div class="pl-20 text-emerald-200">└── 🗜️ [Packing_List_2].zip</div>
                    </div>
                    <p class="text-[11px] text-slate-400 mt-3 pt-2 border-t border-slate-800/80 font-sans">
                        Di dalam setiap arsip packing list terdapat seluruh foto evidence 27 titik SOP bertanggal dan bertanda waktu WIB.
                    </p>
                </div>

                <!-- 6. Indikator Status & Ketersediaan Data -->
                <div class="p-4 rounded-2xl border transition-all"
                     :class="stats.count > 0 ? 'bg-emerald-50/80 border-emerald-200 text-emerald-900' : 'bg-amber-50/80 border-amber-200 text-amber-900'">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div class="flex items-center gap-2.5">
                            <template x-if="isLoading">
                                <span class="animate-spin text-base text-gray-500"><i class="ph-bold ph-spinner"></i></span>
                            </template>
                            <template x-if="!isLoading && stats.count > 0">
                                <span class="w-3 h-3 rounded-full bg-emerald-500 animate-pulse"></span>
                            </template>
                            <template x-if="!isLoading && stats.count === 0">
                                <span class="w-3 h-3 rounded-full bg-amber-500"></span>
                            </template>

                            <span class="text-xs font-semibold">
                                <template x-if="isLoading">
                                    <span>Memeriksa ketersediaan data shipment di server...</span>
                                </template>
                                <template x-if="!isLoading && stats.count > 0">
                                    <span>
                                        Ditemukan <strong class="font-extrabold text-emerald-950" x-text="stats.count"></strong> shipment siap dikompresi ke file ZIP.
                                    </span>
                                </template>
                                <template x-if="!isLoading && stats.count === 0">
                                    <span>Belum ada data shipment dengan bukti foto yang sesuai dengan filter ini.</span>
                                </template>
                            </span>
                        </div>

                        <template x-if="!isLoading && stats.count > 0">
                            <span class="inline-flex items-center gap-1.5 text-xs font-extrabold px-3 py-1 rounded-xl bg-white border border-emerald-200 text-emerald-800 shadow-2xs whitespace-nowrap">
                                <i class="ph-bold ph-gauge text-sm text-emerald-600"></i>
                                Estimasi Ukuran: <span x-text="stats.estimated_size"></span>
                            </span>
                        </template>
                    </div>
                </div>

            </div>

            <!-- Card Footer / Action Button -->
            <div class="p-5 sm:p-7 bg-gray-50 border-t border-gray-100 flex flex-col sm:flex-row items-center justify-between gap-4">
                <a href="{{ route('shipments.index') }}" class="text-xs font-bold text-gray-500 hover:text-gray-800 transition-colors">
                    &larr; Lihat Tabel Daftar Shipment
                </a>

                <button type="button"
                        @click="triggerDownload()"
                        :disabled="stats.count === 0 || isLoading || isDownloading"
                        :class="stats.count > 0 && !isDownloading ? 'bg-amber-500 hover:bg-amber-600 text-white shadow-[0_4px_14px_rgba(245,158,11,0.35)] hover:-translate-y-0.5' : 'bg-gray-200 text-gray-400 cursor-not-allowed'"
                        class="w-full sm:w-auto flex items-center justify-center gap-2.5 px-6 py-3 rounded-2xl text-xs font-bold transition-all">
                    <template x-if="isDownloading">
                        <span class="flex items-center gap-2">
                            <span class="animate-spin"><i class="ph-bold ph-spinner text-base"></i></span>
                            <span>Menyiapkan Berkas ZIP di Server...</span>
                        </span>
                    </template>
                    <template x-if="!isDownloading">
                        <span class="flex items-center gap-2">
                            <i class="ph-bold ph-download-simple text-base"></i>
                            <span>Unduh Berkas Batch ZIP Sekarang</span>
                        </span>
                    </template>
                </button>
            </div>

        </div>

    </div>

    <script>
    function batchZipPageManager() {
        const today = new Date();
        const curYear = today.getFullYear();
        const curMonth = today.getMonth() + 1;
        const curDay = today.getDate();
        const pad = (n) => String(n).padStart(2, '0');
        const todayStr = `${curYear}-${pad(curMonth)}-${pad(curDay)}`;

        const dayOfWeek = today.getDay();
        const diffToMon = (dayOfWeek === 0 ? -6 : 1) - dayOfWeek;
        const mon = new Date(today);
        mon.setDate(today.getDate() + diffToMon);
        const fri = new Date(mon);
        fri.setDate(mon.getDate() + 4);

        const monStr = `${mon.getFullYear()}-${pad(mon.getMonth() + 1)}-${pad(mon.getDate())}`;
        const friStr = `${fri.getFullYear()}-${pad(fri.getMonth() + 1)}-${pad(fri.getDate())}`;

        return {
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
                count: {{ $count ?? 0 }},
                sample_path: '{{ $samplePath ?? '-' }}',
                estimated_size: '{{ $estSize ?? '0 MB' }}',
                filename: '{{ $masterFileName ?? 'SPV_Evidence.zip' }}'
            },

            previewTimer: null,

            init() {
                this.fetchPreview();
            },

            setMode(newMode) {
                this.mode = newMode;
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
                return m ? m.code : 'OKTOBER';
            },

            previewFolderDay() {
                if (this.mode === 'tahunan' || this.mode === 'bulanan') {
                    return '{HARI}';
                }
                return this.day || curDay;
            },

            triggerDownload() {
                if (this.stats.count === 0 || this.isLoading || this.isDownloading) {
                    return;
                }
                this.isDownloading = true;
                const params = this.getQueryParams();
                const downloadUrl = `{{ route('shipments.download-batch-zip') }}?${params.toString()}`;

                window.location.href = downloadUrl;

                setTimeout(() => {
                    this.isDownloading = false;
                }, 4000);
            }
        };
    }
    </script>

</x-layout>
