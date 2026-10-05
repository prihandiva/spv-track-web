<?php

namespace Tests\Feature;

use App\Models\EvidenceItem;
use App\Models\Karyawan;
use App\Models\Shipment;
use App\Models\SopPhotoPoint;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class ShipmentPdfDownloadTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        File::deleteDirectory(storage_path('app/public/shipments/test_pdf'));
        File::deleteDirectory(storage_path('app/pdf_cache'));
        parent::tearDown();
    }

    public function test_user_can_view_shipment_report_preview(): void
    {
        $user = User::factory()->create();
        $karyawan = Karyawan::create(['nomor_induk' => 'KRY010', 'nama' => 'Test Petugas', 'status' => 'aktif']);

        $shipment = Shipment::create([
            'user_id' => $user->id,
            'karyawan_id' => $karyawan->id,
            'packing_list_no' => 'PL-TEST-PDF',
            'shipment_group' => 'SG-100',
            'shipment_no' => 'SH-200',
            'nomor_container_atau_plat' => 'TGHU1122334',
            'plat_nomor' => 'B5555XYZ',
            'jenis_produk' => 'fiber',
            'jenis_pengiriman' => 'export',
            'warehouse_lokasi' => 'atas',
            'cuaca' => 'kering',
            'waktu' => 'siang',
            'tanggal_staging' => now()->toDateString(),
            'status' => 'submitted',
        ]);

        $response = $this->actingAs($user)->get(route('shipments.report', $shipment));

        $response->assertStatus(200);
        $response->assertSee('PL-TEST-PDF');
        $response->assertSee('SG-100');
        $response->assertSee('SH-200');
        $response->assertSee('TGHU1122334');
        $response->assertSee('B5555XYZ');
    }

    public function test_user_can_download_shipment_pdf_report(): void
    {
        $user = User::factory()->create();
        $karyawan = Karyawan::create(['nomor_induk' => 'KRY011', 'nama' => 'Test Petugas', 'status' => 'aktif']);

        $shipment = Shipment::create([
            'user_id' => $user->id,
            'karyawan_id' => $karyawan->id,
            'packing_list_no' => 'PL-DOWNLOAD-PDF',
            'shipment_group' => 'SG-300',
            'shipment_no' => 'SH-400',
            'nomor_container_atau_plat' => 'OOCU9988776',
            'plat_nomor' => 'B9999ABC',
            'jenis_produk' => 'fiber',
            'jenis_pengiriman' => 'export',
            'warehouse_lokasi' => 'atas',
            'cuaca' => 'kering',
            'waktu' => 'siang',
            'tanggal_staging' => now()->toDateString(),
            'status' => 'submitted',
        ]);

        $point = SopPhotoPoint::create([
            'jenis_pengiriman' => 'both',
            'urutan' => 1,
            'nama_titik' => 'Plat Nomor',
            'tipe_item' => 'foto',
            'wajib' => true,
        ]);

        $storageDir = storage_path('app/public/shipments/test_pdf');
        File::ensureDirectoryExists($storageDir);
        $testFile = $storageDir.'/photo1.jpg';

        $img = imagecreatetruecolor(400, 300);
        $color = imagecolorallocate($img, 50, 100, 150);
        imagefilledrectangle($img, 0, 0, 400, 300, $color);
        imagejpeg($img, $testFile, 80);
        imagedestroy($img);

        EvidenceItem::create([
            'shipment_id' => $shipment->id,
            'sop_photo_point_id' => $point->id,
            'file_path' => 'shipments/test_pdf/photo1.jpg',
            'file_name' => 'photo1.jpg',
            'tipe_item' => 'foto',
        ]);

        $response = $this->actingAs($user)->get(route('shipments.download-pdf', $shipment));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
        $this->assertStringContainsString('PL-DOWNLOAD-PDF_Report.pdf', (string) $response->headers->get('content-disposition'));
    }
}
