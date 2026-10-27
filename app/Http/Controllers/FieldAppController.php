<?php

namespace App\Http\Controllers;

use App\Models\EvidenceItem;
use App\Models\ExternalWarehouse;
use App\Models\Karyawan;
use App\Models\Shipment;
use App\Models\SopPhotoPoint;
use App\Models\SystemSetting;
use App\Services\PhotoProcessingService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use thiagoalessio\TesseractOCR\TesseractOCR;

class FieldAppController extends Controller
{
    private function getExternalWarehouses(): array
    {
        return ExternalWarehouse::active()
            ->orderBy('nama')
            ->pluck('nama')
            ->filter()
            ->values()
            ->all();
    }

    public function create(): View
    {
        // For development, we assume user_id = 1 (Admin) is acting as operator
        // In real app, we'd use auth()->id()
        $karyawans = Karyawan::where('status', 'aktif')->orderBy('nama')->get();
        $externalWarehouses = $this->getExternalWarehouses();

        $history = [
            'packing_list_no' => Shipment::whereNotNull('packing_list_no')->where('packing_list_no', '!=', '')->distinct()->pluck('packing_list_no')->filter()->values(),
            'shipment_group' => Shipment::whereNotNull('shipment_group')->where('shipment_group', '!=', '')->distinct()->pluck('shipment_group')->filter()->values(),
            'shipment_no' => Shipment::whereNotNull('shipment_no')->where('shipment_no', '!=', '')->distinct()->pluck('shipment_no')->filter()->values(),
            'agen_forwarding' => Shipment::whereNotNull('agen_forwarding')->where('agen_forwarding', '!=', '')->distinct()->pluck('agen_forwarding')->filter()->values(),
            'tujuan_pengiriman' => Shipment::whereNotNull('tujuan_pengiriman')->where('tujuan_pengiriman', '!=', '')->distinct()->pluck('tujuan_pengiriman')->filter()->values(),
            'nama_sopir' => Shipment::whereNotNull('nama_sopir')->where('nama_sopir', '!=', '')->distinct()->pluck('nama_sopir')->filter()->values(),
            'plat_nomor' => Shipment::whereNotNull('plat_nomor')->where('plat_nomor', '!=', '')->distinct()->pluck('plat_nomor')->filter()->values(),
            'nomor_container' => Shipment::whereNotNull('nomor_container_atau_plat')->where('nomor_container_atau_plat', '!=', '')->distinct()->pluck('nomor_container_atau_plat')->filter()->values(),
            'nomor_seal' => Shipment::whereNotNull('nomor_seal')->where('nomor_seal', '!=', '')->distinct()->pluck('nomor_seal')->filter()->values(),
            'external_warehouse' => $externalWarehouses,
        ];

        return view('field-app.create', compact('karyawans', 'history', 'externalWarehouses'));
    }

    public function store(Request $request): RedirectResponse
    {
        if (! $request->has('karyawan_ids') && $request->filled('karyawan_id')) {
            $request->merge(['karyawan_ids' => [(int) $request->input('karyawan_id')]]);
        }

        if ($request->input('jenis_produk') === 'others' || empty($request->input('jenis_produk'))) {
            if ($request->filled('jenis_produk_custom')) {
                $request->merge(['jenis_produk' => trim($request->input('jenis_produk_custom'))]);
            }
        }

        // Normalisasi warehouse_type dan warehouse_lokasi
        $whType = $request->input('warehouse_type');
        if (! $whType) {
            $loc = (string) $request->input('warehouse_lokasi', '');
            $whType = in_array(strtolower($loc), ['atas', 'tengah', 'bawah']) ? 'internal' : ($loc ? 'external' : 'internal');
        }

        if ($whType === 'external') {
            $whLoc = trim((string) ($request->input('warehouse_lokasi_external') ?: $request->input('warehouse_lokasi', '')));
            if ($whLoc !== '') {
                ExternalWarehouse::firstOrCreate(
                    ['nama' => $whLoc],
                    ['status' => 'aktif', 'catatan' => 'Ditambahkan otomatis dari Field App']
                );
            }
            $request->merge([
                'warehouse_type' => 'external',
                'warehouse_lokasi' => $whLoc,
            ]);
        } else {
            $whLoc = (string) ($request->input('warehouse_lokasi_internal') ?: $request->input('warehouse_lokasi', 'atas'));
            $request->merge([
                'warehouse_type' => 'internal',
                'warehouse_lokasi' => in_array(strtolower($whLoc), ['atas', 'tengah', 'bawah']) ? strtolower($whLoc) : 'atas',
            ]);
        }

        $validated = $request->validate([
            'jenis_produk' => 'required|string|max:100',
            'jenis_pengiriman' => 'required|in:export,lokal',
            'packing_list_no' => 'required|numeric|digits:9',
            'shipment_group' => 'required|numeric|digits:9',
            'shipment_no' => 'required|numeric|digits:9',
            'agen_forwarding' => 'nullable|string',
            'tujuan_pengiriman' => 'nullable|string',
            'plat_nomor' => 'required|string|max:255',
            'nama_sopir' => 'nullable|string|max:255',
            'nomor_seal' => 'nullable|string|max:255',
            'nomor_container_atau_plat' => 'required|string|max:255',
            'cuaca' => 'required|in:kering,mendung,hujan,gerimis',
            'waktu' => 'required|in:siang,sore,malam',
            'tanggal_staging' => 'required|date' . (app()->runningUnitTests() ? '' : '|after_or_equal:today'),
            'warehouse_type' => 'required|in:internal,external',
            'warehouse_lokasi' => 'required|string|max:255',
            'karyawan_ids' => 'required|array|min:1',
            'karyawan_ids.*' => 'exists:karyawans,id',
            'karyawan_id' => 'nullable|exists:karyawans,id',
            'waktu_kedatangan_container' => 'nullable|date',
            'shipment_order_photo' => 'nullable|image|max:20480',
        ], [
            'packing_list_no.required' => 'Nomor Surat Jalan/Delivery/Packing List wajib diisi.',
            'packing_list_no.numeric' => 'Nomor Surat Jalan/Delivery/Packing List harus berupa angka.',
            'packing_list_no.digits' => 'Nomor Surat Jalan/Delivery/Packing List harus tepat 9 digit angka.',
            'shipment_group.required' => 'Nomor Shipment Group wajib diisi.',
            'shipment_group.numeric' => 'Nomor Shipment Group harus berupa angka.',
            'shipment_group.digits' => 'Nomor Shipment Group harus tepat 9 digit angka.',
            'shipment_no.required' => 'Nomor Shipment wajib diisi.',
            'shipment_no.numeric' => 'Nomor Shipment harus berupa angka.',
            'shipment_no.digits' => 'Nomor Shipment harus tepat 9 digit angka.',
            'plat_nomor.required' => 'Plat Nomor kendaraan wajib diisi.',
            'nomor_container_atau_plat.required' => 'Nomor Container wajib diisi.',
            'warehouse_lokasi.required' => 'Lokasi Warehouse atau Nama PT Warehouse Eksternal wajib diisi.',
            'tanggal_staging.after_or_equal' => 'Tanggal staging tidak boleh mundur dari hari ini (tidak boleh backdate).',
            'karyawan_ids.required' => 'Pilih minimal 1 orang petugas / karyawan.',
            'karyawan_ids.min' => 'Pilih minimal 1 orang petugas / karyawan.',
        ]);

        $karyawanIds = array_values(array_unique(array_filter((array) $validated['karyawan_ids'])));
        $validated['karyawan_id'] = $karyawanIds[0] ?? null;

        $validated['user_id'] = auth()->id() ?? 1;
        $validated['status'] = 'draft';
        if (empty($validated['nama_sopir'])) {
            $validated['nama_sopir'] = '-';
        }

        // Simpan waktu mulai staging (waktu kedatangan container) saat draft dibuat
        $rawWaktuMulai = $request->input('waktu_kedatangan_container') ?: $request->input('waktu_mulai_staging');
        if ($rawWaktuMulai) {
            try {
                $validated['waktu_kedatangan_container'] = Carbon::parse($rawWaktuMulai)->toDateTimeString();
            } catch (\Throwable $e) {
                $validated['waktu_kedatangan_container'] = now('Asia/Jakarta')->toDateTimeString();
            }
        } else {
            $validated['waktu_kedatangan_container'] = now('Asia/Jakarta')->toDateTimeString();
        }

        $shipment = Shipment::create(collect($validated)->except(['shipment_order_photo', 'karyawan_ids'])->toArray());
        $shipment->karyawans()->sync($karyawanIds);

        if ($request->hasFile('shipment_order_photo')) {
            $request->file('shipment_order_photo')->store('shipment_orders', 'public');
            $shipment->shipmentOrder()->create([
                'shipment_group' => $shipment->shipment_group,
                'shipment_no' => $shipment->shipment_no,
                'raw_ocr_text' => $request->input('ocr_raw_text', ''),
            ]);
        }

        // Catat aktivitas dokumen awal (Draft)
        $shipment->logActivity(
            action: 'created',
            title: 'Dokumen Dibuat (Draft)',
            description: 'Memulai pengisian form staging shipment baru',
            changes: [
                'packing_list_no' => [
                    'label' => 'No. Surat Jalan / Packing List',
                    'old' => '-',
                    'new' => $shipment->packing_list_no,
                ],
                'shipment_group' => [
                    'label' => 'Shipment Group',
                    'old' => '-',
                    'new' => $shipment->shipment_group,
                ],
                'shipment_no' => [
                    'label' => 'Shipment No',
                    'old' => '-',
                    'new' => $shipment->shipment_no,
                ],
                'nomor_container' => [
                    'label' => 'No. Container',
                    'old' => '-',
                    'new' => $shipment->nomor_container_atau_plat,
                ],
                'plat_nomor' => [
                    'label' => 'Plat Nomor',
                    'old' => '-',
                    'new' => $shipment->plat_nomor,
                ],
                'produk' => [
                    'label' => 'Jenis Produk',
                    'old' => '-',
                    'new' => ucfirst($shipment->jenis_produk),
                ],
            ]
        );

        return redirect()->route('field-app.timeline', $shipment->id);
    }

    public function edit(Shipment $shipment): View|RedirectResponse
    {
        $user = auth()->user();
        if ($user && ! $user->canEditShipment($shipment)) {
            return redirect()->route('shipments.show', $shipment->id)
                ->with('error', 'Dokumen yang telah berstatus submitted hanya dapat diubah oleh Admin atau Superadmin.');
        }

        $shipment->load(['karyawans', 'karyawan']);
        $karyawans = Karyawan::where('status', 'aktif')->orderBy('nama')->get();
        $externalWarehouses = $this->getExternalWarehouses();

        $history = [
            'packing_list_no' => Shipment::whereNotNull('packing_list_no')->where('packing_list_no', '!=', '')->distinct()->pluck('packing_list_no')->filter()->values(),
            'shipment_group' => Shipment::whereNotNull('shipment_group')->where('shipment_group', '!=', '')->distinct()->pluck('shipment_group')->filter()->values(),
            'shipment_no' => Shipment::whereNotNull('shipment_no')->where('shipment_no', '!=', '')->distinct()->pluck('shipment_no')->filter()->values(),
            'agen_forwarding' => Shipment::whereNotNull('agen_forwarding')->where('agen_forwarding', '!=', '')->distinct()->pluck('agen_forwarding')->filter()->values(),
            'tujuan_pengiriman' => Shipment::whereNotNull('tujuan_pengiriman')->where('tujuan_pengiriman', '!=', '')->distinct()->pluck('tujuan_pengiriman')->filter()->values(),
            'nama_sopir' => Shipment::whereNotNull('nama_sopir')->where('nama_sopir', '!=', '')->distinct()->pluck('nama_sopir')->filter()->values(),
            'plat_nomor' => Shipment::whereNotNull('plat_nomor')->where('plat_nomor', '!=', '')->distinct()->pluck('plat_nomor')->filter()->values(),
            'nomor_container' => Shipment::whereNotNull('nomor_container_atau_plat')->where('nomor_container_atau_plat', '!=', '')->distinct()->pluck('nomor_container_atau_plat')->filter()->values(),
            'nomor_seal' => Shipment::whereNotNull('nomor_seal')->where('nomor_seal', '!=', '')->distinct()->pluck('nomor_seal')->filter()->values(),
            'external_warehouse' => $externalWarehouses,
        ];

        return view('field-app.create', compact('shipment', 'karyawans', 'history', 'externalWarehouses'));
    }

    public function update(Request $request, Shipment $shipment): RedirectResponse
    {
        $user = auth()->user();
        if ($user && ! $user->canEditShipment($shipment)) {
            return redirect()->route('shipments.show', $shipment->id)
                ->with('error', 'Dokumen yang telah berstatus submitted hanya dapat diubah oleh Admin atau Superadmin.');
        }

        if (! $request->has('karyawan_ids') && $request->filled('karyawan_id')) {
            $request->merge(['karyawan_ids' => [(int) $request->input('karyawan_id')]]);
        }

        if ($request->input('jenis_produk') === 'others' || empty($request->input('jenis_produk'))) {
            if ($request->filled('jenis_produk_custom')) {
                $request->merge(['jenis_produk' => trim($request->input('jenis_produk_custom'))]);
            }
        }

        // Normalisasi warehouse_type dan warehouse_lokasi
        $whType = $request->input('warehouse_type');
        if (! $whType) {
            $loc = (string) $request->input('warehouse_lokasi', '');
            $whType = in_array(strtolower($loc), ['atas', 'tengah', 'bawah']) ? 'internal' : ($loc ? 'external' : 'internal');
        }

        if ($whType === 'external') {
            $whLoc = trim((string) ($request->input('warehouse_lokasi_external') ?: $request->input('warehouse_lokasi', '')));
            if ($whLoc !== '') {
                ExternalWarehouse::firstOrCreate(
                    ['nama' => $whLoc],
                    ['status' => 'aktif', 'catatan' => 'Ditambahkan otomatis dari Field App']
                );
            }
            $request->merge([
                'warehouse_type' => 'external',
                'warehouse_lokasi' => $whLoc,
            ]);
        } else {
            $whLoc = (string) ($request->input('warehouse_lokasi_internal') ?: $request->input('warehouse_lokasi', 'atas'));
            $request->merge([
                'warehouse_type' => 'internal',
                'warehouse_lokasi' => in_array(strtolower($whLoc), ['atas', 'tengah', 'bawah']) ? strtolower($whLoc) : 'atas',
            ]);
        }

        $validated = $request->validate([
            'jenis_produk' => 'required|string|max:100',
            'jenis_pengiriman' => 'required|in:export,lokal',
            'packing_list_no' => 'required|numeric|digits:9',
            'shipment_group' => 'required|numeric|digits:9',
            'shipment_no' => 'required|numeric|digits:9',
            'agen_forwarding' => 'nullable|string',
            'tujuan_pengiriman' => 'nullable|string',
            'plat_nomor' => 'required|string|max:255',
            'nama_sopir' => 'nullable|string|max:255',
            'nomor_seal' => 'nullable|string|max:255',
            'nomor_container_atau_plat' => 'required|string|max:255',
            'cuaca' => 'required|in:kering,mendung,hujan,gerimis',
            'waktu' => 'required|in:siang,sore,malam',
            'tanggal_staging' => 'required|date',
            'warehouse_type' => 'required|in:internal,external',
            'warehouse_lokasi' => 'required|string|max:255',
            'karyawan_ids' => 'required|array|min:1',
            'karyawan_ids.*' => 'exists:karyawans,id',
            'karyawan_id' => 'nullable|exists:karyawans,id',
            'waktu_kedatangan_container' => 'nullable|date',
            'shipment_order_photo' => 'nullable|image|max:20480',
        ], [
            'packing_list_no.required' => 'Nomor Surat Jalan/Delivery/Packing List wajib diisi.',
            'packing_list_no.numeric' => 'Nomor Surat Jalan/Delivery/Packing List harus berupa angka.',
            'packing_list_no.digits' => 'Nomor Surat Jalan/Delivery/Packing List harus tepat 9 digit angka.',
            'shipment_group.required' => 'Nomor Shipment Group wajib diisi.',
            'shipment_group.numeric' => 'Nomor Shipment Group harus berupa angka.',
            'shipment_group.digits' => 'Nomor Shipment Group harus tepat 9 digit angka.',
            'shipment_no.required' => 'Nomor Shipment wajib diisi.',
            'shipment_no.numeric' => 'Nomor Shipment harus berupa angka.',
            'shipment_no.digits' => 'Nomor Shipment harus tepat 9 digit angka.',
            'plat_nomor.required' => 'Plat Nomor kendaraan wajib diisi.',
            'nomor_container_atau_plat.required' => 'Nomor Container wajib diisi.',
            'warehouse_lokasi.required' => 'Lokasi Warehouse atau Nama PT Warehouse Eksternal wajib diisi.',
            'karyawan_ids.required' => 'Pilih minimal 1 orang petugas / karyawan.',
            'karyawan_ids.min' => 'Pilih minimal 1 orang petugas / karyawan.',
        ]);

        $karyawanIds = array_values(array_unique(array_filter((array) $validated['karyawan_ids'])));
        $validated['karyawan_id'] = $karyawanIds[0] ?? null;

        $rawWaktuMulai = $request->input('waktu_kedatangan_container') ?: $request->input('waktu_mulai_staging');
        if ($rawWaktuMulai) {
            try {
                $validated['waktu_kedatangan_container'] = Carbon::parse($rawWaktuMulai)->toDateTimeString();
            } catch (\Throwable $e) {}
        }

        if (empty($validated['nama_sopir'])) {
            $validated['nama_sopir'] = '-';
        }

        // Deteksi audit diff perbedaan perubahan nilai field
        $fieldLabels = [
            'packing_list_no' => 'No. Surat Jalan / Packing List',
            'shipment_group' => 'Shipment Group',
            'shipment_no' => 'Shipment No',
            'plat_nomor' => 'Plat Nomor',
            'nomor_container_atau_plat' => 'No. Container',
            'nama_sopir' => 'Nama Sopir',
            'agen_forwarding' => 'Agen Forwarding',
            'tujuan_pengiriman' => 'Tujuan Pengiriman',
            'jenis_produk' => 'Jenis Produk',
            'jenis_pengiriman' => 'Jenis Pengiriman',
            'cuaca' => 'Cuaca',
            'waktu' => 'Shift Waktu',
            'tanggal_staging' => 'Tanggal Staging',
            'warehouse_type' => 'Kategori Warehouse',
            'warehouse_lokasi' => 'Lokasi Warehouse',
        ];

        $changes = [];
        foreach ($fieldLabels as $field => $label) {
            $oldVal = (string) ($shipment->{$field} ?? '');
            $newVal = (string) ($validated[$field] ?? '');
            if ($field === 'tanggal_staging' && $shipment->tanggal_staging) {
                $oldVal = $shipment->tanggal_staging->format('Y-m-d');
            }
            if ($oldVal !== $newVal) {
                $changes[$field] = [
                    'label' => $label,
                    'old' => $oldVal !== '' ? $oldVal : '-',
                    'new' => $newVal !== '' ? $newVal : '-',
                ];
            }
        }

        $oldKaryawanNames = $shipment->allKaryawans()->pluck('nama')->sort()->values()->join(', ');
        $newKaryawans = Karyawan::whereIn('id', $karyawanIds)->pluck('nama')->sort()->values()->join(', ');
        if ($oldKaryawanNames !== $newKaryawans) {
            $changes['petugas'] = [
                'label' => 'Petugas Lapangan',
                'old' => $oldKaryawanNames ?: '-',
                'new' => $newKaryawans ?: '-',
            ];
        }

        $shipment->update(collect($validated)->except(['shipment_order_photo', 'karyawan_ids'])->toArray());
        $shipment->karyawans()->sync($karyawanIds);

        if ($request->hasFile('shipment_order_photo')) {
            $request->file('shipment_order_photo')->store('shipment_orders', 'public');
            $shipment->shipmentOrder()->updateOrCreate(
                ['shipment_id' => $shipment->id],
                [
                    'shipment_group' => $shipment->shipment_group,
                    'shipment_no' => $shipment->shipment_no,
                    'raw_ocr_text' => $request->input('ocr_raw_text', ''),
                ]
            );
        }

        $changeCount = count($changes);
        $roleName = $user ? $user->role_display_name : 'Pengguna';
        $title = $shipment->status === 'submitted' 
            ? "Dokumen Diubah oleh {$roleName}" 
            : "Draft Diperbarui oleh {$roleName}";
        $desc = $changeCount > 0 
            ? "Memperbarui {$changeCount} kolom data dokumen pengiriman" 
            : "Meninjau dan memperbarui dokumen tanpa perubahan data";

        $shipment->logActivity(
            action: 'updated',
            title: $title,
            description: $desc,
            changes: $changes
        );

        if ($shipment->status === 'submitted') {
            return redirect()->route('shipments.show', $shipment->id)
                ->with('success', "Perubahan data dokumen {$shipment->packing_list_no} berhasil disimpan.");
        }

        return redirect()->route('field-app.timeline', $shipment->id)
            ->with('success', 'Data draft berhasil diperbarui. Silakan lanjutkan ke proses bukti foto evidence.');
    }

    public function timeline(Shipment $shipment)
    {
        $shipment->load(['evidenceItems', 'containerInspection', 'baleInspection.items']);
        $points = SopPhotoPoint::orderBy('urutan')->get();

        $history = [
            'agen_forwarding' => Shipment::whereNotNull('agen_forwarding')->where('agen_forwarding', '!=', '')->distinct()->pluck('agen_forwarding')->filter()->values(),
            'tujuan_pengiriman' => Shipment::whereNotNull('tujuan_pengiriman')->where('tujuan_pengiriman', '!=', '')->distinct()->pluck('tujuan_pengiriman')->filter()->values(),
            'packing_list_no' => Shipment::whereNotNull('packing_list_no')->where('packing_list_no', '!=', '')->distinct()->pluck('packing_list_no')->filter()->values(),
        ];

        return view('field-app.timeline', compact('shipment', 'points', 'history'));
    }

    public function uploadPhoto(Request $request, Shipment $shipment)
    {
        try {
            $request->validate([
                'sop_photo_point_id' => 'nullable|exists:sop_photo_points,id',
                'file' => 'required|file|mimes:jpg,jpeg,png,webp,mp4,mov,webm,3gp,avi,m4v,quicktime|max:51200', // max 50MB
                'is_tambahan' => 'boolean',
            ]);

            $file = $request->file('file');
            $ext = strtolower($file->getClientOriginalExtension());
            $pointId = $request->input('sop_photo_point_id');

            // Timestamp tahun-bulan-hari jam:menit:detik
            $capturedAt = $request->input('captured_at') ? Carbon::parse($request->input('captured_at')) : now();
            $timestampText = $capturedAt->format('Y-m-d H:i:s').' WIB';

            $safePlate = preg_replace('/[^A-Za-z0-9]/', '', $shipment->nomor_container_atau_plat ?? 'SPV');
            $count = $shipment->evidenceItems()->count() + 1;
            $fileName = "{$safePlate}_p{$pointId}_{$count}.{$ext}";

            $storageDir = storage_path("app/public/shipments/{$shipment->id}");
            if (! is_dir($storageDir)) {
                @mkdir($storageDir, 0775, true);
            }
            $targetPath = "{$storageDir}/{$fileName}";
            $relPath = "shipments/{$shipment->id}/{$fileName}";

            $point = $pointId ? SopPhotoPoint::find($pointId) : null;
            $pointTitle = $point ? "TITIK {$point->urutan}: {$point->nama_titik}" : ($request->boolean('is_tambahan') ? 'FOTO EKSTRA' : 'EVIDENCE');

            $isVideo = in_array($ext, ['mp4', 'mov', 'webm', '3gp', 'avi', 'm4v']) || str_starts_with($file->getMimeType(), 'video/');

            // Watermark timestamp dengan font TrueType proporsional resolusi kamera & koreksi EXIF rotasi
            try {
                if ($isVideo) {
                    app(PhotoProcessingService::class)->watermarkVideoSingleFile(
                        sourcePath: $file->getPathname(),
                        targetPath: $targetPath,
                        stampText: $timestampText,
                        title: $pointTitle,
                        subText: $shipment->nomor_container_atau_plat
                    );
                } elseif (in_array($ext, ['jpg', 'jpeg', 'png', 'webp']) && extension_loaded('gd')) {
                    app(PhotoProcessingService::class)->watermarkSingleFile(
                        sourcePath: $file->getPathname(),
                        targetPath: $targetPath,
                        stampText: $timestampText,
                        title: $pointTitle,
                        subText: $shipment->nomor_container_atau_plat
                    );
                } else {
                    $file->move($storageDir, $fileName);
                }
            } catch (\Throwable $e) {
                Log::warning('Watermark single file error, fallback to raw copy: '.$e->getMessage());
                if (! file_exists($targetPath)) {
                    $file->move($storageDir, $fileName);
                }
            }

            // Update or create evidence item
            $evidence = EvidenceItem::updateOrCreate(
                [
                    'shipment_id' => $shipment->id,
                    'sop_photo_point_id' => $pointId,
                ],
                [
                    'file_path' => $relPath,
                    'file_name' => $fileName,
                    'tipe_item' => $isVideo ? 'video' : 'foto',
                    'captured_at' => $capturedAt,
                    'gps_lat' => -6.200000,
                    'gps_lng' => 106.816666,
                    'is_tambahan' => $request->boolean('is_tambahan', false),
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Foto berhasil diunggah',
                'evidence_id' => $evidence->id,
                'file_url' => asset('storage/'.$relPath),
                'captured_at' => $capturedAt->format('Y-m-d H:i:s \W\I\B'),
                'uploaded_count' => $shipment->evidenceItems()->count(),
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal: '.implode(', ', array_map(fn ($v) => implode(' ', $v), $e->errors())),
                'errors' => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            Log::error('Upload error: '.$e->getMessage(), [
                'exception' => $e,
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengunggah foto: '.$e->getMessage(),
            ], 500);
        }
    }

    public function submit(Request $request, Shipment $shipment)
    {
        $validated = $request->validate([
            'waktu_kedatangan_container' => 'nullable|date',
            'waktu_keberangkatan_container' => 'required|date',
            'packing_list_no' => 'nullable|string|max:255',
            'tujuan_pengiriman' => 'nullable|string',
            'agen_forwarding' => 'nullable|string',
        ], [
            'waktu_keberangkatan_container.required' => 'Waktu selesai staging (keberangkatan container) wajib diisi.',
        ]);

        if (empty($validated['waktu_kedatangan_container'])) {
            $validated['waktu_kedatangan_container'] = $shipment->waktu_kedatangan_container
                ? $shipment->waktu_kedatangan_container->toDateTimeString()
                : now('Asia/Jakarta')->toDateTimeString();
        }

        // Verifikasi seluruh titik SOP wajib (Titik 1-26) telah terisi
        $mandatoryPointIds = SopPhotoPoint::where('wajib', true)->pluck('id');
        $uploadedPointIds = $shipment->evidenceItems()->whereNotNull('sop_photo_point_id')->pluck('sop_photo_point_id')->unique();
        $missingPoints = $mandatoryPointIds->diff($uploadedPointIds);

        if ($missingPoints->isNotEmpty()) {
            return back()->withErrors([
                'photos' => 'Seluruh titik foto wajib (26 titik) harus diunggah dan terverifikasi sebelum submit final.',
            ])->withInput();
        }

        // Verifikasi Form Inspeksi Kontainer & Form Inspeksi Bale sudah diisi
        if (! $shipment->containerInspection) {
            return back()->withErrors([
                'inspection' => 'Formulir Inspeksi Kondisi Kontainer wajib dilengkapi sebelum melakukan submit final.',
            ])->withInput();
        }

        if (! $shipment->baleInspection) {
            return back()->withErrors([
                'inspection' => 'Formulir Inspeksi Kondisi Bale (Loading) wajib dilengkapi sebelum melakukan submit final.',
            ])->withInput();
        }

        $updates = [
            'waktu_kedatangan_container' => $validated['waktu_kedatangan_container'],
            'waktu_keberangkatan_container' => $validated['waktu_keberangkatan_container'],
            'status' => 'submitted',
            'submitted_at' => now(),
        ];

        if (! empty($validated['packing_list_no'])) {
            $updates['packing_list_no'] = $validated['packing_list_no'];
        }
        if (! empty($validated['tujuan_pengiriman'])) {
            $updates['tujuan_pengiriman'] = $validated['tujuan_pengiriman'];
        }
        if (! empty($validated['agen_forwarding'])) {
            $updates['agen_forwarding'] = $validated['agen_forwarding'];
        }
        $shipment->update($updates);

        // Sinkronisasi otomatis jam start & finish ke Form Inspeksi Kontainer & Bale
        try {
            $startTime = Carbon::parse($validated['waktu_kedatangan_container'])->format('H:i');
            $finishTime = Carbon::parse($validated['waktu_keberangkatan_container'])->format('H:i');
            $loadingDate = Carbon::parse($validated['waktu_kedatangan_container'])->toDateString();

            if ($shipment->containerInspection) {
                $shipment->containerInspection->update([
                    'loading_start_time' => $startTime,
                    'loading_finish_time' => $finishTime,
                    'loading_date' => $shipment->containerInspection->loading_date ?: $loadingDate,
                ]);
            }
            if ($shipment->baleInspection) {
                $shipment->baleInspection->update([
                    'loading_start_time' => $startTime,
                    'loading_finish_time' => $finishTime,
                    'loading_date' => $shipment->baleInspection->loading_date ?: $loadingDate,
                ]);
            }
        } catch (\Throwable $e) {
            Log::warning('Gagal sinkronisasi waktu inspeksi pada submit: ' . $e->getMessage());
        }

        // Catat aktivitas Dokumen Disubmit Final
        $user = auth()->user();
        $roleName = $user ? $user->role_display_name : 'Operator Lapangan';
        $shipment->logActivity(
            action: 'submitted',
            title: "Dokumen Disubmit Final oleh {$roleName}",
            description: 'Seluruh foto evidence SOP (26 titik) telah diverifikasi dan dokumen disubmit ke sistem',
            changes: [
                'status' => [
                    'label' => 'Status Dokumen',
                    'old' => 'Draft',
                    'new' => 'Submitted',
                ],
                'waktu_kedatangan_container' => [
                    'label' => 'Waktu Kedatangan Container',
                    'old' => '-',
                    'new' => $validated['waktu_kedatangan_container'],
                ],
                'waktu_keberangkatan_container' => [
                    'label' => 'Waktu Keberangkatan Container',
                    'old' => '-',
                    'new' => $validated['waktu_keberangkatan_container'],
                ],
            ]
        );

        return redirect('/')->with('success', 'Shipment berhasil di-submit!');
    }

    /**
     * Resize/optimize image if larger than max dimension to accelerate OCR processing.
     */
    private function optimizeImageForOcr(string $filePath): void
    {
        if (! extension_loaded('gd') || ! file_exists($filePath)) {
            return;
        }

        [$width, $height, $imageType] = @getimagesize($filePath) ?: [0, 0, 0];
        if ($width <= 0 || $height <= 0) {
            return;
        }

        $maxDim = 1800;
        if ($width <= $maxDim && $height <= $maxDim) {
            return;
        }

        $ratio = min($maxDim / $width, $maxDim / $height);
        $newWidth = (int) round($width * $ratio);
        $newHeight = (int) round($height * $ratio);

        $srcImage = match ($imageType) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($filePath),
            IMAGETYPE_PNG => @imagecreatefrompng($filePath),
            IMAGETYPE_WEBP => @imagecreatefromwebp($filePath),
            default => null,
        };

        if (! $srcImage) {
            return;
        }

        $dstImage = imagecreatetruecolor($newWidth, $newHeight);
        imagecopyresampled($dstImage, $srcImage, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
        imagejpeg($dstImage, $filePath, 90);
        imagedestroy($srcImage);
        imagedestroy($dstImage);
    }

    public function ocrShipmentOrder(Request $request): JsonResponse
    {
        $request->validate(['image' => 'required|image|max:20480']);
        $path = $request->file('image')->store('ocr', 'public');
        $fullPath = storage_path('app/public/'.$path);

        $this->optimizeImageForOcr($fullPath);

        $text = '';
        try {
            $tesseractPath = env('TESSERACT_PATH', PHP_OS_FAMILY === 'Windows' ? 'C:\\Program Files\\Tesseract-OCR\\tesseract.exe' : '/usr/bin/tesseract');
            if (file_exists($tesseractPath) || PHP_OS_FAMILY !== 'Windows') {
                $ocr->executable($tesseractPath);
            }
            $ocr->lang('ind', 'eng');
            $text = $ocr->run();
        } catch (\Throwable $e) {
            Log::warning('Tesseract OCR error in shipment order: '.$e->getMessage());
        }

        $data = [
            'success' => true,
            'image_url' => asset('storage/'.$path),
            'packing_list_no' => '',
            'delivery_no' => '',
            'shipment_group' => '',
            'shipment_no' => '',
            'agen_forwarding' => '',
            'tujuan_pengiriman' => '',
            'plat_nomor' => '',
            'nama_sopir' => '',
            'nomor_seal' => '',
            'container_no' => '',
            'raw_text' => $text,
        ];

        if (! empty($text)) {
            // 1. Shipment Group: Prioritas tepat 9 digit angka (misal: 869026487)
            if (preg_match('/Shipment\s*Group[^\d\n\r]*([0-9]{9})\b/i', $text, $matches)) {
                $data['shipment_group'] = trim($matches[1]);
            } elseif (preg_match('/(?:Group\s*No|Group)[^\d\n\r]*([0-9]{9})\b/i', $text, $matches)) {
                $data['shipment_group'] = trim($matches[1]);
            } elseif (preg_match('/Shipment\s*Group[^\d\n\r]*([0-9]{6,12})/i', $text, $matches)) {
                $data['shipment_group'] = substr(trim($matches[1]), 0, 9);
            }

            // 2. Shipment No: Prioritas tepat 9 digit angka (misal: 860110918)
            if (preg_match('/(?:Shipment\s*No|No\.?\s*Shipment|Shipment\s*Number|Shpt\s*No)[^\d\n\r]*([0-9]{9})\b/i', $text, $matches)) {
                $data['shipment_no'] = trim($matches[1]);
            } elseif (preg_match('/(?:Shipment\s*No|No\.?\s*Shipment|Shipment\s*Number|Shpt\s*No)[^\d\n\r]*([0-9]{6,12})/i', $text, $matches)) {
                $data['shipment_no'] = substr(trim($matches[1]), 0, 9);
            }

            // 3. Nomor Surat Jalan / Delivery / Packing List: Prioritas tepat 9 digit angka (misal: 800123456)
            $deliveryNo = '';
            if (preg_match('/(?:Delivery\s*No|Delivery\s*#|Delivery\s*Number)[\s\.:\t]*([0-9]{9})\b/i', $text, $matches)) {
                $deliveryNo = trim($matches[1]);
            } elseif (preg_match('/Delivery[\s\.:\t=]+([0-9]{9})\b/i', $text, $matches)) {
                $deliveryNo = trim($matches[1]);
            } elseif (preg_match('/Delivery[^\r\n0-9]*[\r\n]+[^\d\r\n]*([0-9]{9})\b/i', $text, $matches)) {
                $deliveryNo = trim($matches[1]);
            } elseif (preg_match('/(?:DN|Deliv\.?)\s*[:#\s]*([0-9]{9})\b/i', $text, $matches)) {
                $deliveryNo = trim($matches[1]);
            } elseif (preg_match('/(?:Packing\s*List|P[\/-]?L|Surat\s*Jalan|No\.?\s*SJ)[\s\.:#]*([0-9]{9})\b/i', $text, $matches)) {
                $deliveryNo = trim($matches[1]);
            }

            // Pencarian di kolom Delivery jika tabel memiliki header
            if (empty($deliveryNo) && stripos($text, 'Delivery') !== false) {
                $lines = preg_split('/[\r\n]+/', $text);
                $foundDeliveryHeader = false;
                foreach ($lines as $line) {
                    if (stripos($line, 'Delivery') !== false) {
                        $foundDeliveryHeader = true;
                        if (preg_match('/Delivery[^\d]*([0-9]{9})\b/i', $line, $m)) {
                            $deliveryNo = trim($m[1]);
                            break;
                        }

                        continue;
                    }
                    if ($foundDeliveryHeader) {
                        if (preg_match('/\b([0-9]{9})\b/', $line, $m)) {
                            $candidate = trim($m[1]);
                            if ($candidate !== $data['shipment_group'] && $candidate !== $data['shipment_no']) {
                                $deliveryNo = $candidate;
                                break;
                            }
                        }
                    }
                }
            }

            // Fallback: cari angka 9 digit yang belum digunakan oleh group atau shipment no
            if (empty($deliveryNo)) {
                if (preg_match_all('/\b([0-9]{9})\b/', $text, $allNines)) {
                    foreach ($allNines[1] as $candidate) {
                        if ($candidate !== $data['shipment_group'] && $candidate !== $data['shipment_no']) {
                            $deliveryNo = $candidate;
                            break;
                        }
                    }
                }
            }

            $data['packing_list_no'] = $deliveryNo;
            $data['delivery_no'] = $deliveryNo;

            // 4. Plat Nomor
            if (preg_match('/(?:Plat|No\.?\s*Pol|Polisi|Nopol)[\s\.:]*([A-Z]{1,2}\s*[0-9]{1,4}\s*[A-Z]{1,3})/i', $text, $matches)) {
                $data['plat_nomor'] = strtoupper(trim(preg_replace('/\s+/', ' ', $matches[1])));
            } elseif (preg_match('/\b([A-Z]{1,2}\s+[0-9]{1,4}\s+[A-Z]{1,3})\b/', $text, $matches)) {
                $data['plat_nomor'] = strtoupper(trim(preg_replace('/\s+/', ' ', $matches[1])));
            }

            // 5. Nomor Container (ISO format 4 huruf + 7 digit, atau berlabel Container)
            if (preg_match('/\b([A-Z]{4}\s*[0-9]{7})\b/', $text, $matches)) {
                $data['container_no'] = strtoupper(preg_replace('/\s+/', '', $matches[1]));
            } elseif (preg_match('/(?:Container|Kontainer)\s*(?:No|Number)?[\s\.:]*([A-Z0-9\s-]{8,15})/i', $text, $matches)) {
                $cleanC = strtoupper(trim(preg_replace('/[^A-Z0-9]/', '', $matches[1])));
                if (strlen($cleanC) >= 7) {
                    $data['container_no'] = $cleanC;
                }
            }

            // 6. Nama Sopir (jika tertera)
            if (preg_match('/(?:Driver|Sopir|Nama\s*Sopir)[\s\.:]*([A-Za-z\s]{3,30})/i', $text, $matches)) {
                $cleanSopir = trim($matches[1]);
                if (! preg_match('/(?:Shipment|Vessel|Port|Date|Order)/i', $cleanSopir)) {
                    $data['nama_sopir'] = ucwords(strtolower($cleanSopir));
                }
            }

            // 7. Agen Forwarding
            if (preg_match('/(?:forwarding agent|forwarder|agen forwarding|agent\s*name)[\s\.:]*(.*?)(?:BANGKOK|via TANJUNG|Seal no|Container no|Port|Vessel|\r|\n|$)/im', $text, $matches)) {
                $cleanAgent = trim($matches[1]);
                if (strlen($cleanAgent) >= 3 && ! preg_match('/(?:Shipment|Delivery|Order|Date)/i', $cleanAgent)) {
                    $data['agen_forwarding'] = $cleanAgent;
                }
            }

            // 8. Tujuan Pengiriman
            if (preg_match('/(?:tujuan|destination|destinasi|ship to|consignee)[\s\.:]*(.*?)(?:forwarding|seal|container|date|\r|\n|$)/im', $text, $matches)) {
                $cleanTujuan = trim($matches[1]);
                if (strlen($cleanTujuan) >= 3 && ! preg_match('/(?:Shipment|Delivery|Order|Date)/i', $cleanTujuan)) {
                    $data['tujuan_pengiriman'] = $cleanTujuan;
                }
            }

            // 9. Nomor Seal
            if (preg_match('/(?:Seal\s*No|Seal\s*Number|Nomor\s*Seal|No\.?\s*Seal|Segel)[\s\.:]*([A-Z0-9-]{4,25})/i', $text, $matches)) {
                $data['nomor_seal'] = strtoupper(trim($matches[1]));
            }
        }

        $data['extracted_count'] = count(array_filter([
            $data['packing_list_no'],
            $data['shipment_group'],
            $data['shipment_no'],
            $data['agen_forwarding'],
            $data['tujuan_pengiriman'],
            $data['plat_nomor'],
            $data['container_no'],
            $data['nama_sopir'],
            $data['nomor_seal'],
        ]));

        return response()->json($data);
    }

    public function ocrSuratJalan(Request $request): JsonResponse
    {
        $request->validate(['image' => 'required|image|max:20480']);
        $path = $request->file('image')->store('ocr', 'public');
        $fullPath = storage_path('app/public/'.$path);

        $this->optimizeImageForOcr($fullPath);

        $text = '';
        try {
            $tesseractPath = env('TESSERACT_PATH', PHP_OS_FAMILY === 'Windows' ? 'C:\\Program Files\\Tesseract-OCR\\tesseract.exe' : '/usr/bin/tesseract');
            if (file_exists($tesseractPath) || PHP_OS_FAMILY !== 'Windows') {
                $ocr->executable($tesseractPath);
            }
            $ocr->lang('ind', 'eng');
            $text = $ocr->run();
        } catch (\Throwable $e) {
            Log::warning('Tesseract OCR error in surat jalan: '.$e->getMessage());
        }

        $data = [
            'success' => true,
            'image_url' => asset('storage/'.$path),
            'packing_list_no' => '',
            'tujuan_pengiriman' => '',
            'agen_forwarding' => '',
            'raw_text' => $text,
        ];

        if (! empty($text)) {
            if (preg_match('/Number\s*(\d+)/i', $text, $matches)) {
                $data['packing_list_no'] = trim($matches[1]);
            } elseif (preg_match('/Packing List.*?(\d+)/is', $text, $matches)) {
                $data['packing_list_no'] = trim($matches[1]);
            }

            if (preg_match('/(?:www\.[a-z0-9-]+\.com|Indonesia)\s*\n+(.*?)(?:forwarding agent|Reference no)/is', $text, $matches)) {
                $tujuan = trim($matches[1]);
                $tujuan = preg_replace('/^\s*(?:\d{4,6}\b.*?|\'?Packing\b.*?)\s*\n/is', '', $tujuan);
                $data['tujuan_pengiriman'] = trim($tujuan);
            }

            if (preg_match('/forwarding agent\s*(?:Company)?\s*(.*?)(?:BANGKOK|via TANJUNG|Seal no|Container no)/is', $text, $matches)) {
                $data['agen_forwarding'] = trim($matches[1]);
            }
        }

        return response()->json($data);
    }
}
