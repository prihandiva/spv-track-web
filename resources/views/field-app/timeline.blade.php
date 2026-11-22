<x-field-layout>
@php
    $isSubmitted = ($shipment->status === 'submitted');
    $mandatoryPointIds = $points->where('wajib', true)->pluck('id')->values()->all();
    $initialUploadedPointIds = $shipment->evidenceItems->pluck('sop_photo_point_id')->filter()->unique()->values()->all();

    $sopPointsJsData = $points->map(function ($p) use ($shipment) {
        $existing = $shipment->evidenceItems->firstWhere('sop_photo_point_id', $p->id);
        $isVideo = ($p->tipe_item === 'video' || $p->urutan == 27 || stripos($p->nama_titik, 'video') !== false);
        return [
            'id' => (int) $p->id,
            'urutan' => (int) $p->urutan,
            'nama_titik' => $p->nama_titik,
            'deskripsi' => $p->deskripsi ?? '',
            'wajib' => (bool) $p->wajib,
            'is_video' => $isVideo,
            'has_evidence' => !is_null($existing),
            'evidence_url' => $existing ? $existing->file_url : null,
            'captured_at' => $existing && $existing->captured_at ? $existing->captured_at->format('Y-m-d H:i:s \W\I\B') : null,
        ];
    })->values();
@endphp

<script>
    window._sopPointsData = @json($sopPointsJsData);
    window._uploadPhotoRoute = "{{ route('field-app.upload-photo', $shipment->id, false) }}";
    window._csrfToken = "{{ csrf_token() }}";
</script>

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

        if (type === 'camera') {
            window.openFullscreenCamera(Number(pointId));
            return;
        }

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
            window.closeVideoSizeWarningModal();
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
     @photo-uploaded.window="addUploadedPoint($event.detail.pointId)"
     style="display: flex; flex-direction: column; gap: 20px;">

    @if($isSubmitted)
    <div style="background: #fef2f2; border: 1.5px solid #f87171; border-radius: 14px; padding: 14px 16px; display: flex; align-items: center; justify-content: space-between; gap: 12px; color: #991b1b; box-shadow: 0 2px 8px rgba(239,68,68,0.12);">
        <div style="display: flex; align-items: center; gap: 10px;">
            <i class="ph-fill ph-lock-key" style="font-size: 24px; color: #dc2626; flex-shrink: 0;"></i>
            <div>
                <p style="font-size: 13px; font-weight: 800; margin: 0 0 2px 0;">Dokumen Telah Disubmit Final (Terkunci)</p>
                <p style="font-size: 11px; margin: 0; color: #b91c1c; line-height: 1.35;">
                    Seluruh foto evidence telah dikunci permanen untuk menjaga keaslian waktu (timestamp) dan tidak dapat diubah/diganti lagi.
                </p>
            </div>
        </div>
        <a href="{{ route('shipments.show', $shipment->id) }}" style="padding: 8px 14px; background: #dc2626; color: white; border-radius: 10px; font-size: 11px; font-weight: 700; text-decoration: none; white-space: nowrap; flex-shrink: 0;">
            Lihat Detail Dokumen &rarr;
        </a>
    </div>
    @endif

    <!-- Shipment Header Card (Packing List as Primary Identifier) -->
    <div style="background: linear-gradient(135deg, #1a3c6e 0%, #285491 60%, #0d5950 100%); border-radius: 16px; padding: 16px; color: white; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 4px 16px rgba(40,84,145,0.3);">
        <div>
            <p style="font-size: 10px; font-weight: 600; color: rgba(255,255,255,0.6); text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 3px;">No. Surat Jalan / Delivery / Packing List</p>
            <h1 style="font-size: 18px; font-weight: 800; font-family: monospace; letter-spacing: 0.04em; line-height: 1.1;">{{ $shipment->packing_list_no ?: ($shipment->nomor_container_atau_plat ?? 'PL-' . $shipment->id) }}</h1>
            <p style="font-size: 11px; color: rgba(255,255,255,0.85); margin-top: 4px;">
                @if($shipment->jenis_pengiriman === 'export' || $shipment->shipment_group)
                    Group: <span style="font-weight:700; color:white;">{{ $shipment->shipment_group ?: '-' }}</span> &bull;
                @endif
                Shpt: <span style="font-weight:700; color:white;">{{ $shipment->shipment_no ?: '-' }}</span> &bull;
                Plat: <span style="font-weight:700; color:white;">{{ $shipment->plat_nomor ?: '-' }}</span> &bull;
                Cont: <span style="font-weight:700; color:white;">{{ $shipment->nomor_container_atau_plat ?: '-' }}</span>
                @if($shipment->jenis_pengiriman === 'export' && $shipment->nomor_seal)
                    &bull; Seal: <span style="font-weight:700; color:white;">{{ $shipment->nomor_seal }}</span>
                @endif
                @if($shipment->agen_forwarding)
                    &bull; Fwd: <span style="font-weight:700; color:white;">{{ $shipment->agen_forwarding }}</span>
                @endif
                @if($shipment->tujuan_pengiriman)
                    &bull; Tujuan (Customer): <span style="font-weight:700; color:white;">{{ $shipment->tujuan_pengiriman }}</span>
                @endif
                &bull; Akun: <span style="font-weight:700; color:white;">{{ $shipment->user?->nama_warehouse ?? 'Akun Sistem' }}</span>
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

    <!-- 2 Form Inspeksi Wajib (Lenzing Standards) -->
    <div style="background: white; border-radius: 16px; padding: 14px 16px; border: 1.5px solid #dbeafe; box-shadow: 0 2px 10px rgba(40,84,145,0.06);">
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

    <!-- BANNER HERO: BUKA KAMERA CEPAT FULLSCREEN (PORTRAIT & LANDSCAPE SESUAI SKETSA) -->
    <div style="background: linear-gradient(135deg, #1e3a8a 0%, #285491 55%, #0d5950 100%); border-radius: 16px; padding: 16px; color: white; box-shadow: 0 4px 18px rgba(40,84,145,0.28); display: flex; flex-direction: column; gap: 12px; border: 1.5px solid rgba(255,255,255,0.15);">
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <div style="width: 40px; height: 40px; border-radius: 12px; background: rgba(255,255,255,0.18); display: flex; align-items: center; justify-content: center; font-size: 20px; box-shadow: 0 2px 6px rgba(0,0,0,0.15);">
                    <i class="ph-bold ph-camera"></i>
                </div>
                <div>
                    <h2 style="font-size: 14px; font-weight: 800; margin: 0; line-height: 1.2;">Kamera Cepat SOP (Fullscreen)</h2>
                    <p style="font-size: 11px; color: rgba(255,255,255,0.85); margin: 2px 0 0;">Ambil foto langsung tanpa bolak-balik klik &bull; Bebas pilih titik &bull; Portrait &amp; Landscape</p>
                </div>
            </div>
            <span style="font-size: 10px; font-weight: 800; background: rgba(5,158,61,0.3); color: #86efac; border: 1px solid rgba(134,239,172,0.4); padding: 3px 10px; border-radius: 20px; display: inline-flex; align-items: center; gap: 4px;">
                <i class="ph-bold ph-lightning"></i> Mode Praktis Lapangan
            </span>
        </div>

        <button type="button"
                onclick="openFullscreenCamera()"
                style="width: 100%; background: #ffffff; color: #1e3a8a; border: none; font-size: 13px; font-weight: 800; padding: 12px 18px; border-radius: 12px; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; box-shadow: 0 4px 14px rgba(0,0,0,0.2); transition: transform 0.15s, background 0.15s;"
                onmouseover="this.style.background='#f0f9ff'; this.style.transform='scale(1.01)'"
                onmouseout="this.style.background='#ffffff'; this.style.transform='scale(1)'">
            <i class="ph-bold ph-aperture" style="font-size: 20px; color: #0284c7;"></i>
            <span>Buka Kamera Fullscreen &bull; Mulai Jepret Foto SOP</span>
        </button>

        <div style="display: flex; align-items: center; justify-content: space-between; font-size: 10px; color: rgba(255,255,255,0.75); border-top: 1px solid rgba(255,255,255,0.15); padding-top: 8px;">
            <span><i class="ph-bold ph-device-rotate"></i> Putar HP mendatar (Landscape) untuk sudut pandang luas</span>
            <span><i class="ph-bold ph-check-circle"></i> Titik tidak harus urut</span>
        </div>
    </div>

    <!-- Card Container untuk Area Unggah Evidence (27 Titik SOP) -->
    <div class="section-card" style="background: white; border-radius: 16px; border: 1px solid #e8edf3; box-shadow: 0 1px 4px rgba(40,84,145,0.05); overflow: hidden;">
        <div class="section-card-header" style="background: linear-gradient(90deg, rgba(40,84,145,0.06), transparent); padding: 12px 16px; display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #f0f4f9;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <div style="width: 30px; height: 30px; border-radius: 8px; background: #285491; color: white; display: flex; align-items: center; justify-content: center; font-size: 14px;">
                    <i class="ph-bold ph-camera"></i>
                </div>
                <div>
                    <h3 style="font-size: 12px; font-weight: 800; color: #1e3a8a; text-transform: uppercase; letter-spacing: 0.04em; margin: 0;">Unggah Evidence</h3>
                    <p style="font-size: 10px; color: #64748b; margin: 1px 0 0;">Posting bukti foto &amp; video staging sesuai urutan 27 titik SOP</p>
                </div>
            </div>
            <span style="font-size: 10px; font-weight: 700; color: #285491; background: #eff4fc; border: 1px solid #bfcfe8; padding: 2px 8px; border-radius: 20px;">
                {{ count($points) }} Titik SOP
            </span>
        </div>
        <div class="section-card-body" style="padding: 14px 16px;">
            <div style="display: flex; flex-direction: column; gap: 10px; position: relative; padding-left: 4px;">
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
                        @if($isVideoPoint)
                            <div style="font-size: 10px; color: #dc2626; font-weight: 700; margin-top: 3px; display: inline-flex; align-items: center; gap: 4px; background: #fef2f2; border: 1px solid #fecaca; padding: 2px 7px; border-radius: 6px;">
                                <i class="ph-bold ph-warning-circle"></i> Maksimal ukuran video: 6MB
                            </div>
                        @endif
                    </div>
                    @if($point->wajib)
                        <span style="font-size: 9px; font-weight: 700; color: #ef4444; background: #fef2f2; border: 1px solid #fecaca; padding: 2px 7px; border-radius: 20px; white-space: nowrap; margin-left: 8px; flex-shrink: 0;">WAJIB</span>
                    @else
                        <span style="font-size: 9px; font-weight: 600; color: {{ $isVideoPoint ? '#dc2626' : '#9ca3af' }}; background: {{ $isVideoPoint ? '#fee2e2' : '#f9fafb' }}; border: 1px solid {{ $isVideoPoint ? '#fca5a5' : '#f3f4f6' }}; padding: 2px 7px; border-radius: 20px; white-space: nowrap; margin-left: 8px; flex-shrink: 0;">
                            {{ $isVideoPoint ? 'Video (Maks 6MB)' : 'Opsional' }}
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
                                <span style="font-size: 9px; color: #ef4444; font-weight: 600;">Camcorder (Maks. 6MB)</span>
                            </label>
                            <div style="width: 1px; background: #f0f4f9;"></div>
                            <label style="flex: 1; padding: 12px 8px; display: flex; flex-direction: column; align-items: center; justify-content: center; cursor: pointer; transition: background 0.2s;"
                                   onmouseover="this.style.background='#eff4fc'" onmouseout="this.style.background='transparent'">
                                <input type="file" id="initial-gallery-input-{{$point->id}}" style="display:none;" accept="video/*" onchange="uploadSopPhoto(this, {{ $point->id }})">
                                <div style="width:34px; height:34px; border-radius:50%; background:#eff4fc; display:flex; align-items:center; justify-content:center; margin-bottom:4px;">
                                    <i class="ph-bold ph-film-strip" style="font-size: 18px; color: #285491;"></i>
                                </div>
                                <span style="font-size: 11px; font-weight: 700; color: #285491;">Pilih Video</span>
                                <span style="font-size: 9px; color: #64748b; font-weight: 600;">Galeri (Maks. 6MB)</span>
                            </label>
                        </div>
                    @else
                        <!-- Photo Initial Buttons -->
                        <div id="icon-{{$point->id}}" style="display: {{ $existing ? 'none' : 'flex' }}; border-bottom: 1px solid #f0f4f9;">
                            <button type="button"
                                    onclick="openFullscreenCamera({{ $point->id }})"
                                    style="flex: 1; padding: 12px 8px; display: flex; flex-direction: column; align-items: center; justify-content: center; cursor: pointer; transition: background 0.2s; background: transparent; border: none;"
                                    onmouseover="this.style.background='#eff4fc'" onmouseout="this.style.background='transparent'">
                                <i class="ph-bold ph-camera" style="font-size: 20px; color: #285491; margin-bottom: 3px; display: block;"></i>
                                <span style="font-size: 10px; font-weight: 700; color: #285491;">Buka Kamera</span>
                            </button>
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
                         data-media-url="{{ $existing ? $existing->file_url : '' }}"
                         data-is-video="{{ $isExistingVideo ? 'true' : 'false' }}"
                         data-timestamp="{{ $existing && $existing->captured_at ? $existing->captured_at->format('Y-m-d H:i:s') . ' WIB' : '' }}"
                         style="display: {{ $existing ? 'block' : 'none' }}; position: relative; height: 180px; background: #0f172a; cursor: pointer;"
                         title="Klik untuk melihat pratinjau stempel & kejelasan bukti">

                        <img id="preview-{{$point->id}}"
                             src="{{ $existing && !$isExistingVideo ? $existing->file_url : '' }}"
                             style="width:100%; height:100%; object-fit:contain; opacity:0.95; display: {{ $existing && !$isExistingVideo ? 'block' : 'none' }};" />

                        <video id="video-preview-{{$point->id}}"
                               src="{{ $existing && $isExistingVideo ? $existing->file_url : '' }}"
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
                        @if(!$isSubmitted)
                        <div style="display:flex; align-items:center; gap:6px;">
                            @if($isVideoPoint)
                                <label style="display:flex; align-items:center; gap:5px; font-size:11px; font-weight:700; color:#dc2626; background:#fee2e2; border:1px solid #fecaca; border-radius:8px; padding:6px 11px; cursor:pointer; transition:background 0.15s;"
                                       onmouseover="this.style.background='#fca5a5'" onmouseout="this.style.background='#fee2e2'" title="Rekam Ulang Video (Maksimal 6MB)">
                                    <input type="file" id="camera-input-{{$point->id}}" style="display:none;" accept="video/*" capture="environment" onchange="uploadSopPhoto(this, {{ $point->id }})">
                                    <i class="ph-fill ph-video-camera" style="font-size:14px;"></i> Rekam Ulang (Maks 6MB)
                                </label>
                                <label style="display:flex; align-items:center; gap:5px; font-size:11px; font-weight:600; color:#285491; background:#eff4fc; border:1px solid #bfcfe8; border-radius:8px; padding:6px 11px; cursor:pointer; transition:background 0.15s;"
                                       onmouseover="this.style.background='#dbe7f6'" onmouseout="this.style.background='#eff4fc'" title="Pilih File Video Lain (Maksimal 6MB)">
                                    <input type="file" id="gallery-input-{{$point->id}}" style="display:none;" accept="video/*" onchange="uploadSopPhoto(this, {{ $point->id }})">
                                    <i class="ph-bold ph-film-strip" style="font-size:14px;"></i> Ganti File (Maks 6MB)
                                </label>
                            @else
                                <button type="button"
                                       onclick="openFullscreenCamera({{ $point->id }})"
                                       style="display:flex; align-items:center; gap:5px; font-size:11px; font-weight:700; color:#285491; background:#eff4fc; border:1.5px solid #bfcfe8; border-radius:8px; padding:6px 12px; cursor:pointer; transition:background 0.15s;"
                                       onmouseover="this.style.background='#dbe7f6'" onmouseout="this.style.background='#eff4fc'" title="Foto Ulang dari Kamera">
                                    <i class="ph-bold ph-camera" style="font-size:14px;"></i> Foto Ulang
                                </button>
                                <label style="display:flex; align-items:center; gap:5px; font-size:11px; font-weight:600; color:#4b5563; background:#f3f4f6; border:1px solid #e5e7eb; border-radius:8px; padding:6px 12px; cursor:pointer; transition:background 0.15s;"
                                       onmouseover="this.style.background='#e5e7eb'" onmouseout="this.style.background='#f3f4f6'" title="Pilih Foto Lain dari Galeri">
                                    <input type="file" id="gallery-input-{{$point->id}}" style="display:none;" accept="image/*" onchange="uploadSopPhoto(this, {{ $point->id }})">
                                    <i class="ph-bold ph-image" style="font-size:14px;"></i> Ganti Galeri
                                </label>
                            @endif
                        </div>
                        @else
                        <span style="display:inline-flex; align-items:center; gap:4px; font-size:11px; font-weight:700; color:#059e3d; background:#ecfdf5; border:1px solid #a7f3d0; border-radius:8px; padding:6px 10px;">
                            <i class="ph-bold ph-lock-key" style="font-size:12px;"></i> Terkunci
                        </span>
                        @endif
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
        </div>
    </div>

    <!-- Surat Jalan Form -->
    <form action="{{ route('field-app.submit', $shipment->id, false) }}" method="POST" id="submitForm" style="display: flex; flex-direction: column; gap: 20px; padding-bottom: 120px;">
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
                    <span style="color:#64748b; font-size:10px; display:block;">Shipment No:</span>
                    <strong style="color:#1e293b; font-size:12px; font-family:monospace;">{{ $shipment->shipment_no ?: '-' }}</strong>
                </div>
                <div style="background:#f8fafc; padding:8px 10px; border-radius:8px; border:1px solid #e2e8f0;">
                    <span style="color:#64748b; font-size:10px; display:block;">Plat Nomor Kendaraan:</span>
                    <strong style="color:#1e293b; font-size:12px;">{{ $shipment->plat_nomor ?: '-' }}</strong>
                </div>
                <div style="background:#f8fafc; padding:8px 10px; border-radius:8px; border:1px solid #e2e8f0;">
                    <span style="color:#64748b; font-size:10px; display:block;">Jenis Pengiriman:</span>
                    <strong style="color:#1e293b; font-size:12px; text-transform:uppercase;">{{ $shipment->jenis_pengiriman ?: 'EXPORT' }}</strong>
                </div>
                <div style="background:#f8fafc; padding:8px 10px; border-radius:8px; border:1px solid #e2e8f0;">
                    <span style="color:#64748b; font-size:10px; display:block;">Nama Sopir:</span>
                    <strong style="color:#1e293b; font-size:12px;">{{ $shipment->nama_sopir ?: '-' }}</strong>
                </div>

                @if($shipment->jenis_pengiriman === 'export' || $shipment->shipment_group || $shipment->nomor_seal)
                    <div style="background:#f8fafc; padding:8px 10px; border-radius:8px; border:1px solid #e2e8f0;">
                        <span style="color:#64748b; font-size:10px; display:block;">Shipment Group (Export):</span>
                        <strong style="color:#1e293b; font-size:12px; font-family:monospace;">{{ $shipment->shipment_group ?: '-' }}</strong>
                    </div>
                    <div style="background:#f8fafc; padding:8px 10px; border-radius:8px; border:1px solid #e2e8f0;">
                        <span style="color:#64748b; font-size:10px; display:block;">Nomor Seal (Export):</span>
                        <strong style="color:#1e293b; font-size:12px; font-family:monospace;">{{ $shipment->nomor_seal ?: '-' }}</strong>
                    </div>
                @endif

                @if($shipment->agen_forwarding)
                    <div style="background:#f8fafc; padding:8px 10px; border-radius:8px; border:1px solid #e2e8f0;">
                        <span style="color:#64748b; font-size:10px; display:block;">Agen Forwarding:</span>
                        <strong style="color:#1e293b; font-size:12px;">{{ $shipment->agen_forwarding }}</strong>
                    </div>
                @endif

                <div style="background:#f8fafc; padding:8px 10px; border-radius:8px; border:1px solid #e2e8f0; {{ !$shipment->agen_forwarding ? 'grid-column: span 2;' : '' }}">
                    <span style="color:#64748b; font-size:10px; display:block;">Tujuan Pengiriman (Customer):</span>
                    <strong style="color:#1e293b; font-size:12px;">{{ $shipment->tujuan_pengiriman ?: '-' }}</strong>
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
        <!-- WAKTU SELESAI STAGING (AKHIR FORM UNGGAH EVIDENCE)         -->
        <!-- ═══════════════════════════════════════════════════════════ -->
        <div class="section-card">
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

                @if(!$isSubmitted)
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
                @else
                <a href="{{ route('shipments.show', $shipment->id) }}"
                   style="flex:1; background:#059e3d; color:white; font-size:14px; font-weight:700; border-radius:12px; padding:13px; text-decoration:none; display:flex; align-items:center; justify-content:center; gap:8px; box-shadow: 0 4px 14px rgba(5,158,61,0.25);">
                    <i class="ph-bold ph-lock-key" style="font-size:18px;"></i>
                    Dokumen Sudah Disubmit Final (Terkunci) &mdash; Ke Detail &rarr;
                </a>
                @endif
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
                    @if(!$isSubmitted)
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
                    @endif
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

    <!-- MODAL PERINGATAN UKURAN VIDEO MELEBIHI 6MB -->
    <div id="video-size-warning-modal"
         onclick="if (event.target === this) closeVideoSizeWarningModal()"
         style="display: none; position: fixed; inset: 0; z-index: 999999; align-items: center; justify-content: center; padding: 16px; background: rgba(15, 23, 42, 0.8); backdrop-filter: blur(8px);">
        <div style="background: white; border-radius: 20px; max-width: 460px; width: 100%; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5); overflow: hidden; border: 1.5px solid #fee2e2;"
             onclick="event.stopPropagation()">
            <!-- Header -->
            <div style="background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%); padding: 18px 20px; display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #fecaca;">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <div style="width: 44px; height: 44px; border-radius: 50%; background: #fee2e2; border: 2px solid #ef4444; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <i class="ph-fill ph-warning-octagon" style="font-size: 24px; color: #dc2626;"></i>
                    </div>
                    <div>
                        <h4 style="margin: 0; font-size: 15px; font-weight: 800; color: #991b1b;">Ukuran Video Melebihi Batas</h4>
                        <span style="font-size: 11px; font-weight: 700; color: #dc2626; background: #fff; padding: 2px 8px; border-radius: 10px; border: 1px solid #fca5a5; display: inline-block; margin-top: 3px;">
                            Batas Maksimal: 6.00 MB
                        </span>
                    </div>
                </div>
                <button type="button"
                        onclick="closeVideoSizeWarningModal()"
                        style="width: 30px; height: 30px; border-radius: 50%; background: #fee2e2; border: none; color: #991b1b; font-size: 16px; display: flex; align-items: center; justify-content: center; cursor: pointer;">
                    <i class="ph-bold ph-x"></i>
                </button>
            </div>
            <!-- Body -->
            <div style="padding: 20px; font-size: 13px; color: #374151; line-height: 1.5;">
                <div style="background: #fff1f2; border: 1px solid #ffe4e6; border-radius: 12px; padding: 12px 14px; margin-bottom: 14px;">
                    <div style="font-weight: 700; color: #be123c; font-size: 13px; display: flex; align-items: center; gap: 6px;">
                        <i class="ph-bold ph-x-circle" style="font-size: 16px;"></i> Gagal Mengunggah Video
                    </div>
                    <p id="video-warning-custom-msg" style="margin: 6px 0 0 0; font-size: 12px; color: #4c0519;">
                        File video yang Anda pilih berukuran <strong id="video-warning-file-size" style="color: #e11d48; font-weight: 800;">-</strong>. Server menolak file karena melebihi batas maksimal <strong>6 MB</strong> demi kecepatan sinkronisasi data lapangan dan keandalan server.
                    </p>
                </div>

                <p style="margin: 0 0 8px 0; font-weight: 700; color: #1f2937; font-size: 12px;">Saran agar video berhasil diunggah:</p>
                <ul style="margin: 0; padding-left: 18px; font-size: 12px; color: #4b5563; display: flex; flex-direction: column; gap: 6px;">
                    <li><strong>Durasi singkat:</strong> Cukup rekam video selama <strong>5 - 10 detik</strong> untuk memperlihatkan kondisi kontainer & muatan.</li>
                    <li><strong>Resolusi kamera:</strong> Atur perekaman video ponsel ke resolusi <strong>720p / 30fps</strong> (hindari resolusi 4K atau 60fps).</li>
                    <li><strong>Kompresi video:</strong> Kompres berkas video terlebih dahulu jika file berasal dari galeri ponsel.</li>
                </ul>
            </div>
            <!-- Footer -->
            <div style="padding: 12px 20px; background: #f8fafc; border-top: 1px solid #e2e8f0; display: flex; justify-content: flex-end;">
                <button type="button" onclick="closeVideoSizeWarningModal()"
                        style="background: #dc2626; color: white; font-size: 12.5px; font-weight: 700; border: none; padding: 9px 20px; border-radius: 10px; cursor: pointer; display: flex; align-items: center; gap: 6px; box-shadow: 0 2px 6px rgba(220,38,38,0.3);">
                    <i class="ph-bold ph-check"></i> Mengerti & Pilih Video Lain
                </button>
            </div>
        </div>
    </div>

    <script>
        const MAX_VIDEO_BYTES = 6 * 1024 * 1024; // Batas maksimal ukuran video 6MB (6,291,456 bytes)

        window.showVideoSizeWarningModal = function(sizeMB = null, customMsg = null) {
            const modal = document.getElementById('video-size-warning-modal');
            const sizeSpan = document.getElementById('video-warning-file-size');
            const msgP = document.getElementById('video-warning-custom-msg');
            if (sizeSpan && sizeMB) {
                sizeSpan.textContent = sizeMB + ' MB';
            }
            if (msgP && customMsg) {
                msgP.textContent = customMsg;
            } else if (msgP && sizeMB) {
                msgP.innerHTML = `File video yang Anda pilih berukuran <strong style="color: #e11d48; font-weight: 800;">${sizeMB} MB</strong>. Server membatasi ukuran berkas video maksimal <strong>6 MB</strong> demi kecepatan sinkronisasi data dan keandalan sistem.`;
            }
            if (modal) {
                if (modal.parentElement !== document.body) {
                    document.body.appendChild(modal);
                }
                modal.style.display = 'flex';
            }
        };

        window.closeVideoSizeWarningModal = function() {
            const modal = document.getElementById('video-size-warning-modal');
            if (modal) modal.style.display = 'none';
        };

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

                // Validasi ukuran video di sisi frontend (maksimal 6MB)
                if (isVideo && file.size > MAX_VIDEO_BYTES) {
                    const sizeMB = (file.size / (1024 * 1024)).toFixed(2);
                    const errorMsg = `Ukuran video (${sizeMB} MB) melebihi batas maksimal 6MB. Gagal mengunggah video.`;

                    // 1. Tampilkan modal dialog peringatan interaktif
                    window.showVideoSizeWarningModal(sizeMB);

                    // 2. Tampilkan toast notifikasi
                    if (window.showToast) {
                        window.showToast(errorMsg, 'error', 'Gagal Unggah: Video Melebihi 6MB', 6000);
                    }

                    // 3. Tampilkan status peringatan pada kartu SOP
                    if (statusBox) {
                        statusBox.innerHTML = `<span style="color:#ef4444; font-weight:700;"><i class="ph-bold ph-warning-circle"></i> Gagal: Video (${sizeMB}MB) > 6MB</span>`;
                    }

                    // 4. Reset input berkas agar pengguna bisa memilih kembali
                    input.value = '';
                    return;
                }

                // Simpan URL media sebelumnya untuk antisipasi fallback jika upload gagal
                if (wrapper && !wrapper.dataset.initialMediaUrl) {
                    wrapper.dataset.initialMediaUrl = wrapper.dataset.mediaUrl || '';
                }

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
                    statusBox.innerHTML = '<span style="color:#fbbf24;"><i class="ph-bold ph-spinner animate-spin"></i> ' + (isVideo ? 'Memproses stempel video...' : 'Memproses stempel...') + '</span>';
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

                        // Update preview with watermarked image/video from server (cache buster prevents stale browser cache)
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
                            wrapper.dataset.initialMediaUrl = data.file_url || blobUrl;
                            wrapper.dataset.isVideo = isVideo ? 'true' : 'false';
                            wrapper.dataset.timestamp = data.captured_at || `${formattedTimestamp} WIB`;
                        }

                        // Update in-memory data for camera sync
                        if (window.syncCameraPointUploaded) {
                            window.syncCameraPointUploaded(pointId, data.file_url || blobUrl, data.captured_at || formattedTimestamp);
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
                        window.showToast(err.message, 'error', 'Gagal Unggah', 6000);
                    }
                    // Jika error dari server terkait batas 6MB, tampilkan modal peringatan
                    if (err.message && (err.message.includes('6MB') || err.message.includes('6mb') || err.message.toLowerCase().includes('video'))) {
                        window.showVideoSizeWarningModal(null, err.message);
                    }
                    // Kembalikan tampilan preview ke kondisi awal jika ada
                    if (wrapper && wrapper.dataset.initialMediaUrl) {
                        if (isVideo && videoPreview) videoPreview.src = wrapper.dataset.initialMediaUrl;
                        if (!isVideo && preview) preview.src = wrapper.dataset.initialMediaUrl;
                    } else if (wrapper) {
                        wrapper.style.display = 'none';
                        if (iconBox) iconBox.style.display = 'flex';
                        if (actionBar) actionBar.style.display = 'none';
                    }
                    input.value = '';
                });
            }
        }
    </script>

    <!-- ═══════════════════════════════════════════════════════════════════ -->
    <!-- MODAL KAMERA FULLSCREEN SOP INTERAKTIF (POTRAIT & LANDSCAPE)        -->
    <!-- ═══════════════════════════════════════════════════════════════════ -->
    <div id="fullscreen-sop-camera-modal" class="sop-cam-modal is-portrait" style="display: none;">
        <!-- Root Container -->
        <div class="sop-cam-container">

            <!-- 1. Header (NAMA TITIK SOP + UNDERLINE DIVIDER) -->
            <div class="sop-cam-header" id="sop-cam-header">
                <div class="sop-cam-header-top">
                    <div class="sop-cam-point-meta">
                        <span class="sop-cam-badge" id="sop-cam-badge">TITIK 1</span>
                        <span class="sop-cam-badge-tag" id="sop-cam-type-tag">WAJIB</span>
                    </div>

                    <div class="sop-cam-top-actions">
                        <button type="button" class="sop-cam-ctrl-btn" onclick="switchCameraFacing()" id="sop-cam-btn-rotate" title="Balik Kamera Depan/Belakang">
                            <i class="ph-bold ph-camera-rotate"></i>
                        </button>
                        <button type="button" class="sop-cam-ctrl-btn" onclick="toggleCameraTorch()" id="sop-cam-btn-torch" title="Lampu Kilat / Flash">
                            <i class="ph-bold ph-flashlight"></i>
                        </button>
                        <button type="button" class="sop-cam-ctrl-btn sop-cam-ctrl-close" onclick="closeFullscreenCamera()" title="Tutup Kamera">
                            <i class="ph-bold ph-x"></i>
                        </button>
                    </div>
                </div>

                <!-- Judul Titik SOP (Nama Titik SOP) -->
                <div class="sop-cam-title-box">
                    <h2 class="sop-cam-title" id="sop-cam-title">1. BUKTI SURAT JALAN / SPK</h2>
                </div>

                <!-- Underline Divider Sesuai Gambar Sketsa -->
                <div class="sop-cam-divider"></div>

                <div class="sop-cam-desc-box">
                    <p class="sop-cam-desc" id="sop-cam-desc">Foto jelas dokumen surat jalan lengkap dengan stempel</p>
                    <span class="sop-cam-status-pill" id="sop-cam-status-pill">
                        <i class="ph-bold ph-clock"></i> Belum diambil
                    </span>
                </div>
            </div>

            <!-- 2. Viewfinder Kamera (Live Stream Video + Overlays) -->
            <div class="sop-cam-viewfinder" id="sop-cam-viewfinder">
                <video id="sop-cam-video" playsinline autoplay muted></video>
                <canvas id="sop-cam-canvas" style="display: none;"></canvas>

                <!-- Efek Flash Putih saat Jepret -->
                <div id="sop-cam-flash" class="sop-cam-flash"></div>

                <!-- Grid & Reticle Bantuan Framing Foto -->
                <div class="sop-cam-reticle">
                    <div class="reticle-corner tl"></div>
                    <div class="reticle-corner tr"></div>
                    <div class="reticle-corner bl"></div>
                    <div class="reticle-corner br"></div>
                </div>

                <!-- Peringatan Akses Kamera Jika Terkendala Izin/Hardware -->
                <div id="sop-cam-error-box" class="sop-cam-error-overlay" style="display: none;">
                    <div class="sop-cam-error-card">
                        <i class="ph-bold ph-camera-slash" style="font-size: 38px; color: #f87171;"></i>
                        <h4 style="margin: 8px 0 4px; font-size: 14px; font-weight: 800; color: white;">Tidak Dapat Membuka Kamera</h4>
                        <p id="sop-cam-error-msg" style="margin: 0 0 14px; font-size: 11px; color: #cbd5e1; line-height: 1.4;">
                            Pastikan izin kamera diizinkan pada browser Anda atau gunakan pilihan pilih foto/rekam dari sistem.
                        </p>
                        <div style="display: flex; gap: 8px; justify-content: center; flex-wrap: wrap;">
                            <button type="button" onclick="startCameraStream()" class="sop-cam-fallback-btn primary">
                                <i class="ph-bold ph-arrows-clockwise"></i> Coba Lagi
                            </button>
                            <label class="sop-cam-fallback-btn secondary">
                                <input type="file" id="sop-cam-error-file-input" style="display: none;" accept="image/*,video/*" onchange="handleCamFallbackUpload(this)">
                                <i class="ph-bold ph-image"></i> Ambil / Pilih File
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 3. Dock Kontrol & SOP Points Reel (Bawah di Portrait, Kanan di Landscape) -->
            <div class="sop-cam-dock" id="sop-cam-dock">

                <!-- SOP Points Scrollable Reel (1 to 27) - Bebas Pilih Titik Mana Saja -->
                <div class="sop-cam-reel-container">
                    <div class="sop-cam-reel-header-hint">
                        <span><i class="ph-bold ph-hand-tap"></i> Ketuk kotak titik manapun (tidak harus berurutan)</span>
                        <span id="sop-cam-counter-label" style="font-weight: 700; color: #38bdf8;">0/26 Wajib</span>
                    </div>

                    <div class="sop-cam-reel-scroll" id="sop-cam-reel">
                        <!-- Kotak-kotak titik SOP 1 - 27 dirender via JS renderCameraReel() -->
                    </div>
                </div>

                <!-- Shutter Action Bar -->
                <div class="sop-cam-shutter-bar">
                    <!-- Tombol Galeri / File Alternatif -->
                    <label class="sop-cam-sub-action" title="Pilih File dari Galeri">
                        <input type="file" id="sop-cam-direct-file" style="display: none;" accept="image/*" onchange="handleDirectCamFileInput(this)">
                        <div class="sop-cam-sub-icon">
                            <i class="ph-bold ph-image"></i>
                        </div>
                        <span class="sop-cam-sub-label">Galeri</span>
                    </label>

                    <!-- Large Shutter Capture Button -->
                    <div class="sop-cam-shutter-btn-wrap">
                        <button type="button" class="sop-cam-shutter-btn" id="sop-cam-shutter-btn" onclick="handleShutterClick()" title="Ambil Foto SOP">
                            <div class="sop-cam-shutter-inner" id="sop-cam-shutter-inner">
                                <i class="ph-fill ph-video-camera" id="sop-cam-shutter-vid-icon" style="display: none; font-size: 24px; color: white;"></i>
                            </div>
                        </button>
                        <!-- Shutter helper badge under button in portrait -->
                        <span class="sop-cam-shutter-hint" id="sop-cam-shutter-hint">Ambil Foto</span>
                    </div>

                    <!-- Input tersembunyi untuk video recording camcorder -->
                    <input type="file" id="sop-cam-video-recorder" style="display: none;" accept="video/*" capture="environment" onchange="handleDirectCamVideoInput(this)">

                    <!-- Tombol Tutup / Selesai -->
                    <button type="button" class="sop-cam-sub-action" onclick="closeFullscreenCamera()" title="Selesai & Keluar">
                        <div class="sop-cam-sub-icon" style="background: rgba(255,255,255,0.15);">
                            <i class="ph-bold ph-check"></i>
                        </div>
                        <span class="sop-cam-sub-label">Selesai</span>
                    </button>
                </div>

            </div>

        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════════════ -->
    <!-- STYLING CSS RESPONSIVE MODAL KAMERA (POTRAIT & LANDSCAPE)           -->
    <!-- ═══════════════════════════════════════════════════════════════════ -->
    <style>
        /* Base Modal Fullscreen Overlay */
        .sop-cam-modal {
            position: fixed;
            inset: 0;
            z-index: 9999999;
            background: #000000;
            width: 100vw;
            height: 100vh;
            height: 100dvh;
            overflow: hidden;
            user-select: none;
            -webkit-user-select: none;
            touch-action: manipulation;
            font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        }

        .sop-cam-container {
            position: relative;
            width: 100%;
            height: 100%;
            display: flex;
            overflow: hidden;
            background: #000;
        }

        /* ------------------------------------------------------------- */
        /* MODE POTRAIT (Sesuai Gambar Sketsa Kiri)                      */
        /* ------------------------------------------------------------- */
        .sop-cam-modal.is-portrait .sop-cam-container {
            flex-direction: column;
        }

        /* Header Potrait: NAMA TITIK SOP di Atas + Underline */
        .sop-cam-modal.is-portrait .sop-cam-header {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            z-index: 40;
            padding: max(12px, env(safe-area-inset-top)) 16px 10px;
            background: linear-gradient(180deg, rgba(15, 23, 42, 0.95) 0%, rgba(15, 23, 42, 0.75) 75%, transparent 100%);
            box-sizing: border-box;
            backdrop-filter: blur(4px);
        }

        /* Viewfinder Potrait: Area Kamera di Tengah */
        .sop-cam-modal.is-portrait .sop-cam-viewfinder {
            flex: 1;
            width: 100%;
            height: 100%;
            position: relative;
            background: #000;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        /* Bottom Dock Potrait: Kotak 1 2 3 4 5 6 + Tombol Shutter di Bawah */
        .sop-cam-modal.is-portrait .sop-cam-dock {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            z-index: 40;
            display: flex;
            flex-direction: column;
            background: linear-gradient(0deg, rgba(15, 23, 42, 0.98) 0%, rgba(15, 23, 42, 0.85) 80%, transparent 100%);
            padding-bottom: max(14px, env(safe-area-inset-bottom));
            backdrop-filter: blur(6px);
            border-top: 1px solid rgba(255, 255, 255, 0.08);
        }

        .sop-cam-modal.is-portrait .sop-cam-reel-scroll {
            display: flex;
            flex-direction: row;
            overflow-x: auto;
            scroll-behavior: smooth;
            -webkit-overflow-scrolling: touch;
            gap: 10px;
            padding: 4px 14px 10px;
            scrollbar-width: none;
        }
        .sop-cam-modal.is-portrait .sop-cam-reel-scroll::-webkit-scrollbar {
            display: none;
        }

        .sop-cam-modal.is-portrait .sop-cam-shutter-bar {
            display: flex;
            align-items: center;
            justify-content: space-around;
            padding: 4px 20px 8px;
            width: 100%;
            box-sizing: border-box;
        }

        /* ------------------------------------------------------------- */
        /* MODE LANDSCAPE (Sesuai Gambar Sketsa Kanan)                   */
        /* ------------------------------------------------------------- */
        .sop-cam-modal.is-landscape .sop-cam-container {
            flex-direction: row;
        }

        /* Viewfinder Landscape: Area Kamera di Sebelah Kiri / Tengah */
        .sop-cam-modal.is-landscape .sop-cam-viewfinder {
            flex: 1;
            height: 100%;
            position: relative;
            background: #000;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        /* Header Landscape: NAMA TITIK SOP di Kiri Atas Overlaid */
        .sop-cam-modal.is-landscape .sop-cam-header {
            position: absolute;
            top: max(10px, env(safe-area-inset-top));
            left: max(14px, env(safe-area-inset-left));
            max-width: 54%;
            z-index: 40;
            background: rgba(15, 23, 42, 0.88);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.16);
            border-radius: 14px;
            padding: 10px 14px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.6);
            box-sizing: border-box;
        }

        /* Right Dock Landscape: Kolom Kotak 1 2 3 4 5 6 Vertikal + Shutter */
        .sop-cam-modal.is-landscape .sop-cam-dock {
            width: 250px;
            max-width: 38vw;
            height: 100%;
            z-index: 40;
            background: rgba(15, 23, 42, 0.96);
            backdrop-filter: blur(12px);
            border-left: 1.5px solid rgba(255, 255, 255, 0.14);
            display: flex;
            flex-direction: row;
            align-items: center;
            padding: max(10px, env(safe-area-inset-top)) 8px max(10px, env(safe-area-inset-bottom)) 8px;
            box-sizing: border-box;
        }

        .sop-cam-modal.is-landscape .sop-cam-reel-container {
            flex: 1;
            height: 100%;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .sop-cam-modal.is-landscape .sop-cam-reel-scroll {
            flex: 1;
            height: 100%;
            display: flex;
            flex-direction: column;
            overflow-y: auto;
            scroll-behavior: smooth;
            -webkit-overflow-scrolling: touch;
            gap: 10px;
            padding: 6px 6px 12px;
            scrollbar-width: thin;
            scrollbar-color: rgba(255,255,255,0.2) transparent;
        }

        .sop-cam-modal.is-landscape .sop-cam-shutter-bar {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 18px;
            padding: 0 4px 0 10px;
            border-left: 1px solid rgba(255, 255, 255, 0.12);
            height: 100%;
        }

        /* ------------------------------------------------------------- */
        /* ELEMEN HEADER & DIVIDER (Sesuai Sketsa: Nama Titik + Garis)   */
        /* ------------------------------------------------------------- */
        .sop-cam-header-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
        }

        .sop-cam-point-meta {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .sop-cam-badge {
            font-size: 11px;
            font-weight: 800;
            background: #0284c7;
            color: #ffffff;
            padding: 2px 8px;
            border-radius: 6px;
            letter-spacing: 0.04em;
        }

        .sop-cam-badge-tag {
            font-size: 9px;
            font-weight: 800;
            background: #ef4444;
            color: #ffffff;
            padding: 2px 6px;
            border-radius: 6px;
            text-transform: uppercase;
        }

        .sop-cam-top-actions {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .sop-cam-ctrl-btn {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: #ffffff;
            font-size: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.15s;
        }
        .sop-cam-ctrl-btn:active {
            background: rgba(255, 255, 255, 0.3);
            transform: scale(0.94);
        }
        .sop-cam-ctrl-btn.active {
            background: #f59e0b;
            color: #111827;
            border-color: #fbbf24;
        }
        .sop-cam-ctrl-close {
            background: rgba(239, 68, 68, 0.3);
            border-color: rgba(239, 68, 68, 0.5);
            color: #fca5a5;
        }

        .sop-cam-title-box {
            margin-top: 6px;
        }

        .sop-cam-title {
            margin: 0;
            font-size: 15px;
            font-weight: 800;
            color: #ffffff;
            letter-spacing: 0.02em;
            text-transform: uppercase;
            line-height: 1.25;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            text-shadow: 0 2px 4px rgba(0,0,0,0.7);
        }

        /* UNDERLINE DIVIDER SESUAI GAMBAR USER */
        .sop-cam-divider {
            width: 100%;
            height: 2.5px;
            background: linear-gradient(90deg, #38bdf8 0%, #0284c7 60%, rgba(56, 189, 248, 0.2) 100%);
            border-radius: 2px;
            margin: 7px 0 6px 0;
            box-shadow: 0 0 8px rgba(56, 189, 248, 0.6);
        }

        .sop-cam-desc-box {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
        }

        .sop-cam-desc {
            margin: 0;
            font-size: 11px;
            color: #cbd5e1;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            flex: 1;
        }

        .sop-cam-status-pill {
            font-size: 10px;
            font-weight: 700;
            color: #94a3b8;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.15);
            padding: 2px 8px;
            border-radius: 12px;
            white-space: nowrap;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        /* ------------------------------------------------------------- */
        /* VIEWFINDER & RETICLE                                          */
        /* ------------------------------------------------------------- */
        #sop-cam-video {
            width: 100%;
            height: 100%;
            object-fit: cover;
            background: #000;
        }

        .sop-cam-flash {
            position: absolute;
            inset: 0;
            background: white;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.25s ease-out;
            z-index: 35;
        }
        .sop-cam-flash.flash-active {
            opacity: 0.95;
            transition: none;
        }

        .sop-cam-reticle {
            position: absolute;
            top: 22%;
            bottom: 22%;
            left: 10%;
            right: 10%;
            pointer-events: none;
            z-index: 25;
            opacity: 0.5;
        }
        .reticle-corner {
            position: absolute;
            width: 18px;
            height: 18px;
            border-color: rgba(255, 255, 255, 0.7);
            border-style: solid;
        }
        .reticle-corner.tl { top: 0; left: 0; border-width: 2px 0 0 2px; }
        .reticle-corner.tr { top: 0; right: 0; border-width: 2px 2px 0 0; }
        .reticle-corner.bl { bottom: 0; left: 0; border-width: 0 0 2px 2px; }
        .reticle-corner.br { bottom: 0; right: 0; border-width: 0 2px 2px 0; }

        /* Error Overlay */
        .sop-cam-error-overlay {
            position: absolute;
            inset: 0;
            background: rgba(15, 23, 42, 0.92);
            z-index: 50;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .sop-cam-error-card {
            background: #1e293b;
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 16px;
            padding: 20px;
            text-align: center;
            max-width: 320px;
        }
        .sop-cam-fallback-btn {
            font-size: 11.5px;
            font-weight: 700;
            padding: 8px 14px;
            border-radius: 8px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            border: none;
        }
        .sop-cam-fallback-btn.primary {
            background: #0284c7;
            color: white;
        }
        .sop-cam-fallback-btn.secondary {
            background: rgba(255,255,255,0.12);
            color: #cbd5e1;
            border: 1px solid rgba(255,255,255,0.2);
        }

        /* ------------------------------------------------------------- */
        /* KOTAK-KOTAK TITIK SOP (1, 2, 3, 4, 5, 6...) DI REEL           */
        /* ------------------------------------------------------------- */
        .sop-cam-reel-header-hint {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 10px;
            color: #94a3b8;
            padding: 4px 14px 2px;
        }

        .sop-reel-card {
            position: relative;
            background: #1e293b;
            border: 2px solid rgba(255, 255, 255, 0.16);
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.18s cubic-bezier(0.4, 0, 0.2, 1);
            overflow: hidden;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Ukuran Box di Portrait */
        .sop-cam-modal.is-portrait .sop-reel-card {
            width: 58px;
            height: 58px;
        }

        /* Ukuran Box di Landscape */
        .sop-cam-modal.is-landscape .sop-reel-card {
            width: 100%;
            height: 52px;
            display: flex;
            align-items: center;
            justify-content: flex-start;
            padding: 0 8px;
            box-sizing: border-box;
            gap: 8px;
        }

        /* Hover & Active Box */
        .sop-reel-card:hover {
            border-color: rgba(56, 189, 248, 0.5);
            background: #243247;
        }
        .sop-reel-card.is-active {
            border-color: #38bdf8 !important;
            background: #0b2e4f !important;
            box-shadow: 0 0 14px rgba(56, 189, 248, 0.8), inset 0 0 8px rgba(56, 189, 248, 0.3) !important;
            transform: scale(1.05);
            z-index: 5;
        }

        /* Label Nomor */
        .sop-reel-num {
            font-size: 14px;
            font-weight: 800;
            color: #e2e8f0;
            font-family: monospace;
            z-index: 2;
        }
        .sop-reel-card.is-active .sop-reel-num {
            color: #38bdf8;
            font-weight: 900;
        }

        /* Thumbnail Image */
        .sop-reel-thumb {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 10px;
            opacity: 0.75;
            z-index: 1;
        }
        .sop-cam-modal.is-landscape .sop-reel-thumb {
            position: relative;
            width: 44px;
            height: 44px;
            flex-shrink: 0;
            opacity: 0.9;
        }

        /* Status Checkmark */
        .sop-reel-check-badge {
            position: absolute;
            top: 3px;
            right: 3px;
            width: 16px;
            height: 16px;
            border-radius: 50%;
            background: #059e3d;
            color: white;
            font-size: 10px;
            font-weight: 900;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 3;
            box-shadow: 0 1px 3px rgba(0,0,0,0.5);
        }

        /* Dot Wajib */
        .sop-reel-wajib-dot {
            position: absolute;
            top: 4px;
            left: 4px;
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: #ef4444;
            box-shadow: 0 0 4px #ef4444;
            z-index: 3;
        }

        /* Spinner Saving */
        .sop-reel-loading {
            position: absolute;
            inset: 0;
            background: rgba(15, 23, 42, 0.85);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #38bdf8;
            font-size: 18px;
            z-index: 4;
        }

        /* ------------------------------------------------------------- */
        /* SHUTTER BUTTON BESAR                                          */
        /* ------------------------------------------------------------- */
        .sop-cam-shutter-btn-wrap {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 4px;
        }

        .sop-cam-shutter-btn {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            background: transparent;
            border: 4px solid #ffffff;
            padding: 4px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: transform 0.12s, border-color 0.15s;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.6);
            outline: none;
            -webkit-tap-highlight-color: transparent;
        }
        .sop-cam-modal.is-landscape .sop-cam-shutter-btn {
            width: 64px;
            height: 64px;
        }
        .sop-cam-shutter-btn:active {
            transform: scale(0.92);
        }

        .sop-cam-shutter-inner {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            background: #ffffff;
            transition: background 0.2s, border-radius 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Video Shutter Mode */
        .sop-cam-shutter-btn.video-mode {
            border-color: #ef4444;
        }
        .sop-cam-shutter-btn.video-mode .sop-cam-shutter-inner {
            background: #ef4444;
            box-shadow: 0 0 14px rgba(239, 68, 68, 0.8);
        }

        .sop-cam-shutter-hint {
            font-size: 10px;
            font-weight: 700;
            color: #cbd5e1;
            letter-spacing: 0.02em;
        }

        /* Sub Action Buttons (Galeri & Selesai) */
        .sop-cam-sub-action {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 4px;
            cursor: pointer;
            background: transparent;
            border: none;
            color: #cbd5e1;
            padding: 6px;
        }
        .sop-cam-sub-icon {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.18);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            color: #ffffff;
            transition: all 0.15s;
        }
        .sop-cam-sub-action:active .sop-cam-sub-icon {
            transform: scale(0.92);
            background: rgba(255, 255, 255, 0.25);
        }
        .sop-cam-sub-label {
            font-size: 10px;
            font-weight: 600;
            color: #94a3b8;
        }
    </style>

    <!-- ═══════════════════════════════════════════════════════════════════ -->
    <!-- LOGIKA JAVASCRIPT KAMERA SOP (WebRTC, Canvas, Upload, Responsive)  -->
    <!-- ═══════════════════════════════════════════════════════════════════ -->
    <script>
        // State Global Kamera Fullscreen SOP
        let camStream = null;
        let camFacingMode = 'environment'; // Default kamera belakang
        let camTorchActive = false;
        let currentSopPointId = null;
        let isUploadingSop = false;

        // =========================================================================
        // NAMA FUNCTION : openFullscreenCamera
        // FUNGSI        : Membuka modal fullscreen kamera, mendeteksi orientasi layar
        //                 (potrait / landscape) dan auto-select titik SOP pertama
        // =========================================================================
        window.openFullscreenCamera = function(pointId = null) {
            const modal = document.getElementById('fullscreen-sop-camera-modal');
            if (!modal) return;

            if (modal.parentElement !== document.body) {
                document.body.appendChild(modal);
            }

            modal.style.display = 'block';
            document.body.style.overflow = 'hidden';

            // 1. Sinkronisasi orientasi layar
            updateCameraOrientation();
            window.addEventListener('resize', updateCameraOrientation);
            if (screen.orientation) {
                screen.orientation.addEventListener('change', updateCameraOrientation);
            }

            // 2. Render kotak-kotak reel titik SOP
            renderCameraReel();

            // 3. Tentukan titik yang aktif (jika ada param pointId pakai itu, jika tidak cari uncaptured pertama)
            let targetPointId = pointId;
            if (!targetPointId) {
                const firstUncaptured = window._sopPointsData.find(p => !p.has_evidence);
                targetPointId = firstUncaptured ? firstUncaptured.id : window._sopPointsData[0].id;
            }
            selectCameraPoint(targetPointId);

            // 4. Nyalakan stream kamera
            startCameraStream();
        };

        // =========================================================================
        // NAMA FUNCTION : updateCameraOrientation
        // FUNGSI        : Mendeteksi apakah perangkat dalam mode Potrait atau Landscape
        //                 dan menerapkan class CSS .is-portrait atau .is-landscape
        // =========================================================================
        function updateCameraOrientation() {
            const modal = document.getElementById('fullscreen-sop-camera-modal');
            if (!modal) return;

            const isLandscape = window.innerWidth > window.innerHeight;
            if (isLandscape) {
                modal.classList.remove('is-portrait');
                modal.classList.add('is-landscape');
            } else {
                modal.classList.remove('is-landscape');
                modal.classList.add('is-portrait');
            }
        }

        // =========================================================================
        // NAMA FUNCTION : startCameraStream
        // FUNGSI        : Mengaktifkan WebRTC getUserMedia dengan preferensi kamera
        //                 belakang & resolusi high-definition (1080p ideal)
        // =========================================================================
        async function startCameraStream() {
            const errorBox = document.getElementById('sop-cam-error-box');
            const video = document.getElementById('sop-cam-video');
            if (errorBox) errorBox.style.display = 'none';

            // Hentikan stream sebelumnya jika ada
            stopCameraStream();

            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                if (errorBox) {
                    document.getElementById('sop-cam-error-msg').textContent = 'Browser ini tidak mendukung akses kamera langsung (getUserMedia). Silakan gunakan tombol Ambil / Pilih File.';
                    errorBox.style.display = 'flex';
                }
                return;
            }

            const constraints = {
                audio: false,
                video: {
                    facingMode: { ideal: camFacingMode },
                    width: { ideal: 1920 },
                    height: { ideal: 1080 }
                }
            };

            try {
                camStream = await navigator.mediaDevices.getUserMedia(constraints);
                video.srcObject = camStream;
                await video.play();

                // Cek ketersediaan fitur senter / flashlight
                checkTorchSupport();
            } catch (err) {
                console.warn('Gagal membuka kamera dengan constraints ideal, mencoba fallback:', err);
                try {
                    // Fallback constraints sederhana
                    camStream = await navigator.mediaDevices.getUserMedia({ video: true, audio: false });
                    video.srcObject = camStream;
                    await video.play();
                    checkTorchSupport();
                } catch (fallbackErr) {
                    console.error('Kamera tidak dapat diakses:', fallbackErr);
                    if (errorBox) {
                        document.getElementById('sop-cam-error-msg').textContent = 'Kamera tidak dapat diakses (' + (fallbackErr.message || 'Izin ditolak') + '). Pastikan izin kamera aktif pada browser.';
                        errorBox.style.display = 'flex';
                    }
                }
            }
        }

        // =========================================================================
        // NAMA FUNCTION : stopCameraStream
        // FUNGSI        : Menghentikan seluruh tracks stream WebRTC kamera
        // =========================================================================
        function stopCameraStream() {
            if (camStream) {
                camStream.getTracks().forEach(track => track.stop());
                camStream = null;
            }
            const video = document.getElementById('sop-cam-video');
            if (video) {
                video.srcObject = null;
            }
        }

        // =========================================================================
        // NAMA FUNCTION : switchCameraFacing
        // FUNGSI        : Menukar kamera depan (selfie) dan belakang (environment)
        // =========================================================================
        window.switchCameraFacing = function() {
            camFacingMode = (camFacingMode === 'environment') ? 'user' : 'environment';
            startCameraStream();
        };

        // =========================================================================
        // NAMA FUNCTION : checkTorchSupport & toggleCameraTorch
        // FUNGSI        : Memeriksa dan menyalakan/mematikan lampu kilat / flashlight
        // =========================================================================
        function checkTorchSupport() {
            const torchBtn = document.getElementById('sop-cam-btn-torch');
            if (!torchBtn) return;

            const track = camStream?.getVideoTracks()?.[0];
            const caps = track?.getCapabilities ? track.getCapabilities() : null;
            if (caps && caps.torch) {
                torchBtn.style.display = 'flex';
            } else {
                torchBtn.style.display = 'none';
            }
        }

        window.toggleCameraTorch = async function() {
            const track = camStream?.getVideoTracks()?.[0];
            const torchBtn = document.getElementById('sop-cam-btn-torch');
            if (!track || !track.applyConstraints) return;

            try {
                camTorchActive = !camTorchActive;
                await track.applyConstraints({
                    advanced: [{ torch: camTorchActive }]
                });
                if (torchBtn) {
                    if (camTorchActive) {
                        torchBtn.classList.add('active');
                    } else {
                        torchBtn.classList.remove('active');
                    }
                }
            } catch (e) {
                console.warn('Gagal mengatur torch:', e);
            }
        };

        // =========================================================================
        // NAMA FUNCTION : renderCameraReel
        // FUNGSI        : Me-render kotak-kotak titik SOP (1 - 27) di dalam reel dock
        //                 memperlihatkan thumbnail jika ada foto, centang hijau, dan dot
        // =========================================================================
        function renderCameraReel() {
            const reel = document.getElementById('sop-cam-reel');
            if (!reel || !window._sopPointsData) return;

            let html = '';
            let uploadedCount = 0;
            const totalCount = window._sopPointsData.length;
            const mandatoryTotal = window._sopPointsData.filter(p => p.wajib).length;
            let mandatoryUploaded = 0;

            window._sopPointsData.forEach(point => {
                const hasEvidence = !!point.has_evidence;
                if (hasEvidence) {
                    uploadedCount++;
                    if (point.wajib) mandatoryUploaded++;
                }

                html += `
                    <div class="sop-reel-card ${hasEvidence ? 'has-evidence' : ''}"
                         id="sop-reel-card-${point.id}"
                         onclick="selectCameraPoint(${point.id})"
                         title="${point.urutan}. ${point.nama_titik}">
                        
                        <!-- Thumbnail Foto jika sudah tersimpan -->
                        ${point.evidence_url && !point.is_video ? `
                            <img src="${point.evidence_url}" class="sop-reel-thumb" id="sop-reel-thumb-${point.id}" alt="${point.urutan}">
                        ` : ''}

                        <!-- Label Nomor Urutan Titik -->
                        <span class="sop-reel-num" id="sop-reel-num-${point.id}">${point.urutan}</span>

                        <!-- Centang Hijau jika terbukti -->
                        <div class="sop-reel-check-badge" id="sop-reel-check-${point.id}" style="${hasEvidence ? '' : 'display: none;'}">
                            ✓
                        </div>

                        <!-- Dot Merah jika Titik Wajib -->
                        ${point.wajib && !hasEvidence ? `<div class="sop-reel-wajib-dot" id="sop-reel-wajib-${point.id}"></div>` : ''}

                        <!-- Spinner Loading Saat Menyimpan -->
                        <div class="sop-reel-loading" id="sop-reel-loading-${point.id}" style="display: none;">
                            <i class="ph-bold ph-spinner animate-spin"></i>
                        </div>
                    </div>
                `;
            });

            reel.innerHTML = html;

            const counterLabel = document.getElementById('sop-cam-counter-label');
            if (counterLabel) {
                counterLabel.textContent = `${mandatoryUploaded}/${mandatoryTotal} Wajib`;
            }
        }

        // =========================================================================
        // NAMA FUNCTION : selectCameraPoint
        // FUNGSI        : Memilih titik SOP aktif (operator bebas memilih titik apa saja
        //                 tanpa harus berurutan). Mengupdate judul, divider underline,
        //                 dan tombol shutter (foto atau rekaman video camcorder).
        // =========================================================================
        window.selectCameraPoint = function(pointId) {
            currentSopPointId = Number(pointId);
            const point = window._sopPointsData.find(p => p.id === currentSopPointId);
            if (!point) return;

            // 1. Update class .is-active pada reel card
            document.querySelectorAll('.sop-reel-card').forEach(card => card.classList.remove('is-active'));
            const activeCard = document.getElementById('sop-reel-card-' + point.id);
            if (activeCard) {
                activeCard.classList.add('is-active');
                activeCard.scrollIntoView({ behavior: 'smooth', block: 'center', inline: 'center' });
            }

            // 2. Update Header Atas
            const badgeEl = document.getElementById('sop-cam-badge');
            const typeTagEl = document.getElementById('sop-cam-type-tag');
            const titleEl = document.getElementById('sop-cam-title');
            const descEl = document.getElementById('sop-cam-desc');
            const statusPill = document.getElementById('sop-cam-status-pill');

            if (badgeEl) badgeEl.textContent = `TITIK ${point.urutan}`;
            if (typeTagEl) {
                if (point.is_video) {
                    typeTagEl.textContent = 'VIDEO 6MB';
                    typeTagEl.style.background = '#dc2626';
                } else if (point.wajib) {
                    typeTagEl.textContent = 'WAJIB';
                    typeTagEl.style.background = '#ef4444';
                } else {
                    typeTagEl.textContent = 'OPSIONAL';
                    typeTagEl.style.background = '#64748b';
                }
            }
            if (titleEl) titleEl.textContent = `${point.urutan}. ${point.nama_titik}`;
            if (descEl) descEl.textContent = point.deskripsi || '-';
            if (statusPill) {
                if (point.has_evidence) {
                    statusPill.innerHTML = '<i class="ph-bold ph-check-circle" style="color:#22c55e;"></i> ' + (point.captured_at || 'Tersimpan');
                    statusPill.style.color = '#86efac';
                    statusPill.style.borderColor = 'rgba(134,239,172,0.4)';
                } else {
                    statusPill.innerHTML = '<i class="ph-bold ph-clock"></i> Belum diambil';
                    statusPill.style.color = '#94a3b8';
                    statusPill.style.borderColor = 'rgba(255,255,255,0.15)';
                }
            }

            // 3. Update Mode Shutter (Foto vs Video Camcorder untuk Titik 27)
            const shutterBtn = document.getElementById('sop-cam-shutter-btn');
            const shutterInner = document.getElementById('sop-cam-shutter-inner');
            const shutterVidIcon = document.getElementById('sop-cam-shutter-vid-icon');
            const shutterHint = document.getElementById('sop-cam-shutter-hint');

            if (point.is_video) {
                if (shutterBtn) shutterBtn.classList.add('video-mode');
                if (shutterVidIcon) shutterVidIcon.style.display = 'block';
                if (shutterHint) shutterHint.textContent = 'Rekam Video (6MB)';
            } else {
                if (shutterBtn) shutterBtn.classList.remove('video-mode');
                if (shutterVidIcon) shutterVidIcon.style.display = 'none';
                if (shutterHint) shutterHint.textContent = 'Ambil Foto';
            }
        };

        // =========================================================================
        // NAMA FUNCTION : handleShutterClick
        // FUNGSI        : Memproses klik tombol shutter (Jepret Foto via Canvas atau
        //                 buka perekam video jika titik video)
        // =========================================================================
        window.handleShutterClick = function() {
            if (isUploadingSop) return;

            const point = window._sopPointsData.find(p => p.id === currentSopPointId);
            if (!point) return;

            // Titik Video: Buka perekam video sistem / camcorder
            if (point.is_video) {
                const vidInput = document.getElementById('sop-cam-video-recorder');
                if (vidInput) {
                    vidInput.click();
                }
                return;
            }

            // Titik Foto: Jepret dari Video Stream
            takeFullscreenPhoto();
        };

        // =========================================================================
        // NAMA FUNCTION : takeFullscreenPhoto
        // FUNGSI        : Menangkap frame live camera ke HTML Canvas, memicu animasi
        //                 flash, mengubah ke JPEG Blob dan mengirim upload AJAX
        // =========================================================================
        function takeFullscreenPhoto() {
            const video = document.getElementById('sop-cam-video');
            const canvas = document.getElementById('sop-cam-canvas');
            const flash = document.getElementById('sop-cam-flash');

            if (!video || !canvas) return;

            // Efek Flash Putih Kamera
            if (flash) {
                flash.classList.add('flash-active');
                setTimeout(() => flash.classList.remove('flash-active'), 120);
            }

            // Haptic feedback getaran jika smartphone mendukung
            if (navigator.vibrate) {
                navigator.vibrate(40);
            }

            // Buat canvas sesuai resolusi video asli
            canvas.width = video.videoWidth || 1280;
            canvas.height = video.videoHeight || 720;
            const ctx = canvas.getContext('2d');
            ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

            // Ekspor ke Blob JPEG
            canvas.toBlob(function(blob) {
                if (!blob) {
                    alert('Gagal mengambil gambar dari kamera.');
                    return;
                }
                uploadBlobToSopPoint(currentSopPointId, blob, 'camera_capture.jpg');
            }, 'image/jpeg', 0.92);
        }

        // =========================================================================
        // NAMA FUNCTION : uploadBlobToSopPoint
        // FUNGSI        : Mengirimkan berkas Blob foto / video ke server via AJAX
        //                 dan memperbarui status kartu secara realtime
        // =========================================================================
        async function uploadBlobToSopPoint(pointId, fileOrBlob, filename = 'sop_photo.jpg') {
            isUploadingSop = true;

            // Indikator loading pada kotak reel
            const loadingEl = document.getElementById('sop-reel-loading-' + pointId);
            if (loadingEl) loadingEl.style.display = 'flex';

            const statusPill = document.getElementById('sop-cam-status-pill');
            if (statusPill) {
                statusPill.innerHTML = '<i class="ph-bold ph-spinner animate-spin" style="color:#38bdf8;"></i> Mengunggah & memberi stempel...';
            }

            // Format timestamp lokal
            const now = new Date();
            const y = now.getFullYear();
            const m = String(now.getMonth() + 1).padStart(2, '0');
            const d = String(now.getDate()).padStart(2, '0');
            const hh = String(now.getHours()).padStart(2, '0');
            const mm = String(now.getMinutes()).padStart(2, '0');
            const ss = String(now.getSeconds()).padStart(2, '0');
            const formattedTs = `${y}-${m}-${d} ${hh}:${mm}:${ss}`;

            const fd = new FormData();
            fd.append('file', fileOrBlob, filename);
            fd.append('sop_photo_point_id', pointId);
            fd.append('captured_at', formattedTs);
            fd.append('_token', window._csrfToken);

            try {
                const response = await fetch(window._uploadPhotoRoute, {
                    method: 'POST',
                    body: fd
                });

                const data = await response.json();

                if (!response.ok || !data.success) {
                    throw new Error(data.message || 'Gagal menyimpan foto.');
                }

                // Sukses tersimpan di server
                syncCameraPointUploaded(pointId, data.file_url, data.captured_at || `${formattedTs} WIB`);

                if (window.showToast) {
                    window.showToast('Foto Titik berhasil disimpan & distempel.', 'success', 'Tersimpan');
                }

                // Auto advance: Sarankan titik berikutnya yang belum terisi (namun operator bebas scroll kapan saja)
                const nextUncaptured = window._sopPointsData.find(p => !p.has_evidence);
                if (nextUncaptured) {
                    setTimeout(() => {
                        selectCameraPoint(nextUncaptured.id);
                    }, 400);
                }

            } catch (err) {
                console.error('Error saat upload SOP Point:', err);
                alert('Gagal mengunggah foto: ' + err.message);
                if (err.message && (err.message.includes('6MB') || err.message.toLowerCase().includes('video'))) {
                    window.showVideoSizeWarningModal(null, err.message);
                }
            } finally {
                if (loadingEl) loadingEl.style.display = 'none';
                isUploadingSop = false;
            }
        }

        // =========================================================================
        // NAMA FUNCTION : syncCameraPointUploaded
        // FUNGSI        : Sinkronisasi data in-memory, reel modal kamera, dan kartu
        //                 timeline di latar belakang tanpa reload halaman
        // =========================================================================
        window.syncCameraPointUploaded = function(pointId, fileUrl, capturedAt) {
            const point = window._sopPointsData.find(p => p.id === Number(pointId));
            if (point) {
                point.has_evidence = true;
                point.evidence_url = fileUrl;
                point.captured_at = capturedAt;
            }

            // Update Reel UI
            const checkBadge = document.getElementById('sop-reel-check-' + pointId);
            if (checkBadge) checkBadge.style.display = 'flex';

            const wajibDot = document.getElementById('sop-reel-wajib-' + pointId);
            if (wajibDot) wajibDot.style.display = 'none';

            const reelCard = document.getElementById('sop-reel-card-' + pointId);
            if (reelCard) {
                reelCard.classList.add('has-evidence');
                let thumbImg = document.getElementById('sop-reel-thumb-' + pointId);
                if (!thumbImg && fileUrl && (!point || !point.is_video)) {
                    thumbImg = document.createElement('img');
                    thumbImg.id = 'sop-reel-thumb-' + pointId;
                    thumbImg.className = 'sop-reel-thumb';
                    thumbImg.src = fileUrl + '?t=' + Date.now();
                    reelCard.prepend(thumbImg);
                } else if (thumbImg && fileUrl) {
                    thumbImg.src = fileUrl + '?t=' + Date.now();
                }
            }

            // Update Header Status jika titik ini yang sedang aktif
            if (currentSopPointId === Number(pointId)) {
                const statusPill = document.getElementById('sop-cam-status-pill');
                if (statusPill) {
                    statusPill.innerHTML = '<i class="ph-bold ph-check-circle" style="color:#22c55e;"></i> ' + (capturedAt || 'Tersimpan');
                    statusPill.style.color = '#86efac';
                    statusPill.style.borderColor = 'rgba(134,239,172,0.4)';
                }
            }

            // Update Counter Wajib di Header Reel
            const mandatoryTotal = window._sopPointsData.filter(p => p.wajib).length;
            const mandatoryUploaded = window._sopPointsData.filter(p => p.wajib && p.has_evidence).length;
            const counterLabel = document.getElementById('sop-cam-counter-label');
            if (counterLabel) {
                counterLabel.textContent = `${mandatoryUploaded}/${mandatoryTotal} Wajib`;
            }

            // Sinkronkan juga kartu di halaman timeline luar
            const numBadge = document.getElementById('num-badge-' + pointId);
            if (numBadge) {
                numBadge.textContent = '✓';
                numBadge.style.color = '#059e3d';
            }

            const previewWrapper = document.getElementById('preview-wrapper-' + pointId);
            const previewImg = document.getElementById('preview-' + pointId);
            const previewVid = document.getElementById('video-preview-' + pointId);
            const iconBox = document.getElementById('icon-' + pointId);
            const actionBar = document.getElementById('action-bar-' + pointId);
            const statusBox = document.getElementById('status-' + pointId);
            const tsBox = document.getElementById('timestamp-' + pointId);

            if (previewWrapper) {
                previewWrapper.style.display = 'block';
                previewWrapper.dataset.mediaUrl = fileUrl;
                previewWrapper.dataset.timestamp = capturedAt;
            }
            if (iconBox) iconBox.style.display = 'none';
            if (actionBar) actionBar.style.display = 'flex';
            if (statusBox) statusBox.innerHTML = '<span style="color:#63c384;">✓ Tersimpan</span>';
            if (tsBox) {
                const tsText = tsBox.querySelector('.ts-text');
                if (tsText) tsText.textContent = capturedAt;
            }

            if (point && point.is_video) {
                if (previewVid) {
                    previewVid.src = fileUrl;
                    previewVid.style.display = 'block';
                }
                if (previewImg) previewImg.style.display = 'none';
            } else {
                if (previewImg) {
                    previewImg.src = fileUrl + '?t=' + Date.now();
                    previewImg.style.display = 'block';
                }
                if (previewVid) previewVid.style.display = 'none';
            }

            // Trigger event Alpine.js
            window.dispatchEvent(new CustomEvent('photo-uploaded', { detail: { pointId: pointId } }));
        };

        // =========================================================================
        // NAMA FUNCTION : handleDirectCamFileInput & handleDirectCamVideoInput
        // FUNGSI        : Menangani berkas yang diunggah dari galeri atau rekaman
        //                 camcorder dari dalam modal kamera fullscreen
        // =========================================================================
        window.handleDirectCamFileInput = function(input) {
            if (input.files && input.files[0]) {
                const file = input.files[0];
                uploadBlobToSopPoint(currentSopPointId, file, file.name);
                input.value = '';
            }
        };

        window.handleDirectCamVideoInput = function(input) {
            if (input.files && input.files[0]) {
                const file = input.files[0];

                // Cek batas maksimal ukuran video 6MB
                if (file.size > MAX_VIDEO_BYTES) {
                    const sizeMB = (file.size / (1024 * 1024)).toFixed(2);
                    window.showVideoSizeWarningModal(sizeMB);
                    input.value = '';
                    return;
                }

                uploadBlobToSopPoint(currentSopPointId, file, file.name);
                input.value = '';
            }
        };

        window.handleCamFallbackUpload = function(input) {
            if (input.files && input.files[0]) {
                const file = input.files[0];
                uploadBlobToSopPoint(currentSopPointId, file, file.name);
                input.value = '';
            }
        };

        // =========================================================================
        // NAMA FUNCTION : closeFullscreenCamera
        // FUNGSI        : Menutup modal fullscreen kamera, mematikan media stream
        //                 dan mengembalikan scroll pada body halaman
        // =========================================================================
        window.closeFullscreenCamera = function() {
            stopCameraStream();
            const modal = document.getElementById('fullscreen-sop-camera-modal');
            if (modal) {
                modal.style.display = 'none';
            }
            document.body.style.overflow = '';
            window.removeEventListener('resize', updateCameraOrientation);
            if (screen.orientation) {
                screen.orientation.removeEventListener('change', updateCameraOrientation);
            }
        };
    </script>


</x-field-layout>
