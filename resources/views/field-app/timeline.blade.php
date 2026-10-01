<x-field-layout>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-gray-800 uppercase">{{ $shipment->nomor_container_atau_plat }}</h1>
            <p class="text-sm text-gray-500 mt-1 capitalize">{{ $shipment->jenis_produk }} - {{ $shipment->jenis_pengiriman }}</p>
        </div>
        <div class="text-right">
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-yellow-100 text-yellow-700 border border-yellow-200 shadow-sm">
                <div class="w-2 h-2 rounded-full bg-yellow-500 mr-2 animate-pulse"></div> Draft
            </span>
        </div>
    </div>

    <!-- Timeline Wrapper -->
    <div class="space-y-4 relative before:absolute before:inset-0 before:ml-5 before:-translate-x-px md:before:mx-auto md:before:translate-x-0 before:h-full before:w-0.5 before:bg-gradient-to-b before:from-transparent before:via-gray-300 before:to-transparent">

        @foreach($points as $point)
            <!-- Timeline Item -->
            <div class="relative flex items-center justify-between md:justify-normal md:odd:flex-row-reverse group is-active">
                
                <!-- Icon Indicator -->
                <div class="flex items-center justify-center w-10 h-10 rounded-full border-4 border-gray-50 bg-white text-gray-400 group-hover:text-spv-blue group-hover:border-blue-50 shadow-sm shrink-0 md:order-1 md:group-odd:-translate-x-1/2 md:group-even:translate-x-1/2 z-10 transition-colors duration-300">
                    <i class="ph-bold ph-camera"></i>
                </div>

                <!-- Card -->
                <div class="w-[calc(100%-3rem)] md:w-[calc(50%-2.5rem)] p-4 rounded-2xl bg-white shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)] border border-gray-100 hover:shadow-[0_8px_20px_-6px_rgba(6,81,237,0.15)] hover:-translate-y-1 hover:border-spv-blue/30 transition-all duration-300 cursor-pointer">
                    
                    <div class="flex items-start justify-between mb-2">
                        <div class="flex items-center space-x-2">
                            <span class="w-6 h-6 rounded bg-gray-100 text-gray-600 text-xs font-bold flex items-center justify-center">{{ $point->urutan }}</span>
                            <h3 class="font-bold text-gray-800 text-sm leading-snug">{{ $point->nama_titik }}</h3>
                        </div>
                        @if($point->wajib)
                            <span class="text-[10px] uppercase font-bold text-red-500 bg-red-50 px-2 py-0.5 rounded-full">Wajib</span>
                        @endif
                    </div>
                    
                    <p class="text-xs text-gray-500 mb-4">{{ $point->deskripsi }}</p>
                    
                    <!-- Upload Split Box -->
                    <div class="relative w-full border-2 border-dashed border-gray-200 rounded-xl bg-gray-50/50 hover:border-spv-blue/50 transition-colors duration-300 overflow-hidden group/upload">
                        
                        <!-- Camera & Gallery Buttons -->
                        <div id="icon-{{$point->id}}" class="flex w-full divide-x divide-gray-200">
                            <!-- Camera -->
                            <label class="flex-1 py-4 flex flex-col items-center justify-center cursor-pointer hover:bg-blue-50/50 transition-colors group/btn">
                                <input type="file" class="hidden" accept="image/*" capture="environment" onchange="previewImage(this, 'preview-{{$point->id}}', 'icon-{{$point->id}}')">
                                <i class="ph-bold ph-camera text-2xl text-gray-400 group-hover/btn:text-spv-blue mb-1"></i>
                                <span class="text-xs font-medium text-gray-500 group-hover/btn:text-spv-blue">Kamera</span>
                            </label>
                            
                            <!-- Gallery -->
                            <label class="flex-1 py-4 flex flex-col items-center justify-center cursor-pointer hover:bg-blue-50/50 transition-colors group/btn">
                                <input type="file" class="hidden" accept="image/*" onchange="previewImage(this, 'preview-{{$point->id}}', 'icon-{{$point->id}}')">
                                <i class="ph-bold ph-image text-2xl text-gray-400 group-hover/btn:text-spv-blue mb-1"></i>
                                <span class="text-xs font-medium text-gray-500 group-hover/btn:text-spv-blue">Galeri</span>
                            </label>
                        </div>
                        
                        <!-- Image Preview Container -->
                        <div id="preview-wrapper-{{$point->id}}" class="hidden absolute inset-0 z-10 bg-black">
                            <img id="preview-{{$point->id}}" class="w-full h-full object-cover opacity-90" />
                            
                            <!-- Retake Overlay Buttons -->
                            <div class="absolute inset-0 flex items-center justify-center space-x-4 opacity-0 hover:opacity-100 transition-opacity bg-black/40">
                                <label class="w-10 h-10 rounded-full bg-white/20 hover:bg-spv-blue backdrop-blur-sm flex items-center justify-center text-white cursor-pointer shadow-lg transition-colors">
                                    <input type="file" class="hidden" accept="image/*" capture="environment" onchange="previewImage(this, 'preview-{{$point->id}}', 'icon-{{$point->id}}')">
                                    <i class="ph-bold ph-camera text-xl"></i>
                                </label>
                                <label class="w-10 h-10 rounded-full bg-white/20 hover:bg-spv-blue backdrop-blur-sm flex items-center justify-center text-white cursor-pointer shadow-lg transition-colors">
                                    <input type="file" class="hidden" accept="image/*" onchange="previewImage(this, 'preview-{{$point->id}}', 'icon-{{$point->id}}')">
                                    <i class="ph-bold ph-image text-xl"></i>
                                </label>
                            </div>
                        </div>
                        
                        <!-- Async Validate Badge (Mock) -->
                        <div id="badge-{{$point->id}}" class="hidden absolute bottom-2 left-1/2 -translate-x-1/2 z-20 px-3 py-1 rounded-full text-[10px] font-bold bg-black/60 text-white backdrop-blur-sm whitespace-nowrap">
                            <i class="ph-bold ph-check-circle text-spv-light-green mr-1"></i> OCR Match
                        </div>

                    </div>
                </div>

            </div>
        @endforeach

    <!-- Add Extra Photo Button -->
        <div class="relative flex items-center justify-center pt-6 pb-6 z-10">
            <button class="flex items-center space-x-2 bg-white border border-gray-200 text-gray-600 font-semibold py-3 px-6 rounded-full shadow-sm hover:shadow-md hover:border-spv-blue hover:text-spv-blue hover:-translate-y-1 transition-all duration-300">
                <i class="ph-bold ph-plus"></i>
                <span>Tambah Foto Ekstra</span>
            </button>
        </div>

    </div>

    <!-- Final Surat Jalan Details -->
    <form action="{{ route('field-app.submit', $shipment->id) }}" method="POST" id="submitForm">
        @csrf
        <div class="bg-white rounded-2xl p-5 shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)] border border-gray-100 mb-28">
            <h3 class="font-bold text-gray-800 mb-4 flex items-center">
                <i class="ph-fill ph-file-text text-spv-blue mr-2 text-xl"></i>
                Data Surat Jalan (Bisa Diedit)
            </h3>

            <!-- Dedicated OCR Scan Surat Jalan -->
            <div class="mb-5 bg-blue-50/50 border border-blue-100 rounded-xl p-3">
                <div class="flex items-center justify-between mb-2">
                    <div>
                        <h4 class="text-xs font-bold text-spv-blue">Scan Surat Bersih (OCR)</h4>
                        <p class="text-[10px] text-gray-500">Scan dokumen sebelum ditumpuk scanner</p>
                    </div>
                </div>
                <label class="block w-full border-2 border-dashed border-spv-blue/30 rounded-lg bg-white hover:bg-blue-50/50 transition-colors cursor-pointer text-center p-3 relative overflow-hidden group">
                    <input type="file" class="hidden" accept="image/*" onchange="previewOcrSuratJalan(this)">
                    <div id="ocr-sj-placeholder" class="flex flex-col items-center justify-center space-y-1">
                        <i class="ph-bold ph-camera text-xl text-gray-400 group-hover:text-spv-blue"></i>
                        <span class="text-[10px] font-medium text-gray-500 group-hover:text-spv-blue">Ambil Foto Dokumen Bersih</span>
                    </div>
                    <img id="ocr-sj-preview" class="hidden absolute inset-0 w-full h-full object-cover z-10 opacity-40 mix-blend-multiply" />
                    <div id="ocr-sj-loading" class="hidden absolute inset-0 bg-white/80 z-20 flex flex-col items-center justify-center backdrop-blur-sm">
                        <i class="ph-bold ph-spinner animate-spin text-spv-blue text-xl mb-1"></i>
                        <span class="text-[10px] font-bold text-spv-blue uppercase tracking-wider">Membaca Dokumen...</span>
                    </div>
                </label>
            </div>
            
            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Packing List (Number)</label>
                    <input type="text" id="packing_list_no" name="packing_list_no" placeholder="Contoh: 803282245" class="w-full bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-spv-blue focus:border-spv-blue outline-none transition-all">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Tujuan Pengiriman</label>
                    <textarea id="tujuan_pengiriman" name="tujuan_pengiriman" rows="2" placeholder="Alamat tujuan..." class="w-full bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-spv-blue focus:border-spv-blue outline-none transition-all"></textarea>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Agen Forwarding</label>
                    <textarea id="agen_forwarding" name="agen_forwarding" rows="2" placeholder="Nama perusahaan forwarder..." class="w-full bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-spv-blue focus:border-spv-blue outline-none transition-all"></textarea>
                </div>
                
                <div class="grid grid-cols-2 gap-3 pt-2 border-t border-gray-100">
                    <div>
                        <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Waktu Datang</label>
                        <input type="datetime-local" id="waktu_kedatangan_container" name="waktu_kedatangan_container" class="w-full bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 text-xs focus:ring-2 focus:ring-spv-blue outline-none">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Waktu Keluar</label>
                        <input type="datetime-local" id="waktu_keberangkatan_container" name="waktu_keberangkatan_container" class="w-full bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 text-xs focus:ring-2 focus:ring-spv-blue outline-none">
                    </div>
                </div>
            </div>
        </div>

    <!-- Final Submit Sticky Footer -->
    <div class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-100 p-4 shadow-[0_-4px_20px_-10px_rgba(0,0,0,0.1)] z-50">
        <div class="max-w-md mx-auto flex space-x-3">
            <button type="button" class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold rounded-xl py-3.5 transition-colors">
                Preview
            </button>
            <button type="submit" class="flex-2 w-2/3 bg-spv-green hover:bg-[#048232] text-white font-bold rounded-xl py-3.5 shadow-lg shadow-spv-green/30 hover:shadow-[#048232]/40 hover:-translate-y-1 transition-all duration-300 flex items-center justify-center space-x-2">
                <i class="ph-bold ph-check-circle text-lg"></i>
                <span>Submit Final</span>
            </button>
        </div>
    </div>
    </form>

    <!-- Simple Script for Preview Image to make it interactive -->
    <script>
        function previewOcrSuratJalan(input) {
            if (input.files && input.files[0]) {
                const preview = document.getElementById('ocr-sj-preview');
                const loading = document.getElementById('ocr-sj-loading');
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.classList.remove('hidden');
                    loading.classList.remove('hidden');
                    
                    const formData = new FormData();
                    formData.append('image', input.files[0]);
                    formData.append('_token', '{{ csrf_token() }}');
                    
                    fetch('{{ route('field-app.ocr.surat-jalan') }}', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        loading.classList.add('hidden');
                        
                        if (data.packing_list_no) document.getElementById('packing_list_no').value = data.packing_list_no;
                        if (data.tujuan_pengiriman) document.getElementById('tujuan_pengiriman').value = data.tujuan_pengiriman;
                        if (data.agen_forwarding) document.getElementById('agen_forwarding').value = data.agen_forwarding;
                        
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

        function previewImage(input, previewId, iconId) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById(previewId);
                    const icon = document.getElementById(iconId);
                    const pointId = previewId.split('-')[1];
                    const wrapper = document.getElementById('preview-wrapper-' + pointId);
                    const badge = document.getElementById('badge-' + pointId);
                    
                    preview.src = e.target.result;
                    wrapper.classList.remove('hidden');
                    icon.classList.add('hidden');
                    
                    // Mocking async validation delay
                    setTimeout(() => {
                        badge.classList.remove('hidden');
                        badge.classList.add('animate-bounce'); 
                        setTimeout(() => badge.classList.remove('animate-bounce'), 1000);
                    }, 1500);
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
</x-field-layout>
