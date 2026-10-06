<?php

namespace App\Http\Controllers;

use App\Models\Shipment;
use App\Models\SopPhotoPoint;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
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
     * Download seluruh bukti foto evidence shipment tunggal dalam satu file ZIP.
     */
    public function downloadZip(Request $request, Shipment $shipment)
    {
        @ini_set('memory_limit', '512M');
        @set_time_limit(180);

        $quality = $request->input('quality', 'compressed');
        $zipPath = $this->generateShipmentZip($shipment, $quality);

        if (! $zipPath || ! file_exists($zipPath)) {
            return back()->with('error', 'Belum ada bukti foto evidence yang tersimpan untuk diunduh.');
        }

        $packingList = trim((string) ($shipment->packing_list_no ?? ''));
        $zipBase = $packingList !== '' ? $packingList : ($shipment->shipment_no ?: ('shipment-'.$shipment->id));
        $safeZipName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $zipBase).'.zip';

        return response()->download($zipPath, $safeZipName, [
            'Content-Type' => 'application/zip',
        ]);
    }

    /**
     * Menghasilkan file ZIP individual untuk sebuah shipment beserta bukti fotonya,
     * lalu menyimpan dan mengembalikan path file dari cache server.
     */
    public function generateShipmentZip(Shipment $shipment, string $quality = 'compressed'): ?string
    {
        $shipment->loadMissing(['evidenceItems.sopPhotoPoint', 'photos', 'extraPhotos']);

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
            return null;
        }

        // 3. Cek apakah file ZIP sudah tersedia di cache server
        $cacheDir = storage_path('app/zip_cache');
        if (! File::isDirectory($cacheDir)) {
            File::makeDirectory($cacheDir, 0755, true);
        }

        $cacheToken = md5(($shipment->updated_at?->timestamp ?? '0').'_'.$evidenceList->count().'_'.$quality);
        $cachedZipPath = $cacheDir.DIRECTORY_SEPARATOR."{$shipment->id}_{$cacheToken}_{$safeZipName}";

        if (file_exists($cachedZipPath) && filesize($cachedZipPath) > 0) {
            return $cachedZipPath;
        }

        // Urutkan berdasarkan urutan titik
        $sorted = $evidenceList->sortBy(fn ($item) => $item['point_no'] ?? 999);

        // Buat file ZIP sementara
        $tempZipPath = tempnam(sys_get_temp_dir(), 'spv_zip_');
        $zip = new ZipArchive;

        if ($zip->open($tempZipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            return null;
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
            @unlink($tempZipPath);

            return $cachedZipPath;
        }

        return null;
    }

    /**
     * Download sekumpulan shipment (Batch ZIP) terorganisir per folder:
     * {TAHUN}/{BULAN}/{HARI}/{PACKING_LIST}.zip
     */
    public function downloadBatchZip(Request $request)
    {
        @ini_set('memory_limit', '1024M');
        @set_time_limit(300);

        $query = $this->buildBatchShipmentsQuery($request);
        $shipments = $query->with(['evidenceItems.sopPhotoPoint', 'photos', 'extraPhotos'])->get();

        if ($shipments->isEmpty()) {
            return back()->with('error', 'Tidak ditemukan data shipment dengan bukti foto pada kriteria filter yang dipilih.');
        }

        $quality = $request->input('quality', 'compressed');

        $indonesianMonths = [
            1 => 'JANUARI',
            2 => 'FEBRUARI',
            3 => 'MARET',
            4 => 'APRIL',
            5 => 'MEI',
            6 => 'JUNI',
            7 => 'JULI',
            8 => 'AGUSTUS',
            9 => 'SEPTEMBER',
            10 => 'OKTOBER',
            11 => 'NOVEMBER',
            12 => 'DESEMBER',
        ];

        $masterZipPath = tempnam(sys_get_temp_dir(), 'spv_batch_');
        $masterZip = new ZipArchive;

        if ($masterZip->open($masterZipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            return back()->with('error', 'Gagal membuat file arsip batch ZIP di server.');
        }

        $usedEntries = [];
        $filesAdded = 0;

        foreach ($shipments as $shipment) {
            $singleZipPath = $this->generateShipmentZip($shipment, $quality);
            if (! $singleZipPath || ! file_exists($singleZipPath)) {
                continue;
            }

            $date = $shipment->tanggal_staging ? Carbon::parse($shipment->tanggal_staging) : ($shipment->created_at ?: now());
            $year = $date->format('Y');
            $monthNum = (int) $date->format('n');
            $monthName = $indonesianMonths[$monthNum] ?? strtoupper($date->format('F'));
            $day = (string) (int) $date->format('j');

            $packingList = trim((string) ($shipment->packing_list_no ?? ''));
            $zipBase = $packingList !== '' ? $packingList : ($shipment->shipment_no ?: ('shipment-'.$shipment->id));
            $safeZipName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $zipBase).'.zip';

            $productFolder = strtoupper((string) ($shipment->jenis_produk ?: 'FIBER'));
            $subFolder = "{$productFolder}/{$year}/{$monthName}/{$day}";
            $baseName = pathinfo($safeZipName, PATHINFO_FILENAME);

            $entryKey = "{$subFolder}/{$safeZipName}";
            if (isset($usedEntries[$entryKey])) {
                $usedEntries[$entryKey]++;
                $entryName = "{$subFolder}/{$baseName}_{$usedEntries[$entryKey]}.zip";
            } else {
                $usedEntries[$entryKey] = 1;
                $entryName = $entryKey;
            }

            $masterZip->addFile($singleZipPath, $entryName);
            $filesAdded++;
        }

        $masterZip->close();

        if ($filesAdded === 0) {
            @unlink($masterZipPath);

            return back()->with('error', 'Tidak ada file bukti foto yang dapat dimasukkan ke dalam arsip ZIP.');
        }

        $masterFileName = $this->getBatchZipFileName($request, $indonesianMonths);

        return response()->download($masterZipPath, $masterFileName, [
            'Content-Type' => 'application/zip',
        ])->deleteFileAfterSend(true);
    }

    /**
     * Endpoint API Preview: Mengecek jumlah data shipment dan contoh struktur folder sebelum diunduh.
     */
    public function previewBatchZip(Request $request)
    {
        $query = $this->buildBatchShipmentsQuery($request);
        $count = $query->count();

        $indonesianMonths = [
            1 => 'JANUARI',
            2 => 'FEBRUARI',
            3 => 'MARET',
            4 => 'APRIL',
            5 => 'MEI',
            6 => 'JUNI',
            7 => 'JULI',
            8 => 'AGUSTUS',
            9 => 'SEPTEMBER',
            10 => 'OKTOBER',
            11 => 'NOVEMBER',
            12 => 'DESEMBER',
        ];

        $masterFileName = $this->getBatchZipFileName($request, $indonesianMonths);

        $samplePath = '-';
        if ($count > 0) {
            $sampleShipment = (clone $query)->first();
            if ($sampleShipment) {
                $date = $sampleShipment->tanggal_staging ? Carbon::parse($sampleShipment->tanggal_staging) : ($sampleShipment->created_at ?: now());
                $year = $date->format('Y');
                $monthNum = (int) $date->format('n');
                $monthName = $indonesianMonths[$monthNum] ?? strtoupper($date->format('F'));
                $day = (string) (int) $date->format('j');
                $pl = $sampleShipment->packing_list_no ?: ($sampleShipment->nomor_container_atau_plat ?: ('SPV-'.$sampleShipment->id));
                $productFolder = strtoupper((string) ($sampleShipment->jenis_produk ?: 'FIBER'));
                $safePl = preg_replace('/[^A-Za-z0-9_\-]/', '_', $pl).'.zip';
                $samplePath = "{$productFolder}/{$year}/{$monthName}/{$day}/{$safePl}";
            }
        }

        $quality = $request->input('quality', 'compressed');
        $avgMb = ($quality === 'original') ? 35 : 5.5;
        $estMb = round($count * $avgMb, 1);
        $estSize = $count === 0 ? '0 MB' : ($estMb >= 1000 ? round($estMb / 1024, 2).' GB' : "±{$estMb} MB");

        return response()->json([
            'count' => $count,
            'sample_path' => $samplePath,
            'estimated_size' => $estSize,
            'filename' => $masterFileName,
        ]);
    }

    /**
     * Membangun query Eloquent untuk shipment sesuai filter periode dan produk.
     */
    protected function buildBatchShipmentsQuery(Request $request)
    {
        $query = Shipment::query();

        // Hanya ambil shipment yang memiliki berkas foto atau bukti evidence
        $query->where(function ($q) {
            $q->has('evidenceItems')->orHas('photos');
        });

        $mode = $request->input('mode', 'harian');
        $product = $request->input('jenis_produk', 'all');

        if ($product && in_array($product, ['fiber', 'sodium'])) {
            $query->where('jenis_produk', $product);
        }

        if ($mode === 'harian') {
            $targetDate = $request->input('date');
            if (! $targetDate && $request->filled('year') && $request->filled('month') && $request->filled('day')) {
                $targetDate = sprintf('%04d-%02d-%02d', (int) $request->input('year'), (int) $request->input('month'), (int) $request->input('day'));
            }
            $targetDate = $targetDate ? Carbon::parse($targetDate)->toDateString() : now()->toDateString();

            $query->where(function ($q) use ($targetDate) {
                $q->whereDate('tanggal_staging', $targetDate)
                    ->orWhere(fn ($sq) => $sq->whereNull('tanggal_staging')->whereDate('created_at', $targetDate));
            });
        } elseif ($mode === 'range' || $mode === 'mingguan') {
            $startDate = $request->input('start_date') ? Carbon::parse($request->input('start_date'))->toDateString() : now()->startOfWeek()->toDateString();
            $endDate = $request->input('end_date') ? Carbon::parse($request->input('end_date'))->toDateString() : now()->endOfWeek()->toDateString();

            if ($startDate > $endDate) {
                [$startDate, $endDate] = [$endDate, $startDate];
            }

            $query->where(function ($q) use ($startDate, $endDate) {
                $q->where(function ($sq) use ($startDate, $endDate) {
                    $sq->whereDate('tanggal_staging', '>=', $startDate)
                        ->whereDate('tanggal_staging', '<=', $endDate);
                })->orWhere(function ($sq) use ($startDate, $endDate) {
                    $sq->whereNull('tanggal_staging')
                        ->whereDate('created_at', '>=', $startDate)
                        ->whereDate('created_at', '<=', $endDate);
                });
            });
        } elseif ($mode === 'bulanan') {
            $year = (int) ($request->input('year') ?: now()->year);
            $month = (int) ($request->input('month') ?: now()->month);

            $query->where(function ($q) use ($year, $month) {
                $q->where(function ($sq) use ($year, $month) {
                    $sq->whereYear('tanggal_staging', $year)
                        ->whereMonth('tanggal_staging', $month);
                })->orWhere(function ($sq) use ($year, $month) {
                    $sq->whereNull('tanggal_staging')
                        ->whereYear('created_at', $year)
                        ->whereMonth('created_at', $month);
                });
            });
        } elseif ($mode === 'tahunan') {
            $year = (int) ($request->input('year') ?: now()->year);

            $query->where(function ($q) use ($year) {
                $q->whereYear('tanggal_staging', $year)
                    ->orWhere(function ($sq) use ($year) {
                        $sq->whereNull('tanggal_staging')
                            ->whereYear('created_at', $year);
                    });
            });
        }

        return $query;
    }

    /**
     * Menghasilkan nama file yang deskriptif untuk Master ZIP.
     */
    protected function getBatchZipFileName(Request $request, array $indonesianMonths): string
    {
        $mode = $request->input('mode', 'harian');
        $product = $request->input('jenis_produk', 'all');
        $productLabel = match ($product) {
            'fiber' => '_Fiber',
            'sodium' => '_Sodium',
            default => '',
        };

        if ($mode === 'harian') {
            $targetDate = $request->input('date');
            if (! $targetDate && $request->filled('year') && $request->filled('month') && $request->filled('day')) {
                $targetDate = sprintf('%04d-%02d-%02d', (int) $request->input('year'), (int) $request->input('month'), (int) $request->input('day'));
            }
            $cDate = Carbon::parse($targetDate ?: now());
            $mName = $indonesianMonths[(int) $cDate->format('n')] ?? $cDate->format('M');

            return "SPV_Evidence_Harian_{$cDate->format('Y')}_{$mName}_{$cDate->format('j')}{$productLabel}.zip";
        }

        if ($mode === 'range' || $mode === 'mingguan') {
            $sDate = Carbon::parse($request->input('start_date') ?: now()->startOfWeek())->format('Ymd');
            $eDate = Carbon::parse($request->input('end_date') ?: now()->endOfWeek())->format('Ymd');

            return "SPV_Evidence_Range_{$sDate}_sd_{$eDate}{$productLabel}.zip";
        }

        if ($mode === 'bulanan') {
            $year = (int) ($request->input('year') ?: now()->year);
            $month = (int) ($request->input('month') ?: now()->month);
            $mName = $indonesianMonths[$month] ?? "Bulan_{$month}";

            return "SPV_Evidence_Bulanan_{$year}_{$mName}{$productLabel}.zip";
        }

        if ($mode === 'tahunan') {
            $year = (int) ($request->input('year') ?: now()->year);

            return "SPV_Evidence_Tahunan_{$year}{$productLabel}.zip";
        }

        return "SPV_Evidence_Batch{$productLabel}.zip";
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
