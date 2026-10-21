<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Inspeksi Kondisi Kontainer - {{ $shipment->packing_list_no ?: $shipment->nomor_container_atau_plat }}</title>
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
            font-family: Arial, Helvetica, sans-serif;
            font-size: 8.5pt;
            color: #111827;
            background: #ffffff;
            margin: 0;
            padding: 0;
            line-height: 1.25;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
        }
        .header-table td {
            vertical-align: top;
        }
        .loading-box {
            border: 1px solid #000;
            width: 170px;
            font-size: 7.5pt;
        }
        .loading-box th {
            background-color: #f3f4f6;
            border-bottom: 1px solid #000;
            padding: 2px 4px;
            text-align: center;
            font-weight: bold;
            letter-spacing: 0.5px;
        }
        .loading-box td {
            padding: 1.5px 4px;
            border-bottom: 1px solid #e5e7eb;
        }
        .loading-box tr:last-child td {
            border-bottom: none;
        }
        .brand-logo {
            text-align: right;
        }
        .lenzing-title {
            font-size: 18pt;
            font-weight: 900;
            color: #00994d;
            letter-spacing: -0.5px;
            line-height: 1;
        }
        .lenzing-sub {
            font-size: 7.5pt;
            color: #4b5563;
            letter-spacing: 0.5px;
            margin-top: 1px;
        }
        .form-title {
            text-align: center;
            font-size: 11pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 4px 0 6px 0;
            padding: 3px 0;
            border-top: 1.5px solid #000;
            border-bottom: 1.5px solid #000;
        }
        .meta-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
            font-size: 8pt;
        }
        .meta-table td {
            padding: 2.5px 5px;
            border: 1px solid #000;
        }
        .meta-table .label {
            font-weight: bold;
            background-color: #f9fafb;
            width: 25%;
        }
        .meta-table .val {
            font-family: monospace;
            font-weight: bold;
            width: 25%;
        }
        .warning-box {
            background-color: #fff;
            border: 1.5px solid #000;
            text-align: center;
            padding: 4px;
            margin-bottom: 6px;
        }
        .warning-box .headline {
            font-size: 8pt;
            font-weight: 900;
            color: #dc2626;
            letter-spacing: 0.5px;
        }
        .warning-box .details {
            font-size: 7.5pt;
            font-weight: bold;
            color: #111827;
            margin-top: 1px;
        }
        .inspection-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
            font-size: 8pt;
        }
        .inspection-table th {
            border: 1px solid #000;
            background-color: #f3f4f6;
            padding: 3px 4px;
            font-weight: bold;
            text-align: center;
        }
        .inspection-table td {
            border: 1px solid #000;
            padding: 3px 4px;
            vertical-align: top;
        }
        .pt-title {
            font-weight: bold;
            color: #000;
            font-size: 8pt;
        }
        .pt-desc {
            font-size: 7.5pt;
            color: #374151;
            margin-top: 1px;
            line-height: 1.2;
        }
        .result-cell {
            text-align: center;
            vertical-align: middle;
            font-weight: bold;
            font-size: 9pt;
            width: 75px;
        }
        .check-box {
            display: inline-block;
            width: 11px;
            height: 11px;
            border: 1px solid #000;
            margin-right: 3px;
            vertical-align: middle;
            line-height: 10px;
            text-align: center;
            font-size: 8pt;
            font-weight: bold;
        }
        .conclusion-box {
            border: 1px solid #000;
            padding: 5px 7px;
            margin-bottom: 6px;
            font-size: 8pt;
        }
        .conclusion-status {
            font-weight: bold;
            margin-bottom: 3px;
        }
        .status-badge {
            display: inline-block;
            padding: 1px 6px;
            font-weight: 900;
            border: 1px solid #000;
            border-radius: 3px;
        }
        .sign-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
            font-size: 8pt;
        }
        .sign-table td {
            border: 1px solid #000;
            padding: 3px 6px;
            vertical-align: bottom;
        }
        @media screen {
            body { background: #f3f4f6; padding: 20px; }
            .sheet {
                background: #fff;
                max-width: 210mm;
                margin: 0 auto;
                padding: 12mm 15mm;
                box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            }
            .action-bar {
                max-width: 210mm;
                margin: 0 auto 12px auto;
                display: flex;
                justify-content: space-between;
                align-items: center;
            }
            .btn {
                padding: 6px 14px;
                font-size: 11px;
                font-weight: bold;
                border-radius: 6px;
                cursor: pointer;
                text-decoration: none;
                border: 1px solid #cbd5e1;
                background: #fff;
                color: #374151;
            }
            .btn-primary { background: #00994d; color: #fff; border-color: #00994d; }
        }
        @media print {
            .action-bar { display: none !important; }
            .sheet { padding: 0 !important; box-shadow: none !important; }
        }
    </style>
</head>
<body>

    @if(!($isPdf ?? false))
    <div class="action-bar">
        <a href="{{ route('shipments.show', $shipment->id) }}" class="btn">&larr; Kembali</a>
        <div style="display: flex; gap: 8px;">
            <button onclick="window.print()" class="btn">&#128438; Print Form</button>
            <a href="{{ route('shipments.container-inspection.pdf', $shipment->id) }}" class="btn btn-primary">&#128196; Download PDF</a>
        </div>
    </div>
    @endif

    <div class="sheet">
        <!-- Top Header -->
        <table class="header-table">
            <tr>
                <td style="width: 50%;">
                    <table class="loading-box">
                        <tr>
                            <th colspan="2">LOADING</th>
                        </tr>
                        <tr>
                            <td style="width: 45px; font-weight: bold;">Date</td>
                            <td>: {{ $shipment->tanggal_staging ? $shipment->tanggal_staging->format('d/m/Y') : ($inspection?->loading_date ? $inspection->loading_date->format('d/m/Y') : now()->format('d/m/Y')) }}</td>
                        </tr>
                        <tr>
                            <td colspan="2" style="font-weight: bold; background: #fafafa; padding-left: 10px;">Time</td>
                        </tr>
                        <tr>
                            <td style="padding-left: 10px;">Start</td>
                            <td>: {{ $shipment->waktu_kedatangan_container ? $shipment->waktu_kedatangan_container->format('H:i') : ($inspection?->loading_start_time ?: '-') }}</td>
                        </tr>
                        <tr>
                            <td style="padding-left: 10px;">Finish</td>
                            <td>: {{ $shipment->waktu_keberangkatan_container ? $shipment->waktu_keberangkatan_container->format('H:i') : ($inspection?->loading_finish_time ?: '-') }}</td>
                        </tr>
                        <tr>
                            <td style="font-weight: bold;">Name</td>
                            <td>: {{ $inspection?->nama_petugas_inspeksi ?: ($shipment->allKaryawans()->pluck('nama')->first() ?: ($shipment->karyawan?->nama ?? '-')) }}</td>
                        </tr>
                    </table>
                </td>
                <td class="brand-logo" style="width: 50%;">
                    <div class="lenzing-title">Lenzing</div>
                    <div class="lenzing-sub">Innovative by nature</div>
                </td>
            </tr>
        </table>

        <!-- Title -->
        <div class="form-title">FORM INSPEKSI KONDISI KONTAINER</div>

        <!-- Meta Table -->
        <table class="meta-table">
            <tr>
                <td class="label">NOMOR KONTAINER</td>
                <td class="val">{{ $inspection?->nomor_kontainer ?: ($shipment->nomor_container_atau_plat ?? '-') }}</td>
                <td class="label">NAMA EMKL & PELAYARAN</td>
                <td class="val" style="font-family: inherit;">{{ $inspection?->nama_emkl_pelayaran ?: ($shipment->agen_forwarding ?? '-') }}</td>
            </tr>
            <tr>
                <td class="label">NOMOR SEAL</td>
                <td class="val">{{ $inspection?->nomor_seal ?: ($shipment->nomor_seal ?? '-') }}</td>
                <td class="label">NAMA PETUGAS INSPEKSI</td>
                <td class="val" style="font-family: inherit;">{{ $inspection?->nama_petugas_inspeksi ?: ($shipment->allKaryawans()->pluck('nama')->first() ?: ($shipment->karyawan?->nama ?? '-')) }}</td>
            </tr>
        </table>

        <!-- Strict Warning Headline -->
        <div class="warning-box">
            <div class="headline">PASTIKAN TIDAK:</div>
            <div class="details">
                BERLUBANG / RETAK, BERKARAT, BERMINYAK / KOTOR, BERBAU / WANGI, ADA BAGIAN YANG TAJAM / PAKU / LANTAI LAPUK
            </div>
        </div>

        <!-- 6 Titik Inspeksi Table -->
        <table class="inspection-table">
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
                            'desc' => "<strong>Pintu bagian luar:</strong> Pastikan tidak bolong / retak.<br><strong>Pintu bagian dalam:</strong> Pastikan tidak bolong, karat, kotor / cat tidak luntur, berdebu, berminyak / ber-oli, bau / wangi menyengat. Lakukan tes usap pada pintu bagian dalam & pastikan bersih.",
                            'status' => $inspection?->pintu_status ?? true,
                            'note' => $inspection?->pintu_catatan,
                        ],
                        [
                            'num' => '2.',
                            'name' => 'DINDING KANAN KONTAINER (LUAR & DALAM)',
                            'desc' => "<strong>Sisi kanan luar:</strong> Pastikan tidak bolong / retak.<br><strong>Sisi kanan dalam:</strong> Pastikan tidak bolong, karat, kotor / cat tidak luntur, berdebu, berminyak / ber-oli, bau / wangi menyengat. Lakukan tes usap pada dinding dalam & pastikan bersih.",
                            'status' => $inspection?->dinding_kanan_status ?? true,
                            'note' => $inspection?->dinding_kanan_catatan,
                        ],
                        [
                            'num' => '3.',
                            'name' => 'DINDING KIRI KONTAINER (LUAR & DALAM)',
                            'desc' => "<strong>Sisi kiri luar:</strong> Pastikan tidak bolong atau retak.<br><strong>Sisi kiri dalam:</strong> Pastikan tidak bolong, karat, kotor / cat tidak luntur, berdebu, berminyak / ber-oli, bau / wangi menyengat. Lakukan tes usap pada dinding dalam & pastikan bersih.",
                            'status' => $inspection?->dinding_kiri_status ?? true,
                            'note' => $inspection?->dinding_kiri_catatan,
                        ],
                        [
                            'num' => '4.',
                            'name' => 'DINDING BAGIAN DEPAN (LUAR & DALAM)',
                            'desc' => "<strong>Dinding depan luar:</strong> Pastikan tidak bolong / retak.<br><strong>Dinding depan dalam:</strong> Pastikan tidak bolong, karat, kotor / cat tidak luntur, berdebu, berminyak / ber-oli, bau / wangi menyengat. Lakukan tes usap pada dinding dalam & pastikan bersih.",
                            'status' => $inspection?->dinding_depan_status ?? true,
                            'note' => $inspection?->dinding_depan_catatan,
                        ],
                        [
                            'num' => '5.',
                            'name' => 'ATAP KONTAINER',
                            'desc' => "Pastikan tidak bolong, retak, bocor, karat, kotor, berminyak / ber-oli, bau / wangi yang menyengat. Pastikan dalam keadaan bersih.",
                            'status' => $inspection?->atap_status ?? true,
                            'note' => $inspection?->atap_catatan,
                        ],
                        [
                            'num' => '6.',
                            'name' => 'LANTAI KONTAINER',
                            'desc' => "Pastikan lantai rata, bersih, kering dan bebas kotoran/debu, tidak berbau tajam, bebas noda oli/minyak. Pastikan tidak ada benda tajam (seperti paku) dan tidak lapuk.",
                            'status' => $inspection?->lantai_status ?? true,
                            'note' => $inspection?->lantai_catatan,
                        ],
                    ];
                @endphp

                @foreach($pts as $p)
                <tr>
                    <td>
                        <div class="pt-title">{{ $p['num'] }} {{ $p['name'] }}</div>
                        <div class="pt-desc">{!! $p['desc'] !!}</div>
                    </td>
                    <td class="result-cell">
                        <div style="margin-bottom: 3px;">
                            <span class="check-box">{{ $p['status'] ? '✓' : '' }}</span> Ya
                        </div>
                        <div>
                            <span class="check-box">{{ !$p['status'] ? '✓' : '' }}</span> Tidak
                        </div>
                    </td>
                    <td style="font-size: 7.5pt; color: {{ $p['status'] ? '#15803d' : '#dc2626' }}; {{ !$p['status'] ? 'font-weight: bold;' : '' }}">
                        {{ $p['note'] ?: ($p['status'] ? 'Kondisi baik & bersih' : '-') }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Kesimpulan -->
        @php
            $kes = $inspection?->kesimpulan ?? 'layak';
        @endphp
        <div class="conclusion-box">
            <div class="conclusion-status">
                <strong>KESIMPULAN:</strong><br>
                Saya telah memastikan bahwa kontainer tersebut dalam keadaan
                <span class="status-badge" style="{{ $kes === 'layak' ? 'background:#dcfce7; color:#15803d;' : '' }}">{{ $kes === 'layak' ? '[✓] ' : '' }}LAYAK</span> / 
                <span class="status-badge" style="{{ $kes === 'tidak_layak' ? 'background:#fee2e2; color:#b91c1c;' : '' }}">{{ $kes === 'tidak_layak' ? '[✓] ' : '' }}TIDAK LAYAK</span> / 
                <span class="status-badge" style="{{ $kes === 'layak_dengan_catatan' ? 'background:#fef3c7; color:#b45309;' : '' }}">{{ $kes === 'layak_dengan_catatan' ? '[✓] ' : '' }}LAYAK DENGAN CATATAN</span>** 
                untuk dilakukan proses loading/stuffing.
            </div>
            <div style="margin-top: 4px; font-size: 7.5pt;">
                <strong>CATATAN:</strong><br>
                <div style="border-bottom: 1px dotted #9ca3af; min-height: 14px; padding-top: 2px;">
                    {{ $inspection?->catatan ?: '-' }}
                </div>
            </div>
            <div style="font-size: 6.5pt; color: #6b7280; margin-top: 3px;">**Pilihan status kelayakan yang divalidasi inspektor</div>
        </div>

        <!-- Tanda Tangan & Tanggal Inspeksi -->
        <table class="sign-table">
            <tr>
                <td style="width: 50%; height: 50px;">
                    <div style="font-size: 7pt; color: #4b5563; margin-bottom: 25px;">Tanda Tangan & Nama Inspektor:</div>
                    <div style="font-weight: bold; border-top: 1px solid #000; display: inline-block; min-width: 150px; padding-top: 2px;">
                        ( {{ $inspection?->nama_inspektor ?: ($shipment->allKaryawans()->pluck('nama')->first() ?: ($shipment->karyawan?->nama ?? '..................................')) }} )
                    </div>
                </td>
                <td style="width: 50%;">
                    <div style="font-size: 7pt; color: #4b5563; margin-bottom: 25px;">Tanggal Inspeksi:</div>
                    <div style="font-weight: bold; border-top: 1px solid #000; display: inline-block; min-width: 150px; padding-top: 2px;">
                        {{ $inspection?->tanggal_inspeksi ? $inspection->tanggal_inspeksi->format('d F Y') : now()->format('d F Y') }}
                    </div>
                </td>
            </tr>
        </table>
    </div>

</body>
</html>
