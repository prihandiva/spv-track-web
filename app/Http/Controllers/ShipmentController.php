<?php

namespace App\Http\Controllers;

use App\Models\Shipment;
use App\Models\SopPhotoPoint;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use ZipArchive;

class ShipmentController extends Controller
{
    public function index(Request $request)
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
            $query->where('status', $status);
        }

        $shipments = $query->paginate(10)->withQueryString();

        return view('shipments.index', compact('shipments'));
    }

    public function show(Shipment $shipment)
    {
        $shipment->load(['karyawan', 'karyawans', 'user', 'evidenceItems.sopPhotoPoint', 'photos']);
        $points = SopPhotoPoint::orderBy('urutan')->get();

        return view('shipments.show', compact('shipment', 'points'));
    }

    /**
     * Download seluruh bukti foto evidence shipment dalam satu file ZIP.
     * Nama ZIP = nomor packing list (fallback ke nomor shipment).
     * Nama gambar = nomor container, atau nomor plat jika container tidak ada / 0.
     * Secara default foto dikompresi proporsional (max 1600px, Q80) agar ukuran ZIP hemat (~5-6 MB vs 40+ MB).
     */
    public function downloadZip(Request $request, Shipment $shipment)
    {
        @ini_set('memory_limit', '512M');
        @set_time_limit(180);

        $shipment->load(['evidenceItems.sopPhotoPoint', 'photos', 'extraPhotos']);

        $quality = $request->input('quality', 'compressed');
        $optimize = $quality !== 'original';

        // 1. Tentukan nama file ZIP dari packing_list_no (fallback ke shipment_no atau ID)
        $packingList = trim((string) ($shipment->packing_list_no ?? ''));
        $zipBase = $packingList !== '' ? $packingList : ($shipment->shipment_no ?: ('shipment-'.$shipment->id));
        $safeZipName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $zipBase).'.zip';

        // 2. Tentukan nama dasar gambar: nomor container atau plat nomor (jika container tidak ada / 0)
        $container = trim((string) ($shipment->nomor_container_atau_plat ?? ''));
        $plate = trim((string) ($shipment->plat_nomor ?? ''));

        $hasValidContainer = $container !== '' && $container !== '0' && strtolower($container) !== 'none';
        $baseIdentifier = $hasValidContainer ? $container : ($plate !== '' ? $plate : ('SPV-'.$shipment->id));
        $safeIdentifier = preg_replace('/[^A-Za-z0-9_\-]/', '_', $baseIdentifier);

        // Kumpulkan semua file bukti foto/video
        $evidenceList = collect();

        foreach ($shipment->evidenceItems as $ev) {
            $fullPath = storage_path('app/public/'.$ev->file_path);
            if (file_exists($fullPath)) {
                $evidenceList->push([
                    'path' => $fullPath,
                    'point_no' => $ev->sopPhotoPoint?->urutan,
                    'point_name' => $ev->sopPhotoPoint?->nama_titik,
                    'is_extra' => (bool) $ev->is_tambahan,
                    'ext' => pathinfo($fullPath, PATHINFO_EXTENSION) ?: 'jpg',
                ]);
            }
        }

        // Tambahkan foto dari model photos/extraPhotos jika ada yang belum terdaftar
        foreach ($shipment->photos as $ph) {
            $fullPath = storage_path('app/public/'.($ph->stamped_path ?: $ph->path));
            if (file_exists($fullPath) && ! $evidenceList->contains('path', $fullPath)) {
                $evidenceList->push([
                    'path' => $fullPath,
                    'point_no' => $ph->point_no,
                    'point_name' => $ph->point_label,
                    'is_extra' => (bool) $ph->is_extra,
                    'ext' => pathinfo($fullPath, PATHINFO_EXTENSION) ?: 'jpg',
                ]);
            }
        }

        if ($evidenceList->isEmpty()) {
            return back()->with('error', 'Belum ada bukti foto evidence yang tersimpan untuk diunduh.');
        }

        // 3. Cek apakah file ZIP sudah tersedia di cache server
        $cacheDir = storage_path('app/zip_cache');
        if (! File::isDirectory($cacheDir)) {
            File::makeDirectory($cacheDir, 0755, true);
        }

        $cacheToken = md5(($shipment->updated_at?->timestamp ?? '0').'_'.$evidenceList->count().'_'.$quality);
        $cachedZipPath = $cacheDir.DIRECTORY_SEPARATOR."{$shipment->id}_{$cacheToken}_{$safeZipName}";

        if (file_exists($cachedZipPath) && filesize($cachedZipPath) > 0) {
            return response()->download($cachedZipPath, $safeZipName, [
                'Content-Type' => 'application/zip',
            ]);
        }

        // Urutkan berdasarkan urutan titik
        $sorted = $evidenceList->sortBy(fn ($item) => $item['point_no'] ?? 999);

        // Buat file ZIP sementara
        $tempZipPath = tempnam(sys_get_temp_dir(), 'spv_zip_');
        $zip = new ZipArchive;

        if ($zip->open($tempZipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            return back()->with('error', 'Gagal membuat file arsip ZIP di server.');
        }

        $extraIndex = 1;
        $usedNames = [];

        foreach ($sorted as $item) {
            if ($item['point_no']) {
                $ptNo = str_pad((string) $item['point_no'], 2, '0', STR_PAD_LEFT);
                $cleanTitle = trim(preg_replace('/[^A-Za-z0-9]+/', '_', (string) $item['point_name']), '_');
                $entryName = "{$safeIdentifier}_Titik_{$ptNo}_{$cleanTitle}.{$item['ext']}";
            } elseif ($item['is_extra']) {
                $entryName = "{$safeIdentifier}_Ekstra_{$extraIndex}.{$item['ext']}";
                $extraIndex++;
            } else {
                $entryName = "{$safeIdentifier}_Foto_{$extraIndex}.{$item['ext']}";
                $extraIndex++;
            }

            // Mencegah bentrok nama duplikat di dalam ZIP
            if (isset($usedNames[$entryName])) {
                $usedNames[$entryName]++;
                $entryName = pathinfo($entryName, PATHINFO_FILENAME).'_'.$usedNames[$entryName].'.'.$item['ext'];
            } else {
                $usedNames[$entryName] = 1;
            }

            $this->addImageToZipOptimized($zip, $item['path'], $entryName, $optimize);
        }

        $zip->close();

        // Simpan ke cache agar unduhan selanjutnya instan
        if (file_exists($tempZipPath) && filesize($tempZipPath) > 0) {
            @copy($tempZipPath, $cachedZipPath);
        }

        return response()->download($tempZipPath, $safeZipName, [
            'Content-Type' => 'application/zip',
        ])->deleteFileAfterSend(true);
    }

    /**
     * Memasukkan file gambar ke dalam ZIP dengan optimasi resolusi proporsional & kompresi JPEG.
     */
    protected function addImageToZipOptimized(ZipArchive $zip, string $filePath, string $entryName, bool $optimize = true): void
    {
        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        if (! $optimize || ! in_array($ext, ['jpg', 'jpeg', 'png', 'webp']) || ! extension_loaded('gd')) {
            $zip->addFile($filePath, $entryName);

            return;
        }

        $fileSize = @filesize($filePath);
        $info = @getimagesize($filePath);
        if (! $info) {
            $zip->addFile($filePath, $entryName);

            return;
        }

        $w = $info[0];
        $h = $info[1];
        $maxDim = 1600;

        // Jika ukuran file sudah kecil dan resolusi <= 1600, tambahkan langsung
        if ($w <= $maxDim && $h <= $maxDim && $fileSize < 350 * 1024) {
            $zip->addFile($filePath, $entryName);

            return;
        }

        if ($w > $h) {
            $newW = min($w, $maxDim);
            $newH = (int) round($h * ($newW / $w));
        } else {
            $newH = min($h, $maxDim);
            $newW = (int) round($w * ($newH / $h));
        }

        $src = match ($ext) {
            'png' => @imagecreatefrompng($filePath),
            'webp' => @imagecreatefromwebp($filePath),
            default => @imagecreatefromjpeg($filePath),
        };

        if (! $src) {
            $zip->addFile($filePath, $entryName);

            return;
        }

        $dst = imagecreatetruecolor($newW, $newH);
        $white = imagecolorallocate($dst, 255, 255, 255);
        imagefilledrectangle($dst, 0, 0, $newW, $newH, $white);

        imagecopyresampled($dst, $src, 0, 0, 0, 0, $newW, $newH, $w, $h);
        imagedestroy($src);

        ob_start();
        imagejpeg($dst, null, 80);
        $optimizedData = ob_get_clean();
        imagedestroy($dst);

        if ($optimizedData !== false && strlen($optimizedData) > 0) {
            $zipEntryName = preg_replace('/\.(png|webp)$/i', '.jpg', $entryName);
            $zip->addFromString($zipEntryName, $optimizedData);
        } else {
            $zip->addFile($filePath, $entryName);
        }
    }

    /**
     * Tampilan pratinjau laporan PDF / cetak browser (screen preview & @media print).
     */
    public function printReport(Shipment $shipment)
    {
        $shipment->load(['karyawan', 'karyawans', 'user', 'evidenceItems.sopPhotoPoint', 'photos', 'extraPhotos']);

        $photoItems = collect();

        foreach ($shipment->evidenceItems as $ev) {
            $ext = strtolower(pathinfo($ev->file_path, PATHINFO_EXTENSION));
            // Hanya ambil FOTO (tanpa video)
            if ($ev->tipe_item !== 'foto' || in_array($ext, ['mp4', 'mov', 'webm', '3gp', 'avi', 'm4v'])) {
                continue;
            }

            $fullPath = storage_path('app/public/'.$ev->file_path);
            if (file_exists($fullPath)) {
                $photoItems->push([
                    'point_no' => $ev->sopPhotoPoint?->urutan,
                    'point_name' => $ev->sopPhotoPoint?->nama_titik ?? 'Bukti Loading',
                    'is_extra' => (bool) $ev->is_tambahan,
                    'tipe_item' => 'foto',
                    'timestamp_wib' => $ev->captured_at ? $ev->captured_at->format('d/m/Y H:i:s \W\I\B') : ($ev->created_at ? $ev->created_at->format('d/m/Y H:i:s \W\I\B') : '-'),
                    'image_src' => asset('storage/'.$ev->file_path),
                ]);
            }
        }

        // Tambahkan foto dari model photos/extraPhotos jika ada yang belum terdaftar
        foreach ($shipment->photos as $ph) {
            $relPath = $ph->stamped_path ?: $ph->path;
            $ext = strtolower(pathinfo($relPath, PATHINFO_EXTENSION));
            if (in_array($ext, ['mp4', 'mov', 'webm'])) {
                continue;
            }

            $fullPath = storage_path('app/public/'.$relPath);
            if (file_exists($fullPath) && ! $photoItems->contains('image_src', asset('storage/'.$relPath))) {
                $photoItems->push([
                    'point_no' => $ph->point_no,
                    'point_name' => $ph->point_label ?? 'Bukti Loading',
                    'is_extra' => (bool) $ph->is_extra,
                    'tipe_item' => 'foto',
                    'timestamp_wib' => $ph->stamped_at ? $ph->stamped_at->format('d/m/Y H:i:s \W\I\B') : ($ph->created_at ? $ph->created_at->format('d/m/Y H:i:s \W\I\B') : '-'),
                    'image_src' => asset('storage/'.$relPath),
                ]);
            }
        }

        $shipmentPhotos = $photoItems->sortBy(fn ($item) => $item['point_no'] ?? 999)->values();

        return view('shipments.pdf', compact('shipment', 'shipmentPhotos') + ['isPdf' => false]);
    }

    /**
     * Download dokumen laporan lengkap evidence dalam format PDF (.pdf).
     */
    public function downloadPdf(Shipment $shipment)
    {
        @ini_set('memory_limit', '512M');
        @set_time_limit(180);

        $shipment->load(['karyawan', 'karyawans', 'user', 'evidenceItems.sopPhotoPoint', 'photos', 'extraPhotos']);

        $packingList = trim((string) ($shipment->packing_list_no ?? ''));
        $pdfBase = $packingList !== '' ? $packingList : ($shipment->shipment_no ?: ('shipment-'.$shipment->id));
        $safePdfName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $pdfBase).'_Report.pdf';

        $cacheDir = storage_path('app/pdf_cache');
        if (! File::isDirectory($cacheDir)) {
            File::makeDirectory($cacheDir, 0755, true);
        }

        $cacheToken = md5(($shipment->updated_at?->timestamp ?? '0').'_'.$shipment->evidenceItems()->count());
        $cachedPdfPath = $cacheDir.DIRECTORY_SEPARATOR."{$shipment->id}_{$cacheToken}_{$safePdfName}";

        if (file_exists($cachedPdfPath) && filesize($cachedPdfPath) > 0) {
            return response()->download($cachedPdfPath, $safePdfName, [
                'Content-Type' => 'application/pdf',
            ]);
        }

        $photoItems = collect();

        foreach ($shipment->evidenceItems as $ev) {
            $ext = strtolower(pathinfo($ev->file_path, PATHINFO_EXTENSION));
            if ($ev->tipe_item !== 'foto' || in_array($ext, ['mp4', 'mov', 'webm', '3gp', 'avi', 'm4v'])) {
                continue;
            }

            $fullPath = storage_path('app/public/'.$ev->file_path);
            if (file_exists($fullPath)) {
                $base64 = $this->prepareImageBase64ForPdf($fullPath);
                if ($base64) {
                    $photoItems->push([
                        'point_no' => $ev->sopPhotoPoint?->urutan,
                        'point_name' => $ev->sopPhotoPoint?->nama_titik ?? 'Bukti Loading',
                        'is_extra' => (bool) $ev->is_tambahan,
                        'tipe_item' => 'foto',
                        'timestamp_wib' => $ev->captured_at ? $ev->captured_at->format('d/m/Y H:i:s \W\I\B') : ($ev->created_at ? $ev->created_at->format('d/m/Y H:i:s \W\I\B') : '-'),
                        'image_src' => $base64,
                    ]);
                }
            }
        }

        foreach ($shipment->photos as $ph) {
            $relPath = $ph->stamped_path ?: $ph->path;
            $ext = strtolower(pathinfo($relPath, PATHINFO_EXTENSION));
            if (in_array($ext, ['mp4', 'mov', 'webm'])) {
                continue;
            }

            $fullPath = storage_path('app/public/'.$relPath);
            if (file_exists($fullPath) && ! $photoItems->contains('point_name', $ph->point_label)) {
                $base64 = $this->prepareImageBase64ForPdf($fullPath);
                if ($base64) {
                    $photoItems->push([
                        'point_no' => $ph->point_no,
                        'point_name' => $ph->point_label ?? 'Bukti Loading',
                        'is_extra' => (bool) $ph->is_extra,
                        'tipe_item' => 'foto',
                        'timestamp_wib' => $ph->stamped_at ? $ph->stamped_at->format('d/m/Y H:i:s \W\I\B') : ($ph->created_at ? $ph->created_at->format('d/m/Y H:i:s \W\I\B') : '-'),
                        'image_src' => $base64,
                    ]);
                }
            }
        }

        $shipmentPhotos = $photoItems->sortBy(fn ($item) => $item['point_no'] ?? 999)->values();

        $pdf = Pdf::loadView('shipments.pdf', compact('shipment', 'shipmentPhotos') + ['isPdf' => true])
            ->setPaper('a4', 'portrait')
            ->setOption(['isRemoteEnabled' => true, 'isHtml5ParserEnabled' => true]);

        $pdf->save($cachedPdfPath);

        return response()->download($cachedPdfPath, $safePdfName, [
            'Content-Type' => 'application/pdf',
        ]);
    }

    /**
     * Mempersiapkan base64 data URI teroptimasi untuk rendering DomPDF yang cepat dan ringan.
     */
    protected function prepareImageBase64ForPdf(string $filePath, int $maxDim = 1000): ?string
    {
        $info = @getimagesize($filePath);
        if (! $info) {
            return null;
        }

        $w = $info[0];
        $h = $info[1];
        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        if ($w > $h) {
            $newW = min($w, $maxDim);
            $newH = (int) round($h * ($newW / $w));
        } else {
            $newH = min($h, $maxDim);
            $newW = (int) round($w * ($newH / $h));
        }

        $src = match ($ext) {
            'png' => @imagecreatefrompng($filePath),
            'webp' => @imagecreatefromwebp($filePath),
            default => @imagecreatefromjpeg($filePath),
        };

        if (! $src) {
            return 'data:image/jpeg;base64,'.base64_encode(file_get_contents($filePath));
        }

        $dst = imagecreatetruecolor($newW, $newH);
        $white = imagecolorallocate($dst, 255, 255, 255);
        imagefilledrectangle($dst, 0, 0, $newW, $newH, $white);

        imagecopyresampled($dst, $src, 0, 0, 0, 0, $newW, $newH, $w, $h);
        imagedestroy($src);

        ob_start();
        imagejpeg($dst, null, 75);
        $data = ob_get_clean();
        imagedestroy($dst);

        if ($data) {
            return 'data:image/jpeg;base64,'.base64_encode($data);
        }

        return null;
    }
}
