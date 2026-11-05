<?php

namespace App\Helpers;

class FormatHelper
{
    /**
     * Format nomor plat kendaraan agar huruf dan angka terpisah oleh spasi dan UPPERCASE.
     * Contoh:
     * - "b8976uiy"    => "B 8976 UIY"
     * - "B 8976UIY"   => "B 8976 UIY"
     * - "B8976 UIY"   => "B 8976 UIY"
     * - "b 8976 uiy"  => "B 8976 UIY"
     * - "B   8976 UIY"=> "B 8976 UIY"
     * - "AB1234CD"    => "AB 1234 CD"
     * - "B123"        => "B 123"
     * - "RI 1"        => "RI 1"
     * - "RI1"         => "RI 1"
     */
    public static function formatPlatNomor(?string $plate): string
    {
        if ($plate === null || trim($plate) === '') {
            return '';
        }

        $plate = mb_strtoupper(trim($plate), 'UTF-8');
        $cleaned = preg_replace('/\s+/', '', $plate);

        // Pola standar plat nomor Indonesia:
        // 1-2 huruf wilayah (A-Z) + 1-4 digit angka (0-9) + 0-3 huruf seri (A-Z)
        if (preg_match('/^([A-Z]{1,2})([0-9]{1,4})([A-Z]{0,3})$/', $cleaned, $matches)) {
            $parts = [$matches[1], $matches[2]];
            if (! empty($matches[3])) {
                $parts[] = $matches[3];
            }

            return implode(' ', $parts);
        }

        // Pola umum: kumpulan huruf, angka, lalu huruf opsional
        if (preg_match('/^([A-Z]+)\s*([0-9]+)\s*([A-Z]*)$/', $plate, $matches)) {
            $parts = [$matches[1], $matches[2]];
            if (! empty($matches[3])) {
                $parts[] = $matches[3];
            }

            return implode(' ', $parts);
        }

        // Fallback: pisahkan setiap perbatasan huruf & angka dengan spasi
        $formatted = preg_replace('/([A-Z])([0-9])/', '$1 $2', $plate);
        $formatted = preg_replace('/([0-9])([A-Z])/', '$1 $2', $formatted);

        return mb_strtoupper(trim(preg_replace('/\s+/', ' ', $formatted)), 'UTF-8');
    }

    /**
     * Konversi teks menjadi UPPERCASE dengan trim rapi.
     */
    public static function toUpper(?string $text): ?string
    {
        if ($text === null) {
            return null;
        }

        $trimmed = trim($text);
        if ($trimmed === '') {
            return '';
        }

        return mb_strtoupper($trimmed, 'UTF-8');
    }
}
