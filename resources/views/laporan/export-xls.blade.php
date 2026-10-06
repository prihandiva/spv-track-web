<!DOCTYPE html>
<html xmlns:o="urn:schemas-microsoft-com:office:office"
      xmlns:x="urn:schemas-microsoft-com:office:excel"
      xmlns="http://www.w3.org/TR/REC-html40">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="ProgId" content="Excel.Sheet" />
    <meta name="Generator" content="Microsoft Excel" />
    <style>
        body { font-family: Calibri, Arial, sans-serif; font-size: 11pt; color: #1e293b; }
        .title { font-size: 16pt; font-weight: bold; color: #1a3c6e; }
        .subtitle { font-size: 11pt; color: #64748b; }
        .meta-table { margin-bottom: 20px; }
        .meta-table td { padding: 4px 8px; font-size: 10pt; }
        .data-table { border-collapse: collapse; width: 100%; }
        .data-table th { background-color: #1a3c6e; color: #ffffff; font-weight: bold; padding: 8px 10px; border: 1px solid #0f2347; text-align: center; }
        .data-table td { padding: 6px 10px; border: 1px solid #cbd5e1; font-size: 10pt; vertical-align: middle; }
        .zebra { background-color: #f8fafc; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .badge-green { background-color: #dcfce7; color: #15803d; font-weight: bold; text-align: center; }
        .badge-amber { background-color: #fef3c7; color: #b45309; font-weight: bold; text-align: center; }
        .summary-box { background-color: #f1f5f9; border: 1px solid #94a3b8; font-weight: bold; }
    </style>
</head>
<body>
    <table>
        <tr>
            <td colspan="19" class="title">PT SOUTH PACIFIC VISCOSE - REKAPITULASI STAGING & LOADING WAREHOUSE</td>
        </tr>
        <tr>
            <td colspan="19" class="subtitle">Sistem Monitoring Evidence Digital (SPV-Track) &bull; Periode: {{ $periodLabel }}</td>
        </tr>
        <tr>
            <td colspan="19" style="font-size: 9pt; color: #94a3b8;">Tanggal Ekspor: {{ now('Asia/Jakarta')->translatedFormat('d F Y, H:i:s') }} WIB</td>
        </tr>
    </table>

    <br/>

    <!-- Summary Box -->
    <table class="data-table" style="width: auto;">
        <tr class="summary-box">
            <td>Total Armada: {{ $summary->total_shipments }}</td>
            <td>Fiber: {{ $summary->total_fiber }}</td>
            <td>Sodium: {{ $summary->total_sodium }}</td>
            <td>Export: {{ $summary->total_export }}</td>
            <td>Lokal: {{ $summary->total_lokal }}</td>
            <td>Foto SOP Lengkap (27/27): {{ $summary->total_lengkap }} ({{ $summary->avg_compliance }}%)</td>
            <td>OCR Valid: {{ $summary->ocr_valid }}</td>
        </tr>
    </table>

    <br/>

    <!-- Data Table -->
    <table class="data-table">
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal Staging</th>
                <th>Waktu / Shift</th>
                <th>Lokasi Gudang</th>
                <th>Kondisi Cuaca</th>
                <th>No. Packing List</th>
                <th>No. Container</th>
                <th>Plat Nomor</th>
                <th>Shipment No</th>
                <th>Produk</th>
                <th>Tipe Pengiriman</th>
                <th>Nama Sopir</th>
                <th>Forwarding Agent</th>
                <th>Tujuan Pengiriman</th>
                <th>Petugas Staging</th>
                <th>Foto SOP (X/27)</th>
                <th>Kepatuhan (%)</th>
                <th>Status Validasi OCR</th>
                <th>Status Dokumen</th>
            </tr>
        </thead>
        <tbody>
            @forelse($shipments as $index => $s)
                <tr class="{{ $index % 2 === 1 ? 'zebra' : '' }}">
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-center">{{ $s->tanggal_staging ? $s->tanggal_staging->format('d/m/Y') : ($s->created_at ? $s->created_at->format('d/m/Y') : '-') }}</td>
                    <td class="text-center">{{ ucfirst((string) $s->waktu) }}</td>
                    <td class="text-center">{{ ucfirst((string) $s->warehouse_lokasi) }}</td>
                    <td class="text-center">{{ ucfirst((string) $s->cuaca) }}</td>
                    <td><strong>{{ $s->packing_list_no ?: '-' }}</strong></td>
                    <td>{{ $s->nomor_container_atau_plat ?: '-' }}</td>
                    <td class="text-center">{{ $s->plat_nomor ?: '-' }}</td>
                    <td class="text-center">{{ $s->shipment_no ?: '-' }}</td>
                    <td class="text-center">{{ strtoupper((string) $s->jenis_produk) }}</td>
                    <td class="text-center">{{ strtoupper((string) $s->jenis_pengiriman) }}</td>
                    <td>{{ $s->nama_sopir ?: '-' }}</td>
                    <td>{{ $s->agen_forwarding ?: '-' }}</td>
                    <td>{{ $s->tujuan_pengiriman ?: '-' }}</td>
                    <td>{{ $s->allKaryawans()->pluck('nama')->join(', ') ?: ($s->karyawan?->nama ?? '-') }}</td>
                    <td class="text-center {{ $s->is_complete ? 'badge-green' : 'badge-amber' }}">
                        {{ $s->points_count }} / 27
                    </td>
                    <td class="text-center {{ $s->is_complete ? 'badge-green' : 'badge-amber' }}">
                        {{ $s->compliance_rate }}%
                    </td>
                    <td class="text-center">{{ $s->ocr_label }}</td>
                    <td class="text-center {{ $s->status === 'submitted' ? 'badge-green' : 'badge-amber' }}">
                        {{ strtoupper((string) $s->status) }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="19" class="text-center" style="padding: 20px; color: #94a3b8;">
                        Tidak ada data staging pada periode filter yang dipilih.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
