<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Berita Acara & Rekapitulasi Staging - SPV-Track</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 7mm 8mm 8mm 8mm;
        }

        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 8.5px;
            color: #1e293b;
            background-color: #ffffff;
            margin: 0;
            padding: 0;
            line-height: 1.3;
        }

        .no-print {
            display: {{ $isPdf ?? false ? 'none' : 'block' }};
        }

        @media print {
            .no-print {
                display: none !important;
            }
            body {
                margin: 0;
                padding: 0;
            }
        }

        /* Screen Action Bar */
        .action-bar {
            background: linear-gradient(135deg, #1a3c6e 0%, #285491 100%);
            color: #ffffff;
            padding: 12px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            font-size: 13px;
        }

        .action-bar-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #ffffff;
            color: #1a3c6e;
            padding: 6px 14px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: bold;
            font-size: 12px;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
        }

        .action-bar-btn:hover {
            background: #f1f5f9;
            transform: translateY(-1px);
        }

        .action-bar-btn-secondary {
            background: rgba(255,255,255,0.15);
            color: #ffffff;
            border: 1px solid rgba(255,255,255,0.3);
        }

        .action-bar-btn-secondary:hover {
            background: rgba(255,255,255,0.25);
            color: #ffffff;
        }

        .content-wrapper {
            padding: {{ $isPdf ?? false ? '0' : '20px 30px' }};
        }

        /* Official Header */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 2px solid #1a3c6e;
            padding-bottom: 6px;
            margin-bottom: 8px;
        }

        .header-table td {
            vertical-align: middle;
        }

        .company-name {
            font-size: 15px;
            font-weight: 800;
            color: #1a3c6e;
            letter-spacing: 0.5px;
            margin: 0;
        }

        .company-sub {
            font-size: 8.5px;
            font-weight: 600;
            color: #475569;
            margin: 2px 0 0 0;
            text-transform: uppercase;
            letter-spacing: 0.6px;
        }

        .doc-title {
            font-size: 13px;
            font-weight: 800;
            color: #0f172a;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            text-align: right;
        }

        .doc-meta {
            font-size: 8px;
            color: #64748b;
            text-align: right;
            margin-top: 2px;
        }

        /* KPI & Filter Info Bar */
        .info-grid {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
        }

        .info-grid td {
            padding: 5px 8px;
            font-size: 8.5px;
            border: 1px solid #e2e8f0;
        }

        .kpi-badge {
            font-weight: bold;
            color: #1a3c6e;
        }

        /* Data Tables */
        .rekap-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }

        .rekap-table th {
            background-color: #1a3c6e;
            color: #ffffff;
            font-weight: 700;
            font-size: 8px;
            padding: 5px 6px;
            border: 1px solid #1a3c6e;
            text-align: center;
            text-transform: uppercase;
        }

        .rekap-table td {
            padding: 4px 6px;
            font-size: 8px;
            border: 1px solid #cbd5e1;
            vertical-align: middle;
        }

        .rekap-table tr:nth-child(even) {
            background-color: #f8fafc;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }

        .pill {
            display: inline-block;
            padding: 1px 4px;
            border-radius: 3px;
            font-size: 7.5px;
            font-weight: 700;
            text-align: center;
        }

        .pill-green {
            background: #dcfce7;
            color: #15803d;
        }

        .pill-amber {
            background: #fef3c7;
            color: #b45309;
        }

        .pill-blue {
            background: #e0f2fe;
            color: #0369a1;
        }

        .pill-purple {
            background: #f3e8ff;
            color: #7e22ce;
        }

        /* Section Title */
        .section-title {
            font-size: 9.5px;
            font-weight: 800;
            color: #1a3c6e;
            text-transform: uppercase;
            border-bottom: 1px solid #cbd5e1;
            padding-bottom: 3px;
            margin: 10px 0 6px 0;
            letter-spacing: 0.4px;
        }

        /* Signature Block */
        .signature-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 14px;
            page-break-inside: avoid;
        }

        .signature-table td {
            width: 33.33%;
            text-align: center;
            vertical-align: top;
            padding: 4px 10px;
        }

        .sig-role {
            font-size: 8.5px;
            font-weight: 700;
            color: #334155;
            margin-bottom: 45px;
        }

        .sig-name {
            font-size: 8.5px;
            font-weight: 800;
            color: #0f172a;
            border-top: 1px solid #64748b;
            padding-top: 4px;
            display: inline-block;
            min-width: 140px;
        }

        .sig-title {
            font-size: 7.5px;
            color: #64748b;
            margin-top: 1px;
        }
    </style>
</head>
<body>

    @if(!($isPdf ?? false))
        <!-- Screen Action Bar (Hidden on Print & PDF) -->
        <div class="action-bar no-print">
            <div style="display: flex; align-items: center; gap: 12px;">
                <span style="font-weight: bold; font-size: 14px;">SPV-Track &bull; Cetak Berita Acara Rekap</span>
                <span style="opacity: 0.8; font-size: 12px;">| Periode: {{ $periodLabel }}</span>
            </div>
            <div style="display: flex; align-items: center; gap: 10px;">
                <a href="{{ route('laporan.index', request()->query()) }}" class="action-bar-btn action-bar-btn-secondary">
                    &larr; Kembali ke Laporan
                </a>
                <a href="{{ route('laporan.download-rekap-pdf', request()->query()) }}" class="action-bar-btn action-bar-btn-secondary">
                    Unduh File PDF
                </a>
                <button type="button" onclick="window.print()" class="action-bar-btn">
                    Cetak Dokumen
                </button>
            </div>
        </div>
    @endif

    <div class="content-wrapper">
        <!-- Header Dokumen Resmi -->
        <table class="header-table">
            <tr>
                <td style="width: 55%;">
                    <p class="company-name">PT. SOUTH PACIFIC VISCOSE</p>
                    <p class="company-sub">Warehouse & Logistics Department &bull; Digital Evidence Monitoring System</p>
                    <p style="font-size: 7.5px; color: #64748b; margin: 1px 0 0 0;">Desa Cicadas, Kec. Purwakarta, Kab. Purwakarta, Jawa Barat - Indonesia</p>
                </td>
                <td style="width: 45%;">
                    <p class="doc-title">BERITA ACARA & REKAPITULASI STAGING</p>
                    <p class="doc-meta">
                        Dokumen: <strong>BA-SPV/{{ date('Y') }}/{{ date('m') }}/{{ str_pad((string)($summary->total_shipments), 3, '0', STR_PAD_LEFT) }}</strong><br>
                        Periode: <strong>{{ $periodLabel }}</strong> &bull; Dicetak: {{ now('Asia/Jakarta')->translatedFormat('d M Y H:i') }} WIB
                    </p>
                </td>
            </tr>
        </table>

        <!-- Ringkasan Eksekutif KPI -->
        <table class="info-grid">
            <tr>
                <td style="width: 25%;">
                    Total Armada/Kontainer: <span class="kpi-badge">{{ $summary->total_shipments }} Unit</span>
                </td>
                <td style="width: 25%;">
                    Breakdown Produk: <span class="kpi-badge">Fiber: {{ $summary->total_fiber }} &bull; Sodium: {{ $summary->total_sodium }}</span>
                </td>
                <td style="width: 25%;">
                    Jenis Pengiriman: <span class="kpi-badge">Export: {{ $summary->total_export }} &bull; Lokal: {{ $summary->total_lokal }}</span>
                </td>
                <td style="width: 25%;">
                    Kepatuhan SOP Foto: <span class="kpi-badge">{{ $summary->avg_compliance }}%</span> ({{ $summary->total_lengkap }}/{{ $summary->total_shipments }} Lengkap 27 Titik)
                </td>
            </tr>
            <tr>
                <td>
                    Status Dokumen: <span class="kpi-badge">Submitted: {{ $summary->total_submitted }} &bull; Draft: {{ $summary->total_draft }}</span>
                </td>
                <td>
                    Validasi OCR Kontainer: <span class="kpi-badge">{{ $summary->ocr_valid }} Unit Cocok</span>
                </td>
                <td>
                    Petugas Bertugas: <span class="kpi-badge">{{ $summary->unique_petugas_count }} Orang Karyawan</span>
                </td>
                <td>
                    Audit Mutu Staging: <span class="kpi-badge">{{ $summary->total_kurang === 0 ? 'MEMENUHI SYARAT (100%)' : 'PERHATIAN (' . $summary->total_kurang . ' Kurang Foto)' }}</span>
                </td>
            </tr>
        </table>

        <!-- Tabel Rekapitulasi Detail Staging -->
        <div class="section-title">I. REKAPITULASI PENGIRIMAN & EVIDENCE STAGING (DAFTAR ARMADA)</div>
        <table class="rekap-table">
            <thead>
                <tr>
                    <th style="width: 22px;">No</th>
                    <th style="width: 65px;">Tanggal</th>
                    <th style="width: 45px;">Shift/Wkt</th>
                    <th style="width: 85px;">No. Packing List</th>
                    <th style="width: 80px;">No. Container</th>
                    <th style="width: 60px;">Plat Truk</th>
                    <th style="width: 50px;">Produk</th>
                    <th style="width: 50px;">Tipe</th>
                    <th>Nama Sopir</th>
                    <th>Forwarding Agent</th>
                    <th>Petugas Staging</th>
                    <th style="width: 65px;">Foto SOP</th>
                    <th style="width: 50px;">OCR</th>
                    <th style="width: 55px;">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($shipments as $index => $s)
                    <tr>
                        <td class="text-center font-bold">{{ $index + 1 }}</td>
                        <td class="text-center">{{ $s->tanggal_staging ? $s->tanggal_staging->format('d/m/Y') : ($s->created_at ? $s->created_at->format('d/m/Y') : '-') }}</td>
                        <td class="text-center">{{ ucfirst((string) $s->waktu) }}</td>
                        <td><strong>{{ $s->packing_list_no ?: '-' }}</strong></td>
                        <td>{{ $s->nomor_container_atau_plat ?: '-' }}</td>
                        <td class="text-center">{{ $s->plat_nomor ?: '-' }}</td>
                        <td class="text-center">
                            <span class="pill {{ $s->jenis_produk === 'fiber' ? 'pill-blue' : 'pill-purple' }}">
                                {{ strtoupper((string) $s->jenis_produk) }}
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="pill {{ $s->jenis_pengiriman === 'export' ? 'pill-purple' : 'pill-blue' }}">
                                {{ strtoupper((string) $s->jenis_pengiriman) }}
                            </span>
                        </td>
                        <td>{{ $s->nama_sopir ?: '-' }}</td>
                        <td>{{ $s->agen_forwarding ?: '-' }}</td>
                        <td>{{ $s->allKaryawans()->pluck('nama')->join(', ') ?: ($s->karyawan?->nama ?? '-') }}</td>
                        <td class="text-center">
                            <span class="pill {{ $s->is_complete ? 'pill-green' : 'pill-amber' }}">
                                {{ $s->points_count }}/27 ({{ $s->compliance_rate }}%)
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="pill {{ $s->ocr_match ? 'pill-green' : 'pill-amber' }}">
                                {{ $s->ocr_match ? 'Cocok' : ($s->ocr_checked ? 'Mismatch' : '-') }}
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="pill {{ $s->status === 'submitted' ? 'pill-green' : 'pill-amber' }}">
                                {{ strtoupper((string) $s->status) }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="14" class="text-center" style="padding: 15px; color: #94a3b8;">
                            Tidak ada data armada staging pada kriteria filter laporan yang dipilih.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Ringkasan Kepatuhan 27 Titik SOP -->
        <div class="section-title">II. RINGKASAN AUDIT KEPATUHAN 27 TITIK FOTO SOP EVIDENCE</div>
        <table class="rekap-table">
            <thead>
                <tr>
                    <th style="width: 25px;">#</th>
                    <th>Nama Titik Foto SOP</th>
                    <th style="width: 65px;">Tipe</th>
                    <th style="width: 75px;">Keterisian</th>
                    <th style="width: 60px;">Persentase</th>
                    <th style="width: 90px;">Status Kepatuhan</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sopAuditMatrix->take(10) as $audit)
                    <tr>
                        <td class="text-center font-bold">{{ $audit->urutan }}</td>
                        <td>{{ $audit->nama_titik }}</td>
                        <td class="text-center uppercase">{{ $audit->tipe_item }}</td>
                        <td class="text-center">{{ $audit->covered_count }} / {{ $summary->total_shipments }} Kontainer</td>
                        <td class="text-center font-bold">{{ $audit->percentage }}%</td>
                        <td class="text-center">
                            <span class="pill {{ $audit->percentage >= 95 ? 'pill-green' : ($audit->percentage >= 80 ? 'pill-amber' : 'pill-purple') }}">
                                {{ $audit->percentage >= 95 ? 'Sempurna' : ($audit->percentage >= 80 ? 'Baik' : 'Perlu Evaluasi') }}
                            </span>
                        </td>
                    </tr>
                @endforeach
                @if($sopAuditMatrix->count() > 10)
                    <tr>
                        <td colspan="6" class="text-center" style="font-size: 7.5px; color: #64748b; background: #f8fafc;">
                            <em>Menampilkan 10 dari total 27 titik SOP. Seluruh 27 titik dapat diakses pada Audit Matrix digital di sistem SPV-Track.</em>
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>

        <!-- Kolom Pengesahan & Tanda Tangan -->
        <table class="signature-table">
            <tr>
                <td>
                    <p class="sig-role">Disiapkan Oleh (Prepared by):</p>
                    <p class="sig-name">Supervisor Staging</p>
                    <p class="sig-title">Staging & Loading Warehouse</p>
                </td>
                <td>
                    <p class="sig-role">Diperiksa Oleh (Verified by):</p>
                    <p class="sig-name">Quality Control / Auditor</p>
                    <p class="sig-title">Warehouse Quality Assurance</p>
                </td>
                <td>
                    <p class="sig-role">Disetujui Oleh (Approved by):</p>
                    <p class="sig-name">Warehouse Manager</p>
                    <p class="sig-title">Logistics & Supply Chain</p>
                </td>
            </tr>
        </table>
    </div>

</body>
</html>
