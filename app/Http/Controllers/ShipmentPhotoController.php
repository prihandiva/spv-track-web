<?php

namespace App\Http\Controllers;

use App\Models\Shipment;
use App\Models\ShipmentPhoto;
use App\Services\PhotoProcessingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ShipmentPhotoController extends Controller
{
    public function __construct(
        protected PhotoProcessingService $photoService
    ) {}

    /**
     * Endpoint API: Upload foto ke tempat penyimpanan sementara (Tahap 1).
     * Server menempelkan timestamp burn-in dan mengembalikan preview URL.
     */
    public function uploadTemp(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'file' => 'required|image|mimes:jpeg,jpg,png,webp|max:20480', // Maksimal 20MB
            'point_no' => 'nullable|integer|min:1',
            'point_label' => 'required|string|max:255',
            'is_extra' => 'nullable|boolean',
            'captured_at' => 'nullable|date',
        ]);

        $file = $request->file('file');
        $userId = auth()->id() ?? 1; // Fallback ke default operator jika auth mock

        try {
            $result = $this->photoService->processTemporaryUpload($file, $validated, $userId);

            return response()->json([
                'success' => true,
                'message' => 'Foto berhasil diunggah ke penyimpanan sementara dan distempel waktu server.',
                'data' => $result,
            ], 201);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses upload foto: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Endpoint Preview: Melihat foto sementara (stamped, original, thumbnail).
     */
    public function previewTemp(Request $request, string $tempId): BinaryFileResponse|JsonResponse
    {
        $type = $request->query('type', 'stamped');
        if (! in_array($type, ['stamped', 'original', 'thumbnail'])) {
            $type = 'stamped';
        }

        $filePath = $this->photoService->getTempFilePath($tempId, $type);

        if (! $filePath || ! file_exists($filePath)) {
            return response()->json([
                'success' => false,
                'message' => 'Foto sementara tidak ditemukan atau sudah kadaluarsa.',
            ], 404);
        }

        return response()->file($filePath, [
            'Content-Type' => 'image/jpeg',
            'Cache-Control' => 'no-cache, private',
        ]);
    }

    /**
     * Endpoint Final: Submit foto-foto sementara ke shipment secara permanen (Tahap 2).
     */
    public function submit(Request $request, Shipment $shipment): JsonResponse
    {
        $validated = $request->validate([
            'photos' => 'required|array|min:1',
            'photos.*.temp_id' => 'required|string',
            'photos.*.point_no' => 'nullable|integer',
            'photos.*.point_label' => 'required|string|max:255',
            'photos.*.is_extra' => 'nullable|boolean',
            'photos.*.ocr_text' => 'nullable|string',
            // Opsional informasi shipment tambahan
            'packing_list_no' => 'nullable|string|max:255',
            'tujuan_pengiriman' => 'nullable|string',
            'agen_forwarding' => 'nullable|string',
        ]);

        if ($shipment->status === 'submitted') {
            return response()->json([
                'success' => false,
                'message' => 'Shipment ini sudah disubmit sebelumnya dan bersifat permanen.',
            ], 422);
        }

        $userId = auth()->id() ?? $shipment->user_id ?? 1;

        try {
            $savedPhotos = DB::transaction(function () use ($validated, $shipment, $userId) {
                $created = [];

                foreach ($validated['photos'] as $item) {
                    $pointNo = isset($item['point_no']) && $item['point_no'] !== null ? (int) $item['point_no'] : null;
                    $isExtra = (bool) ($item['is_extra'] ?? false);
                    $pointLabel = $item['point_label'];
                    $ocrText = $item['ocr_text'] ?? null;

                    $photo = $this->photoService->promoteTempToPermanent(
                        tempId: $item['temp_id'],
                        shipmentId: $shipment->id,
                        pointNo: $pointNo,
                        pointLabel: $pointLabel,
                        isExtra: $isExtra,
                        userId: $userId,
                        ocrText: $ocrText
                    );

                    $created[] = [
                        'id' => $photo->id,
                        'point_no' => $photo->point_no,
                        'point_label' => $photo->point_label,
                        'is_extra' => $photo->is_extra,
                        'size' => $photo->size,
                        'sha256' => $photo->sha256,
                        'captured_at' => $photo->captured_at?->format('d/m/Y H:i:s \W\I\B'),
                        'stamped_at' => $photo->stamped_at?->format('d/m/Y H:i:s \W\I\B'),
                        'stamped_url' => $photo->stamped_url,
                        'original_url' => $photo->original_url,
                        'thumbnail_url' => $photo->thumbnail_url,
                    ];
                }

                // Update shipment status jika ada field opsional
                $shipmentUpdates = [
                    'status' => 'submitted',
                    'submitted_at' => now(),
                ];

                if (! empty($validated['packing_list_no'])) {
                    $shipmentUpdates['packing_list_no'] = $validated['packing_list_no'];
                }
                if (! empty($validated['tujuan_pengiriman'])) {
                    $shipmentUpdates['tujuan_pengiriman'] = $validated['tujuan_pengiriman'];
                }
                if (! empty($validated['agen_forwarding'])) {
                    $shipmentUpdates['agen_forwarding'] = $validated['agen_forwarding'];
                }

                $shipment->update($shipmentUpdates);

                return $created;
            });

            return response()->json([
                'success' => true,
                'message' => 'Seluruh foto evidence berhasil disimpan permanen ke database.',
                'data' => [
                    'shipment_id' => $shipment->id,
                    'status' => 'submitted',
                    'submitted_at' => $shipment->submitted_at?->format('d/m/Y H:i:s \W\I\B'),
                    'total_photos' => count($savedPhotos),
                    'photos' => $savedPhotos,
                ],
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses submit foto: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Endpoint Private File Server: Mengakses file foto permanen dari disk private.
     */
    public function servePermanentPhoto(Request $request, ShipmentPhoto $photo): BinaryFileResponse|JsonResponse
    {
        // Proteksi login / akses:
        // Di sini bisa dipasang Gate/Policy sesuai role pengguna (operator, admin, superadmin)
        $type = $request->query('type', 'stamped');

        $relPath = match ($type) {
            'original' => $photo->path,
            'thumbnail' => $photo->thumbnail_path ?: $photo->stamped_path,
            default => $photo->stamped_path,
        };

        if (! Storage::disk('local')->exists($relPath)) {
            return response()->json([
                'success' => false,
                'message' => 'File foto tidak ditemukan di penyimpanan server.',
            ], 404);
        }

        $fullPath = Storage::disk('local')->path($relPath);

        return response()->file($fullPath, [
            'Content-Type' => $photo->mime ?: 'image/jpeg',
            'Content-Disposition' => 'inline; filename="'.basename($relPath).'"',
            'Cache-Control' => 'private, max-age=86400',
        ]);
    }

    /**
     * Endpoint API: Mengambil daftar foto permanen dari sebuah shipment.
     */
    public function index(Shipment $shipment): JsonResponse
    {
        $photos = $shipment->photos()->orderBy('is_extra')->orderBy('point_no')->get();

        $data = $photos->map(function ($p) {
            return [
                'id' => $p->id,
                'point_no' => $p->point_no,
                'point_label' => $p->point_label,
                'is_extra' => $p->is_extra,
                'size' => $p->size,
                'sha256' => $p->sha256,
                'captured_at' => $p->captured_at?->format('d/m/Y H:i:s \W\I\B'),
                'stamped_at' => $p->stamped_at?->format('d/m/Y H:i:s \W\I\B'),
                'ocr_text' => $p->ocr_text,
                'stamped_url' => $p->stamped_url,
                'original_url' => $p->original_url,
                'thumbnail_url' => $p->thumbnail_url,
            ];
        });

        return response()->json([
            'success' => true,
            'shipment_id' => $shipment->id,
            'total' => $photos->count(),
            'photos' => $data,
        ]);
    }
}
