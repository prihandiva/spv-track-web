<x-field-layout>

    <!-- Page Header -->
    <div style="padding: 4px 2px 8px;">
        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 4px;">
            <div style="width: 36px; height: 36px; border-radius: 12px; background: linear-gradient(135deg, #285491, #0d5950); display: flex; align-items: center; justify-content: center;">
                <i class="ph-bold ph-clipboard-text" style="font-size: 18px; color: white;"></i>
            </div>
            <div>
                <h1 style="font-size: 16px; font-weight: 700; color: #1f2937; line-height: 1.2;">Mulai Staging</h1>
                <p style="font-size: 11px; color: #9ca3af;">Isi data awal sebelum upload foto</p>
            </div>
        </div>
    </div>

    <form action="{{ route('field-app.store', [], false) }}" method="POST" style="display: flex; flex-direction: column; gap: 10px;">
        @csrf

        <!-- SECTION 1: Data Pengiriman -->
        <div class="section-card" x-data="{ open: true }">
            <div class="section-card-header" @click="open = !open"
                 style="background: linear-gradient(90deg, rgba(40,84,145,0.04), transparent);">
                <div style="width: 28px; height: 28px; border-radius: 8px; background: #eff4fc; display: flex; align-items: center; justify-content: center; shrink: 0;">
                    <i class="ph-fill ph-truck" style="font-size: 14px; color: #285491;"></i>
                </div>
                <div style="flex: 1;">
                    <p style="font-size: 12px; font-weight: 700; color: #1f2937;">Data Pengiriman</p>
                    <p style="font-size: 10px; color: #9ca3af;">Produk, tipe & waktu</p>
                </div>
                <i class="ph-bold" :class="open ? 'ph-caret-up' : 'ph-caret-down'" style="font-size: 14px; color: #9ca3af;"></i>
            </div>
            <div class="section-card-body" x-show="open" x-collapse style="display: flex; flex-direction: column; gap: 10px;">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                    <div>
                        <label class="field-label">Produk</label>
                        <select name="jenis_produk" class="field-input">
                            <option value="fiber">Fiber</option>
                            <option value="sodium">Sodium</option>
                        </select>
                    </div>
                    <div>
                        <label class="field-label">Jenis Pengiriman</label>
                        <select name="jenis_pengiriman" class="field-input">
                            <option value="export">Export</option>
                            <option value="lokal">Lokal</option>
                        </select>
                    </div>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                    <div>
                        <label class="field-label">Cuaca</label>
                        <select name="cuaca" class="field-input">
                            <option value="kering">☀️ Kering</option>
                            <option value="mendung">☁️ Mendung</option>
                            <option value="hujan">🌧️ Hujan</option>
                            <option value="gerimis">🌦️ Gerimis</option>
                        </select>
                    </div>
                    <div>
                        <label class="field-label">Waktu</label>
                        <select name="waktu" class="field-input">
                            <option value="siang">🌤️ Siang</option>
                            <option value="sore">🌅 Sore</option>
                            <option value="malam">🌙 Malam</option>
                        </select>
                    </div>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                    <div>
                        <label class="field-label">Tanggal Staging</label>
                        <input type="date" name="tanggal_staging" required value="{{ date('Y-m-d') }}" class="field-input">
                    </div>
                    <div>
                        <label class="field-label">Lokasi WH</label>
                        <select name="warehouse_lokasi" class="field-input">
                            <option value="atas">Atas</option>
                            <option value="tengah">Tengah</option>
                            <option value="bawah">Bawah</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="field-label">No. Container (Opsional)</label>
                    <input type="text" id="nomor_container_atau_plat" name="nomor_container_atau_plat" placeholder="Contoh: MSKU1234567" class="field-input" style="text-transform:uppercase;">
                </div>
            </div>
        </div>

        <!-- SECTION 2: Scan Shipment Order (OCR) -->
        <div class="section-card" x-data="{ open: true }">
            <div class="section-card-header" @click="open = !open"
                 style="background: linear-gradient(90deg, rgba(5,158,61,0.04), transparent);">
                <div style="width: 28px; height: 28px; border-radius: 8px; background: #e1f8eb; display: flex; align-items: center; justify-content: center;">
                    <i class="ph-bold ph-scan" style="font-size: 14px; color: #059e3d;"></i>
                </div>
                <div style="flex: 1;">
                    <p style="font-size: 12px; font-weight: 700; color: #1f2937;">Scan Shipment Order <span style="font-size: 10px; font-weight: 500; color: #9ca3af;">(Opsional)</span></p>
                    <p style="font-size: 10px; color: #9ca3af;">OCR auto-fill data dari dokumen</p>
                </div>
                <i class="ph-bold" :class="open ? 'ph-caret-up' : 'ph-caret-down'" style="font-size: 14px; color: #9ca3af;"></i>
            </div>
            <div class="section-card-body" x-show="open" x-collapse style="display: flex; flex-direction: column; gap: 10px;">
                <!-- Upload area -->
                <label style="display: block; border: 2px dashed #bfcfe8; border-radius: 12px; background: #fafcff; cursor: pointer; text-align: center; padding: 16px; position: relative; overflow: hidden; transition: all 0.2s;"
                       onmouseover="this.style.borderColor='#285491'; this.style.background='#f5f8ff'"
                       onmouseout="this.style.borderColor='#bfcfe8'; this.style.background='#fafcff'">
                    <input type="file" name="shipment_order_photo" style="display:none;" accept="image/*" onchange="previewOcrDoc(this)">
                    <div id="ocr-doc-placeholder">
                        <i class="ph-bold ph-camera" style="font-size: 24px; color: #9ca3af; display: block; margin-bottom: 6px;"></i>
                        <p style="font-size: 11px; font-weight: 600; color: #6b7280;">Ambil Foto / Pilih Dokumen</p>
                        <p style="font-size: 10px; color: #9ca3af; margin-top: 2px;">Format: JPG, PNG</p>
                    </div>
                    <img id="ocr-doc-preview" style="display:none; position:absolute; inset:0; width:100%; height:100%; object-fit:cover; opacity:0.35;" />
                    <div id="ocr-loading" style="display:none; position:absolute; inset:0; background:rgba(255,255,255,0.9); backdrop-filter:blur(4px); display:none; flex-direction:column; align-items:center; justify-content:center; gap:6px;">
                        <i class="ph-bold ph-spinner" style="font-size: 22px; color: #285491; animation: spin 1s linear infinite;"></i>
                        <p style="font-size: 10px; font-weight: 700; color: #285491; letter-spacing: 0.05em;">MEMBACA DOKUMEN...</p>
                    </div>
                </label>
                <style>@keyframes spin { to { transform: rotate(360deg); } } #ocr-loading { display: none; }</style>

                <!-- Extracted Fields -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                    <div>
                        <label class="field-label" style="display:flex; align-items:center; gap:4px;">
                            <i class="ph-bold ph-hash" style="color:#285491; font-size:10px;"></i>Shipment Group
                        </label>
                        <input type="text" id="shipment_group" name="shipment_group" placeholder="Auto / Manual" class="field-input" style="font-size:12px;">
                    </div>
                    <div>
                        <label class="field-label" style="display:flex; align-items:center; gap:4px;">
                            <i class="ph-bold ph-hash" style="color:#285491; font-size:10px;"></i>Shipment No.
                        </label>
                        <input type="text" id="shipment_no" name="shipment_no" placeholder="Auto / Manual" class="field-input" style="font-size:12px;">
                    </div>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                    <div>
                        <label class="field-label" style="display:flex; align-items:center; gap:4px;">
                            <i class="ph-bold ph-car" style="color:#285491; font-size:10px;"></i>Plat Nomor
                        </label>
                        <input type="text" id="plat_nomor" name="plat_nomor" placeholder="B 9619 UF2" class="field-input" style="font-size:12px; text-transform:uppercase;">
                    </div>
                    <div>
                        <label class="field-label" style="display:flex; align-items:center; gap:4px;">
                            <i class="ph-bold ph-user" style="color:#285491; font-size:10px;"></i>Nama Sopir
                        </label>
                        <input type="text" id="nama_sopir" name="nama_sopir" placeholder="Nama sopir" class="field-input" style="font-size:12px; text-transform:capitalize;">
                    </div>
                </div>
            </div>
        </div>

        <!-- SECTION 3: Petugas -->
        <div class="section-card" x-data="{ open: true }">
            <div class="section-card-header" @click="open = !open"
                 style="background: linear-gradient(90deg, rgba(13,89,80,0.04), transparent);">
                <div style="width: 28px; height: 28px; border-radius: 8px; background: #e8f5f3; display: flex; align-items: center; justify-content: center;">
                    <i class="ph-fill ph-user" style="font-size: 14px; color: #0d5950;"></i>
                </div>
                <div style="flex: 1;">
                    <p style="font-size: 12px; font-weight: 700; color: #1f2937;">Petugas Lapangan</p>
                    <p style="font-size: 10px; color: #9ca3af;">Pilih petugas yang bertugas</p>
                </div>
                <i class="ph-bold" :class="open ? 'ph-caret-up' : 'ph-caret-down'" style="font-size: 14px; color: #9ca3af;"></i>
            </div>
            <div class="section-card-body" x-show="open" x-collapse>
                <label class="field-label">Nama Petugas <span style="color:#ef4444;">*</span></label>
                <div style="position: relative;">
                    <i class="ph-bold ph-user" style="position:absolute; left:12px; top:50%; transform:translateY(-50%); font-size:13px; color:#9ca3af;"></i>
                    <select name="karyawan_id" required class="field-input" style="padding-left: 34px;">
                        <option value="">Pilih Petugas...</option>
                         @foreach($karyawans as $karyawan)
                            <option value="{{ $karyawan->id }}">{{ $karyawan->nama }} ({{ $karyawan->nomor_induk }})</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <!-- Tip Card (collapsible) -->
        <div x-data="{ open: false }" style="border-radius: 14px; border: 1px solid #e1f8eb; background: #f7fff9; overflow: hidden;">
            <div @click="open = !open" style="padding: 10px 14px; display: flex; align-items: center; gap: 8px; cursor: pointer;">
                <i class="ph-fill ph-lightbulb" style="font-size: 16px; color: #059e3d;"></i>
                <p style="font-size: 11px; font-weight: 600; color: #059e3d; flex: 1;">Tips OCR Terbaik</p>
                <i class="ph-bold" :class="open ? 'ph-caret-up' : 'ph-caret-down'" style="font-size: 12px; color: #059e3d;"></i>
            </div>
            <div x-show="open" x-collapse style="padding: 0 14px 12px; font-size: 11px; color: #374151; line-height: 1.6;">
                <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 4px;">
                    <li style="display:flex; gap:6px;"><span style="color:#059e3d;">✓</span> Pastikan dokumen rata & tidak kusut</li>
                    <li style="display:flex; gap:6px;"><span style="color:#059e3d;">✓</span> Hindari bayangan dan silau kamera</li>
                    <li style="display:flex; gap:6px;"><span style="color:#059e3d;">✓</span> Arahkan kamera tegak lurus dokumen</li>
                    <li style="display:flex; gap:6px;"><span style="color:#f59e0b;">!</span> Tulisan tangan (plat/sopir) mungkin perlu isi manual</li>
                </ul>
            </div>
        </div>

        <!-- Submit Button -->
        <button type="submit"
                style="width: 100%; background: linear-gradient(135deg, #285491, #0d5950); color: white; font-weight: 700; font-size: 14px; border: none; border-radius: 14px; padding: 14px; display: flex; align-items: center; justify-content: center; gap: 8px; cursor: pointer; box-shadow: 0 4px 16px rgba(40,84,145,0.35); transition: all 0.2s; margin-top: 4px;"
                onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 8px 24px rgba(40,84,145,0.45)'"
                onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 16px rgba(40,84,145,0.35)'">
            Lanjut ke Timeline Foto
            <i class="ph-bold ph-arrow-right" style="font-size: 16px;"></i>
        </button>

    </form>

    <script>
        function previewOcrDoc(input) {
            if (input.files && input.files[0]) {
                const preview = document.getElementById('ocr-doc-preview');
                const loading = document.getElementById('ocr-loading');
                const placeholder = document.getElementById('ocr-doc-placeholder');

                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                    placeholder.style.display = 'none';
                    loading.style.display = 'flex';

                    const formData = new FormData();
                    formData.append('image', input.files[0]);
                    formData.append('_token', '{{ csrf_token() }}');

                    fetch('{{ route('field-app.ocr.shipment-order', [], false) }}', {
                        method: 'POST',
                        body: formData
                    })
                    .then(r => {
                        if (!r.ok) throw new Error('HTTP ' + r.status);
                        return r.json();
                    })
                    .then(data => {
                        loading.style.display = 'none';
                        let found = 0;
                        if (data.shipment_group) { document.getElementById('shipment_group').value = data.shipment_group; found++; }
                        if (data.shipment_no) { document.getElementById('shipment_no').value = data.shipment_no; found++; }
                        if (data.plat_nomor && document.getElementById('plat_nomor')) { document.getElementById('plat_nomor').value = data.plat_nomor; found++; }
                        if (data.nama_sopir && document.getElementById('nama_sopir')) { document.getElementById('nama_sopir').value = data.nama_sopir; found++; }
                        
                        if (found > 0) {
                            alert('OCR Selesai! ' + found + ' data berhasil diekstrak.');
                        } else {
                            alert('Foto tersimpan! Teks dokumen tidak terdeteksi otomatis, silakan lengkapi manual.');
                        }
                    })
                    .catch(err => {
                        loading.style.display = 'none';
                        console.error('OCR Error:', err);
                        alert('Foto tersimpan. Silakan lengkapi data dokumen secara manual.');
                    });
                };
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>

</x-field-layout>
