<?php

namespace App\Http\Controllers;

use App\Models\EvidenceItem;
use App\Models\Karyawan;
use App\Models\Shipment;
use App\Models\SopPhotoPoint;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use thiagoalessio\TesseractOCR\TesseractOCR;

class FieldAppController extends Controller
{
    public function create()
    {
        // For development, we assume user_id = 1 (Admin) is acting as operator
        // In real app, we'd use auth()->id()
        $karyawans = Karyawan::where('status', 'aktif')->get();

        return view('field-app.create', compact('karyawans'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'jenis_produk' => 'required|in:fiber,sodium',
            'jenis_pengiriman' => 'required|in:export,lokal',
            'shipment_group' => 'nullable|string|max:255',
            'shipment_no' => 'nullable|string|max:255',
            'plat_nomor' => 'nullable|string|max:255',
            'nama_sopir' => 'nullable|string|max:255',
            'nomor_container_atau_plat' => 'nullable|string|max:255',
            'cuaca' => 'required|in:kering,mendung,hujan,gerimis',
            'waktu' => 'required|in:siang,sore,malam',
            'tanggal_staging' => 'required|date',
            'warehouse_lokasi' => 'required|in:atas,tengah,bawah',
            'karyawan_id' => 'required|exists:karyawans,id',
        ]);

        // Add dummy user_id
        $validated['user_id'] = 1;
        $validated['status'] = 'draft';

        // Ensure nomor_container_atau_plat is never empty (fallback to plat_nomor or shipment_no)
        if (empty($validated['nomor_container_atau_plat'])) {
            $validated['nomor_container_atau_plat'] = ! empty($validated['plat_nomor'])
                ? $validated['plat_nomor']
                : (! empty($validated['shipment_no']) ? $validated['shipment_no'] : 'SPV-'.date('Ymd-His'));
        }

        $shipment = Shipment::create($validated);

        return redirect()->route('field-app.timeline', $shipment->id);
    }

    public function timeline(Shipment $shipment)
    {
        $shipment->load('evidenceItems');
        $points = SopPhotoPoint::orderBy('urutan')->get();

        return view('field-app.timeline', compact('shipment', 'points'));
    }

    public function uploadPhoto(Request $request, Shipment $shipment)
    {
        $request->validate([
            'sop_photo_point_id' => 'nullable|exists:sop_photo_points,id',
            'file' => 'required|file|mimes:jpg,jpeg,png,mp4,mov|max:20480', // max 20MB
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

        // Watermark timestamp directly on image if supported
        if (in_array($ext, ['jpg', 'jpeg', 'png']) && extension_loaded('gd')) {
            $img = null;
            if (in_array($ext, ['jpg', 'jpeg'])) {
                $img = @imagecreatefromjpeg($file->getPathname());
            } elseif ($ext === 'png') {
                $img = @imagecreatefrompng($file->getPathname());
            }

            if ($img) {
                $w = imagesx($img);
                $h = imagesy($img);

                // Draw dark translucent bar at the bottom
                $barHeight = max(42, (int) ($h * 0.055));
                $barY = $h - $barHeight;
                $barColor = imagecolorallocatealpha($img, 0, 0, 0, 45);
                imagefilledrectangle($img, 0, $barY, $w, $h, $barColor);

                // Watermark text: YYYY-MM-DD HH:mm:ss | No. Container
                $textColor = imagecolorallocate($img, 255, 255, 255);
                $stampStr = "{$timestampText} | {$shipment->nomor_container_atau_plat}";
                $textY = $barY + (int) (($barHeight - 16) / 2);
                imagestring($img, 5, 14, $textY, $stampStr, $textColor);

                if (in_array($ext, ['jpg', 'jpeg'])) {
                    imagejpeg($img, $targetPath, 88);
                } else {
                    imagepng($img, $targetPath, 8);
                }
                imagedestroy($img);
            } else {
                $file->move($storageDir, $fileName);
            }
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
                'tipe_item' => in_array($ext, ['mp4', 'mov']) ? 'video' : 'foto',
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
            'tujuan_pengiriman' => 'nullable|string',
            'agen_forwarding' => 'nullable|string',
            'waktu_kedatangan_container' => 'nullable|date',
            'waktu_keberangkatan_container' => 'nullable|date',
        ]);

        $shipment->update([
            'packing_list_no' => $validated['packing_list_no'] ?? null,
            'tujuan_pengiriman' => $validated['tujuan_pengiriman'] ?? null,
            'agen_forwarding' => $validated['agen_forwarding'] ?? null,
            'waktu_kedatangan_container' => $validated['waktu_kedatangan_container'] ?? null,
            'waktu_keberangkatan_container' => $validated['waktu_keberangkatan_container'] ?? null,
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);

        return redirect('/')->with('success', 'Shipment berhasil di-submit!');
    }

    public function ocrShipmentOrder(Request $request)
    {
        $request->validate(['image' => 'required|image']);
        $path = $request->file('image')->store('ocr', 'public');
        $fullPath = storage_path('app/public/'.$path);

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
            'shipment_group' => '',
            'shipment_no' => '',
            'plat_nomor' => '',
            'nama_sopir' => '',
            'raw_text' => $text,
        ];

        if (! empty($text)) {
            if (preg_match('/(?:Shipment\s*Group|Group)[\s\.:]*([\d\/]+)/i', $text, $matches)) {
                $data['shipment_group'] = trim($matches[1]);
            }
            if (preg_match('/(?:Shipment\s*No|No\.?\s*Shipment)[\s\.:]*(\d+)/i', $text, $matches)) {
                $data['shipment_no'] = trim($matches[1]);
            }
            if (preg_match('/(?:Plat|No\.?\s*Pol|Polisi)[\s\.:]*([A-Z0-9\s]{4,14})/i', $text, $matches)) {
                $data['plat_nomor'] = trim($matches[1]);
            }
            if (preg_match('/(?:Driver|Sopir|Nama\s*Sopir)[\s\.:]*([A-Za-z\s]{3,30})/i', $text, $matches)) {
                $data['nama_sopir'] = trim($matches[1]);
            }
        }

        return response()->json($data);
    }

    public function ocrSuratJalan(Request $request)
    {
        $request->validate(['image' => 'required|image']);
        $path = $request->file('image')->store('ocr', 'public');
        $fullPath = storage_path('app/public/'.$path);

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
