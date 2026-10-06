<?php

namespace Tests\Feature;

use App\Models\EvidenceItem;
use App\Models\Karyawan;
use App\Models\Shipment;
use App\Models\SopPhotoPoint;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LaporanTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Karyawan $karyawan;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->karyawan = Karyawan::create([
            'nama' => 'Budi Santoso',
            'nomor_induk' => 'SPV001',
            'status' => 'aktif',
        ]);

        // Buat beberapa SOP Photo Points
        SopPhotoPoint::create(['urutan' => 1, 'nama_titik' => 'Plat Nomor', 'tipe_item' => 'foto', 'jenis_pengiriman' => 'both']);
        SopPhotoPoint::create(['urutan' => 2, 'nama_titik' => 'Foto Fiber/Sodium Area', 'tipe_item' => 'foto', 'jenis_pengiriman' => 'both']);
        SopPhotoPoint::create(['urutan' => 3, 'nama_titik' => 'Bales Nomor Terbaca', 'tipe_item' => 'foto', 'jenis_pengiriman' => 'both']);
    }

    public function test_user_can_view_laporan_index_page(): void
    {
        $shipment = Shipment::create([
            'user_id' => $this->user->id,
            'karyawan_id' => $this->karyawan->id,
            'packing_list_no' => 'PL-TEST-001',
            'nomor_container_atau_plat' => 'CONT-998811',
            'plat_nomor' => 'B1234XYZ',
            'jenis_produk' => 'fiber',
            'jenis_pengiriman' => 'export',
            'warehouse_lokasi' => 'atas',
            'cuaca' => 'kering',
            'waktu' => 'siang',
            'tanggal_staging' => now()->toDateString(),
            'status' => 'submitted',
        ]);

        $response = $this->actingAs($this->user)->get(route('laporan.index'));

        $response->assertStatus(200);
        $response->assertSee('Laporan & Audit Staging', false);
        $response->assertSee('PL-TEST-001');
        $response->assertSee('CONT-998811');
        $response->assertSee('Budi Santoso');
    }

    public function test_laporan_filters_by_product_and_shipment_type(): void
    {
        Shipment::create([
            'user_id' => $this->user->id,
            'karyawan_id' => $this->karyawan->id,
            'packing_list_no' => 'PL-FIBER-EXPORT',
            'jenis_produk' => 'fiber',
            'jenis_pengiriman' => 'export',
            'nomor_container_atau_plat' => 'FIBER-CONT',
            'warehouse_lokasi' => 'atas',
            'cuaca' => 'kering',
            'waktu' => 'siang',
            'tanggal_staging' => now()->toDateString(),
            'status' => 'submitted',
        ]);

        Shipment::create([
            'user_id' => $this->user->id,
            'karyawan_id' => $this->karyawan->id,
            'packing_list_no' => 'PL-SODIUM-LOKAL',
            'jenis_produk' => 'sodium',
            'jenis_pengiriman' => 'lokal',
            'nomor_container_atau_plat' => 'SODIUM-TRUCK',
            'warehouse_lokasi' => 'tengah',
            'cuaca' => 'hujan',
            'waktu' => 'malam',
            'tanggal_staging' => now()->toDateString(),
            'status' => 'draft',
        ]);

        // Filter Fiber Export
        $response = $this->actingAs($this->user)->get(route('laporan.index', [
            'jenis_produk' => 'fiber',
            'jenis_pengiriman' => 'export',
        ]));

        $response->assertStatus(200);
        $response->assertSee('PL-FIBER-EXPORT');
        $response->assertDontSee('PL-SODIUM-LOKAL');

        // Filter Sodium Lokal
        $responseSodium = $this->actingAs($this->user)->get(route('laporan.index', [
            'jenis_produk' => 'sodium',
            'jenis_pengiriman' => 'lokal',
        ]));

        $responseSodium->assertStatus(200);
        $responseSodium->assertSee('PL-SODIUM-LOKAL');
        $responseSodium->assertDontSee('PL-FIBER-EXPORT');
    }

    public function test_laporan_displays_sop_compliance_and_audit_matrix(): void
    {
        $shipment = Shipment::create([
            'user_id' => $this->user->id,
            'karyawan_id' => $this->karyawan->id,
            'packing_list_no' => 'PL-SOP-AUDIT',
            'nomor_container_atau_plat' => 'CONT-SOP',
            'jenis_produk' => 'fiber',
            'jenis_pengiriman' => 'export',
            'warehouse_lokasi' => 'bawah',
            'cuaca' => 'kering',
            'waktu' => 'sore',
            'tanggal_staging' => now()->toDateString(),
            'status' => 'submitted',
        ]);

        $point1 = SopPhotoPoint::where('urutan', 1)->first();
        EvidenceItem::create([
            'shipment_id' => $shipment->id,
            'sop_photo_point_id' => $point1->id,
            'file_path' => 'evidence/test1.jpg',
            'file_name' => 'test1.jpg',
            'tipe_item' => 'foto',
        ]);

        $response = $this->actingAs($this->user)->get(route('laporan.index', ['tab' => 'audit']));

        $response->assertStatus(200);
        $response->assertSee('Matriks Evaluasi Kepatuhan 27 Titik SOP');
        $response->assertSee('Plat Nomor');
        $response->assertSee('Foto Fiber/Sodium Area');
    }

    public function test_laporan_displays_petugas_productivity_tab(): void
    {
        Shipment::create([
            'user_id' => $this->user->id,
            'karyawan_id' => $this->karyawan->id,
            'packing_list_no' => 'PL-PETUGAS-01',
            'nomor_container_atau_plat' => 'CONT-PETUGAS',
            'jenis_produk' => 'fiber',
            'jenis_pengiriman' => 'export',
            'warehouse_lokasi' => 'atas',
            'cuaca' => 'kering',
            'waktu' => 'siang',
            'tanggal_staging' => now()->toDateString(),
            'status' => 'submitted',
        ]);

        $response = $this->actingAs($this->user)->get(route('laporan.index', ['tab' => 'petugas']));

        $response->assertStatus(200);
        $response->assertSee('Rekap Kinerja & Aktivitas Petugas Staging', false);
        $response->assertSee('Budi Santoso');
        $response->assertSee('SPV001');
    }

    public function test_user_can_export_laporan_to_csv(): void
    {
        Shipment::create([
            'user_id' => $this->user->id,
            'karyawan_id' => $this->karyawan->id,
            'packing_list_no' => 'PL-EXPORT-CSV',
            'nomor_container_atau_plat' => 'CONT-CSV-123',
            'plat_nomor' => 'D9876AA',
            'jenis_produk' => 'sodium',
            'jenis_pengiriman' => 'lokal',
            'warehouse_lokasi' => 'tengah',
            'cuaca' => 'gerimis',
            'waktu' => 'sore',
            'tanggal_staging' => now()->toDateString(),
            'status' => 'submitted',
        ]);

        $response = $this->actingAs($this->user)->get(route('laporan.export-excel', ['format' => 'csv']));

        $response->assertStatus(200);
        $this->assertEquals('text/csv; charset=utf-8', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('Rekap_Laporan_SPV_Track_', (string) $response->headers->get('Content-Disposition'));

        $content = $response->streamedContent();
        $this->assertStringContainsString('PL-EXPORT-CSV', $content);
        $this->assertStringContainsString('CONT-CSV-123', $content);
        $this->assertStringContainsString('SODIUM', $content);
    }

    public function test_user_can_export_laporan_to_xls(): void
    {
        Shipment::create([
            'user_id' => $this->user->id,
            'karyawan_id' => $this->karyawan->id,
            'packing_list_no' => 'PL-EXPORT-XLS',
            'nomor_container_atau_plat' => 'CONT-XLS-456',
            'jenis_produk' => 'fiber',
            'jenis_pengiriman' => 'export',
            'warehouse_lokasi' => 'atas',
            'cuaca' => 'kering',
            'waktu' => 'siang',
            'tanggal_staging' => now()->toDateString(),
            'status' => 'submitted',
        ]);

        $response = $this->actingAs($this->user)->get(route('laporan.export-excel', ['format' => 'xls']));

        $response->assertStatus(200);
        $this->assertStringContainsString('application/vnd.ms-excel', (string) $response->headers->get('Content-Type'));
        $response->assertSee('PT SOUTH PACIFIC VISCOSE');
        $response->assertSee('PL-EXPORT-XLS');
        $response->assertSee('CONT-XLS-456');
    }

    public function test_user_can_view_print_rekap_page(): void
    {
        Shipment::create([
            'user_id' => $this->user->id,
            'karyawan_id' => $this->karyawan->id,
            'packing_list_no' => 'PL-PRINT-REKAP',
            'nomor_container_atau_plat' => 'CONT-PRINT-777',
            'jenis_produk' => 'fiber',
            'jenis_pengiriman' => 'export',
            'warehouse_lokasi' => 'atas',
            'cuaca' => 'kering',
            'waktu' => 'siang',
            'tanggal_staging' => now()->toDateString(),
            'status' => 'submitted',
        ]);

        $response = $this->actingAs($this->user)->get(route('laporan.print-rekap'));

        $response->assertStatus(200);
        $response->assertSee('BERITA ACARA & REKAPITULASI STAGING', false);
        $response->assertSee('PL-PRINT-REKAP');
        $response->assertSee('CONT-PRINT-777');
        $response->assertSee('Supervisor Staging');
        $response->assertSee('Warehouse Manager');
    }

    public function test_user_can_download_rekap_pdf(): void
    {
        Shipment::create([
            'user_id' => $this->user->id,
            'karyawan_id' => $this->karyawan->id,
            'packing_list_no' => 'PL-PDF-REKAP',
            'nomor_container_atau_plat' => 'CONT-PDF-888',
            'jenis_produk' => 'fiber',
            'jenis_pengiriman' => 'export',
            'warehouse_lokasi' => 'atas',
            'cuaca' => 'kering',
            'waktu' => 'siang',
            'tanggal_staging' => now()->toDateString(),
            'status' => 'submitted',
        ]);

        $response = $this->actingAs($this->user)->get(route('laporan.download-rekap-pdf'));

        $response->assertStatus(200);
        $this->assertEquals('application/pdf', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('Berita_Acara_Rekap_SPV_Track_', (string) $response->headers->get('Content-Disposition'));
    }
}
