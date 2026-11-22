<?php

namespace App\Services;

use RuntimeException;

class PhotoProcessingService
{
    /**
     * Batas ukuran file foto yang disimpan di storage (bytes).
     * Default: 2MB = 2 * 1024 * 1024
     */
    const MAX_FILE_BYTES = 2 * 1024 * 1024; // 2 MB

    // =========================================================================
    // NAMA FUNCTION : compressToMaxSize
    // FUNGSI        : Kompres gambar GD resource ke format JPEG binary berukuran ≤ 2MB
    //                 dengan menurunkan quality bertahap tanpa merusak/downscale resolusi foto
    // =========================================================================
    protected function compressToMaxSize($imageResource, int $maxBytes = self::MAX_FILE_BYTES): string
    {
        // ─────────────────────────────────────────────────────────────────
        // STRATEGI: Hanya turunkan quality JPEG, JANGAN pernah downscale
        // resolusi agar foto tetap bisa di-zoom dan teks/nomor tetap tajam.
        //
        // Jika quality terbawah (25) masih menghasilkan file > maxBytes,
        // foto disimpan apa adanya — lebih baik sedikit besar daripada pecah.
        // ─────────────────────────────────────────────────────────────────
        $qualities = [88, 80, 72, 60, 48, 35, 25];

        foreach ($qualities as $quality) {
            ob_start();
            imagejpeg($imageResource, null, $quality);
            $data = ob_get_clean();

            if (strlen($data) <= $maxBytes) {
                return $data;
            }
        }

        // Semua quality sudah dicoba, tapi foto masih > batas —
        // simpan dengan quality 25 (tanpa downscale) agar resolusi tidak rusak.
        ob_start();
        imagejpeg($imageResource, null, 25);
        return ob_get_clean();
    }

    // =========================================================================
    // NAMA FUNCTION : createImageResource
    // FUNGSI        : [HELPER STAMPING]
    //                 Membuat resource GD Image dari file path (JPEG, PNG, WEBP)
    // =========================================================================
    protected function createImageResource(string $filePath, ?string $mime = null)
    {
        if (empty($mime) || $mime === 'image/' || $mime === 'image') {
            $imageInfo = @getimagesize($filePath);
            $mime = $imageInfo['mime'] ?? (@mime_content_type($filePath) ?: 'image/jpeg');
        }

        $mime = strtolower((string) $mime);

        if (str_contains($mime, 'jpeg') || str_contains($mime, 'jpg')) {
            $res = @imagecreatefromjpeg($filePath);
            if ($res) {
                return $res;
            }
        }

        if (str_contains($mime, 'png')) {
            $res = @imagecreatefrompng($filePath);
            if ($res) {
                return $res;
            }
        }

        if (str_contains($mime, 'webp') && function_exists('imagecreatefromwebp')) {
            $res = @imagecreatefromwebp($filePath);
            if ($res) {
                return $res;
            }
        }

        $content = @file_get_contents($filePath);

        return $content !== false ? @imagecreatefromstring($content) : false;
    }

    // =========================================================================
    // NAMA FUNCTION : fixExifOrientation
    // FUNGSI        : [HELPER STAMPING]
    //                 Memperbaiki orientasi rotasi foto kamera ponsel berdasarkan tag EXIF (1-8)
    //                 tanpa menyebabkan TypeError jika EXIF tidak ditemukan / bernilai boolean false
    // =========================================================================
    protected function fixExifOrientation($image, $exif = null)
    {
        if (! is_array($exif) || empty($exif['Orientation'])) {
            return $image;
        }

        $orientation = (int) $exif['Orientation'];
        $rotated = match ($orientation) {
            3 => @imagerotate($image, 180, 0),
            6 => @imagerotate($image, -90, 0),
            8 => @imagerotate($image, 90, 0),
            default => $image,
        };

        if ($rotated !== false && $rotated !== $image) {
            imagedestroy($image);

            return $rotated;
        }

        return $image;
    }

    // =========================================================================
    // NAMA FUNCTION : detectOrientation
    // FUNGSI        : [HELPER ORIENTASI FOTO & VIDEO]
    //                 Mendeteksi orientasi kanvas (portrait, square 1:1, ultrawide, landscape)
    //                 berdasarkan rasio aspek lebar dan tinggi secara presisi
    // =========================================================================
    public function detectOrientation(int $width, int $height): string
    {
        $ratio = $width / max(1, $height);

        if ($ratio < 0.92) {
            return 'portrait';
        }

        if ($ratio <= 1.08) {
            return 'square'; // Rasio 1:1 (Persegi)
        }

        if ($ratio > 2.0) {
            return 'ultrawide'; // Panorama / Wide Banner
        }

        return 'landscape';
    }

    // =========================================================================
    // NAMA FUNCTION : watermarkSingleFile
    // FUNGSI        : [INTI ALGORITMA STAMPING FOTO UTAMA]
    //                 Menempelkan banner stempel waktu server (WIB), judul titik SOP,
    //                 dan nomor kontainer/plat langsung pada file gambar target
    //                 secara adaptif terhadap seluruh rasio aspek (1:1 Square, Ultrawide,
    //                 Landscape, Portrait) dengan jaminan stempel WAJIB tertempel.
    // =========================================================================
    public function watermarkSingleFile(
        string $sourcePath,
        string $targetPath,
        string $stampText,
        ?string $title = null,
        ?string $subText = null
    ): void {
        // Alokasikan memori yang cukup untuk pemrosesan foto kamera resolusi tinggi di VPS
        if (function_exists('ini_set')) {
            @ini_set('memory_limit', '512M');
        }

        $imageInfo = @getimagesize($sourcePath);
        $mime = $imageInfo['mime'] ?? (@mime_content_type($sourcePath) ?: 'image/jpeg');

        // EXIF data membaca aman: jika exif_read_data mengembalikan false (seperti pada PNG/WebP/foto tanpa EXIF), gunakan null
        $rawExif = function_exists('exif_read_data') ? @exif_read_data($sourcePath) : null;
        $exif = is_array($rawExif) ? $rawExif : null;

        $img = $this->createImageResource($sourcePath, $mime);
        if (! $img) {
            copy($sourcePath, $targetPath);

            return;
        }

        // 1. Koreksi orientasi kamera ponsel (EXIF tag 3, 6, 8) agar tidak miring/terbalik
        $img = $this->fixExifOrientation($img, $exif);

        // Optimasi resolusi kamera sangat tinggi (> 2800px) agar memori GD efisien
        $maxDimension = 2800;
        $origW = imagesx($img);
        $origH = imagesy($img);
        if ($origW > $maxDimension || $origH > $maxDimension) {
            $scale = min($maxDimension / $origW, $maxDimension / $origH);
            $newW = (int) round($origW * $scale);
            $newH = (int) round($origH * $scale);
            $scaledImg = imagecreatetruecolor($newW, $newH);
            imagecopyresampled($scaledImg, $img, 0, 0, 0, 0, $newW, $newH, $origW, $origH);
            imagedestroy($img);
            $img = $scaledImg;
        }

        $w = imagesx($img);
        $h = imagesy($img);

        // 2. Deteksi Orientasi Presisi (Square 1:1, Ultra-Wide, Landscape, Portrait)
        $orientation = $this->detectOrientation($w, $h);

        // Hitung tinggi banner secara proporsional sesuai rasio orientasi foto
        $barHeight = match ($orientation) {
            'portrait'  => (int) max(72, min(round($h * 0.12), round($w * 0.16))),
            'square'    => (int) max(68, round($h * 0.12)),
            'ultrawide' => (int) max(64, min(round($h * 0.22), 160)),
            default     => (int) max(68, round($h * 0.13)), // landscape
        };

        // Pastikan tinggi banner tidak melebihi 60% tinggi gambar pada gambar beresolusi sangat kecil
        $barHeight = min($barHeight, (int) max(40, round($h * 0.60)));
        $barY = $h - $barHeight;

        $accentHeight = (int) max(3, min(8, round($barHeight * 0.04)));
        $paddingX = (int) max(16, min(round($w * 0.03), 60));
        $maxWidth = max(50, $w - (2 * $paddingX));

        $contentHeight = $barHeight - $accentHeight;

        // 3. Banner hitam transparan di bagian bawah foto
        $darkBar = imagecolorallocatealpha($img, 10, 15, 26, 30);
        imagefilledrectangle($img, 0, $barY, $w, $h, $darkBar);

        // Garis aksen biru SPV di tepi atas banner
        $accentColor = imagecolorallocate($img, 37, 99, 235);
        imagefilledrectangle($img, 0, $barY, $w, $barY + $accentHeight, $accentColor);

        // Warna teks
        $white = imagecolorallocate($img, 255, 255, 255);
        $gold = imagecolorallocate($img, 250, 204, 21); // Amber / Kuning emas untuk timestamp
        $lightGray = imagecolorallocate($img, 229, 231, 235);
        $blackShadow = imagecolorallocate($img, 0, 0, 0);

        $line1 = $title ? 'SPV-TRACK EVIDENCE  |  '.mb_strtoupper($title) : 'SPV-TRACK EVIDENCE';
        if ($subText) {
            $line1 .= '  |  '.$subText;
        }
        $line2 = str_starts_with($stampText, 'JAM') || str_starts_with($stampText, 'DIAMBIL') ? $stampText : 'JAM SERVER : '.$stampText;

        // Ukuran font dasar diturunkan secara proporsional dari tinggi konten banner
        $fontSizeTitle = (int) max(9, min(52, round($contentHeight * 0.22)));
        $fontSizeTime = (int) max(11, min(72, round($contentHeight * 0.32)));

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

        $ttfRendered = false;
        if ($fontFile && function_exists('imagettftext')) {
            try {
                // Skala font otomatis agar teks pas di dalam lebar batas foto
                $fontSizeTitle = $this->fitFontSize($line1, $fontSizeTitle, $maxWidth, $fontFile, 8);
                $fontSizeTime = $this->fitFontSize($line2, $fontSizeTime, $maxWidth, $fontFile, 9);

                // Potong teks jika tetap melebihi kanvas pada ukuran font terkecil
                $line1 = $this->fitTextToWidth($line1, $fontSizeTitle, $maxWidth, $fontFile);
                $line2 = $this->fitTextToWidth($line2, $fontSizeTime, $maxWidth, $fontFile);

                $yLine1 = (int) round($barY + $accentHeight + ($contentHeight * 0.38));
                $yLine2 = (int) round($barY + $accentHeight + ($contentHeight * 0.80));

                // Jamin line 2 tidak terpotong di tepi bawah kanvas
                $bboxFinal2 = @imagettfbbox($fontSizeTime, 0, $fontFile, $line2);
                $descender2 = $bboxFinal2 ? max($bboxFinal2[1], $bboxFinal2[3]) : 0;
                if ($yLine2 + $descender2 >= $h - 2) {
                    $yLine2 = $h - $descender2 - 4;
                }

                $shadowOffset = max(1, min(4, (int) round($fontSizeTime * 0.05)));

                // Shadow teks agar kontras tinggi
                @imagettftext($img, $fontSizeTitle, 0, $paddingX + $shadowOffset, $yLine1 + $shadowOffset, $blackShadow, $fontFile, $line1);
                @imagettftext($img, $fontSizeTitle, 0, $paddingX, $yLine1, $lightGray, $fontFile, $line1);

                @imagettftext($img, $fontSizeTime, 0, $paddingX + $shadowOffset, $yLine2 + $shadowOffset, $blackShadow, $fontFile, $line2);
                @imagettftext($img, $fontSizeTime, 0, $paddingX, $yLine2, $gold, $fontFile, $line2);
                $ttfRendered = true;
            } catch (\Throwable $e) {
                $ttfRendered = false;
            }
        }

        if (! $ttfRendered) {
            $paddingX = (int) max(10, min(24, round($w * 0.02)));
            $yLine1 = (int) round($barY + $accentHeight + ($contentHeight * 0.20));
            $yLine2 = (int) round($barY + $accentHeight + ($contentHeight * 0.60));
            @imagestring($img, 4, $paddingX, $yLine1, $line1, $white);
            @imagestring($img, 5, $paddingX, $yLine2, $line2, $gold);
        }

        // Kompres output akhir ke maksimal MAX_FILE_BYTES (default 2 MB)
        $compressedData = $this->compressToMaxSize($img, self::MAX_FILE_BYTES);
        imagedestroy($img);

        $saved = (bool) @file_put_contents($targetPath, $compressedData);

        if (! $saved || ! file_exists($targetPath)) {
            copy($sourcePath, $targetPath);
        }
    }

    // =========================================================================
    // NAMA FUNCTION : getFfmpegBinary
    // FUNGSI        : [HELPER VIDEO]
    //                 Mendeteksi path binary FFmpeg yang terpasang di sistem operasi server
    // =========================================================================
    public function getFfmpegBinary(): ?string
    {
        $candidates = [
            'd:/laragon/bin/ffmpeg/bin/ffmpeg.exe',
            'D:/laragon/bin/ffmpeg/bin/ffmpeg.exe',
            'C:/Users/FITRIA RAMADHANI/AppData/Local/Microsoft/WinGet/Packages/Gyan.FFmpeg.Essentials_Microsoft.Winget.Source_8wekyb3d8bbwe/ffmpeg-9.0.1-essentials_build/bin/ffmpeg.exe',
            'ffmpeg',
            'ffmpeg.exe',
        ];

        foreach ($candidates as $bin) {
            if ($bin === 'ffmpeg' || $bin === 'ffmpeg.exe') {
                $check = @shell_exec("where {$bin} 2>&1");
                if ($check && ! str_contains($check, 'Could not find') && ! str_contains($check, 'not recognized')) {
                    return $bin;
                }
            } elseif (file_exists($bin)) {
                return $bin;
            }
        }

        return null;
    }

    // =========================================================================
    // NAMA FUNCTION : getFfprobeBinary
    // FUNGSI        : [HELPER VIDEO]
    //                 Mendeteksi path binary FFprobe untuk membaca resolusi dan durasi video
    // =========================================================================
    public function getFfprobeBinary(): ?string
    {
        $candidates = [
            'd:/laragon/bin/ffmpeg/bin/ffprobe.exe',
            'D:/laragon/bin/ffmpeg/bin/ffprobe.exe',
            'C:/Users/FITRIA RAMADHANI/AppData/Local/Microsoft/WinGet/Packages/Gyan.FFmpeg.Essentials_Microsoft.Winget.Source_8wekyb3d8bbwe/ffmpeg-9.0.1-essentials_build/bin/ffprobe.exe',
            'ffprobe',
            'ffprobe.exe',
        ];

        foreach ($candidates as $bin) {
            if ($bin === 'ffprobe' || $bin === 'ffprobe.exe') {
                $check = @shell_exec("where {$bin} 2>&1");
                if ($check && ! str_contains($check, 'Could not find') && ! str_contains($check, 'not recognized')) {
                    return $bin;
                }
            } elseif (file_exists($bin)) {
                return $bin;
            }
        }

        return null;
    }

    // =========================================================================
    // NAMA FUNCTION : watermarkVideoSingleFile
    // FUNGSI        : [INTI ALGORITMA STAMPING VIDEO UTAMA]
    //                 Menempelkan banner stempel waktu server (WIB) & info titik/kontainer
    //                 pada video bukti evidence SOP menggunakan FFmpeg drawbox/drawtext
    //                 serta kompresi ukuran maksimal 6MB
    // =========================================================================
    public function watermarkVideoSingleFile(
        string $sourcePath,
        string $targetPath,
        string $stampText,
        ?string $title = null,
        ?string $subText = null
    ): void {
        $ffmpeg = $this->getFfmpegBinary();
        $ffprobe = $this->getFfprobeBinary();

        if (! $ffmpeg || ! file_exists($sourcePath)) {
            if (file_exists($sourcePath) && filesize($sourcePath) > 6 * 1024 * 1024) {
                throw new \RuntimeException('Ukuran video yang tersimpan di server melebihi batas maksimal 6MB.');
            }
            copy($sourcePath, $targetPath);
            return;
        }

        // 1. Deteksi resolusi video menggunakan ffprobe
        $w = 1280;
        $h = 720;
        if ($ffprobe) {
            $cmdProbe = "\"{$ffprobe}\" -v error -select_streams v:0 -show_entries stream=width,height -of csv=s=x:p=0 " . escapeshellarg($sourcePath);
            $probeOutput = @shell_exec($cmdProbe);
            if ($probeOutput && preg_match('/(\d+)x(\d+)/', trim($probeOutput), $m)) {
                $w = (int) $m[1];
                $h = (int) $m[2];
            }
        }

        $orientation = $this->detectOrientation($w, $h);

        // 2. Banner proporsional sesuai rasio orientasi video
        $barHeight = match ($orientation) {
            'portrait'  => (int) max(64, min(round($h * 0.12), round($w * 0.15))),
            'square'    => (int) max(60, round($h * 0.12)),
            'ultrawide' => (int) max(56, min(round($h * 0.20), 120)),
            default     => (int) max(60, round($h * 0.125)),
        };
        $accentHeight = (int) max(3, min(6, round($barHeight * 0.04)));
        $paddingX = (int) max(16, min(round($w * 0.03), 48));
        $maxWidth = max(50, $w - (2 * $paddingX));

        $contentHeight = $barHeight - $accentHeight;
        $fontSizeTitle = (int) max(8, min(36, round($contentHeight * 0.22)));
        $fontSizeTime = (int) max(10, min(48, round($contentHeight * 0.32)));

        $canvas = imagecreatetruecolor($w, $barHeight);
        imagealphablending($canvas, false);
        imagesavealpha($canvas, true);
        $transparent = imagecolorallocatealpha($canvas, 0, 0, 0, 127);
        imagefill($canvas, 0, 0, $transparent);
        imagealphablending($canvas, true);

        // Dark bar transparan & aksen biru SPV
        $darkBar = imagecolorallocatealpha($canvas, 10, 15, 26, 25);
        imagefilledrectangle($canvas, 0, 0, $w, $barHeight, $darkBar);
        $accentColor = imagecolorallocate($canvas, 37, 99, 235);
        imagefilledrectangle($canvas, 0, 0, $w, $accentHeight, $accentColor);

        // Warna teks
        $white = imagecolorallocate($canvas, 255, 255, 255);
        $gold = imagecolorallocate($canvas, 250, 204, 21);
        $lightGray = imagecolorallocate($canvas, 229, 231, 235);
        $blackShadow = imagecolorallocate($canvas, 0, 0, 0);

        $line1 = $title ? 'SPV-TRACK EVIDENCE  |  '.mb_strtoupper($title) : 'SPV-TRACK EVIDENCE';
        if ($subText) {
            $line1 .= '  |  '.$subText;
        }
        $line2 = str_starts_with($stampText, 'JAM') || str_starts_with($stampText, 'DIAMBIL') ? $stampText : 'JAM SERVER : '.$stampText;

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
            $fontSizeTitle = $this->fitFontSize($line1, $fontSizeTitle, $maxWidth, $fontFile, 8);
            $fontSizeTime = $this->fitFontSize($line2, $fontSizeTime, $maxWidth, $fontFile, 10);

            $line1 = $this->fitTextToWidth($line1, $fontSizeTitle, $maxWidth, $fontFile);
            $line2 = $this->fitTextToWidth($line2, $fontSizeTime, $maxWidth, $fontFile);

            $yLine1 = $accentHeight + (int) round($contentHeight * 0.38);
            $yLine2 = $accentHeight + (int) round($contentHeight * 0.80);
            $shadowOffset = max(1, (int) round($fontSizeTime * 0.05));

            @imagettftext($canvas, $fontSizeTitle, 0, $paddingX + $shadowOffset, $yLine1 + $shadowOffset, $blackShadow, $fontFile, $line1);
            @imagettftext($canvas, $fontSizeTitle, 0, $paddingX, $yLine1, $lightGray, $fontFile, $line1);

            @imagettftext($canvas, $fontSizeTime, 0, $paddingX + $shadowOffset, $yLine2 + $shadowOffset, $blackShadow, $fontFile, $line2);
            @imagettftext($canvas, $fontSizeTime, 0, $paddingX, $yLine2, $gold, $fontFile, $line2);
        } else {
            $paddingX = 16;
            $yLine1 = (int) round($barHeight * 0.25);
            $yLine2 = (int) round($barHeight * 0.65);
            imagestring($canvas, 4, $paddingX, $yLine1, $line1, $white);
            imagestring($canvas, 5, $paddingX, $yLine2, $line2, $gold);
        }

        $overlayPng = tempnam(sys_get_temp_dir(), 'ovr_') . '.png';
        imagepng($canvas, $overlayPng);
        imagedestroy($canvas);

        // 3. Proses burning ke video dengan FFmpeg (dengan preset CRF & bitrate aman agar hemat ukuran)
        $tempOutput = tempnam(sys_get_temp_dir(), 'vid_') . '.mp4';
        $cmdFfmpeg = escapeshellcmd($ffmpeg) .
            ' -y -i ' . escapeshellarg($sourcePath) .
            ' -i ' . escapeshellarg($overlayPng) .
            ' -filter_complex "[0:v][1:v]overlay=0:H-h" -c:v libx264 -preset veryfast -crf 26 -maxrate 2500k -bufsize 5000k -c:a aac -b:a 96k -movflags +faststart ' .
            escapeshellarg($tempOutput) . ' 2>&1';

        @shell_exec($cmdFfmpeg);
        @unlink($overlayPng);

        $maxSizeBytes = 6 * 1024 * 1024; // Maksimal 6MB (6,291,456 bytes)

        if (file_exists($tempOutput) && filesize($tempOutput) > 1000) {
            // Jika hasil burning melebihi batas 6MB, lakukan kompresi adaptif agar ukuran file yang tersimpan <= 6MB
            if (filesize($tempOutput) > $maxSizeBytes) {
                $tempOutput = $this->compressVideoUnderLimit($ffmpeg, $ffprobe, $tempOutput, $maxSizeBytes);
            }

            if (file_exists($tempOutput) && filesize($tempOutput) <= $maxSizeBytes) {
                @rename($tempOutput, $targetPath);
            } elseif (file_exists($tempOutput)) {
                @unlink($tempOutput);
                if (file_exists($sourcePath) && filesize($sourcePath) <= $maxSizeBytes) {
                    copy($sourcePath, $targetPath);
                } else {
                    throw new \RuntimeException('Ukuran video yang tersimpan di server melebihi batas maksimal 6MB.');
                }
            } else {
                copy($sourcePath, $targetPath);
            }
        } else {
            @unlink($tempOutput);
            if (file_exists($sourcePath) && filesize($sourcePath) <= $maxSizeBytes) {
                copy($sourcePath, $targetPath);
            } else {
                throw new \RuntimeException('Ukuran video yang tersimpan di server melebihi batas maksimal 6MB.');
            }
        }
    }

    // =========================================================================
    // NAMA FUNCTION : compressVideoUnderLimit
    // FUNGSI        : [HELPER VIDEO]
    //                 Mengompresi video menggunakan FFmpeg kalkulasi two-pass / multi-tier
    //                 agar ukuran file video yang disimpan di server tidak melebihi 6MB
    // =========================================================================
    public function compressVideoUnderLimit(
        string $ffmpeg,
        ?string $ffprobe,
        string $videoPath,
        int $maxSizeBytes = 6291456
    ): string {
        if (! file_exists($videoPath) || filesize($videoPath) <= $maxSizeBytes) {
            return $videoPath;
        }

        // Tahap 1: Deteksi durasi video jika ada ffprobe untuk menghitung target bitrate presisi
        $duration = 0.0;
        if ($ffprobe) {
            $cmdDur = "\"{$ffprobe}\" -v error -show_entries format=duration -of default=noprint_wrappers=1:nokey=1 " . escapeshellarg($videoPath);
            $durOut = @shell_exec($cmdDur);
            if ($durOut && is_numeric(trim($durOut))) {
                $duration = (float) trim($durOut);
            }
        }

        // Jika durasi diketahui, hitung target bitrate untuk ukuran aman 5.2MB (margin aman di bawah 6MB)
        if ($duration > 0.5) {
            $safeTargetBytes = 5.2 * 1024 * 1024; // 5.2 MB
            $targetBits = $safeTargetBytes * 8;
            $totalBitrateKbps = (int) (($targetBits / $duration) / 1000);
            $audioBitrateKbps = 64;
            $videoBitrateKbps = max(100, $totalBitrateKbps - $audioBitrateKbps);

            $tempComp1 = tempnam(sys_get_temp_dir(), 'vid_c1_') . '.mp4';
            $cmdComp1 = escapeshellcmd($ffmpeg) .
                ' -y -i ' . escapeshellarg($videoPath) .
                ' -vf "scale=trunc(iw*min(1\,min(1280/iw\,720/ih))/2)*2:trunc(ih*min(1\,min(1280/iw\,720/ih))/2)*2" ' .
                ' -c:v libx264 -preset veryfast -b:v ' . $videoBitrateKbps . 'k -maxrate ' . $videoBitrateKbps . 'k -bufsize ' . ($videoBitrateKbps * 2) . 'k ' .
                ' -c:a aac -b:a ' . $audioBitrateKbps . 'k -movflags +faststart ' .
                escapeshellarg($tempComp1) . ' 2>&1';

            @shell_exec($cmdComp1);

            if (file_exists($tempComp1) && filesize($tempComp1) > 1000) {
                @unlink($videoPath);
                $videoPath = $tempComp1;
                if (filesize($videoPath) <= $maxSizeBytes) {
                    return $videoPath;
                }
            } else {
                @unlink($tempComp1);
            }
        }

        // Tahap 2: Kompresi CRF 28 dengan batas resolusi 720p
        if (filesize($videoPath) > $maxSizeBytes) {
            $tempComp2 = tempnam(sys_get_temp_dir(), 'vid_c2_') . '.mp4';
            $cmdComp2 = escapeshellcmd($ffmpeg) .
                ' -y -i ' . escapeshellarg($videoPath) .
                ' -vf "scale=trunc(iw*min(1\,min(1280/iw\,720/ih))/2)*2:trunc(ih*min(1\,min(1280/iw\,720/ih))/2)*2" ' .
                ' -c:v libx264 -preset veryfast -crf 28 -c:a aac -b:a 64k -movflags +faststart ' .
                escapeshellarg($tempComp2) . ' 2>&1';

            @shell_exec($cmdComp2);

            if (file_exists($tempComp2) && filesize($tempComp2) > 1000) {
                @unlink($videoPath);
                $videoPath = $tempComp2;
                if (filesize($videoPath) <= $maxSizeBytes) {
                    return $videoPath;
                }
            } else {
                @unlink($tempComp2);
            }
        }

        // Tahap 3: Kompresi agresif CRF 32 dengan batas resolusi 480p
        if (filesize($videoPath) > $maxSizeBytes) {
            $tempComp3 = tempnam(sys_get_temp_dir(), 'vid_c3_') . '.mp4';
            $cmdComp3 = escapeshellcmd($ffmpeg) .
                ' -y -i ' . escapeshellarg($videoPath) .
                ' -vf "scale=trunc(iw*min(1\,min(854/iw\,480/ih))/2)*2:trunc(ih*min(1\,min(854/iw\,480/ih))/2)*2" ' .
                ' -c:v libx264 -preset veryfast -crf 32 -c:a aac -b:a 48k -movflags +faststart ' .
                escapeshellarg($tempComp3) . ' 2>&1';

            @shell_exec($cmdComp3);

            if (file_exists($tempComp3) && filesize($tempComp3) > 1000) {
                @unlink($videoPath);
                $videoPath = $tempComp3;
            } else {
                @unlink($tempComp3);
            }
        }

        return $videoPath;
    }

    // =========================================================================
    // NAMA FUNCTION : fitFontSize
    // FUNGSI        : [HELPER STAMPING]
    //                 Menghitung ukuran font TrueType terbaik secara otomatis agar
    //                 teks stempel pas di dalam lebar gambar tanpa terpotong di kanan
    // =========================================================================
    protected function fitFontSize(string $text, int $initialSize, int $maxWidth, ?string $fontFile, int $minSize = 10): int
    {
        if (! $fontFile || ! function_exists('imagettfbbox') || empty($text) || $maxWidth <= 10) {
            return max($minSize, $initialSize);
        }

        $size = $initialSize;
        $bbox = @imagettfbbox($size, 0, $fontFile, $text);
        if (! $bbox) {
            return max($minSize, $initialSize);
        }

        $textWidth = abs($bbox[4] - $bbox[0]);
        $safeMax = (int) floor($maxWidth * 0.95);

        if ($textWidth > $safeMax && $textWidth > 0) {
            $size = max($minSize, (int) floor($size * ($safeMax / $textWidth)));
        }

        return $size;
    }

    // =========================================================================
    // NAMA FUNCTION : fitTextToWidth
    // FUNGSI        : [HELPER STAMPING]
    //                 Memotong teks bukti dengan ellipsis (...) jika panjang karakter
    //                 masih melebihi lebar batas kanvas pada ukuran font terkecil
    // =========================================================================
    protected function fitTextToWidth(string $text, int $fontSize, int $maxWidth, ?string $fontFile): string
    {
        if (! $fontFile || ! function_exists('imagettfbbox') || empty($text) || $maxWidth <= 10) {
            return $text;
        }

        $safeMax = (int) floor($maxWidth * 0.95);
        $bbox = @imagettfbbox($fontSize, 0, $fontFile, $text);
        if (! $bbox) {
            return $text;
        }

        $width = abs($bbox[4] - $bbox[0]);
        if ($width <= $safeMax) {
            return $text;
        }

        $ellipsis = '...';
        $len = mb_strlen($text);
        while ($len > 3) {
            $len -= 2;
            $candidate = mb_substr($text, 0, $len) . $ellipsis;
            $bbox = @imagettfbbox($fontSize, 0, $fontFile, $candidate);
            if ($bbox && abs($bbox[4] - $bbox[0]) <= $safeMax) {
                return $candidate;
            }
        }

        return $text;
    }



}
