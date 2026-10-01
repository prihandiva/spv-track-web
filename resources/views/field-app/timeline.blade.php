<x-field-layout>

    <!-- Shipment Header Card -->
    <div style="background: linear-gradient(135deg, #1a3c6e 0%, #285491 60%, #0d5950 100%); border-radius: 16px; padding: 16px; color: white; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 4px 16px rgba(40,84,145,0.3);">
        <div>
            <p style="font-size: 10px; font-weight: 600; color: rgba(255,255,255,0.55); text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 3px;">Shipment Aktif</p>
            <h1 style="font-size: 18px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.03em; line-height: 1.1;">{{ $shipment->nomor_container_atau_plat ?? 'SPV-' . $shipment->id }}</h1>
            <p style="font-size: 11px; color: rgba(255,255,255,0.65); margin-top: 3px; text-transform: capitalize;">{{ $shipment->jenis_produk }} &bull; {{ $shipment->jenis_pengiriman }}</p>
        </div>
        <div style="text-align:right;">
            <span style="display: inline-flex; align-items: center; gap: 5px; background: rgba(217,119,6,0.2); border: 1px solid rgba(217,119,6,0.4); color: #fbbf24; font-size: 10px; font-weight: 700; padding: 4px 10px; border-radius: 20px;">
                <span style="width: 6px; height: 6px; border-radius: 50%; background: #fbbf24; animation: pulse 1.5s infinite;"></span>
                {{ ucfirst($shipment->status) }}
            </span>
            <p style="font-size: 10px; color: rgba(255,255,255,0.45); margin-top: 6px;">{{ $shipment->tanggal_staging ? \Carbon\Carbon::parse($shipment->tanggal_staging)->format('d M Y') : '-' }}</p>
        </div>
    </div>

    <!-- Progress Info -->
    <div style="display: flex; gap: 8px;">
        <div style="flex: 1; background: white; border-radius: 12px; padding: 10px 14px; border: 1px solid #e8edf3; text-align: center;">
            <p style="font-size: 18px; font-weight: 800; color: #285491;">{{ count($points) }}</p>
            <p style="font-size: 10px; color: #9ca3af; font-weight: 500;">Titik Total</p>
        </div>
        <div style="flex: 1; background: white; border-radius: 12px; padding: 10px 14px; border: 1px solid #e8edf3; text-align: center;">
            <p style="font-size: 18px; font-weight: 800; color: #059e3d;" id="uploaded-count">{{ $shipment->evidenceItems->count() }}</p>
            <p style="font-size: 10px; color: #9ca3af; font-weight: 500;">Terupload</p>
        </div>
        <div style="flex: 1; background: white; border-radius: 12px; padding: 10px 14px; border: 1px solid #e8edf3; text-align: center;">
            <p style="font-size: 18px; font-weight: 800; color: #d97706;">{{ $points->where('wajib', true)->count() }}</p>
            <p style="font-size: 10px; color: #9ca3af; font-weight: 500;">Wajib</p>
        </div>
    </div>

    <!-- Timeline Points -->
    <div style="display: flex; flex-direction: column; gap: 8px; position: relative; padding-left: 4px;">
        <!-- Vertical line -->
        <div style="position: absolute; left: 20px; top: 20px; bottom: 20px; width: 2px; background: linear-gradient(to bottom, #e8edf3, #bfcfe8, #e8edf3); z-index: 0;"></div>

        @foreach($points as $point)
        @php
            $existing = $shipment->evidenceItems->firstWhere('sop_photo_point_id', $point->id);
        @endphp
        <div style="display: flex; gap: 12px; align-items: flex-start; position: relative; z-index: 1;">
            <!-- Number Badge -->
            <div style="width: 36px; height: 36px; border-radius: 50%; background: white; border: 2px solid {{ $existing ? '#059e3d' : '#e8edf3' }}; display: flex; align-items: center; justify-content: center; shrink: 0; box-shadow: 0 1px 4px rgba(40,84,145,0.1); flex-shrink: 0;">
                <span id="num-badge-{{$point->id}}" style="font-size: 11px; font-weight: 800; color: {{ $existing ? '#059e3d' : '#285491' }};">
                    {{ $existing ? '✓' : $point->urutan }}
                </span>
            </div>

            <!-- Card -->
            <div style="flex: 1; background: white; border-radius: 14px; border: 1px solid #e8edf3; box-shadow: 0 1px 4px rgba(40,84,145,0.06); overflow: hidden; transition: all 0.2s;"
                 onmouseover="this.style.boxShadow='0 4px 14px rgba(40,84,145,0.12)'; this.style.borderColor='#bfcfe8'"
                 onmouseout="this.style.boxShadow='0 1px 4px rgba(40,84,145,0.06)'; this.style.borderColor='#e8edf3'">

                <!-- Card Header -->
                <div style="padding: 10px 12px; display: flex; align-items: flex-start; justify-content: space-between; border-bottom: 1px solid #f0f4f9;">
                    <div>
                        <p style="font-size: 12px; font-weight: 700; color: #1f2937; line-height: 1.2;">{{ $point->nama_titik }}</p>
                        <p style="font-size: 10px; color: #9ca3af; margin-top: 2px;">{{ $point->deskripsi }}</p>
                    </div>
                    @if($point->wajib)
                        <span style="font-size: 9px; font-weight: 700; color: #ef4444; background: #fef2f2; border: 1px solid #fecaca; padding: 2px 7px; border-radius: 20px; white-space: nowrap; margin-left: 8px; flex-shrink: 0;">WAJIB</span>
                    @else
                        <span style="font-size: 9px; font-weight: 600; color: #9ca3af; background: #f9fafb; border: 1px solid #f3f4f6; padding: 2px 7px; border-radius: 20px; white-space: nowrap; margin-left: 8px; flex-shrink: 0;">Opsional</span>
                    @endif
                </div>

                <!-- Upload Area -->
                <div style="position: relative; border: 2px dashed #e5e7eb; border-radius: 10px; margin: 10px 12px 12px; background: #fafbfd; overflow: hidden; transition: border-color 0.2s;"
                     onmouseover="this.style.borderColor='#285491'" onmouseout="this.style.borderColor='#e5e7eb'">

                    <!-- Camera & Gallery Initial Buttons -->
                    <div id="icon-{{$point->id}}" style="display: {{ $existing ? 'none' : 'flex' }}; border-bottom: 1px solid #f0f4f9;">
                        <label style="flex: 1; padding: 12px 8px; display: flex; flex-direction: column; align-items: center; justify-content: center; cursor: pointer; transition: background 0.2s;"
                               onmouseover="this.style.background='#eff4fc'" onmouseout="this.style.background='transparent'">
                            <input type="file" style="display:none;" accept="image/*" capture="environment" onchange="uploadSopPhoto(this, {{ $point->id }})">
                            <i class="ph-bold ph-camera" style="font-size: 20px; color: #285491; margin-bottom: 3px; display: block;"></i>
                            <span style="font-size: 10px; font-weight: 700; color: #285491;">Buka Kamera</span>
                        </label>
                        <div style="width: 1px; background: #f0f4f9;"></div>
                        <label style="flex: 1; padding: 12px 8px; display: flex; flex-direction: column; align-items: center; justify-content: center; cursor: pointer; transition: background 0.2s;"
                               onmouseover="this.style.background='#eff4fc'" onmouseout="this.style.background='transparent'">
                            <input type="file" style="display:none;" accept="image/*" onchange="uploadSopPhoto(this, {{ $point->id }})">
                            <i class="ph-bold ph-image" style="font-size: 20px; color: #6b7280; margin-bottom: 3px; display: block;"></i>
                            <span style="font-size: 10px; font-weight: 600; color: #6b7280;">Pilih Galeri</span>
                        </label>
                    </div>

                    <!-- Photo Preview Container -->
                    <div id="preview-wrapper-{{$point->id}}" style="display: {{ $existing ? 'block' : 'none' }}; position: relative; height: 160px; background: #111827;">
                        <img id="preview-{{$point->id}}"
                             src="{{ $existing ? asset('storage/' . $existing->file_path) : '' }}"
                             style="width:100%; height:100%; object-fit:cover; opacity:0.9;" />

                        <!-- Replace / Retake Buttons Overlay -->
                        <div style="position:absolute; inset:0; display:flex; align-items:center; justify-content:center; gap:12px; background:rgba(0,0,0,0); transition:background 0.2s;"
                             onmouseover="this.style.background='rgba(0,0,0,0.5)'; this.querySelectorAll('label').forEach(el=>el.style.opacity='1')"
                             onmouseout="this.style.background='rgba(0,0,0,0)'; this.querySelectorAll('label').forEach(el=>el.style.opacity='0')">
                            <label style="width:38px; height:38px; border-radius:50%; background:rgba(255,255,255,0.25); backdrop-filter:blur(4px); display:flex; align-items:center; justify-content:center; cursor:pointer; opacity:0; transition:opacity 0.2s;"
                                   onmouseover="this.style.background='#285491'" onmouseout="this.style.background='rgba(255,255,255,0.25)'" title="Foto Ulang Kamera">
                                <input type="file" style="display:none;" accept="image/*" capture="environment" onchange="uploadSopPhoto(this, {{ $point->id }})">
                                <i class="ph-bold ph-camera" style="color:white; font-size:18px;"></i>
                            </label>
                            <label style="width:38px; height:38px; border-radius:50%; background:rgba(255,255,255,0.25); backdrop-filter:blur(4px); display:flex; align-items:center; justify-content:center; cursor:pointer; opacity:0; transition:opacity 0.2s;"
                                   onmouseover="this.style.background='#285491'" onmouseout="this.style.background='rgba(255,255,255,0.25)'" title="Ganti dari Galeri">
                                <input type="file" style="display:none;" accept="image/*" onchange="uploadSopPhoto(this, {{ $point->id }})">
                                <i class="ph-bold ph-image" style="color:white; font-size:18px;"></i>
                            </label>
                        </div>

                        <!-- Status Badge (Top Right) -->
                        <div id="status-{{$point->id}}" style="position:absolute; top:8px; right:8px; background:rgba(0,0,0,0.7); backdrop-filter:blur(4px); font-size:10px; font-weight:700; padding:3px 9px; border-radius:12px; z-index:10; border:1px solid rgba(255,255,255,0.15);">
                            <span style="color:#63c384;">✓ Tersimpan</span>
                        </div>

                        <!-- Live Timestamp Badge (Bottom Left - Year, Month, Day, Time) -->
                        <div id="timestamp-{{$point->id}}" style="position:absolute; bottom:8px; left:8px; right:8px; background:rgba(0,0,0,0.75); backdrop-filter:blur(4px); color:#63c384; font-size:10px; font-weight:700; padding:4px 8px; border-radius:6px; display:flex; align-items:center; gap:5px; z-index:10; font-family:monospace; border:1px solid rgba(255,255,255,0.15);">
                            <i class="ph-bold ph-calendar-check" style="font-size:12px; color:#63c384;"></i>
                            <span class="ts-text" style="white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                                {{ $existing && $existing->captured_at ? $existing->captured_at->format('Y-m-d H:i:s') . ' WIB' : '' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach

        <!-- Add Extra Photo -->
        <div style="display:flex; justify-content:center; padding: 8px 0 4px; position:relative; z-index:1;">
            <button type="button"
                    onclick="alert('Fitur foto ekstra dapat diunggah melalui formulir titik inspeksi di atas.')"
                    style="display:flex; align-items:center; gap:6px; background:white; border: 1px solid #e8edf3; color:#6b7280; font-size:11px; font-weight:600; padding:9px 18px; border-radius:20px; cursor:pointer; transition:all 0.2s; box-shadow: 0 1px 4px rgba(40,84,145,0.06);">
                <i class="ph-bold ph-plus"></i>
                Tambah Foto Ekstra
            </button>
        </div>
    </div>

    <!-- Surat Jalan Form -->
    <form action="{{ route('field-app.submit', $shipment->id, false) }}" method="POST" id="submitForm" style="margin-top: 10px;">
        @csrf

        <!-- OCR Scan Area (Collapsible) -->
        <div class="section-card" x-data="{ open: true }">
            <div class="section-card-header" @click="open = !open"
                 style="background: linear-gradient(90deg, rgba(5,158,61,0.04), transparent);">
                <div style="width: 28px; height: 28px; border-radius: 8px; background: #e1f8eb; display: flex; align-items: center; justify-content: center; flex-shrink:0;">
                    <i class="ph-bold ph-scan" style="font-size: 14px; color: #059e3d;"></i>
                </div>
                <div style="flex: 1;">
                    <p style="font-size: 12px; font-weight: 700; color: #1f2937;">Scan Surat Bersih (OCR)</p>
                    <p style="font-size: 10px; color: #9ca3af;">Foto sebelum ditumpuk alat scanner</p>
                </div>
                <i class="ph-bold" :class="open ? 'ph-caret-up' : 'ph-caret-down'" style="font-size: 14px; color: #9ca3af;"></i>
            </div>
            <div class="section-card-body" x-show="open" x-collapse style="display:flex; flex-direction:column; gap:10px;">
                <!-- Upload Area -->
                <label style="display:block; border: 2px dashed #bfcfe8; border-radius:12px; background:#fafcff; cursor:pointer; text-align:center; padding:16px; position:relative; overflow:hidden;"
                       onmouseover="this.style.borderColor='#285491'; this.style.background='#f5f8ff'"
                       onmouseout="this.style.borderColor='#bfcfe8'; this.style.background='#fafcff'">
                    <input type="file" style="display:none;" accept="image/*" capture="environment" onchange="previewOcrSuratJalan(this)">
                    <div id="ocr-sj-placeholder">
                        <i class="ph-bold ph-camera" style="font-size: 24px; color: #9ca3af; display: block; margin-bottom: 6px;"></i>
                        <p style="font-size: 11px; font-weight: 600; color: #6b7280;">Ambil Foto Surat Bersih</p>
                        <p style="font-size: 10px; color: #9ca3af; margin-top: 2px;">Format: JPG, PNG</p>
                    </div>
                    <img id="ocr-sj-preview" style="display:none; position:absolute; inset:0; width:100%; height:100%; object-fit:cover; opacity:0.35;" />
                    <div id="ocr-sj-loading" style="display:none; position:absolute; inset:0; background:rgba(255,255,255,0.9); backdrop-filter:blur(4px); flex-direction:column; align-items:center; justify-content:center; gap:6px;">
                        <i class="ph-bold ph-spinner" style="font-size: 22px; color: #285491; animation: spin 1s linear infinite;"></i>
                        <p style="font-size: 10px; font-weight: 700; color: #285491; letter-spacing: 0.05em;">MEMBACA DOKUMEN...</p>
                    </div>
                </label>

                <!-- Extracted Fields -->
                <div>
                    <label class="field-label">Packing List No.</label>
                    <input type="text" id="packing_list_no" name="packing_list_no" value="{{ old('packing_list_no', $shipment->packing_list_no) }}" placeholder="Contoh: 12345" class="field-input">
                </div>

                <div>
                    <label class="field-label">Tujuan Pengiriman</label>
                    <textarea id="tujuan_pengiriman" name="tujuan_pengiriman" rows="2" placeholder="Nama PT / Alamat lengkap" class="field-input">{{ old('tujuan_pengiriman', $shipment->tujuan_pengiriman) }}</textarea>
                </div>

                <div>
                    <label class="field-label">Agen Forwarding</label>
                    <input type="text" id="agen_forwarding" name="agen_forwarding" value="{{ old('agen_forwarding', $shipment->agen_forwarding) }}" placeholder="Contoh: PT. Buana Express" class="field-input">
                </div>
            </div>
        </div>

        <!-- Section 3: Waktu Staging Container -->
        <div class="section-card" x-data="{ open: true }" style="margin-top: 10px;">
            <div class="section-card-header" @click="open = !open">
                <div style="width: 28px; height: 28px; border-radius: 8px; background: #eff4fc; display: flex; align-items: center; justify-content: center; flex-shrink:0;">
                    <i class="ph-fill ph-clock" style="font-size: 14px; color: #285491;"></i>
                </div>
                <div style="flex: 1;">
                    <p style="font-size: 12px; font-weight: 700; color: #1f2937;">Waktu Staging Container</p>
                    <p style="font-size: 10px; color: #9ca3af;">Kedatangan & Keberangkatan</p>
                </div>
                <i class="ph-bold" :class="open ? 'ph-caret-up' : 'ph-caret-down'" style="font-size: 14px; color: #9ca3af;"></i>
            </div>
            <div class="section-card-body" x-show="open" x-collapse style="display:flex; flex-direction:column; gap:10px;">
                <div style="display:flex; flex-direction:column; gap:10px;">
                    <div style="width:100%;">
                        <label class="field-label">Waktu Kedatangan</label>
                        <input type="datetime-local" id="waktu_kedatangan_container" name="waktu_kedatangan_container"
                               value="{{ old('waktu_kedatangan_container', $shipment->waktu_kedatangan_container ? $shipment->waktu_kedatangan_container->format('Y-m-d\TH:i') : '') }}"
                               class="field-input" style="width:100%; box-sizing:border-box;">
                    </div>
                    <div style="width:100%;">
                        <label class="field-label">Waktu Keberangkatan</label>
                        <input type="datetime-local" id="waktu_keberangkatan_container" name="waktu_keberangkatan_container"
                               value="{{ old('waktu_keberangkatan_container', $shipment->waktu_keberangkatan_container ? $shipment->waktu_keberangkatan_container->format('Y-m-d\TH:i') : '') }}"
                               class="field-input" style="width:100%; box-sizing:border-box;">
                    </div>
                </div>
            </div>
        </div>

        <!-- Sticky Footer Submit Button -->
        <div style="position:fixed; bottom:0; left:0; right:0; background:white; border-top:1px solid #e8edf3; padding:12px 16px; z-index:100; box-shadow: 0 -4px 20px rgba(40,84,145,0.08);">
            <div style="max-width:720px; margin:0 auto; display:flex; gap:10px;">
                <button type="submit" style="flex:1; background:linear-gradient(135deg, #059e3d, #0d5950); color:white; font-size:14px; font-weight:700; border:none; border-radius:12px; padding:13px; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:8px; box-shadow: 0 4px 14px rgba(5,158,61,0.35); transition:all 0.2s;"
                        onmouseover="this.style.transform='translateY(-1px)'; this.style.boxShadow='0 6px 18px rgba(5,158,61,0.45)'"
                        onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 14px rgba(5,158,61,0.35)'">
                    <i class="ph-bold ph-check-circle" style="font-size:18px;"></i>
                    Submit Final Data
                </button>
            </div>
        </div>

    </form>

    <script>
        // Upload photo SOP with live preview and timestamp
        function uploadSopPhoto(input, pointId) {
            if (input.files && input.files[0]) {
                const file = input.files[0];
                const preview = document.getElementById('preview-' + pointId);
                const wrapper = document.getElementById('preview-wrapper-' + pointId);
                const iconBox = document.getElementById('icon-' + pointId);
                const statusBox = document.getElementById('status-' + pointId);
                const tsBox = document.getElementById('timestamp-' + pointId);
                const tsText = tsBox ? tsBox.querySelector('.ts-text') : null;
                const numBadge = document.getElementById('num-badge-' + pointId);

                // 1. Instant local preview
                const blobUrl = URL.createObjectURL(file);
                preview.src = blobUrl;
                wrapper.style.display = 'block';
                if (iconBox) iconBox.style.display = 'none';

                // 2. Format Timestamp tahun-bulan-hari jam:menit:detik
                const now = new Date();
                const y = now.getFullYear();
                const m = String(now.getMonth() + 1).padStart(2, '0');
                const d = String(now.getDate()).padStart(2, '0');
                const hh = String(now.getHours()).padStart(2, '0');
                const mm = String(now.getMinutes()).padStart(2, '0');
                const ss = String(now.getSeconds()).padStart(2, '0');
                const formattedTimestamp = `${y}-${m}-${d} ${hh}:${mm}:${ss}`;

                if (tsText) {
                    tsText.textContent = `${formattedTimestamp} WIB`;
                }

                // 3. Status saving
                if (statusBox) {
                    statusBox.innerHTML = '<span style="color:#fbbf24;"><i class="ph-bold ph-spinner animate-spin"></i> Menyimpan...</span>';
                }

                // 4. Send FormData to backend
                const fd = new FormData();
                fd.append('file', file);
                fd.append('sop_photo_point_id', pointId);
                fd.append('captured_at', formattedTimestamp);
                fd.append('_token', '{{ csrf_token() }}');

                fetch('{{ route('field-app.upload-photo', $shipment->id, false) }}', {
                    method: 'POST',
                    body: fd
                })
                .then(r => {
                    if (!r.ok) throw new Error('HTTP ' + r.status);
                    return r.json();
                })
                .then(data => {
                    if (data.success) {
                        if (statusBox) {
                            statusBox.innerHTML = '<span style="color:#63c384;">✓ Tersimpan</span>';
                        }
                        if (data.captured_at && tsText) {
                            tsText.textContent = data.captured_at;
                        }
                        if (numBadge) {
                            numBadge.textContent = '✓';
                            numBadge.style.color = '#059e3d';
                        }
                        const countEl = document.getElementById('uploaded-count');
                        if (countEl && data.uploaded_count !== undefined) {
                            countEl.textContent = data.uploaded_count;
                        }
                    } else {
                        throw new Error(data.message || 'Gagal');
                    }
                })
                .catch(err => {
                    console.error('Upload error:', err);
                    if (statusBox) {
                        statusBox.innerHTML = '<span style="color:#f87171;">Offline (Lokal)</span>';
                    }
                });
            }
        }

        // OCR Surat Jalan
        function previewOcrSuratJalan(input) {
            if (input.files && input.files[0]) {
                const preview = document.getElementById('ocr-sj-preview');
                const loading = document.getElementById('ocr-sj-loading');
                const placeholder = document.getElementById('ocr-sj-placeholder');

                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                    placeholder.style.display = 'none';
                    loading.style.display = 'flex';

                    const formData = new FormData();
                    formData.append('image', input.files[0]);
                    formData.append('_token', '{{ csrf_token() }}');

                    fetch('{{ route('field-app.ocr.surat-jalan', [], false) }}', {
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
                        if (data.packing_list_no) { document.getElementById('packing_list_no').value = data.packing_list_no; found++; }
                        if (data.tujuan_pengiriman) { document.getElementById('tujuan_pengiriman').value = data.tujuan_pengiriman; found++; }
                        if (data.agen_forwarding) { document.getElementById('agen_forwarding').value = data.agen_forwarding; found++; }
                        
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
