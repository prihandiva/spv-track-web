<x-layout :title="'Parameter Sistem'">

    <!-- Header Section Card -->
    <div class="bg-white rounded-2xl sm:rounded-3xl p-4 sm:p-5 lg:p-6 border border-gray-100 shadow-[0_1px_6px_rgba(40,84,145,0.05)] mb-6">
        <h1 class="text-xl lg:text-2xl font-black text-gray-800 tracking-tight">Parameter Sistem & Warehouse</h1>
        <p class="text-xs text-gray-500 mt-1">Konfigurasi parameter operasional warehouse, modul AI & OCR, watermark dokumen, dan format unduhan yang disimpan di database.</p>
    </div>

    <!-- Navigation Tabs -->
    @include('settings.tabs', ['active' => 'system'])

    @php
        $appName = $settings['app_name']->value ?? 'SPV-Track Logistic Monitoring';
        $companyName = $settings['company_name']->value ?? 'PT SPV Evidence Logistics';
        $warehouseName = $settings['warehouse_default_name']->value ?? 'Warehouse A (Fiber & Sodium)';
        
        $locations = $settings['warehouse_locations']->value ?? '["Atas", "Tengah", "Bawah"]';
        if (is_string($locations) && str_starts_with($locations, '[')) {
            $locationsArr = json_decode($locations, true) ?: ['Atas', 'Tengah', 'Bawah'];
            $locationsStr = implode(', ', $locationsArr);
        } else {
            $locationsStr = (string)$locations;
        }

        $products = $settings['product_types']->value ?? '["Fiber", "Sodium"]';
        if (is_string($products) && str_starts_with($products, '[')) {
            $productsArr = json_decode($products, true) ?: ['Fiber', 'Sodium'];
            $productsStr = implode(', ', $productsArr);
        } else {
            $productsStr = (string)$products;
        }

        $ocrAuto = filter_var($settings['ocr_auto_validation']->value ?? '1', FILTER_VALIDATE_BOOLEAN);
        $aiPerson = filter_var($settings['ai_person_detection']->value ?? '1', FILTER_VALIDATE_BOOLEAN);
        $aiBarcode = filter_var($settings['ai_barcode_detection']->value ?? '1', FILTER_VALIDATE_BOOLEAN);
        $watermark = filter_var($settings['watermark_evidence']->value ?? '1', FILTER_VALIDATE_BOOLEAN);
        $zipFormat = $settings['zip_naming_format']->value ?? '{NOMOR_CONTAINER}_EVIDENCE';
    @endphp

    <form action="{{ route('settings.system.update') }}" method="POST">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            <!-- Section 1: Identitas Warehouse & Organisasi -->
            <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-[0_1px_6px_rgba(40,84,145,0.04)]">
                <div class="flex items-center gap-3 pb-4 mb-4 border-b border-gray-100">
                    <div class="w-10 h-10 rounded-xl bg-blue-50 text-spv-blue flex items-center justify-center font-bold">
                        <i class="ph-fill ph-buildings text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-gray-900">Identitas Warehouse & Perusahaan</h3>
                        <p class="text-[11px] text-gray-500">Nama identitas operasional dan kop laporan.</p>
                    </div>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1.5">Nama Aplikasi <span class="text-red-500">*</span></label>
                        <input type="text" name="app_name" value="{{ old('app_name', $appName) }}" required
                               class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-xs focus:ring-2 focus:ring-spv-blue focus:border-spv-blue outline-none transition-all">
                        <p class="text-[10px] text-gray-400 mt-1">Ditampilkan pada judul aplikasi dan navigasi sistem.</p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1.5">Nama Perusahaan / Organisasi <span class="text-red-500">*</span></label>
                        <input type="text" name="company_name" value="{{ old('company_name', $companyName) }}" required
                               class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-xs focus:ring-2 focus:ring-spv-blue focus:border-spv-blue outline-none transition-all">
                        <p class="text-[10px] text-gray-400 mt-1">Dicetak pada lembar Berita Acara dan laporan audit staging PDF.</p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1.5">Nama Warehouse Utama <span class="text-red-500">*</span></label>
                        <input type="text" name="warehouse_default_name" value="{{ old('warehouse_default_name', $warehouseName) }}" required
                               class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-xs focus:ring-2 focus:ring-spv-blue focus:border-spv-blue outline-none transition-all">
                    </div>
                </div>
            </div>

            <!-- Section 2: Master Opsi Operasional Lapangan -->
            <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-[0_1px_6px_rgba(40,84,145,0.04)]">
                <div class="flex items-center gap-3 pb-4 mb-4 border-b border-gray-100">
                    <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
                        <i class="ph-fill ph-map-pin text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-gray-900">Opsi Operasional Staging</h3>
                        <p class="text-[11px] text-gray-500">Pilihan dropdown yang muncul saat operator membuat sesi baru.</p>
                    </div>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1.5">Daftar Lokasi Staging Warehouse <span class="text-red-500">*</span></label>
                        <input type="text" name="warehouse_locations" value="{{ old('warehouse_locations', $locationsStr) }}" required
                               placeholder="Pisahkan dengan koma: Atas, Tengah, Bawah"
                               class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-xs focus:ring-2 focus:ring-spv-blue focus:border-spv-blue outline-none transition-all">
                        <p class="text-[10px] text-gray-400 mt-1">Pisahkan tiap opsi dengan tanda koma (misal: Atas, Tengah, Bawah).</p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1.5">Komoditas Produk Kargo <span class="text-red-500">*</span></label>
                        <input type="text" name="product_types" value="{{ old('product_types', $productsStr) }}" required
                               placeholder="Pisahkan dengan koma: Fiber, Sodium"
                               class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-xs focus:ring-2 focus:ring-spv-blue focus:border-spv-blue outline-none transition-all">
                        <p class="text-[10px] text-gray-400 mt-1">Pilihan produk kargo saat memulai checklist evidence.</p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1.5">Pola Format Berkas Batch ZIP <span class="text-red-500">*</span></label>
                        <input type="text" name="zip_naming_format" value="{{ old('zip_naming_format', $zipFormat) }}" required
                               class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-xs font-mono focus:ring-2 focus:ring-spv-blue focus:border-spv-blue outline-none transition-all">
                        <p class="text-[10px] text-gray-400 mt-1">Gunakan tag variabel seperti <code class="font-bold text-spv-blue">{NOMOR_CONTAINER}</code> atau <code class="font-bold text-spv-blue">{TIMESTAMP}</code>.</p>
                    </div>
                </div>
            </div>

            <!-- Section 3: AI & OCR Processing Settings -->
            <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-[0_1px_6px_rgba(40,84,145,0.04)]">
                <div class="flex items-center gap-3 pb-4 mb-4 border-b border-gray-100">
                    <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center font-bold">
                        <i class="ph-fill ph-cpu text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-gray-900">Validasi AI & OCR Asinkron</h3>
                        <p class="text-[11px] text-gray-500">Mesin analisis otomatis non-blocking di latar belakang.</p>
                    </div>
                </div>

                <div class="space-y-4">
                    <label class="flex items-start gap-3 p-3.5 rounded-xl bg-gray-50 border border-gray-200/70 cursor-pointer hover:bg-blue-50/20 transition-colors">
                        <input type="checkbox" name="ocr_auto_validation" value="1" {{ $ocrAuto ? 'checked' : '' }}
                               class="mt-1 rounded text-spv-blue focus:ring-spv-blue w-4 h-4 border-gray-300">
                        <div>
                            <p class="text-xs font-bold text-gray-800">Auto-OCR Validasi Nomor Container</p>
                            <p class="text-[11px] text-gray-500 mt-0.5">Jalankan pembacaan teks kontainer secara asinkron setelah foto diunggah tanpa memblokir petugas lapangan.</p>
                        </div>
                    </label>

                    <label class="flex items-start gap-3 p-3.5 rounded-xl bg-gray-50 border border-gray-200/70 cursor-pointer hover:bg-blue-50/20 transition-colors">
                        <input type="checkbox" name="ai_person_detection" value="1" {{ $aiPerson ? 'checked' : '' }}
                               class="mt-1 rounded text-spv-blue focus:ring-spv-blue w-4 h-4 border-gray-300">
                        <div>
                            <p class="text-xs font-bold text-gray-800">AI Deteksi Orang pada Pembersihan Bale</p>
                            <p class="text-[11px] text-gray-500 mt-0.5">Verifikasi otomatis bahwa petugas terlihat saat SOP pembersihan bale (titik 4, 5, 6).</p>
                        </div>
                    </label>

                    <label class="flex items-start gap-3 p-3.5 rounded-xl bg-gray-50 border border-gray-200/70 cursor-pointer hover:bg-blue-50/20 transition-colors">
                        <input type="checkbox" name="ai_barcode_detection" value="1" {{ $aiBarcode ? 'checked' : '' }}
                               class="mt-1 rounded text-spv-blue focus:ring-spv-blue w-4 h-4 border-gray-300">
                        <div>
                            <p class="text-xs font-bold text-gray-800">AI Deteksi Area & Barcode Bale</p>
                            <p class="text-[11px] text-gray-500 mt-0.5">Pemeriksaan visual keberadaan label barcode pada titik foto bales loading area.</p>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Section 4: Watermark & Evidence Integrity -->
            <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-[0_1px_6px_rgba(40,84,145,0.04)] flex flex-col justify-between">
                <div>
                    <div class="flex items-center gap-3 pb-4 mb-4 border-b border-gray-100">
                        <div class="w-10 h-10 rounded-xl bg-teal-50 text-teal-700 flex items-center justify-center font-bold">
                            <i class="ph-fill ph-stamp text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-gray-900">Integritas Bukti & Watermark Evidence</h3>
                            <p class="text-[11px] text-gray-500">Stempel legalitas dokumen dan audit trail pengiriman.</p>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <label class="flex items-start gap-3 p-3.5 rounded-xl bg-gray-50 border border-gray-200/70 cursor-pointer hover:bg-blue-50/20 transition-colors">
                            <input type="checkbox" name="watermark_evidence" value="1" {{ $watermark ? 'checked' : '' }}
                                   class="mt-1 rounded text-spv-blue focus:ring-spv-blue w-4 h-4 border-gray-300">
                            <div>
                                <p class="text-xs font-bold text-gray-800">Sertakan Watermark WIB & Metadata pada Ekspor PDF</p>
                                <p class="text-[11px] text-gray-500 mt-0.5">Secara otomatis menyematkan tanggal, waktu WIB, nomor kontainer, dan identitas petugas pada setiap berkas PDF cetak.</p>
                            </div>
                        </label>

                        <div class="p-4 rounded-xl bg-blue-50/50 border border-blue-100 text-xs text-gray-600 leading-relaxed">
                            <div class="flex items-center gap-2 font-bold text-spv-blue mb-1">
                                <i class="ph-bold ph-info text-base"></i>
                                Penyimpanan Asli Terproteksi
                            </div>
                            <p class="text-[11px] text-gray-500">
                                Berkas JPG asli dari petugas lapangan selalu tersimpan utuh tanpa modifikasi/kompresi destruktif di storage sistem untuk kebutuhan audit kepatuhan ISO & asuransi kargo.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Submit Button in Card or Bottom Bar -->
                <div class="mt-6 pt-4 border-t border-gray-100 flex items-center justify-end">
                    <button type="submit"
                            class="w-full sm:w-auto flex items-center justify-center gap-2 bg-spv-blue hover:bg-blue-800 text-white text-xs font-bold px-6 py-3 rounded-xl shadow-[0_4px_12px_rgba(40,84,145,0.25)] hover:-translate-y-0.5 transition-all">
                        <i class="ph-bold ph-floppy-disk text-base"></i>
                        Simpan Seluruh Parameter ke Database
                    </button>
                </div>
            </div>

        </div>
    </form>

</x-layout>
