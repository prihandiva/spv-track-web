<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Karyawan;
use App\Models\Shipment;
use App\Models\SopPhotoPoint;
use App\Models\EvidenceItem;

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

        $shipment = Shipment::create($validated);

        return redirect()->route('field-app.timeline', $shipment->id);
    }

    public function timeline(Shipment $shipment)
    {
        // Load the 27 SOP points
        $points = SopPhotoPoint::orderBy('urutan')->get();
        
        // Group by 3 for layouting if needed, or just list
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
        
        // Simulating GPS extraction from EXIF
        $lat = -6.200000; 
        $lng = 106.816666;

        // Path & naming convention NOMOR(n)
        $ext = $file->getClientOriginalExtension();
        $count = $shipment->evidenceItems()->count() + 1;
        $fileName = preg_replace('/[^A-Za-z0-9]/', '', $shipment->nomor_container_atau_plat) . "({$count}).{$ext}";
        
        $path = $file->storeAs("shipments/{$shipment->id}", $fileName, 'public');

        $evidence = EvidenceItem::create([
            'shipment_id' => $shipment->id,
            'sop_photo_point_id' => $request->sop_photo_point_id,
            'file_path' => $path,
            'file_name' => $fileName,
            'tipe_item' => in_array($ext, ['mp4','mov']) ? 'video' : 'foto',
            'captured_at' => now(), // Mocked timestamp
            'gps_lat' => $lat,
            'gps_lng' => $lng,
            'is_tambahan' => $request->boolean('is_tambahan', false),
        ]);

        // Mock async OCR job dispatch here
        // ProcessOcrJob::dispatch($evidence);

        return response()->json([
            'success' => true,
            'message' => 'File uploaded',
            'evidence_id' => $evidence->id,
            'file_url' => asset('storage/' . $path)
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
        $fullPath = storage_path('app/public/' . $path);
        
        $ocr = new \thiagoalessio\TesseractOCR\TesseractOCR($fullPath);
        $ocr->executable('C:\Program Files\Tesseract-OCR\tesseract.exe');
        $text = $ocr->run();
        
        $data = [
            'shipment_group' => '',
            'shipment_no' => '',
            'raw_text' => $text,
        ];
        
        if (preg_match('/Shipment Group:?\s*([\d\/]+)/i', $text, $matches)) {
            $data['shipment_group'] = trim($matches[1]);
        }
        if (preg_match('/Shipment No\.?:?\s*(\d+)/i', $text, $matches)) {
            $data['shipment_no'] = trim($matches[1]);
        }
        
        return response()->json($data);
    }

    public function ocrSuratJalan(Request $request)
    {
        $request->validate(['image' => 'required|image']);
        $path = $request->file('image')->store('ocr', 'public');
        $fullPath = storage_path('app/public/' . $path);
        
        $ocr = new \thiagoalessio\TesseractOCR\TesseractOCR($fullPath);
        $ocr->executable('C:\Program Files\Tesseract-OCR\tesseract.exe');
        $text = $ocr->run();
        
        $data = [
            'packing_list_no' => '',
            'tujuan_pengiriman' => '',
            'agen_forwarding' => '',
            'raw_text' => $text,
        ];
        
        // Simple heuristics for Packing List Number
        if (preg_match('/Number\s*(\d+)/i', $text, $matches)) {
            $data['packing_list_no'] = trim($matches[1]);
        } else if (preg_match('/Packing List.*?(\d+)/is', $text, $matches)) {
            $data['packing_list_no'] = trim($matches[1]);
        }
        
        // Heuristics for Tujuan Pengiriman
        // Usually located after "www.pt-spv.com" and before "forwarding agent" or "Reference no"
        if (preg_match('/(?:www\.[a-z0-9-]+\.com|Indonesia)\s*\n+(.*?)(?:forwarding agent|Reference no)/is', $text, $matches)) {
            $tujuan = trim($matches[1]);
            // Clean up accidental OCR noise at the start (like "54732 'Packing |" or "41333")
            $tujuan = preg_replace('/^\s*(?:\d{4,6}\b.*?|\'?Packing\b.*?)\s*\n/is', '', $tujuan);
            $data['tujuan_pengiriman'] = trim($tujuan);
        }
        
        // Heuristics for Agen Forwarding
        // Located between "forwarding agent" and shipping vessel details ("BANGKOK", "via", "Seal no")
        if (preg_match('/forwarding agent\s*(?:Company)?\s*(.*?)(?:BANGKOK|via TANJUNG|Seal no|Container no)/is', $text, $matches)) {
            $data['agen_forwarding'] = trim($matches[1]);
        }
        
        return response()->json($data);
    }
}
