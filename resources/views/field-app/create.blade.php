<x-field-layout>
    
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Mulai Staging</h1>
        <p class="text-sm text-gray-500 mt-1">Lengkapi form kondisi lapangan sebelum memulai upload foto.</p>
    </div>

    <form action="{{ route('field-app.store') }}" method="POST" class="space-y-5">
        @csrf

        <!-- Produk & Pengiriman -->
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Produk</label>
                <select name="jenis_produk" class="w-full bg-white border border-gray-300 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-spv-blue focus:border-spv-blue outline-none transition-all shadow-sm hover:border-spv-blue">
                    <option value="fiber">Fiber</option>
                    <option value="sodium">Sodium</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Pengiriman</label>
                <select name="jenis_pengiriman" class="w-full bg-white border border-gray-300 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-spv-blue focus:border-spv-blue outline-none transition-all shadow-sm hover:border-spv-blue">
                    <option value="export">Export</option>
                    <option value="lokal">Lokal</option>
                </select>
            </div>
        </div>

        <!-- OCR Scan Shipment Order -->
        <div class="bg-blue-50/50 border border-blue-100 rounded-xl p-4">
            <div class="flex items-center justify-between mb-3">
                <div>
                    <h3 class="text-sm font-bold text-spv-blue">Scan Shipment Order</h3>
                    <p class="text-xs text-gray-500">Auto-fill data dari dokumen (Opsional)</p>
                </div>
                <div class="w-8 h-8 rounded-full bg-spv-blue/10 text-spv-blue flex items-center justify-center">
                    <i class="ph-bold ph-scan text-lg"></i>
                </div>
            </div>
            
            <label class="block w-full border-2 border-dashed border-spv-blue/30 rounded-xl bg-white hover:bg-blue-50/50 transition-colors cursor-pointer text-center p-4 relative overflow-hidden group">
                <input type="file" name="shipment_order_photo" class="hidden" accept="image/*" onchange="previewOcrDoc(this)">
                <div id="ocr-doc-placeholder" class="flex flex-col items-center justify-center space-y-1">
                    <i class="ph-bold ph-camera text-2xl text-gray-400 group-hover:text-spv-blue"></i>
                    <span class="text-xs font-medium text-gray-500 group-hover:text-spv-blue">Ambil Foto / Unggah Surat</span>
                </div>
                <img id="ocr-doc-preview" class="hidden absolute inset-0 w-full h-full object-cover z-10 opacity-40 mix-blend-multiply" />
                <div id="ocr-loading" class="hidden absolute inset-0 bg-white/80 z-20 flex flex-col items-center justify-center backdrop-blur-sm">
                    <i class="ph-bold ph-spinner animate-spin text-spv-blue text-2xl mb-1"></i>
                    <span class="text-[10px] font-bold text-spv-blue uppercase tracking-wider">Membaca Dokumen...</span>
                </div>
            </label>
        </div>

        <!-- Hasil Ekstraksi Surat Shipment (Bisa Diedit Manual) -->
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Shipment Group</label>
                <input type="text" id="shipment_group" name="shipment_group" placeholder="Contoh: 869026477" class="w-full bg-white border border-gray-300 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-spv-blue focus:border-spv-blue outline-none transition-all shadow-sm hover:border-spv-blue">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Shipment No.</label>
                <input type="text" id="shipment_no" name="shipment_no" placeholder="Contoh: 860110875" class="w-full bg-white border border-gray-300 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-spv-blue focus:border-spv-blue outline-none transition-all shadow-sm hover:border-spv-blue">
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Plat Nomor</label>
                <input type="text" id="plat_nomor" name="plat_nomor" placeholder="Contoh: B 9619 UF2" class="w-full bg-white border border-gray-300 rounded-xl px-4 py-3 text-sm uppercase focus:ring-2 focus:ring-spv-blue focus:border-spv-blue outline-none transition-all shadow-sm hover:border-spv-blue">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Nama Sopir</label>
                <input type="text" id="nama_sopir" name="nama_sopir" placeholder="Contoh: Dadang" class="w-full bg-white border border-gray-300 rounded-xl px-4 py-3 text-sm capitalize focus:ring-2 focus:ring-spv-blue focus:border-spv-blue outline-none transition-all shadow-sm hover:border-spv-blue">
            </div>
        </div>

        <div class="pt-4 border-t border-gray-200">
            <label class="block text-sm font-semibold text-gray-700 mb-1">No. Container (Jika ada)</label>
            <input type="text" id="nomor_container_atau_plat" name="nomor_container_atau_plat" placeholder="Contoh: MSKU1234567" class="w-full bg-white border border-gray-300 rounded-xl px-4 py-3 text-sm uppercase focus:ring-2 focus:ring-spv-blue focus:border-spv-blue outline-none transition-all shadow-sm hover:border-spv-blue">
        </div>

        <!-- Script OCR Autofill -->
        <script>
            function previewOcrDoc(input) {
                if (input.files && input.files[0]) {
                    const preview = document.getElementById('ocr-doc-preview');
                    const loading = document.getElementById('ocr-loading');
                    
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        preview.src = e.target.result;
                        preview.classList.remove('hidden');
                        loading.classList.remove('hidden');
                        
                        // Create FormData and send to API
                        const formData = new FormData();
                        formData.append('image', input.files[0]);
                        // Ensure CSRF token is sent
                        formData.append('_token', '{{ csrf_token() }}');
                        
                        fetch('{{ route('field-app.ocr.shipment-order') }}', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            loading.classList.add('hidden');
                            
                            // Auto fill extracted data
                            if (data.shipment_group) document.getElementById('shipment_group').value = data.shipment_group;
                            if (data.shipment_no) document.getElementById('shipment_no').value = data.shipment_no;
                            
                            console.log("Raw OCR Text:", data.raw_text);
                            alert('Proses OCR Selesai!\nSistem mencoba mengekstrak data otomatis. Silakan periksa hasilnya.');
                        })
                        .catch(error => {
                            loading.classList.add('hidden');
                            console.error('OCR Error:', error);
                            alert('Gagal membaca dokumen (Error API).');
                        });
                    }
                    reader.readAsDataURL(input.files[0]);
                }
            }
        </script>

        <!-- Cuaca & Waktu -->
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Cuaca</label>
                <select name="cuaca" class="w-full bg-white border border-gray-300 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-spv-blue focus:border-spv-blue outline-none transition-all shadow-sm hover:border-spv-blue">
                    <option value="kering">Kering</option>
                    <option value="mendung">Mendung</option>
                    <option value="hujan">Hujan</option>
                    <option value="gerimis">Gerimis</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Waktu</label>
                <select name="waktu" class="w-full bg-white border border-gray-300 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-spv-blue focus:border-spv-blue outline-none transition-all shadow-sm hover:border-spv-blue">
                    <option value="siang">Siang</option>
                    <option value="sore">Sore</option>
                    <option value="malam">Malam</option>
                </select>
            </div>
        </div>

        <!-- Tanggal & Lokasi -->
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Tanggal</label>
                <input type="date" name="tanggal_staging" required value="{{ date('Y-m-d') }}" class="w-full bg-white border border-gray-300 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-spv-blue focus:border-spv-blue outline-none transition-all shadow-sm hover:border-spv-blue">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Lokasi WH</label>
                <select name="warehouse_lokasi" class="w-full bg-white border border-gray-300 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-spv-blue focus:border-spv-blue outline-none transition-all shadow-sm hover:border-spv-blue">
                    <option value="atas">Atas</option>
                    <option value="tengah">Tengah</option>
                    <option value="bawah">Bawah</option>
                </select>
            </div>
        </div>

        <!-- Petugas -->
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Nama Petugas</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <i class="ph-bold ph-user text-gray-400"></i>
                </div>
                <select name="karyawan_id" required class="w-full bg-white border border-gray-300 rounded-xl pl-10 pr-4 py-3 text-sm focus:ring-2 focus:ring-spv-blue focus:border-spv-blue outline-none transition-all shadow-sm hover:border-spv-blue appearance-none">
                    <option value="">Pilih Petugas...</option>
                    @foreach($karyawans as $karyawan)
                        <option value="{{ $karyawan->id }}">{{ $karyawan->nama }} ({{ $karyawan->nomor_induk }})</option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="pt-4">
            <button type="submit" class="w-full bg-spv-blue hover:bg-spv-dark-teal text-white font-bold rounded-xl py-4 flex items-center justify-center space-x-2 shadow-lg shadow-spv-blue/30 hover:shadow-spv-dark-teal/40 hover:-translate-y-1 transition-all duration-300">
                <span>Lanjut ke Timeline Foto</span>
                <i class="ph-bold ph-arrow-right"></i>
            </button>
        </div>

    </form>
</x-field-layout>
