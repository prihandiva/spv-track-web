<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Inspeksi Kondisi Bale (Loading) - {{ $shipment->packing_list_no ?: $shipment->nomor_container_atau_plat }}</title>
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
            font-size: 8pt;
            color: #111827;
            background: #ffffff;
            margin: 0;
            padding: 0;
            line-height: 1.2;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 4px;
        }
        .loading-box {
            border: 1px solid #000;
            width: 155px;
            font-size: 7pt;
        }
        .loading-box th {
            background-color: #f3f4f6;
            border-bottom: 1px solid #000;
            padding: 1px 3px;
            text-align: center;
            font-weight: bold;
        }
        .loading-box td {
            padding: 1px 3px;
            border-bottom: 1px solid #e5e7eb;
        }
        .loading-box tr:last-child td { border-bottom: none; }
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
        .form-title {
            text-align: center;
            font-size: 10pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 3px 0 5px 0;
            padding: 2px 0;
            border-top: 1.5px solid #000;
            border-bottom: 1.5px solid #000;
        }
        .sec-head {
            font-weight: bold;
            font-size: 8pt;
            margin: 4px 0 2px 0;
            text-transform: uppercase;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 4px;
            font-size: 7.5pt;
        }
        .info-table td {
            padding: 2px 4px;
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
        .table-bale {
            width: 100%;
            border-collapse: collapse;
            font-size: 7pt;
            margin-bottom: 4px;
        }
        .table-bale th {
            border: 1px solid #000;
            background: #f3f4f6;
            padding: 2px 3px;
            text-align: center;
            font-weight: bold;
        }
        .table-bale td {
            border: 1px solid #000;
            padding: 2px 3px;
            vertical-align: middle;
        }
        .rule-note {
            font-size: 7pt;
            font-weight: bold;
            color: #00994d;
            margin: 2px 0 4px 0;
        }
        .grid-2col {
            width: 100%;
            border-collapse: collapse;
            font-size: 7pt;
            margin-bottom: 4px;
        }
        .grid-2col td {
            vertical-align: top;
            border: 1px solid #000;
            padding: 3px 5px;
        }
        .sign-line {
            border-bottom: 1px solid #000;
            display: inline-block;
            min-width: 110px;
            margin-left: 3px;
            padding-bottom: 1px;
            font-weight: bold;
        }
        .conclusion-box {
            border: 1px solid #000;
            padding: 3px 5px;
            font-size: 7.5pt;
            margin-top: 3px;
        }
        @media screen {
            body { background: #f3f4f6; padding: 20px; }
            .sheet {
                background: #fff;
                max-width: 210mm;
                margin: 0 auto;
                padding: 10mm 12mm;
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
            <a href="{{ route('shipments.bale-inspection.pdf', $shipment->id) }}" class="btn btn-primary">&#128196; Download PDF</a>
        </div>
    </div>
    @endif

    <div class="sheet">
        <!-- Top Header -->
        <table class="header-table">
            <tr>
                <td style="width: 50%;">
                    <table class="loading-box">
                        <tr><th colspan="2">LOADING</th></tr>
                        <tr>
                            <td style="width: 35px; font-weight: bold;">Date</td>
                            <td>: {{ $shipment->tanggal_staging ? $shipment->tanggal_staging->format('d/m/Y') : ($inspection?->loading_date ? $inspection->loading_date->format('d/m/Y') : now()->format('d/m/Y')) }}</td>
                        </tr>
                        <tr><td colspan="2" style="font-weight: bold; background: #fafafa; padding-left: 6px;">Time</td></tr>
                        <tr>
                            <td style="padding-left: 6px;">Start</td>
                            <td>: {{ $shipment->waktu_kedatangan_container ? $shipment->waktu_kedatangan_container->format('H:i') : ($inspection?->loading_start_time ?: '-') }}</td>
                        </tr>
                        <tr>
                            <td style="padding-left: 6px;">Finish</td>
                            <td>: {{ $shipment->waktu_keberangkatan_container ? $shipment->waktu_keberangkatan_container->format('H:i') : ($inspection?->loading_finish_time ?: '-') }}</td>
                        </tr>
                        <tr>
                            <td style="font-weight: bold;">Name</td>
                            <td>: {{ $inspection?->nama_petugas_loading ?: ($shipment->allKaryawans()->pluck('nama')->first() ?: ($shipment->karyawan?->nama ?? '-')) }}</td>
                        </tr>
                    </table>
                </td>
                <td style="width: 50%; vertical-align: top;">
                    <div class="lenzing-title">Lenzing</div>
                    <div class="lenzing-sub">Innovative by nature</div>
                </td>
            </tr>
        </table>

        <!-- Title -->
        <div class="form-title">FORM INSPEKSI KONDISI BALE (LOADING)</div>

        <!-- Section A: Informasi Umum -->
        <div class="sec-head">A. Informasi Umum</div>
        @php
            $jBale = (array) ($inspection?->jenis_bale ?? ['tx_ecovero']);
            $jLabel = (array) ($inspection?->jenis_label ?? ['tanpa_label']);
        @endphp
        <table class="info-table">
            <tr>
                <td style="width: 50%;">
                    &bull; Nomor Surat Jalan : <strong>{{ $inspection?->nomor_surat_jalan ?: ($shipment->packing_list_no ?? '-') }}</strong><br>
                    &bull; Nama Pelanggan : <strong>{{ $inspection?->nama_pelanggan ?: ($shipment->tujuan_pengiriman ?? '-') }}</strong><br>
                    &bull; Jumlah Bale : <strong>{{ $inspection?->jumlah_bale ? $inspection->jumlah_bale . ' Bale' : '-' }}</strong>
                </td>
                <td style="width: 50%;">
                    &bull; Nomor Kontainer / Truk Lokal : <strong>{{ $inspection?->nomor_kontainer_truk ?: ($shipment->nomor_container_atau_plat ?: ($shipment->plat_nomor ?? '-')) }}</strong><br>
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

        <!-- Section B: Pemeriksaan Kondisi Bale – Pemeriksa Pertama -->
        <div class="sec-head">B. Pemeriksaan Kondisi Bale – Pemeriksa Pertama</div>
        <div style="font-size: 6.5pt; color: #4b5563; margin-bottom: 2px;">
            Pastikan bale TIDAK mengalami kondisi berikut (beri tanda & isi jumlah jika ada temuan)
        </div>

        <table class="table-bale">
            <thead>
                <tr>
                    <th style="width: 20px;">No</th>
                    <th style="width: 140px; text-align: left;">Jenis Ketidaksesuaian</th>
                    <th style="width: 25px;">Ada (✓)</th>
                    <th style="width: 25px;">Tidak (✓)</th>
                    <th style="width: 45px;">Jumlah Bale</th>
                    <th style="width: 160px; text-align: left;">Tindakan Perbaikan (✓)</th>
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
                    $itemsMap = $inspection ? $inspection->items->keyBy('item_index') : collect();
                @endphp

                @foreach($checklistMap as $idx => $label)
                @php
                    $it = $itemsMap->get($idx);
                    $ada = (bool) ($it?->ada_ketidaksesuaian ?? false);
                    $tind = $it?->tindakan_perbaikan;
                @endphp
                <tr>
                    <td style="text-align: center; font-weight: bold;">{{ $idx }}</td>
                    <td><strong>{{ $label }}</strong></td>
                    <td style="text-align: center;"><span class="check-box">{{ $ada ? '✓' : '' }}</span></td>
                    <td style="text-align: center;"><span class="check-box">{{ !$ada ? '✓' : '' }}</span></td>
                    <td style="text-align: center; font-weight: bold;">{{ $ada && $it?->jumlah_bale ? $it->jumlah_bale : '-' }}</td>
                    <td style="font-size: 6.5pt;">
                        <span class="check-box">{{ $ada && $tind === 'diganti' ? '✓' : '' }}</span> Diganti &nbsp;
                        <span class="check-box">{{ $ada && $tind === 'diperbaiki' ? '✓' : '' }}</span> Diperbaiki/ditambal/dibersihkan<br>
                        <span class="check-box">{{ $ada && $tind === 'ganti_label' ? '✓' : '' }}</span> Ganti label (EV / Non-EV) &nbsp;
                        <span class="check-box">{{ $ada && $tind === 'lainnya' ? '✓' : '' }}</span> Lainnya: {{ $ada && $tind === 'lainnya' ? ($it?->tindakan_lainnya ?: '-') : '' }}
                    </td>
                    <td style="text-align: center; font-family: monospace;">{{ $ada ? ($it?->nomor_bale_awal ?: '-') : '-' }}</td>
                    <td style="text-align: center; font-family: monospace;">{{ $ada ? ($it?->nomor_bale_pengganti ?: '-') : '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="rule-note">
            Bale harus dalam keadaan: <u>Bersih, Mulus dan Tidak Cacat</u>
        </div>

        <!-- Section C, D, E, F Grid -->
        <table class="grid-2col">
            <tr>
                <!-- Column Left: C. Verifikasi 4 Eyes Principle -->
                <td style="width: 50%;">
                    <div style="font-weight: bold; text-transform: uppercase; margin-bottom: 2px;">C. VERIFIKASI 4 EYES PRINCIPLE</div>
                    
                    <div style="font-weight: bold; margin-top: 2px;">1. Pemeriksa Pertama (First Eye)</div>
                    <div style="font-size: 6.5pt; color: #4b5563; font-style: italic;">
                        Saya menyatakan bahwa inspeksi kondisi bale telah dilakukan secara menyeluruh sesuai prosedur yang berlaku.
                    </div>
                    <div style="margin-top: 2px;">
                        &bull; Nama : <span class="sign-line">{{ $inspection?->first_eye_nama ?: ($shipment->allKaryawans()->pluck('nama')->first() ?: ($shipment->karyawan?->nama ?? '........................')) }}</span><br>
                        &bull; Jabatan / Unit : <span class="sign-line">{{ $inspection?->first_eye_jabatan ?: 'Operator Lapangan' }}</span><br>
                        &bull; Tanda Tangan & Tanggal : <span class="sign-line">{{ $inspection?->first_eye_tanggal ? $inspection->first_eye_tanggal->format('d/m/Y') : now()->format('d/m/Y') }}</span>
                    </div>

                    <div style="font-weight: bold; margin-top: 5px;">2. Pemeriksa Kedua (Second Eye / Verifikator)</div>
                    <div style="font-size: 6.5pt; color: #4b5563; font-style: italic;">
                        Saya telah melakukan verifikasi independen terhadap hasil inspeksi dan menyetujui / menolak hasil serta tindakan perbaikan.
                    </div>
                    @php
                        $secStat = $inspection?->second_eye_status ?? 'disetujui';
                    @endphp
                    <div style="margin-top: 2px;">
                        Status Verifikasi (✓):<br>
                        <span class="check-box">{{ $secStat === 'disetujui' ? '✓' : '' }}</span> <strong>Disetujui</strong> &nbsp;
                        <span class="check-box">{{ $secStat === 'perlu_perbaikan' ? '✓' : '' }}</span> <strong>Perlu Perbaikan Tambahan</strong> &nbsp;
                        <span class="check-box">{{ $secStat === 'tidak_disetujui' ? '✓' : '' }}</span> <strong>Tidak Disetujui</strong>
                    </div>
                    <div style="margin-top: 2px;">
                        &bull; Catatan Verifikator : <span class="sign-line">{{ $inspection?->second_eye_catatan ?: '-' }}</span><br>
                        &bull; Nama : <span class="sign-line">{{ $inspection?->second_eye_nama ?: ($shipment->allKaryawans()->count() > 1 ? $shipment->allKaryawans()[1]->nama : 'Supervisor Warehouse') }}</span><br>
                        &bull; Jabatan / Unit : <span class="sign-line">{{ $inspection?->second_eye_jabatan ?: 'Verifikator / SPV' }}</span><br>
                        &bull; Tanda Tangan & Tanggal : <span class="sign-line">{{ $inspection?->second_eye_tanggal ? $inspection->second_eye_tanggal->format('d/m/Y') : now()->format('d/m/Y') }}</span>
                    </div>
                </td>

                <!-- Column Right: D, E, F -->
                <td style="width: 50%;">
                    <!-- D. Sumber Bale -->
                    <div style="font-weight: bold; text-transform: uppercase;">D. Sumber Bale (✓)</div>
                    @php $sb = $inspection?->sumber_bale ?? 'inspeksi_join_checking'; @endphp
                    <div style="margin-top: 1px;">
                        <span class="check-box">{{ $sb === 'inspeksi_join_checking' ? '✓' : '' }}</span> Bale hasil inspeksi & join checking (FGW-TCS – QPD – PDP)<br>
                        <span class="check-box">{{ $sb === 'produksi_baru' ? '✓' : '' }}</span> Bale produksi baru<br>
                        <span class="check-box">{{ $sb === 'lainnya' ? '✓' : '' }}</span> Lainnya: {{ $sb === 'lainnya' ? ($inspection?->sumber_bale_lainnya ?: '-') : '' }}
                    </div>

                    <!-- E. Lokasi Stagging -->
                    <div style="font-weight: bold; text-transform: uppercase; margin-top: 4px;">E. Lokasi Stagging (✓)</div>
                    <div>
                        Pastikan Bale dikumpulkan dalam satu lokasi stagging dengan 2 Rubber Cone:<br>
                        <span class="check-box">{{ ($inspection?->stagging_2_rubber_cone ?? true) ? '✓' : '' }}</span> Ya &nbsp;&nbsp;
                        <span class="check-box">{{ !($inspection?->stagging_2_rubber_cone ?? true) ? '✓' : '' }}</span> Tidak<br>
                        Lokasi Stagging : <span class="sign-line">{{ $inspection?->lokasi_stagging ?: $shipment->warehouse_label }}</span>
                    </div>

                    <!-- F. Kondisi Loading -->
                    <div style="font-weight: bold; text-transform: uppercase; margin-top: 4px;">F. Kondisi Proses Pemuatan / Loading (✓)</div>
                    @php
                        $cuaca = $inspection?->cuaca ?: ($shipment->cuaca ?: 'kering');
                        $waktu = $inspection?->waktu ?: ($shipment->waktu ?: 'siang');
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
                    <div class="conclusion-box">
                        <div style="font-weight: bold; text-transform: uppercase; margin-bottom: 2px;">Kesimpulan Akhir</div>
                        <div style="font-size: 6.5pt; color: #374151; margin-bottom: 3px;">
                            Berdasarkan hasil inspeksi dan verifikasi 4 eyes, seluruh bale yang tercantum dalam surat jalan dinyatakan (✓):
                        </div>
                        @php $fin = $inspection?->kesimpulan_akhir ?? 'layak'; @endphp
                        <div>
                            <span class="check-box">{{ $fin === 'layak' ? '✓' : '' }}</span> <strong>Layak untuk proses loading / stuffing</strong><br>
                            <span class="check-box">{{ $fin === 'layak_dengan_catatan' ? '✓' : '' }}</span> <strong>Layak dengan catatan (lihat bagian C.2)</strong><br>
                            <span class="check-box">{{ $fin === 'tidak_layak' ? '✓' : '' }}</span> <strong>Tidak layak (ditunda / diganti)</strong>
                        </div>
                    </div>
                </td>
            </tr>
        </table>
    </div>

</body>
</html>
