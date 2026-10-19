<?php

namespace App\Http\Controllers;

use App\Models\EvidenceItem;
use App\Models\Karyawan;
use App\Models\Shipment;
use App\Models\SopPhotoPoint;
use App\Services\PhotoProcessingService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use thiagoalessio\TesseractOCR\TesseractOCR;

class MobileApiController extends Controller
{
    /**
     * Dashboard KPI & statistik operasional untuk aplikasi mobile.
     */
    public function dashboard(): JsonResponse
    {
        $totalShipments = Shipment::count();
        $totalSubmitted = Shipment::where('status', 'submitted')->count();
        $totalDraft = Shipment::where('status', 'draft')->count();
        $totalPetugas = Karyawan::where('status', 'aktif')->count();
        $successRate = $totalShipments > 0 ? round(($totalSubmitted / $totalShipments) * 100, 1) : 0;

        $todayDate = Carbon::today('Asia/Jakarta');
        $todayShipments = Shipment::whereDate('tanggal_staging', $todayDate)
            ->orWhere(function ($q) use ($todayDate) {
                $q->whereNull('tanggal_staging')->whereDate('created_at', $todayDate);
            })->count();

        $todaySubmitted = Shipment::where('status', 'submitted')
            ->where(function ($q) use ($todayDate) {
                $q->whereDate('tanggal_staging', $todayDate)
                    ->orWhere(function ($sq) use ($todayDate) {
                        $sq->whereNull('tanggal_staging')->whereDate('created_at', $todayDate);
                    });
            })->count();

        // 5 Shipment Terbaru
        $recentShipments = Shipment::with(['karyawan', 'evidenceItems'])
            ->latest()
            ->take(6)
            ->get()
            ->map(function ($s) {
                return [
                    'id' => $s->id,
                    'packing_list_no' => $s->packing_list_no,
                    'nomor_container_atau_plat' => $s->nomor_container_atau_plat,
                    'plat_nomor' => $s->plat_nomor,
                    'nama_sopir' => $s->nama_sopir,
                    'nomor_seal' => $s->nomor_seal,
                    'shipment_no' => $s->shipment_no,
                    'shipment_group' => $s->shipment_group,
                    'jenis_produk' => $s->jenis_produk,
                    'jenis_pengiriman' => $s->jenis_pengiriman,
                    'status' => $s->status,
                    'cuaca' => $s->cuaca,
                    'waktu' => $s->waktu,
                    'warehouse_lokasi' => $s->warehouse_lokasi,
                    'agen_forwarding' => $s->agen_forwarding,
                    'tujuan_pengiriman' => $s->tujuan_pengiriman,
                    'tanggal_staging' => $s->tanggal_staging ? Carbon::parse($s->tanggal_staging)->format('Y-m-d') : null,
                    'petugas_nama' => $s->karyawan?->nama ?? 'Petugas Lapangan',
                    'evidence_count' => $s->evidenceItems->count(),
                    'created_at' => $s->created_at->format('d M Y H:i'),
                ];
            });

        // Produk & Jenis
        $fiberCount = Shipment::where('jenis_produk', 'fiber')->count();
        $sodiumCount = Shipment::where('jenis_produk', 'sodium')->count();
        $exportCount = Shipment::where('jenis_pengiriman', 'export')->count();
        $lokalCount = Shipment::where('jenis_pengiriman', 'lokal')->count();

        return response()->json([
            'success' => true,
            'data' => [
                'total_shipments' => $totalShipments,
                'total_submitted' => $totalSubmitted,
                'total_draft' => $totalDraft,
                'total_petugas' => $totalPetugas,
                'success_rate' => $successRate,
                'today_shipments' => $todayShipments,
                'today_submitted' => $todaySubmitted,
                'jenis_produk' => [
                    'fiber' => $fiberCount,
                    'sodium' => $sodiumCount,
                ],
                'jenis_pengiriman' => [
                    'export' => $exportCount,
                    'lokal' => $lokalCount,
                ],
                'recent_shipments' => $recentShipments,
            ],
        ]);
    }

    /**
     * Daftar seluruh Shipment dengan pencarian & filter.
     */
    public function shipments(Request $request): JsonResponse
    {
        $query = Shipment::with(['karyawan', 'karyawans', 'evidenceItems'])->latest();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('packing_list_no', 'like', "%{$search}%")
                    ->orWhere('nomor_container_atau_plat', 'like', "%{$search}%")
                    ->orWhere('plat_nomor', 'like', "%{$search}%")
                    ->orWhere('shipment_no', 'like', "%{$search}%")
                    ->orWhere('shipment_group', 'like', "%{$search}%")
                    ->orWhere('nama_sopir', 'like', "%{$search}%")
                    ->orWhere('agen_forwarding', 'like', "%{$search}%")
                    ->orWhere('tujuan_pengiriman', 'like', "%{$search}%");
            });
        }

        if ($type = $request->input('type')) {
            if ($type !== 'semua') {
                $query->where('jenis_pengiriman', $type);
            }
        }

        if ($status = $request->input('status')) {
            if ($status !== 'semua') {
                $query->where('status', $status);
            }
        }

        $shipments = $query->paginate($request->input('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $shipments->items(),
            'current_page' => $shipments->currentPage(),
            'last_page' => $shipments->lastPage(),
            'total' => $shipments->total(),
        ]);
    }

    /**
     * Detail Shipment dan seluruh item evidence.
     */
    public function shipmentDetail(Shipment $shipment): JsonResponse
    {
        $shipment->load(['karyawan', 'karyawans', 'evidenceItems.sopPhotoPoint', 'photos']);

        $evidenceList = $shipment->evidenceItems->map(function ($ev) {
            return [
                'id' => $ev->id,
                'sop_photo_point_id' => $ev->sop_photo_point_id,
                'point_urutan' => $ev->sopPhotoPoint?->urutan,
                'point_nama' => $ev->sopPhotoPoint?->nama_titik ?? 'Foto Ekstra',
                'file_name' => $ev->file_name,
                'file_url' => asset('storage/'.$ev->file_path),
                'api_file_url' => url('api/evidence/'.$ev->id.'/file'),
                'tipe_item' => $ev->tipe_item,
                'captured_at' => $ev->captured_at ? $ev->captured_at->format('Y-m-d H:i:s \W\I\B') : null,
                'is_tambahan' => $ev->is_tambahan,
            ];
        });


        return response()->json([
            'success' => true,
            'data' => [
                'shipment' => $shipment,
                'evidence_items' => $evidenceList,
            ],
        ]);
    }

    /**
     * Buat data Shipment baru (Draft) dari Field App.
     */
    public function storeShipment(Request $request): JsonResponse
    {
        if (! $request->has('karyawan_ids') && $request->filled('karyawan_id')) {
            $request->merge(['karyawan_ids' => [(int) $request->input('karyawan_id')]]);
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
            'warehouse_lokasi' => 'required|in:atas,tengah,bawah',
            'karyawan_ids' => 'required|array|min:1',
        ]);

        $karyawanIds = array_values(array_unique(array_filter((array) $validated['karyawan_ids'])));
        $validated['karyawan_id'] = $karyawanIds[0] ?? null;
        $validated['user_id'] = 1;
        $validated['status'] = 'draft';
        if (empty($validated['nama_sopir'])) {
            $validated['nama_sopir'] = '-';
        }

        $shipment = Shipment::create(collect($validated)->except(['karyawan_ids'])->toArray());
        $shipment->karyawans()->sync($karyawanIds);

        return response()->json([
            'success' => true,
            'message' => 'Shipment draft berhasil dibuat',
            'data' => $shipment->load('karyawans'),
        ], 201);
    }

    /**
     * Upload evidence foto/video untuk SOP point.
     */
    public function uploadEvidence(Request $request, Shipment $shipment): JsonResponse
    {
        $request->validate([
            'sop_photo_point_id' => 'nullable|exists:sop_photo_points,id',
            'file' => 'required|file|max:51200', // 50MB
            'is_tambahan' => 'nullable|boolean',
            'captured_at' => 'nullable|string',
        ]);

        $file = $request->file('file');
        $ext = strtolower($file->getClientOriginalExtension());
        $pointId = $request->input('sop_photo_point_id');

        $capturedAt = $request->input('captured_at') ? Carbon::parse($request->input('captured_at')) : now();
        $timestampText = $capturedAt->format('Y-m-d H:i:s').' WIB';

        $safePlate = preg_replace('/[^A-Za-z0-9]/', '', $shipment->nomor_container_atau_plat ?? 'SPV');
        $count = $shipment->evidenceItems()->count() + 1;
        $fileName = "{$safePlate}_p{$pointId}_{$count}.{$ext}";

        $storageDir = storage_path("app/public/shipments/{$shipment->id}");
        if (! is_dir($storageDir)) {
            mkdir($storageDir, 0755, true);
        }
        $targetPath = "{$storageDir}/{$fileName}";
        $relPath = "shipments/{$shipment->id}/{$fileName}";

        $point = $pointId ? SopPhotoPoint::find($pointId) : null;
        $pointTitle = $point ? "TITIK {$point->urutan}: {$point->nama_titik}" : ($request->boolean('is_tambahan') ? 'FOTO EKSTRA' : 'EVIDENCE');

        $isVideo = in_array($ext, ['mp4', 'mov', 'webm', '3gp', 'avi', 'm4v']) || str_starts_with($file->getMimeType(), 'video/');

        if ($isVideo) {
            app(PhotoProcessingService::class)->watermarkVideoSingleFile(
                sourcePath: $file->getPathname(),
                targetPath: $targetPath,
                stampText: $timestampText,
                title: $pointTitle,
                subText: $shipment->nomor_container_atau_plat
            );
        } elseif (in_array($ext, ['jpg', 'jpeg', 'png']) && extension_loaded('gd')) {
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
            'message' => 'Evidence berhasil diunggah',
            'data' => [
                'evidence_id' => $evidence->id,
                'file_url' => asset('storage/'.$relPath),
                'captured_at' => $capturedAt->format('Y-m-d H:i:s \W\I\B'),
                'uploaded_count' => $shipment->evidenceItems()->count(),
            ],
        ]);
    }

    /**
     * Submit final shipment.
     */
    public function finalizeShipment(Request $request, Shipment $shipment): JsonResponse
    {
        $validated = $request->validate([
            'waktu_kedatangan_container' => 'required|date',
            'waktu_keberangkatan_container' => 'required|date',
            'packing_list_no' => 'nullable|string|max:255',
            'tujuan_pengiriman' => 'nullable|string',
            'agen_forwarding' => 'nullable|string',
        ]);

        $mandatoryPointIds = SopPhotoPoint::where('wajib', true)->pluck('id');
        $uploadedPointIds = $shipment->evidenceItems()->whereNotNull('sop_photo_point_id')->pluck('sop_photo_point_id')->unique();
        $missingPoints = $mandatoryPointIds->diff($uploadedPointIds);

        if ($missingPoints->isNotEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Seluruh titik foto wajib ('.$mandatoryPointIds->count().' titik) harus diunggah sebelum submit.',
                'missing_count' => $missingPoints->count(),
            ], 422);
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

        return response()->json([
            'success' => true,
            'message' => 'Shipment berhasil diselesaikan dan disubmit secara permanen!',
            'data' => $shipment,
        ]);
    }

    /**
     * Daftar Master Karyawan / Petugas Aktif.
     */
    public function getKaryawans(): JsonResponse
    {
        $karyawans = Karyawan::where('status', 'aktif')->orderBy('nama')->get();

        return response()->json([
            'success' => true,
            'data' => $karyawans,
        ]);
    }

    /**
     * Daftar 27 SOP Photo Points.
     */
    public function getSopPoints(): JsonResponse
    {
        $points = SopPhotoPoint::orderBy('urutan')->get();

        return response()->json([
            'success' => true,
            'data' => $points,
        ]);
    }

    /**
     * OCR Shipment Order untuk Auto-Fill.
     */
    public function ocrShipmentOrder(Request $request): JsonResponse
    {
        return app(FieldAppController::class)->ocrShipmentOrder($request);
    }

    /**
     * Stream file evidence langsung dari storage dengan header CORS.
     */
    public function serveEvidenceFile(EvidenceItem $evidence)
    {
        $path = storage_path('app/public/' . $evidence->file_path);
        if (! file_exists($path)) {
            return response()->json(['error' => 'File tidak ditemukan'], 404);
        }

        return response()->file($path, [
            'Content-Type' => $evidence->tipe_item === 'video' ? 'video/mp4' : 'image/jpeg',
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Methods' => 'GET, POST, OPTIONS',
            'Access-Control-Allow-Headers' => '*',
            'Cross-Origin-Resource-Policy' => 'cross-origin',
            'Cache-Control' => 'no-cache, private',
        ]);
    }
}

