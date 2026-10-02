<?php

namespace App\Http\Controllers;

use App\Models\EvidenceItem;
use App\Models\Karyawan;
use App\Models\Shipment;
use App\Models\SopPhotoPoint;
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
    public function create(): View
    {
        // For development, we assume user_id = 1 (Admin) is acting as operator
        // In real app, we'd use auth()->id()
        $karyawans = Karyawan::where('status', 'aktif')->orderBy('nama')->get();

        $history = [
            'packing_list_no' => Shipment::whereNotNull('packing_list_no')->where('packing_list_no', '!=', '')->distinct()->pluck('packing_list_no')->filter()->values(),
            'shipment_group' => Shipment::whereNotNull('shipment_group')->where('shipment_group', '!=', '')->distinct()->pluck('shipment_group')->filter()->values(),
            'nama_sopir' => Shipment::whereNotNull('nama_sopir')->where('nama_sopir', '!=', '')->distinct()->pluck('nama_sopir')->filter()->values(),
            'plat_nomor' => Shipment::whereNotNull('plat_nomor')->where('plat_nomor', '!=', '')->distinct()->pluck('plat_nomor')->filter()->values(),
            'nomor_container' => Shipment::whereNotNull('nomor_container_atau_plat')->where('nomor_container_atau_plat', '!=', '')->distinct()->pluck('nomor_container_atau_plat')->filter()->values(),
        ];

        return view('field-app.create', compact('karyawans', 'history'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'jenis_produk' => 'required|in:fiber,sodium',
            'jenis_pengiriman' => 'required|in:export,lokal',
            'packing_list_no' => 'required|string|max:255',
            'shipment_group' => 'required|string|max:255',
            'shipment_no' => 'required|string|max:255',
            'plat_nomor' => 'required|string|max:255',
            'nama_sopir' => 'required|string|max:255',
            'nomor_container_atau_plat' => 'required|string|max:255',
            'cuaca' => 'required|in:kering,mendung,hujan,gerimis',
            'waktu' => 'required|in:siang,sore,malam',
            'tanggal_staging' => 'required|date',
            'warehouse_lokasi' => 'required|in:atas,tengah,bawah',
            'karyawan_id' => 'required|exists:karyawans,id',
            'shipment_order_photo' => 'nullable|image|max:20480',
        ]);

        // Add dummy user_id
        $validated['user_id'] = 1;
        $validated['status'] = 'draft';

        $shipment = Shipment::create(collect($validated)->except('shipment_order_photo')->toArray());

        if ($request->hasFile('shipment_order_photo')) {
            $request->file('shipment_order_photo')->store('shipment_orders', 'public');
            $shipment->shipmentOrder()->create([
                'shipment_group' => $shipment->shipment_group,
                'shipment_no' => $shipment->shipment_no,
                'raw_ocr_text' => $request->input('ocr_raw_text', ''),
            ]);
        }

        return redirect()->route('field-app.timeline', $shipment->id);
    }

    public function timeline(Shipment $shipment)
    {
        $shipment->load('evidenceItems');
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
        $request->validate([
            'sop_photo_point_id' => 'nullable|exists:sop_photo_points,id',
            'file' => 'required|file|mimes:jpg,jpeg,png,mp4,mov,webm,3gp,avi,m4v,quicktime|max:51200', // max 50MB
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
            mkdir($storageDir, 0755, true);
        }
        $targetPath = "{$storageDir}/{$fileName}";
        $relPath = "shipments/{$shipment->id}/{$fileName}";

        $point = $pointId ? SopPhotoPoint::find($pointId) : null;
        $pointTitle = $point ? "TITIK {$point->urutan}: {$point->nama_titik}" : ($request->boolean('is_tambahan') ? 'FOTO EKSTRA' : 'EVIDENCE');

        $isVideo = in_array($ext, ['mp4', 'mov', 'webm', '3gp', 'avi', 'm4v']) || str_starts_with($file->getMimeType(), 'video/');

        // Watermark timestamp dengan font TrueType proporsional resolusi kamera & koreksi EXIF rotasi (hanya foto)
        if (! $isVideo && in_array($ext, ['jpg', 'jpeg', 'png']) && extension_loaded('gd')) {
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
    }

    public function submit(Request $request, Shipment $shipment)
    {
        $validated = $request->validate([
            'packing_list_no' => 'nullable|string|max:255',
            'tujuan_pengiriman' => 'required|string',
            'agen_forwarding' => 'required|string',
            'waktu_kedatangan_container' => 'required|date',
            'waktu_keberangkatan_container' => 'required|date',
        ]);

        // Verifikasi seluruh titik SOP wajib (Titik 1-26) telah terisi
        $mandatoryPointIds = SopPhotoPoint::where('wajib', true)->pluck('id');
        $uploadedPointIds = $shipment->evidenceItems()->whereNotNull('sop_photo_point_id')->pluck('sop_photo_point_id')->unique();
        $missingPoints = $mandatoryPointIds->diff($uploadedPointIds);

        if ($missingPoints->isNotEmpty()) {
            return back()->withErrors([
                'photos' => 'Seluruh titik foto wajib (26 titik) harus diunggah dan terverifikasi sebelum submit final.',
            ])->withInput();
        }

        $shipment->update([
            'packing_list_no' => $validated['packing_list_no'] ?: $shipment->packing_list_no,
            'tujuan_pengiriman' => $validated['tujuan_pengiriman'],
            'agen_forwarding' => $validated['agen_forwarding'],
            'waktu_kedatangan_container' => $validated['waktu_kedatangan_container'],
            'waktu_keberangkatan_container' => $validated['waktu_keberangkatan_container'],
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);

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
            $ocr = new TesseractOCR($fullPath);
            $ocr->executable('C:\\Program Files\\Tesseract-OCR\\tesseract.exe');
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
            'plat_nomor' => '',
            'nama_sopir' => '',
            'container_no' => '',
            'raw_text' => $text,
        ];

        if (! empty($text)) {
            // Shipment Group: e.g. "Shipment Group: 869026487 1/2" -> "869026487"
            if (preg_match('/Shipment\s*Group[^\d\n\r]*([0-9]{6,12})/i', $text, $matches)) {
                $data['shipment_group'] = trim($matches[1]);
            } elseif (preg_match('/(?:Group\s*No|Group)[^\d\n\r]*([0-9]{6,12})/i', $text, $matches)) {
                $data['shipment_group'] = trim($matches[1]);
            }

            // Shipment No: e.g. "Shipment No.: 860110918" -> "860110918"
            if (preg_match('/(?:Shipment\s*No|No\.?\s*Shipment|Shipment\s*Number|Shpt\s*No)[^\d\n\r]*([0-9]{6,12})/i', $text, $matches)) {
                $data['shipment_no'] = trim($matches[1]);
            }

            // Delivery / Packing List No from "Delivery" column or label
            $deliveryNo = '';
            if (preg_match('/(?:Delivery\s*No|Delivery\s*#|Delivery\s*Number)[\s\.:\t]*([0-9]{6,14})/i', $text, $matches)) {
                $deliveryNo = trim($matches[1]);
            } elseif (preg_match('/Delivery[\s\.:\t=]+([0-9]{6,14})/i', $text, $matches)) {
                $deliveryNo = trim($matches[1]);
            } elseif (preg_match('/Delivery[^\r\n0-9]*[\r\n]+[^\d\r\n]*([0-9]{6,14})/i', $text, $matches)) {
                // Header "Delivery" followed on the next line by the number
                $deliveryNo = trim($matches[1]);
            } elseif (preg_match('/(?:DN|Deliv\.?)\s*[:#\s]*([0-9]{6,14})/i', $text, $matches)) {
                $deliveryNo = trim($matches[1]);
            } elseif (preg_match('/(?:Packing\s*List|P[\/-]?L|Surat\s*Jalan|No\.?\s*SJ)[\s\.:#]*([A-Za-z0-9\-\/]{4,20})/i', $text, $matches)) {
                $deliveryNo = trim($matches[1]);
            }

            // If table had "Delivery" in header, search lines below header for an 8-10 digit number
            if (empty($deliveryNo) && stripos($text, 'Delivery') !== false) {
                $lines = preg_split('/[\r\n]+/', $text);
                $foundDeliveryHeader = false;
                foreach ($lines as $line) {
                    if (stripos($line, 'Delivery') !== false) {
                        $foundDeliveryHeader = true;
                        if (preg_match('/Delivery[^\d]*([0-9]{6,14})/i', $line, $m)) {
                            $deliveryNo = trim($m[1]);
                            break;
                        }

                        continue;
                    }
                    if ($foundDeliveryHeader) {
                        if (preg_match('/\b([0-9]{7,12})\b/', $line, $m)) {
                            $candidate = trim($m[1]);
                            if ($candidate !== $data['shipment_group'] && $candidate !== $data['shipment_no']) {
                                $deliveryNo = $candidate;
                                break;
                            }
                        }
                    }
                }
            }

            $data['packing_list_no'] = $deliveryNo;
            $data['delivery_no'] = $deliveryNo;

            // Plat Nomor (if printed)
            if (preg_match('/(?:Plat|No\.?\s*Pol|Polisi|Nopol)[\s\.:]*([A-Z]{1,2}\s*[0-9]{1,5}\s*[A-Z]{1,3})/i', $text, $matches)) {
                $data['plat_nomor'] = strtoupper(trim(preg_replace('/\s+/', '', $matches[1])));
            }

            // Sopir (if printed)
            if (preg_match('/(?:Driver|Sopir|Nama\s*Sopir)[\s\.:]*([A-Za-z\s]{3,30})/i', $text, $matches)) {
                $cleanSopir = trim($matches[1]);
                if (! preg_match('/(?:Shipment|Vessel|Port|Date|Order)/i', $cleanSopir)) {
                    $data['nama_sopir'] = ucwords(strtolower($cleanSopir));
                }
            }

            // Container No (if standard ISO container code present)
            if (preg_match('/\b([A-Z]{4}\s*[0-9]{7})\b/', $text, $matches)) {
                $data['container_no'] = strtoupper(preg_replace('/\s+/', '', $matches[1]));
            }
        }

        $data['extracted_count'] = count(array_filter([
            $data['packing_list_no'],
            $data['shipment_group'],
            $data['shipment_no'],
            $data['plat_nomor'],
            $data['nama_sopir'],
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
            $ocr = new TesseractOCR($fullPath);
            $ocr->executable('C:\\Program Files\\Tesseract-OCR\\tesseract.exe');
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
