<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SopPhotoPoint extends Model
{
    protected $fillable = [
        'jenis_pengiriman',
        'urutan',
        'nama_titik',
        'deskripsi',
        'tipe_item',
        'wajib',
        'perlu_ocr_container',
        'perlu_deteksi_orang',
        'perlu_deteksi_barcode',
    ];

    protected function casts(): array
    {
        return [
            'wajib' => 'boolean',
            'perlu_ocr_container' => 'boolean',
            'perlu_deteksi_orang' => 'boolean',
            'perlu_deteksi_barcode' => 'boolean',
        ];
    }

    public function evidenceItems(): HasMany
    {
        return $this->hasMany(EvidenceItem::class);
    }

    /**
     * Scope query to order by sequence.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('urutan');
    }

    /**
     * Standard 27 default points definition.
     */
    public static function defaultPoints(): array
    {
        return [
            ['urutan' => 1, 'nama_titik' => 'Plat Nomor', 'deskripsi' => 'Foto plat nomor truck/container', 'tipe_item' => 'foto', 'wajib' => true, 'perlu_ocr_container' => false, 'perlu_deteksi_orang' => false, 'perlu_deteksi_barcode' => false, 'jenis_pengiriman' => 'both'],
            ['urutan' => 2, 'nama_titik' => 'Foto di Loading Area', 'deskripsi' => 'Photo Fiber/Sodium di loading/staging area yang sudah dikumpulkan', 'tipe_item' => 'foto', 'wajib' => true, 'perlu_ocr_container' => false, 'perlu_deteksi_orang' => false, 'perlu_deteksi_barcode' => false, 'jenis_pengiriman' => 'both'],
            ['urutan' => 3, 'nama_titik' => 'Foto Bales (Zoomable)', 'deskripsi' => 'Photo Fiber/Sodium di loading/staging area, nomor bales terbaca saat di-zoom', 'tipe_item' => 'foto', 'wajib' => true, 'perlu_ocr_container' => false, 'perlu_deteksi_orang' => false, 'perlu_deteksi_barcode' => true, 'jenis_pengiriman' => 'both'],
            ['urutan' => 4, 'nama_titik' => 'Pembersihan Bagian Depan', 'deskripsi' => 'Photo Fiber/Sodium bagian depan sedang dibersihkan', 'tipe_item' => 'foto', 'wajib' => true, 'perlu_ocr_container' => false, 'perlu_deteksi_orang' => true, 'perlu_deteksi_barcode' => false, 'jenis_pengiriman' => 'both'],
            ['urutan' => 5, 'nama_titik' => 'Pembersihan Bagian Samping', 'deskripsi' => 'Photo Fiber/Sodium bagian samping sedang dibersihkan', 'tipe_item' => 'foto', 'wajib' => true, 'perlu_ocr_container' => false, 'perlu_deteksi_orang' => true, 'perlu_deteksi_barcode' => false, 'jenis_pengiriman' => 'both'],
            ['urutan' => 6, 'nama_titik' => 'Pembersihan Bagian Atas', 'deskripsi' => 'Photo Fiber/Sodium bagian atas sedang dibersihkan', 'tipe_item' => 'foto', 'wajib' => true, 'perlu_ocr_container' => false, 'perlu_deteksi_orang' => true, 'perlu_deteksi_barcode' => false, 'jenis_pengiriman' => 'both'],
            ['urutan' => 7, 'nama_titik' => 'Container Luar Kiri', 'deskripsi' => 'Photo container bagian luar sisi kiri, nomor container terbaca', 'tipe_item' => 'foto', 'wajib' => true, 'perlu_ocr_container' => true, 'perlu_deteksi_orang' => false, 'perlu_deteksi_barcode' => false, 'jenis_pengiriman' => 'both'],
            ['urutan' => 8, 'nama_titik' => 'Container Luar Kanan', 'deskripsi' => 'Photo container bagian luar sisi kanan dari arah depan container', 'tipe_item' => 'foto', 'wajib' => true, 'perlu_ocr_container' => false, 'perlu_deteksi_orang' => false, 'perlu_deteksi_barcode' => false, 'jenis_pengiriman' => 'both'],
            ['urutan' => 9, 'nama_titik' => 'Lantai Container Kosong', 'deskripsi' => 'Photo lantai container dalam keadaan kosong (wajib ada terpal untuk non woven)', 'tipe_item' => 'foto', 'wajib' => true, 'perlu_ocr_container' => false, 'perlu_deteksi_orang' => false, 'perlu_deteksi_barcode' => false, 'jenis_pengiriman' => 'both'],
            ['urutan' => 10, 'nama_titik' => 'Bagian Atas Dalam', 'deskripsi' => 'Photo bagian atas dalam container', 'tipe_item' => 'foto', 'wajib' => true, 'perlu_ocr_container' => false, 'perlu_deteksi_orang' => false, 'perlu_deteksi_barcode' => false, 'jenis_pengiriman' => 'both'],
            ['urutan' => 11, 'nama_titik' => 'Bagian Kanan Dalam', 'deskripsi' => 'Photo bagian kanan dalam container, nomor container harus terbaca', 'tipe_item' => 'foto', 'wajib' => true, 'perlu_ocr_container' => true, 'perlu_deteksi_orang' => false, 'perlu_deteksi_barcode' => false, 'jenis_pengiriman' => 'both'],
            ['urutan' => 12, 'nama_titik' => 'Bagian Kiri Dalam', 'deskripsi' => 'Photo bagian kiri dalam container', 'tipe_item' => 'foto', 'wajib' => true, 'perlu_ocr_container' => false, 'perlu_deteksi_orang' => false, 'perlu_deteksi_barcode' => false, 'jenis_pengiriman' => 'both'],
            ['urutan' => 13, 'nama_titik' => 'Swab Test Kiri', 'deskripsi' => 'Photo swab test permukaan dinding kiri menggunakan majun', 'tipe_item' => 'foto', 'wajib' => true, 'perlu_ocr_container' => false, 'perlu_deteksi_orang' => false, 'perlu_deteksi_barcode' => false, 'jenis_pengiriman' => 'both'],
            ['urutan' => 14, 'nama_titik' => 'Swab Test Kanan', 'deskripsi' => 'Photo swab test permukaan dinding kanan menggunakan majun', 'tipe_item' => 'foto', 'wajib' => true, 'perlu_ocr_container' => false, 'perlu_deteksi_orang' => false, 'perlu_deteksi_barcode' => false, 'jenis_pengiriman' => 'both'],
            ['urutan' => 15, 'nama_titik' => 'Swab Test Depan', 'deskripsi' => 'Photo swab test permukaan dinding depan menggunakan majun', 'tipe_item' => 'foto', 'wajib' => true, 'perlu_ocr_container' => false, 'perlu_deteksi_orang' => false, 'perlu_deteksi_barcode' => false, 'jenis_pengiriman' => 'both'],
            ['urutan' => 16, 'nama_titik' => 'Terisi Baris Pertama', 'deskripsi' => 'Photo terisi fiber/bales baris pertama', 'tipe_item' => 'foto', 'wajib' => true, 'perlu_ocr_container' => false, 'perlu_deteksi_orang' => false, 'perlu_deteksi_barcode' => false, 'jenis_pengiriman' => 'both'],
            ['urutan' => 17, 'nama_titik' => 'Terisi Setengah', 'deskripsi' => 'Photo terisi fiber/bales setengah container', 'tipe_item' => 'foto', 'wajib' => true, 'perlu_ocr_container' => false, 'perlu_deteksi_orang' => false, 'perlu_deteksi_barcode' => false, 'jenis_pengiriman' => 'both'],
            ['urutan' => 18, 'nama_titik' => 'Terisi Penuh', 'deskripsi' => 'Photo terisi fiber/bales penuh dan tersegel', 'tipe_item' => 'foto', 'wajib' => true, 'perlu_ocr_container' => false, 'perlu_deteksi_orang' => false, 'perlu_deteksi_barcode' => false, 'jenis_pengiriman' => 'both'],
            ['urutan' => 19, 'nama_titik' => 'Satu Pintu Tertutup', 'deskripsi' => 'Photo tertutup satu pintu, nomor container harus terbaca', 'tipe_item' => 'foto', 'wajib' => true, 'perlu_ocr_container' => true, 'perlu_deteksi_orang' => false, 'perlu_deteksi_barcode' => false, 'jenis_pengiriman' => 'both'],
            ['urutan' => 20, 'nama_titik' => 'Tertutup Terkunci', 'deskripsi' => 'Photo container tertutup dan terkunci', 'tipe_item' => 'foto', 'wajib' => true, 'perlu_ocr_container' => false, 'perlu_deteksi_orang' => false, 'perlu_deteksi_barcode' => false, 'jenis_pengiriman' => 'both'],
            ['urutan' => 21, 'nama_titik' => 'Tampak Segel & Plat', 'deskripsi' => 'Photo pintu tertutup tampak segel, nomor container, plat nomor, pelayaran', 'tipe_item' => 'foto', 'wajib' => true, 'perlu_ocr_container' => true, 'perlu_deteksi_orang' => false, 'perlu_deteksi_barcode' => false, 'jenis_pengiriman' => 'both'],
            ['urutan' => 22, 'nama_titik' => 'Foto Segel', 'deskripsi' => 'Foto segel', 'tipe_item' => 'foto', 'wajib' => true, 'perlu_ocr_container' => false, 'perlu_deteksi_orang' => false, 'perlu_deteksi_barcode' => false, 'jenis_pengiriman' => 'both'],
            ['urutan' => 23, 'nama_titik' => 'Ban Diganjal', 'deskripsi' => 'Photo 2 ban diganjal stopper', 'tipe_item' => 'foto', 'wajib' => true, 'perlu_ocr_container' => false, 'perlu_deteksi_orang' => false, 'perlu_deteksi_barcode' => false, 'jenis_pengiriman' => 'both'],
            ['urutan' => 24, 'nama_titik' => 'Checklist Document', 'deskripsi' => 'Truck Checklist (Lokal) / Container Checklist (Export)', 'tipe_item' => 'dokumen', 'wajib' => true, 'perlu_ocr_container' => false, 'perlu_deteksi_orang' => false, 'perlu_deteksi_barcode' => false, 'jenis_pengiriman' => 'both'],
            ['urutan' => 25, 'nama_titik' => 'Bales Inspection Ball', 'deskripsi' => 'Bales Inspection Ball', 'tipe_item' => 'dokumen', 'wajib' => true, 'perlu_ocr_container' => false, 'perlu_deteksi_orang' => false, 'perlu_deteksi_barcode' => false, 'jenis_pengiriman' => 'both'],
            ['urutan' => 26, 'nama_titik' => 'Surat Jalan', 'deskripsi' => 'Surat Jalan + scan OK', 'tipe_item' => 'dokumen', 'wajib' => true, 'perlu_ocr_container' => false, 'perlu_deteksi_orang' => false, 'perlu_deteksi_barcode' => false, 'jenis_pengiriman' => 'both'],
            ['urutan' => 27, 'nama_titik' => 'Video Singkat', 'deskripsi' => 'Video singkat', 'tipe_item' => 'video', 'wajib' => false, 'perlu_ocr_container' => false, 'perlu_deteksi_orang' => false, 'perlu_deteksi_barcode' => false, 'jenis_pengiriman' => 'both'],
        ];
    }
}
