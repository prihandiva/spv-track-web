<x-field-layout>

    <!-- Page Header -->
    <div style="padding: 4px 2px 8px;">
        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 4px;">
            <div style="width: 36px; height: 36px; border-radius: 12px; background: linear-gradient(135deg, #285491, #0d5950); display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(40,84,145,0.25);">
                <i class="ph-bold ph-clipboard-text" style="font-size: 18px; color: white;"></i>
            </div>
            <div>
                <h1 style="font-size: 16px; font-weight: 700; color: #1f2937; line-height: 1.2;">Mulai Staging</h1>
                <p style="font-size: 11px; color: #64748b;">Isi data awal dan verifikasi dokumen sebelum unggah bukti foto SOP</p>
            </div>
        </div>
    </div>

    <form action="{{ route('field-app.store', [], false) }}" method="POST" enctype="multipart/form-data"
          x-data="{
              nomorContainer: '{{ old('nomor_container_atau_plat', '') }}',
              shipmentGroup: '{{ old('shipment_group', '') }}',
              shipmentNo: '{{ old('shipment_no', '') }}',
              platNomor: '{{ old('plat_nomor', '') }}',
              namaSopir: '{{ old('nama_sopir', '') }}',
              karyawanId: '{{ old('karyawan_id', '') }}',
              packingListNo: '{{ old('packing_list_no', '') }}',
              dropdownOpen: false,
              searchKaryawan: '',
              photoPreviewUrl: '',
              isOcrLoading: false,
              ocrStatus: 'idle', // idle, loading, success, warning, error
              ocrMessage: '',
              ocrExtractedSummary: '',
              ocrRawText: '',
              showRawText: false,
              zoomModalOpen: false,
              karyawans: {{ Js::from($karyawans->map(fn($k) => ['id' => $k->id, 'nama' => $k->nama, 'nomor_induk' => $k->nomor_induk])) }},
              get selectedKaryawan() {
                  return this.karyawans.find(k => String(k.id) === String(this.karyawanId)) || null;
              },
              get selectedKaryawanName() {
                  const found = this.selectedKaryawan;
                  return found ? found.nama + ' (' + found.nomor_induk + ')' : '';
              },
              get filteredKaryawans() {
                  if (!this.searchKaryawan.trim()) return this.karyawans;
                  const q = this.searchKaryawan.toLowerCase();
                  return this.karyawans.filter(k => k.nama.toLowerCase().includes(q) || k.nomor_induk.toLowerCase().includes(q));
              },
              get isValid() {
                  return this.packingListNo.trim().length > 0 &&
                         this.nomorContainer.trim().length > 0 &&
                         this.shipmentGroup.trim().length > 0 &&
                         this.shipmentNo.trim().length > 0 &&
                         this.platNomor.trim().length > 0 &&
                         this.namaSopir.trim().length > 0 &&
                         this.karyawanId !== '';
              },
              get missingFields() {
                  const missing = [];
                  if (!this.packingListNo.trim()) missing.push('No. Packing List / Delivery');
                  if (!this.nomorContainer.trim()) missing.push('No. Container');
                  if (!this.shipmentGroup.trim()) missing.push('Shipment Group');
                  if (!this.shipmentNo.trim()) missing.push('Shipment No');
                  if (!this.platNomor.trim()) missing.push('Plat Nomor');
                  if (!this.namaSopir.trim()) missing.push('Nama Sopir');
                  if (!this.karyawanId) missing.push('Petugas Lapangan');
                  return missing;
              },
              handlePhotoChange(event) {
                  const input = event.target;
                  if (!input.files || !input.files[0]) return;
                  const file = input.files[0];

                  // Instant high-performance local preview URL
                  if (this.photoPreviewUrl && this.photoPreviewUrl.startsWith('blob:')) {
                      URL.revokeObjectURL(this.photoPreviewUrl);
                  }
                  this.photoPreviewUrl = URL.createObjectURL(file);
                  this.isOcrLoading = true;
                  this.ocrStatus = 'loading';
                  this.ocrMessage = 'Membaca dokumen Shipment Order dengan OCR (Tesseract)...';

                  // Fast client-side scaling to 1600px max before upload (reduces 12MB photo to ~350KB in milliseconds)
                  this.scaleImageForUpload(file, 1600, 0.85).then(optimizedBlob => {
                      const formData = new FormData();
                      formData.append('image', optimizedBlob, 'shipment_order.jpg');
                      formData.append('_token', '{{ csrf_token() }}');

                      fetch('{{ route('field-app.ocr.shipment-order', [], false) }}', {
                          method: 'POST',
                          headers: {
                              'Accept': 'application/json',
                              'X-Requested-With': 'XMLHttpRequest',
                              'ngrok-skip-browser-warning': 'true'
                          },
                          body: formData
                      })
                      .then(res => {
                          if (!res.ok) throw new Error('HTTP ' + res.status);
                          return res.json();
                      })
                      .then(data => {
                          this.isOcrLoading = false;
                          this.ocrRawText = data.raw_text || '';

                          let extractedItems = [];
                          if (data.packing_list_no || data.delivery_no) {
                              const plVal = data.packing_list_no || data.delivery_no;
                              this.packingListNo = plVal;
                              extractedItems.push('Packing List / Delivery: ' + plVal);
                          }
                          if (data.shipment_group) {
                              this.shipmentGroup = data.shipment_group;
                              extractedItems.push('Shipment Group: ' + data.shipment_group);
                          }
                          if (data.shipment_no) {
                              this.shipmentNo = data.shipment_no;
                              extractedItems.push('Shipment No: ' + data.shipment_no);
                          }
                          if (data.plat_nomor) {
                              this.platNomor = data.plat_nomor;
                              extractedItems.push('Plat: ' + data.plat_nomor);
                          }
                          if (data.nama_sopir) {
                              this.namaSopir = data.nama_sopir;
                              extractedItems.push('Sopir: ' + data.nama_sopir);
                          }
                          if (data.container_no && !this.nomorContainer) {
                              this.nomorContainer = data.container_no;
                              extractedItems.push('Container: ' + data.container_no);
                          }

                          if (extractedItems.length > 0) {
                              this.ocrStatus = 'success';
                              this.ocrMessage = 'OCR Berhasil! ' + extractedItems.length + ' data berhasil diekstrak otomatis.';
                              this.ocrExtractedSummary = extractedItems.join(' • ');
                          } else {
                              this.ocrStatus = 'warning';
                              this.ocrMessage = 'Dokumen terunggah. Teks nomor shipment tidak terdeteksi otomatis, silakan isi manual atau pilih rekomendasi.';
                          }
                      })
                      .catch(err => {
                          this.isOcrLoading = false;
                          this.ocrStatus = 'warning';
                          this.ocrMessage = 'Foto tersimpan. Sistem tidak dapat membaca teks otomatis, silakan lengkapi form secara manual.';
                          console.warn('OCR error:', err);
                      });
                  });
              },
              scaleImageForUpload(file, maxDimension, quality) {
                  return new Promise((resolve) => {
                      const img = new Image();
                      img.onload = () => {
                          let width = img.width;
                          let height = img.height;
                          if (width > maxDimension || height > maxDimension) {
                              if (width > height) {
                                  height = Math.round((height * maxDimension) / width);
                                  width = maxDimension;
                              } else {
                                  width = Math.round((width * maxDimension) / height);
                                  height = maxDimension;
                              }
                          }
                          const canvas = document.createElement('canvas');
                          canvas.width = width;
                          canvas.height = height;
                          const ctx = canvas.getContext('2d');
                          ctx.drawImage(img, 0, 0, width, height);
                          canvas.toBlob((blob) => {
                              resolve(blob || file);
                          }, 'image/jpeg', quality);
                      };
                      img.onerror = () => resolve(file);
                      img.src = URL.createObjectURL(file);
                  });
              },
              removePhoto() {
                  if (this.photoPreviewUrl && this.photoPreviewUrl.startsWith('blob:')) {
                      URL.revokeObjectURL(this.photoPreviewUrl);
                  }
                  this.photoPreviewUrl = '';
                  this.ocrStatus = 'idle';
                  this.ocrMessage = '';
                  this.ocrExtractedSummary = '';
                  this.ocrRawText = '';
                  if (this.$refs.orderCameraInput) {
                      this.$refs.orderCameraInput.value = '';
                  }
                  if (this.$refs.orderGalleryInput) {
                      this.$refs.orderGalleryInput.value = '';
                  }
              }
          }"

          style="display: flex; flex-direction: column; gap: 12px;">
        @csrf

        <input type="hidden" name="ocr_raw_text" :value="ocrRawText">

        <!-- SECTION 1: Data Pengiriman -->
        <div class="section-card" x-data="{ open: true }">
            <div class="section-card-header" @click="open = !open"
                 style="background: linear-gradient(90deg, rgba(40,84,145,0.04), transparent);">
                <div style="width: 30px; height: 30px; border-radius: 8px; background: #eff4fc; display: flex; align-items: center; justify-content: center; shrink: 0;">
                    <i class="ph-fill ph-truck" style="font-size: 16px; color: #285491;"></i>
                </div>
                <div style="flex: 1;">
                    <p style="font-size: 13px; font-weight: 700; color: #1f2937;">1. Data Pengiriman</p>
                    <p style="font-size: 10px; color: #64748b;">Produk, jenis pengiriman, waktu & container</p>
                </div>
                <i class="ph-bold" :class="open ? 'ph-caret-up' : 'ph-caret-down'" style="font-size: 14px; color: #9ca3af;"></i>
            </div>
            <div class="section-card-body" x-show="open" x-collapse style="display: flex; flex-direction: column; gap: 12px;">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
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
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
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
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div>
                        <label class="field-label">Tanggal Staging</label>
                        <input type="date" name="tanggal_staging" required value="{{ date('Y-m-d') }}" class="field-input">
                    </div>
                    <div>
                        <label class="field-label">Lokasi Warehouse</label>
                        <select name="warehouse_lokasi" class="field-input">
                            <option value="atas">Atas</option>
                            <option value="tengah">Tengah</option>
                            <option value="bawah">Bawah</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="field-label">No. Container <span style="color:#ef4444;">*</span></label>
                    <input type="text" id="nomor_container_atau_plat" name="nomor_container_atau_plat"
                           x-model="nomorContainer"
                           list="history-container"
                           placeholder="Contoh: MSKU1234567 atau Plat Truk" required class="field-input" style="text-transform:uppercase; font-weight: 600;">
                    <datalist id="history-container">
                        @foreach($history['nomor_container'] ?? [] as $item)
                            <option value="{{ $item }}">
                        @endforeach
                    </datalist>
                    @if(count($history['nomor_container'] ?? []) > 0)
                        <div style="display:flex; flex-wrap:wrap; gap:5px; margin-top:6px;">
                            <span style="font-size:10px; color:#9ca3af; align-self:center;">Rekomendasi:</span>
                            @foreach(($history['nomor_container'] ?? collect())->take(3) as $rec)
                                <button type="button" @click="nomorContainer = '{{ addslashes($rec) }}'" style="font-size:10px; font-weight: 600; padding:2px 8px; border-radius:10px; background:#eff4fc; color:#285491; border:1px solid #d0e0f5; cursor:pointer;">
                                    {{ $rec }}
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- SECTION 2: Scan Shipment Order (OCR) & Preview -->
        <div class="section-card" x-data="{ open: true }">
            <div class="section-card-header" @click="open = !open"
                 style="background: linear-gradient(90deg, rgba(5,158,61,0.04), transparent);">
                <div style="width: 30px; height: 30px; border-radius: 8px; background: #e1f8eb; display: flex; align-items: center; justify-content: center;">
                    <i class="ph-bold ph-scan" style="font-size: 16px; color: #059e3d;"></i>
                </div>
                <div style="flex: 1;">
                    <p style="font-size: 13px; font-weight: 700; color: #1f2937;">2. Dokumen Shipment Order <span style="font-size: 10px; font-weight: 600; color: #059e3d; background: #e1f8eb; padding: 2px 7px; border-radius: 6px; margin-left: 4px;">OCR Auto-Fill</span></p>
                    <p style="font-size: 10px; color: #64748b;">Foto dokumen untuk auto-fill nomor shipment otomatis</p>
                </div>
                <i class="ph-bold" :class="open ? 'ph-caret-up' : 'ph-caret-down'" style="font-size: 14px; color: #9ca3af;"></i>
            </div>

            <div class="section-card-body" x-show="open" x-collapse style="display: flex; flex-direction: column; gap: 14px;">

                <!-- Hidden file inputs: 1 for Direct Camera, 1 for Gallery/File Manager -->
                <input type="file" name="shipment_order_photo" x-ref="orderCameraInput" accept="image/*" capture="environment" style="display: none;" @change="handlePhotoChange($event)">
                <input type="file" name="shipment_order_photo_gallery" x-ref="orderGalleryInput" accept="image/*" style="display: none;" @change="handlePhotoChange($event)">

                <!-- MODE A: Dropzone Saat Belum Ada Foto (Pilihan Langsung Kamera vs Galeri) -->
                <div x-show="!photoPreviewUrl"
                     style="border: 2px dashed #93c5fd; border-radius: 14px; background: #f8fafc; padding: 20px 14px; text-align: center;">
                    <div style="width: 44px; height: 44px; border-radius: 12px; background: #eff4fc; color: #285491; display: flex; align-items: center; justify-content: center; margin: 0 auto 8px; box-shadow: 0 2px 8px rgba(40,84,145,0.08);">
                        <i class="ph-bold ph-scan" style="font-size: 24px;"></i>
                    </div>
                    <p style="font-size: 13px; font-weight: 700; color: #1e293b;">Foto Dokumen Shipment Order</p>
                    <p style="font-size: 11px; color: #64748b; margin-top: 2px;">
                        Pilih foto langsung lewat kamera HP atau ambil dari galeri berkas
                    </p>

                    <!-- Pilihan Tombol Kamera vs Galeri -->
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-top: 14px; max-width: 420px; margin-left: auto; margin-right: auto;">
                        <!-- Tombol Buka Kamera Langsung -->
                        <button type="button" @click="$refs.orderCameraInput.click()"
                                style="display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 6px; padding: 14px 10px; background: linear-gradient(135deg, #059e3d, #0d5950); color: white; border-radius: 12px; border: none; cursor: pointer; box-shadow: 0 4px 12px rgba(5,158,61,0.25); transition: transform 0.15s;"
                                onmouseover="this.style.transform='scale(1.02)'"
                                onmouseout="this.style.transform='scale(1)'">
                            <i class="ph-bold ph-camera" style="font-size: 24px;"></i>
                            <span style="font-size: 12px; font-weight: 700;">Buka Kamera</span>
                            <span style="font-size: 9px; opacity: 0.9;">Ambil foto langsung</span>
                        </button>

                        <!-- Tombol Galeri / File -->
                        <button type="button" @click="$refs.orderGalleryInput.click()"
                                style="display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 6px; padding: 14px 10px; background: #eff4fc; color: #285491; border: 1.5px solid #bfcfe8; border-radius: 12px; cursor: pointer; transition: all 0.15s;"
                                onmouseover="this.style.borderColor='#285491'; this.style.background='#e0edff'"
                                onmouseout="this.style.borderColor='#bfcfe8'; this.style.background='#eff4fc'">
                            <i class="ph-bold ph-folder-open" style="font-size: 24px;"></i>
                            <span style="font-size: 12px; font-weight: 700;">Pilih Galeri</span>
                            <span style="font-size: 9px; color: #64748b;">Ambil dari berkas</span>
                        </button>
                    </div>
                </div>

                <!-- MODE B: Preview Foto Jelas & Terbaca Saat Sudah Ada Foto -->
                <div x-show="photoPreviewUrl" style="display: flex; flex-direction: column; gap: 10px;">
                    <!-- Preview Image Frame -->
                    <div style="background: #0f172a; border-radius: 14px; border: 1px solid #1e293b; padding: 8px; position: relative; overflow: hidden; box-shadow: 0 4px 16px rgba(0,0,0,0.15);">
                        <!-- Action Bar Top -->
                        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px; padding: 2px 4px; flex-wrap: wrap; gap: 6px;">
                            <span style="display: inline-flex; align-items: center; gap: 5px; background: rgba(5,158,61,0.9); color: white; padding: 4px 10px; border-radius: 20px; font-size: 10px; font-weight: 700;">
                                <i class="ph-bold ph-check"></i> Foto Dokumen Siap
                            </span>
                            <div style="display: flex; align-items: center; gap: 5px; flex-wrap: wrap;">
                                <button type="button" @click="zoomModalOpen = true"
                                        style="background: rgba(255,255,255,0.2); color: white; border: none; border-radius: 8px; padding: 5px 8px; font-size: 10px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 3px;"
                                        title="Perbesar">
                                    <i class="ph-bold ph-magnifying-glass-plus"></i> Zoom
                                </button>
                                <button type="button" @click="$refs.orderCameraInput.click()"
                                        style="background: rgba(5,158,61,0.85); color: white; border: none; border-radius: 8px; padding: 5px 8px; font-size: 10px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 3px;"
                                        title="Foto Ulang via Kamera">
                                    <i class="ph-bold ph-camera"></i> Kamera
                                </button>
                                <button type="button" @click="$refs.orderGalleryInput.click()"
                                        style="background: rgba(255,255,255,0.2); color: white; border: none; border-radius: 8px; padding: 5px 8px; font-size: 10px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 3px;"
                                        title="Ganti dari Galeri">
                                    <i class="ph-bold ph-folder-open"></i> Galeri
                                </button>
                                <button type="button" @click="removePhoto()"
                                        style="background: rgba(239,68,68,0.35); color: #fca5a5; border: none; border-radius: 8px; padding: 5px 7px; font-size: 10px; font-weight: 600; cursor: pointer;"
                                        title="Hapus Foto">
                                    <i class="ph-bold ph-trash"></i>
                                </button>
                            </div>
                        </div>


                        <!-- Real Image with Crisp Contain Scaling -->
                        <div style="width: 100%; height: 220px; display: flex; align-items: center; justify-content: center; background: #020617; border-radius: 8px; overflow: hidden; cursor: pointer;"
                             @click="zoomModalOpen = true">
                            <img :src="photoPreviewUrl" alt="Dokumen Shipment Order"
                                 style="max-height: 100%; max-width: 100%; object-fit: contain; display: block; border-radius: 4px;">
                        </div>
                    </div>

                    <!-- OCR Progress / Feedback Bar -->
                    <div x-show="isOcrLoading"
                         style="background: #eff4fc; border: 1px solid #bfcfe8; border-radius: 12px; padding: 12px 14px; display: flex; align-items: center; gap: 10px;">
                        <i class="ph-bold ph-spinner" style="font-size: 20px; color: #285491; animation: spin 1s linear infinite; flex-shrink: 0;"></i>
                        <div>
                            <p style="font-size: 11px; font-weight: 700; color: #285491;">Sedang Menganalisis Dokumen dengan OCR (Tesseract)...</p>
                            <p style="font-size: 10px; color: #64748b; margin-top: 1px;">Mengekstrak nomor Shipment Group, Shipment No, plat, & sopir secara presisi.</p>
                        </div>
                    </div>

                    <!-- OCR Success Alert -->
                    <div x-show="ocrStatus === 'success' && !isOcrLoading"
                         style="background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 12px; padding: 12px 14px;">
                        <div style="display: flex; align-items: flex-start; gap: 8px;">
                            <i class="ph-fill ph-check-circle" style="color: #059e3d; font-size: 18px; margin-top: 1px; flex-shrink: 0;"></i>
                            <div style="flex: 1;">
                                <p style="font-size: 11px; font-weight: 700; color: #065f46;" x-text="ocrMessage"></p>
                                <p style="font-size: 10px; color: #047857; margin-top: 2px; font-weight: 600;" x-text="ocrExtractedSummary"></p>
                                <p style="font-size: 10px; color: #4b5563; margin-top: 4px;">
                                    Catatan: Plat Nomor & Nama Sopir biasanya berupa tulisan tangan/surat jalan terpisah. Silakan pilih dari rekomendasi atau lengkapi manual.
                                </p>
                                <div style="margin-top: 6px;">
                                    <button type="button" @click="showRawText = !showRawText" style="font-size: 10px; color: #047857; text-decoration: underline; background: none; border: none; padding: 0; cursor: pointer; font-weight: 600;">
                                        <span x-text="showRawText ? 'Sembunyikan Teks Mentah OCR' : 'Lihat Hasil Bacaan Teks Dokumen (OCR)'"></span>
                                    </button>
                                    <div x-show="showRawText" x-collapse style="margin-top: 6px; padding: 8px; background: white; border: 1px solid #d1fae5; border-radius: 8px; max-height: 120px; overflow-y: auto; font-family: monospace; font-size: 10px; color: #374151; white-space: pre-wrap;" x-text="ocrRawText"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- OCR Warning Alert (Partial / Unread) -->
                    <div x-show="ocrStatus === 'warning' && !isOcrLoading"
                         style="background: #fffbeb; border: 1px solid #fde68a; border-radius: 12px; padding: 12px 14px;">
                        <div style="display: flex; align-items: flex-start; gap: 8px;">
                            <i class="ph-fill ph-info" style="color: #d97706; font-size: 18px; margin-top: 1px; flex-shrink: 0;"></i>
                            <div style="flex: 1;">
                                <p style="font-size: 11px; font-weight: 700; color: #92400e;" x-text="ocrMessage"></p>
                                <p style="font-size: 10px; color: #78350f; margin-top: 2px;">
                                    Anda dapat langsung mengisi formulir secara manual atau memilih rekomendasi riwayat di bawah.
                                </p>
                                <template x-if="ocrRawText">
                                    <div style="margin-top: 6px;">
                                        <button type="button" @click="showRawText = !showRawText" style="font-size: 10px; color: #b45309; text-decoration: underline; background: none; border: none; padding: 0; cursor: pointer;">
                                            <span x-text="showRawText ? 'Tutup Teks' : 'Lihat Teks yang Terbaca Tesseract'"></span>
                                        </button>
                                        <div x-show="showRawText" x-collapse style="margin-top: 6px; padding: 8px; background: white; border: 1px solid #fef3c7; border-radius: 8px; max-height: 120px; overflow-y: auto; font-family: monospace; font-size: 10px; color: #374151; white-space: pre-wrap;" x-text="ocrRawText"></div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Extracted Fields with History Recommendations -->
                <!-- Nomor Packing List / Delivery (Identitas Utama Pengiriman) -->
                <div style="background: #eff4fc; border: 1.5px solid #bfcfe8; border-radius: 12px; padding: 12px; margin-bottom: 12px;">
                    <label class="field-label" style="display:flex; align-items:center; justify-content:space-between; margin-bottom:4px;">
                        <span style="display:flex; align-items:center; gap:5px; font-weight:700; color:#1e3a8a; font-size:12px;">
                            <i class="ph-bold ph-barcode" style="color:#285491; font-size:15px;"></i>Nomor Packing List / No. Surat Jalan (Kolom Delivery) <span style="color:#ef4444;">*</span>
                        </span>
                        <span style="font-size:10px; font-weight:700; color:#285491; background:white; padding:2px 8px; border-radius:8px; border:1px solid #bfcfe8;">Nomor Unik Staging</span>
                    </label>
                    <input type="text" id="packing_list_no" name="packing_list_no" x-model="packingListNo"
                           list="history-packing-list"
                           required placeholder="Contoh: 800123456 (tertera pada kolom 'Delivery' Shipment Order)"
                           class="field-input" style="font-size:13px; font-weight:700; background:white; letter-spacing:0.02em;">
                    <datalist id="history-packing-list">
                        @foreach($history['packing_list_no'] ?? [] as $item)
                            <option value="{{ $item }}">
                        @endforeach
                    </datalist>
                    @if(count($history['packing_list_no'] ?? []) > 0)
                        <div style="display:flex; flex-wrap:wrap; gap:4px; margin-top:5px;">
                            <span style="font-size:9px; color:#9ca3af; align-self:center;">Rekomendasi:</span>
                            @foreach(($history['packing_list_no'] ?? collect())->take(3) as $rec)
                                <button type="button" @click="packingListNo = '{{ addslashes($rec) }}'" style="font-size:10px; font-weight: 600; padding:2px 7px; border-radius:10px; background:#fff; color:#285491; border:1px solid #bfcfe8; cursor:pointer;">
                                    {{ $rec }}
                                </button>
                            @endforeach
                        </div>
                    @endif
                    <p style="font-size:10px; color:#64748b; margin-top:4px;">
                        💡 Angka nomor packing list diambil langsung dari kolom <strong>"Delivery"</strong> pada tabel dokumen Shipment Order.
                    </p>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div>
                        <label class="field-label" style="display:flex; align-items:center; gap:4px;">
                            <i class="ph-bold ph-hash" style="color:#285491; font-size:11px;"></i>Shipment Group <span style="color:#ef4444;">*</span>
                        </label>
                        <input type="text" id="shipment_group" name="shipment_group" x-model="shipmentGroup"
                               list="history-shipment-group"
                               required placeholder="Contoh: 869026487" class="field-input" style="font-size:12px; font-weight: 600;">
                        <datalist id="history-shipment-group">
                            @foreach($history['shipment_group'] ?? [] as $item)
                                <option value="{{ $item }}">
                            @endforeach
                        </datalist>
                        @if(count($history['shipment_group'] ?? []) > 0)
                            <div style="display:flex; flex-wrap:wrap; gap:4px; margin-top:5px;">
                                <span style="font-size:9px; color:#9ca3af; align-self:center;">Rekomendasi:</span>
                                @foreach(($history['shipment_group'] ?? collect())->take(2) as $rec)
                                    <button type="button" @click="shipmentGroup = '{{ addslashes($rec) }}'" style="font-size:10px; font-weight: 600; padding:2px 7px; border-radius:10px; background:#eff4fc; color:#285491; border:1px solid #d0e0f5; cursor:pointer;">
                                        {{ $rec }}
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    </div>
                    <div>
                        <label class="field-label" style="display:flex; align-items:center; gap:4px;">
                            <i class="ph-bold ph-hash" style="color:#285491; font-size:11px;"></i>Shipment No. <span style="color:#ef4444;">*</span>
                        </label>
                        <input type="text" id="shipment_no" name="shipment_no" x-model="shipmentNo" required placeholder="Contoh: 860110918" class="field-input" style="font-size:12px; font-weight: 600;">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div>
                        <label class="field-label" style="display:flex; align-items:center; gap:4px;">
                            <i class="ph-bold ph-car" style="color:#285491; font-size:11px;"></i>Plat Nomor <span style="color:#ef4444;">*</span>
                        </label>
                        <input type="text" id="plat_nomor" name="plat_nomor" x-model="platNomor"
                               list="history-plat"
                               required placeholder="Contoh: B 9588 UIY" class="field-input" style="font-size:12px; font-weight: 600; text-transform:uppercase;">
                        <datalist id="history-plat">
                            @foreach($history['plat_nomor'] ?? [] as $item)
                                <option value="{{ $item }}">
                            @endforeach
                        </datalist>
                        @if(count($history['plat_nomor'] ?? []) > 0)
                            <div style="display:flex; flex-wrap:wrap; gap:4px; margin-top:5px;">
                                <span style="font-size:9px; color:#9ca3af; align-self:center;">Rekomendasi:</span>
                                @foreach(($history['plat_nomor'] ?? collect())->take(3) as $rec)
                                    <button type="button" @click="platNomor = '{{ addslashes($rec) }}'" style="font-size:10px; font-weight: 600; padding:2px 7px; border-radius:10px; background:#eff4fc; color:#285491; border:1px solid #d0e0f5; cursor:pointer;">
                                        {{ $rec }}
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    </div>
                    <div>
                        <label class="field-label" style="display:flex; align-items:center; gap:4px;">
                            <i class="ph-bold ph-user" style="color:#285491; font-size:11px;"></i>Nama Sopir <span style="color:#ef4444;">*</span>
                        </label>
                        <input type="text" id="nama_sopir" name="nama_sopir" x-model="namaSopir"
                               list="history-sopir"
                               required placeholder="Nama sopir" class="field-input" style="font-size:12px; font-weight: 600; text-transform:capitalize;">
                        <datalist id="history-sopir">
                            @foreach($history['nama_sopir'] ?? [] as $item)
                                <option value="{{ $item }}">
                            @endforeach
                        </datalist>
                        @if(count($history['nama_sopir'] ?? []) > 0)
                            <div style="display:flex; flex-wrap:wrap; gap:4px; margin-top:5px;">
                                <span style="font-size:9px; color:#9ca3af; align-self:center;">Rekomendasi:</span>
                                @foreach(($history['nama_sopir'] ?? collect())->take(3) as $rec)
                                    <button type="button" @click="namaSopir = '{{ addslashes($rec) }}'" style="font-size:10px; font-weight: 600; padding:2px 7px; border-radius:10px; background:#eff4fc; color:#285491; border:1px solid #d0e0f5; cursor:pointer;">
                                        {{ $rec }}
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

            </div>
        </div>

        <!-- SECTION 3: Petugas Lapangan (Wider Card & Fix Menabrak Card) -->
        <div class="section-card" style="overflow: visible !important; position: relative; z-index: 50;" x-data="{ open: true }">
            <div class="section-card-header" @click="open = !open"
                 style="background: linear-gradient(90deg, rgba(13,89,80,0.04), transparent);">
                <div style="width: 30px; height: 30px; border-radius: 8px; background: #e8f5f3; display: flex; align-items: center; justify-content: center;">
                    <i class="ph-fill ph-users" style="font-size: 16px; color: #0d5950;"></i>
                </div>
                <div style="flex: 1;">
                    <p style="font-size: 13px; font-weight: 700; color: #1f2937;">3. Petugas Lapangan (Pemeriksa)</p>
                    <p style="font-size: 10px; color: #64748b;">Pilih petugas yang bertugas melakukan inspeksi staging</p>
                </div>
                <i class="ph-bold" :class="open ? 'ph-caret-up' : 'ph-caret-down'" style="font-size: 14px; color: #9ca3af;"></i>
            </div>

            <div class="section-card-body" x-show="open" x-collapse style="overflow: visible !important; padding: 16px;">
                <label class="field-label" style="margin-bottom: 6px;">
                    Nama Petugas Pemeriksa <span style="color:#ef4444;">*</span>
                </label>

                <!-- Searchable Combobox Container with Click-Outside -->
                <div style="position: relative; width: 100%;" @click.outside="dropdownOpen = false">

                    <!-- STATE 1: Saat Petugas Belum Dipilih -->
                    <div x-show="!karyawanId"
                         @click="dropdownOpen = !dropdownOpen; if(dropdownOpen) $nextTick(() => $refs.karyawanSearchInput?.focus())"
                         style="cursor: pointer; display: flex; align-items: center; justify-content: space-between; padding: 12px 14px; background: #fff; border: 1.5px solid #cbd5e1; border-radius: 12px; min-height: 48px; box-sizing: border-box; transition: all 0.2s;"
                         onmouseover="this.style.borderColor='#285491'"
                         onmouseout="this.style.borderColor='#cbd5e1'">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <div style="width: 26px; height: 26px; border-radius: 8px; background: #eff4fc; display: flex; align-items: center; justify-content: center;">
                                <i class="ph-bold ph-user" style="color: #285491; font-size: 14px;"></i>
                            </div>
                            <span style="color: #64748b; font-size: 13px; font-weight: 500;">Cari & Pilih Petugas Lapangan...</span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 6px;">
                            <i class="ph-bold ph-caret-down" style="color: #64748b; font-size: 14px;"></i>
                        </div>
                    </div>

                    <!-- STATE 2: Saat Petugas Sudah Dipilih (Handsome Card View) -->
                    <div x-show="karyawanId"
                         style="display: flex; align-items: center; justify-content: space-between; padding: 10px 14px; background: #f0fdf4; border: 1.5px solid #86efac; border-radius: 12px; min-height: 48px; box-sizing: border-box;">
                        <div style="display: flex; align-items: center; gap: 12px; overflow: hidden;">
                            <div style="width: 32px; height: 32px; border-radius: 10px; background: linear-gradient(135deg, #059e3d, #63c384); color: white; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 12px; shrink: 0; box-shadow: 0 2px 6px rgba(5,158,61,0.25);">
                                <i class="ph-bold ph-user-check"></i>
                            </div>
                            <div style="overflow: hidden;">
                                <p style="font-weight: 700; color: #14532d; font-size: 13px; line-height: 1.2; text-overflow: ellipsis; overflow: hidden; white-space: nowrap;"
                                   x-text="selectedKaryawan?.nama"></p>
                                <div style="display: flex; align-items: center; gap: 6px; margin-top: 2px;">
                                    <span style="font-family: monospace; font-size: 10px; font-weight: 700; background: #dcfce7; color: #166534; padding: 1px 6px; border-radius: 4px;"
                                          x-text="selectedKaryawan?.nomor_induk"></span>
                                    <span style="font-size: 10px; color: #15803d; font-weight: 600;">● Aktif</span>
                                </div>
                            </div>
                        </div>

                        <!-- Change Officer Button -->
                        <button type="button"
                                @click="dropdownOpen = !dropdownOpen; if(dropdownOpen) $nextTick(() => $refs.karyawanSearchInput?.focus())"
                                style="background: white; border: 1px solid #bbf7d0; color: #166534; padding: 5px 12px; border-radius: 8px; font-size: 11px; font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 4px; shrink: 0;"
                                onmouseover="this.style.background='#dcfce7'"
                                onmouseout="this.style.background='white'">
                            <i class="ph-bold ph-pencil-simple"></i> Ganti
                        </button>
                    </div>

                    <!-- DROPDOWN FLOATING PANEL (High Z-Index, Wide, Free from Card Overflow) -->
                    <div x-cloak x-show="dropdownOpen"
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 translate-y-1"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-100"
                         x-transition:leave-start="opacity-100 translate-y-0"
                         x-transition:leave-end="opacity-0 translate-y-1"
                         style="position: absolute; top: calc(100% + 6px); left: 0; right: 0; background: white; border: 1.5px solid #cbd5e1; border-radius: 14px; box-shadow: 0 14px 36px rgba(15,23,42,0.18); z-index: 1000; overflow: hidden;">

                        <!-- Search Box Header -->
                        <div style="padding: 10px; background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                            <div style="position: relative;">
                                <i class="ph-bold ph-magnifying-glass" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); font-size: 14px; color: #64748b;"></i>
                                <input type="text"
                                       x-ref="karyawanSearchInput"
                                       x-model="searchKaryawan"
                                       placeholder="Ketik nama atau nomor induk petugas..."
                                       style="width: 100%; box-sizing: border-box; padding: 8px 12px 8px 34px; font-size: 12px; border: 1.5px solid #cbd5e1; border-radius: 10px; outline: none; background: white;"
                                       @keydown.escape="dropdownOpen = false">
                                <button type="button" x-show="searchKaryawan" @click="searchKaryawan = ''" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; font-size: 14px; color: #94a3b8; cursor: pointer;">&times;</button>
                            </div>
                        </div>

                        <!-- Scrollable Petugas Items (Large touch targets for mobile) -->
                        <div style="max-height: 240px; overflow-y: auto; padding: 6px;">
                            <template x-for="item in filteredKaryawans" :key="item.id">
                                <div @click="karyawanId = String(item.id); dropdownOpen = false; searchKaryawan = '';"
                                     style="padding: 10px 12px; border-radius: 10px; font-size: 12px; cursor: pointer; display: flex; align-items: center; justify-content: space-between; transition: all 0.15s; margin-bottom: 2px;"
                                     :style="karyawanId == item.id ? 'background: #eff4fc; color: #285491;' : 'color: #1e293b;'"
                                     onmouseover="this.style.background='#f1f5f9'"
                                     onmouseout="this.style.background = (karyawanId == item.id ? '#eff4fc' : 'transparent')">
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        <div style="width: 28px; height: 28px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 10px; shrink: 0;"
                                             :style="karyawanId == item.id ? 'background: #285491; color: white;' : 'background: #e2e8f0; color: #475569;'">
                                            <span x-text="item.nama.substring(0, 2).toUpperCase()"></span>
                                        </div>
                                        <div>
                                            <p style="font-weight: 700; line-height: 1.2;" x-text="item.nama"></p>
                                            <p style="font-family: monospace; font-size: 10px; color: #64748b; margin-top: 1px;" x-text="'NIK: ' + item.nomor_induk"></p>
                                        </div>
                                    </div>
                                    <div style="display: flex; align-items: center; gap: 6px;">
                                        <span x-show="karyawanId == item.id" style="font-size: 10px; font-weight: 700; color: #059e3d; background: #e1f8eb; padding: 2px 7px; border-radius: 6px;">
                                            <i class="ph-bold ph-check"></i> Terpilih
                                        </span>
                                    </div>
                                </div>
                            </template>

                            <div x-show="filteredKaryawans.length === 0" style="padding: 18px; text-align: center; color: #64748b; font-size: 12px;">
                                <i class="ph-bold ph-user-minus" style="font-size: 20px; color: #94a3b8; display: block; margin-bottom: 4px;"></i>
                                Petugas dengan nama tersebut tidak ditemukan
                            </div>
                        </div>

                    </div>

                    <!-- Hidden input for standard form submission & validation -->
                    <input type="hidden" name="karyawan_id" :value="karyawanId" required>
                </div>

            </div>
        </div>

        <!-- Tip Card (collapsible) -->
        <div x-data="{ open: false }" style="border-radius: 14px; border: 1px solid #e1f8eb; background: #f7fff9; overflow: hidden;">
            <div @click="open = !open" style="padding: 10px 14px; display: flex; align-items: center; gap: 8px; cursor: pointer;">
                <i class="ph-fill ph-lightbulb" style="font-size: 16px; color: #059e3d;"></i>
                <p style="font-size: 11px; font-weight: 700; color: #059e3d; flex: 1;">Tips Pengambilan Foto Dokumen OCR</p>
                <i class="ph-bold" :class="open ? 'ph-caret-up' : 'ph-caret-down'" style="font-size: 12px; color: #059e3d;"></i>
            </div>
            <div x-show="open" x-collapse style="padding: 0 14px 12px; font-size: 11px; color: #374151; line-height: 1.6;">
                <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 4px;">
                    <li style="display:flex; gap:6px;"><span style="color:#059e3d; font-weight: bold;">✓</span> Pastikan lembar dokumen rata, terang, dan tidak berbayang</li>
                    <li style="display:flex; gap:6px;"><span style="color:#059e3d; font-weight: bold;">✓</span> Posisikan kamera tepat tegak lurus di atas dokumen</li>
                    <li style="display:flex; gap:6px;"><span style="color:#059e3d; font-weight: bold;">✓</span> Nomor shipment order yang tercetak rapi akan terbaca 100% otomatis</li>
                    <li style="display:flex; gap:6px;"><span style="color:#f59e0b; font-weight: bold;">!</span> Kolom plat nomor & sopir bisa dipilih melalui tombol Rekomendasi</li>
                </ul>
            </div>
        </div>

        <!-- Validation Warning / Ready Badge -->
        <div x-cloak x-show="!isValid" style="border-radius: 12px; background: #fffbeb; border: 1.5px solid #fde68a; padding: 12px 14px; display: flex; align-items: flex-start; gap: 10px;">
            <i class="ph-fill ph-warning-circle" style="color: #d97706; font-size: 18px; margin-top: 1px; flex-shrink: 0;"></i>
            <div style="font-size: 11px; color: #92400e; line-height: 1.4;">
                <span style="font-weight: 700;">Formulir Belum Lengkap:</span>
                <span x-text="'Harap lengkapi ' + missingFields.join(', ') + ' agar tombol aktif.'"></span>
            </div>
        </div>

        <div x-cloak x-show="isValid" style="border-radius: 12px; background: #ecfdf5; border: 1.5px solid #a7f3d0; padding: 12px 14px; display: flex; align-items: center; gap: 10px;">
            <i class="ph-fill ph-check-circle" style="color: #059e3d; font-size: 18px; flex-shrink: 0;"></i>
            <div style="font-size: 11px; color: #065f46; font-weight: 700;">
                Semua data wajib telah lengkap. Siap melanjutkan ke proses Loading Evidence!
            </div>
        </div>

        <!-- Submit / Next Button -->
        <button type="submit"
                :disabled="!isValid"
                :style="isValid 
                    ? 'width: 100%; background: linear-gradient(135deg, #285491, #0d5950); color: white; font-weight: 700; font-size: 14px; border: none; border-radius: 14px; padding: 14px; display: flex; align-items: center; justify-content: center; gap: 8px; cursor: pointer; box-shadow: 0 4px 16px rgba(40,84,145,0.35); transition: all 0.2s; margin-top: 4px;' 
                    : 'width: 100%; background: #94a3b8; color: #f1f5f9; font-weight: 700; font-size: 14px; border: none; border-radius: 14px; padding: 14px; display: flex; align-items: center; justify-content: center; gap: 8px; cursor: not-allowed; box-shadow: none; opacity: 0.7; transition: all 0.2s; margin-top: 4px;'"
                onmouseover="if (!this.disabled) { this.style.transform='translateY(-2px)'; this.style.boxShadow='0 8px 24px rgba(40,84,145,0.45)'; }"
                onmouseout="if (!this.disabled) { this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 16px rgba(40,84,145,0.35)'; }">
            Lanjut ke Loading Evidence
            <i class="ph-bold ph-arrow-right" style="font-size: 16px;"></i>
        </button>

        <!-- LIGHTBOX MODAL: Zoom Full Foto Dokumen -->
        <div x-cloak x-show="zoomModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm"
             @click="zoomModalOpen = false">
            <div class="relative max-w-2xl w-full bg-slate-900 rounded-2xl overflow-hidden shadow-2xl p-3" @click.stop>
                <div class="flex items-center justify-between mb-2 px-2 text-white">
                    <p class="text-xs font-bold">Preview Dokumen Shipment Order</p>
                    <button type="button" @click="zoomModalOpen = false" class="text-gray-300 hover:text-white p-1">
                        <i class="ph-bold ph-x text-lg"></i>
                    </button>
                </div>
                <div class="max-h-[80vh] overflow-auto flex items-center justify-center bg-black/50 rounded-xl p-1">
                    <img :src="photoPreviewUrl" class="max-h-full max-w-full object-contain" alt="Dokumen Penuh">
                </div>
            </div>
        </div>

    </form>

</x-field-layout>
