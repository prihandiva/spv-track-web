<x-field-layout>

    <!-- Page Header -->
    <div style="padding: 4px 2px 8px;">
        <div style="display: flex; align-items: center; justify-content: space-between; gap: 10px; margin-bottom: 4px;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <div style="width: 36px; height: 36px; border-radius: 12px; background: linear-gradient(135deg, #285491, #0d5950); display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(40,84,145,0.25);">
                    <i class="ph-bold {{ isset($shipment) ? 'ph-pencil-simple-line' : 'ph-clipboard-text' }}" style="font-size: 18px; color: white;"></i>
                </div>
                <div>
                    <h1 style="font-size: 16px; font-weight: 700; color: #1f2937; line-height: 1.2;">
                        @if(isset($shipment))
                            {{ $shipment->status === 'submitted' ? 'Edit Dokumen Shipment' : 'Edit & Lengkapi Draft Staging' }}
                        @else
                            Mulai Staging
                        @endif
                    </h1>
                    <p style="font-size: 11px; color: #64748b;">
                        @if(isset($shipment))
                            No: <strong>{{ $shipment->packing_list_no ?: ($shipment->nomor_container_atau_plat ?: 'Shipment #' . $shipment->id) }}</strong> &bull; Periksa & perbarui formulir sebelum melanjutkan
                        @else
                            Scan dokumen Shipment Order untuk auto-fill 5 data utama pengiriman
                        @endif
                    </p>
                </div>
            </div>
            @if(isset($shipment))
                <a href="{{ route('shipments.show', $shipment->id) }}"
                   style="display: inline-flex; align-items: center; gap: 5px; font-size: 11px; font-weight: 600; color: #285491; background: #eff4fc; border: 1px solid #bfcfe8; padding: 6px 12px; border-radius: 10px; text-decoration: none; transition: background 0.15s;"
                   onmouseover="this.style.background='#e0edff'" onmouseout="this.style.background='#eff4fc'">
                    <i class="ph-bold ph-arrow-left"></i> Kembali
                </a>
            @endif
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

    @php
        $rawProduct = (string) old('jenis_produk', $shipment->jenis_produk ?? 'fiber');
        $rawProductCustom = (string) old('jenis_produk_custom', '');
        $standardOptions = ['fiber', 'sodium', 'sliver', 'yarn'];
        if (in_array(strtolower($rawProduct), $standardOptions, true)) {
            $initProductSelect = strtolower($rawProduct);
            $initCustomText = '';
        } else {
            $initProductSelect = 'others';
            $initCustomText = $rawProduct !== 'others' ? $rawProduct : $rawProductCustom;
        }

        // Warehouse Type & Location Initialization
        $existingWhType = (string) old('warehouse_type', isset($shipment) ? ($shipment->warehouse_type ?? (in_array(strtolower($shipment->warehouse_lokasi ?? ''), ['atas', 'tengah', 'bawah']) ? 'internal' : ($shipment->warehouse_lokasi ? 'external' : 'internal'))) : 'internal');
        $existingWhLoc = (string) old('warehouse_lokasi', $shipment->warehouse_lokasi ?? 'atas');
        $initWhInternal = in_array(strtolower($existingWhLoc), ['atas', 'tengah', 'bawah']) ? strtolower($existingWhLoc) : (string) old('warehouse_lokasi_internal', 'atas');
        $initWhExternal = !in_array(strtolower($existingWhLoc), ['atas', 'tengah', 'bawah']) ? $existingWhLoc : (string) old('warehouse_lokasi_external', '');

        $extWarehousesList = $externalWarehouses ?? ($history['external_warehouse'] ?? []);
        $isMatchedWh = in_array($initWhExternal, $extWarehousesList, true);
        $initWhExternalSelect = $isMatchedWh ? $initWhExternal : ($initWhExternal !== '' ? '__custom__' : '');
        $initWhExternalCustom = ! $isMatchedWh ? $initWhExternal : '';
    @endphp

    <script>
    function stagingFormData() {
        return {
            nomorContainer: {{ Js::from((string) old('nomor_container_atau_plat', $shipment->nomor_container_atau_plat ?? '')) }},
            shipmentGroup: {{ Js::from((string) old('shipment_group', $shipment->shipment_group ?? '')) }},
            shipmentNo: {{ Js::from((string) old('shipment_no', $shipment->shipment_no ?? '')) }},
            agenForwarding: {{ Js::from((string) old('agen_forwarding', $shipment->agen_forwarding ?? '')) }},
            tujuanPengiriman: {{ Js::from((string) old('tujuan_pengiriman', $shipment->tujuan_pengiriman ?? '')) }},
            platNomor: {{ Js::from((string) old('plat_nomor', $shipment->plat_nomor ?? '')) }},
            namaSopir: {{ Js::from((string) old('nama_sopir', $shipment->nama_sopir ?? '')) }},
            nomorSeal: {{ Js::from((string) old('nomor_seal', $shipment->nomor_seal ?? '')) }},
            selectedKaryawanIds: {{ Js::from(array_map('strval', (array) old('karyawan_ids', isset($shipment) ? ($shipment->karyawans->pluck('id')->map('strval')->all() ?: ($shipment->karyawan_id ? [(string) $shipment->karyawan_id] : [])) : (old('karyawan_id') ? [(string) old('karyawan_id')] : [])))) }},
            packingListNo: {{ Js::from((string) old('packing_list_no', $shipment->packing_list_no ?? '')) }},
            productChoice: {{ Js::from($initProductSelect) }},
            customProductText: {{ Js::from($initCustomText) }},
            whType: {{ Js::from($existingWhType) }},
            whInternal: {{ Js::from($initWhInternal) }},
            whExternalSelect: {{ Js::from($initWhExternalSelect) }},
            whExternalCustom: {{ Js::from($initWhExternalCustom) }},

            get whExternal() {
                if (this.whExternalSelect === '__custom__') {
                    return (this.whExternalCustom || '').trim();
                }
                return (this.whExternalSelect || '').trim();
            },
            handleWhExternalSelectChange() {
                if (this.whExternalSelect === '__custom__') {
                    this.$nextTick(() => {
                        const input = document.getElementById('wh-custom-input');
                        if (input) input.focus();
                    });
                }
            },
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

            // ── Barcode Scanner ──────────────────────────────────────
            barcodeScanning: false,
            barcodeTarget: null,  // nama field tujuan hasil scan
            barcodeStream: null,
            html5Scanner: null,
            barcodeFacingMode: 'environment',
            barcodeMultipleCameras: false,
            barcodeTorchAvailable: false,
            barcodeTorchOn: false,
            barcodeZoomAvailable: false,
            barcodeZoomLevel: 1,
            barcodeZoomMin: 1,
            barcodeZoomMax: 3,
            barcodeLastRead: '',
            barcodeWarning: '',
            barcodeSuccess: '',
            barcodeProcessing: false,
            barcodeHiResTimer: null,
            nativeDetector: null,

            get barcodeTargetTitle() {
                switch (this.barcodeTarget) {
                    case 'packing_list_no': return 'No. Surat Jalan / Packing List (Code 128 - 9 Digit Angka)';
                    case 'shipment_group':  return 'Shipment Group (Code 128 - 9 Digit Angka)';
                    case 'shipment_no':     return 'Nomor Shipment / Delivery (Code 128 - 9 Digit Angka)';
                    case 'nomor_container': return 'Nomor Container (Plat/Kode)';
                    case 'nomor_seal':      return 'Nomor Seal (Segel)';
                    default: return (this.barcodeTarget || '').toUpperCase();
                }
            },

            // ── Waktu Mulai Staging (Default Sekarang & Editable) ───
            waktuKedatangan: {{ Js::from(old('waktu_kedatangan_container', isset($shipment) && $shipment->waktu_kedatangan_container ? $shipment->waktu_kedatangan_container->format('Y-m-d H:i') : now('Asia/Jakarta')->format('Y-m-d H:i'))) }},
            fpKedatangan: null,

            // ── Field Tambahan Form ────────────────────────────────
            jenisPengiriman: {{ Js::from(old('jenis_pengiriman', $shipment->jenis_pengiriman ?? 'export')) }},
            cuaca: {{ Js::from(old('cuaca', $shipment->cuaca ?? 'kering')) }},
            waktu: {{ Js::from(old('waktu', $shipment->waktu ?? 'siang')) }},
            tanggalStaging: {{ Js::from(old('tanggal_staging', isset($shipment) && $shipment->tanggal_staging ? $shipment->tanggal_staging->format('Y-m-d') : date('Y-m-d'))) }},

            // ── Google Form-Style Real-time Auto-Save (LocalStorage) ─
            isEditMode: {{ Js::from(isset($shipment)) }},
            autoSaveStatus: 'idle', // 'idle' | 'saving' | 'saved'
            lastSavedText: '',
            hasRestoredDraft: false,

            formatPlatNomor(val) {
                if (!val) return '';
                val = String(val).toUpperCase().trim();
                const cleaned = val.replace(/\s+/g, '');
                const standardMatch = cleaned.match(/^([A-Z]{1,2})([0-9]{1,4})([A-Z]{0,3})$/);
                if (standardMatch) {
                    const parts = [standardMatch[1], standardMatch[2]];
                    if (standardMatch[3]) parts.push(standardMatch[3]);
                    return parts.join(' ');
                }
                const generalMatch = val.match(/^([A-Z]+)\s*([0-9]+)\s*([A-Z]*)$/);
                if (generalMatch) {
                    const parts = [generalMatch[1], generalMatch[2]];
                    if (generalMatch[3]) parts.push(generalMatch[3]);
                    return parts.join(' ');
                }
                return val.replace(/([A-Z])([0-9])/g, '$1 $2')
                          .replace(/([0-9])([A-Z])/g, '$1 $2')
                          .replace(/\s+/g, ' ')
                          .trim();
            },

            init() {
                if (this.platNomor) this.platNomor = this.formatPlatNomor(this.platNomor);
                this.initDatePicker();
                if (!this.isEditMode) {
                    this.loadDraftFromLocalStorage();
                    // Debounce watcher untuk auto-save real-time setiap ada perubahan input
                    let debounceTimer = null;
                    this.$watch('$data', () => {
                        clearTimeout(debounceTimer);
                        debounceTimer = setTimeout(() => {
                            this.saveDraftToLocalStorage();
                        }, 400);
                    });
                }
            },

            saveDraftToLocalStorage() {
                if (this.isEditMode) return;
                // Hanya simpan jika minimal satu field utama terisi
                const hasInput = this.nomorContainer || this.packingListNo || this.shipmentGroup || this.shipmentNo || this.platNomor || this.namaSopir || this.nomorSeal;
                if (!hasInput) return;

                this.autoSaveStatus = 'saving';
                try {
                    const draft = {
                        nomorContainer: this.nomorContainer,
                        shipmentGroup: this.shipmentGroup,
                        shipmentNo: this.shipmentNo,
                        packingListNo: this.packingListNo,
                        platNomor: this.platNomor,
                        namaSopir: this.namaSopir,
                        nomorSeal: this.nomorSeal,
                        agenForwarding: this.agenForwarding,
                        tujuanPengiriman: this.tujuanPengiriman,
                        productChoice: this.productChoice,
                        customProductText: this.customProductText,
                        jenisPengiriman: this.jenisPengiriman,
                        cuaca: this.cuaca,
                        waktu: this.waktu,
                        tanggalStaging: this.tanggalStaging,
                        whType: this.whType,
                        whInternal: this.whInternal,
                        whExternalSelect: this.whExternalSelect,
                        whExternalCustom: this.whExternalCustom,
                        selectedKaryawanIds: this.selectedKaryawanIds,
                        waktuKedatangan: this.waktuKedatangan,
                        ocrRawText: this.ocrRawText,
                        photoPreviewUrl: this.photoPreviewUrl,
                        _savedAt: new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' }) + ' WIB',
                        _timestamp: Date.now()
                    };
                    localStorage.setItem('spv_staging_create_draft_v1', JSON.stringify(draft));
                    this.lastSavedText = 'Semua perubahan disimpan otomatis (' + draft._savedAt + ')';
                    this.autoSaveStatus = 'saved';
                } catch (e) {
                    console.warn('Auto-save error', e);
                    this.autoSaveStatus = 'idle';
                }
            },

            loadDraftFromLocalStorage() {
                if (this.isEditMode) return;
                try {
                    const raw = localStorage.getItem('spv_staging_create_draft_v1');
                    if (!raw) return;
                    const draft = JSON.parse(raw);
                    if (!draft || typeof draft !== 'object') return;

                    const hasInput = draft.nomorContainer || draft.packingListNo || draft.shipmentGroup || draft.shipmentNo || draft.platNomor || draft.namaSopir;
                    if (!hasInput) return;

                    if (draft.nomorContainer) this.nomorContainer = draft.nomorContainer;
                    if (draft.shipmentGroup) this.shipmentGroup = draft.shipmentGroup;
                    if (draft.shipmentNo) this.shipmentNo = draft.shipmentNo;
                    if (draft.packingListNo) this.packingListNo = draft.packingListNo;
                    if (draft.platNomor) this.platNomor = this.formatPlatNomor(draft.platNomor);
                    if (draft.namaSopir) this.namaSopir = draft.namaSopir.toUpperCase();
                    if (draft.nomorSeal) this.nomorSeal = draft.nomorSeal.toUpperCase();
                    if (draft.agenForwarding) this.agenForwarding = draft.agenForwarding.toUpperCase();
                    if (draft.tujuanPengiriman) this.tujuanPengiriman = draft.tujuanPengiriman.toUpperCase();
                    if (draft.productChoice) this.productChoice = draft.productChoice;
                    if (draft.customProductText) this.customProductText = draft.customProductText;
                    if (draft.jenisPengiriman) this.jenisPengiriman = draft.jenisPengiriman;
                    if (draft.cuaca) this.cuaca = draft.cuaca;
                    if (draft.waktu) this.waktu = draft.waktu;
                    if (draft.tanggalStaging) this.tanggalStaging = draft.tanggalStaging;
                    if (draft.whType) this.whType = draft.whType;
                    if (draft.whInternal) this.whInternal = draft.whInternal;
                    if (draft.whExternalSelect) this.whExternalSelect = draft.whExternalSelect;
                    if (draft.whExternalCustom) this.whExternalCustom = draft.whExternalCustom;
                    if (Array.isArray(draft.selectedKaryawanIds)) this.selectedKaryawanIds = draft.selectedKaryawanIds;
                    if (draft.waktuKedatangan) {
                        this.waktuKedatangan = draft.waktuKedatangan;
                        if (this.fpKedatangan) this.fpKedatangan.setDate(this.waktuKedatangan, true);
                    }
                    if (draft.ocrRawText) this.ocrRawText = draft.ocrRawText;
                    if (draft.photoPreviewUrl) this.photoPreviewUrl = draft.photoPreviewUrl;

                    this.hasRestoredDraft = true;
                    this.lastSavedText = 'Draf dipulihkan otomatis (' + (draft._savedAt || 'tersimpan sebelumnya') + ')';
                    this.autoSaveStatus = 'saved';
                } catch (e) {
                    console.warn('Load draft error', e);
                }
            },

            clearDraftAndReset() {
                if (confirm('Kosongkan draf otomatis dan mulai dari formulir bersih?')) {
                    try {
                        localStorage.removeItem('spv_staging_create_draft_v1');
                    } catch (e) {}
                    window.location.reload();
                }
            },

            clearAutoSaveDraft() {
                try {
                    localStorage.removeItem('spv_staging_create_draft_v1');
                } catch (e) {}
            },
            initDatePicker() {
                const self = this;
                const el = document.getElementById('waktu_kedatangan_container');
                if (el && typeof flatpickr !== 'undefined') {
                    this.fpKedatangan = flatpickr(el, {
                        enableTime: true,
                        time_24hr: true,
                        dateFormat: 'Y-m-d H:i',
                        altInput: true,
                        altFormat: 'd/m/Y H:i',
                        altInputClass: 'field-input flatpickr-custom-input',
                        locale: (typeof flatpickr !== 'undefined' && flatpickr.l10ns && flatpickr.l10ns.id) ? flatpickr.l10ns.id : 'default',
                        disableMobile: true,
                        allowInput: true,
                        monthSelectorType: 'static',
                        defaultDate: this.waktuKedatangan || null,
                        onChange(selectedDates, dateStr) {
                            self.waktuKedatangan = dateStr || '';
                        },
                        onClose(selectedDates, dateStr) {
                            self.waktuKedatangan = dateStr || '';
                        }
                    });
                }
            },
            setNowKedatangan() {
                const now = new Date();
                const y = now.getFullYear();
                const m = String(now.getMonth() + 1).padStart(2, '0');
                const d = String(now.getDate()).padStart(2, '0');
                const hh = String(now.getHours()).padStart(2, '0');
                const mm = String(now.getMinutes()).padStart(2, '0');
                const val = `${y}-${m}-${d} ${hh}:${mm}`;
                this.waktuKedatangan = val;
                if (this.fpKedatangan) {
                    this.fpKedatangan.setDate(val, true);
                }
            },
            karyawans: {{ Js::from($karyawans->map(fn($k) => ['id' => (string) $k->id, 'nama' => $k->nama, 'nomor_induk' => $k->nomor_induk, 'position' => $k->position_label ?? ucfirst($k->position ?? 'Operator')])) }},

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
                return this.karyawans.filter(k => k.nama.toLowerCase().includes(q) || k.nomor_induk.toLowerCase().includes(q) || (k.position && k.position.toLowerCase().includes(q)));
            },

            // Validasi 9 digit angka
            get isPackingListValid() {
                return this.packingListNo.trim().length === 9;
            },
            get isShipmentGroupValid() {
                // Shipment Group hanya wajib untuk Export. Untuk Lokal tidak wajib.
                if (this.jenisPengiriman === 'lokal') return true;
                return this.shipmentGroup.trim().length === 9;
            },
            get isShipmentNoValid() {
                return this.shipmentNo.trim().length === 9;
            },

            get isProductValid() {
                if (this.productChoice === 'others') {
                    return this.customProductText.trim().length > 0;
                }
                return (this.productChoice || '').trim().length > 0;
            },

            get isWarehouseValid() {
                if (this.whType === 'external') {
                    return this.whExternal.trim().length > 0;
                }
                return ['atas', 'tengah', 'bawah'].includes(this.whInternal);
            },

            get isValid() {
                return this.isPackingListValid &&
                       this.isShipmentGroupValid &&
                       this.isShipmentNoValid &&
                       this.nomorContainer.trim().length > 0 &&
                       this.platNomor.trim().length > 0 &&
                       this.selectedKaryawanIds.length > 0 &&
                       this.isProductValid &&
                       this.isWarehouseValid;
            },
            get missingFields() {
                const missing = [];
                if (this.packingListNo.length === 0) {
                    missing.push('No. Surat Jalan / Delivery / Packing List (9 digit)');
                } else if (this.packingListNo.length < 9) {
                    missing.push('No. Surat Jalan (baru ' + this.packingListNo.length + '/9 digit)');
                }

                if (this.jenisPengiriman === 'export') {
                    if (this.shipmentGroup.length === 0) {
                        missing.push('Shipment Group (9 digit)');
                    } else if (this.shipmentGroup.length < 9) {
                        missing.push('Shipment Group (baru ' + this.shipmentGroup.length + '/9 digit)');
                    }
                }

                if (this.shipmentNo.length === 0) {
                    missing.push('Shipment No (9 digit)');
                } else if (this.shipmentNo.length < 9) {
                    missing.push('Shipment No (baru ' + this.shipmentNo.length + '/9 digit)');
                }

                if (!this.platNomor.trim()) missing.push('Plat Nomor');
                if (!this.nomorContainer.trim()) missing.push('No. Container');
                if (this.selectedKaryawanIds.length === 0) missing.push('Petugas Lapangan (minimal 1)');
                if (!this.isProductValid) missing.push('Ketik Nama Produk (Others)');
                if (!this.isWarehouseValid) {
                    if (this.whType === 'external') {
                        missing.push('Nama PT Pemilik Warehouse Eksternal');
                    } else {
                        missing.push('Pilih Lokasi Warehouse Internal (Atas/Tengah/Bawah)');
                    }
                }
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
                            this.platNomor = this.formatPlatNomor(data.plat_nomor);
                            extractedItems.push('Plat: ' + this.platNomor);
                        }
                        if (data.container_no) {
                            this.nomorContainer = data.container_no;
                            extractedItems.push('Container: ' + data.container_no);
                        }
                        if (data.nama_sopir) {
                            this.namaSopir = data.nama_sopir;
                            extractedItems.push('Sopir: ' + data.nama_sopir);
                        }
                        if (data.nomor_seal) {
                            this.nomorSeal = data.nomor_seal;
                            extractedItems.push('Seal: ' + data.nomor_seal);
                        }
                        if (data.agen_forwarding) {
                            this.agenForwarding = data.agen_forwarding;
                            extractedItems.push('Forwarder: ' + data.agen_forwarding);
                        }
                        if (data.tujuan_pengiriman) {
                            this.tujuanPengiriman = data.tujuan_pengiriman;
                            extractedItems.push('Tujuan: ' + data.tujuan_pengiriman);
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
            },
            // ── Barcode Scanner Helpers ───────────────────────────
            openBarcodeScanner(targetField) {
                this.barcodeTarget = targetField;
                this.barcodeScanning = true;
                this.barcodeLastRead = '';
                this.barcodeWarning = '';
                this.barcodeSuccess = '';
                this.barcodeProcessing = false;
                this.barcodeTorchOn = false;
                this.barcodeTorchAvailable = false;
                this.barcodeZoomAvailable = false;
                this.barcodeZoomLevel = 1;
                this.$nextTick(() => {
                    this.startBarcodeScan();
                });
            },
            async startBarcodeScan() {
                // Periksa apakah perangkat memiliki lebih dari 1 kamera (depan & belakang)
                if (typeof Html5Qrcode !== 'undefined' && Html5Qrcode.getCameras) {
                    try {
                        const devices = await Html5Qrcode.getCameras();
                        this.barcodeMultipleCameras = devices && devices.length > 1;
                    } catch (e) {
                        this.barcodeMultipleCameras = false;
                    }
                }

                // Opsi 1: Html5Qrcode Scanner (Universal & Terbukti Stabil)
                if (typeof Html5Qrcode !== 'undefined') {
                    try {
                        const readerEl = document.getElementById('barcode-reader-view');
                        if (readerEl) {
                            readerEl.innerHTML = '';
                            
                            // Format logistik prioritas tinggi agar pemindaian sangat responsif dan tidak membebani CPU
                            const fmt = window.Html5QrcodeSupportedFormats || (window.__Html5QrcodeLibrary__ && window.__Html5QrcodeLibrary__.Html5QrcodeSupportedFormats) || {};
                            const priorityFormats = [
                                fmt.CODE_128 ?? 5,
                                fmt.CODE_39 ?? 3,
                                fmt.CODE_93 ?? 4,
                                fmt.ITF ?? 8,
                                fmt.EAN_13 ?? 9,
                                fmt.UPC_A ?? 14,
                                fmt.QR_CODE ?? 0
                            ];

                            this.html5Scanner = new Html5Qrcode('barcode-reader-view', {
                                formatsToSupport: priorityFormats,
                                experimentalFeatures: {
                                    useBarCodeDetectorIfSupported: true
                                },
                                verbose: false
                            });

                            // Bidang pandang lapang agar seluruh garis barcode panjang dan margin putih (quiet zone) tidak terpotong
                            const scanConfig = {
                                fps: 20,
                                qrbox: (viewfinderWidth, viewfinderHeight) => {
                                    const w = Math.min(Math.floor(viewfinderWidth * 0.94), 380);
                                    const h = Math.min(Math.floor(viewfinderHeight * 0.75), 180);
                                    return { width: Math.max(w, 280), height: Math.max(h, 130) };
                                },
                                aspectRatio: 1.333333
                            };

                            const startCamera = async (facing) => {
                                // Meminta resolusi HD/Full HD ke driver kamera agar garis-garis barcode Delivery yang rapat tetap tajam
                                const constraints = {
                                    facingMode: facing,
                                    width: { min: 1280, ideal: 1920 },
                                    height: { min: 720, ideal: 1080 }
                                };
                                try {
                                    return await this.html5Scanner.start(
                                        { facingMode: facing },
                                        { ...scanConfig, videoConstraints: constraints },
                                        (decodedText) => {
                                            this.handleBarcodeResult(decodedText);
                                        },
                                        () => {}
                                    );
                                } catch (hdErr) {
                                    console.warn('HD stream constraint not supported, fallback to standard stream:', hdErr);
                                    return await this.html5Scanner.start(
                                        { facingMode: facing },
                                        scanConfig,
                                        (decodedText) => {
                                            this.handleBarcodeResult(decodedText);
                                        },
                                        () => {}
                                    );
                                }
                            };

                            try {
                                await startCamera(this.barcodeFacingMode || 'environment');
                            } catch (envErr) {
                                console.warn('Kamera environment gagal/tidak ada, beralih ke default webcam/user:', envErr);
                                this.barcodeFacingMode = 'user';
                                await startCamera('user');
                            }

                            // ── Mesin Decoder HD Beresolusi Penuh (Bypass Downsampling Canvas) ──
                            // Html5Qrcode secara default mengecilkan canvas ke lebar CSS elemen (340px)
                            // yang membuat garis tipis barcode Delivery tampak buram/pecah di webcam laptop.
                            // Interval ini memproses frame langsung dari elemen <video> pada resolusi hardware asli (1080p/720p).
                            if (this.barcodeHiResTimer) clearInterval(this.barcodeHiResTimer);
                            const hiResCanvas = document.createElement('canvas');
                            const hiResCtx = hiResCanvas.getContext('2d', { willReadFrequently: true });

                            this.barcodeHiResTimer = setInterval(async () => {
                                if (!this.barcodeScanning || this.barcodeProcessing) return;
                                const video = document.querySelector('#barcode-reader-view video');
                                if (!video || !video.videoWidth || !video.videoHeight || video.readyState < 2) return;

                                // ── Prioritas 1: Akselerasi Hardware Native BarcodeDetector (Chrome Android / HP) ──
                                // Sangat responsif membaca barcode batang Code 128 langsung dari stream GPU video
                                if (window.BarcodeDetector) {
                                    try {
                                        if (!this.nativeDetector) {
                                            this.nativeDetector = new BarcodeDetector({
                                                formats: ['code_128', 'code_39', 'code_93', 'itf', 'ean_13', 'qr_code']
                                            });
                                        }
                                        const codes = await this.nativeDetector.detect(video);
                                        if (codes && codes.length > 0 && codes[0].rawValue) {
                                            this.handleBarcodeResult(codes[0].rawValue);
                                            return;
                                        }
                                    } catch (detErr) {
                                        // BarcodeDetector error / not supported for this frame, continue to canvas fallback
                                    }
                                }

                                const vw = video.videoWidth;
                                const vh = video.videoHeight;
                                const cropW = Math.round(vw * 0.94);
                                const cropH = Math.round(vh * 0.60);
                                const cropX = Math.round((vw - cropW) / 2);
                                const cropY = Math.round((vh - cropH) / 2);

                                hiResCanvas.width = cropW;
                                hiResCanvas.height = cropH;
                                hiResCtx.drawImage(video, cropX, cropY, cropW, cropH, 0, 0, cropW, cropH);

                                try {
                                    if (this.html5Scanner && this.html5Scanner.qrcode) {
                                        const res = await this.html5Scanner.qrcode.decodeRobustlyAsync(hiResCanvas);
                                        if (res && res.text) {
                                            this.handleBarcodeResult(res.text);
                                        }
                                    }
                                } catch (e) {
                                    // Belum terbaca pada frame ini
                                }
                            }, 180);

                            // Periksa kemampuan Torch (senter) dan Zoom optik/digital
                            try {
                                const track = this.html5Scanner.getRunningTrack();
                                if (track && track.getCapabilities) {
                                    const caps = track.getCapabilities();
                                    this.barcodeTorchAvailable = !!caps.torch;
                                    if (caps.zoom) {
                                        this.barcodeZoomAvailable = true;
                                        this.barcodeZoomMin = caps.zoom.min || 1;
                                        this.barcodeZoomMax = caps.zoom.max || 3;
                                    }
                                }
                            } catch (e) {
                                this.barcodeTorchAvailable = false;
                                this.barcodeZoomAvailable = false;
                            }
                            return;
                        }
                    } catch (err) {
                        console.warn('Html5Qrcode scanner failed, fallback to native BarcodeDetector:', err);
                    }
                }

                // Opsi 2: Fallback ke native BarcodeDetector API jika Html5Qrcode gagal
                const video = this.$refs.barcodeVideo;
                if (video && ('BarcodeDetector' in window)) {
                    navigator.mediaDevices.getUserMedia({
                        video: {
                            facingMode: this.barcodeFacingMode || 'environment',
                            width: { ideal: 1280 },
                            height: { ideal: 720 }
                        }
                    })
                    .then(stream => {
                        this.barcodeStream = stream;
                        video.style.display = 'block';
                        video.srcObject = stream;
                        video.play();

                        const track = stream.getVideoTracks()[0];
                        if (track && track.getCapabilities) {
                            const caps = track.getCapabilities();
                            this.barcodeTorchAvailable = !!caps.torch;
                        }

                        const detector = new BarcodeDetector({ formats: ['code_128', 'code_39', 'code_93', 'itf', 'ean_13', 'qr_code', 'data_matrix'] });
                        const scan = () => {
                            if (!this.barcodeScanning || this.barcodeProcessing) return;
                            detector.detect(video).then(codes => {
                                if (codes.length > 0 && codes[0].rawValue) {
                                    this.handleBarcodeResult(codes[0].rawValue);
                                }
                                if (this.barcodeScanning) {
                                    requestAnimationFrame(scan);
                                }
                            }).catch(() => {
                                if (this.barcodeScanning) requestAnimationFrame(scan);
                            });
                        };
                        requestAnimationFrame(scan);
                    })
                    .catch(() => {
                        this.barcodeScanning = false;
                        alert('Tidak dapat membuka kamera. Pastikan izin kamera telah diberikan di browser.');
                    });
                } else {
                    alert('Scanner kamera tidak tersedia. Anda dapat menggunakan tombol "Unggah / Foto Barcode" atau input manual.');
                    this.closeBarcodeScanner();
                }
            },
            async closeBarcodeScanner() {
                this.barcodeScanning = false;
                this.barcodeTorchOn = false;
                this.barcodeZoomLevel = 1;
                this.nativeDetector = null;
                if (this.barcodeHiResTimer) {
                    clearInterval(this.barcodeHiResTimer);
                    this.barcodeHiResTimer = null;
                }
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
                const video = this.$refs.barcodeVideo;
                if (video) {
                    video.style.display = 'none';
                    video.srcObject = null;
                }
            },
            async switchBarcodeCamera() {
                this.barcodeFacingMode = this.barcodeFacingMode === 'environment' ? 'user' : 'environment';
                await this.closeBarcodeScanner();
                this.barcodeScanning = true;
                this.$nextTick(() => {
                    this.startBarcodeScan();
                });
            },
            async toggleBarcodeTorch() {
                if (!this.barcodeTorchAvailable) return;
                try {
                    let track = null;
                    if (this.html5Scanner && this.html5Scanner.getRunningTrack) {
                        track = this.html5Scanner.getRunningTrack();
                    } else if (this.barcodeStream) {
                        track = this.barcodeStream.getVideoTracks()[0];
                    }
                    if (track && track.applyConstraints) {
                        this.barcodeTorchOn = !this.barcodeTorchOn;
                        await track.applyConstraints({
                            advanced: [{ torch: this.barcodeTorchOn }]
                        });
                    }
                } catch (e) {
                    console.warn('Torch toggle failed:', e);
                }
            },
            async toggleBarcodeZoom() {
                if (!this.barcodeZoomAvailable) return;
                try {
                    let track = null;
                    if (this.html5Scanner && this.html5Scanner.getRunningTrack) {
                        track = this.html5Scanner.getRunningTrack();
                    }
                    if (track && track.applyConstraints) {
                        const targetZoom = this.barcodeZoomLevel === 1 ? Math.min(2, this.barcodeZoomMax || 2) : 1;
                        this.barcodeZoomLevel = targetZoom;
                        await track.applyConstraints({
                            advanced: [{ zoom: targetZoom }]
                        });
                    }
                } catch (e) {
                    console.warn('Zoom toggle failed:', e);
                }
            },
            async scanBarcodeFromFile(event) {
                const file = event.target.files?.[0];
                if (!file) return;
                this.barcodeWarning = 'Sedang menganalisis foto barcode...';
                try {
                    // Hentikan live scan camera terlebih dahulu agar tidak bentrok
                    if (this.barcodeHiResTimer) {
                        clearInterval(this.barcodeHiResTimer);
                        this.barcodeHiResTimer = null;
                    }
                    if (this.html5Scanner) {
                        try {
                            await this.html5Scanner.stop();
                        } catch (e) {}
                    }

                    // Prioritas A: Coba BarcodeDetector native jika didukung (sangat akurat untuk file foto Code 128)
                    if (window.BarcodeDetector) {
                        try {
                            const imgBmp = await createImageBitmap(file);
                            const detector = new BarcodeDetector({ formats: ['code_128', 'code_39', 'code_93', 'itf', 'ean_13', 'qr_code'] });
                            const codes = await detector.detect(imgBmp);
                            if (codes && codes.length > 0 && codes[0].rawValue) {
                                this.handleBarcodeResult(codes[0].rawValue);
                                return;
                            }
                        } catch (bdErr) {
                            console.warn('Native BarcodeDetector on image file failed:', bdErr);
                        }
                    }

                    const fmt = window.Html5QrcodeSupportedFormats || (window.__Html5QrcodeLibrary__ && window.__Html5QrcodeLibrary__.Html5QrcodeSupportedFormats) || {};
                    const priorityFormats = [
                        fmt.CODE_128 ?? 5,
                        fmt.CODE_39 ?? 3,
                        fmt.CODE_93 ?? 4,
                        fmt.ITF ?? 8,
                        fmt.EAN_13 ?? 9,
                        fmt.UPC_A ?? 14,
                        fmt.QR_CODE ?? 0
                    ];
                    let scanner = new Html5Qrcode('barcode-reader-view', {
                        formatsToSupport: priorityFormats,
                        experimentalFeatures: { useBarCodeDetectorIfSupported: true },
                        verbose: false
                    });
                    const result = await scanner.scanFile(file, true);
                    if (result) {
                        this.handleBarcodeResult(result);
                    }
                } catch (err) {
                    this.barcodeWarning = 'Barcode tidak terdeteksi pada foto. Pastikan posisi kamera tegak, barcode tidak buram, dan pencahayaan terang.';
                    // Jika belum sukses dan modal masih aktif, hidupkan kembali scanner kamera
                    if (this.barcodeScanning && !this.barcodeSuccess) {
                        setTimeout(() => this.startBarcodeScan(), 1200);
                    }
                } finally {
                    event.target.value = '';
                }
            },
            handleBarcodeResult(raw) {
                if (this.barcodeProcessing) return;
                const cleaned = (raw || '').trim();
                if (!cleaned) return;

                // 1. Target 9 Digit Wajib: packing_list_no, shipment_group, shipment_no
                if (['packing_list_no', 'shipment_group', 'shipment_no'].includes(this.barcodeTarget)) {
                    // Ekstrak semua digit angka
                    const rawDigits = cleaned.replace(/\D/g, '');

                    // Langkah A: Cek pola kata tepat 9 digit yang diawali [1-9] (misal "869026487", "803282245/10", "SJ 869026487")
                    const matchClean9 = cleaned.match(/\b([1-9]\d{8})\b/);
                    if (matchClean9) {
                        this.applyBarcodeSuccess(matchClean9[1]);
                        return;
                    }

                    // Langkah B: Jika barcode mengandung leading zero (khas format 10-digit SAP NUMC / ITF padding)
                    // Contoh nyata: '0869026487' -> hilangkan '0' di depan -> menghasilkan '869026487' (tepat 9 digit)
                    // Contoh lain:  '00803282147' -> hilangkan '00' di depan -> menghasilkan '803282147'
                    const strippedLeadingZeros = rawDigits.replace(/^0+/, '');
                    if (strippedLeadingZeros.length === 9) {
                        this.applyBarcodeSuccess(strippedLeadingZeros);
                        return;
                    }

                    // Langkah C: Jika setelah hilangkan leading zero masih lebih dari 9 digit
                    // (misal barcode dokumen SAP yang menyertakan nomor item / batch, seperti '080328224510' atau '803282245000010'):
                    // Ambil 9 digit utama nomor pengiriman
                    if (strippedLeadingZeros.length > 9) {
                        this.applyBarcodeSuccess(strippedLeadingZeros.substring(0, 9));
                        return;
                    }

                    // Langkah D: Tepat 9 digit angka (jika ada nomor khusus)
                    if (rawDigits.length === 9) {
                        this.applyBarcodeSuccess(rawDigits);
                        return;
                    }

                    // JIKA BUKAN 9 DIGIT (misal barcode material 8 digit seperti "00917698"):
                    // JANGAN langsung menutup scanner! Kamera terus mencari nomor 9 digit Surat Jalan / Delivery
                    this.barcodeLastRead = strippedLeadingZeros || rawDigits || cleaned;
                    this.barcodeWarning = `Terbaca "${cleaned}" (${(strippedLeadingZeros || rawDigits).length} digit). Kolom ini butuh 9 digit nomor pengiriman. Kamera terus mencari...`;
                    return;
                }

                // 2. Target Container
                if (this.barcodeTarget === 'nomor_container') {
                    const upper = cleaned.toUpperCase().replace(/[^A-Z0-9]/g, '');
                    if (upper.length >= 4) {
                        this.applyBarcodeSuccess(upper);
                        return;
                    }
                    this.barcodeLastRead = cleaned;
                    this.barcodeWarning = `Format kontainer tidak valid ("${cleaned}").`;
                    return;
                }

                // 3. Target Nomor Seal atau Field Bebas Lainnya
                if (this.barcodeTarget === 'nomor_seal') {
                    if (cleaned.length >= 2) {
                        this.applyBarcodeSuccess(cleaned);
                        return;
                    }
                    this.barcodeLastRead = cleaned;
                    this.barcodeWarning = `Nomor Seal terlalu pendek ("${cleaned}").`;
                    return;
                }

                this.applyBarcodeSuccess(cleaned);
            },
            applyBarcodeSuccess(value) {
                this.barcodeProcessing = true;
                this.barcodeWarning = '';
                this.barcodeSuccess = value;

                // Haptic feedback getar pada smartphone
                if (typeof navigator !== 'undefined' && navigator.vibrate) {
                    try { navigator.vibrate([40, 50, 40]); } catch (e) {}
                }

                switch (this.barcodeTarget) {
                    case 'packing_list_no': this.packingListNo  = value; break;
                    case 'shipment_group':  this.shipmentGroup  = value; break;
                    case 'shipment_no':     this.shipmentNo     = value; break;
                    case 'nomor_seal':      this.nomorSeal      = value; break;
                    case 'nomor_container': this.nomorContainer = value.toUpperCase(); break;
                    default: break;
                }

                // Jeda 450ms agar operator melihat indikator centang hijau sukses sebelum modal tertutup
                setTimeout(() => {
                    this.closeBarcodeScanner();
                    this.barcodeProcessing = false;
                }, 450);
            },
            forceApplyBarcode(val) {
                if (!val) return;
                let digits = val.replace(/\D/g, '');
                if (['packing_list_no', 'shipment_group', 'shipment_no'].includes(this.barcodeTarget)) {
                    const stripped = digits.replace(/^0+/, '');
                    if (stripped.length >= 9) {
                        digits = stripped.slice(0, 9);
                    } else if (digits.length > 9) {
                        digits = digits.slice(0, 9);
                    }
                }
                switch (this.barcodeTarget) {
                    case 'packing_list_no': this.packingListNo  = digits || val; break;
                    case 'shipment_group':  this.shipmentGroup  = digits || val; break;
                    case 'shipment_no':     this.shipmentNo     = digits || val; break;
                    case 'nomor_seal':      this.nomorSeal      = val; break;
                    case 'nomor_container': this.nomorContainer = val.toUpperCase(); break;
                    default: break;
                }
                this.closeBarcodeScanner();
            }
        };
    }
    </script>

    <form action="{{ isset($shipment) ? route('field-app.update', $shipment->id, false) : route('field-app.store', [], false) }}" method="POST" enctype="multipart/form-data"
          x-data="stagingFormData()"
          @submit="clearAutoSaveDraft()"
          style="display: flex; flex-direction: column; gap: 24px;">
        @csrf
        @if(isset($shipment))
            @method('PUT')
        @endif

        <input type="hidden" name="ocr_raw_text" :value="ocrRawText">

        <!-- ═══════════════════════════════════════════════════════════ -->
        <!-- GOOGLE FORM STYLE REALTIME AUTO-SAVE STATUS & RECOVERY BAR   -->
        <!-- ═══════════════════════════════════════════════════════════ -->
        @if(!isset($shipment))
            <div style="background: white; border: 1.5px solid #e2e8f0; border-radius: 14px; padding: 10px 14px; box-shadow: 0 1px 4px rgba(40,84,145,0.04); display: flex; align-items: center; justify-content: space-between; gap: 8px; font-size: 11px;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <template x-if="autoSaveStatus === 'saving'">
                        <span style="display: inline-flex; align-items: center; gap: 6px; color: #2563eb; font-weight: 700;">
                            <i class="ph-bold ph-arrows-clockwise" style="animation: spin 1s linear infinite; font-size: 14px;"></i>
                            Menyimpan draf...
                        </span>
                    </template>
                    <template x-if="autoSaveStatus === 'saved'">
                        <span style="display: inline-flex; align-items: center; gap: 6px; color: #059e3d; font-weight: 700;">
                            <i class="ph-fill ph-cloud-check" style="font-size: 17px; color: #059e3d;"></i>
                            <span x-text="lastSavedText || 'Semua perubahan tersimpan otomatis'"></span>
                        </span>
                    </template>
                    <template x-if="autoSaveStatus === 'idle'">
                        <span style="display: inline-flex; align-items: center; gap: 6px; color: #64748b; font-weight: 500;">
                            <i class="ph-bold ph-cloud" style="font-size: 15px; color: #94a3b8;"></i>
                            Auto-Save Aktif: Isian form otomatis tersimpan saat Anda mengetik
                        </span>
                    </template>
                </div>

                <div style="display: flex; align-items: center; gap: 6px;">
                    <template x-if="autoSaveStatus === 'saved' || hasRestoredDraft">
                        <button type="button" @click="clearDraftAndReset()"
                                title="Kosongkan draf otomatis dan mulai dari formulir bersih"
                                style="background: #fef2f2; border: 1px solid #fecaca; color: #dc2626; border-radius: 8px; padding: 4px 9px; font-size: 10px; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 4px; transition: all 0.15s;"
                                onmouseover="this.style.background='#fee2e2'"
                                onmouseout="this.style.background='#fef2f2'">
                            <i class="ph-bold ph-trash"></i>
                            Reset Draf
                        </button>
                    </template>
                </div>
            </div>

            <!-- Banner Notifikasi Saat Draf Berhasil Dipulihkan Dari Sesi Sebelumnya -->
            <div x-cloak x-show="hasRestoredDraft" x-transition
                 style="background: #eff6ff; border: 1.5px solid #bfdbfe; border-radius: 14px; padding: 12px 14px; display: flex; align-items: center; justify-content: space-between; gap: 10px;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div style="width: 32px; height: 32px; border-radius: 10px; background: #2563eb; color: white; display: flex; align-items: center; justify-content: center; flex-shrink: 0; box-shadow: 0 2px 6px rgba(37,99,235,0.25);">
                        <i class="ph-bold ph-arrow-counter-clockwise" style="font-size: 18px;"></i>
                    </div>
                    <div>
                        <p style="font-size: 12px; font-weight: 700; color: #1e40af; margin: 0;">
                            Draf Formulir Sebelumnya Berhasil Dipulihkan!
                        </p>
                        <p style="font-size: 11px; color: #3b82f6; margin: 2px 0 0 0;">
                            Data yang Anda ketik sebelum sesi tertutup/ter-logout telah otomatis dimuat kembali. Anda dapat langsung melanjutkan pengisian form.
                        </p>
                    </div>
                </div>
                <button type="button" @click="hasRestoredDraft = false"
                        style="background: transparent; border: none; color: #94a3b8; cursor: pointer; padding: 4px; font-size: 18px; display: flex; align-items: center;"
                        title="Tutup pemberitahuan">
                    <i class="ph-bold ph-x"></i>
                </button>
            </div>
        @endif

        <!-- ═══════════════════════════════════════════════════════════ -->
        <!-- SECTION 1: DETAIL DATA STAGING                              -->
        <!-- ═══════════════════════════════════════════════════════════ -->
        <div class="section-card" x-data="{ open: true }">
            <div class="section-card-header" @click="open = !open"
                 style="background: linear-gradient(90deg, rgba(5,158,61,0.06), transparent);">
                <div style="width: 34px; height: 34px; border-radius: 10px; background: #e1f8eb; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <i class="ph-fill ph-package" style="font-size: 18px; color: #059e3d;"></i>
                </div>
                <div style="flex: 1;">
                    <div style="display:flex; align-items:center; gap:6px; flex-wrap:wrap;">
                        <p style="font-size: 13px; font-weight: 700; color: #1f2937; margin: 0;">1. Detail Data Staging</p>
                        <span style="font-size: 10px; font-weight: 700; color: #059e3d; background: #e1f8eb; border: 1px solid #bbf7d0; border-radius: 6px; padding: 1px 7px;">Tahap Awal</span>
                    </div>
                    <p style="font-size: 10px; color: #64748b; margin: 2px 0 0;">Jenis pengiriman, produk, waktu mulai staging, cuaca &amp; lokasi warehouse</p>
                </div>
                <i class="ph-bold" :class="open ? 'ph-caret-up' : 'ph-caret-down'" style="font-size: 14px; color: #9ca3af;"></i>
            </div>

            <div class="section-card-body" x-show="open" x-collapse style="display: flex; flex-direction: column; gap: 14px;">

                <!-- 1. Waktu Mulai Staging (Container Tiba) -->
                <div style="background: #f8fafc; border: 1.5px solid #cbd5e1; border-radius: 12px; padding: 12px; transition: all 0.2s;"
                     :style="waktuKedatangan ? 'border-color: #86efac; background: #f0fdf4;' : ''">
                    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:6px;">
                        <label class="field-label" style="margin-bottom:0; display:flex; align-items:center; gap:5px; font-weight:700; color:#1e293b;">
                            <i class="ph-bold ph-calendar-check" style="color:#059e3d; font-size:16px;"></i>
                            Waktu Mulai Staging <span style="color:#ef4444;">*</span>
                            <span style="font-size:10px; font-weight:700; color:#285491; background:#e0ecfb; border:1px solid #bfcfe8; border-radius:6px; padding:1px 6px; margin-left:4px;">
                                Format 24 Jam
                            </span>
                        </label>
                        <button type="button" @click="setNowKedatangan()"
                                style="font-size:10px; font-weight:700; color:#059e3d; background:#e1f8eb; border:1px solid #bbf7d0; border-radius:6px; padding:3px 8px; cursor:pointer; display:flex; align-items:center; gap:3px; transition:background 0.15s;"
                                onmouseover="this.style.background='#cbf4dc'"
                                onmouseout="this.style.background='#e1f8eb'">
                            <i class="ph-bold ph-clock"></i> Set Sekarang
                        </button>
                    </div>
                    <div style="position:relative;">
                        <input type="text" id="waktu_kedatangan_container" name="waktu_kedatangan_container"
                               value="{{ old('waktu_kedatangan_container', isset($shipment) && $shipment->waktu_kedatangan_container ? $shipment->waktu_kedatangan_container->format('Y-m-d H:i') : now('Asia/Jakarta')->format('Y-m-d H:i')) }}"
                               placeholder="Pilih tanggal &amp; jam format 24 jam (contoh: {{ now('Asia/Jakarta')->format('d/m/Y H:i') }})..."
                               class="field-input flatpickr-custom-input"
                               style="width:100%; box-sizing:border-box; background:white; font-size:13px; font-weight:600; padding-right:38px; cursor:pointer;"
                               required>
                        <div style="position:absolute; right:12px; top:50%; transform:translateY(-50%); pointer-events:none; color:#059e3d; display:flex; align-items:center;">
                            <i class="ph-bold ph-calendar-blank" style="font-size:17px;"></i>
                        </div>
                    </div>
                    <p style="font-size:10px; color:#64748b; margin-top:4px;">
                        Waktu saat container tiba di lokasi (format 24 jam: 00:00 - 23:59 WIB). Otomatis terisi saat form dibuka dan dapat diubah.
                    </p>
                </div>

                <!-- 2. Jenis Pengiriman & Produk -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div>
                        <label class="field-label" style="display:flex; align-items:center; gap:4px; font-weight:700;">
                            <i class="ph-bold ph-airplane-tilt" style="color:#285491; font-size:13px;"></i>
                            Jenis Pengiriman <span style="color:#ef4444;">*</span>
                        </label>
                        <select name="jenis_pengiriman" x-model="jenisPengiriman" class="field-input" style="font-weight: 700;">
                            <option value="export">🚢 Export</option>
                            <option value="lokal">🚚 Lokal</option>
                        </select>
                        <p style="font-size: 10px; color: #64748b; margin-top: 3px;">
                            Pilih Export (ada Shipment Group &amp; Seal) atau Lokal.
                        </p>
                    </div>
                    <div>
                        <label class="field-label" style="display: flex; align-items: center; justify-content: space-between;">
                            <span style="font-weight:700;">Produk <span style="color:#ef4444;">*</span></span>
                            <span x-show="productChoice === 'others'" style="font-size: 10px; color: #285491; font-weight: 700;">Wajib ketik nama produk</span>
                        </label>
                        <select name="product_type_select" x-model="productChoice" class="field-input" style="font-weight: 600;">
                            <option value="fiber">Fiber</option>
                            <option value="sodium">Sodium</option>
                            <option value="sliver">Sliver</option>
                            <option value="yarn">Yarn</option>
                            <option value="others">Others (Lainnya)</option>
                        </select>
                        <input type="hidden" name="jenis_produk" :value="productChoice === 'others' ? customProductText.trim() : productChoice">

                        <!-- Manual Text Input saat Others dipilih -->
                        <div x-cloak x-show="productChoice === 'others'" style="margin-top: 8px;">
                            <input type="text"
                                   name="jenis_produk_custom"
                                   x-model="customProductText"
                                   placeholder="Ketik jenis produk di sini (contoh: Chemical, Cotton)..."
                                   class="field-input"
                                   style="border-color: #285491; background: #f0f7ff; font-weight: 600;">
                        </div>
                    </div>
                </div>

                <!-- 3. Cuaca & Waktu -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div>
                        <label class="field-label">Cuaca</label>
                        <select name="cuaca" x-model="cuaca" class="field-input">
                            <option value="kering">☀️ Kering</option>
                            <option value="mendung">☁️ Mendung</option>
                            <option value="hujan">🌧️ Hujan</option>
                            <option value="gerimis">🌦️ Gerimis</option>
                        </select>
                    </div>
                    <div>
                        <label class="field-label">Waktu</label>
                        <select name="waktu" x-model="waktu" class="field-input">
                            <option value="siang">🌤️ Siang</option>
                            <option value="sore">🌅 Sore</option>
                            <option value="malam">🌙 Malam</option>
                        </select>
                    </div>
                </div>

                <!-- 4. Tanggal Staging & Kategori Warehouse -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 2px;">
                    <div>
                        <label class="field-label" style="display:flex; align-items:center; gap:5px;">
                            <i class="ph-bold ph-calendar-check" style="color:#059e3d; font-size:13px;"></i>
                            Tanggal Staging <span style="color:#ef4444;">*</span>
                        </label>
                        <input type="date"
                               name="tanggal_staging"
                               x-model="tanggalStaging"
                               required
                               min="{{ date('Y-m-d') }}"
                               class="field-input"
                               style="font-weight: 700;"
                               title="Tanggal staging tidak boleh mundur dari hari ini">
                        <p style="font-size: 10px; color: #059e3d; margin-top: 3px; display: flex; align-items: center; gap: 4px; font-weight: 600;">
                            <i class="ph-bold ph-lock" style="font-size: 11px;"></i>
                            Tanggal tidak bisa dipilih mundur (backdate)
                        </p>
                    </div>
                    <div>
                        <label class="field-label">Kategori Warehouse <span style="color:#ef4444;">*</span></label>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 6px;">
                            <button type="button" 
                                    @click="whType = 'internal'"
                                    :style="whType === 'internal' ? 'border: 2px solid #00994d; background: #f0fdf4; color: #166534; font-weight: 700;' : 'border: 1px solid #cbd5e1; background: #fff; color: #475569; font-weight: 500;'"
                                    style="display: flex; align-items: center; justify-content: center; gap: 6px; padding: 8px 10px; border-radius: 10px; cursor: pointer; font-size: 11px; transition: all 0.15s;">
                                <i class="ph-bold ph-buildings" style="font-size: 14px;"></i>
                                <span>Internal SPV</span>
                            </button>
                            <button type="button" 
                                    @click="whType = 'external'"
                                    :style="whType === 'external' ? 'border: 2px solid #285491; background: #eff6ff; color: #1e40af; font-weight: 700;' : 'border: 1px solid #cbd5e1; background: #fff; color: #475569; font-weight: 500;'"
                                    style="display: flex; align-items: center; justify-content: center; gap: 6px; padding: 8px 10px; border-radius: 10px; cursor: pointer; font-size: 11px; transition: all 0.15s;">
                                <i class="ph-bold ph-truck" style="font-size: 14px;"></i>
                                <span>Eksternal</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Hidden inputs to submit unified warehouse data -->
                <input type="hidden" name="warehouse_type" :value="whType">
                <input type="hidden" name="warehouse_lokasi" :value="whType === 'internal' ? whInternal : whExternal">
                <input type="hidden" name="warehouse_lokasi_external" :value="whExternal">

                <!-- Lokasi Warehouse Internal SPV -->
                <div x-show="whType === 'internal'" x-transition style="margin-top: 2px;">
                    <label class="field-label">Lokasi Warehouse Internal SPV <span style="color:#ef4444;">*</span></label>
                    <select name="warehouse_lokasi_internal" x-model="whInternal" class="field-input">
                        <option value="atas">Gudang Atas</option>
                        <option value="tengah">Gudang Tengah</option>
                        <option value="bawah">Gudang Bawah</option>
                    </select>
                    <p style="font-size: 10px; color: #64748b; margin-top: 4px;">Pilih salah satu zona gudang internal PT SPV (Atas, Tengah, atau Bawah).</p>
                </div>

                <!-- Lokasi Warehouse Eksternal -->
                <div x-cloak x-show="whType === 'external'" x-transition style="margin-top: 2px;">
                    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom: 4px;">
                        <label class="field-label" style="display:flex; align-items:center; gap:5px; margin-bottom: 0;">
                            <i class="ph-bold ph-warehouse" style="color:#285491; font-size:14px;"></i>
                            Nama PT / Pemilik Warehouse Eksternal <span style="color:#ef4444;">*</span>
                        </label>
                    </div>

                    <div style="position: relative;">
                        <select x-model="whExternalSelect"
                                @change="handleWhExternalSelectChange()"
                                class="field-input"
                                style="border-color: #285491; background: white; font-weight: 600; cursor: pointer;">
                            <option value="">-- Pilih Warehouse Eksternal --</option>
                            @foreach($externalWarehouses ?? ($history['external_warehouse'] ?? []) as $extWh)
                                <option value="{{ $extWh }}">{{ $extWh }}</option>
                            @endforeach
                            <option value="__custom__">➕ Lainnya (Ketik Nama PT Baru)</option>
                        </select>
                    </div>

                    <div x-cloak x-show="whExternalSelect === '__custom__'" x-transition style="margin-top: 8px;">
                        <label class="field-label" style="font-size: 11px; color: #059e3d; display: flex; align-items: center; gap: 4px; margin-bottom: 3px;">
                            <i class="ph-bold ph-pencil-line"></i> Ketik Nama PT / Rekanan Eksternal Baru <span style="color:#ef4444;">*</span>
                        </label>
                        <input type="text"
                               id="wh-custom-input"
                               x-model="whExternalCustom"
                               placeholder="Contoh: PT Surya Logistik Sentosa..."
                               class="field-input"
                               style="border-color: #059e3d; background: #fff; font-weight: 600;">
                        <p style="font-size: 10px; color: #059e3d; margin-top: 3px; display: flex; align-items: center; gap: 4px;">
                            <i class="ph-bold ph-check"></i> Nama PT baru ini akan otomatis tercatat dan tersimpan di database.
                        </p>
                    </div>

                    <p style="font-size: 10px; color: #64748b; margin-top: 5px;">
                        Pilih dari daftar master rekanan gudang eksternal atau pilih "Lainnya" untuk mengetik nama PT baru.
                    </p>
                </div>
            </div>
        </div>

        <!-- ═══════════════════════════════════════════════════════════ -->
        <!-- SECTION 2: DOKUMEN SHIPMENT ORDER & DATA PENGIRIMAN        -->
        <!-- ═══════════════════════════════════════════════════════════ -->
        <div class="section-card" x-data="{ open: true }">
            <div class="section-card-header" @click="open = !open"
                 style="background: linear-gradient(90deg, rgba(40,84,145,0.04), transparent);">
                <div style="width: 34px; height: 34px; border-radius: 10px; background: #eff4fc; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <i class="ph-bold ph-file-text" style="font-size: 18px; color: #285491;"></i>
                </div>
                <div style="flex: 1;">
                    <div style="display:flex; align-items:center; gap:6px; flex-wrap:wrap;">
                        <p style="font-size: 13px; font-weight: 700; color: #1f2937; margin: 0;">2. Dokumen Shipment Order &amp; Data Pengiriman</p>
                        <span style="font-size: 10px; font-weight: 700; color: #285491; background: #eff4fc; padding: 2px 7px; border-radius: 6px;">OCR Auto-Fill</span>
                    </div>
                    <p style="font-size: 10px; color: #64748b; margin: 2px 0 0;">Foto dokumen (opsional) atau isi formulir data pengiriman di bawah</p>
                </div>
                <i class="ph-bold" :class="open ? 'ph-caret-up' : 'ph-caret-down'" style="font-size: 14px; color: #9ca3af;"></i>
            </div>

            <div class="section-card-body" x-show="open" x-collapse style="display: flex; flex-direction: column; gap: 14px;">

                <!-- Hidden inputs for file upload -->
                <input type="file" name="shipment_order_photo" x-ref="orderCameraInput" accept="image/*" capture="environment" style="display: none;" @change="handlePhotoChange($event)">
                <input type="file" name="shipment_order_photo_gallery" x-ref="orderGalleryInput" accept="image/*" style="display: none;" @change="handlePhotoChange($event)">

                <!-- Compact, Simple OCR / Document Box -->
                <div style="background: #f8fafc; border: 1.5px dashed #cbd5e1; border-radius: 12px; padding: 12px 14px; display: flex; flex-direction: column; gap: 8px;">
                    <div style="display: flex; align-items: center; justify-content: space-between; gap: 10px; flex-wrap: wrap;">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <template x-if="photoPreviewUrl">
                                <div @click="zoomModalOpen = true"
                                     style="width: 48px; height: 48px; border-radius: 8px; overflow: hidden; background: #0f172a; cursor: pointer; flex-shrink: 0; border: 1px solid #cbd5e1; display: flex; align-items: center; justify-content: center; position: relative;"
                                     title="Klik untuk memperbesar foto">
                                    <img :src="photoPreviewUrl" alt="Shipment Order" style="max-width: 100%; max-height: 100%; object-fit: cover;">
                                    <div style="position: absolute; inset: 0; background: rgba(0,0,0,0.3); display: flex; align-items: center; justify-content: center; opacity: 0; transition: opacity 0.15s;"
                                         onmouseover="this.style.opacity=1" onmouseout="this.style.opacity=0">
                                        <i class="ph-bold ph-magnifying-glass-plus" style="color: white; font-size: 16px;"></i>
                                    </div>
                                </div>
                            </template>
                            <template x-if="!photoPreviewUrl">
                                <div style="width: 40px; height: 40px; border-radius: 10px; background: #eff4fc; color: #285491; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                    <i class="ph-bold ph-scan" style="font-size: 20px;"></i>
                                </div>
                            </template>
                            <div>
                                <div style="display: flex; align-items: center; gap: 6px; flex-wrap: wrap;">
                                    <span style="font-size: 12px; font-weight: 700; color: #1e293b;">Foto Dokumen Shipment Order</span>
                                    <span style="font-size: 10px; color: #64748b; font-weight: 500;">(Opsional / OCR)</span>
                                </div>
                                <p style="font-size: 10px; color: #64748b; margin: 2px 0 0;" x-show="!photoPreviewUrl">
                                    Bantu auto-fill lewat foto kamera/galeri, atau langsung ketik formulir di bawah.
                                </p>
                                <p style="font-size: 10px; color: #059e3d; margin: 2px 0 0; font-weight: 600;" x-show="photoPreviewUrl && !isOcrLoading">
                                    ✓ Foto siap &amp; tersimpan
                                </p>
                            </div>
                        </div>

                        <!-- Action Buttons: Kamera, Galeri, Hapus -->
                        <div style="display: flex; align-items: center; gap: 6px; flex-wrap: wrap;">
                            <button type="button" @click="$refs.orderCameraInput.click()"
                                    style="display: inline-flex; align-items: center; gap: 4px; padding: 6px 11px; font-size: 11px; font-weight: 700; border-radius: 8px; background: #059e3d; color: white; border: none; cursor: pointer; transition: background 0.15s; box-shadow: 0 1px 3px rgba(5,158,61,0.2);"
                                    onmouseover="this.style.background='#047857'" onmouseout="this.style.background='#059e3d'">
                                <i class="ph-bold ph-camera"></i>
                                <span x-text="photoPreviewUrl ? 'Foto Ulang' : 'Kamera'"></span>
                            </button>
                            <button type="button" @click="$refs.orderGalleryInput.click()"
                                    style="display: inline-flex; align-items: center; gap: 4px; padding: 6px 11px; font-size: 11px; font-weight: 700; border-radius: 8px; background: #eff4fc; color: #285491; border: 1.5px solid #bfcfe8; cursor: pointer; transition: all 0.15s;"
                                    onmouseover="this.style.background='#dbe7f6'; this.style.borderColor='#285491'" onmouseout="this.style.background='#eff4fc'; this.style.borderColor='#bfcfe8'">
                                <i class="ph-bold ph-folder-open"></i>
                                <span x-text="photoPreviewUrl ? 'Ganti Galeri' : 'Galeri'"></span>
                            </button>
                            <button type="button" x-show="photoPreviewUrl" @click="removePhoto()"
                                    style="display: inline-flex; align-items: center; gap: 4px; padding: 6px 8px; font-size: 11px; font-weight: 600; border-radius: 8px; background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; cursor: pointer;"
                                    title="Hapus Foto Dokumen">
                                <i class="ph-bold ph-trash"></i>
                            </button>
                        </div>
                    </div>

                    <!-- OCR Mini Loading -->
                    <div x-show="isOcrLoading" style="display: flex; align-items: center; gap: 8px; background: #eff4fc; padding: 6px 10px; border-radius: 8px; border: 1px solid #bfcfe8;">
                        <i class="ph-bold ph-spinner" style="font-size: 15px; color: #285491; animation: spin 1s linear infinite; flex-shrink: 0;"></i>
                        <span style="font-size: 11px; color: #285491; font-weight: 600;">Membaca teks dokumen (Tesseract OCR)...</span>
                    </div>

                    <!-- OCR Mini Status -->
                    <div x-show="ocrStatus === 'success' && !isOcrLoading" style="display: flex; align-items: center; gap: 6px; background: #ecfdf5; padding: 5px 10px; border-radius: 8px; border: 1px solid #a7f3d0; font-size: 11px; color: #065f46; font-weight: 600;">
                        <i class="ph-fill ph-check-circle" style="color: #059e3d; font-size: 14px;"></i>
                        <span x-text="ocrMessage"></span>
                    </div>
                    <div x-show="ocrStatus === 'warning' && !isOcrLoading" style="display: flex; align-items: center; gap: 6px; background: #fffbeb; padding: 5px 10px; border-radius: 8px; border: 1px solid #fde68a; font-size: 11px; color: #92400e; font-weight: 600;">
                        <i class="ph-fill ph-info" style="color: #d97706; font-size: 14px;"></i>
                        <span x-text="ocrMessage"></span>
                    </div>
                </div>

                <!-- 1. Nomor Surat Jalan / Delivery / Packing List (Fix 9 Digit Angka) -->
                <div style="background: white; border: 1.5px solid #bfcfe8; border-radius: 12px; padding: 12px;"
                     :style="packingListNo.length === 9 ? 'border-color:#86efac; background:#f0fdf4;' : (packingListNo.length > 0 ? 'border-color:#fde68a; background:#fffdf5;' : '')">
                    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:4px;">
                        <label class="field-label" style="display:flex; align-items:center; gap:5px; font-weight:700; color:#1e3a8a; font-size:12px; margin-bottom:0;">
                            <i class="ph-bold ph-barcode" style="color:#285491; font-size:15px;"></i>
                            No. Surat Jalan / Delivery / Packing List <span style="color:#ef4444;">*</span>
                        </label>
                        <div style="display:flex; align-items:center; gap:6px;">
                            <button type="button" @click="openBarcodeScanner('packing_list_no')"
                                    style="display:inline-flex; align-items:center; gap:3px; font-size:10px; font-weight:700; color:#285491; background:#e0ecfb; border:1px solid #bfcfe8; border-radius:6px; padding:2px 7px; cursor:pointer;"
                                    title="Scan Barcode Batang Surat Jalan">
                                <i class="ph-bold ph-barcode"></i> Scan
                            </button>
                            <span style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 10px; font-weight: 700; padding: 2px 7px; border-radius: 6px; letter-spacing: 0.02em; font-variant-numeric: tabular-nums; display: inline-flex; align-items: center;"
                                  :style="packingListNo.length === 9 ? 'background:#dcfce7; color:#15803d; border:1px solid #86efac;' : (packingListNo.length > 0 ? 'background:#fef3c7; color:#b45309; border:1px solid #fde68a;' : 'background:#eff4fc; color:#285491; border:1px solid #bfcfe8;')">
                                <span x-text="packingListNo.length"></span>/9 Digit
                            </span>
                        </div>
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
                                <button type="button" 
                                        @click="packingListNo = ($el.dataset.val || '').replace(/\D/g, '').slice(0, 9)"
                                        data-val="{{ $rec }}"
                                        title="Klik untuk mengisi: {{ $rec }}"
                                        style="font-size:10px; font-weight: 600; padding:2px 7px; border-radius:10px; background:#fff; color:#285491; border:1px solid #bfcfe8; cursor:pointer;"
                                        onmouseover="this.style.background='#eff4fc'" onmouseout="this.style.background='#fff'">
                                    {{ $rec }}
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- 2. Plat Nomor & Nomor Container -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <!-- Plat Nomor -->
                    <div>
                        <label class="field-label" style="display:flex; align-items:center; gap:4px;">
                            <i class="ph-bold ph-car" style="color:#285491; font-size:12px;"></i>Plat Nomor <span style="color:#ef4444;">*</span>
                        </label>
                        <input type="text" id="plat_nomor" name="plat_nomor" x-model="platNomor"
                               @blur="platNomor = formatPlatNomor(platNomor)"
                               @change="platNomor = formatPlatNomor(platNomor)"
                               @input="platNomor = $event.target.value.toUpperCase()"
                               list="history-plat"
                               required placeholder="Contoh: B 8976 UIY" class="field-input" style="font-size:12px; font-weight: 600; text-transform:uppercase;">
                        <datalist id="history-plat">
                            @foreach($history['plat_nomor'] ?? [] as $item)
                                <option value="{{ $item }}">
                            @endforeach
                        </datalist>
                        @if(count($history['plat_nomor'] ?? []) > 0)
                            <div style="display:flex; flex-wrap:wrap; gap:4px; margin-top:5px;">
                                <span style="font-size:9px; color:#9ca3af; align-self:center;">Riwayat:</span>
                                @foreach(($history['plat_nomor'] ?? collect())->take(2) as $rec)
                                    <button type="button" 
                                            @click="platNomor = formatPlatNomor($el.dataset.val)"
                                            data-val="{{ $rec }}"
                                            title="Klik untuk mengisi: {{ $rec }}"
                                            style="font-size:10px; font-weight: 600; padding:2px 7px; border-radius:10px; background:#eff4fc; color:#285491; border:1px solid #d0e0f5; cursor:pointer;"
                                            onmouseover="this.style.background='#dbeafe'; this.style.borderColor='#93c5fd'"
                                            onmouseout="this.style.background='#eff4fc'; this.style.borderColor='#d0e0f5'">
                                        {{ $rec }}
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <!-- Nomor Container -->
                    <div>
                        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom: 3px;">
                            <label class="field-label" style="display:flex; align-items:center; gap:4px; margin-bottom: 0;">
                                <i class="ph-bold ph-truck" style="color:#285491; font-size:12px;"></i>No. Container <span style="color:#ef4444;">*</span>
                            </label>
                            <button type="button" @click="openBarcodeScanner('nomor_container')"
                                    style="display:inline-flex; align-items:center; gap:3px; font-size:10px; font-weight:700; color:#285491; background:#eff4fc; border:1px solid #bfcfe8; border-radius:6px; padding:2px 7px; cursor:pointer;"
                                    title="Scan Barcode Container">
                                <i class="ph-bold ph-barcode"></i> Scan
                            </button>
                        </div>
                        <input type="text" id="nomor_container_atau_plat" name="nomor_container_atau_plat"
                               x-model="nomorContainer"
                               @input="nomorContainer = $event.target.value.toUpperCase()"
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
                                    <button type="button" 
                                            @click="nomorContainer = $el.dataset.val"
                                            data-val="{{ $rec }}"
                                            title="Klik untuk mengisi: {{ $rec }}"
                                            style="font-size:10px; font-weight: 600; padding:2px 7px; border-radius:10px; background:#eff4fc; color:#285491; border:1px solid #d0e0f5; cursor:pointer;"
                                            onmouseover="this.style.background='#dbeafe'; this.style.borderColor='#93c5fd'"
                                            onmouseout="this.style.background='#eff4fc'; this.style.borderColor='#d0e0f5'">
                                        {{ $rec }}
                                    </button>
                                @endforeach
                            @endif
                        </div>
                    </div>
                </div>

                <!-- 3. Agen Forwarding & Tujuan Pengiriman (Customer) -->
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 12px;">
                    <!-- Agen Forwarding -->
                    <div>
                        <label class="field-label" style="display:flex; align-items:center; gap:4px; margin-bottom: 4px;">
                            <i class="ph-bold ph-buildings" style="color:#285491; font-size:12px;"></i>Agen Forwarding (Opsional)
                        </label>
                        <textarea id="agen_forwarding"
                                  name="agen_forwarding"
                                  x-model="agenForwarding"
                                  @input="agenForwarding = $event.target.value.toUpperCase()"
                                  rows="2"
                                  placeholder="Contoh: PT. SAMUDERA LOGISTICS / EMKL..."
                                  class="field-input"
                                  style="font-size:12px; font-weight: 600; resize: vertical; min-height: 52px; line-height: 1.4; text-transform:uppercase;"></textarea>
                        @if(count($history['agen_forwarding'] ?? []) > 0)
                            <div style="display:flex; flex-wrap:wrap; gap:4px; margin-top:5px;">
                                <span style="font-size:9px; color:#9ca3af; align-self:center;">Riwayat:</span>
                                @foreach(($history['agen_forwarding'] ?? collect())->take(2) as $rec)
                                    <button type="button" 
                                            @click="agenForwarding = $el.dataset.val"
                                            data-val="{{ $rec }}"
                                            title="Klik untuk mengisi riwayat ini"
                                            style="font-size:10px; font-weight: 600; padding:2px 7px; border-radius:10px; background:#eff4fc; color:#285491; border:1px solid #d0e0f5; cursor:pointer; text-align:left; max-width:100%; word-break:break-word;"
                                            onmouseover="this.style.background='#dbeafe'; this.style.borderColor='#93c5fd'"
                                            onmouseout="this.style.background='#eff4fc'; this.style.borderColor='#d0e0f5'">
                                        {{ $rec }}
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <!-- Tujuan Pengiriman (Customer) -->
                    <div>
                        <label class="field-label" style="display:flex; align-items:center; gap:4px; margin-bottom: 4px;">
                            <i class="ph-bold ph-map-pin" style="color:#285491; font-size:12px;"></i>Tujuan Pengiriman (Customer) (Opsional)
                        </label>
                        <textarea id="tujuan_pengiriman"
                                  name="tujuan_pengiriman"
                                  x-model="tujuanPengiriman"
                                  @input="tujuanPengiriman = $event.target.value.toUpperCase()"
                                  rows="2"
                                  placeholder="Contoh: Pelabuhan Tanjung Priok / Gudang Customer Surabaya..."
                                  class="field-input"
                                  style="font-size:12px; font-weight: 600; resize: vertical; min-height: 52px; line-height: 1.4; text-transform:uppercase;"></textarea>
                        @if(count($history['tujuan_pengiriman'] ?? []) > 0)
                            <div style="display:flex; flex-wrap:wrap; gap:4px; margin-top:5px;">
                                <span style="font-size:9px; color:#9ca3af; align-self:center;">Riwayat:</span>
                                @foreach(($history['tujuan_pengiriman'] ?? collect())->take(2) as $rec)
                                    <button type="button" 
                                            @click="tujuanPengiriman = $el.dataset.val"
                                            data-val="{{ $rec }}"
                                            title="Klik untuk mengisi riwayat ini"
                                            style="font-size:10px; font-weight: 600; padding:3px 8px; border-radius:10px; background:#eff4fc; color:#285491; border:1px solid #d0e0f5; cursor:pointer; text-align:left; max-width:100%; word-break:break-word; line-height:1.3;"
                                            onmouseover="this.style.background='#dbeafe'; this.style.borderColor='#93c5fd'"
                                            onmouseout="this.style.background='#eff4fc'; this.style.borderColor='#d0e0f5'">
                                        {{ $rec }}
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                <!-- 4. Nomor Shipment & Nama Sopir -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <!-- Nomor Shipment -->
                    <div style="background: white; border: 1.5px solid #cbd5e1; border-radius: 12px; padding: 10px;"
                         :style="shipmentNo.length === 9 ? 'border-color:#86efac; background:#f0fdf4;' : (shipmentNo.length > 0 ? 'border-color:#fde68a; background:#fffdf5;' : '')">
                        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:4px;">
                            <label class="field-label" style="display:flex; align-items:center; gap:4px; margin-bottom:0;">
                                <i class="ph-bold ph-hash" style="color:#285491; font-size:12px;"></i>Shipment No. <span style="color:#ef4444;">*</span>
                            </label>
                            <div style="display:flex; align-items:center; gap:4px;">
                                <button type="button" @click="openBarcodeScanner('shipment_no')"
                                        style="display:inline-flex; align-items:center; gap:2px; font-size:9px; font-weight:700; color:#285491; background:#eff4fc; border:1px solid #bfcfe8; border-radius:5px; padding:2px 5px; cursor:pointer;"
                                        title="Scan Barcode Batang">
                                    <i class="ph-bold ph-barcode"></i> Scan
                                </button>
                                <span style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 10px; font-weight: 700; padding: 2px 7px; border-radius: 6px; letter-spacing: 0.02em; font-variant-numeric: tabular-nums; display: inline-flex; align-items: center;"
                                      :style="shipmentNo.length === 9 ? 'background:#dcfce7; color:#15803d;' : (shipmentNo.length > 0 ? 'background:#fef3c7; color:#b45309;' : 'background:#eff4fc; color:#285491; border:1px solid #bfcfe8;')">
                                    <span x-text="shipmentNo.length"></span>/9 Digit
                                </span>
                            </div>
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
                                    <button type="button" 
                                            @click="shipmentNo = ($el.dataset.val || '').replace(/\D/g, '').slice(0, 9)"
                                            data-val="{{ $rec }}"
                                            title="Klik untuk mengisi: {{ $rec }}"
                                            style="font-size:9px; font-weight: 600; padding:2px 6px; border-radius:8px; background:#eff4fc; color:#285491; border:1px solid #d0e0f5; cursor:pointer;"
                                            onmouseover="this.style.background='#dbeafe'" onmouseout="this.style.background='#eff4fc'">
                                        {{ $rec }}
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <!-- Nama Sopir -->
                    <div>
                        <label class="field-label" style="display:flex; align-items:center; gap:4px;">
                            <i class="ph-bold ph-user" style="color:#285491; font-size:12px;"></i>Nama Sopir (Opsional)
                        </label>
                        <input type="text" id="nama_sopir" name="nama_sopir" x-model="namaSopir"
                               @input="namaSopir = $event.target.value.toUpperCase()"
                               list="history-sopir"
                               placeholder="Nama sopir kendaraan" class="field-input" style="font-size:12px; font-weight: 600; text-transform:uppercase;">
                        <datalist id="history-sopir">
                            @foreach($history['nama_sopir'] ?? [] as $item)
                                <option value="{{ $item }}">
                            @endforeach
                        </datalist>
                        @if(count($history['nama_sopir'] ?? []) > 0)
                            <div style="display:flex; flex-wrap:wrap; gap:4px; margin-top:5px;">
                                <span style="font-size:9px; color:#9ca3af; align-self:center;">Riwayat:</span>
                                @foreach(($history['nama_sopir'] ?? collect())->take(3) as $rec)
                                    <button type="button" 
                                            @click="namaSopir = $el.dataset.val"
                                            data-val="{{ $rec }}"
                                            title="Klik untuk mengisi: {{ $rec }}"
                                            style="font-size:10px; font-weight: 600; padding:2px 7px; border-radius:10px; background:#eff4fc; color:#285491; border:1px solid #d0e0f5; cursor:pointer;"
                                            onmouseover="this.style.background='#dbeafe'; this.style.borderColor='#93c5fd'"
                                            onmouseout="this.style.background='#eff4fc'; this.style.borderColor='#d0e0f5'">
                                        {{ $rec }}
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                <!-- 5. KHUSUS PENGIRIMAN EXPORT: Shipment Group & Nomor Seal -->
                <div x-show="jenisPengiriman === 'export'" x-transition style="display: flex; flex-direction: column; gap: 10px;">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                        <!-- Nomor Shipment Group -->
                        <div style="background: white; border: 1.5px solid #cbd5e1; border-radius: 12px; padding: 10px;"
                             :style="shipmentGroup.length === 9 ? 'border-color:#86efac; background:#f0fdf4;' : (shipmentGroup.length > 0 ? 'border-color:#fde68a; background:#fffdf5;' : '')">
                            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:4px;">
                                <label class="field-label" style="display:flex; align-items:center; gap:4px; margin-bottom:0;">
                                    <i class="ph-bold ph-hash" style="color:#285491; font-size:12px;"></i>Shipment Group <span style="color:#ef4444;">*</span>
                                    <span style="font-size: 9px; font-weight: 700; color: #285491; background: #eff4fc; border: 1px solid #bfcfe8; padding: 1px 5px; border-radius: 4px;">EXPORT</span>
                                </label>
                                <div style="display:flex; align-items:center; gap:4px;">
                                    <button type="button" @click="openBarcodeScanner('shipment_group')"
                                            style="display:inline-flex; align-items:center; gap:2px; font-size:9px; font-weight:700; color:#285491; background:#eff4fc; border:1px solid #bfcfe8; border-radius:5px; padding:2px 5px; cursor:pointer;"
                                            title="Scan Barcode Batang">
                                        <i class="ph-bold ph-barcode"></i> Scan
                                    </button>
                                    <span style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 10px; font-weight: 700; padding: 2px 7px; border-radius: 6px; letter-spacing: 0.02em; font-variant-numeric: tabular-nums; display: inline-flex; align-items: center;"
                                          :style="shipmentGroup.length === 9 ? 'background:#dcfce7; color:#15803d; border:1px solid #86efac;' : (shipmentGroup.length > 0 ? 'background:#fef3c7; color:#b45309; border:1px solid #fde68a;' : 'background:#eff4fc; color:#285491; border:1px solid #bfcfe8;')">
                                        <span x-text="shipmentGroup.length"></span>/9 Digit
                                    </span>
                                </div>
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
                                   :required="jenisPengiriman === 'export'"
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
                                        <button type="button" 
                                                @click="shipmentGroup = ($el.dataset.val || '').replace(/\D/g, '').slice(0, 9)"
                                                data-val="{{ $rec }}"
                                                title="Klik untuk mengisi: {{ $rec }}"
                                                style="font-size:9px; font-weight: 600; padding:2px 6px; border-radius:8px; background:#eff4fc; color:#285491; border:1px solid #d0e0f5; cursor:pointer;"
                                                onmouseover="this.style.background='#dbeafe'" onmouseout="this.style.background='#eff4fc'">
                                            {{ $rec }}
                                        </button>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        <!-- Nomor Seal -->
                        <div style="background: white; border: 1.5px solid #cbd5e1; border-radius: 12px; padding: 10px;">
                            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom: 3px;">
                                <label class="field-label" style="display:flex; align-items:center; gap:4px; margin-bottom: 0;">
                                    <i class="ph-bold ph-lock-key" style="color:#285491; font-size:12px;"></i>Nomor Seal
                                    <span style="font-size: 9px; font-weight: 700; color: #285491; background: #eff4fc; border: 1px solid #bfcfe8; padding: 1px 5px; border-radius: 4px;">EXPORT</span>
                                </label>
                                <button type="button" @click="openBarcodeScanner('nomor_seal')"
                                        style="display:inline-flex; align-items:center; gap:3px; font-size:10px; font-weight:700; color:#285491; background:#eff4fc; border:1px solid #bfcfe8; border-radius:6px; padding:2px 7px; cursor:pointer;"
                                        title="Scan Barcode Seal">
                                    <i class="ph-bold ph-barcode"></i> Scan
                                </button>
                            </div>
                            <input type="text" id="nomor_seal" name="nomor_seal" x-model="nomorSeal"
                                   @input="nomorSeal = $event.target.value.toUpperCase()"
                                   list="history-seal"
                                   placeholder="Contoh: S000123 / Segel" class="field-input" style="font-size:12px; font-weight: 600; text-transform:uppercase;">
                            <datalist id="history-seal">
                                @foreach($history['nomor_seal'] ?? [] as $item)
                                    <option value="{{ $item }}">
                                @endforeach
                            </datalist>
                            @if(count($history['nomor_seal'] ?? []) > 0)
                                <div style="display:flex; flex-wrap:wrap; gap:4px; margin-top:5px;">
                                    <span style="font-size:9px; color:#9ca3af; align-self:center;">Riwayat:</span>
                                    @foreach(($history['nomor_seal'] ?? collect())->take(3) as $rec)
                                        <button type="button" 
                                                @click="nomorSeal = $el.dataset.val"
                                                data-val="{{ $rec }}"
                                                title="Klik untuk mengisi: {{ $rec }}"
                                                style="font-size:10px; font-weight: 600; padding:2px 7px; border-radius:10px; background:#eff4fc; color:#285491; border:1px solid #d0e0f5; cursor:pointer;"
                                                onmouseover="this.style.background='#dbeafe'; this.style.borderColor='#93c5fd'"
                                                onmouseout="this.style.background='#eff4fc'; this.style.borderColor='#d0e0f5'">
                                            {{ $rec }}
                                        </button>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Info Box saat Pengiriman Lokal Dipilih -->
                <div x-cloak x-show="jenisPengiriman === 'lokal'" x-transition
                     style="background: #f0fdf4; border: 1.5px dashed #86efac; border-radius: 12px; padding: 10px 14px; display: flex; align-items: center; gap: 10px;">
                    <div style="width: 28px; height: 28px; border-radius: 8px; background: #dcfce7; color: #166534; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <i class="ph-bold ph-info" style="font-size: 16px;"></i>
                    </div>
                    <p style="font-size: 11px; color: #166534; margin: 0; line-height: 1.4;">
                        <strong>Pengiriman Lokal:</strong> Nomor Shipment Group dan Nomor Seal tidak digunakan / tidak wajib diisi.
                    </p>
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
                                  x-text="selectedKaryawanIds.length + ' Petugas Dipilih: ' + selectedKaryawans.map(k => k.nama + ' (' + (k.position || 'Operator') + ')').join(', ')">
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
                                <span style="font-size: 9px; font-weight: 700; text-transform: uppercase; background: #dbeafe; color: #1e40af; padding: 1px 5px; border-radius: 4px;" x-text="k.position || 'Operator'"></span>
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
                                       placeholder="Cari nama, NIK, atau posisi..."
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
                                    <div style="flex: 1; min-width: 0; display: flex; align-items: center; justify-content: space-between; gap: 8px;">
                                        <div style="display: flex; align-items: center; gap: 6px; min-width: 0;">
                                            <span style="font-weight: 500; font-size: 13px; color: #1e293b;" x-text="item.nama"></span>
                                            <span style="font-size: 11px; color: #64748b;" x-text="'(' + item.nomor_induk + ')'"></span>
                                        </div>
                                        <span style="font-size: 10px; font-weight: 700; text-transform: uppercase; padding: 2px 7px; border-radius: 6px; flex-shrink: 0;"
                                              :style="(item.position || '').toLowerCase() === 'checker' ? 'background:#f3e8ff; color:#7e22ce;' : ((item.position || '').toLowerCase() === 'helper' ? 'background:#ccfbf1; color:#0f766e;' : 'background:#dbeafe; color:#1e40af;')"
                                              x-text="item.position || 'Operator'"></span>
                                    </div>
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
            @if(isset($shipment) && $shipment->status === 'submitted')
                <i class="ph-bold ph-floppy-disk" style="font-size: 18px;"></i>
                Simpan Perubahan Data Dokumen
            @elseif(isset($shipment))
                Simpan &amp; Lanjut ke Loading Evidence
                <i class="ph-bold ph-arrow-right" style="font-size: 16px;"></i>
            @else
                Lanjut ke Loading Evidence
                <i class="ph-bold ph-arrow-right" style="font-size: 16px;"></i>
            @endif
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

        <!-- ═══════════════════════════════════════════════════════════════ -->
        <!-- MODAL BARCODE SCANNER (Kamera Realtime Logistik SPV)           -->
        <!-- ═══════════════════════════════════════════════════════════════ -->
        <template x-teleport="body">
            <div x-cloak x-show="barcodeScanning"
                 style="position: fixed; inset: 0; z-index: 999999; background: rgba(0,0,0,0.85); display: flex; align-items: center; justify-content: center; padding: 20px 16px; overflow-y: auto;"
                 @keydown.escape.window="closeBarcodeScanner()">
                <div style="background: #0f172a; border-radius: 20px; overflow: hidden; width: 100%; max-width: 440px; margin: auto; box-shadow: 0 25px 60px rgba(0,0,0,0.8); border: 1.5px solid rgba(59,130,246,0.3);" @click.stop>
                    
                    <!-- Modal Header -->
                    <div style="padding: 12px 16px; background: linear-gradient(135deg, #1e3a8a, #0d5950); display: flex; align-items: center; justify-content: space-between; gap: 8px;">
                        <div style="display: flex; align-items: center; gap: 8px; min-width: 0;">
                            <div style="width: 32px; height: 32px; border-radius: 8px; background: rgba(255,255,255,0.15); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                <i class="ph-bold ph-barcode" style="font-size: 18px; color: white;"></i>
                            </div>
                            <div style="min-width: 0;">
                                <p style="font-size: 13px; font-weight: 700; color: white; line-height: 1.2;">Pemindai Barcode Surat Jalan</p>
                                <p style="font-size: 10px; color: #93c5fd; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" x-text="barcodeTargetTitle"></p>
                            </div>
                        </div>
                        <div style="display: flex; align-items: center; gap: 6px; flex-shrink: 0;">
                            <!-- Tombol Ganti Kamera (Depan/Belakang) -->
                            <button type="button" x-show="barcodeMultipleCameras" @click="switchBarcodeCamera()"
                                    style="background: rgba(255,255,255,0.15); border: none; color: white; border-radius: 8px; padding: 5px 8px; cursor: pointer; font-size: 11px; font-weight: 700; display: inline-flex; align-items: center; gap: 4px;"
                                    title="Beralih Kamera">
                                <i class="ph-bold ph-arrows-clockwise"></i>
                                <span x-text="barcodeFacingMode === 'environment' ? 'Kamera Belakang' : 'Kamera Depan'"></span>
                            </button>
                            <!-- Tombol Zoom 1x / 2x -->
                            <button type="button" x-show="barcodeZoomAvailable" @click="toggleBarcodeZoom()"
                                    style="background: rgba(255,255,255,0.15); border: none; color: white; border-radius: 8px; padding: 5px 8px; cursor: pointer; font-size: 11px; font-weight: 700; display: inline-flex; align-items: center; gap: 3px;"
                                    title="Zoom Kamera">
                                <i class="ph-bold ph-magnifying-glass-plus"></i>
                                <span x-text="barcodeZoomLevel + 'x'"></span>
                            </button>
                            <!-- Tombol Senter / Torch (Aktif jika perangkat mendukung) -->
                            <button type="button" x-show="barcodeTorchAvailable" @click="toggleBarcodeTorch()"
                                    style="border: none; border-radius: 8px; padding: 5px 8px; cursor: pointer; font-size: 11px; font-weight: 700; display: inline-flex; align-items: center; gap: 4px; transition: all 0.15s;"
                                    :style="barcodeTorchOn ? 'background: #eab308; color: #0f172a;' : 'background: rgba(255,255,255,0.15); color: white;'">
                                <i class="ph-bold" :class="barcodeTorchOn ? 'ph-flashlight-fill' : 'ph-flashlight'"></i>
                                <span x-text="barcodeTorchOn ? 'Senter ON' : 'Senter'"></span>
                            </button>
                            <button type="button" @click="closeBarcodeScanner()"
                                    style="background: rgba(255,255,255,0.15); border: none; color: white; border-radius: 8px; padding: 6px 10px; cursor: pointer; font-size: 12px; font-weight: 700;">
                                ✕ Tutup
                            </button>
                        </div>
                    </div>

                    <!-- Camera Viewfinder with Scanner Frame -->
                    <div style="position: relative; width: 100%; min-height: 290px; background: black; display: flex; align-items: center; justify-content: center; overflow: hidden;">
                        <div id="barcode-reader-view" style="width: 100%;"></div>
                        <video x-ref="barcodeVideo" autoplay playsinline muted
                               style="width: 100%; display: none; max-height: 340px; object-fit: cover;"></video>
                        
                        <!-- Scanner Laser & Guide Overlay (Tinggi 160px beresolusi tinggi, pas untuk barcode dokumen) -->
                        <div style="position: absolute; inset: 0; pointer-events: none; display: flex; flex-direction: column; align-items: center; justify-content: center;">
                            <div style="width: 88%; max-width: 340px; height: 160px; border: 2px dashed rgba(56,189,248,0.85); border-radius: 14px; position: relative; box-shadow: 0 0 0 9999px rgba(0,0,0,0.25);">
                                <!-- Barcode badge -->
                                <div style="position: absolute; top: 6px; left: 10px; font-size: 9px; font-weight: 800; font-family: monospace; color: #38bdf8; background: rgba(15,23,42,0.7); padding: 2px 6px; border-radius: 4px; letter-spacing: 0.05em;">
                                    |||| CODE_128 1D
                                </div>
                                <!-- Laser line -->
                                <div style="position: absolute; left: 8px; right: 8px; height: 2px; background: linear-gradient(90deg, transparent, #22c55e, #38bdf8, #22c55e, transparent); box-shadow: 0 0 10px #22c55e; animation: scanLine 2s infinite ease-in-out;"></div>
                                <!-- Pojok styling -->
                                <div style="position: absolute; top: -2px; left: -2px; width: 18px; height: 18px; border-top: 3.5px solid #38bdf8; border-left: 3.5px solid #38bdf8; border-radius: 4px 0 0 0;"></div>
                                <div style="position: absolute; top: -2px; right: -2px; width: 18px; height: 18px; border-top: 3.5px solid #38bdf8; border-right: 3.5px solid #38bdf8; border-radius: 0 4px 0 0;"></div>
                                <div style="position: absolute; bottom: -2px; left: -2px; width: 18px; height: 18px; border-bottom: 3.5px solid #38bdf8; border-left: 3.5px solid #38bdf8; border-radius: 0 0 0 4px;"></div>
                                <div style="position: absolute; bottom: -2px; right: -2px; width: 18px; height: 18px; border-bottom: 3.5px solid #38bdf8; border-right: 3.5px solid #38bdf8; border-radius: 0 0 4px 0;"></div>
                            </div>
                            <p style="font-size: 11px; color: rgba(255,255,255,0.92); font-weight: 600; margin-top: 10px; background: rgba(0,0,0,0.65); padding: 4px 14px; border-radius: 12px; letter-spacing: 0.02em; text-align: center;">
                                Posisikan barcode batang horizontal di dalam kotak (jarak 20–30 cm & tahan diam agar fokus tajam)
                            </p>
                        </div>
                    </div>

                    <!-- Feedback Banner Status -->
                    <!-- 1. Success Banner -->
                    <div x-cloak x-show="barcodeSuccess"
                         style="padding: 10px 14px; background: #052e16; border-top: 1px solid #166534; display: flex; align-items: center; gap: 10px;">
                        <i class="ph-fill ph-check-circle" style="color: #22c55e; font-size: 22px; flex-shrink: 0;"></i>
                        <div>
                            <p style="font-size: 11px; font-weight: 700; color: #86efac;">Barcode Valid Terbaca!</p>
                            <p style="font-size: 14px; font-weight: 800; color: white; letter-spacing: 0.05em; font-family: monospace;" x-text="barcodeSuccess"></p>
                        </div>
                    </div>

                    <!-- 2. Warning / Salah Baca Filter Banner (Ketika kamera membaca barcode yang salah misal 8 digit) -->
                    <div x-cloak x-show="barcodeWarning && !barcodeSuccess"
                         style="padding: 10px 14px; background: #451a03; border-top: 1px solid #92400e; display: flex; flex-direction: column; gap: 6px;">
                        <div style="display: flex; align-items: flex-start; gap: 8px;">
                            <i class="ph-fill ph-warning" style="color: #f59e0b; font-size: 18px; flex-shrink: 0; margin-top: 1px;"></i>
                            <p style="font-size: 11px; color: #fed7aa; line-height: 1.4; margin: 0;" x-text="barcodeWarning"></p>
                        </div>
                        <div style="display: flex; align-items: center; justify-content: flex-end; gap: 8px; margin-top: 2px;">
                            <button type="button" @click="forceApplyBarcode(barcodeLastRead)"
                                     style="font-size: 11px; font-weight: 700; background: #ea580c; color: white; border: none; border-radius: 6px; padding: 4px 10px; cursor: pointer;">
                                Pakai "<span x-text="barcodeLastRead"></span>" Tetap
                            </button>
                        </div>
                    </div>

                    <!-- Footer Action & Secondary Fallbacks -->
                    <div style="padding: 10px 16px; border-top: 1px solid rgba(255,255,255,0.08); display: flex; align-items: center; justify-content: space-between; gap: 10px;">
                        <!-- Upload File / Galeri foto alternatif -->
                        <label style="display: inline-flex; align-items: center; gap: 5px; font-size: 11px; font-weight: 600; color: #93c5fd; background: rgba(59,130,246,0.15); border: 1px solid rgba(59,130,246,0.3); border-radius: 8px; padding: 6px 10px; cursor: pointer;">
                            <i class="ph-bold ph-image" style="font-size: 14px;"></i>
                            <span>Unggah / Foto Barcode</span>
                            <input type="file" accept="image/*" style="display: none;" @change="scanBarcodeFromFile($event)">
                        </label>
                        <span style="font-size: 10px; color: rgba(255,255,255,0.65); text-align: right; line-height: 1.3;">
                            Jarak 20–30 cm & diam sebentar agar fokus tajam
                        </span>
                    </div>
                </div>
            </div>
        </template>
        <style>
            #barcode-reader-view video { width: 100% !important; object-fit: cover !important; border-radius: 0 !important; }
            #barcode-reader-view img { display: none !important; }
            #barcode-reader-view__scan_region { border: none !important; }
            #barcode-reader-view__dashboard { display: none !important; }
            @keyframes scanLine {
                0%   { top: 6px; opacity: 1; }
                50%  { top: calc(100% - 8px); opacity: 0.9; }
                100% { top: 6px; opacity: 1; }
            }
        </style>

    </form>

</x-field-layout>
