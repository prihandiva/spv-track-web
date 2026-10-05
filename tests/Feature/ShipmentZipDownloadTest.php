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
use ZipArchive;

class ShipmentZipDownloadTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        // Cleanup test directory if created
        File::deleteDirectory(storage_path('app/public/shipments/test_zip'));
        parent::tearDown();
    }

    public function test_user_can_download_shipment_evidence_as_zip_named_by_packing_list(): void
    {
        $user = User::factory()->create();
        $karyawan = Karyawan::create(['nomor_induk' => 'KRY001', 'nama' => 'Test Petugas', 'status' => 'aktif']);

        $shipment = Shipment::create([
            'user_id' => $user->id,
            'karyawan_id' => $karyawan->id,
            'packing_list_no' => 'PL-99881122',
            'nomor_container_atau_plat' => 'TGHU1234567',
            'plat_nomor' => 'B1234ABC',
            'jenis_produk' => 'fiber',
            'jenis_pengiriman' => 'fcl',
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

        $storageDir = storage_path('app/public/shipments/test_zip');
        File::ensureDirectoryExists($storageDir);
        $testFile = $storageDir.'/photo1.jpg';
        file_put_contents($testFile, 'fake image content');

        EvidenceItem::create([
            'shipment_id' => $shipment->id,
            'sop_photo_point_id' => $point->id,
            'file_path' => 'shipments/test_zip/photo1.jpg',
            'file_name' => 'photo1.jpg',
            'tipe_item' => 'foto',
        ]);

        $response = $this->actingAs($user)->get(route('shipments.download-evidence', $shipment));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/zip');
        $this->assertStringContainsString('PL-99881122.zip', (string) $response->headers->get('content-disposition'));

        // Inspect zip contents
        $content = $response->streamedContent();
        $tempZip = tempnam(sys_get_temp_dir(), 'test_verify_');
        file_put_contents($tempZip, $content);

        $zip = new ZipArchive;
        $this->assertTrue($zip->open($tempZip));
        $this->assertEquals(1, $zip->numFiles);
        $entryName = $zip->getNameIndex(0);
        $this->assertStringStartsWith('TGHU1234567_Titik_01_Plat_Nomor', $entryName);
        $zip->close();
        @unlink($tempZip);
    }

    public function test_shipment_without_container_uses_plate_number_for_image_names(): void
    {
        $user = User::factory()->create();
        $karyawan = Karyawan::create(['nomor_induk' => 'KRY002', 'nama' => 'Test Petugas', 'status' => 'aktif']);

        $shipment = Shipment::create([
            'user_id' => $user->id,
            'karyawan_id' => $karyawan->id,
            'packing_list_no' => 'PL-NO-CONTAINER',
            'nomor_container_atau_plat' => '0',
            'plat_nomor' => 'B9876XYZ',
            'jenis_produk' => 'fiber',
            'jenis_pengiriman' => 'cbu',
            'warehouse_lokasi' => 'atas',
            'cuaca' => 'kering',
            'waktu' => 'siang',
            'tanggal_staging' => now()->toDateString(),
            'status' => 'submitted',
        ]);

        $point = SopPhotoPoint::create([
            'jenis_pengiriman' => 'both',
            'urutan' => 2,
            'nama_titik' => 'Loading Area',
            'tipe_item' => 'foto',
            'wajib' => true,
        ]);

        $storageDir = storage_path('app/public/shipments/test_zip');
        File::ensureDirectoryExists($storageDir);
        $testFile = $storageDir.'/photo2.jpg';
        file_put_contents($testFile, 'fake image content');

        EvidenceItem::create([
            'shipment_id' => $shipment->id,
            'sop_photo_point_id' => $point->id,
            'file_path' => 'shipments/test_zip/photo2.jpg',
            'file_name' => 'photo2.jpg',
            'tipe_item' => 'foto',
        ]);

        $response = $this->actingAs($user)->get(route('shipments.download-evidence', $shipment));

        $response->assertStatus(200);
        $this->assertStringContainsString('PL-NO-CONTAINER.zip', (string) $response->headers->get('content-disposition'));

        // Inspect zip contents
        $content = $response->streamedContent();
        $tempZip = tempnam(sys_get_temp_dir(), 'test_verify_');
        file_put_contents($tempZip, $content);

        $zip = new ZipArchive;
        $this->assertTrue($zip->open($tempZip));
        $entryName = $zip->getNameIndex(0);
        // Container was '0', so image name uses plat nomor: B9876XYZ
        $this->assertStringStartsWith('B9876XYZ_Titik_02_Loading_Area', $entryName);
        $zip->close();
        @unlink($tempZip);
    }

    public function test_shipment_with_no_photos_redirects_back_with_error(): void
    {
        $user = User::factory()->create();
        $karyawan = Karyawan::create(['nomor_induk' => 'KRY003', 'nama' => 'Test Petugas', 'status' => 'aktif']);

        $shipment = Shipment::create([
            'user_id' => $user->id,
            'karyawan_id' => $karyawan->id,
            'packing_list_no' => 'PL-EMPTY',
            'nomor_container_atau_plat' => '0',
            'plat_nomor' => 'B1111AA',
            'jenis_produk' => 'fiber',
            'jenis_pengiriman' => 'fcl',
            'warehouse_lokasi' => 'atas',
            'cuaca' => 'kering',
            'waktu' => 'siang',
            'tanggal_staging' => now()->toDateString(),
            'status' => 'draft',
        ]);

        $response = $this->actingAs($user)->get(route('shipments.download-evidence', $shipment));

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    public function test_download_zip_optimizes_large_photos_and_caches_result(): void
    {
        $user = User::factory()->create();
        $karyawan = Karyawan::create(['nomor_induk' => 'KRY004', 'nama' => 'Test Petugas', 'status' => 'aktif']);

        $shipment = Shipment::create([
            'user_id' => $user->id,
            'karyawan_id' => $karyawan->id,
            'packing_list_no' => 'PL-COMPRESSION-TEST',
            'nomor_container_atau_plat' => 'TGHU9999999',
            'plat_nomor' => 'B1234ZZZ',
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

        $storageDir = storage_path('app/public/shipments/test_zip');
        File::ensureDirectoryExists($storageDir);
        $testFile = $storageDir.'/large_photo.jpg';

        // Create a large 2400x1800 image
        $img = imagecreatetruecolor(2400, 1800);
        $color = imagecolorallocate($img, 100, 150, 200);
        imagefilledrectangle($img, 0, 0, 2400, 1800, $color);
        imagejpeg($img, $testFile, 95);
        imagedestroy($img);

        $rawSize = filesize($testFile);

        EvidenceItem::create([
            'shipment_id' => $shipment->id,
            'sop_photo_point_id' => $point->id,
            'file_path' => 'shipments/test_zip/large_photo.jpg',
            'file_name' => 'large_photo.jpg',
            'tipe_item' => 'foto',
        ]);

        // First request: optimizes image and writes to cache
        $response = $this->actingAs($user)->get(route('shipments.download-evidence', $shipment));
        $response->assertStatus(200);

        $tempZip = tempnam(sys_get_temp_dir(), 'test_verify_opt_');
        file_put_contents($tempZip, $response->streamedContent());

        $zip = new ZipArchive;
        $this->assertTrue($zip->open($tempZip));
        $stat = $zip->statIndex(0);
        // The image inside ZIP should be optimized and smaller than raw 2400x1800 image
        $this->assertLessThan($rawSize, $stat['size']);
        $zip->close();
        @unlink($tempZip);

        // Verify cache file exists
        $cacheDir = storage_path('app/zip_cache');
        $cachedFiles = glob($cacheDir.'/'.$shipment->id.'_*_PL-COMPRESSION-TEST.zip');
        $this->assertNotEmpty($cachedFiles);

        // Second request should serve cached file directly
        $secondResponse = $this->actingAs($user)->get(route('shipments.download-evidence', $shipment));
        $secondResponse->assertStatus(200);

        // Cleanup
        foreach ($cachedFiles as $cf) {
            @unlink($cf);
        }
    }
}
