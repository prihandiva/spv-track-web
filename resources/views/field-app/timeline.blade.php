<x-field-layout>
@php
    $mandatoryPointIds = $points->where('wajib', true)->pluck('id')->values()->all();
    $initialUploadedPointIds = $shipment->evidenceItems->pluck('sop_photo_point_id')->filter()->unique()->values()->all();
@endphp

<script>
    window.openSopPreview = function(pointId, urutan, title, desc) {
        const modal = document.getElementById('sop-preview-lightbox');
        if (!modal) return;

        if (modal.parentElement !== document.body) {
            document.body.appendChild(modal);
        }

        const wrapper = document.getElementById('preview-wrapper-' + pointId);
        const img = document.getElementById('preview-' + pointId);
        const vid = document.getElementById('video-preview-' + pointId);
        const tsBox = document.getElementById('timestamp-' + pointId);
        const tsText = tsBox ? tsBox.querySelector('.ts-text')?.textContent?.trim() : '';

        const isVideo = wrapper?.dataset?.isVideo === 'true';
        let mediaUrl = isVideo ? (vid?.currentSrc || vid?.src || wrapper?.dataset?.mediaUrl) : (img?.currentSrc || img?.src || wrapper?.dataset?.mediaUrl);

        if (!mediaUrl && img && img.src) mediaUrl = img.src;
        if (!mediaUrl && vid && vid.src) mediaUrl = vid.src;

        const elUrutan = document.getElementById('lightbox-point-urutan');
        const elTitle = document.getElementById('lightbox-point-title');
        const elDesc = document.getElementById('lightbox-point-desc');
        const elTs = document.getElementById('lightbox-timestamp');
        const modalImg = document.getElementById('lightbox-img');
        const modalVid = document.getElementById('lightbox-video');
        const retakeText = document.getElementById('lightbox-btn-retake-text');

        if (elUrutan) elUrutan.textContent = 'Titik ' + (urutan || '-');
        if (elTitle) elTitle.textContent = title || '';
        if (elDesc) elDesc.textContent = desc || '';
        if (elTs) elTs.textContent = tsText || (wrapper?.dataset?.timestamp || 'Tersimpan & Terverifikasi');

        if (isVideo) {
            if (modalImg) modalImg.style.display = 'none';
            if (modalVid) {
                modalVid.src = mediaUrl || '';
                modalVid.style.display = 'block';
                modalVid.load();
                modalVid.play().catch(() => {});
            }
            if (retakeText) retakeText.textContent = 'Rekam Ulang';
        } else {
            if (modalVid) {
                modalVid.pause();
                modalVid.style.display = 'none';
            }
            if (modalImg) {
                modalImg.src = mediaUrl || '';
                modalImg.style.display = 'block';
            }
            if (retakeText) retakeText.textContent = 'Foto Ulang';
        }

        modal.dataset.currentPointId = pointId;
        modal.dataset.isVideo = isVideo ? 'true' : 'false';

        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    };

    window.closeSopPreview = function() {
        const modal = document.getElementById('sop-preview-lightbox');
        if (modal) {
            modal.style.display = 'none';
            const modalVid = document.getElementById('lightbox-video');
            if (modalVid) {
                modalVid.pause();
            }
        }
        document.body.style.overflow = '';
    };

    window.retakeFromLightbox = function(type) {
        const modal = document.getElementById('sop-preview-lightbox');
        const pointId = modal?.dataset?.currentPointId;
        window.closeSopPreview();
        if (!pointId) return;

        setTimeout(() => {
            const input = document.getElementById(type + '-input-' + pointId) || document.getElementById('initial-' + type + '-input-' + pointId);
            if (input) {
                input.click();
            }
        }, 120);
    };

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            window.closeSopPreview();
        }
    });

    function registerTimelineManager() {
        if (!window.Alpine) return;
        if (window._timelineManagerRegistered) return;
        window._timelineManagerRegistered = true;

        Alpine.data('timelineManager', () => ({
            mandatoryPointIds: @json($mandatoryPointIds),
            uploadedPointIds: @json($initialUploadedPointIds),
            waktuKedatangan: @json(old('waktu_kedatangan_container', $shipment->waktu_kedatangan_container ? $shipment->waktu_kedatangan_container->format('Y-m-d H:i') : now('Asia/Jakarta')->format('Y-m-d H:i'))),
            waktuKeberangkatan: @json(old('waktu_keberangkatan_container', $shipment->waktu_keberangkatan_container ? $shipment->waktu_keberangkatan_container->format('Y-m-d H:i') : now('Asia/Jakarta')->format('Y-m-d H:i'))),
            openStagingSection: true,
            fpKedatangan: null,
            fpKeberangkatan: null,

            openPointPreview(pointId, urutan, title, desc) {
                window.openSopPreview(pointId, urutan, title, desc);
            },

            closePreview() {
                window.closeSopPreview();
            },

            retakeFromModal(pointId, type = 'camera') {
                window.retakeFromLightbox(type);
            },

            init() {
                this.initPickers();
            },

            initPickers() {
                const self = this;
                const baseConfig = {
                    enableTime: true,
                    time_24hr: true,
                    dateFormat: 'Y-m-d H:i',
                    altInput: true,
                    altFormat: 'd/m/Y H:i',
                    altInputClass: 'field-input flatpickr-custom-input',
                    locale: (typeof flatpickr !== 'undefined' && flatpickr.l10ns && flatpickr.l10ns.id) ? flatpickr.l10ns.id : 'default',
                    disableMobile: true,
                    allowInput: true,
                    monthSelectorType: 'static'
                };

                const elKedatangan = document.getElementById('waktu_kedatangan_container');
                if (elKedatangan && typeof flatpickr !== 'undefined') {
                    this.fpKedatangan = flatpickr(elKedatangan, {
                        ...baseConfig,
                        defaultDate: this.waktuKedatangan || null,
                        onChange(selectedDates, dateStr) {
                            self.waktuKedatangan = dateStr || '';
                        },
                        onClose(selectedDates, dateStr) {
                            self.waktuKedatangan = dateStr || '';
                        }
                    });
                }

                const elKeberangkatan = document.getElementById('waktu_keberangkatan_container');
                if (elKeberangkatan && typeof flatpickr !== 'undefined') {
                    this.fpKeberangkatan = flatpickr(elKeberangkatan, {
                        ...baseConfig,
                        defaultDate: this.waktuKeberangkatan || null,
                        onChange(selectedDates, dateStr) {
                            self.waktuKeberangkatan = dateStr || '';
                        },
                        onClose(selectedDates, dateStr) {
                            self.waktuKeberangkatan = dateStr || '';
                        }
                    });
                }
            },

            setNow(field) {
                const now = new Date();
                const y = now.getFullYear();
                const m = String(now.getMonth() + 1).padStart(2, '0');
                const d = String(now.getDate()).padStart(2, '0');
                const hh = String(now.getHours()).padStart(2, '0');
                const mm = String(now.getMinutes()).padStart(2, '0');
                const val = `${y}-${m}-${d} ${hh}:${mm}`;
                if (field === 'kedatangan') {
                    this.waktuKedatangan = val;
                    if (this.fpKedatangan) {
                        this.fpKedatangan.setDate(val, true);
                    }
                }
                if (field === 'keberangkatan') {
                    this.waktuKeberangkatan = val;
                    if (this.fpKeberangkatan) {
                        this.fpKeberangkatan.setDate(val, true);
                    }
                }
            },

            addUploadedPoint(id) {
                id = Number(id);
                if (!this.uploadedPointIds.includes(id)) {
                    this.uploadedPointIds.push(id);
                }
            },
            get mandatoryUploadedCount() {
                return this.mandatoryPointIds.filter(id => this.uploadedPointIds.includes(Number(id))).length;
            },
            get totalMandatoryCount() {
                return this.mandatoryPointIds.length;
            },
            get isPhotosComplete() {
                return this.mandatoryUploadedCount >= this.totalMandatoryCount;
            },
            get isFormComplete() {
                return (this.waktuKedatangan || '').trim().length > 0 &&
                       (this.waktuKeberangkatan || '').trim().length > 0;
            },
            get isContainerComplete() {
                return {{ $shipment->containerInspection ? 'true' : 'false' }};
            },
            get isBaleComplete() {
                return {{ $shipment->baleInspection ? 'true' : 'false' }};
            },
            get canSubmit() {
                return this.isPhotosComplete && this.isFormComplete && this.isContainerComplete && this.isBaleComplete;
            },
            get missingSummary() {
                const missing = [];
                if (!this.isContainerComplete) {
                    missing.push('Form Inspeksi Kontainer');
                }
                if (!this.isBaleComplete) {
                    missing.push('Form Inspeksi Bale');
                }
                if (!this.isPhotosComplete) {
                    const diff = this.totalMandatoryCount - this.mandatoryUploadedCount;
                    missing.push(diff + ' foto wajib');
                }
                if (!(this.waktuKedatangan || '').trim()) missing.push('Waktu Mulai Staging');
                if (!(this.waktuKeberangkatan || '').trim()) missing.push('Waktu Akhir Staging');
                return missing;
            }
        }));
    }

    if (window.Alpine) {
        registerTimelineManager();
    } else {
        document.addEventListener('alpine:init', registerTimelineManager);
    }
</script>

<div x-data="timelineManager"
     @photo-uploaded.window="addUploadedPoint($event.detail.pointId)">

    <!-- Shipment Header Card (Packing List as Primary Identifier) -->
    <div style="background: linear-gradient(135deg, #1a3c6e 0%, #285491 60%, #0d5950 100%); border-radius: 16px; padding: 16px; color: white; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 4px 16px rgba(40,84,145,0.3);">
        <div>
            <p style="font-size: 10px; font-weight: 600; color: rgba(255,255,255,0.6); text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 3px;">No. Surat Jalan / Delivery / Packing List</p>
            <h1 style="font-size: 18px; font-weight: 800; font-family: monospace; letter-spacing: 0.04em; line-height: 1.1;">{{ $shipment->packing_list_no ?: ($shipment->nomor_container_atau_plat ?? 'PL-' . $shipment->id) }}</h1>
            <p style="font-size: 11px; color: rgba(255,255,255,0.85); margin-top: 4px;">
                Group: <span style="font-weight:700; color:white;">{{ $shipment->shipment_group ?: '-' }}</span> &bull;
                Shpt: <span style="font-weight:700; color:white;">{{ $shipment->shipment_no ?: '-' }}</span> &bull;
                Plat: <span style="font-weight:700; color:white;">{{ $shipment->plat_nomor ?: '-' }}</span> &bull;
                Cont: <span style="font-weight:700; color:white;">{{ $shipment->nomor_container_atau_plat ?: '-' }}</span>
                @if($shipment->agen_forwarding)
                    &bull; Fwd: <span style="font-weight:700; color:white;">{{ $shipment->agen_forwarding }}</span>
                @endif
                @if($shipment->tujuan_pengiriman)
                    &bull; Tujuan: <span style="font-weight:700; color:white;">{{ $shipment->tujuan_pengiriman }}</span>
                @endif
            </p>
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
            <p style="font-size: 18px; font-weight: 800; color: #059e3d;" id="uploaded-count" x-text="uploadedPointIds.length">{{ $shipment->evidenceItems->count() }}</p>
            <p style="font-size: 10px; color: #9ca3af; font-weight: 500;">Terupload</p>
        </div>
        <div style="flex: 1; background: white; border-radius: 12px; padding: 10px 14px; border: 1px solid #e8edf3; text-align: center;">
            <p style="font-size: 18px; font-weight: 800;" :style="isPhotosComplete ? 'color:#059e3d;' : 'color:#d97706;'">
                <span x-text="mandatoryUploadedCount"></span> / <span x-text="totalMandatoryCount"></span>
            </p>
            <p style="font-size: 10px; color: #9ca3af; font-weight: 500;">Wajib Terverifikasi</p>
        </div>
    </div>

    <!-- Rekomendasi Posisi Kamera Horizontal / Landscape -->
    <div style="background: linear-gradient(135deg, #eff6ff 0%, #f0fdf4 100%); border: 1.5px solid #93c5fd; border-radius: 14px; padding: 12px 14px; box-shadow: 0 2px 8px rgba(37,99,235,0.06); margin-top: 4px;">
        <div style="display: flex; align-items: flex-start; gap: 12px;">
            <div style="width: 38px; height: 38px; border-radius: 10px; background: linear-gradient(135deg, #2563eb, #059e3d); color: white; display: flex; align-items: center; justify-content: center; shrink: 0; box-shadow: 0 2px 6px rgba(37,99,235,0.3);">
                <i class="ph-bold ph-device-rotate" style="font-size: 20px;"></i>
            </div>
            <div style="flex: 1;">
                <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 4px;">
                    <p style="font-size: 12px; font-weight: 800; color: #1e3a8a;">Rekomendasi: Posisikan Kamera Horizontal (Landscape)</p>
                    <span style="font-size: 9px; font-weight: 700; background: #dbeafe; color: #1d4ed8; padding: 2px 8px; border-radius: 6px;">
                        Mode Mendatar 16:9
                    </span>
                </div>
                <p style="font-size: 11px; color: #334155; margin-top: 3px; line-height: 1.4;">
                    Posisikan ponsel Anda secara <strong>mendatar (landscape/horizontal)</strong> saat mengambil foto bukti dan merekam video. Sudut pandang container akan lebih luas, seluruh muatan terlihat utuh, dan stempel jam server WIB tercetak sempurna.
                </p>
            </div>
        </div>
    </div>

    <!-- 2 Form Inspeksi Wajib (Lenzing Standards) -->
    <div style="background: white; border-radius: 16px; padding: 14px 16px; border: 1.5px solid #dbeafe; box-shadow: 0 2px 10px rgba(40,84,145,0.06); margin-top: 4px;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <div style="width: 28px; height: 28px; border-radius: 8px; background: #285491; color: white; display: flex; align-items: center; justify-content: center; font-size: 14px;">
                    <i class="ph-bold ph-clipboard-text"></i>
                </div>
                <div>
                    <h3 style="font-size: 12px; font-weight: 800; color: #1e3a8a; text-transform: uppercase; letter-spacing: 0.04em;">Formulir Inspeksi Wajib</h3>
                    <p style="font-size: 10px; color: #64748b; margin-top: 1px;">Standar Mutu & Audit Fisik Lenzing (Wajib dilengkapi sebelum submit)</p>
                </div>
            </div>
            <span style="font-size: 10px; font-weight: 700; padding: 2px 8px; border-radius: 20px; background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe;">
                2 Formulir
            </span>
        </div>

        <div style="display: flex; flex-direction: column; gap: 10px;">
            <!-- 1. Form Inspeksi Kontainer -->
            @php $cInsp = $shipment->containerInspection; @endphp
            <div style="border-radius: 12px; padding: 12px; border: 1.5px solid {{ $cInsp ? '#86efac' : '#fed7aa' }}; background: {{ $cInsp ? '#f0fdf4' : '#fffbeb' }}; display: flex; align-items: center; justify-content: space-between; gap: 10px; flex-wrap: wrap;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div style="width: 32px; height: 32px; border-radius: 50%; background: {{ $cInsp ? '#059e3d' : '#f59e0b' }}; color: white; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 13px; shrink: 0;">
                        {{ $cInsp ? '✓' : '1' }}
                    </div>
                    <div>
                        <div style="display: flex; align-items: center; gap: 6px; flex-wrap: wrap;">
                            <span style="font-size: 12px; font-weight: 800; color: #1f2937;">Form Inspeksi Kontainer</span>
                            <span style="font-size: 9px; font-weight: 700; padding: 1px 6px; border-radius: 4px; background: {{ $cInsp ? '#dcfce7' : '#fee2e2' }}; color: {{ $cInsp ? '#15803d' : '#b91c1c' }};">
                                {{ $cInsp ? 'SELESAI: ' . strtoupper($cInsp->kesimpulan_label) : 'BELUM DIISI' }}
                            </span>
                        </div>
                        <p style="font-size: 10px; color: #64748b; margin-top: 2px;">
                            Pemeriksaan 6 titik fisik: Pintu, Dinding Kiri/Kanan/Depan, Atap & Lantai
                        </p>
                    </div>
                </div>

                <a href="{{ route('field-app.container-inspection', $shipment->id) }}"
                   style="display: inline-flex; align-items: center; gap: 5px; font-size: 11px; font-weight: 700; padding: 7px 14px; border-radius: 8px; text-decoration: none; transition: all 0.2s; background: {{ $cInsp ? '#ffffff' : '#f59e0b' }}; color: {{ $cInsp ? '#15803d' : '#ffffff' }}; border: 1px solid {{ $cInsp ? '#86efac' : '#d97706' }}; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                    <i class="ph-bold {{ $cInsp ? 'ph-pencil-simple' : 'ph-plus-circle' }}"></i>
                    <span>{{ $cInsp ? 'Edit / Tinjau' : 'Isi Form Kontainer' }}</span>
                </a>
            </div>

            <!-- 2. Form Inspeksi Bale -->
            @php $bInsp = $shipment->baleInspection; @endphp
            <div style="border-radius: 12px; padding: 12px; border: 1.5px solid {{ $bInsp ? '#86efac' : '#fed7aa' }}; background: {{ $bInsp ? '#f0fdf4' : '#fffbeb' }}; display: flex; align-items: center; justify-content: space-between; gap: 10px; flex-wrap: wrap;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div style="width: 32px; height: 32px; border-radius: 50%; background: {{ $bInsp ? '#059e3d' : '#f59e0b' }}; color: white; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 13px; shrink: 0;">
                        {{ $bInsp ? '✓' : '2' }}
                    </div>
                    <div>
                        <div style="display: flex; align-items: center; gap: 6px; flex-wrap: wrap;">
                            <span style="font-size: 12px; font-weight: 800; color: #1f2937;">Form Inspeksi Kondisi Bale (Loading)</span>
                            <span style="font-size: 9px; font-weight: 700; padding: 1px 6px; border-radius: 4px; background: {{ $bInsp ? '#dcfce7' : '#fee2e2' }}; color: {{ $bInsp ? '#15803d' : '#b91c1c' }};">
                                {{ $bInsp ? 'SELESAI: ' . strtoupper($bInsp->kesimpulan_label) : 'BELUM DIISI' }}
                            </span>
                        </div>
                        <p style="font-size: 10px; color: #64748b; margin-top: 2px;">
                            8 Cek ketidaksesuaian bale, perbaikan, & verifikasi 4-Eyes Principle
                        </p>
                    </div>
                </div>

                <a href="{{ route('field-app.bale-inspection', $shipment->id) }}"
                   style="display: inline-flex; align-items: center; gap: 5px; font-size: 11px; font-weight: 700; padding: 7px 14px; border-radius: 8px; text-decoration: none; transition: all 0.2s; background: {{ $bInsp ? '#ffffff' : '#285491' }}; color: {{ $bInsp ? '#15803d' : '#ffffff' }}; border: 1px solid {{ $bInsp ? '#86efac' : '#1e3a8a' }}; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                    <i class="ph-bold {{ $bInsp ? 'ph-pencil-simple' : 'ph-plus-circle' }}"></i>
                    <span>{{ $bInsp ? 'Edit / Tinjau' : 'Isi Form Bale' }}</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Titik Loading Evidence (27 Titik SOP) -->
    <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 10px; margin-bottom: -2px;">
        <p style="font-size: 12px; font-weight: 700; color: #1f2937;">Daftar Loading Evidence</p>
        <span style="font-size: 10px; font-weight: 600; color: #6b7280; background: #e5e7eb; padding: 2px 8px; border-radius: 10px;">27 Titik SOP</span>
    </div>
    <div style="display: flex; flex-direction: column; gap: 8px; position: relative; padding-left: 4px;">
        <!-- Vertical line -->
        <div style="position: absolute; left: 20px; top: 20px; bottom: 20px; width: 2px; background: linear-gradient(to bottom, #e8edf3, #bfcfe8, #e8edf3); z-index: 0;"></div>

        @foreach($points as $point)
        @php
            $existing = $shipment->evidenceItems->firstWhere('sop_photo_point_id', $point->id);
            $isVideoPoint = ($point->tipe_item === 'video' || $point->urutan == 27 || stripos($point->nama_titik, 'video') !== false);
            $isExistingVideo = $existing && ($existing->tipe_item === 'video' || preg_match('/\.(mp4|mov|webm|3gp|avi|m4v)$/i', $existing->file_path));
        @endphp
        <div style="display: flex; gap: 12px; align-items: flex-start; position: relative; z-index: 1;">
            <!-- Number Badge -->
            <div style="width: 36px; height: 36px; border-radius: 50%; background: white; border: 2px solid {{ $existing ? '#059e3d' : ($isVideoPoint ? '#f87171' : '#e8edf3') }}; display: flex; align-items: center; justify-content: center; shrink: 0; box-shadow: 0 1px 4px rgba(40,84,145,0.1); flex-shrink: 0;">
                <span id="num-badge-{{$point->id}}" style="font-size: 11px; font-weight: 800; color: {{ $existing ? '#059e3d' : ($isVideoPoint ? '#dc2626' : '#285491') }};">
                    {{ $existing ? '✓' : $point->urutan }}
                </span>
            </div>

            <!-- Card -->
            <div style="flex: 1; background: white; border-radius: 14px; border: 1.5px solid {{ $isVideoPoint ? '#fecaca' : '#e8edf3' }}; box-shadow: 0 1px 4px rgba(40,84,145,0.06); overflow: hidden; transition: all 0.2s;"
                 onmouseover="this.style.boxShadow='0 4px 14px rgba(40,84,145,0.12)'; this.style.borderColor='{{ $isVideoPoint ? '#f87171' : '#bfcfe8' }}'"
                 onmouseout="this.style.boxShadow='0 1px 4px rgba(40,84,145,0.06)'; this.style.borderColor='{{ $isVideoPoint ? '#fecaca' : '#e8edf3' }}'">

                <!-- Card Header -->
                <div style="padding: 10px 12px; display: flex; align-items: flex-start; justify-content: space-between; border-bottom: 1px solid #f0f4f9; background: {{ $isVideoPoint ? '#fffafb' : 'transparent' }};">
                    <div>
                        <div style="display: flex; align-items: center; gap: 6px;">
                            @if($isVideoPoint)
                                <i class="ph-fill ph-video-camera" style="color: #dc2626; font-size: 14px;"></i>
                            @endif
                            <p style="font-size: 12px; font-weight: 700; color: #1f2937; line-height: 1.2;">{{ $point->nama_titik }}</p>
                        </div>
                        <p style="font-size: 10px; color: #9ca3af; margin-top: 2px;">{{ $point->deskripsi }}</p>
                    </div>
                    @if($point->wajib)
                        <span style="font-size: 9px; font-weight: 700; color: #ef4444; background: #fef2f2; border: 1px solid #fecaca; padding: 2px 7px; border-radius: 20px; white-space: nowrap; margin-left: 8px; flex-shrink: 0;">WAJIB</span>
                    @else
                        <span style="font-size: 9px; font-weight: 600; color: {{ $isVideoPoint ? '#dc2626' : '#9ca3af' }}; background: {{ $isVideoPoint ? '#fee2e2' : '#f9fafb' }}; border: 1px solid {{ $isVideoPoint ? '#fca5a5' : '#f3f4f6' }}; padding: 2px 7px; border-radius: 20px; white-space: nowrap; margin-left: 8px; flex-shrink: 0;">
                            {{ $isVideoPoint ? 'Video (Opsional)' : 'Opsional' }}
                        </span>
                    @endif
                </div>

                <!-- Upload Area -->
                <div style="position: relative; border: 2px dashed {{ $isVideoPoint ? '#fca5a5' : '#e5e7eb' }}; border-radius: 10px; margin: 10px 12px 12px; background: #fafbfd; overflow: hidden; transition: border-color 0.2s;">

                    @if($isVideoPoint)
                        <!-- Video Initial Buttons (Camcorder & Video Picker) -->
                        <div id="icon-{{$point->id}}" style="display: {{ $existing ? 'none' : 'flex' }}; border-bottom: 1px solid #f0f4f9;">
                            <label style="flex: 1; padding: 12px 8px; display: flex; flex-direction: column; align-items: center; justify-content: center; cursor: pointer; transition: background 0.2s;"
                                   onmouseover="this.style.background='#fef2f2'" onmouseout="this.style.background='transparent'">
                                <input type="file" id="initial-camera-input-{{$point->id}}" style="display:none;" accept="video/*" capture="environment" onchange="uploadSopPhoto(this, {{ $point->id }})">
                                <div style="width:34px; height:34px; border-radius:50%; background:#fee2e2; display:flex; align-items:center; justify-content:center; margin-bottom:4px;">
                                    <i class="ph-fill ph-video-camera" style="font-size: 18px; color: #dc2626;"></i>
                                </div>
                                <span style="font-size: 11px; font-weight: 700; color: #dc2626;">Rekam Video</span>
                                <span style="font-size: 9px; color: #ef4444;">Buka Camcorder Langsung</span>
                            </label>
                            <div style="width: 1px; background: #f0f4f9;"></div>
                            <label style="flex: 1; padding: 12px 8px; display: flex; flex-direction: column; align-items: center; justify-content: center; cursor: pointer; transition: background 0.2s;"
                                   onmouseover="this.style.background='#eff4fc'" onmouseout="this.style.background='transparent'">
                                <input type="file" id="initial-gallery-input-{{$point->id}}" style="display:none;" accept="video/*" onchange="uploadSopPhoto(this, {{ $point->id }})">
                                <div style="width:34px; height:34px; border-radius:50%; background:#eff4fc; display:flex; align-items:center; justify-content:center; margin-bottom:4px;">
                                    <i class="ph-bold ph-film-strip" style="font-size: 18px; color: #285491;"></i>
                                </div>
                                <span style="font-size: 11px; font-weight: 700; color: #285491;">Pilih Video</span>
                                <span style="font-size: 9px; color: #64748b;">Dari Galeri / Berkas</span>
                            </label>
                        </div>
                    @else
                        <!-- Photo Initial Buttons -->
                        <div id="icon-{{$point->id}}" style="display: {{ $existing ? 'none' : 'flex' }}; border-bottom: 1px solid #f0f4f9;">
                            <label style="flex: 1; padding: 12px 8px; display: flex; flex-direction: column; align-items: center; justify-content: center; cursor: pointer; transition: background 0.2s;"
                                   onmouseover="this.style.background='#eff4fc'" onmouseout="this.style.background='transparent'">
                                <input type="file" id="initial-camera-input-{{$point->id}}" style="display:none;" accept="image/*" capture="environment" onchange="uploadSopPhoto(this, {{ $point->id }})">
                                <i class="ph-bold ph-camera" style="font-size: 20px; color: #285491; margin-bottom: 3px; display: block;"></i>
                                <span style="font-size: 10px; font-weight: 700; color: #285491;">Buka Kamera</span>
                            </label>
                            <div style="width: 1px; background: #f0f4f9;"></div>
                            <label style="flex: 1; padding: 12px 8px; display: flex; flex-direction: column; align-items: center; justify-content: center; cursor: pointer; transition: background 0.2s;"
                                   onmouseover="this.style.background='#eff4fc'" onmouseout="this.style.background='transparent'">
                                <input type="file" id="initial-gallery-input-{{$point->id}}" style="display:none;" accept="image/*" onchange="uploadSopPhoto(this, {{ $point->id }})">
                                <i class="ph-bold ph-image" style="font-size: 20px; color: #6b7280; margin-bottom: 3px; display: block;"></i>
                                <span style="font-size: 10px; font-weight: 600; color: #6b7280;">Pilih Galeri</span>
                            </label>
                        </div>
                    @endif

                    <!-- Media Preview Container (Clickable to open inspection modal) -->
                    <div id="preview-wrapper-{{$point->id}}"
                         onclick="openSopPreview({{ $point->id }}, {{ $point->urutan }}, {{ Js::from($point->nama_titik) }}, {{ Js::from($point->deskripsi) }})"
                         data-point-id="{{ $point->id }}"
                         data-media-url="{{ $existing ? asset('storage/' . $existing->file_path) : '' }}"
                         data-is-video="{{ $isExistingVideo ? 'true' : 'false' }}"
                         data-timestamp="{{ $existing && $existing->captured_at ? $existing->captured_at->format('Y-m-d H:i:s') . ' WIB' : '' }}"
                         style="display: {{ $existing ? 'block' : 'none' }}; position: relative; height: 180px; background: #0f172a; cursor: pointer;"
                         title="Klik untuk melihat pratinjau stempel & kejelasan bukti">

                        <img id="preview-{{$point->id}}"
                             src="{{ $existing && !$isExistingVideo ? asset('storage/' . $existing->file_path) : '' }}"
                             style="width:100%; height:100%; object-fit:contain; opacity:0.95; display: {{ $existing && !$isExistingVideo ? 'block' : 'none' }};" />

                        <video id="video-preview-{{$point->id}}"
                               src="{{ $existing && $isExistingVideo ? asset('storage/' . $existing->file_path) : '' }}"
                               playsinline
                               style="width:100%; height:100%; object-fit:contain; background:#000; display: {{ $existing && $isExistingVideo ? 'block' : 'none' }};"></video>

                        <!-- Hint Badge (Top Left) -->
                        <div style="position:absolute; top:8px; left:8px; background:rgba(15,23,42,0.75); backdrop-filter:blur(4px); color:white; font-size:10px; font-weight:700; padding:3px 8px; border-radius:12px; z-index:10; border:1px solid rgba(255,255,255,0.2); display:flex; align-items:center; gap:4px; pointer-events:none;">
                            <i class="ph-bold ph-magnifying-glass-plus"></i> Klik untuk Periksa
                        </div>

                        <!-- Status Badge (Top Right) -->
                        <div id="status-{{$point->id}}" style="position:absolute; top:8px; right:8px; background:rgba(0,0,0,0.7); backdrop-filter:blur(4px); font-size:10px; font-weight:700; padding:3px 9px; border-radius:12px; z-index:10; border:1px solid rgba(255,255,255,0.15); pointer-events:none;">
                            <span style="color:#63c384;">✓ Tersimpan</span>
                        </div>

                        <!-- Live Timestamp Badge (Bottom Left) -->
                        <div id="timestamp-{{$point->id}}" style="position:absolute; bottom:8px; left:8px; right:8px; background:rgba(0,0,0,0.75); backdrop-filter:blur(4px); color:#63c384; font-size:10px; font-weight:700; padding:4px 8px; border-radius:6px; display:flex; align-items:center; gap:5px; z-index:10; font-family:monospace; border:1px solid rgba(255,255,255,0.15); pointer-events:none;">
                            <i class="ph-bold ph-calendar-check" style="font-size:12px; color:#63c384;"></i>
                            <span class="ts-text" style="white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                                {{ $existing && $existing->captured_at ? $existing->captured_at->format('Y-m-d H:i:s') . ' WIB' : '' }}
                            </span>
                        </div>
                    </div>

                    <!-- Permanent Mobile & Desktop Action Bar (Visible below the photo when media exists) -->
                    <div id="action-bar-{{$point->id}}" style="display: {{ $existing ? 'flex' : 'none' }}; border-top: 1.5px solid #e2e8f0; background: #f8fafc; padding: 8px 12px; align-items: center; justify-content: space-between; gap: 8px; flex-wrap: wrap;">
                        <button type="button"
                                onclick="openSopPreview({{$point->id}}, {{$point->urutan}}, {{ Js::from($point->nama_titik) }}, {{ Js::from($point->deskripsi) }})"
                                style="display:flex; align-items:center; gap:5px; font-size:11px; font-weight:700; color:#285491; background:white; border:1.5px solid #cbd5e1; border-radius:8px; padding:6px 12px; cursor:pointer; transition:all 0.15s; box-shadow: 0 1px 2px rgba(0,0,0,0.04);"
                                onmouseover="this.style.background='#eff4fc'; this.style.borderColor='#285491'" onmouseout="this.style.background='white'; this.style.borderColor='#cbd5e1'">
                            <i class="ph-bold ph-eye" style="font-size:14px; color:#285491;"></i> Periksa Bukti
                        </button>
                        <div style="display:flex; align-items:center; gap:6px;">
                            @if($isVideoPoint)
                                <label style="display:flex; align-items:center; gap:5px; font-size:11px; font-weight:700; color:#dc2626; background:#fee2e2; border:1px solid #fecaca; border-radius:8px; padding:6px 11px; cursor:pointer; transition:background 0.15s;"
                                       onmouseover="this.style.background='#fca5a5'" onmouseout="this.style.background='#fee2e2'" title="Rekam Ulang Video">
                                    <input type="file" id="camera-input-{{$point->id}}" style="display:none;" accept="video/*" capture="environment" onchange="uploadSopPhoto(this, {{ $point->id }})">
                                    <i class="ph-fill ph-video-camera" style="font-size:14px;"></i> Rekam Ulang
                                </label>
                                <label style="display:flex; align-items:center; gap:5px; font-size:11px; font-weight:600; color:#285491; background:#eff4fc; border:1px solid #bfcfe8; border-radius:8px; padding:6px 11px; cursor:pointer; transition:background 0.15s;"
                                       onmouseover="this.style.background='#dbe7f6'" onmouseout="this.style.background='#eff4fc'" title="Pilih File Video Lain">
                                    <input type="file" id="gallery-input-{{$point->id}}" style="display:none;" accept="video/*" onchange="uploadSopPhoto(this, {{ $point->id }})">
                                    <i class="ph-bold ph-film-strip" style="font-size:14px;"></i> Ganti File
                                </label>
                            @else
                                <label style="display:flex; align-items:center; gap:5px; font-size:11px; font-weight:700; color:#285491; background:#eff4fc; border:1.5px solid #bfcfe8; border-radius:8px; padding:6px 12px; cursor:pointer; transition:background 0.15s;"
                                       onmouseover="this.style.background='#dbe7f6'" onmouseout="this.style.background='#eff4fc'" title="Foto Ulang dari Kamera">
                                    <input type="file" id="camera-input-{{$point->id}}" style="display:none;" accept="image/*" capture="environment" onchange="uploadSopPhoto(this, {{ $point->id }})">
                                    <i class="ph-bold ph-camera" style="font-size:14px;"></i> Foto Ulang
                                </label>
                                <label style="display:flex; align-items:center; gap:5px; font-size:11px; font-weight:600; color:#4b5563; background:#f3f4f6; border:1px solid #e5e7eb; border-radius:8px; padding:6px 12px; cursor:pointer; transition:background 0.15s;"
                                       onmouseover="this.style.background='#e5e7eb'" onmouseout="this.style.background='#f3f4f6'" title="Pilih Foto Lain dari Galeri">
                                    <input type="file" id="gallery-input-{{$point->id}}" style="display:none;" accept="image/*" onchange="uploadSopPhoto(this, {{ $point->id }})">
                                    <i class="ph-bold ph-image" style="font-size:14px;"></i> Ganti Galeri
                                </label>
                            @endif
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
    <form action="{{ route('field-app.submit', $shipment->id, false) }}" method="POST" id="submitForm" style="margin-top: 10px; padding-bottom: 120px;">
        @csrf

        @if($errors->any())
            <div style="background:#fef2f2; border:1px solid #fecaca; border-radius:12px; padding:12px 14px; margin-bottom:12px; color:#b91c1c; font-size:12px;">
                <p style="font-weight:700; margin-bottom:4px; display:flex; align-items:center; gap:6px;">
                    <i class="ph-bold ph-warning-circle"></i> Tidak dapat menyimpan:
                </p>
                <ul style="margin:0; padding-left:20px;">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <input type="hidden" name="waktu_kedatangan_container" :value="waktuKedatangan">

        <!-- Data Utama Shipment (Terverifikasi dari Dokumen Shipment Order) -->
        <div style="background: white; border: 1.5px solid #d0e0f5; border-radius: 14px; padding: 14px; box-shadow: 0 2px 8px rgba(40,84,145,0.04);">
            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:10px; border-bottom:1px solid #f1f5f9; padding-bottom:8px;">
                <span style="font-size:11px; font-weight:700; color:#1e3a8a; display:flex; align-items:center; gap:6px;">
                    <i class="ph-bold ph-identification-badge" style="font-size:15px; color:#285491;"></i>
                    Identitas Pengiriman (Shipment Order)
                </span>
                <span style="font-size:10px; font-weight:700; background:#dcfce7; color:#166534; padding:2px 8px; border-radius:8px; display:inline-flex; align-items:center; gap:4px; border:1px solid #bbf7d0;">
                    <i class="ph-bold ph-check"></i> Terverifikasi di Awal
                </span>
            </div>

            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:10px; font-size:11px;">
                <div style="background:#f8fafc; padding:8px 10px; border-radius:8px; border:1px solid #e2e8f0;">
                    <span style="color:#64748b; font-size:10px; display:block;">No. Surat Jalan / Delivery / PL:</span>
                    <strong style="color:#1e3a8a; font-size:13px; font-family:monospace; letter-spacing:0.04em;">{{ $shipment->packing_list_no ?: '-' }}</strong>
                </div>
                <div style="background:#f8fafc; padding:8px 10px; border-radius:8px; border:1px solid #e2e8f0;">
                    <span style="color:#64748b; font-size:10px; display:block;">No. Container:</span>
                    <strong style="color:#1e293b; font-size:13px; font-family:monospace;">{{ $shipment->nomor_container_atau_plat ?: '-' }}</strong>
                </div>
                <div style="background:#f8fafc; padding:8px 10px; border-radius:8px; border:1px solid #e2e8f0;">
                    <span style="color:#64748b; font-size:10px; display:block;">Shipment Group:</span>
                    <strong style="color:#1e293b; font-size:12px; font-family:monospace;">{{ $shipment->shipment_group ?: '-' }}</strong>
                </div>
                <div style="background:#f8fafc; padding:8px 10px; border-radius:8px; border:1px solid #e2e8f0;">
                    <span style="color:#64748b; font-size:10px; display:block;">Shipment No:</span>
                    <strong style="color:#1e293b; font-size:12px; font-family:monospace;">{{ $shipment->shipment_no ?: '-' }}</strong>
                </div>
                <div style="background:#f8fafc; padding:8px 10px; border-radius:8px; border:1px solid #e2e8f0;">
                    <span style="color:#64748b; font-size:10px; display:block;">Plat Nomor Kendaraan:</span>
                    <strong style="color:#1e293b; font-size:12px;">{{ $shipment->plat_nomor ?: '-' }}</strong>
                </div>
                <div style="background:#f8fafc; padding:8px 10px; border-radius:8px; border:1px solid #e2e8f0;">
                    <span style="color:#64748b; font-size:10px; display:block;">Nama Sopir:</span>
                    <strong style="color:#1e293b; font-size:12px;">{{ $shipment->nama_sopir ?: '-' }}</strong>
                </div>
                <div style="grid-column: span 2; background:#f0fdf4; padding:8px 10px; border-radius:8px; border:1px solid #bbf7d0; display:flex; align-items:center; justify-content:space-between;">
                    <div>
                        <span style="color:#166534; font-size:10px; font-weight:600; display:block;">Waktu Mulai Staging Container (Diisi di Form Awal):</span>
                        <strong style="color:#15803d; font-size:12px; font-family:monospace;" x-text="waktuKedatangan ? waktuKedatangan + ' WIB' : '{{ $shipment->waktu_kedatangan_container ? $shipment->waktu_kedatangan_container->format('d/m/Y H:i') . ' WIB' : '-' }}'">
                            {{ $shipment->waktu_kedatangan_container ? $shipment->waktu_kedatangan_container->format('d/m/Y H:i') . ' WIB' : '-' }}
                        </strong>
                    </div>
                    <span style="font-size:9px; font-weight:700; background:#dcfce7; color:#15803d; padding:2px 8px; border-radius:6px; border:1px solid #86efac;">
                        ✓ TERCATAT DI AWAL
                    </span>
                </div>
            </div>
        </div>

        <!-- ═══════════════════════════════════════════════════════════ -->
        <!-- WAKTU SELESAI STAGING (AKHIR FORM TIMELINE)                -->
        <!-- ═══════════════════════════════════════════════════════════ -->
        <div class="section-card" style="margin-top: 10px;">
            <div class="section-card-header" style="background: linear-gradient(90deg, rgba(40,84,145,0.08), transparent);">
                <div style="width: 34px; height: 34px; border-radius: 10px; background: #eff4fc; border: 1px solid #bfcfe8; display: flex; align-items: center; justify-content: center; flex-shrink:0;">
                    <i class="ph-fill ph-check-circle" style="font-size: 18px; color: #285491;"></i>
                </div>
                <div style="flex: 1;">
                    <div style="display: flex; align-items: center; justify-content: space-between;">
                        <p style="font-size: 13px; font-weight: 700; color: #1f2937;">Waktu Pelaksanaan Staging Container — Selesai</p>
                        <span style="font-size: 10px; font-weight: 700; color: #285491; background:#e0ecfb; border:1px solid #bfcfe8; border-radius: 6px; padding: 1px 7px;">⏱ AKHIR FORM</span>
                    </div>
                    <p style="font-size: 10px; color: #64748b;">Dicatat saat seluruh muatan barang selesai dimuat (otomatis terisi saat ini &amp; dapat diedit)</p>
                </div>
            </div>
            <div class="section-card-body" style="padding: 14px;">
                <div style="background: #f8fafc; border: 1.5px solid #cbd5e1; border-radius: 12px; padding: 12px; transition: all 0.2s;"
                     :style="waktuKeberangkatan ? 'border-color: #86efac; background: #f0fdf4;' : ''">
                    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:6px;">
                        <label class="field-label" style="margin-bottom:0; display:flex; align-items:center; gap:5px; font-weight:700; color:#1e293b;">
                            <i class="ph-bold ph-flag-checkered" style="color:#285491; font-size:16px;"></i>
                            Waktu Selesai Staging <span style="color:#ef4444;">*</span>
                            <span style="font-size:10px; font-weight:700; color:#285491; background:#e0ecfb; border:1px solid #bfcfe8; border-radius:6px; padding:1px 6px; margin-left:4px;">
                                Format 24 Jam
                            </span>
                        </label>
                        <button type="button" @click="setNow('keberangkatan')"
                                style="font-size:10px; font-weight:700; color:#285491; background:#eff4fc; border:1px solid #bfcfe8; border-radius:6px; padding:3px 8px; cursor:pointer; display:flex; align-items:center; gap:3px; transition:background 0.15s;"
                                onmouseover="this.style.background='#dbe7f6'"
                                onmouseout="this.style.background='#eff4fc'">
                            <i class="ph-bold ph-clock"></i> Set Sekarang
                        </button>
                    </div>
                    <div style="position:relative;">
                        <input type="text" id="waktu_keberangkatan_container" name="waktu_keberangkatan_container"
                               value="{{ old('waktu_keberangkatan_container', $shipment->waktu_keberangkatan_container ? $shipment->waktu_keberangkatan_container->format('Y-m-d H:i') : now('Asia/Jakarta')->format('Y-m-d H:i')) }}"
                               placeholder="Pilih tanggal & jam format 24 jam (contoh: {{ now('Asia/Jakarta')->format('d/m/Y H:i') }})..."
                               class="field-input flatpickr-custom-input"
                               style="width:100%; box-sizing:border-box; background:white; font-size:13px; font-weight:600; padding-right:38px; cursor:pointer;"
                               required>
                        <div style="position:absolute; right:12px; top:50%; transform:translateY(-50%); pointer-events:none; color:#285491; display:flex; align-items:center;">
                            <i class="ph-bold ph-calendar-blank" style="font-size:17px;"></i>
                        </div>
                    </div>
                    <p style="font-size:10px; color:#64748b; margin-top:4px;">Waktu saat proses pemuatan selesai dan container siap diberangkatkan (format 24 jam: 00:00 - 23:59 WIB). Terisi otomatis dan dapat diedit.</p>
                </div>
            </div>
        </div>

        <!-- Sticky Footer Submit Card (Seragam Rounded 16px dengan Card di Atasnya) -->
        <div style="position:fixed; bottom:12px; left:0; right:0; z-index:100; pointer-events:none; padding: 0 16px;">
            <div style="max-width:860px; margin:0 auto; background:white; border-radius:16px; border:1.5px solid #e8edf3; padding:12px 16px 14px; box-shadow: 0 8px 30px rgba(40,84,145,0.16), 0 2px 8px rgba(0,0,0,0.06); pointer-events:auto; display:flex; flex-direction:column; gap:8px;">
                <!-- Status Info Row -->
                <div style="display:flex; align-items:center; justify-content:space-between; font-size:11px;">
                    <span style="font-weight:700; color:#374151; display:flex; align-items:center; gap:6px;">
                        <i class="ph-bold ph-shield-check" :style="canSubmit ? 'color:#059e3d;' : 'color:#d97706;'"></i>
                        <span x-text="canSubmit ? 'Verifikasi Lengkap' : 'Kelengkapan Sebelum Submit'"></span>
                    </span>
                    <span style="font-size:11px; font-weight:700;" :style="isPhotosComplete ? 'color:#059e3d;' : 'color:#d97706;'">
                        <span x-text="mandatoryUploadedCount"></span> / <span x-text="totalMandatoryCount"></span> Foto Wajib
                    </span>
                </div>

                <!-- Info banner when not ready -->
                <div x-cloak x-show="!canSubmit" style="font-size:11px; color:#b45309; background:#fffbeb; border:1px solid #fde68a; border-radius:8px; padding:6px 10px; display:flex; align-items:flex-start; gap:6px; line-height:1.4;">
                    <i class="ph-fill ph-warning-circle" style="font-size:14px; margin-top:1px; flex-shrink:0;"></i>
                    <div>
                        <span>Tombol submit dinonaktifkan. Harap lengkapi: </span>
                        <span style="font-weight:700;" x-text="missingSummary.join(' & ')"></span>.
                    </div>
                </div>

                <!-- Ready banner -->
                <div x-cloak x-show="canSubmit" style="font-size:11px; color:#065f46; background:#ecfdf5; border:1px solid #a7f3d0; border-radius:8px; padding:6px 10px; display:flex; align-items:center; gap:6px; font-weight:600;">
                    <i class="ph-fill ph-check-circle" style="font-size:14px; flex-shrink:0; color:#059e3d;"></i>
                    Seluruh 26 bukti foto wajib & jam waktu pelaksanaan staging terisi lengkap.
                </div>

                <button type="submit"
                        :disabled="!canSubmit"
                        :style="canSubmit
                            ? 'flex:1; background:linear-gradient(135deg, #059e3d, #0d5950); color:white; font-size:14px; font-weight:700; border:none; border-radius:12px; padding:13px; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:8px; box-shadow: 0 4px 14px rgba(5,158,61,0.35); transition:all 0.2s;'
                            : 'flex:1; background:#94a3b8; color:#f1f5f9; font-size:14px; font-weight:700; border:none; border-radius:12px; padding:13px; cursor:not-allowed; display:flex; align-items:center; justify-content:center; gap:8px; box-shadow:none; opacity:0.7; transition:all 0.2s;'"
                        onmouseover="if (!this.disabled) { this.style.transform='translateY(-1px)'; this.style.boxShadow='0 6px 18px rgba(5,158,61,0.45)'; }"
                        onmouseout="if (!this.disabled) { this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 14px rgba(5,158,61,0.35)'; }">
                    <i class="ph-bold ph-check-circle" style="font-size:18px;"></i>
                    Submit Final Data
                </button>
            </div>
        </div>

    </form>

    <!-- MODAL PRATINJAU BUKTI FOTO & VIDEO (LIGHTBOX OPERATOR) -->
    <div id="sop-preview-lightbox"
         onclick="if (event.target === this) closeSopPreview()"
         style="display: none; position: fixed; inset: 0; z-index: 99999; align-items: center; justify-content: center; padding: 12px; background: rgba(15, 23, 42, 0.88); backdrop-filter: blur(8px);">

        <div style="background: #0f172a; border: 1px solid rgba(255,255,255,0.18); border-radius: 20px; width: 100%; max-width: 840px; max-height: 94vh; display: flex; flex-direction: column; overflow: hidden; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.85);"
             onclick="event.stopPropagation()">

            <!-- Modal Header -->
            <div style="padding: 12px 16px; background: rgba(30, 41, 59, 0.95); border-bottom: 1px solid rgba(255,255,255,0.1); display: flex; align-items: center; justify-content: space-between; gap: 12px;">
                <div style="min-width: 0;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span id="lightbox-point-urutan" style="font-size: 11px; font-weight: 800; background: #2563eb; color: white; padding: 2px 8px; border-radius: 6px;">Titik -</span>
                        <h3 id="lightbox-point-title" style="font-size: 13px; font-weight: 700; color: white; margin: 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">-</h3>
                    </div>
                    <p id="lightbox-point-desc" style="font-size: 11px; color: #94a3b8; margin: 2px 0 0 0;">-</p>
                </div>
                <button type="button"
                        onclick="closeSopPreview()"
                        style="width: 32px; height: 32px; border-radius: 50%; background: rgba(255,255,255,0.1); border: none; color: #cbd5e1; font-size: 18px; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: background 0.2s;"
                        onmouseover="this.style.background='rgba(255,255,255,0.2)'; this.style.color='white'"
                        onmouseout="this.style.background='rgba(255,255,255,0.1)'; this.style.color='#cbd5e1'">
                    <i class="ph-bold ph-x"></i>
                </button>
            </div>

            <!-- Modal Media Content -->
            <div style="flex: 1; min-height: 250px; max-height: 68vh; background: #000; display: flex; align-items: center; justify-content: center; position: relative; overflow: hidden;">
                <img id="lightbox-img"
                     src=""
                     alt="Bukti Foto Evidence"
                     style="max-width: 100%; max-height: 68vh; object-fit: contain; display: none; margin: auto;">

                <video id="lightbox-video"
                       src=""
                       controls playsinline
                       style="max-width: 100%; max-height: 68vh; object-fit: contain; display: none; margin: auto; background: black;"></video>

                <!-- Watermark Info Pill inside Modal -->
                <div style="position: absolute; bottom: 12px; left: 12px; right: 12px; background: rgba(0,0,0,0.8); backdrop-filter: blur(4px); border: 1px solid rgba(255,255,255,0.15); border-radius: 8px; padding: 6px 12px; display: flex; align-items: center; justify-content: space-between; gap: 8px; font-size: 11px; color: #94a3b8; pointer-events: none; z-index: 10;">
                    <div style="display: flex; align-items: center; gap: 6px; font-family: monospace; color: #6ee7b7; font-weight: 700;">
                        <i class="ph-bold ph-calendar-check" style="font-size: 14px;"></i>
                        <span id="lightbox-timestamp">-</span>
                    </div>
                    <span style="font-size: 10px; color: #94a3b8;">Stempel Digital SPV-Track</span>
                </div>
            </div>

            <!-- Modal Footer Controls -->
            <div style="padding: 12px 16px; background: rgba(30, 41, 59, 0.95); border-top: 1px solid rgba(255,255,255,0.1); display: flex; align-items: center; justify-content: space-between; gap: 10px; flex-wrap: wrap;">
                <p style="font-size: 11px; color: #94a3b8; margin: 0; line-height: 1.3;">
                    <i class="ph-fill ph-info" style="color: #60a5fa; margin-right: 3px;"></i>
                    Pastikan foto/video jelas dan stempel waktu terbaca sebelum submit final.
                </p>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <button type="button"
                            onclick="retakeFromLightbox('camera')"
                            style="display: flex; align-items: center; gap: 5px; font-size: 12px; font-weight: 700; color: white; background: #2563eb; border: none; padding: 7px 14px; border-radius: 8px; cursor: pointer; transition: background 0.2s;"
                            onmouseover="this.style.background='#1d4ed8'"
                            onmouseout="this.style.background='#2563eb'">
                        <i class="ph-bold ph-camera" style="font-size: 15px;"></i>
                        <span id="lightbox-btn-retake-text">Foto Ulang</span>
                    </button>
                    <button type="button"
                            onclick="retakeFromLightbox('gallery')"
                            style="display: flex; align-items: center; gap: 5px; font-size: 12px; font-weight: 600; color: #cbd5e1; background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2); padding: 7px 14px; border-radius: 8px; cursor: pointer; transition: all 0.2s;"
                            onmouseover="this.style.background='rgba(255,255,255,0.2)'; this.style.color='white'"
                            onmouseout="this.style.background='rgba(255,255,255,0.1)'; this.style.color='#cbd5e1'">
                        <i class="ph-bold ph-image" style="font-size: 15px;"></i>
                        <span>Pilih Galeri</span>
                    </button>
                    <button type="button"
                            onclick="closeSopPreview()"
                            style="font-size: 12px; font-weight: 600; color: #94a3b8; background: transparent; border: 1px solid rgba(255,255,255,0.1); padding: 7px 14px; border-radius: 8px; cursor: pointer; transition: all 0.2s;"
                            onmouseover="this.style.background='rgba(255,255,255,0.05)'; this.style.color='white'"
                            onmouseout="this.style.background='transparent'; this.style.color='#94a3b8'">
                        Tutup
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>

    <script>
        // Upload photo SOP with live preview and timestamp
        function uploadSopPhoto(input, pointId) {
            if (input.files && input.files[0]) {
                const file = input.files[0];
                const preview = document.getElementById('preview-' + pointId);
                const videoPreview = document.getElementById('video-preview-' + pointId);
                const wrapper = document.getElementById('preview-wrapper-' + pointId);
                const actionBar = document.getElementById('action-bar-' + pointId);
                const iconBox = document.getElementById('icon-' + pointId);
                const statusBox = document.getElementById('status-' + pointId);
                const tsBox = document.getElementById('timestamp-' + pointId);
                const tsText = tsBox ? tsBox.querySelector('.ts-text') : null;
                const numBadge = document.getElementById('num-badge-' + pointId);

                const isVideo = file.type.startsWith('video/') || file.name.match(/\.(mp4|mov|webm|3gp|avi|m4v)$/i);

                // 1. Instant local preview
                const blobUrl = URL.createObjectURL(file);
                if (isVideo) {
                    if (preview) preview.style.display = 'none';
                    if (videoPreview) {
                        videoPreview.src = blobUrl;
                        videoPreview.style.display = 'block';
                    }
                } else {
                    if (videoPreview) videoPreview.style.display = 'none';
                    if (preview) {
                        preview.src = blobUrl;
                        preview.style.display = 'block';
                    }
                }
                wrapper.style.display = 'block';
                if (iconBox) iconBox.style.display = 'none';
                if (actionBar) actionBar.style.display = 'flex';

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
                    statusBox.innerHTML = '<span style="color:#fbbf24;"><i class="ph-bold ph-spinner animate-spin"></i> Memproses stempel...</span>';
                }

                // Temporary dataset for lightbox
                if (wrapper) {
                    wrapper.dataset.mediaUrl = blobUrl;
                    wrapper.dataset.isVideo = isVideo ? 'true' : 'false';
                    wrapper.dataset.timestamp = `${formattedTimestamp} WIB`;
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
                .then(async r => {
                    if (!r.ok) {
                        let errMsg = 'HTTP ' + r.status;
                        try {
                            const errData = await r.json();
                            if (errData && errData.message) {
                                errMsg = errData.message;
                            }
                        } catch (e) {}
                        throw new Error(errMsg);
                    }
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

                        // Update preview with watermarked image from server (cache buster prevents stale browser cache)
                        if (data.file_url) {
                            if (isVideo) {
                                if (videoPreview) videoPreview.src = data.file_url;
                            } else {
                                if (preview) {
                                    preview.src = data.file_url + (data.file_url.includes('?') ? '&' : '?') + 't=' + Date.now();
                                }
                            }
                        }

                        if (wrapper) {
                            wrapper.dataset.mediaUrl = data.file_url || blobUrl;
                            wrapper.dataset.isVideo = isVideo ? 'true' : 'false';
                            wrapper.dataset.timestamp = data.captured_at || `${formattedTimestamp} WIB`;
                        }

                        // Reset input so selecting the same filename again still fires onchange
                        input.value = '';

                        // Dispatch event to Alpine reactive manager
                        window.dispatchEvent(new CustomEvent('photo-uploaded', { detail: { pointId: pointId } }));
                    } else {
                        throw new Error(data.message || 'Gagal');
                    }
                })
                .catch(err => {
                    console.error('Upload error:', err);
                    if (statusBox) {
                        statusBox.innerHTML = `<span style="color:#f87171;" title="${err.message}">Gagal: ${err.message}</span>`;
                    }
                    if (window.showToast) {
                        window.showToast(err.message, 'error');
                    }
                });
            }
        }
    </script>

</x-field-layout>
