<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Loading Evidence - {{ $shipment->packing_list_no ?: $shipment->nomor_container_atau_plat }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 7mm 8mm 7mm 8mm;
        }

        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 8.5pt;
            color: #1e293b;
            background-color: #ffffff;
            margin: 0;
            padding: 0;
            line-height: 1.25;
        }

        .page-break {
            page-break-after: always;
            break-after: page;
        }

        /* Header Style */
        .header-container {
            border-bottom: 2px solid #1e3a8a;
            padding-bottom: 4px;
            margin-bottom: 6px;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
        }

        .header-table td {
            vertical-align: middle;
        }

        .brand-title {
            font-size: 15px;
            font-weight: 800;
            color: #1e3a8a;
            letter-spacing: 0.5px;
            margin: 0;
        }

        .brand-subtitle {
            font-size: 9px;
            font-weight: 600;
            color: #64748b;
            margin: 1px 0 0 0;
            text-transform: uppercase;
            letter-spacing: 0.8px;
        }

        .doc-badge {
            text-align: right;
        }

        .doc-title {
            font-size: 12px;
            font-weight: 700;
            color: #0f172a;
            margin: 0;
            text-transform: uppercase;
        }

        .doc-meta {
            font-size: 8.5px;
            color: #64748b;
            margin-top: 1px;
        }

        /* Section Headings */
        .section-title {
            font-size: 9.5pt;
            font-weight: 700;
            color: #1e3a8a;
            background-color: #f1f5f9;
            padding: 3.5px 6px;
            border-left: 3px solid #1e3a8a;
            margin: 6px 0 4px 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Info Table */
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
        }

        .info-table th,
        .info-table td {
            border: 1px solid #cbd5e1;
            padding: 3.5px 6px;
            font-size: 8.5pt;
        }

        .info-table th {
            background-color: #f8fafc;
            color: #475569;
            font-weight: 600;
            text-align: left;
            width: 20%;
        }

        .info-table td {
            color: #0f172a;
            width: 30%;
        }

        .badge-status {
            display: inline-block;
            padding: 1.5px 5px;
            border-radius: 4px;
            font-size: 8pt;
            font-weight: 700;
            text-transform: uppercase;
        }

        .badge-submitted {
            background-color: #dcfce7;
            color: #15803d;
        }

        .badge-draft {
            background-color: #fef3c7;
            color: #b45309;
        }

        /* Lenzing Specific Styling */
        .lenzing-title {
            font-size: 16pt;
            font-weight: 900;
            color: #00994d;
            letter-spacing: -0.5px;
            line-height: 1;
            text-align: right;
        }

        .lenzing-sub {
            font-size: 7pt;
            color: #4b5563;
            text-align: right;
            margin-top: 1px;
        }

        .form-banner-title {
            text-align: center;
            font-size: 10.5pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 4px 0 5px 0;
            padding: 2.5px 0;
            border-top: 1.5px solid #000;
            border-bottom: 1.5px solid #000;
        }

        .loading-box {
            border: 1px solid #000;
            width: 155px;
            font-size: 7pt;
        }
        .loading-box th {
            background-color: #f3f4f6;
            border-bottom: 1px solid #000;
            padding: 1.5px 3px;
            text-align: center;
            font-weight: bold;
        }
        .loading-box td {
            padding: 1px 3px;
            border-bottom: 1px solid #e5e7eb;
        }
        .loading-box tr:last-child td { border-bottom: none; }

        .meta-box-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 5px;
            font-size: 7.5pt;
        }
        .meta-box-table td {
            padding: 2px 4px;
            border: 1px solid #000;
        }
        .meta-box-table .lbl {
            font-weight: bold;
            background-color: #f9fafb;
            width: 25%;
        }
        .meta-box-table .vl {
            font-family: monospace;
            font-weight: bold;
            width: 25%;
        }

        .warning-banner {
            border: 1.5px solid #000;
            text-align: center;
            padding: 3px;
            margin-bottom: 5px;
            font-size: 7.5pt;
        }
        .warning-banner .hl {
            font-weight: 900;
            color: #dc2626;
        }

        .grid-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 7.5pt;
            margin-bottom: 5px;
        }
        .grid-table th {
            border: 1px solid #000;
            background-color: #f3f4f6;
            padding: 2.5px 3px;
            font-weight: bold;
            text-align: center;
        }
        .grid-table td {
            border: 1px solid #000;
            padding: 2.5px 3px;
            vertical-align: top;
        }

        .check-box {
            display: inline-block;
            width: 10px;
            height: 10px;
            border: 1px solid #000;
            margin-right: 2px;
            vertical-align: middle;
            line-height: 9px;
            text-align: center;
            font-size: 7pt;
            font-weight: bold;
        }

        .sign-line {
            border-bottom: 1px solid #000;
            display: inline-block;
            min-width: 100px;
            margin-left: 2px;
            font-weight: bold;
        }

        /* Evidence Photos Grid */
        .evidence-grid {
            width: 100%;
            border-collapse: separate;
            border-spacing: 6px;
            margin-top: 4px;
        }

        .evidence-row {
            page-break-inside: avoid !important;
            break-inside: avoid !important;
        }

        .evidence-cell {
            width: 50%;
            vertical-align: top;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            background-color: #ffffff;
            padding: 4px;
            page-break-inside: avoid !important;
            break-inside: avoid !important;
        }

        .photo-card {
            width: 100%;
        }

        .photo-header {
            background-color: #1e3a8a;
            color: #ffffff;
            padding: 3.5px 6px;
            border-radius: 3px 3px 0 0;
            font-size: 8.5pt;
            font-weight: 700;
            line-height: 1.2;
        }

        .photo-wrapper {
            width: 100%;
            height: 155px;
            text-align: center;
            background-color: #0f172a;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .photo-img {
            max-width: 100%;
            max-height: 155px;
            object-fit: contain;
            display: block;
            margin: 0 auto;
        }

        .photo-footer {
            background-color: #f8fafc;
            border-top: 1px solid #e2e8f0;
            padding: 3px 6px;
            font-size: 7.5pt;
            color: #64748b;
            border-radius: 0 0 3px 3px;
        }

        .footer-note {
            margin-top: 8px;
            padding-top: 4px;
            border-top: 1px solid #e2e8f0;
            font-size: 7.5pt;
            color: #94a3b8;
            text-align: right;
            page-break-inside: avoid;
        }

        /* Screen only controls (hidden when printed) */
        @media screen {
            body {
                background-color: #e2e8f0;
                padding: 20px;
            }

            .sheet {
                background: white;
                box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
                margin: 0 auto 20px auto;
                max-width: 210mm;
                padding: 12mm 15mm;
                border-radius: 8px;
            }

            .action-bar {
                max-width: 210mm;
                margin: 0 auto 15px auto;
                display: flex;
                justify-content: space-between;
                align-items: center;
            }

            .btn {
                display: inline-flex;
                align-items: center;
                gap: 6px;
                padding: 8px 16px;
                font-size: 12px;
                font-weight: 700;
                border-radius: 8px;
                cursor: pointer;
                text-decoration: none;
                transition: all 0.2s;
                border: none;
            }

            .btn-primary {
                background-color: #1e3a8a;
                color: #ffffff;
            }

            .btn-secondary {
                background-color: #ffffff;
                color: #475569;
                border: 1px solid #cbd5e1;
            }
        }

        @media print {
            .action-bar {
                display: none !important;
            }
            .sheet {
                box-shadow: none !important;
                padding: 0 !important;
                margin: 0 !important;
            }
        }
    </style>
</head>
<body>

    @if(!($isPdf ?? false))
    <!-- Action Bar for Screen Browser Preview -->
    <div class="action-bar">
        <a href="{{ route('shipments.show', $shipment->id) }}" class="btn btn-secondary">
            &larr; Kembali ke Detail Shipment
        </a>
        <div style="display: flex; gap: 8px;">
            <button onclick="window.print()" class="btn btn-secondary">
                &#128438; Cetak Dokumen / Simpan PDF
            </button>
            <a href="{{ route('shipments.download-pdf', $shipment->id) }}" class="btn btn-primary">
                &#128196; Download File PDF (.pdf)
            </a>
        </div>
    </div>
    @endif

    <!-- ========================================================================= -->
    <!-- HALAMAN 1 : TABEL DETAIL SHIPMENT LENGKAP -->
    <!-- ========================================================================= -->
    <div class="sheet">
        <!-- Header Halaman 1 -->
        <div class="header-container">
            <table class="header-table">
                <tr>
                    <td style="width: 55%;">
                        <h1 class="brand-title">PT. SOUTH PACIFIC VISCOSE</h1>
                        <p class="brand-subtitle">SPV-Track Logistics & Warehouse Monitoring</p>
                    </td>
                    <td class="doc-badge" style="width: 45%;">
                        <h2 class="doc-title">Laporan Loading Evidence</h2>
                        <div class="doc-meta">
                            No. Dokumen: <strong>{{ $shipment->packing_list_no ?: ('SPV-'.$shipment->id) }}</strong><br>
                            Waktu Cetak: {{ now('Asia/Jakarta')->format('d F Y, H:i') }} WIB
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Section 1: Informasi Shipment Lengkap -->
        <div class="section-title">1. Data Informasi Pengiriman (Shipment)</div>
        <table class="info-table">
            <tr>
                <th>No. Packing List</th>
                <td><strong style="color: #1e3a8a; font-size: 10pt;">{{ $shipment->packing_list_no ?? '-' }}</strong></td>
                <th>No. Shipment Group</th>
                <td><strong>{{ $shipment->shipment_group ?? '-' }}</strong></td>
            </tr>
            <tr>
                <th>No. Shipment</th>
                <td><strong>{{ $shipment->shipment_no ?? '-' }}</strong></td>
                <th>Customer / Tujuan</th>
                <td>{{ $shipment->tujuan_pengiriman ?? '-' }}</td>
            </tr>
            <tr>
                <th>Agen Shipping / Forwarding</th>
                <td>{{ $shipment->agen_forwarding ?? '-' }}</td>
                <th>Tanggal & Shift Staging</th>
                <td>
                    {{ $shipment->tanggal_staging ? $shipment->tanggal_staging->format('d/m/Y') : '-' }}
                    ({{ ucfirst($shipment->waktu ?? '-') }})
                </td>
            </tr>
            <tr>
                <th>Lokasi Warehouse</th>
                <td>{{ $shipment->warehouse_label }}</td>
                <th>Jenis Produk & Pengiriman</th>
                <td>
                    <span style="text-transform: capitalize;">{{ $shipment->jenis_produk ?? '-' }}</span> &bull; 
                    <span style="text-transform: uppercase;">{{ $shipment->jenis_pengiriman ?? '-' }}</span>
                </td>
            </tr>
            <tr>
                <th>No. Container</th>
                <td><strong style="color: #0f172a;">{{ $shipment->nomor_container_atau_plat ?? '-' }}</strong></td>
                <th>No. Plat Truk</th>
                <td><strong>{{ $shipment->plat_nomor ?? '-' }}</strong></td>
            </tr>
            <tr>
                <th>Nama Sopir</th>
                <td>{{ $shipment->nama_sopir ?? '-' }}</td>
                <th>Nomor Seal</th>
                <td><strong style="color: #0f172a;">{{ $shipment->nomor_seal ?? '-' }}</strong></td>
            </tr>
            <tr>
                <th>Petugas Pelaksana</th>
                <td>
                    {{ $shipment->allKaryawans()->pluck('nama')->join(', ') ?: ($shipment->karyawan?->nama ?? '-') }}
                </td>
                <th>Kondisi Cuaca</th>
                <td style="text-transform: capitalize;">{{ $shipment->cuaca ?? '-' }}</td>
            </tr>
            <tr>
                <th>Status Dokumen</th>
                <td>
                    <span class="badge-status {{ $shipment->status === 'submitted' ? 'badge-submitted' : 'badge-draft' }}">
                        {{ strtoupper($shipment->status ?? 'DRAFT') }}
                    </span>
                    @if($shipment->submitted_at)
                        <span style="font-size: 7.5pt; color: #64748b;">({{ $shipment->submitted_at->format('d/m/Y H:i') }} WIB)</span>
                    @endif
                </td>
                <th>Waktu Kedatangan Kontainer</th>
                <td>{{ $shipment->waktu_kedatangan_container ? $shipment->waktu_kedatangan_container->format('d/m/Y H:i') . ' WIB' : '-' }}</td>
            </tr>
            <tr>
                <th>Waktu Keberangkatan</th>
                <td colspan="3">{{ $shipment->waktu_keberangkatan_container ? $shipment->waktu_keberangkatan_container->format('d/m/Y H:i') . ' WIB' : '-' }}</td>
            </tr>
        </table>

        <!-- Ringkasan Status 2 Form Inspeksi Tambahan -->
        <div class="section-title">2. Ringkasan Status Inspeksi Fisik (Lenzing Standards)</div>
        <table class="info-table" style="margin-bottom: 12px;">
            <tr>
                <th style="width: 25%;">Form Inspeksi Kontainer</th>
                <td style="width: 25%;">
                    @if($shipment->containerInspection)
                        <span class="badge-status" style="background:#dcfce7; color:#15803d;">
                            {{ strtoupper($shipment->containerInspection->kesimpulan_label) }}
                        </span>
                        <div style="font-size: 7.5pt; color: #64748b; margin-top: 2px;">
                            Inspektor: {{ $shipment->containerInspection->nama_inspektor }}
                        </div>
                    @else
                        <span class="badge-status" style="background:#fee2e2; color:#b91c1c;">BELUM DIISI</span>
                    @endif
                </td>
                <th style="width: 25%;">Form Inspeksi Bale (Loading)</th>
                <td style="width: 25%;">
                    @if($shipment->baleInspection)
                        <span class="badge-status" style="background:#dcfce7; color:#15803d;">
                            {{ strtoupper($shipment->baleInspection->kesimpulan_label) }}
                        </span>
                        <div style="font-size: 7.5pt; color: #64748b; margin-top: 2px;">
                            Verifikator: {{ $shipment->baleInspection->second_eye_nama ?: '-' }}
                        </div>
                    @else
                        <span class="badge-status" style="background:#fee2e2; color:#b91c1c;">BELUM DIISI</span>
                    @endif
                </td>
            </tr>
        </table>

        <!-- Pengesahan Dokumen -->
        <table style="width: 100%; border-collapse: collapse; margin-top: 15px; font-size: 8pt;">
            <tr>
                <td style="width: 33%; text-align: center; border: 1px solid #cbd5e1; padding: 6px;">
                    <div style="color: #64748b; margin-bottom: 30px;">Petugas Pelaksana Staging:</div>
                    <div style="font-weight: bold; border-top: 1px dotted #94a3b8; padding-top: 3px;">
                        {{ $shipment->allKaryawans()->pluck('nama')->first() ?: ($shipment->karyawan?->nama ?? '........................') }}
                    </div>
                </td>
                <td style="width: 33%; text-align: center; border: 1px solid #cbd5e1; padding: 6px;">
                    <div style="color: #64748b; margin-bottom: 30px;">Petugas Inspeksi Kontainer:</div>
                    <div style="font-weight: bold; border-top: 1px dotted #94a3b8; padding-top: 3px;">
                        {{ $shipment->containerInspection?->nama_inspektor ?: '........................' }}
                    </div>
                </td>
                <td style="width: 33%; text-align: center; border: 1px solid #cbd5e1; padding: 6px;">
                    <div style="color: #64748b; margin-bottom: 30px;">Verifikator 4-Eyes (Supervisor):</div>
                    <div style="font-weight: bold; border-top: 1px dotted #94a3b8; padding-top: 3px;">
                        {{ $shipment->baleInspection?->second_eye_nama ?: '........................' }}
                    </div>
                </td>
            </tr>
        </table>

        <div class="footer-note">
            Dokumen ini dihasilkan secara otomatis oleh Sistem SPV-Track PT. South Pacific Viscose &bull; <strong>Halaman 1: Ringkasan & Detail Shipment</strong>
        </div>
    </div>

    <div class="page-break"></div>

    <!-- ========================================================================= -->
    <!-- HALAMAN 2 : FORM INSPEKSI KONDISI KONTAINER (LENZING STANDARD) -->
    <!-- ========================================================================= -->
    <div class="sheet">
        @php
            $cInsp = $shipment->containerInspection;
        @endphp
        <!-- Top Header Lenzing -->
        <table class="header-table">
            <tr>
                <td style="width: 50%;">
                    <table class="loading-box">
                        <tr><th colspan="2">LOADING</th></tr>
                        <tr>
                            <td style="width: 40px; font-weight: bold;">Date</td>
                            <td>: {{ $shipment->tanggal_staging ? $shipment->tanggal_staging->format('d/m/Y') : ($cInsp?->loading_date ? $cInsp->loading_date->format('d/m/Y') : now()->format('d/m/Y')) }}</td>
                        </tr>
                        <tr><td colspan="2" style="font-weight: bold; background: #fafafa; padding-left: 8px;">Time</td></tr>
                        <tr>
                            <td style="padding-left: 8px;">Start</td>
                            <td>: {{ $shipment->waktu_kedatangan_container ? $shipment->waktu_kedatangan_container->format('H:i') : ($cInsp?->loading_start_time ?: '-') }}</td>
                        </tr>
                        <tr>
                            <td style="padding-left: 8px;">Finish</td>
                            <td>: {{ $shipment->waktu_keberangkatan_container ? $shipment->waktu_keberangkatan_container->format('H:i') : ($cInsp?->loading_finish_time ?: '-') }}</td>
                        </tr>
                        <tr>
                            <td style="font-weight: bold;">Name</td>
                            <td>: {{ $cInsp?->nama_petugas_inspeksi ?: ($shipment->allKaryawans()->pluck('nama')->first() ?: ($shipment->karyawan?->nama ?? '-')) }}</td>
                        </tr>
                    </table>
                </td>
                <td style="width: 50%; vertical-align: top;">
                    <div class="lenzing-title">Lenzing</div>
                    <div class="lenzing-sub">Innovative by nature</div>
                </td>
            </tr>
        </table>

        <div class="form-banner-title">FORM INSPEKSI KONDISI KONTAINER</div>

        <table class="meta-box-table">
            <tr>
                <td class="lbl">NOMOR KONTAINER</td>
                <td class="vl">{{ $cInsp?->nomor_kontainer ?: ($shipment->nomor_container_atau_plat ?? '-') }}</td>
                <td class="lbl">NAMA EMKL & PELAYARAN</td>
                <td class="vl" style="font-family: inherit;">{{ $cInsp?->nama_emkl_pelayaran ?: ($shipment->agen_forwarding ?? '-') }}</td>
            </tr>
            <tr>
                <td class="lbl">NOMOR SEAL</td>
                <td class="vl">{{ $cInsp?->nomor_seal ?: ($shipment->nomor_seal ?? '-') }}</td>
                <td class="lbl">NAMA PETUGAS INSPEKSI</td>
                <td class="vl" style="font-family: inherit;">{{ $cInsp?->nama_petugas_inspeksi ?: ($shipment->allKaryawans()->pluck('nama')->first() ?: ($shipment->karyawan?->nama ?? '-')) }}</td>
            </tr>
        </table>

        <div class="warning-banner">
            <span class="hl">PASTIKAN TIDAK:</span>
            <strong>BERLUBANG / RETAK, BERKARAT, BERMINYAK / KOTOR, BERBAU / WANGI, ADA BAGIAN YANG TAJAM / PAKU / LANTAI LAPUK</strong>
        </div>

        <!-- 6 Titik Inspeksi Container -->
        <table class="grid-table">
            <thead>
                <tr>
                    <th style="width: 52%;">TITIK INSPEKSI</th>
                    <th style="width: 16%;">HASIL*<br><span style="font-size: 6.5pt; font-weight: normal;">*Centang salah satu</span></th>
                    <th style="width: 32%;">CATATAN PEMERIKSAAN / KETERANGAN KONDISI<br><span style="font-size: 6.5pt; font-weight: normal;">(Jika ditemukan gagal harus segera dilaporkan kepada supervisor)</span></th>
                </tr>
            </thead>
            <tbody>
                @php
                    $pts = [
                        [
                            'num' => '1.',
                            'name' => 'PINTU (BAGIAN LUAR & DALAM)',
                            'desc' => "<strong>Pintu luar:</strong> Pastikan tidak bolong / retak.<br><strong>Pintu dalam:</strong> Pastikan tidak bolong, karat, kotor / cat tidak luntur, berdebu, berminyak, bau menyengat. Lakukan tes usap pada pintu dalam & pastikan bersih.",
                            'status' => $cInsp?->pintu_status ?? true,
                            'note' => $cInsp?->pintu_catatan,
                        ],
                        [
                            'num' => '2.',
                            'name' => 'DINDING KANAN KONTAINER (LUAR & DALAM)',
                            'desc' => "<strong>Sisi kanan luar:</strong> Pastikan tidak bolong / retak.<br><strong>Sisi kanan dalam:</strong> Pastikan tidak bolong, karat, kotor, cat luntur, berdebu, ber-oli, bau menyengat. Lakukan tes usap pada dinding dalam & pastikan bersih.",
                            'status' => $cInsp?->dinding_kanan_status ?? true,
                            'note' => $cInsp?->dinding_kanan_catatan,
                        ],
                        [
                            'num' => '3.',
                            'name' => 'DINDING KIRI KONTAINER (LUAR & DALAM)',
                            'desc' => "<strong>Sisi kiri luar:</strong> Pastikan tidak bolong atau retak.<br><strong>Sisi kiri dalam:</strong> Pastikan tidak bolong, karat, kotor, cat luntur, berdebu, ber-oli, bau menyengat. Lakukan tes usap pada dinding dalam & pastikan bersih.",
                            'status' => $cInsp?->dinding_kiri_status ?? true,
                            'note' => $cInsp?->dinding_kiri_catatan,
                        ],
                        [
                            'num' => '4.',
                            'name' => 'DINDING BAGIAN DEPAN (LUAR & DALAM)',
                            'desc' => "<strong>Dinding depan luar:</strong> Pastikan tidak bolong / retak.<br><strong>Dinding depan dalam:</strong> Pastikan tidak bolong, karat, kotor, cat luntur, berdebu, berminyak, bau menyengat. Lakukan tes usap & pastikan bersih.",
                            'status' => $cInsp?->dinding_depan_status ?? true,
                            'note' => $cInsp?->dinding_depan_catatan,
                        ],
                        [
                            'num' => '5.',
                            'name' => 'ATAP KONTAINER',
                            'desc' => "Pastikan tidak bolong, retak, bocor, karat, kotor, berminyak / ber-oli, bau / wangi yang menyengat. Pastikan dalam keadaan bersih.",
                            'status' => $cInsp?->atap_status ?? true,
                            'note' => $cInsp?->atap_catatan,
                        ],
                        [
                            'num' => '6.',
                            'name' => 'LANTAI KONTAINER',
                            'desc' => "Pastikan lantai rata, bersih, kering, bebas kotoran/debu & oli. Pastikan tidak ada benda tajam (seperti paku) dan tidak lapuk.",
                            'status' => $cInsp?->lantai_status ?? true,
                            'note' => $cInsp?->lantai_catatan,
                        ],
                    ];
                @endphp

                @foreach($pts as $p)
                <tr>
                    <td>
                        <div style="font-weight: bold; color: #000;">{{ $p['num'] }} {{ $p['name'] }}</div>
                        <div style="font-size: 7pt; color: #374151; margin-top: 1px;">{!! $p['desc'] !!}</div>
                    </td>
                    <td style="text-align: center; vertical-align: middle; font-weight: bold;">
                        <div style="margin-bottom: 2px;"><span class="check-box">{{ $p['status'] ? '✓' : '' }}</span> Ya</div>
                        <div><span class="check-box">{{ !$p['status'] ? '✓' : '' }}</span> Tidak</div>
                    </td>
                    <td style="font-size: 7.5pt; color: {{ $p['status'] ? '#15803d' : '#dc2626' }}; {{ !$p['status'] ? 'font-weight: bold;' : '' }}">
                        {{ $p['note'] ?: ($p['status'] ? 'Kondisi baik & bersih' : '-') }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Kesimpulan Halaman 2 -->
        @php $kes = $cInsp?->kesimpulan ?? 'layak'; @endphp
        <div style="border: 1px solid #000; padding: 4px 6px; font-size: 7.5pt; margin-bottom: 5px;">
            <div>
                <strong>KESIMPULAN:</strong> Saya telah memastikan bahwa kontainer tersebut dalam keadaan:
                <strong style="{{ $kes === 'layak' ? 'background:#dcfce7; padding:1px 4px; border:1px solid #15803d;' : '' }}">{{ $kes === 'layak' ? '[✓] ' : '' }}LAYAK</strong> / 
                <strong style="{{ $kes === 'tidak_layak' ? 'background:#fee2e2; padding:1px 4px; border:1px solid #b91c1c;' : '' }}">{{ $kes === 'tidak_layak' ? '[✓] ' : '' }}TIDAK LAYAK</strong> / 
                <strong style="{{ $kes === 'layak_dengan_catatan' ? 'background:#fef3c7; padding:1px 4px; border:1px solid #b45309;' : '' }}">{{ $kes === 'layak_dengan_catatan' ? '[✓] ' : '' }}LAYAK DENGAN CATATAN</strong>
                untuk dilakukan proses loading/stuffing.
            </div>
            <div style="margin-top: 3px;">
                <strong>CATATAN:</strong> {{ $cInsp?->catatan ?: '-' }}
            </div>
        </div>

        <table style="width: 100%; border-collapse: collapse; font-size: 7.5pt;">
            <tr>
                <td style="width: 50%; border: 1px solid #000; padding: 3px 6px;">
                    <div style="font-size: 6.5pt; color: #4b5563; margin-bottom: 20px;">Tanda Tangan & Nama Inspektor:</div>
                    <strong>( {{ $cInsp?->nama_inspektor ?: ($shipment->allKaryawans()->pluck('nama')->first() ?: ($shipment->karyawan?->nama ?? '........................')) }} )</strong>
                </td>
                <td style="width: 50%; border: 1px solid #000; padding: 3px 6px;">
                    <div style="font-size: 6.5pt; color: #4b5563; margin-bottom: 20px;">Tanggal Inspeksi:</div>
                    <strong>{{ $cInsp?->tanggal_inspeksi ? $cInsp->tanggal_inspeksi->format('d F Y') : now()->format('d F Y') }}</strong>
                </td>
            </tr>
        </table>

        <div class="footer-note">
            Dokumen ini dihasilkan secara otomatis oleh Sistem SPV-Track PT. South Pacific Viscose &bull; <strong>Halaman 2: Formulir Inspeksi Kondisi Kontainer</strong>
        </div>
    </div>

    <div class="page-break"></div>

    <!-- ========================================================================= -->
    <!-- HALAMAN 3 : FORM INSPEKSI KONDISI BALE (LOADING) -->
    <!-- ========================================================================= -->
    <div class="sheet">
        @php
            $bInsp = $shipment->baleInspection;
        @endphp
        <!-- Top Header Lenzing -->
        <table class="header-table">
            <tr>
                <td style="width: 50%;">
                    <table class="loading-box">
                        <tr><th colspan="2">LOADING</th></tr>
                        <tr>
                            <td style="width: 35px; font-weight: bold;">Date</td>
                            <td>: {{ $shipment->tanggal_staging ? $shipment->tanggal_staging->format('d/m/Y') : ($bInsp?->loading_date ? $bInsp->loading_date->format('d/m/Y') : now()->format('d/m/Y')) }}</td>
                        </tr>
                        <tr><td colspan="2" style="font-weight: bold; background: #fafafa; padding-left: 6px;">Time</td></tr>
                        <tr>
                            <td style="padding-left: 6px;">Start</td>
                            <td>: {{ $shipment->waktu_kedatangan_container ? $shipment->waktu_kedatangan_container->format('H:i') : ($bInsp?->loading_start_time ?: '-') }}</td>
                        </tr>
                        <tr>
                            <td style="padding-left: 6px;">Finish</td>
                            <td>: {{ $shipment->waktu_keberangkatan_container ? $shipment->waktu_keberangkatan_container->format('H:i') : ($bInsp?->loading_finish_time ?: '-') }}</td>
                        </tr>
                        <tr>
                            <td style="font-weight: bold;">Name</td>
                            <td>: {{ $bInsp?->nama_petugas_loading ?: ($shipment->allKaryawans()->pluck('nama')->first() ?: ($shipment->karyawan?->nama ?? '-')) }}</td>
                        </tr>
                    </table>
                </td>
                <td style="width: 50%; vertical-align: top;">
                    <div class="lenzing-title">Lenzing</div>
                    <div class="lenzing-sub">Innovative by nature</div>
                </td>
            </tr>
        </table>

        <div class="form-banner-title">FORM INSPEKSI KONDISI BALE (LOADING)</div>

        <!-- Section A: Info Umum -->
        <div style="font-weight: bold; font-size: 7.5pt; text-transform: uppercase; margin: 3px 0 1px 0;">A. Informasi Umum</div>
        @php
            $jBale = (array) ($bInsp?->jenis_bale ?? ['tx_ecovero']);
            $jLabel = (array) ($bInsp?->jenis_label ?? ['tanpa_label']);
        @endphp
        <table style="width: 100%; border-collapse: collapse; font-size: 7pt; margin-bottom: 3px;">
            <tr>
                <td style="width: 50%; vertical-align: top;">
                    &bull; Nomor Surat Jalan : <strong>{{ $bInsp?->nomor_surat_jalan ?: ($shipment->packing_list_no ?? '-') }}</strong><br>
                    &bull; Nama Pelanggan : <strong>{{ $bInsp?->nama_pelanggan ?: ($shipment->tujuan_pengiriman ?? '-') }}</strong><br>
                    &bull; Jumlah Bale : <strong>{{ $bInsp?->jumlah_bale ? $bInsp->jumlah_bale . ' Bale' : '-' }}</strong>
                </td>
                <td style="width: 50%; vertical-align: top;">
                    &bull; Nomor Kontainer / Truk : <strong>{{ $bInsp?->nomor_kontainer_truk ?: ($shipment->nomor_container_atau_plat ?: ($shipment->plat_nomor ?? '-')) }}</strong><br>
                    &bull; Jenis Bale (✓) : 
                    <span class="check-box">{{ in_array('tx_ecovero', $jBale) ? '✓' : '' }}</span> TX ECOVERO™ &nbsp;
                    <span class="check-box">{{ in_array('tx_non_ecovero', $jBale) ? '✓' : '' }}</span> TX Non-ECOVERO™ &nbsp;
                    <span class="check-box">{{ in_array('nw', $jBale) ? '✓' : '' }}</span> NW<br>
                    &bull; Jenis Sticker (✓) : 
                    <span class="check-box">{{ in_array('tanpa_label', $jLabel) ? '✓' : '' }}</span> Tanpa Sticker &nbsp;
                    <span class="check-box">{{ in_array('fsc', $jLabel) ? '✓' : '' }}</span> FSC &nbsp;
                    <span class="check-box">{{ in_array('isi', $jLabel) ? '✓' : '' }}</span> ISI &nbsp;
                    <span class="check-box">{{ in_array('n', $jLabel) ? '✓' : '' }}</span> N &nbsp;
                    <span class="check-box">{{ in_array('o', $jLabel) ? '✓' : '' }}</span> O &nbsp;
                    <span class="check-box">{{ in_array('a', $jLabel) ? '✓' : '' }}</span> A
                </td>
            </tr>
        </table>

        <!-- Section B: 8 Poin Pemeriksaan Kondisi Bale -->
        <div style="font-weight: bold; font-size: 7.5pt; text-transform: uppercase; margin: 3px 0 1px 0;">B. Pemeriksaan Kondisi Bale – Pemeriksa Pertama</div>
        <table class="grid-table" style="font-size: 6.5pt; margin-bottom: 3px;">
            <thead>
                <tr>
                    <th style="width: 15px;">No</th>
                    <th style="width: 135px; text-align: left;">Jenis Ketidaksesuaian</th>
                    <th style="width: 25px;">Ada (✓)</th>
                    <th style="width: 25px;">Tidak (✓)</th>
                    <th style="width: 45px;">Jumlah Bale</th>
                    <th style="width: 165px; text-align: left;">Tindakan Perbaikan (✓)</th>
                    <th style="width: 75px;">Nomor Bale Awal</th>
                    <th style="width: 75px;">Nomor Bale Pengganti</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $checklistMap = [
                        1 => 'Sobek',
                        2 => 'Ada tambalan',
                        3 => 'Kotor / Bernoda',
                        4 => 'Putus tali strapping',
                        5 => 'Sobek pada welding packaging (NW)',
                        6 => 'Ada serangga',
                        7 => 'Basah',
                        8 => 'Label rusak / tidak seragam (EV / Non-EV)',
                    ];
                    $baleItems = $bInsp ? $bInsp->items->keyBy('item_index') : collect();
                @endphp

                @foreach($checklistMap as $idx => $label)
                @php
                    $it = $baleItems->get($idx);
                    $ada = (bool) ($it?->ada_ketidaksesuaian ?? false);
                    $tind = $it?->tindakan_perbaikan;
                @endphp
                <tr>
                    <td style="text-align: center; font-weight: bold;">{{ $idx }}</td>
                    <td><strong>{{ $label }}</strong></td>
                    <td style="text-align: center;"><span class="check-box">{{ $ada ? '✓' : '' }}</span></td>
                    <td style="text-align: center;"><span class="check-box">{{ !$ada ? '✓' : '' }}</span></td>
                    <td style="text-align: center; font-weight: bold;">{{ $ada && $it?->jumlah_bale ? $it->jumlah_bale : '-' }}</td>
                    <td>
                        <span class="check-box">{{ $ada && $tind === 'diganti' ? '✓' : '' }}</span> Diganti &nbsp;
                        <span class="check-box">{{ $ada && $tind === 'diperbaiki' ? '✓' : '' }}</span> Diperbaiki/ditambal/dibersihkan<br>
                        <span class="check-box">{{ $ada && $tind === 'ganti_label' ? '✓' : '' }}</span> Ganti label (EV/Non-EV) &nbsp;
                        <span class="check-box">{{ $ada && $tind === 'lainnya' ? '✓' : '' }}</span> Lainnya: {{ $ada && $tind === 'lainnya' ? ($it?->tindakan_lainnya ?: '-') : '' }}
                    </td>
                    <td style="text-align: center; font-family: monospace;">{{ $ada ? ($it?->nomor_bale_awal ?: '-') : '-' }}</td>
                    <td style="text-align: center; font-family: monospace;">{{ $ada ? ($it?->nomor_bale_pengganti ?: '-') : '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div style="font-size: 7pt; font-weight: bold; color: #00994d; margin-bottom: 4px;">
            Bale harus dalam keadaan: <u>Bersih, Mulus dan Tidak Cacat</u>
        </div>

        <!-- Section C, D, E, F Grid -->
        <table style="width: 100%; border-collapse: collapse; font-size: 7pt;">
            <tr>
                <!-- Column Left: C. Verifikasi 4 Eyes Principle -->
                <td style="width: 50%; vertical-align: top; border: 1px solid #000; padding: 3px 5px;">
                    <div style="font-weight: bold; text-transform: uppercase;">C. VERIFIKASI 4 EYES PRINCIPLE</div>
                    
                    <div style="font-weight: bold; margin-top: 2px;">1. Pemeriksa Pertama (First Eye)</div>
                    <div style="font-size: 6.5pt; color: #4b5563; font-style: italic;">
                        Saya menyatakan inspeksi kondisi bale telah dilakukan menyeluruh sesuai prosedur.
                    </div>
                    <div style="margin-top: 2px;">
                        &bull; Nama : <span class="sign-line">{{ $bInsp?->first_eye_nama ?: ($shipment->allKaryawans()->pluck('nama')->first() ?: ($shipment->karyawan?->nama ?? '........................')) }}</span><br>
                        &bull; Jabatan / Unit : <span class="sign-line">{{ $bInsp?->first_eye_jabatan ?: 'Operator Lapangan' }}</span><br>
                        &bull; Tanda Tangan & Tanggal : <span class="sign-line">{{ $bInsp?->first_eye_tanggal ? $bInsp->first_eye_tanggal->format('d/m/Y') : now()->format('d/m/Y') }}</span>
                    </div>

                    <div style="font-weight: bold; margin-top: 4px;">2. Pemeriksa Kedua (Second Eye / Verifikator)</div>
                    @php $secStat = $bInsp?->second_eye_status ?? 'disetujui'; @endphp
                    <div style="margin-top: 1px;">
                        Status Verifikasi (✓):<br>
                        <span class="check-box">{{ $secStat === 'disetujui' ? '✓' : '' }}</span> <strong>Disetujui</strong> &nbsp;
                        <span class="check-box">{{ $secStat === 'perlu_perbaikan' ? '✓' : '' }}</span> <strong>Perlu Perbaikan</strong> &nbsp;
                        <span class="check-box">{{ $secStat === 'tidak_disetujui' ? '✓' : '' }}</span> <strong>Tidak Disetujui</strong>
                    </div>
                    <div style="margin-top: 2px;">
                        &bull; Catatan Verifikator : <span class="sign-line">{{ $bInsp?->second_eye_catatan ?: '-' }}</span><br>
                        &bull; Nama : <span class="sign-line">{{ $bInsp?->second_eye_nama ?: ($shipment->allKaryawans()->count() > 1 ? $shipment->allKaryawans()[1]->nama : 'Supervisor Warehouse') }}</span><br>
                        &bull; Jabatan / Unit : <span class="sign-line">{{ $bInsp?->second_eye_jabatan ?: 'Verifikator / SPV' }}</span><br>
                        &bull; Tanda Tangan & Tanggal : <span class="sign-line">{{ $bInsp?->second_eye_tanggal ? $bInsp->second_eye_tanggal->format('d/m/Y') : now()->format('d/m/Y') }}</span>
                    </div>
                </td>

                <!-- Column Right: D, E, F -->
                <td style="width: 50%; vertical-align: top; border: 1px solid #000; padding: 3px 5px;">
                    <!-- D. Sumber Bale -->
                    <div style="font-weight: bold; text-transform: uppercase;">D. Sumber Bale (✓)</div>
                    @php $sb = $bInsp?->sumber_bale ?? 'inspeksi_join_checking'; @endphp
                    <div style="margin-top: 1px;">
                        <span class="check-box">{{ $sb === 'inspeksi_join_checking' ? '✓' : '' }}</span> Hasil inspeksi & join checking (FGW-TCS – QPD – PDP)<br>
                        <span class="check-box">{{ $sb === 'produksi_baru' ? '✓' : '' }}</span> Bale produksi baru &nbsp;
                        <span class="check-box">{{ $sb === 'lainnya' ? '✓' : '' }}</span> Lainnya
                    </div>

                    <!-- E. Lokasi Stagging -->
                    <div style="font-weight: bold; text-transform: uppercase; margin-top: 3px;">E. Lokasi Stagging (✓)</div>
                    <div>
                        Bale dikumpulkan dengan 2 Rubber Cone: 
                        <span class="check-box">{{ ($bInsp?->stagging_2_rubber_cone ?? true) ? '✓' : '' }}</span> Ya &nbsp;
                        <span class="check-box">{{ !($bInsp?->stagging_2_rubber_cone ?? true) ? '✓' : '' }}</span> Tidak<br>
                        Lokasi Stagging : <span class="sign-line">{{ $bInsp?->lokasi_stagging ?: $shipment->warehouse_label }}</span>
                    </div>

                    <!-- F. Kondisi Loading -->
                    <div style="font-weight: bold; text-transform: uppercase; margin-top: 3px;">F. Kondisi Pemuatan (✓)</div>
                    @php
                        $cuaca = $bInsp?->cuaca ?: ($shipment->cuaca ?: 'kering');
                        $waktu = $bInsp?->waktu ?: ($shipment->waktu ?: 'siang');
                    @endphp
                    <div>
                        &bull; Cuaca : 
                        <span class="check-box">{{ $cuaca === 'kering' ? '✓' : '' }}</span> Kering &nbsp;
                        <span class="check-box">{{ $cuaca === 'mendung' ? '✓' : '' }}</span> Mendung &nbsp;
                        <span class="check-box">{{ $cuaca === 'gerimis' ? '✓' : '' }}</span> Gerimis &nbsp;
                        <span class="check-box">{{ $cuaca === 'hujan' ? '✓' : '' }}</span> Hujan<br>
                        &bull; Waktu : 
                        <span class="check-box">{{ $waktu === 'siang' ? '✓' : '' }}</span> Siang &nbsp;
                        <span class="check-box">{{ $waktu === 'sore' ? '✓' : '' }}</span> Sore &nbsp;
                        <span class="check-box">{{ $waktu === 'malam' ? '✓' : '' }}</span> Malam
                    </div>

                    <!-- Kesimpulan Akhir -->
                    <div style="border-top: 1px solid #000; margin-top: 3px; padding-top: 2px;">
                        <div style="font-weight: bold; text-transform: uppercase;">Kesimpulan Akhir (✓)</div>
                        @php $fin = $bInsp?->kesimpulan_akhir ?? 'layak'; @endphp
                        <div>
                            <span class="check-box">{{ $fin === 'layak' ? '✓' : '' }}</span> <strong>Layak untuk proses loading / stuffing</strong><br>
                            <span class="check-box">{{ $fin === 'layak_dengan_catatan' ? '✓' : '' }}</span> <strong>Layak dengan catatan (lihat C.2)</strong><br>
                            <span class="check-box">{{ $fin === 'tidak_layak' ? '✓' : '' }}</span> <strong>Tidak layak (ditunda / diganti)</strong>
                        </div>
                    </div>
                </td>
            </tr>
        </table>

        <div class="footer-note">
            Dokumen ini dihasilkan secara otomatis oleh Sistem SPV-Track PT. South Pacific Viscose &bull; <strong>Halaman 3: Formulir Inspeksi Kondisi Bale (Loading)</strong>
        </div>
    </div>

    <div class="page-break"></div>

    <!-- ========================================================================= -->
    <!-- HALAMAN 4 DST : GALERI FOTO EVIDENCE SOP 27 TITIK -->
    <!-- ========================================================================= -->
    <div class="sheet">
        <!-- Header Galeri Foto -->
        <div class="header-container">
            <table class="header-table">
                <tr>
                    <td style="width: 55%;">
                        <h1 class="brand-title">PT. SOUTH PACIFIC VISCOSE</h1>
                        <p class="brand-subtitle">SPV-Track Evidence Documentation Gallery</p>
                    </td>
                    <td class="doc-badge" style="width: 45%;">
                        <h2 class="doc-title">Lampiran Foto Evidence</h2>
                        <div class="doc-meta">
                            No. Dokumen: <strong>{{ $shipment->packing_list_no ?: ('SPV-'.$shipment->id) }}</strong><br>
                            Kontainer / Truk: <strong>{{ $shipment->nomor_container_atau_plat ?: $shipment->plat_nomor }}</strong>
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        <div class="section-title">4. Bukti Foto Loading Evidence (27 Titik SOP Inspeksi)</div>
        
        @php
            // Filter hanya FOTO (tanpa video)
            $photoEvidence = $shipmentPhotos->filter(function($item) {
                return $item['tipe_item'] === 'foto';
            })->values();

            $chunks = $photoEvidence->chunk(2);
        @endphp

        @if($photoEvidence->isEmpty())
            <div style="padding: 30px; text-align: center; border: 1px dashed #cbd5e1; border-radius: 6px; color: #64748b; font-size: 10px; margin-top: 15px;">
                Belum ada bukti foto SOP yang diunggah untuk shipment ini.
            </div>
        @else
            <table class="evidence-grid">
                @foreach($chunks as $pair)
                    <tr class="evidence-row">
                        @foreach($pair as $item)
                            <td class="evidence-cell">
                                <div class="photo-card">
                                    <div class="photo-header">
                                        <div style="float: right;">
                                            @if($item['is_extra'])
                                                <span style="background: rgba(255,255,255,0.25); padding: 1px 4px; border-radius: 2px; font-size: 7pt;">EKSTRA</span>
                                            @endif
                                        </div>
                                        <span>Titik {{ $item['point_no'] ? sprintf('%02d', $item['point_no']) : '-' }}: {{ $item['point_name'] }}</span>
                                    </div>
                                    <div class="photo-wrapper">
                                        <img src="{{ $item['image_src'] }}" class="photo-img" alt="{{ $item['point_name'] }}">
                                    </div>
                                    <div class="photo-footer">
                                        Waktu Stempel Server: <strong>{{ $item['timestamp_wib'] }}</strong>
                                    </div>
                                </div>
                            </td>
                        @endforeach

                        {{-- Jika kolom ganjil pada baris terakhir, buat sel dummy agar lebar seimbang --}}
                        @if($pair->count() == 1)
                            <td class="evidence-cell" style="border: none; background: transparent;"></td>
                        @endif
                    </tr>
                @endforeach
            </table>
        @endif

        <div class="footer-note">
            Dokumen ini dihasilkan secara otomatis oleh Sistem SPV-Track PT. South Pacific Viscose &bull; <strong>Halaman 4 dst: Galeri Foto Evidence SOP</strong>
        </div>
    </div>

</body>
</html>
