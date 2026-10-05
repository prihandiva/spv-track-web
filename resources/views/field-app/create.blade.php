<x-field-layout>

    <!-- Page Header -->
    <div style="padding: 4px 2px 8px;">
        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 4px;">
            <div style="width: 36px; height: 36px; border-radius: 12px; background: linear-gradient(135deg, #285491, #0d5950); display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(40,84,145,0.25);">
                <i class="ph-bold ph-clipboard-text" style="font-size: 18px; color: white;"></i>
            </div>
            <div>
                <h1 style="font-size: 16px; font-weight: 700; color: #1f2937; line-height: 1.2;">Mulai Staging</h1>
                <p style="font-size: 11px; color: #64748b;">Scan dokumen Shipment Order untuk auto-fill 5 data utama pengiriman</p>
            </div>
        </div>
    </div>

    @if($errors->any())
        <div style="background:#fef2f2; border:1px solid #fecaca; border-radius:12px; padding:12px 14px; margin-bottom:12px; color:#b91c1c; font-size:12px;">
            <p style="font-weight:700; margin-bottom:4px; display:flex; align-items:center; gap:6px;">
                <i class="ph-bold ph-warning-circle"></i> Mohon periksa kembali formulir:
            </p>
            <ul style="margin:0; padding-left:20px;">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('field-app.store', [], false) }}" method="POST" enctype="multipart/form-data"
          x-data="{
              nomorContainer: '{{ old('nomor_container_atau_plat', '') }}',
              shipmentGroup: '{{ old('shipment_group', '') }}',
              shipmentNo: '{{ old('shipment_no', '') }}',
              platNomor: '{{ old('plat_nomor', '') }}',
              namaSopir: '{{ old('nama_sopir', '') }}',
              selectedKaryawanIds: {{ Js::from(array_map('strval', (array) old('karyawan_ids', old('karyawan_id') ? [old('karyawan_id')] : []))) }},
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
              karyawans: {{ Js::from($karyawans->map(fn($k) => ['id' => (string) $k->id, 'nama' => $k->nama, 'nomor_induk' => $k->nomor_induk])) }},

              sanitizeDigits(val) {
                  return String(val || '').replace(/\D/g, '').slice(0, 9);
              },

              toggleKaryawan(id) {
                  const sId = String(id);
                  const idx = this.selectedKaryawanIds.indexOf(sId);
                  if (idx > -1) {
                      this.selectedKaryawanIds.splice(idx, 1);
                  } else {
                      this.selectedKaryawanIds.push(sId);
                  }
              },
              isKaryawanSelected(id) {
                  return this.selectedKaryawanIds.includes(String(id));
              },
              removeKaryawan(id) {
                  this.selectedKaryawanIds = this.selectedKaryawanIds.filter(item => item !== String(id));
              },
              selectAllFiltered() {
                  this.filteredKaryawans.forEach(item => {
                      const sId = String(item.id);
                      if (!this.selectedKaryawanIds.includes(sId)) {
                          this.selectedKaryawanIds.push(sId);
                      }
                  });
              },
              clearSelected() {
                  this.selectedKaryawanIds = [];
              },
              get selectedKaryawans() {
                  return this.karyawans.filter(k => this.selectedKaryawanIds.includes(String(k.id)));
              },
              get filteredKaryawans() {
                  if (!this.searchKaryawan.trim()) return this.karyawans;
                  const q = this.searchKaryawan.toLowerCase();
                  return this.karyawans.filter(k => k.nama.toLowerCase().includes(q) || k.nomor_induk.toLowerCase().includes(q));
              },

              // Validasi 9 digit angka
              get isPackingListValid() {
                  return this.packingListNo.trim().length === 9;
              },
              get isShipmentGroupValid() {
                  return this.shipmentGroup.trim().length === 9;
              },
              get isShipmentNoValid() {
                  return this.shipmentNo.trim().length === 9;
              },

              get isValid() {
                  return this.isPackingListValid &&
                         this.isShipmentGroupValid &&
                         this.isShipmentNoValid &&
                         this.nomorContainer.trim().length > 0 &&
                         this.platNomor.trim().length > 0 &&
                         this.selectedKaryawanIds.length > 0;
              },
              get missingFields() {
                  const missing = [];
                  if (this.packingListNo.length === 0) {
                      missing.push('No. Surat Jalan / Delivery / Packing List (9 digit)');
                  } else if (this.packingListNo.length < 9) {
                      missing.push('No. Surat Jalan (baru ' + this.packingListNo.length + '/9 digit)');
                  }

                  if (this.shipmentGroup.length === 0) {
                      missing.push('Shipment Group (9 digit)');
                  } else if (this.shipmentGroup.length < 9) {
                      missing.push('Shipment Group (baru ' + this.shipmentGroup.length + '/9 digit)');
                  }

                  if (this.shipmentNo.length === 0) {
                      missing.push('Shipment No (9 digit)');
                  } else if (this.shipmentNo.length < 9) {
                      missing.push('Shipment No (baru ' + this.shipmentNo.length + '/9 digit)');
                  }

                  if (!this.platNomor.trim()) missing.push('Plat Nomor');
                  if (!this.nomorContainer.trim()) missing.push('No. Container');
                  if (this.selectedKaryawanIds.length === 0) missing.push('Petugas Lapangan (minimal 1)');
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
                              const plVal = this.sanitizeDigits(data.packing_list_no || data.delivery_no);
                              this.packingListNo = plVal;
                              extractedItems.push('Delivery/SJ: ' + plVal);
                          }
                          if (data.shipment_group) {
                              const sgVal = this.sanitizeDigits(data.shipment_group);
                              this.shipmentGroup = sgVal;
                              extractedItems.push('Shipment Group: ' + sgVal);
                          }
                          if (data.shipment_no) {
                              const snVal = this.sanitizeDigits(data.shipment_no);
                              this.shipmentNo = snVal;
                              extractedItems.push('Shipment No: ' + snVal);
                          }
                          if (data.plat_nomor) {
                              this.platNomor = data.plat_nomor;
                              extractedItems.push('Plat: ' + data.plat_nomor);
                          }
                          if (data.container_no) {
                              this.nomorContainer = data.container_no;
                              extractedItems.push('Container: ' + data.container_no);
                          }
                          if (data.nama_sopir) {
                              this.namaSopir = data.nama_sopir;
                              extractedItems.push('Sopir: ' + data.nama_sopir);
                          }

                          if (extractedItems.length > 0) {
                              this.ocrStatus = 'success';
                              this.ocrMessage = 'OCR Berhasil! ' + extractedItems.length + ' data berhasil diekstrak otomatis.';
                              this.ocrExtractedSummary = extractedItems.join(' • ');
                          } else {
                              this.ocrStatus = 'warning';
                              this.ocrMessage = 'Dokumen terunggah. Teks dokumen tidak terdeteksi otomatis, silakan lengkapi form atau pilih dari riwayat.';
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

          style="display: flex; flex-direction: column; gap: 14px;">
        @csrf

        <input type="hidden" name="ocr_raw_text" :value="ocrRawText">

        <!-- SECTION 1 (DI TARUH DEPAN SEMUA): Dokumen Shipment Order (OCR) & 5 Data Utama -->
        <div class="section-card" x-data="{ open: true }">
            <div class="section-card-header" @click="open = !open"
                 style="background: linear-gradient(90deg, rgba(5,158,61,0.06), transparent);">
                <div style="width: 32px; height: 32px; border-radius: 10px; background: #e1f8eb; display: flex; align-items: center; justify-content: center; shrink: 0;">
                    <i class="ph-bold ph-scan" style="font-size: 18px; color: #059e3d;"></i>
                </div>
                <div style="flex: 1;">
                    <div style="display:flex; align-items:center; gap:6px; flex-wrap:wrap;">
                        <p style="font-size: 13px; font-weight: 700; color: #1f2937;">1. Dokumen Shipment Order</p>
                        <span style="font-size: 10px; font-weight: 700; color: #059e3d; background: #e1f8eb; padding: 2px 7px; border-radius: 6px;">OCR Auto-Fill</span>
                    </div>
                    <p style="font-size: 10px; color: #64748b;">Foto dokumen untuk auto-fill 5 data utama pengiriman otomatis</p>
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
                    <div style="width: 46px; height: 46px; border-radius: 12px; background: #eff4fc; color: #285491; display: flex; align-items: center; justify-content: center; margin: 0 auto 8px; box-shadow: 0 2px 8px rgba(40,84,145,0.08);">
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
                                <i class="ph-bold ph-check"></i> Foto Shipment Order Siap
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
                            <p style="font-size: 10px; color: #64748b; margin-top: 1px;">Mengekstrak nomor Surat Jalan/Delivery, Shipment Group, Shipment No, Plat, & Container.</p>
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
                                    Anda dapat langsung memeriksa dan melengkapi 5 data di bawah secara manual atau memilih dari riwayat.
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

                <!-- 5 DATA UTAMA PADA SHIPMENT ORDER -->
                <div style="padding: 12px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; display: flex; flex-direction: column; gap: 14px;">
                    <div style="display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #e2e8f0; padding-bottom: 8px;">
                        <span style="font-size: 12px; font-weight: 700; color: #1e3a8a; display: flex; align-items: center; gap: 6px;">
                            <i class="ph-bold ph-identification-card" style="font-size: 16px; color: #285491;"></i>
                            5 Data Wajib Shipment Order
                        </span>
                        <span style="font-size: 10px; font-weight: 700; color: #d97706; background: #fffbeb; border: 1px solid #fde68a; padding: 2px 8px; border-radius: 10px;">
                            Wajib Isi
                        </span>
                    </div>

                    <!-- 1. Nomor Surat Jalan / Delivery / Number Packing List (Fix 9 Digit Angka) -->
                    <div style="background: white; border: 1.5px solid #bfcfe8; border-radius: 12px; padding: 12px;"
                         :style="packingListNo.length === 9 ? 'border-color:#86efac; background:#f0fdf4;' : (packingListNo.length > 0 ? 'border-color:#fde68a; background:#fffdf5;' : '')">
                        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:4px;">
                            <label class="field-label" style="display:flex; align-items:center; gap:5px; font-weight:700; color:#1e3a8a; font-size:12px; margin-bottom:0;">
                                <i class="ph-bold ph-barcode" style="color:#285491; font-size:15px;"></i>
                                No. Surat Jalan / Delivery / Packing List <span style="color:#ef4444;">*</span>
                            </label>
                            <span style="font-size:10px; font-weight:700; padding:2px 7px; border-radius:6px; font-family: inherit;"
                                  :style="packingListNo.length === 9 ? 'background:#dcfce7; color:#15803d; border:1px solid #86efac;' : (packingListNo.length > 0 ? 'background:#fef3c7; color:#b45309; border:1px solid #fde68a;' : 'background:#eff4fc; color:#285491; border:1px solid #bfcfe8;')">
                                <span x-text="packingListNo.length"></span>/9 Digit
                            </span>
                        </div>

                        <input type="text"
                               inputmode="numeric"
                               pattern="[0-9]*"
                               maxlength="9"
                               id="packing_list_no"
                               name="packing_list_no"
                               x-model="packingListNo"
                               @input="packingListNo = $event.target.value.replace(/\D/g, '').slice(0, 9); $event.target.value = packingListNo;"
                               list="history-packing-list"
                               required
                               placeholder="Contoh: 800123456 (Wajib tepat 9 digit angka)"
                               class="field-input"
                               style="font-size:14px; font-weight:700; letter-spacing:0.04em; background:white;">

                        <!-- Warning jika kurang dari 9 digit -->
                        <div x-cloak x-show="packingListNo.length > 0 && packingListNo.length < 9"
                             style="display:flex; align-items:center; gap:5px; margin-top:6px; padding:5px 8px; border-radius:6px; background:#fffbeb; border:1px solid #fde68a; color:#b45309; font-size:11px; font-weight:600;">
                            <i class="ph-bold ph-warning" style="font-size:14px; color:#d97706; flex-shrink:0;"></i>
                            <span>Angka kurang dari 9 digit! (Saat ini: <strong x-text="packingListNo.length"></strong> digit, kurang <strong x-text="9 - packingListNo.length"></strong> digit lagi).</span>
                        </div>

                        <!-- Status pas 9 digit -->
                        <div x-cloak x-show="packingListNo.length === 9"
                             style="display:flex; align-items:center; gap:5px; margin-top:6px; padding:5px 8px; border-radius:6px; background:#ecfdf5; border:1px solid #a7f3d0; color:#065f46; font-size:11px; font-weight:600;">
                            <i class="ph-bold ph-check-circle" style="font-size:14px; color:#059e3d; flex-shrink:0;"></i>
                            <span>Format valid (tepat 9 digit angka).</span>
                        </div>

                        <datalist id="history-packing-list">
                            @foreach($history['packing_list_no'] ?? [] as $item)
                                <option value="{{ $item }}">
                            @endforeach
                        </datalist>
                        @if(count($history['packing_list_no'] ?? []) > 0)
                            <div style="display:flex; flex-wrap:wrap; gap:4px; margin-top:6px;">
                                <span style="font-size:9px; color:#9ca3af; align-self:center;">Riwayat:</span>
                                @foreach(($history['packing_list_no'] ?? collect())->take(3) as $rec)
                                    <button type="button" @click="packingListNo = '{{ preg_replace('/\D/', '', $rec) }}'.slice(0, 9)"
                                            style="font-size:10px; font-weight: 600; padding:2px 7px; border-radius:10px; background:#fff; color:#285491; border:1px solid #bfcfe8; cursor:pointer;">
                                        {{ $rec }}
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <!-- 2 & 3. Nomor Shipment Group & Nomor Shipment (Keduanya Fix 9 Digit Angka) -->
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                        <!-- Nomor Shipment Group -->
                        <div style="background: white; border: 1.5px solid #cbd5e1; border-radius: 12px; padding: 10px;"
                             :style="shipmentGroup.length === 9 ? 'border-color:#86efac; background:#f0fdf4;' : (shipmentGroup.length > 0 ? 'border-color:#fde68a; background:#fffdf5;' : '')">
                            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:4px;">
                                <label class="field-label" style="display:flex; align-items:center; gap:4px; margin-bottom:0;">
                                    <i class="ph-bold ph-hash" style="color:#285491; font-size:12px;"></i>Shipment Group <span style="color:#ef4444;">*</span>
                                </label>
                                <span style="font-size:9px; font-weight:700; padding:1px 5px; border-radius:4px; font-family: inherit;"
                                      :style="shipmentGroup.length === 9 ? 'background:#dcfce7; color:#15803d;' : (shipmentGroup.length > 0 ? 'background:#fef3c7; color:#b45309;' : 'background:#eff4fc; color:#285491;')">
                                    <span x-text="shipmentGroup.length"></span>/9 Digit
                                </span>
                            </div>

                            <input type="text"
                                   inputmode="numeric"
                                   pattern="[0-9]*"
                                   maxlength="9"
                                   id="shipment_group"
                                   name="shipment_group"
                                   x-model="shipmentGroup"
                                   @input="shipmentGroup = $event.target.value.replace(/\D/g, '').slice(0, 9); $event.target.value = shipmentGroup;"
                                   list="history-shipment-group"
                                   required
                                   placeholder="Contoh: 869026487"
                                   class="field-input"
                                   style="font-size:13px; font-weight: 700; background:white;">

                            <!-- Warning jika < 9 -->
                            <div x-cloak x-show="shipmentGroup.length > 0 && shipmentGroup.length < 9"
                                 style="margin-top:4px; font-size:10px; color:#b45309; display:flex; align-items:center; gap:4px;">
                                <i class="ph-bold ph-warning"></i> Kurang <span x-text="9 - shipmentGroup.length"></span> digit
                            </div>
                            <div x-cloak x-show="shipmentGroup.length === 9"
                                 style="margin-top:4px; font-size:10px; color:#059e3d; display:flex; align-items:center; gap:4px; font-weight:600;">
                                <i class="ph-bold ph-check"></i> Pas 9 digit
                            </div>

                            <datalist id="history-shipment-group">
                                @foreach($history['shipment_group'] ?? [] as $item)
                                    <option value="{{ $item }}">
                                @endforeach
                            </datalist>
                            @if(count($history['shipment_group'] ?? []) > 0)
                                <div style="display:flex; flex-wrap:wrap; gap:4px; margin-top:5px;">
                                    @foreach(($history['shipment_group'] ?? collect())->take(2) as $rec)
                                        <button type="button" @click="shipmentGroup = '{{ preg_replace('/\D/', '', $rec) }}'.slice(0, 9)"
                                                style="font-size:9px; font-weight: 600; padding:2px 6px; border-radius:8px; background:#eff4fc; color:#285491; border:1px solid #d0e0f5; cursor:pointer;">
                                            {{ $rec }}
                                        </button>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        <!-- Nomor Shipment -->
                        <div style="background: white; border: 1.5px solid #cbd5e1; border-radius: 12px; padding: 10px;"
                             :style="shipmentNo.length === 9 ? 'border-color:#86efac; background:#f0fdf4;' : (shipmentNo.length > 0 ? 'border-color:#fde68a; background:#fffdf5;' : '')">
                            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:4px;">
                                <label class="field-label" style="display:flex; align-items:center; gap:4px; margin-bottom:0;">
                                    <i class="ph-bold ph-hash" style="color:#285491; font-size:12px;"></i>Shipment No. <span style="color:#ef4444;">*</span>
                                </label>
                                <span style="font-size:9px; font-weight:700; padding:1px 5px; border-radius:4px; font-family: inherit;"
                                      :style="shipmentNo.length === 9 ? 'background:#dcfce7; color:#15803d;' : (shipmentNo.length > 0 ? 'background:#fef3c7; color:#b45309;' : 'background:#eff4fc; color:#285491;')">
                                    <span x-text="shipmentNo.length"></span>/9 Digit
                                </span>
                            </div>

                            <input type="text"
                                   inputmode="numeric"
                                   pattern="[0-9]*"
                                   maxlength="9"
                                   id="shipment_no"
                                   name="shipment_no"
                                   x-model="shipmentNo"
                                   @input="shipmentNo = $event.target.value.replace(/\D/g, '').slice(0, 9); $event.target.value = shipmentNo;"
                                   list="history-shipment-no"
                                   required
                                   placeholder="Contoh: 860110918"
                                   class="field-input"
                                   style="font-size:13px; font-weight: 700; background:white;">

                            <!-- Warning jika < 9 -->
                            <div x-cloak x-show="shipmentNo.length > 0 && shipmentNo.length < 9"
                                 style="margin-top:4px; font-size:10px; color:#b45309; display:flex; align-items:center; gap:4px;">
                                <i class="ph-bold ph-warning"></i> Kurang <span x-text="9 - shipmentNo.length"></span> digit
                            </div>
                            <div x-cloak x-show="shipmentNo.length === 9"
                                 style="margin-top:4px; font-size:10px; color:#059e3d; display:flex; align-items:center; gap:4px; font-weight:600;">
                                <i class="ph-bold ph-check"></i> Pas 9 digit
                            </div>

                            <datalist id="history-shipment-no">
                                @foreach($history['shipment_no'] ?? [] as $item)
                                    <option value="{{ $item }}">
                                @endforeach
                            </datalist>
                            @if(count($history['shipment_no'] ?? []) > 0)
                                <div style="display:flex; flex-wrap:wrap; gap:4px; margin-top:5px;">
                                    @foreach(($history['shipment_no'] ?? collect())->take(2) as $rec)
                                        <button type="button" @click="shipmentNo = '{{ preg_replace('/\D/', '', $rec) }}'.slice(0, 9)"
                                                style="font-size:9px; font-weight: 600; padding:2px 6px; border-radius:8px; background:#eff4fc; color:#285491; border:1px solid #d0e0f5; cursor:pointer;">
                                            {{ $rec }}
                                        </button>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- 4 & 5. Plat Nomor & Nomor Container -->
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                        <!-- Plat Nomor -->
                        <div>
                            <label class="field-label" style="display:flex; align-items:center; gap:4px;">
                                <i class="ph-bold ph-car" style="color:#285491; font-size:12px;"></i>Plat Nomor <span style="color:#ef4444;">*</span>
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
                                    <span style="font-size:9px; color:#9ca3af; align-self:center;">Riwayat:</span>
                                    @foreach(($history['plat_nomor'] ?? collect())->take(2) as $rec)
                                        <button type="button" @click="platNomor = '{{ addslashes($rec) }}'" style="font-size:10px; font-weight: 600; padding:2px 7px; border-radius:10px; background:#eff4fc; color:#285491; border:1px solid #d0e0f5; cursor:pointer;">
                                            {{ $rec }}
                                        </button>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        <!-- Nomor Container -->
                        <div>
                            <label class="field-label" style="display:flex; align-items:center; gap:4px; margin-bottom: 3px;">
                                <i class="ph-bold ph-truck" style="color:#285491; font-size:12px;"></i>No. Container <span style="color:#ef4444;">*</span>
                            </label>
                            <input type="text" id="nomor_container_atau_plat" name="nomor_container_atau_plat"
                                   x-model="nomorContainer"
                                   list="history-container"
                                   placeholder="Contoh: MSKU1234567 atau 0" required class="field-input" style="font-size:12px; font-weight: 600; text-transform:uppercase;">
                            <p style="font-size: 10px; color: #64748b; margin-top: 4px; line-height: 1.3;">
                                Jika tidak ada container, maka diisi <strong>0</strong>.
                            </p>
                            <datalist id="history-container">
                                @foreach($history['nomor_container'] ?? [] as $item)
                                    <option value="{{ $item }}">
                                @endforeach
                            </datalist>
                            <div style="display:flex; flex-wrap:wrap; gap:4px; margin-top:5px; align-items:center;">
                                <button type="button" @click="nomorContainer = '0'" style="font-size:10px; font-weight: 600; padding:2px 7px; border-radius:10px; background:#eff4fc; color:#285491; border:1px solid #d0e0f5; cursor:pointer;" title="Klik untuk mengisi angka 0 otomatis">
                                    + Isi 0 (Tanpa Container)
                                </button>
                                @if(count($history['nomor_container'] ?? []) > 0)
                                    <span style="font-size:9px; color:#9ca3af; align-self:center; margin-left: 2px;">Riwayat:</span>
                                    @foreach(($history['nomor_container'] ?? collect())->take(2) as $rec)
                                        <button type="button" @click="nomorContainer = '{{ addslashes($rec) }}'" style="font-size:10px; font-weight: 600; padding:2px 7px; border-radius:10px; background:#eff4fc; color:#285491; border:1px solid #d0e0f5; cursor:pointer;">
                                            {{ $rec }}
                                        </button>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- 6. Nama Sopir (Pendukung) -->
                    <div>
                        <label class="field-label" style="display:flex; align-items:center; gap:4px;">
                            <i class="ph-bold ph-user" style="color:#285491; font-size:12px;"></i>Nama Sopir (Opsional)
                        </label>
                        <input type="text" id="nama_sopir" name="nama_sopir" x-model="namaSopir"
                               list="history-sopir"
                               placeholder="Nama sopir kendaraan" class="field-input" style="font-size:12px; font-weight: 600; text-transform:capitalize;">
                        <datalist id="history-sopir">
                            @foreach($history['nama_sopir'] ?? [] as $item)
                                <option value="{{ $item }}">
                            @endforeach
                        </datalist>
                        @if(count($history['nama_sopir'] ?? []) > 0)
                            <div style="display:flex; flex-wrap:wrap; gap:4px; margin-top:5px;">
                                <span style="font-size:9px; color:#9ca3af; align-self:center;">Riwayat:</span>
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

        <!-- SECTION 2: Data Pengiriman & Operasional Staging -->
        <div class="section-card" x-data="{ open: true }">
            <div class="section-card-header" @click="open = !open"
                 style="background: linear-gradient(90deg, rgba(40,84,145,0.04), transparent);">
                <div style="width: 32px; height: 32px; border-radius: 10px; background: #eff4fc; display: flex; align-items: center; justify-content: center; shrink: 0;">
                    <i class="ph-fill ph-package" style="font-size: 16px; color: #285491;"></i>
                </div>
                <div style="flex: 1;">
                    <p style="font-size: 13px; font-weight: 700; color: #1f2937;">2. Detail Data Staging</p>
                    <p style="font-size: 10px; color: #64748b;">Produk, jenis pengiriman, cuaca, waktu & lokasi warehouse</p>
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
            </div>
        </div>

        <!-- SECTION 3: Petugas Lapangan (Pemeriksa) -->
        <div class="section-card" style="overflow: visible !important; position: relative; z-index: 50;" x-data="{ open: true }">
            <div class="section-card-header" @click="open = !open"
                 style="background: linear-gradient(90deg, rgba(13,89,80,0.04), transparent); cursor: pointer;">
                <div style="width: 32px; height: 32px; border-radius: 10px; background: #e8f5f3; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <i class="ph-fill ph-users" style="font-size: 16px; color: #0d5950;"></i>
                </div>
                <div style="flex: 1; min-width: 0;">
                    <div style="display: flex; align-items: center; gap: 7px; flex-wrap: wrap;">
                        <p style="font-size: 13px; font-weight: 700; color: #1f2937; margin: 0;">3. Petugas Lapangan</p>
                        <span x-cloak x-show="selectedKaryawanIds.length > 0"
                              style="font-size: 10px; font-weight: 700; background: #dcfce7; color: #15803d; border: 1px solid #86efac; padding: 2px 7px; border-radius: 999px;"
                              x-text="selectedKaryawanIds.length + ' dipilih'"></span>
                    </div>
                    <p style="font-size: 11px; color: #64748b; margin: 2px 0 0;">Pilih satu atau lebih petugas pemeriksa</p>
                </div>
                <i class="ph-bold" :class="open ? 'ph-caret-up' : 'ph-caret-down'" style="font-size: 14px; color: #9ca3af; flex-shrink: 0;"></i>
            </div>

            <div class="section-card-body" x-show="open" x-collapse style="overflow: visible !important;">

                <!-- Label -->
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 6px;">
                    <label class="field-label" style="margin-bottom: 0;">
                        Petugas Pemeriksa <span style="color: #ef4444;">*</span>
                    </label>
                    <span x-cloak x-show="selectedKaryawanIds.length > 0"
                          style="font-size: 11px; color: #059e3d; font-weight: 600;">
                        <span x-text="selectedKaryawanIds.length"></span> orang terpilih
                    </span>
                </div>

                <!-- Dropdown Trigger Container -->
                <div style="position: relative;" @click.outside="dropdownOpen = false">

                    <!-- Trigger Box (looks identical to standard field-input) -->
                    <div @click="dropdownOpen = !dropdownOpen; if(dropdownOpen) $nextTick(() => $refs.karyawanSearchInput?.focus())"
                         class="field-input"
                         style="display: flex; align-items: center; justify-content: space-between; cursor: pointer; min-height: 42px; background: white; user-select: none;">
                        <div style="flex: 1; min-width: 0; display: flex; align-items: center; gap: 8px;">
                            <i class="ph ph-user-check" style="font-size: 16px; color: #64748b; flex-shrink: 0;"></i>
                            <span x-show="selectedKaryawanIds.length === 0" style="color: #9ca3af; font-size: 13px;">
                                Cari &amp; pilih petugas...
                            </span>
                            <span x-cloak x-show="selectedKaryawanIds.length > 0" style="color: #1f2937; font-size: 13px; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"
                                  x-text="selectedKaryawanIds.length + ' Petugas Dipilih: ' + selectedKaryawans.map(k => k.nama).join(', ')">
                            </span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 6px; flex-shrink: 0; margin-left: 8px;">
                            <i class="ph-bold" :class="dropdownOpen ? 'ph-caret-up' : 'ph-caret-down'" style="font-size: 12px; color: #64748b;"></i>
                        </div>
                    </div>

                    <!-- Selected Badges (Chips) -->
                    <div x-cloak x-show="selectedKaryawanIds.length > 0" style="display: flex; flex-wrap: wrap; gap: 6px; margin-top: 8px;">
                        <template x-for="k in selectedKaryawans" :key="k.id">
                            <span style="display: inline-flex; align-items: center; gap: 6px; background: #eff6ff; border: 1px solid #bfdbfe; color: #1e40af; padding: 3px 8px; border-radius: 6px; font-size: 12px; font-weight: 500;">
                                <span x-text="k.nama"></span>
                                <span style="font-size: 11px; color: #60a5fa;" x-text="'(' + k.nomor_induk + ')'"></span>
                                <button type="button"
                                        @click.stop="removeKaryawan(k.id)"
                                        style="background: none; border: none; padding: 0; margin-left: 2px; color: #93c5fd; cursor: pointer; font-size: 14px; line-height: 1; font-weight: 700; display: inline-flex; align-items: center;"
                                        onmouseover="this.style.color='#ef4444'"
                                        onmouseout="this.style.color='#93c5fd'"
                                        title="Hapus petugas ini">
                                    &times;
                                </button>
                            </span>
                        </template>
                    </div>

                    <!-- Dropdown Panel -->
                    <div x-cloak x-show="dropdownOpen"
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="opacity-0 translate-y-1"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-75"
                         x-transition:leave-start="opacity-100 translate-y-0"
                         x-transition:leave-end="opacity-0 translate-y-1"
                         style="position: absolute; top: calc(100% + 4px); left: 0; right: 0; background: white; border: 1px solid #cbd5e1; border-radius: 10px; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1), 0 8px 10px -6px rgba(0,0,0,0.05); z-index: 100; overflow: hidden;">

                        <!-- Search & Quick Action Toolbar -->
                        <div style="padding: 8px 10px; background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                            <div style="position: relative;">
                                <i class="ph-bold ph-magnifying-glass" style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); font-size: 13px; color: #94a3b8; pointer-events: none;"></i>
                                <input type="text"
                                       x-ref="karyawanSearchInput"
                                       x-model="searchKaryawan"
                                       placeholder="Cari nama atau NIK..."
                                       style="width: 100%; box-sizing: border-box; padding: 6px 26px 6px 28px; font-size: 12px; font-family: inherit; border: 1px solid #cbd5e1; border-radius: 6px; outline: none; background: white; color: #1e293b;"
                                       @keydown.escape="dropdownOpen = false">
                                <button type="button" x-show="searchKaryawan" @click="searchKaryawan = ''"
                                        style="position: absolute; right: 8px; top: 50%; transform: translateY(-50%); background: none; border: none; font-size: 14px; color: #94a3b8; cursor: pointer; line-height: 1;">&times;</button>
                            </div>

                            <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 6px; font-size: 11px; color: #64748b;">
                                <div style="display: flex; gap: 8px;">
                                    <button type="button" @click="selectAllFiltered()" style="background: none; border: none; padding: 0; color: #285491; font-weight: 600; cursor: pointer; font-size: 11px;">
                                        Pilih Semua
                                    </button>
                                    <span style="color: #cbd5e1;">|</span>
                                    <button type="button" @click="clearSelected()" x-show="selectedKaryawanIds.length > 0" style="background: none; border: none; padding: 0; color: #ef4444; font-weight: 600; cursor: pointer; font-size: 11px;">
                                        Reset
                                    </button>
                                </div>
                                <span x-text="selectedKaryawanIds.length + ' dari ' + karyawans.length + ' dipilih'"></span>
                            </div>
                        </div>

                        <!-- Normal Small Checklist Items -->
                        <div style="max-height: 220px; overflow-y: auto; padding: 4px;">
                            <template x-for="item in filteredKaryawans" :key="item.id">
                                <label style="display: flex; align-items: center; gap: 9px; padding: 8px 10px; border-radius: 6px; cursor: pointer; font-size: 13px; color: #1e293b; margin: 0; user-select: none; transition: background 0.1s;"
                                       :class="isKaryawanSelected(item.id) ? 'bg-blue-50' : 'hover:bg-slate-50'">
                                    <input type="checkbox"
                                           :value="item.id"
                                           :checked="isKaryawanSelected(item.id)"
                                           @change="toggleKaryawan(item.id)"
                                           style="width: 15px; height: 15px; accent-color: #285491; cursor: pointer; margin: 0; flex-shrink: 0;">
                                    <span style="font-weight: 500; font-size: 13px;" x-text="item.nama"></span>
                                    <span style="font-size: 11px; color: #64748b;" x-text="'(NIK: ' + item.nomor_induk + ')'"></span>
                                </label>
                            </template>

                            <div x-show="filteredKaryawans.length === 0" style="padding: 16px; text-align: center; color: #94a3b8; font-size: 12px;">
                                Petugas tidak ditemukan
                            </div>
                        </div>

                        <!-- Dropdown Footer -->
                        <div style="padding: 6px 10px; background: #f8fafc; border-top: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: flex-end;">
                            <button type="button" @click="dropdownOpen = false"
                                    style="background: #285491; color: white; border: none; border-radius: 6px; padding: 5px 14px; font-size: 12px; font-weight: 600; cursor: pointer;">
                                Selesai
                            </button>
                        </div>

                    </div>

                    <!-- Hidden form inputs -->
                    <template x-for="id in selectedKaryawanIds" :key="id">
                        <input type="hidden" name="karyawan_ids[]" :value="id">
                    </template>
                    <input type="hidden" name="karyawan_id" :value="selectedKaryawanIds[0] || ''">
                </div>

            </div>
        </div>

        <!-- Tip Card (collapsible) -->
        <div x-data="{ open: false }" style="border-radius: 14px; border: 1px solid #e1f8eb; background: #f7fff9; overflow: hidden;">
            <div @click="open = !open" style="padding: 10px 14px; display: flex; align-items: center; gap: 8px; cursor: pointer;">
                <i class="ph-fill ph-lightbulb" style="font-size: 16px; color: #059e3d;"></i>
                <p style="font-size: 11px; font-weight: 700; color: #059e3d; flex: 1;">Tips Pengambilan Foto Dokumen Shipment Order</p>
                <i class="ph-bold" :class="open ? 'ph-caret-up' : 'ph-caret-down'" style="font-size: 12px; color: #059e3d;"></i>
            </div>
            <div x-show="open" x-collapse style="padding: 0 14px 12px; font-size: 11px; color: #374151; line-height: 1.6;">
                <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 4px;">
                    <li style="display:flex; gap:6px;"><span style="color:#059e3d; font-weight: bold;">✓</span> Pastikan lembar dokumen Shipment Order rata, terang, dan tidak berbayang</li>
                    <li style="display:flex; gap:6px;"><span style="color:#059e3d; font-weight: bold;">✓</span> Posisikan kamera tepat tegak lurus di atas dokumen</li>
                    <li style="display:flex; gap:6px;"><span style="color:#059e3d; font-weight: bold;">✓</span> Kolom Delivery, Shipment Group, & Shipment No masing-masing berisi 9 digit angka</li>
                    <li style="display:flex; gap:6px;"><span style="color:#f59e0b; font-weight: bold;">!</span> Plat nomor & Container bisa pilih dari riwayat</li>
                </ul>
            </div>
        </div>

        <!-- Validation Warning / Ready Badge -->
        <div x-cloak x-show="!isValid" style="border-radius: 12px; background: #fffbeb; border: 1.5px solid #fde68a; padding: 12px 14px; display: flex; align-items: flex-start; gap: 10px;">
            <i class="ph-fill ph-warning-circle" style="color: #d97706; font-size: 18px; margin-top: 1px; flex-shrink: 0;"></i>
            <div style="font-size: 11px; color: #92400e; line-height: 1.4;">
                <span style="font-weight: 700;">Formulir Belum Lengkap / Kurang 9 Digit:</span>
                <span x-text="'Harap periksa ' + missingFields.join(', ') + ' agar tombol aktif.'"></span>
            </div>
        </div>

        <div x-cloak x-show="isValid" style="border-radius: 12px; background: #ecfdf5; border: 1.5px solid #a7f3d0; padding: 12px 14px; display: flex; align-items: center; gap: 10px;">
            <i class="ph-fill ph-check-circle" style="color: #059e3d; font-size: 18px; flex-shrink: 0;"></i>
            <div style="font-size: 11px; color: #065f46; font-weight: 700;">
                Semua data wajib telah lengkap & 9 digit valid. Siap melanjutkan ke Loading Evidence!
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
