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

    <!-- Barcode Scanner & App Script -->
    <script src="{{ asset('vendor/html5-qrcode.min.js') }}"></script>
    <script>
    function baleInspectionApp() {
        return {
            barcodeScanning: false,
            barcodeTitle: '',
            barcodeTargetTitle: '',
            barcodeTargetType: 'general', // 'awal' (rusak) atau 'pengganti'
            barcodeTargetCallback: null,
            barcodeOrientation: (function() {
                try {
                    return localStorage.getItem('spv_bale_barcode_orientation') || 'vertical';
                } catch (e) {
                    return 'vertical';
                }
            })(),
            html5Scanner: null,
            barcodeStream: null,
            barcodeHiResTimer: null,
            nativeDetector: null,
            manualBarcodeInput: '',
            barcodeSuccess: '',
            barcodeProcessing: false,

            openBarcodeScanner(title, callback, type = 'general') {
                this.barcodeTitle = title;
                this.barcodeTargetTitle = title;
                this.barcodeTargetType = type;
                this.barcodeTargetCallback = callback;
                this.manualBarcodeInput = '';
                this.barcodeSuccess = '';
                this.barcodeProcessing = false;
                this.barcodeScanning = true;
                this.$nextTick(() => {
                    this.startBarcodeScan();
                });
            },

            async setBarcodeOrientation(mode) {
                if (this.barcodeOrientation === mode) return;
                this.barcodeOrientation = mode;
                try { localStorage.setItem('spv_bale_barcode_orientation', mode); } catch (e) {}
                if (this.barcodeScanning) {
                    await this.closeBarcodeScanner(true);
                    this.barcodeScanning = true;
                    this.$nextTick(() => {
                        this.startBarcodeScan();
                    });
                }
            },

            cleanBarcodeNumber(raw) {
                if (!raw) return '';
                let text = String(raw).trim();

                // 1. Hapus AIM symbology identifier di awal jika ada (seperti ]C1, ]e0, ]d2, dll)
                text = text.replace(/^\][A-Za-z0-9]{2}/, '');

                // 2. Hapus tanda bintang pembungkus Code 39 (*12345678* -> 12345678)
                text = text.replace(/^\*+/, '').replace(/\*+$/, '');

                // 3. Tangani format GS1 Application Identifiers:
                // misal (21)12345678 (Serial/Bale number) -> ambil nilainya
                const gs1SerialMatch = text.match(/\(21\)\s*([A-Za-z0-9\-]+)/i);
                if (gs1SerialMatch) return gs1SerialMatch[1].trim();

                // misal (10)LOT12345 (Batch/Lot number)
                const gs1LotMatch = text.match(/\(10\)\s*([A-Za-z0-9\-]+)/i);
                if (gs1LotMatch) return gs1LotMatch[1].trim();

                // Jika format GS1 tanpa tanda kurung: misal diawali '21' diikuti nomor 6-14 digit
                if (/^21\d{6,14}$/.test(text)) {
                    text = text.substring(2);
                }

                // 4. Jika multiline atau mengandung label teks: "BALE: 123456" / "NO. BALE: 123456"
                const labeledMatch = text.match(/(?:BALE|NO\.?\s*BALE|NOMOR\s*BALE|BALL)[\s\.:#\-_]*([A-Za-z0-9\-]+)/i);
                if (labeledMatch) {
                    return labeledMatch[1].trim();
                }

                // 5. Jika mengandung baris baru, ambil baris pertama atau baris yang mengandung angka
                if (text.includes('\n')) {
                    const lines = text.split('\n').map(l => l.trim()).filter(Boolean);
                    const digitLine = lines.find(l => /\d/.test(l));
                    if (digitLine) text = digitLine;
                }

                // 6. Bersihkan karakter non-standar (hanya sisakan huruf, angka, tanda hubung/dash)
                text = text.replace(/[^A-Za-z0-9\-\s]/g, '').trim();

                // 7. Jika ada deretan digit signifikan (misal 6-14 digit), prioritaskan
                const digitsMatch = text.match(/\b([0-9]{6,14})\b/);
                if (digitsMatch) {
                    return digitsMatch[1];
                }

                return text;
            },

            async startBarcodeScan() {
                const isVertical = this.barcodeOrientation === 'vertical';

                if (typeof Html5Qrcode !== 'undefined') {
                    try {
                        const readerEl = document.getElementById('bale-barcode-reader');
                        if (readerEl) {
                            readerEl.innerHTML = '';
                            const fmt = window.Html5QrcodeSupportedFormats || (window.__Html5QrcodeLibrary__ && window.__Html5QrcodeLibrary__.Html5QrcodeSupportedFormats) || {};
                            const priorityFormats = [
                                fmt.CODE_128 ?? 5,
                                fmt.CODE_39 ?? 3,
                                fmt.CODE_93 ?? 4,
                                fmt.EAN_13 ?? 9,
                                fmt.ITF ?? 8,
                                fmt.UPC_A ?? 14,
                                fmt.QR_CODE ?? 0
                            ];

                            this.html5Scanner = new Html5Qrcode('bale-barcode-reader', {
                                formatsToSupport: priorityFormats,
                                experimentalFeatures: { useBarCodeDetectorIfSupported: true },
                                verbose: false
                            });

                            // Konfigurasi qrbox dinamis sesuai orientasi stiker
                            const scanConfig = {
                                fps: 20,
                                qrbox: (viewfinderWidth, viewfinderHeight) => {
                                    if (isVertical) {
                                        const w = Math.min(Math.floor(viewfinderWidth * 0.60), 200);
                                        const h = Math.min(Math.floor(viewfinderHeight * 0.82), 340);
                                        return { width: Math.max(w, 140), height: Math.max(h, 240) };
                                    } else {
                                        const w = Math.min(Math.floor(viewfinderWidth * 0.92), 350);
                                        const h = Math.min(Math.floor(viewfinderHeight * 0.48), 150);
                                        return { width: Math.max(w, 240), height: Math.max(h, 110) };
                                    }
                                },
                                aspectRatio: isVertical ? 0.85 : 1.333333
                            };

                            await this.html5Scanner.start(
                                { facingMode: 'environment' },
                                scanConfig,
                                (decodedText) => {
                                    this.handleBarcodeResult(decodedText);
                                },
                                () => {}
                            );

                            // Akselerasi Hardware Native BarcodeDetector (Omnidirectional 360 derajat)
                            // Sangat responsif membaca barcode batang Code 128 baik posisi horizontal maupun vertikal
                            if (window.BarcodeDetector) {
                                if (this.barcodeHiResTimer) clearInterval(this.barcodeHiResTimer);
                                this.barcodeHiResTimer = setInterval(async () => {
                                    if (!this.barcodeScanning || this.barcodeProcessing) return;
                                    const video = document.querySelector('#bale-barcode-reader video');
                                    if (!video || !video.videoWidth || !video.videoHeight || video.readyState < 2) return;

                                    try {
                                        if (!this.nativeDetector) {
                                            this.nativeDetector = new BarcodeDetector({
                                                formats: ['code_128', 'code_39', 'code_93', 'itf', 'ean_13', 'qr_code']
                                            });
                                        }
                                        const codes = await this.nativeDetector.detect(video);
                                        if (codes && codes.length > 0 && codes[0].rawValue) {
                                            this.handleBarcodeResult(codes[0].rawValue);
                                        }
                                    } catch (e) {}
                                }, 180);
                            }

                            return;
                        }
                    } catch (err) {
                        console.warn('Html5Qrcode scanner failed, fallback to native BarcodeDetector:', err);
                    }
                }

                // Fallback native BarcodeDetector jika Html5Qrcode gagal
                const video = this.$refs.baleBarcodeVideo;
                if (video && ('BarcodeDetector' in window)) {
                    navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } })
                        .then(stream => {
                            this.barcodeStream = stream;
                            video.style.display = 'block';
                            video.srcObject = stream;
                            video.play();
                            const detector = new BarcodeDetector({ formats: ['code_128', 'code_39', 'code_93', 'ean_13', 'ean_8', 'itf', 'qr_code', 'data_matrix'] });
                            const scan = () => {
                                if (!this.barcodeScanning || this.barcodeProcessing) return;
                                detector.detect(video).then(codes => {
                                    if (codes.length > 0 && codes[0].rawValue) {
                                        this.handleBarcodeResult(codes[0].rawValue);
                                    } else {
                                        requestAnimationFrame(scan);
                                    }
                                }).catch(() => requestAnimationFrame(scan));
                            };
                            requestAnimationFrame(scan);
                        })
                        .catch(() => {
                            this.barcodeScanning = false;
                            alert('Tidak dapat membuka kamera. Pastikan izin kamera aktif, atau gunakan barcode scanner USB/Bluetooth — klik pada kolom lalu scan.');
                        });
                } else {
                    alert('Scanner kamera tidak tersedia. Anda dapat mengetik nomor barcode atau menggunakan scanner USB.');
                }
            },

            async closeBarcodeScanner(isRestarting = false) {
                if (!isRestarting) {
                    this.barcodeScanning = false;
                    this.barcodeSuccess = '';
                    this.barcodeProcessing = false;
                }
                if (this.barcodeHiResTimer) {
                    clearInterval(this.barcodeHiResTimer);
                    this.barcodeHiResTimer = null;
                }
                this.nativeDetector = null;
                if (this.html5Scanner) {
                    try {
                        await this.html5Scanner.stop();
                        this.html5Scanner.clear();
                    } catch (e) {}
                    this.html5Scanner = null;
                }
                if (this.barcodeStream) {
                    this.barcodeStream.getTracks().forEach(t => t.stop());
                    this.barcodeStream = null;
                }
                const video = this.$refs.baleBarcodeVideo;
                if (video) {
                    video.style.display = 'none';
                    video.srcObject = null;
                }
            },

            playBeep() {
                try {
                    const ctx = new (window.AudioContext || window.webkitAudioContext)();
                    const osc = ctx.createOscillator();
                    const gain = ctx.createGain();
                    osc.connect(gain);
                    gain.connect(ctx.destination);
                    osc.frequency.value = 920;
                    gain.gain.setValueAtTime(0.2, ctx.currentTime);
                    gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.12);
                    osc.start();
                    osc.stop(ctx.currentTime + 0.12);
                    if (navigator.vibrate) navigator.vibrate(80);
                } catch (e) {}
            },

            handleBarcodeResult(raw) {
                if (this.barcodeProcessing) return;
                const cleaned = this.cleanBarcodeNumber(raw);
                if (!cleaned) return;

                this.barcodeProcessing = true;
                this.barcodeSuccess = cleaned;
                this.playBeep();

                if (typeof this.barcodeTargetCallback === 'function') {
                    this.barcodeTargetCallback(cleaned);
                }

                setTimeout(() => {
                    this.closeBarcodeScanner();
                }, 480);
            },

            submitManualBarcode() {
                if (this.manualBarcodeInput.trim()) {
                    const cleaned = this.cleanBarcodeNumber(this.manualBarcodeInput);
                    this.handleBarcodeResult(cleaned || this.manualBarcodeInput.trim());
                }
            },

            async scanBarcodeFromFile(event) {
                const file = event.target.files?.[0];
                if (!file) return;
                try {
                    if (window.BarcodeDetector) {
                        try {
                            const imgBmp = await createImageBitmap(file);
                            const detector = new BarcodeDetector({ formats: ['code_128', 'code_39', 'code_93', 'itf', 'ean_13', 'qr_code'] });
                            const codes = await detector.detect(imgBmp);
                            if (codes && codes.length > 0 && codes[0].rawValue) {
                                this.handleBarcodeResult(codes[0].rawValue);
                                return;
                            }
                        } catch (bdErr) {}
                    }
                    const fmt = window.Html5QrcodeSupportedFormats || (window.__Html5QrcodeLibrary__ && window.__Html5QrcodeLibrary__.Html5QrcodeSupportedFormats) || {};
                    const priorityFormats = [fmt.CODE_128 ?? 5, fmt.CODE_39 ?? 3, fmt.EAN_13 ?? 9, fmt.ITF ?? 8, fmt.QR_CODE ?? 0];
                    let scanner = new Html5Qrcode('bale-barcode-reader', { formatsToSupport: priorityFormats, verbose: false });
                    const result = await scanner.scanFile(file, true);
                    if (result) {
                        this.handleBarcodeResult(result);
                    }
                } catch (err) {
                    alert('Barcode tidak terdeteksi pada foto. Pastikan posisi barcode tajam dan jelas.');
                } finally {
                    event.target.value = '';
                }
            }
        };
    }
    </script>

    <style>
        @keyframes baleLaserVert {
            0%   { top: 8px; opacity: 1; }
            50%  { top: calc(100% - 10px); opacity: 0.9; }
            100% { top: 8px; opacity: 1; }
        }
        @keyframes baleLaserHoriz {
            0%   { left: 8px; opacity: 1; }
            50%  { left: calc(100% - 10px); opacity: 0.9; }
            100% { left: 8px; opacity: 1; }
        }
        .animate-bale-laser-vert {
            animation: baleLaserVert 1.8s ease-in-out infinite;
        }
        .animate-bale-laser-horiz {
            animation: baleLaserHoriz 1.8s ease-in-out infinite;
        }
        #bale-barcode-reader video {
            width: 100% !important;
            height: auto !important;
            object-fit: cover !important;
        }
        #bale-barcode-reader img { display: none !important; }
        #bale-barcode-reader__scan_region { border: none !important; }
        #bale-barcode-reader__dashboard { display: none !important; }

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

    @php
        $minDate = (auth()->user()?->isOperator() ?? true) ? date('Y-m-d') : '';
    @endphp

    <form id="baleForm" action="{{ route('field-app.bale-inspection.save', $shipment->id) }}" method="POST"
          x-data="baleInspectionApp()"
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
                        FORM INSPEKSI KONDISI BALE (LOADING)
                    </span>
                    <p class="text-[11px] text-gray-400 mt-1">Standar Kualitas & 4-Eyes Principle Verification</p>
                </div>
            </div>

            <!-- Loading Times Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-5">
                <div>
                    <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1 flex items-center justify-between">
                        <span>Tanggal Staging / Loading <span class="text-red-500">*</span></span>
                        <span class="text-[10px] text-emerald-600 font-semibold normal-case">Staging Date</span>
                    </label>
                    <input type="date" name="loading_date"
                           value="{{ old('loading_date', $inspection->loading_date ? $inspection->loading_date->format('Y-m-d') : ($shipment->tanggal_staging ? $shipment->tanggal_staging->format('Y-m-d') : now()->toDateString())) }}"
                           @if($minDate) min="{{ $minDate }}" @endif
                           required
                           class="form-req w-full text-sm font-bold px-3.5 py-2.5 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition-all">
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
                    <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1 flex items-center justify-between">
                        <span>Nama Petugas Loading <span class="text-red-500">*</span></span>
                        <span class="text-[10px] text-emerald-600 font-semibold normal-case">Tabel Karyawan</span>
                    </label>
                    <select name="nama_petugas_loading" required
                            class="form-req w-full text-sm font-semibold px-3.5 py-2.5 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition-all bg-white">
                        <option value="">-- Pilih Petugas Loading (Dari Tabel Karyawan) --</option>
                        @foreach($karyawans as $k)
                            <option value="{{ $k->nama }}" {{ old('nama_petugas_loading', $inspection->nama_petugas_loading) === $k->nama ? 'selected' : '' }}>
                                {{ $k->nama }} ({{ $k->position_label }} - {{ $k->nomor_induk }})
                            </option>
                        @endforeach
                        @if(!empty($inspection->nama_petugas_loading) && !$karyawans->contains('nama', $inspection->nama_petugas_loading))
                            <option value="{{ $inspection->nama_petugas_loading }}" selected>
                                {{ $inspection->nama_petugas_loading }} (Tersimpan Sebelumnya)
                            </option>
                        @endif
                    </select>
                    <p class="field-error-msg hidden text-[11px] text-red-600 font-semibold mt-1 items-center gap-1">
                        <i class="ph-bold ph-warning-circle"></i> Petugas loading wajib dipilih dari tabel karyawan
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
                           placeholder="Contoh: 800123456"
                           class="form-req w-full text-sm font-mono font-bold px-3.5 py-2.5 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 uppercase bg-slate-50/50">
                    <p class="field-error-msg hidden text-[11px] text-red-600 font-semibold mt-1 items-center gap-1">
                        <i class="ph-bold ph-warning-circle"></i> Nomor surat jalan wajib diisi
                    </p>
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1">Nama Pelanggan (Customer) <span class="text-red-500">*</span></label>
                    <input type="text" name="nama_pelanggan" value="{{ old('nama_pelanggan', $inspection->nama_pelanggan ?: $shipment->tujuan_pengiriman) }}" required
                           placeholder="Contoh: Pelabuhan Tanjung Priok"
                           class="form-req w-full text-sm px-3.5 py-2.5 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20">
                    <p class="field-error-msg hidden text-[11px] text-red-600 font-semibold mt-1 items-center gap-1">
                        <i class="ph-bold ph-warning-circle"></i> Nama pelanggan wajib diisi
                    </p>
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1">Jumlah Total Bale <span class="text-red-500">*</span></label>
                    <input type="number" name="jumlah_bale" value="{{ old('jumlah_bale', $inspection->jumlah_bale ?: $shipment->jumlah_bale) }}" placeholder="Contoh: 86" min="1" required
                           class="form-req w-full text-sm font-bold px-3.5 py-2.5 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20">
                    <p class="field-error-msg hidden text-[11px] text-red-600 font-semibold mt-1 items-center gap-1">
                        <i class="ph-bold ph-warning-circle"></i> Jumlah total bale wajib diisi
                    </p>
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1">No. Kontainer / Truk Lokal <span class="text-red-500">*</span></label>
                    <input type="text" name="nomor_kontainer_truk" value="{{ old('nomor_kontainer_truk', $inspection->nomor_kontainer_truk ?: ($shipment->nomor_container_atau_plat ?: $shipment->plat_nomor)) }}" required
                           placeholder="Contoh: MSKU1234567"
                           class="form-req w-full text-sm font-mono font-bold px-3.5 py-2.5 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 uppercase">
                    <p class="field-error-msg hidden text-[11px] text-red-600 font-semibold mt-1 items-center gap-1">
                        <i class="ph-bold ph-warning-circle"></i> Nomor kontainer/truk wajib diisi
                    </p>
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

            <div class="space-y-4">
                @foreach($defaultChecklist as $idx => $label)
                @php
                    $item = $items->get($idx);
                    $ada = old("items.{$idx}.ada", $item?->ada_ketidaksesuaian ?? false);
                    $jml = old("items.{$idx}.jumlah", $item?->jumlah_bale);
                    $tindakan = old("items.{$idx}.tindakan", $item?->tindakan_perbaikan ?? 'diganti');
                    $tindakanLain = old("items.{$idx}.tindakan_lainnya", $item?->tindakan_lainnya);
                    $pairsInit = $item?->bale_pairs ?? [['awal' => '', 'pengganti' => '']];
                    if (old("items.{$idx}.pairs")) {
                        $pairsInit = old("items.{$idx}.pairs");
                    } elseif (old("items.{$idx}.bale_awal")) {
                        $rawA = explode(',', old("items.{$idx}.bale_awal"));
                        $rawP = explode(',', old("items.{$idx}.bale_pengganti", ''));
                        $c = max(count($rawA), count($rawP));
                        $pairsInit = [];
                        for ($k=0; $k<$c; $k++) {
                            $pairsInit[] = ['awal' => trim($rawA[$k] ?? ''), 'pengganti' => trim($rawP[$k] ?? '')];
                        }
                    }
                @endphp
                <div x-data="{
                    ada: {{ $ada ? 1 : 0 }},
                    tindakan: '{{ $tindakan }}',
                    pairs: {{ Js::from($pairsInit) }},
                    addPair() {
                        this.pairs.push({ awal: '', pengganti: '' });
                    },
                    removePair(pIdx) {
                        if (this.pairs.length > 1) {
                            this.pairs.splice(pIdx, 1);
                        } else {
                            this.pairs = [{ awal: '', pengganti: '' }];
                        }
                    }
                }"
                     class="rounded-2xl p-4 sm:p-5 border transition-all"
                     :class="ada === 1 ? 'bg-amber-50/50 border-amber-300 shadow-sm' : 'bg-slate-50/50 border-gray-200'">
                    
                    <!-- Hidden input to reliably submit 0 or 1 -->
                    <input type="hidden" name="items[{{ $idx }}][ada]" :value="ada">

                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <span class="w-8 h-8 rounded-xl font-extrabold text-xs flex items-center justify-center shrink-0 transition-all"
                                  :class="ada === 1 ? 'bg-amber-500 text-white shadow-xs' : 'bg-white border border-gray-200 text-gray-600'">
                                {{ $idx }}
                            </span>
                            <div>
                                <span class="text-xs sm:text-sm font-bold text-gray-800">{{ $label }}</span>
                                <p x-show="ada === 1" class="text-[11px] text-amber-700 font-semibold mt-0.5" x-cloak>
                                    <span x-text="pairs.filter(p => p.awal || p.pengganti).length || pairs.length"></span> Bale Cacat Tercatat
                                </p>
                            </div>
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
                    <div x-show="ada === 1" x-cloak class="mt-4 pt-4 border-t border-amber-200/80 space-y-4">
                        <!-- Row 1: Tindakan Perbaikan & Ringkasan Qty -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">
                                    Tindakan Perbaikan <span class="text-red-500">*</span>
                                </label>
                                <select name="items[{{ $idx }}][tindakan]" x-model="tindakan" :required="ada === 1"
                                        class="form-req w-full text-xs font-semibold px-3 py-2.5 rounded-xl border border-gray-300 focus:border-amber-500 bg-white">
                                    <option value="diganti">Diganti</option>
                                    <option value="diperbaiki">Diperbaiki / ditambal / dibersihkan</option>
                                    <option value="ganti_label">Ganti label (EV / Non-EV)</option>
                                    <option value="lainnya">Lainnya</option>
                                </select>
                                <p class="field-error-msg hidden text-[11px] text-red-600 font-semibold mt-1 items-center gap-1">
                                    <i class="ph-bold ph-warning-circle"></i> Tindakan perbaikan wajib dipilih
                                </p>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">
                                    Jumlah Bale Cacat:
                                </label>
                                <div class="flex items-center gap-2">
                                    <input type="number" name="items[{{ $idx }}][jumlah]"
                                           :value="pairs.length"
                                           readonly
                                           class="w-24 text-xs font-extrabold px-3 py-2.5 rounded-xl border border-amber-300 bg-amber-50 text-amber-900 text-center">
                                    <span class="text-[11px] text-gray-500 font-medium leading-tight">
                                        Otomatis dihitung dari jumlah pasang bale rusak &amp; pengganti di bawah
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Row 2: Dynamic List of Pairs (Bale Rusak & Pengganti) with Barcode Scanner -->
                        <div class="bg-white/80 backdrop-blur-xs p-3.5 sm:p-4 rounded-xl border border-amber-200">
                            <div class="flex items-center justify-between mb-2.5 flex-wrap gap-2">
                                <div class="flex items-center gap-2">
                                    <i class="ph-bold ph-barcode text-amber-600 text-base"></i>
                                    <h4 class="text-xs font-extrabold text-gray-800 uppercase tracking-wide">
                                        Pencatatan Nomor Barcode Bale Rusak &amp; Penggantinya
                                    </h4>
                                </div>
                                <span class="text-[10px] font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded-md">
                                    Wajib Ada Penggantinya
                                </span>
                            </div>
                            <p class="text-[11px] text-gray-500 mb-3">
                                Gunakan tombol <strong>Scan</strong> untuk membaca barcode batang pada bale via kamera HP/Laptop, atau klik kolom dan tembak dengan scanner USB/Bluetooth.
                            </p>

                            <!-- Pairs Repeater -->
                            <div class="space-y-3">
                                <template x-for="(pair, pIdx) in pairs" :key="pIdx">
                                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 hover:border-amber-300 transition-all flex flex-col md:flex-row items-stretch md:items-center gap-3">
                                        <span class="w-6 h-6 rounded-lg bg-amber-500 text-white font-bold text-[11px] flex items-center justify-center shrink-0 self-start md:self-center"
                                              x-text="'#' + (pIdx + 1)"></span>

                                        <!-- Bale Rusak (Awal) -->
                                        <div class="flex-1">
                                            <label class="block text-[10px] font-bold text-gray-600 uppercase tracking-wider mb-1">
                                                No. Bale Rusak (Awal) <span class="text-red-500">*</span>
                                            </label>
                                            <div class="flex items-center gap-1.5">
                                                <input type="text"
                                                       :name="'items[{{ $idx }}][pairs][' + pIdx + '][awal]'"
                                                       x-model="pair.awal"
                                                       :required="ada === 1"
                                                       placeholder="Scan / ketik no bale rusak..."
                                                       class="form-req w-full text-xs font-mono font-bold px-3 py-2 rounded-lg border border-gray-300 focus:border-amber-500 bg-white uppercase">
                                                <button type="button"
                                                        @click="openBarcodeScanner('Scan: {{ addslashes($label) }} - Bale Rusak #' + (pIdx + 1), (val) => { pair.awal = val }, 'awal')"
                                                        class="px-2.5 py-2 rounded-lg bg-blue-50 text-spv-blue hover:bg-blue-100 border border-blue-200 text-xs font-bold flex items-center gap-1 shrink-0 transition-all cursor-pointer"
                                                        title="Scan Barcode Batang Bale Rusak">
                                                    <i class="ph-bold ph-barcode text-sm"></i>
                                                    <span class="hidden sm:inline">Scan</span>
                                                </button>
                                            </div>
                                            <p class="field-error-msg hidden text-[10px] text-red-600 font-semibold mt-1 items-center gap-1">
                                                <i class="ph-bold ph-warning-circle"></i> Nomor bale rusak wajib diisi
                                            </p>
                                        </div>

                                        <!-- Arrow Indicator -->
                                        <div class="hidden md:flex items-center justify-center shrink-0 text-amber-500 pt-3">
                                            <i class="ph-bold ph-arrow-right text-base"></i>
                                        </div>

                                        <!-- Bale Pengganti -->
                                        <div class="flex-1">
                                            <label class="block text-[10px] font-bold text-emerald-700 uppercase tracking-wider mb-1">
                                                No. Bale Pengganti <span class="text-red-500">* (Harus Ada)</span>
                                            </label>
                                            <div class="flex items-center gap-1.5">
                                                <input type="text"
                                                       :name="'items[{{ $idx }}][pairs][' + pIdx + '][pengganti]'"
                                                       x-model="pair.pengganti"
                                                       :required="ada === 1"
                                                       placeholder="Scan / ketik no bale baru..."
                                                       class="form-req w-full text-xs font-mono font-bold px-3 py-2 rounded-lg border border-emerald-300 focus:border-emerald-500 bg-emerald-50/20 uppercase">
                                                <button type="button"
                                                        @click="openBarcodeScanner('Scan: {{ addslashes($label) }} - Bale Pengganti #' + (pIdx + 1), (val) => { pair.pengganti = val }, 'pengganti')"
                                                        class="px-2.5 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold flex items-center gap-1 shrink-0 transition-all cursor-pointer shadow-xs"
                                                        title="Scan Barcode Batang Bale Pengganti">
                                                    <i class="ph-bold ph-barcode text-sm"></i>
                                                    <span class="hidden sm:inline">Scan</span>
                                                </button>
                                            </div>
                                            <p class="field-error-msg hidden text-[10px] text-red-600 font-semibold mt-1 items-center gap-1">
                                                <i class="ph-bold ph-warning-circle"></i> Nomor bale pengganti wajib diisi
                                            </p>
                                        </div>

                                        <!-- Action Delete (jika > 1) -->
                                        <div class="pt-0 md:pt-4 self-end md:self-center shrink-0">
                                            <button type="button"
                                                    x-show="pairs.length > 1"
                                                    @click="removePair(pIdx)"
                                                    class="p-2 rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50 transition-colors cursor-pointer"
                                                    title="Hapus baris bale ini">
                                                <i class="ph-bold ph-trash text-sm"></i>
                                            </button>
                                        </div>
                                    </div>
                                </template>
                            </div>

                            <!-- Add Pair Button -->
                            <div class="mt-3 pt-2.5 border-t border-slate-200/70 flex items-center justify-between flex-wrap gap-2">
                                <button type="button"
                                        @click="addPair()"
                                        class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-bold text-amber-800 bg-amber-100 hover:bg-amber-200 transition-all shadow-xs cursor-pointer">
                                    <i class="ph-bold ph-plus-circle text-base"></i>
                                    <span>Tambah Bale Rusak &amp; Pengganti</span>
                                </button>
                                <span class="text-[11px] font-semibold text-gray-600">
                                    Total: <strong class="text-amber-700" x-text="pairs.length"></strong> Pasang Bale
                                </span>
                            </div>

                            <!-- Fallback hidden inputs for legacy controller -->
                            <input type="hidden" name="items[{{ $idx }}][bale_awal]" :value="pairs.map(p => p.awal).filter(Boolean).join(', ')">
                            <input type="hidden" name="items[{{ $idx }}][bale_pengganti]" :value="pairs.map(p => p.pengganti).filter(Boolean).join(', ')">
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
                            <select name="first_eye_nama" required
                                    class="form-req w-full text-xs font-semibold px-3 py-2 rounded-xl border border-gray-200 bg-white focus:border-blue-500">
                                <option value="">-- Pilih Pemeriksa Pertama (Dari Tabel Karyawan) --</option>
                                @foreach($karyawans as $k)
                                    <option value="{{ $k->nama }}" {{ old('first_eye_nama', $inspection->first_eye_nama) === $k->nama ? 'selected' : '' }}>
                                        {{ $k->nama }} ({{ $k->position_label }} - {{ $k->nomor_induk }})
                                    </option>
                                @endforeach
                                @if(!empty($inspection->first_eye_nama) && !$karyawans->contains('nama', $inspection->first_eye_nama))
                                    <option value="{{ $inspection->first_eye_nama }}" selected>
                                        {{ $inspection->first_eye_nama }} (Tersimpan Sebelumnya)
                                    </option>
                                @endif
                            </select>
                            <p class="field-error-msg hidden text-[11px] text-red-600 font-semibold mt-1 items-center gap-1">
                                <i class="ph-bold ph-warning-circle"></i> Nama pemeriksa pertama wajib dipilih
                            </p>
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-gray-600 mb-1">Jabatan / Unit <span class="text-red-500">*</span></label>
                            <input type="text" name="first_eye_jabatan" value="{{ old('first_eye_jabatan', $inspection->first_eye_jabatan ?: 'Inspektor / QPD') }}" required
                                   class="form-req w-full text-xs px-3 py-2 rounded-xl border border-gray-200 bg-white">
                            <p class="field-error-msg hidden text-[11px] text-red-600 font-semibold mt-1 items-center gap-1">
                                <i class="ph-bold ph-warning-circle"></i> Jabatan pemeriksa pertama wajib diisi
                            </p>
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-gray-600 mb-1">Tanggal Verifikasi <span class="text-red-500">*</span></label>
                            <input type="date" name="first_eye_tanggal"
                                   value="{{ old('first_eye_tanggal', $inspection->first_eye_tanggal ? $inspection->first_eye_tanggal->format('Y-m-d') : now()->toDateString()) }}"
                                   @if($minDate) min="{{ $minDate }}" @endif
                                   required
                                   class="form-req w-full text-xs px-3 py-2 rounded-xl border border-gray-200 bg-white">
                            <p class="field-error-msg hidden text-[11px] text-red-600 font-semibold mt-1 items-center gap-1">
                                <i class="ph-bold ph-warning-circle"></i> Tanggal verifikasi pemeriksa pertama wajib diisi
                            </p>
                            @if($minDate)
                            <p class="text-[10px] text-emerald-700 font-semibold mt-1 flex items-center gap-1">
                                <i class="ph-bold ph-lock"></i> Tanggal tidak bisa backdate (minimal hari ini)
                            </p>
                            @endif
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
                            <select name="second_eye_nama"
                                    class="w-full text-xs font-semibold px-3 py-2 rounded-xl border border-blue-200 bg-white focus:border-spv-blue">
                                <option value="">-- Pilih Verifikator (Dari Tabel Karyawan) --</option>
                                @foreach($karyawans as $k)
                                    <option value="{{ $k->nama }}" {{ old('second_eye_nama', $inspection->second_eye_nama) === $k->nama ? 'selected' : '' }}>
                                        {{ $k->nama }} ({{ $k->position_label }} - {{ $k->nomor_induk }})
                                    </option>
                                @endforeach
                                @if(!empty($inspection->second_eye_nama) && !$karyawans->contains('nama', $inspection->second_eye_nama))
                                    <option value="{{ $inspection->second_eye_nama }}" selected>
                                        {{ $inspection->second_eye_nama }} (Tersimpan Sebelumnya)
                                    </option>
                                @endif
                            </select>
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-gray-600 mb-1">Jabatan / Unit</label>
                            <input type="text" name="second_eye_jabatan" value="{{ old('second_eye_jabatan', $inspection->second_eye_jabatan) }}"
                                   class="w-full text-xs px-3 py-2 rounded-xl border border-blue-200 bg-white">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-gray-600 mb-1">Tanggal Verifikasi</label>
                            <input type="date" name="second_eye_tanggal"
                                   value="{{ old('second_eye_tanggal', $inspection->second_eye_tanggal ? $inspection->second_eye_tanggal->format('Y-m-d') : now()->toDateString()) }}"
                                   @if($minDate) min="{{ $minDate }}" @endif
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
                        <input type="radio" name="stagging_2_rubber_cone" value="1" {{ old('stagging_2_rubber_cone', $inspection->stagging_2_rubber_cone ?? 1) ? 'checked' : '' }} class="text-emerald-600 focus:ring-emerald-500">
                        <span>Ya (Ada 2 Cone)</span>
                    </label>
                    <label class="flex items-center gap-1.5 text-xs font-bold text-gray-700 cursor-pointer">
                        <input type="radio" name="stagging_2_rubber_cone" value="0" {{ old('stagging_2_rubber_cone', $inspection->stagging_2_rubber_cone) === 0 ? 'checked' : '' }} class="text-red-600 focus:ring-red-500">
                        <span>Tidak</span>
                    </label>
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-gray-600 mb-1">Nama / Kode Lokasi Stagging: <span class="text-red-500">*</span></label>
                    <input type="text" name="lokasi_stagging" value="{{ old('lokasi_stagging', $inspection->lokasi_stagging) }}" placeholder="Contoh: Bay 3 Atas" required
                           class="form-req w-full text-xs px-3 py-2 rounded-xl border border-gray-200 focus:border-blue-500">
                    <p class="field-error-msg hidden text-[11px] text-red-600 font-semibold mt-1 items-center gap-1">
                        <i class="ph-bold ph-warning-circle"></i> Lokasi stagging wajib diisi
                    </p>
                </div>
            </div>

            <!-- F. Kondisi Proses Loading -->
            <div class="bg-white rounded-2xl sm:rounded-3xl p-5 border border-gray-100 shadow-[0_1px_6px_rgba(40,84,145,0.05)]">
                <h2 class="text-sm font-extrabold text-gray-700 uppercase tracking-wider mb-3 flex items-center gap-2">
                    <span class="w-5 h-5 rounded-md bg-spv-blue text-white flex items-center justify-center text-xs font-black">F</span>
                    Kondisi Pemuatan
                </h2>
                <div class="mb-3">
                    <label class="block text-[11px] font-bold text-gray-600 mb-1">Cuaca: <span class="text-red-500">*</span></label>
                    <div class="grid grid-cols-2 gap-1.5">
                        @foreach(['kering' => 'Kering', 'mendung' => 'Mendung', 'gerimis' => 'Gerimis', 'hujan' => 'Hujan'] as $cVal => $cLbl)
                        <label class="flex items-center gap-1.5 text-xs font-medium text-gray-700 cursor-pointer">
                            <input type="radio" name="cuaca" value="{{ $cVal }}" {{ old('cuaca', $inspection->cuaca ?? 'kering') === $cVal ? 'checked' : '' }} required class="text-spv-blue focus:ring-spv-blue">
                            <span>{{ $cLbl }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-gray-600 mb-1">Waktu: <span class="text-red-500">*</span></label>
                    <div class="flex items-center gap-3">
                        @foreach(['siang' => 'Siang', 'sore' => 'Sore', 'malam' => 'Malam'] as $wVal => $wLbl)
                        <label class="flex items-center gap-1.5 text-xs font-medium text-gray-700 cursor-pointer">
                            <input type="radio" name="waktu" value="{{ $wVal }}" {{ old('waktu', $inspection->waktu ?? 'siang') === $wVal ? 'checked' : '' }} required class="text-spv-blue focus:ring-spv-blue">
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

        <!-- Barcode Scanner Modal (Flexible Vertikal / Horizontal) -->
        <div x-cloak x-show="barcodeScanning"
             class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-black/85 backdrop-blur-xs"
             @keydown.escape.window="closeBarcodeScanner()">
            <div class="bg-slate-900 border border-slate-700 rounded-3xl overflow-hidden w-full max-w-md shadow-2xl relative" @click.stop>
                <!-- Modal Header: Berubah warna adaptif sesuai target Bale Rusak vs Bale Pengganti -->
                <div class="p-4 flex items-center justify-between text-white transition-all"
                     :class="barcodeTargetType === 'pengganti' ? 'bg-gradient-to-r from-emerald-800 to-teal-900 border-b border-emerald-600/30' : 'bg-gradient-to-r from-spv-blue to-teal-800 border-b border-blue-600/30'">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-xl flex items-center justify-center"
                             :class="barcodeTargetType === 'pengganti' ? 'bg-emerald-500/20 text-emerald-300' : 'bg-white/10 text-white'">
                            <i class="ph-bold ph-barcode text-lg"></i>
                        </div>
                        <div>
                            <p class="text-xs font-extrabold" x-text="barcodeTitle || (barcodeTargetType === 'pengganti' ? 'Scan Barcode Bale Pengganti' : 'Scan Barcode Bale Rusak')"></p>
                            <p class="text-[10px] text-white/80"
                               x-text="barcodeTargetType === 'pengganti' ? 'Arahkan kamera ke stiker barcode BALE PENGGANTI' : 'Arahkan kamera ke stiker barcode BALE RUSAK'"></p>
                        </div>
                    </div>
                    <button type="button" @click="closeBarcodeScanner()"
                            class="px-2.5 py-1 rounded-lg bg-white/10 hover:bg-white/20 text-white text-xs font-bold transition-all cursor-pointer">
                        ✕ Tutup
                    </button>
                </div>

                <!-- Camera Viewfinder with Dynamic Flexible Reticle (Vertical / Horizontal) -->
                <div class="relative w-full min-h-[300px] bg-black flex items-center justify-center overflow-hidden">
                    <div id="bale-barcode-reader" class="w-full"></div>
                    <video x-ref="baleBarcodeVideo" autoplay playsinline muted
                           class="w-full max-h-[340px] object-cover" style="display: none;"></video>

                    <!-- Orientation Switcher Pill (Floating Top) -->
                    <div class="absolute top-3 inset-x-0 z-20 flex items-center justify-center pointer-events-auto">
                        <div class="bg-slate-950/80 backdrop-blur-md p-1 rounded-xl border border-white/15 flex items-center gap-1 shadow-lg">
                            <button type="button"
                                    @click="setBarcodeOrientation('vertical')"
                                    :class="barcodeOrientation === 'vertical' ? 'bg-amber-500 text-slate-950 font-black shadow-sm' : 'text-white/70 hover:text-white'"
                                    class="px-2.5 py-1 rounded-lg text-[11px] font-bold flex items-center gap-1 transition-all cursor-pointer">
                                <i class="ph-bold ph-arrows-down-up text-xs"></i>
                                <span>Stiker Vertikal</span>
                            </button>
                            <button type="button"
                                    @click="setBarcodeOrientation('horizontal')"
                                    :class="barcodeOrientation === 'horizontal' ? 'bg-amber-500 text-slate-950 font-black shadow-sm' : 'text-white/70 hover:text-white'"
                                    class="px-2.5 py-1 rounded-lg text-[11px] font-bold flex items-center gap-1 transition-all cursor-pointer">
                                <i class="ph-bold ph-arrows-left-right text-xs"></i>
                                <span>Horizontal</span>
                            </button>
                        </div>
                    </div>

                    <!-- Reticle Frame Overlay -->
                    <div class="absolute inset-0 pointer-events-none flex flex-col items-center justify-center pt-8 pb-3">
                        <!-- Mode Vertikal (Tinggi & Ramping untuk stiker vertikal) -->
                        <div x-show="barcodeOrientation === 'vertical'"
                             class="relative w-[170px] h-[260px] border-2 border-dashed rounded-2xl flex items-center justify-center overflow-hidden shadow-[0_0_0_9999px_rgba(0,0,0,0.55)] transition-all duration-200"
                             :class="barcodeTargetType === 'pengganti' ? 'border-emerald-400' : 'border-amber-400'">
                            <!-- Laser scanline horizontal bergerak vertikal melintasi barcode -->
                            <div class="absolute inset-x-0 h-0.5 bg-red-500 shadow-[0_0_10px_#ef4444] animate-bale-laser-vert"></div>
                            <div class="absolute top-2 text-[9px] font-mono font-bold px-2 py-0.5 rounded bg-black/70 text-amber-300">
                                ↕ STIKER VERTIKAL
                            </div>
                        </div>

                        <!-- Mode Horizontal (Lebar untuk stiker horizontal) -->
                        <div x-show="barcodeOrientation === 'horizontal'"
                             class="relative w-[85%] max-w-[320px] h-[115px] border-2 border-dashed rounded-2xl flex items-center justify-center overflow-hidden shadow-[0_0_0_9999px_rgba(0,0,0,0.55)] transition-all duration-200"
                             :class="barcodeTargetType === 'pengganti' ? 'border-emerald-400' : 'border-amber-400'">
                            <!-- Laser scanline vertikal bergerak horizontal melintasi barcode -->
                            <div class="absolute inset-y-0 w-0.5 bg-red-500 shadow-[0_0_10px_#ef4444] animate-bale-laser-horiz"></div>
                            <div class="absolute top-2 left-2 text-[9px] font-mono font-bold px-2 py-0.5 rounded bg-black/70 text-amber-300">
                                ↔ HORIZONTAL
                            </div>
                        </div>

                        <p class="text-[11px] text-white/90 font-medium mt-3 bg-black/70 px-3 py-1 rounded-full border border-white/10 text-center max-w-[90%]">
                            <span x-show="barcodeOrientation === 'vertical'">Arahkan stiker barcode vertikal bale ke dalam kotak</span>
                            <span x-show="barcodeOrientation === 'horizontal'">Arahkan stiker barcode horizontal ke dalam kotak</span>
                        </p>
                    </div>

                    <!-- Success Feedback Overlay -->
                    <div x-show="barcodeSuccess" x-cloak
                         class="absolute inset-0 z-30 bg-slate-950/90 backdrop-blur-xs flex flex-col items-center justify-center p-6 text-center animate-fade-in">
                        <div class="w-14 h-14 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center mb-2.5 ring-8 ring-emerald-500/10">
                            <i class="ph-bold ph-check text-2xl"></i>
                        </div>
                        <p class="text-xs text-emerald-400 font-bold uppercase tracking-wider mb-1">Nomor Barcode Berhasil Terbaca</p>
                        <p class="text-base font-mono font-black text-white px-4 py-1.5 bg-white/10 rounded-xl border border-white/10 tracking-wider" x-text="barcodeSuccess"></p>
                        <p class="text-[10px] text-slate-400 mt-2">Memasukkan nilai ke formulir...</p>
                    </div>
                </div>

                <!-- Footer: Input Manual & Upload Foto Barcode -->
                <div class="p-4 border-t border-slate-800 bg-slate-950/70 space-y-2.5">
                    <label class="block text-[11px] font-bold text-gray-300">Input Manual Nomor Barcode Bale:</label>
                    <div class="flex items-center gap-2">
                        <input type="text"
                               x-model="manualBarcodeInput"
                               @keydown.enter.prevent="submitManualBarcode()"
                               placeholder="Ketik nomor bale..."
                               class="flex-1 text-xs px-3 py-2 rounded-xl bg-slate-900 border border-slate-700 text-white placeholder-slate-500 focus:border-amber-500 focus:ring-1 focus:ring-amber-500 uppercase font-mono font-bold">
                        <button type="button"
                                @click="submitManualBarcode()"
                                class="px-3.5 py-2 bg-amber-500 hover:bg-amber-600 text-slate-950 text-xs font-bold rounded-xl transition-all cursor-pointer">
                            Gunakan
                        </button>
                    </div>

                    <div class="pt-1 flex items-center justify-between gap-2 flex-wrap">
                        <!-- Upload File / Foto Barcode dari Galeri/Kamera -->
                        <label class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-[11px] font-bold text-sky-300 bg-sky-950/60 hover:bg-sky-900/60 border border-sky-700/50 cursor-pointer transition-all">
                            <i class="ph-bold ph-image text-sm"></i>
                            <span>Unggah Foto Barcode</span>
                            <input type="file" accept="image/*" class="hidden" @change="scanBarcodeFromFile($event)">
                        </label>
                        <span class="text-[10px] text-slate-400">
                            💡 Bisa tembak langsung via scanner USB/BT
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </form>

</div>
</x-layout>
