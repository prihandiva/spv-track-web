<?php

namespace App\Http\Controllers;

use App\Models\BaleInspection;
use App\Models\BaleInspectionItem;
use App\Models\ContainerInspection;
use App\Models\Shipment;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InspectionController extends Controller
{
    /**
     * Tampilkan form inspeksi kondisi kontainer
     */
    public function editContainer(Shipment $shipment): View
    {
        $inspection = $shipment->containerInspection ?: new ContainerInspection([
            'loading_date' => $shipment->tanggal_staging ?: now(),
            'loading_start_time' => $shipment->waktu_kedatangan_container ? $shipment->waktu_kedatangan_container->format('H:i') : null,
            'loading_finish_time' => $shipment->waktu_keberangkatan_container ? $shipment->waktu_keberangkatan_container->format('H:i') : null,
            'nomor_kontainer' => $shipment->nomor_container_atau_plat,
            'nomor_seal' => $shipment->nomor_seal,
            'nama_emkl_pelayaran' => $shipment->agen_forwarding,
            'nama_petugas_inspeksi' => $shipment->allKaryawans()->pluck('nama')->first() ?: ($shipment->karyawan?->nama ?? auth()->user()?->name),
            'nama_inspektor' => $shipment->allKaryawans()->pluck('nama')->first() ?: ($shipment->karyawan?->nama ?? auth()->user()?->name),
            'tanggal_inspeksi' => now(),
            'pintu_status' => true,
            'dinding_kanan_status' => true,
            'dinding_kiri_status' => true,
            'dinding_depan_status' => true,
            'atap_status' => true,
            'lantai_status' => true,
            'kesimpulan' => 'layak',
            'catatan' => 'Kontainer dalam keadaan bersih, kering, tidak berbau tajam, bebas benda tajam/paku, dan layak untuk stuffing.',
        ]);

        return view('inspections.container', compact('shipment', 'inspection'));
    }

    /**
     * Simpan / Perbarui form inspeksi kondisi kontainer
     */
    public function saveContainer(Request $request, Shipment $shipment): RedirectResponse
    {
        $validated = $request->validate([
            'loading_date' => 'nullable|date',
            'loading_start_time' => 'nullable|string|max:10',
            'loading_finish_time' => 'nullable|string|max:10',
            'nomor_kontainer' => 'required|string|max:100',
            'nomor_seal' => 'nullable|string|max:100',
            'nama_emkl_pelayaran' => 'nullable|string|max:255',
            'nama_petugas_inspeksi' => 'required|string|max:255',
            'pintu_status' => 'required|boolean',
            'pintu_catatan' => 'required|string|max:500',
            'dinding_kanan_status' => 'required|boolean',
            'dinding_kanan_catatan' => 'required|string|max:500',
            'dinding_kiri_status' => 'required|boolean',
            'dinding_kiri_catatan' => 'required|string|max:500',
            'dinding_depan_status' => 'required|boolean',
            'dinding_depan_catatan' => 'required|string|max:500',
            'atap_status' => 'required|boolean',
            'atap_catatan' => 'required|string|max:500',
            'lantai_status' => 'required|boolean',
            'lantai_catatan' => 'required|string|max:500',
            'kesimpulan' => 'required|in:layak,tidak_layak,layak_dengan_catatan',
            'catatan' => 'required|string|max:1000',
            'nama_inspektor' => 'required|string|max:255',
            'tanggal_inspeksi' => 'required|date',
        ], [
            'pintu_catatan.required' => 'Catatan pemeriksaan Pintu (Bagian Luar & Dalam) wajib diisi.',
            'dinding_kanan_catatan.required' => 'Catatan pemeriksaan Dinding Kanan wajib diisi.',
            'dinding_kiri_catatan.required' => 'Catatan pemeriksaan Dinding Kiri wajib diisi.',
            'dinding_depan_catatan.required' => 'Catatan pemeriksaan Dinding Depan wajib diisi.',
            'atap_catatan.required' => 'Catatan pemeriksaan Atap wajib diisi.',
            'lantai_catatan.required' => 'Catatan pemeriksaan Lantai wajib diisi.',
            'catatan.required' => 'Catatan tambahan kondisi kontainer wajib diisi (meskipun tidak ada kendala).',
        ]);

        // Fallback otomatis jam & tanggal dari shipment jika tidak diisi manual
        if (empty($validated['loading_start_time'])) {
            $validated['loading_start_time'] = $shipment->waktu_kedatangan_container ? $shipment->waktu_kedatangan_container->format('H:i') : ($shipment->containerInspection?->loading_start_time);
        }
        if (empty($validated['loading_finish_time'])) {
            $validated['loading_finish_time'] = $shipment->waktu_keberangkatan_container ? $shipment->waktu_keberangkatan_container->format('H:i') : ($shipment->containerInspection?->loading_finish_time);
        }
        if (empty($validated['loading_date'])) {
            $validated['loading_date'] = $shipment->tanggal_staging ? $shipment->tanggal_staging->toDateString() : now()->toDateString();
        }

        $inspection = $shipment->containerInspection()->updateOrCreate(
            ['shipment_id' => $shipment->id],
            $validated
        );

        // Sinkronisasi data dasar shipment jika diisi di form kontainer
        $shipmentUpdates = [];
        if (! empty($validated['nomor_kontainer']) && $shipment->nomor_container_atau_plat !== $validated['nomor_kontainer']) {
            $shipmentUpdates['nomor_container_atau_plat'] = $validated['nomor_kontainer'];
        }
        if (! empty($validated['nomor_seal']) && $shipment->nomor_seal !== $validated['nomor_seal']) {
            $shipmentUpdates['nomor_seal'] = $validated['nomor_seal'];
        }
        if (! empty($validated['nama_emkl_pelayaran']) && empty($shipment->agen_forwarding)) {
            $shipmentUpdates['agen_forwarding'] = $validated['nama_emkl_pelayaran'];
        }
        if (! empty($shipmentUpdates)) {
            $shipment->update($shipmentUpdates);
        }

        // Catat aktivitas di shipment
        $shipment->logActivity(
            action: 'updated',
            title: 'Form Inspeksi Kontainer Disimpan',
            description: "Pemeriksaan 6 titik kontainer disimpan dengan kesimpulan: {$inspection->kesimpulan_label}",
            changes: [
                'kesimpulan' => [
                    'label' => 'Kesimpulan Kontainer',
                    'old' => '-',
                    'new' => $inspection->kesimpulan_label,
                ],
            ]
        );

        $redirectRoute = $request->input('next') === 'bale'
            ? route('field-app.bale-inspection', $shipment->id)
            : route('field-app.timeline', $shipment->id);

        return redirect($redirectRoute)->with('success', 'Formulir Inspeksi Kondisi Kontainer berhasil disimpan.');
    }

    /**
     * Tampilkan form inspeksi kondisi bale / sodium
     */
    public function editBale(Shipment $shipment): View
    {
        $inspection = $shipment->baleInspection ?: new BaleInspection([
            'loading_date' => $shipment->tanggal_staging ?: now(),
            'loading_start_time' => $shipment->waktu_kedatangan_container ? $shipment->waktu_kedatangan_container->format('H:i') : null,
            'loading_finish_time' => $shipment->waktu_keberangkatan_container ? $shipment->waktu_keberangkatan_container->format('H:i') : null,
            'nama_petugas_loading' => $shipment->allKaryawans()->pluck('nama')->first() ?: ($shipment->karyawan?->nama ?? auth()->user()?->name),
            'nomor_surat_jalan' => $shipment->packing_list_no,
            'nama_pelanggan' => $shipment->tujuan_pengiriman,
            'nomor_kontainer_truk' => $shipment->nomor_container_atau_plat,
            'jenis_bale' => ['tx_ecovero'],
            'jenis_label' => ['tanpa_label'],
            'first_eye_nama' => $shipment->allKaryawans()->pluck('nama')->first() ?: ($shipment->karyawan?->nama ?? auth()->user()?->name),
            'first_eye_jabatan' => 'Operator Staging / Loading',
            'first_eye_tanggal' => now(),
            'second_eye_status' => 'disetujui',
            'second_eye_nama' => $shipment->allKaryawans()->count() > 1 ? $shipment->allKaryawans()[1]->nama : 'Supervisor Warehouse',
            'second_eye_jabatan' => 'Verifikator / Supervisor',
            'second_eye_tanggal' => now(),
            'sumber_bale' => 'inspeksi_join_checking',
            'stagging_2_rubber_cone' => true,
            'lokasi_stagging' => 'Area Staging Warehouse ' . ucfirst($shipment->warehouse_lokasi ?? 'Atas'),
            'cuaca' => $shipment->cuaca ?: 'kering',
            'waktu' => $shipment->waktu ?: 'siang',
            'kesimpulan_akhir' => 'layak',
        ]);

        $defaultChecklist = BaleInspectionItem::defaultChecklist();
        $items = $inspection->exists ? $inspection->items->keyBy('item_index') : collect();

        return view('inspections.bale', compact('shipment', 'inspection', 'defaultChecklist', 'items'));
    }

    /**
     * Simpan / Perbarui form inspeksi kondisi bale / sodium
     */
    public function saveBale(Request $request, Shipment $shipment): RedirectResponse
    {
        $validated = $request->validate([
            'loading_date' => 'nullable|date',
            'loading_start_time' => 'nullable|string|max:10',
            'loading_finish_time' => 'nullable|string|max:10',
            'nama_petugas_loading' => 'nullable|string|max:255',
            'nomor_surat_jalan' => 'required|string|max:100',
            'nama_pelanggan' => 'nullable|string|max:255',
            'jumlah_bale' => 'nullable|integer|min:0',
            'nomor_kontainer_truk' => 'nullable|string|max:100',
            'jenis_bale' => 'nullable|array',
            'jenis_label' => 'nullable|array',
            'first_eye_nama' => 'required|string|max:255',
            'first_eye_jabatan' => 'nullable|string|max:255',
            'first_eye_tanggal' => 'required|date',
            'second_eye_status' => 'nullable|in:disetujui,perlu_perbaikan,tidak_disetujui',
            'second_eye_tindakan' => 'nullable|string',
            'second_eye_bale_awal' => 'nullable|string|max:255',
            'second_eye_bale_pengganti' => 'nullable|string|max:255',
            'second_eye_catatan' => 'nullable|string',
            'second_eye_nama' => 'nullable|string|max:255',
            'second_eye_jabatan' => 'nullable|string|max:255',
            'second_eye_tanggal' => 'nullable|date',
            'sumber_bale' => 'nullable|string',
            'sumber_bale_lainnya' => 'nullable|string',
            'stagging_2_rubber_cone' => 'required|boolean',
            'lokasi_stagging' => 'nullable|string|max:255',
            'stagging_catatan' => 'nullable|string',
            'cuaca' => 'nullable|in:kering,mendung,gerimis,hujan',
            'waktu' => 'nullable|in:siang,sore,malam',
            'kesimpulan_akhir' => 'required|in:layak,layak_dengan_catatan,tidak_layak',
            'items' => 'required|array',
            'items.*.ada' => 'required|boolean',
            'items.*.jumlah' => 'nullable|integer|min:0',
            'items.*.tindakan' => 'nullable|string',
            'items.*.tindakan_lainnya' => 'nullable|string',
            'items.*.bale_awal' => 'nullable|string',
            'items.*.bale_pengganti' => 'nullable|string',
        ]);

        $baleData = collect($validated)->except(['items'])->toArray();

        // Fallback otomatis jam & tanggal dari shipment jika tidak diisi manual
        if (empty($baleData['loading_start_time'])) {
            $baleData['loading_start_time'] = $shipment->waktu_kedatangan_container ? $shipment->waktu_kedatangan_container->format('H:i') : ($shipment->baleInspection?->loading_start_time);
        }
        if (empty($baleData['loading_finish_time'])) {
            $baleData['loading_finish_time'] = $shipment->waktu_keberangkatan_container ? $shipment->waktu_keberangkatan_container->format('H:i') : ($shipment->baleInspection?->loading_finish_time);
        }
        if (empty($baleData['loading_date'])) {
            $baleData['loading_date'] = $shipment->tanggal_staging ? $shipment->tanggal_staging->toDateString() : now()->toDateString();
        }

        $inspection = $shipment->baleInspection()->updateOrCreate(
            ['shipment_id' => $shipment->id],
            $baleData
        );

        $defaultNames = BaleInspectionItem::defaultChecklist();
        foreach ($validated['items'] as $index => $itemData) {
            $inspection->items()->updateOrCreate(
                ['item_index' => $index],
                [
                    'jenis_ketidaksesuaian' => $defaultNames[$index] ?? ('Kriteria ' . $index),
                    'ada_ketidaksesuaian' => (bool) $itemData['ada'],
                    'jumlah_bale' => $itemData['ada'] ? ($itemData['jumlah'] ?? null) : null,
                    'tindakan_perbaikan' => $itemData['ada'] ? ($itemData['tindakan'] ?? null) : null,
                    'tindakan_lainnya' => $itemData['ada'] ? ($itemData['tindakan_lainnya'] ?? null) : null,
                    'nomor_bale_awal' => $itemData['ada'] ? ($itemData['bale_awal'] ?? null) : null,
                    'nomor_bale_pengganti' => $itemData['ada'] ? ($itemData['bale_pengganti'] ?? null) : null,
                ]
            );
        }

        // Catat aktivitas di shipment
        $shipment->logActivity(
            action: 'updated',
            title: 'Form Inspeksi Bale Disimpan',
            description: "Pemeriksaan kondisi bale dan 4-Eyes principle disimpan dengan kesimpulan: {$inspection->kesimpulan_label}",
            changes: [
                'kesimpulan_bale' => [
                    'label' => 'Kesimpulan Kondisi Bale',
                    'old' => '-',
                    'new' => $inspection->kesimpulan_label,
                ],
            ]
        );

        return redirect()->route('field-app.timeline', $shipment->id)
            ->with('success', 'Formulir Inspeksi Kondisi Bale (Loading) berhasil disimpan.');
    }

    /**
     * Cetak mandiri Form Inspeksi Kontainer (HTML Print View)
     */
    public function printContainer(Shipment $shipment): View
    {
        $inspection = $shipment->containerInspection;
        return view('inspections.print-container', compact('shipment', 'inspection') + ['isPdf' => false]);
    }

    /**
     * Download mandiri Form Inspeksi Kontainer (.PDF)
     */
    public function downloadContainerPdf(Shipment $shipment)
    {
        $inspection = $shipment->containerInspection;
        $pdfName = 'Form_Inspeksi_Kontainer_' . ($shipment->packing_list_no ?: $shipment->nomor_container_atau_plat) . '.pdf';

        $pdf = Pdf::loadView('inspections.print-container', compact('shipment', 'inspection') + ['isPdf' => true])
            ->setPaper('a4', 'portrait')
            ->setOption([
                'isRemoteEnabled' => true,
                'isHtml5ParserEnabled' => true,
                'defaultFont' => 'DejaVu Sans',
            ]);

        return $pdf->download($pdfName);
    }

    /**
     * Cetak mandiri Form Inspeksi Bale (HTML Print View)
     */
    public function printBale(Shipment $shipment): View
    {
        $inspection = $shipment->baleInspection;
        return view('inspections.print-bale', compact('shipment', 'inspection') + ['isPdf' => false]);
    }

    /**
     * Download mandiri Form Inspeksi Bale (.PDF)
     */
    public function downloadBalePdf(Shipment $shipment)
    {
        $inspection = $shipment->baleInspection;
        $pdfName = 'Form_Inspeksi_Bale_' . ($shipment->packing_list_no ?: $shipment->nomor_container_atau_plat) . '.pdf';

        $pdf = Pdf::loadView('inspections.print-bale', compact('shipment', 'inspection') + ['isPdf' => true])
            ->setPaper('a4', 'portrait')
            ->setOption([
                'isRemoteEnabled' => true,
                'isHtml5ParserEnabled' => true,
                'defaultFont' => 'DejaVu Sans',
            ]);

        return $pdf->download($pdfName);
    }
}
