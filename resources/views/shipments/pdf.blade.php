<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Loading Evidence - {{ $shipment->packing_list_no ?: $shipment->nomor_container_atau_plat }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 8mm 9mm 9mm 9mm;
        }

        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 10px;
            color: #1e293b;
            background-color: #ffffff;
            margin: 0;
            padding: 0;
            line-height: 1.35;
        }

        /* Header Style */
        .header-container {
            border-bottom: 2px solid #1e3a8a;
            padding-bottom: 5px;
            margin-bottom: 8px;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
        }

        .header-table td {
            vertical-align: middle;
        }

        .brand-title {
            font-size: 16px;
            font-weight: 800;
            color: #1e3a8a;
            letter-spacing: 0.5px;
            margin: 0;
        }

        .brand-subtitle {
            font-size: 9.5px;
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
            font-size: 13px;
            font-weight: 700;
            color: #0f172a;
            margin: 0;
            text-transform: uppercase;
        }

        .doc-meta {
            font-size: 9px;
            color: #64748b;
            margin-top: 2px;
        }

        /* Section Headings */
        .section-title {
            font-size: 10px;
            font-weight: 700;
            color: #1e3a8a;
            background-color: #f1f5f9;
            padding: 4px 6px;
            border-left: 3px solid #1e3a8a;
            margin: 6px 0 4px 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Info Table */
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }

        .info-table th,
        .info-table td {
            border: 1px solid #cbd5e1;
            padding: 3.5px 6px;
            font-size: 9.5px;
        }

        .info-table th {
            background-color: #f8fafc;
            color: #475569;
            font-weight: 600;
            text-align: left;
            width: 18%;
        }

        .info-table td {
            color: #0f172a;
            width: 32%;
        }

        .badge-status {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 9px;
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

        /* Evidence Photos Grid */
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
            font-size: 9px;
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
            font-size: 8px;
            color: #64748b;
            border-radius: 0 0 3px 3px;
        }

        .footer-note {
            margin-top: 10px;
            padding-top: 6px;
            border-top: 1px solid #e2e8f0;
            font-size: 8px;
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
                padding: 15mm;
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

            .btn-primary:hover {
                background-color: #1e40af;
            }

            .btn-secondary {
                background-color: #ffffff;
                color: #475569;
                border: 1px solid #cbd5e1;
            }

            .btn-secondary:hover {
                background-color: #f8fafc;
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

    <div class="sheet">
        <!-- Header -->
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
                <td><strong style="color: #1e3a8a; font-size: 11px;">{{ $shipment->packing_list_no ?? '-' }}</strong></td>
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
                <td>Gudang {{ ucfirst($shipment->warehouse_lokasi ?? '-') }}</td>
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
                <th>Petugas Pelaksana</th>
                <td>
                    {{ $shipment->allKaryawans()->pluck('nama')->join(', ') ?: ($shipment->karyawan?->nama ?? '-') }}
                </td>
            </tr>
            <tr>
                <th>Kondisi Cuaca</th>
                <td style="text-transform: capitalize;">{{ $shipment->cuaca ?? '-' }}</td>
                <th>Status Dokumen</th>
                <td>
                    <span class="badge-status {{ $shipment->status === 'submitted' ? 'badge-submitted' : 'badge-draft' }}">
                        {{ strtoupper($shipment->status ?? 'DRAFT') }}
                    </span>
                    @if($shipment->submitted_at)
                        <span style="font-size: 9px; color: #64748b;">({{ $shipment->submitted_at->format('d/m/Y H:i') }} WIB)</span>
                    @endif
                </td>
            </tr>
            <tr>
                <th>Waktu Kedatangan Kontainer</th>
                <td>{{ $shipment->waktu_kedatangan_container ? $shipment->waktu_kedatangan_container->format('d/m/Y H:i') . ' WIB' : '-' }}</td>
                <th>Waktu Keberangkatan</th>
                <td>{{ $shipment->waktu_keberangkatan_container ? $shipment->waktu_keberangkatan_container->format('d/m/Y H:i') . ' WIB' : '-' }}</td>
            </tr>
        </table>

        <!-- Section 2: 27 Titik SOP Loading Evidence (Hanya Foto, Tanpa Video) -->
        <div class="section-title">2. Bukti Foto Loading Evidence (27 Titik SOP Inspeksi)</div>
        
        @php
            // Filter hanya FOTO (tanpa video)
            $photoEvidence = $shipmentPhotos->filter(function($item) {
                return $item['tipe_item'] === 'foto';
            })->values();

            $chunks = $photoEvidence->chunk(2);
        @endphp

        @if($photoEvidence->isEmpty())
            <div style="padding: 20px; text-align: center; border: 1px dashed #cbd5e1; border-radius: 6px; color: #64748b; font-size: 10px; margin-top: 10px;">
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
                                                <span style="background: rgba(255,255,255,0.25); padding: 1px 4px; border-radius: 2px; font-size: 7.5px;">EKSTRA</span>
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
            Dokumen ini dihasilkan secara otomatis oleh Sistem SPV-Track PT. South Pacific Viscose &bull; Sah sebagai bukti verifikasi loading evidence &bull; Halaman 1 dari Selesai
        </div>
    </div>

</body>
</html>
