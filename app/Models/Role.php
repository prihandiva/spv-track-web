<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    protected $fillable = [
        'name',
        'display_name',
        'description',
        'permissions',
        'is_system',
    ];

    protected function casts(): array
    {
        return [
            'permissions' => 'array',
            'is_system' => 'boolean',
        ];
    }

    /**
     * Get users associated with this role.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'role', 'name');
    }

    /**
     * Check if this role has a specific permission.
     */
    public function hasPermission(string $permission): bool
    {
        $permissions = $this->permissions ?? [];

        return in_array($permission, $permissions, true);
    }

    /**
     * Predefined available permission modules and actions.
     */
    public static function availablePermissions(): array
    {
        return [
            'Shipment & Staging' => [
                'shipment.view' => 'Lihat Data Shipment & Riwayat Staging',
                'shipment.create' => 'Buat Entri Shipment Baru',
                'shipment.edit' => 'Koreksi & Edit Data Kondisi Lapangan',
                'shipment.delete' => 'Hapus Data Shipment (Jika Diizinkan)',
            ],
            'Field App & Evidence' => [
                'field_app.access' => 'Akses Antarmuka Lapangan (Field App)',
                'field_app.upload' => 'Unggah Foto, Dokumen & Video Bukti SOP',
                'field_app.submit' => 'Submit Final Staging Lapangan',
            ],
            'Laporan & Ekspor' => [
                'report.view' => 'Buka Rekapitulasi Laporan & Audit Staging',
                'report.download_pdf' => 'Download Laporan Berita Acara (PDF)',
                'report.download_excel' => 'Ekspor Data Rekap ke Microsoft Excel',
                'report.download_zip' => 'Unduh Batch ZIP Foto Asli Tanpa Kompresi',
            ],
            'Petugas Lapangan' => [
                'petugas.view' => 'Lihat Daftar Petugas Lapangan',
                'petugas.create' => 'Tambah Data Petugas Baru',
                'petugas.edit' => 'Edit Data & Ubah Status Petugas',
                'petugas.delete' => 'Hapus Petugas Lapangan',
            ],
            'Master Data SOP' => [
                'sop.view' => 'Lihat Konfigurasi 27 Titik SOP Evidence',
                'sop.create' => 'Tambah Titik SOP Baru',
                'sop.edit' => 'Modifikasi Urutan, Deskripsi & Flag AI Titik',
                'sop.delete' => 'Hapus Titik SOP dari Database',
            ],
            'Pengguna & Sistem' => [
                'users.view' => 'Lihat Daftar Akun Pengguna',
                'users.create' => 'Daftarkan Akun Pengguna Baru',
                'users.edit' => 'Perbarui Informasi Akun & Reset Sandi',
                'users.delete' => 'Hapus Akun Pengguna',
                'roles.view' => 'Lihat Konfigurasi Role & Matriks Akses',
                'roles.edit' => 'Atur Hak Akses & Matriks Izin Role',
                'settings.view' => 'Buka Halaman Pengaturan Sistem',
                'settings.edit' => 'Perbarui Parameter Perusahaan, AI & Warehouse',
            ],
        ];
    }
}
