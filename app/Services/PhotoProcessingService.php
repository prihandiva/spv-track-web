<?php

namespace App\Services;

use App\Models\ShipmentPhoto;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class PhotoProcessingService
{
    protected string $disk = 'local'; // Maps to storage/app/private

    /**
     * Memproses upload sementara:
     * 1. Hitung SHA-256 dan ukuran file asli.
     * 2. Koreksi rotasi EXIF.
     * 3. Tempelkan (burn-in) jam server Asia/Jakarta format dd/MM/yyyy HH:mm:ss WIB.
     * 4. Buat thumbnail terkompresi untuk listing ringan.
     * 5. Simpan ke private disk folder temp_photos/{temp_id}.
     */
    public function processTemporaryUpload(UploadedFile $file, array $attributes, ?int $userId = null): array
    {
        $tempId = (string) Str::uuid();
        $tempDir = "temp_photos/{$tempId}";

        // 1. Ekstrak captured_at dari EXIF atau input pengguna
        $filePath = $file->getRealPath();
        $exif = @exif_read_data($filePath);
        $capturedAt = null;

        if (! empty($attributes['captured_at'])) {
            try {
                $capturedAt = Carbon::parse($attributes['captured_at'])->setTimezone('Asia/Jakarta');
            } catch (\Throwable $e) {
                // Ignore parse error
            }
        }

        if (! $capturedAt && ! empty($exif['DateTimeOriginal'])) {
            try {
                $capturedAt = Carbon::createFromFormat('Y:m:d H:i:s', $exif['DateTimeOriginal'], 'Asia/Jakarta');
            } catch (\Throwable $e) {
                // Ignore parse error
            }
        }

        if (! $capturedAt) {
            $capturedAt = Carbon::now('Asia/Jakarta');
        }

        // Jam server saat ini (Asia/Jakarta)
        $stampedAt = Carbon::now('Asia/Jakarta');
        $stampedAtFormatted = $stampedAt->format('d/m/Y H:i:s').' WIB';

        // 2. Info file asli
        $sha256 = hash_file('sha256', $filePath);
        $size = filesize($filePath);
        $mime = $file->getMimeType() ?: 'image/jpeg';

        // Simpan file asli tanpa perubahan sama sekali
        $originalRelPath = "{$tempDir}/original.jpg";
        Storage::disk($this->disk)->put($originalRelPath, file_get_contents($filePath));

        // 3. Load image resource di GD untuk burn-in dan thumbnail
        $imageResource = $this->createImageResource($filePath, $mime);
        if (! $imageResource) {
            throw new RuntimeException('Format gambar tidak didukung atau file gambar rusak.');
        }

        // Koreksi orientasi EXIF jika ada
        $imageResource = $this->fixExifOrientation($imageResource, $exif);

        // 4. Burn-in timestamp
        $pointLabel = $attributes['point_label'] ?? 'Evidence';
        $pointNo = isset($attributes['point_no']) && $attributes['point_no'] !== null ? (int) $attributes['point_no'] : null;
        $isExtra = (bool) ($attributes['is_extra'] ?? false);

        $stampedResource = $this->applyBurnInStamp(
            $imageResource,
            $stampedAtFormatted,
            $pointNo,
            $pointLabel,
            $isExtra,
            $capturedAt ? $capturedAt->format('d/m/Y H:i:s').' WIB' : null
        );

        // Simpan stamped image
        $stampedRelPath = "{$tempDir}/stamped.jpg";
        $stampedTempFile = tempnam(sys_get_temp_dir(), 'stmp_');
        imagejpeg($stampedResource, $stampedTempFile, 88);
        Storage::disk($this->disk)->put($stampedRelPath, file_get_contents($stampedTempFile));
        @unlink($stampedTempFile);

        // 5. Generate thumbnail (max 500x500 mempertahankan rasio)
        $thumbnailResource = $this->createThumbnail($stampedResource, 500);
        $thumbnailRelPath = "{$tempDir}/thumbnail.jpg";
        $thumbTempFile = tempnam(sys_get_temp_dir(), 'thmb_');
        imagejpeg($thumbnailResource, $thumbTempFile, 80);
        Storage::disk($this->disk)->put($thumbnailRelPath, file_get_contents($thumbTempFile));
        @unlink($thumbTempFile);

        // Bersihkan GD memory
        imagedestroy($imageResource);
        imagedestroy($stampedResource);
        imagedestroy($thumbnailResource);

        // 6. Simpan metadata ke disk temp
        $meta = [
            'temp_id' => $tempId,
            'point_no' => $pointNo,
            'point_label' => $pointLabel,
            'is_extra' => $isExtra,
            'mime' => $mime,
            'size' => $size,
            'sha256' => $sha256,
            'captured_at' => $capturedAt->toIso8601String(),
            'stamped_at' => $stampedAt->toIso8601String(),
            'stamped_at_formatted' => $stampedAtFormatted,
            'uploaded_by' => $userId,
            'created_at' => now()->toIso8601String(),
        ];

        Storage::disk($this->disk)->put("{$tempDir}/metadata.json", json_encode($meta, JSON_PRETTY_PRINT));

        return [
            'temp_id' => $tempId,
            'point_no' => $pointNo,
            'point_label' => $pointLabel,
            'is_extra' => $isExtra,
            'captured_at' => $capturedAt->format('d/m/Y H:i:s').' WIB',
            'stamped_at' => $stampedAtFormatted,
            'size' => $size,
            'sha256' => $sha256,
            'preview_url' => route('photos.temp.file', ['tempId' => $tempId, 'type' => 'stamped']),
            'original_url' => route('photos.temp.file', ['tempId' => $tempId, 'type' => 'original']),
            'thumbnail_url' => route('photos.temp.file', ['tempId' => $tempId, 'type' => 'thumbnail']),
        ];
    }

    /**
     * Memindahkan foto sementara ke penyimpanan permanen shipment dan mencatat ke database.
     */
    public function promoteTempToPermanent(
        string $tempId,
        int $shipmentId,
        ?int $pointNo,
        string $pointLabel,
        bool $isExtra,
        ?int $userId,
        ?string $ocrText = null
    ): ShipmentPhoto {
        $meta = $this->getTempMetadata($tempId);
        if (! $meta) {
            throw new RuntimeException("Data upload sementara dengan ID {$tempId} tidak ditemukan atau sudah kadaluarsa.");
        }

        $permDir = "shipments/{$shipmentId}/photos";
        $safePrefix = $isExtra ? "extra_{$tempId}" : "p{$pointNo}_{$tempId}";

        $permOriginalPath = "{$permDir}/{$safePrefix}_original.jpg";
        $permStampedPath = "{$permDir}/{$safePrefix}_stamped.jpg";
        $permThumbPath = "{$permDir}/{$safePrefix}_thumb.jpg";

        $tempOriginal = "temp_photos/{$tempId}/original.jpg";
        $tempStamped = "temp_photos/{$tempId}/stamped.jpg";
        $tempThumb = "temp_photos/{$tempId}/thumbnail.jpg";

        // Pastikan file temp ada
        if (! Storage::disk($this->disk)->exists($tempOriginal) || ! Storage::disk($this->disk)->exists($tempStamped)) {
            throw new RuntimeException("File foto sementara {$tempId} tidak lengkap.");
        }

        // Pindahkan file ke lokasi permanen
        Storage::disk($this->disk)->move($tempOriginal, $permOriginalPath);
        Storage::disk($this->disk)->move($tempStamped, $permStampedPath);

        if (Storage::disk($this->disk)->exists($tempThumb)) {
            Storage::disk($this->disk)->move($tempThumb, $permThumbPath);
        } else {
            $permThumbPath = null;
        }

        // Buat record database permanen (immutable)
        $photo = ShipmentPhoto::create([
            'shipment_id' => $shipmentId,
            'point_no' => $pointNo,
            'point_label' => $pointLabel,
            'is_extra' => $isExtra,
            'path' => $permOriginalPath,
            'stamped_path' => $permStampedPath,
            'thumbnail_path' => $permThumbPath,
            'mime' => $meta['mime'] ?? 'image/jpeg',
            'size' => $meta['size'] ?? 0,
            'sha256' => $meta['sha256'] ?? '',
            'captured_at' => ! empty($meta['captured_at']) ? Carbon::parse($meta['captured_at']) : null,
            'stamped_at' => ! empty($meta['stamped_at']) ? Carbon::parse($meta['stamped_at']) : now(),
            'uploaded_by' => $userId ?? ($meta['uploaded_by'] ?? null),
            'ocr_text' => $ocrText,
        ]);

        // Bersihkan sisa direktori temp
        Storage::disk($this->disk)->deleteDirectory("temp_photos/{$tempId}");

        return $photo;
    }

    /**
     * Mengambil metadata upload sementara.
     */
    public function getTempMetadata(string $tempId): ?array
    {
        $metaPath = "temp_photos/{$tempId}/metadata.json";
        if (! Storage::disk($this->disk)->exists($metaPath)) {
            return null;
        }

        $content = Storage::disk($this->disk)->get($metaPath);

        return json_decode($content, true);
    }

    /**
     * Mengambil path absolut file sementara di disk private.
     */
    public function getTempFilePath(string $tempId, string $type = 'stamped'): ?string
    {
        $type = match ($type) {
            'original' => 'original.jpg',
            'thumbnail' => 'thumbnail.jpg',
            default => 'stamped.jpg',
        };

        $relPath = "temp_photos/{$tempId}/{$type}";
        if (! Storage::disk($this->disk)->exists($relPath)) {
            return null;
        }

        return Storage::disk($this->disk)->path($relPath);
    }

    /**
     * Menghapus upload sementara secara manual.
     */
    public function deleteTempUpload(string $tempId): void
    {
        Storage::disk($this->disk)->deleteDirectory("temp_photos/{$tempId}");
    }

    /**
     * Membersihkan upload sementara yang sudah kadaluarsa (default 24 jam).
     */
    public function cleanExpiredTempUploads(int $hoursOld = 24): int
    {
        $count = 0;
        $allTempDirs = Storage::disk($this->disk)->directories('temp_photos');
        $cutoff = Carbon::now()->subHours($hoursOld);

        foreach ($allTempDirs as $dir) {
            $metaPath = "{$dir}/metadata.json";
            if (Storage::disk($this->disk)->exists($metaPath)) {
                $meta = json_decode(Storage::disk($this->disk)->get($metaPath), true);
                $createdAt = ! empty($meta['created_at']) ? Carbon::parse($meta['created_at']) : null;

                if ($createdAt && $createdAt->lt($cutoff)) {
                    Storage::disk($this->disk)->deleteDirectory($dir);
                    $count++;
                }
            } else {
                // Jika tidak ada metadata dan foldernya lebih lama dari cutoff waktu modifikasi
                $fullPath = Storage::disk($this->disk)->path($dir);
                if (file_exists($fullPath) && filemtime($fullPath) < $cutoff->timestamp) {
                    Storage::disk($this->disk)->deleteDirectory($dir);
                    $count++;
                }
            }
        }

        return $count;
    }

    /**
     * Buat image GD resource berdasarkan file dan MIME type.
     */
    protected function createImageResource(string $filePath, string $mime)
    {
        return match (true) {
            str_contains($mime, 'jpeg') || str_contains($mime, 'jpg') => @imagecreatefromjpeg($filePath),
            str_contains($mime, 'png') => @imagecreatefrompng($filePath),
            str_contains($mime, 'webp') => @imagecreatefromwebp($filePath),
            default => @imagecreatefromstring(file_get_contents($filePath)),
        };
    }

    /**
     * Koreksi rotasi foto dari tag EXIF Orientation.
     */
    protected function fixExifOrientation($image, ?array $exif)
    {
        if (empty($exif['Orientation'])) {
            return $image;
        }

        return match ($exif['Orientation']) {
            3 => imagerotate($image, 180, 0),
            6 => imagerotate($image, -90, 0),
            8 => imagerotate($image, 90, 0),
            default => $image,
        };
    }

    /**
     * Stempel file gambar langsung pada disk dengan koreksi orientasi EXIF & ukuran font proporsional resolusi kamera.
     */
    public function watermarkSingleFile(
        string $sourcePath,
        string $targetPath,
        string $stampText,
        ?string $title = null,
        ?string $subText = null
    ): void {
        $ext = strtolower(pathinfo($sourcePath, PATHINFO_EXTENSION));
        $exif = @exif_read_data($sourcePath);

        $img = $this->createImageResource($sourcePath, 'image/'.($ext === 'jpg' ? 'jpeg' : $ext));
        if (! $img) {
            copy($sourcePath, $targetPath);

            return;
        }

        // 1. Koreksi orientasi kamera ponsel (EXIF tag 3, 6, 8) agar tidak miring/terbalik
        $img = $this->fixExifOrientation($img, $exif);

        $w = imagesx($img);
        $h = imagesy($img);
        $minDim = min($w, $h);

        // 2. Skala proporsional terhadap resolusi foto kamera:
        // Pada foto kamera 3000-4000px, font mencapai 96-120pt sehingga tetap besar dan terbaca jelas!
        $fontSizeTime = max(20, (int) ($minDim * 0.034));
        $fontSizeTitle = max(14, (int) ($fontSizeTime * 0.70));
        $barHeight = (int) max(88, $minDim * 0.125);
        $barY = $h - $barHeight;
        $accentHeight = (int) max(4, $minDim * 0.005);

        // 3. Banner hitam transparan di bagian bawah foto
        $darkBar = imagecolorallocatealpha($img, 10, 15, 26, 30);
        imagefilledrectangle($img, 0, $barY, $w, $h, $darkBar);

        // Garis aksen biru SPV
        $accentColor = imagecolorallocate($img, 37, 99, 235);
        imagefilledrectangle($img, 0, $barY, $w, $barY + $accentHeight, $accentColor);

        // Warna teks
        $white = imagecolorallocate($img, 255, 255, 255);
        $gold = imagecolorallocate($img, 250, 204, 21); // Kuning emas untuk jam
        $lightGray = imagecolorallocate($img, 229, 231, 235);
        $blackShadow = imagecolorallocate($img, 0, 0, 0);

        $line1 = $title ? 'SPV-TRACK EVIDENCE  |  '.mb_strtoupper($title) : 'SPV-TRACK EVIDENCE';
        if ($subText) {
            $line1 .= '  |  '.$subText;
        }
        $line2 = 'DIAMBIL PADA: '.$stampText;

        $fontFile = null;
        $candidateFonts = [
            resource_path('fonts/bold.ttf'),
            resource_path('fonts/regular.ttf'),
            'C:/Windows/Fonts/arialbd.ttf',
            'C:/Windows/Fonts/arial.ttf',
            'C:/Windows/Fonts/calibrib.ttf',
            '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf',
        ];

        foreach ($candidateFonts as $f) {
            if (file_exists($f)) {
                $fontFile = $f;
                break;
            }
        }

        if ($fontFile && function_exists('imagettftext')) {
            $paddingX = (int) max(20, $minDim * 0.03);
            $yLine1 = $barY + $accentHeight + (int) ($barHeight * 0.35);
            $yLine2 = $barY + $accentHeight + (int) ($barHeight * 0.74);
            $shadowOffset = max(2, (int) ($fontSizeTime * 0.05));

            // Shadow teks agar kontras tinggi
            imagettftext($img, $fontSizeTitle, 0, $paddingX + $shadowOffset, $yLine1 + $shadowOffset, $blackShadow, $fontFile, $line1);
            imagettftext($img, $fontSizeTitle, 0, $paddingX, $yLine1, $lightGray, $fontFile, $line1);

            imagettftext($img, $fontSizeTime, 0, $paddingX + $shadowOffset, $yLine2 + $shadowOffset, $blackShadow, $fontFile, $line2);
            imagettftext($img, $fontSizeTime, 0, $paddingX, $yLine2, $gold, $fontFile, $line2);
        } else {
            $paddingX = 16;
            $yLine1 = $barY + 14;
            $yLine2 = $barY + 45;
            imagestring($img, 4, $paddingX, $yLine1, $line1, $white);
            imagestring($img, 5, $paddingX, $yLine2, $line2, $gold);
        }

        imagejpeg($img, $targetPath, 88);
        imagedestroy($img);
    }

    /**
     * Menempelkan (burn-in) banner stempel jam server dan metadata titik ke gambar.
     */
    protected function applyBurnInStamp(
        $sourceImage,
        string $stampedAtFormatted,
        ?int $pointNo,
        string $pointLabel,
        bool $isExtra,
        ?string $capturedAtFormatted
    ) {
        $w = imagesx($sourceImage);
        $h = imagesy($sourceImage);
        $minDim = min($w, $h);

        // Buat kanvas duplikat
        $canvas = imagecreatetruecolor($w, $h);
        imagecopy($canvas, $sourceImage, 0, 0, 0, 0, $w, $h);

        // Hitung skala font & banner berdasarkan resolusi kamera (proporsional 3.4% dari sisi terkecil)
        $fontSizeTime = max(20, (int) ($minDim * 0.034));
        $fontSizeTitle = max(14, (int) ($fontSizeTime * 0.70));
        $barHeight = (int) max(88, $minDim * 0.125);
        $barY = $h - $barHeight;
        $accentHeight = (int) max(4, $minDim * 0.005);

        // Gambar banner hitam transparan di bagian bawah foto
        // Alpha GD: 0 = solid, 127 = transparan penuh. 30 = ~75% solid
        $darkBar = imagecolorallocatealpha($canvas, 10, 15, 26, 30);
        imagefilledrectangle($canvas, 0, $barY, $w, $h, $darkBar);

        // Garis aksen biru/hijau di tepi atas banner
        $accentColor = imagecolorallocate($canvas, 37, 99, 235); // SPV blue
        imagefilledrectangle($canvas, 0, $barY, $w, $barY + $accentHeight, $accentColor);

        // Warna teks
        $white = imagecolorallocate($canvas, 255, 255, 255);
        $gold = imagecolorallocate($canvas, 250, 204, 21); // Amber / gold untuk jam
        $lightGray = imagecolorallocate($canvas, 229, 231, 235);
        $blackShadow = imagecolorallocate($canvas, 0, 0, 0);

        // Format teks stempel
        $tagTitle = $isExtra ? '[FOTO EKSTRA] '.mb_strtoupper($pointLabel) : "[TITIK {$pointNo}] ".mb_strtoupper($pointLabel);
        $line1 = 'SPV-TRACK EVIDENCE  |  '.$tagTitle;
        $line2 = 'JAM SERVER : '.$stampedAtFormatted;
        if ($capturedAtFormatted) {
            $line2 .= '  |  AMBIL : '.$capturedAtFormatted;
        }

        // Cari font TTF sistem atau gunakan font yang dibundel
        $fontFile = null;
        $candidateFonts = [
            resource_path('fonts/bold.ttf'),
            resource_path('fonts/regular.ttf'),
            'C:/Windows/Fonts/arialbd.ttf',
            'C:/Windows/Fonts/arial.ttf',
            'C:/Windows/Fonts/calibrib.ttf',
            '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf',
        ];

        foreach ($candidateFonts as $f) {
            if (file_exists($f)) {
                $fontFile = $f;
                break;
            }
        }

        if ($fontFile && function_exists('imagettftext')) {
            $paddingX = (int) max(20, $minDim * 0.03);
            $yLine1 = $barY + $accentHeight + (int) ($barHeight * 0.35);
            $yLine2 = $barY + $accentHeight + (int) ($barHeight * 0.74);
            $shadowOffset = max(2, (int) ($fontSizeTime * 0.05));

            // Render Line 1 (Tag & Title) dengan shadow
            imagettftext($canvas, $fontSizeTitle, 0, $paddingX + $shadowOffset, $yLine1 + $shadowOffset, $blackShadow, $fontFile, $line1);
            imagettftext($canvas, $fontSizeTitle, 0, $paddingX, $yLine1, $lightGray, $fontFile, $line1);

            // Render Line 2 (Timestamp Utama - Bold & Emas/Kuning terang) dengan shadow
            imagettftext($canvas, $fontSizeTime, 0, $paddingX + $shadowOffset, $yLine2 + $shadowOffset, $blackShadow, $fontFile, $line2);
            imagettftext($canvas, $fontSizeTime, 0, $paddingX, $yLine2, $gold, $fontFile, $line2);
        } else {
            // Fallback GD built-in font jika TTF tidak ditemukan
            $paddingX = 16;
            $yLine1 = $barY + 14;
            $yLine2 = $barY + 45;

            imagestring($canvas, 4, $paddingX, $yLine1, $line1, $white);
            imagestring($canvas, 5, $paddingX, $yLine2, $line2, $gold);
        }

        return $canvas;
    }

    /**
     * Membuat thumbnail dengan kompresi dan dimensi maksimum yang ditentukan.
     */
    protected function createThumbnail($sourceImage, int $maxDimension = 500)
    {
        $w = imagesx($sourceImage);
        $h = imagesy($sourceImage);

        if ($w <= $maxDimension && $h <= $maxDimension) {
            $newW = $w;
            $newH = $h;
        } elseif ($w > $h) {
            $newW = $maxDimension;
            $newH = (int) round(($h * $maxDimension) / $w);
        } else {
            $newH = $maxDimension;
            $newW = (int) round(($w * $maxDimension) / $h);
        }

        $thumb = imagecreatetruecolor($newW, $newH);
        imagecopyresampled($thumb, $sourceImage, 0, 0, 0, 0, $newW, $newH, $w, $h);

        return $thumb;
    }
}
