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

class ShipmentBatchZipTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        File::deleteDirectory(storage_path('app/public/shipments/test_batch_zip'));
        File::deleteDirectory(storage_path('app/zip_cache'));
        parent::tearDown();
    }

    public function test_batch_zip_download_yearly_creates_correct_folder_structure(): void
    {
        $user = User::factory()->create();
        $karyawan = Karyawan::create(['nomor_induk' => 'KRY010', 'nama' => 'Petugas Batch', 'status' => 'aktif']);

        $point = SopPhotoPoint::create([
            'jenis_pengiriman' => 'both',
            'urutan' => 1,
            'nama_titik' => 'Container Depan',
            'tipe_item' => 'foto',
            'wajib' => true,
        ]);

        $storageDir = storage_path('app/public/shipments/test_batch_zip');
        File::ensureDirectoryExists($storageDir);
        $testFile1 = $storageDir.'/photo_sept1.jpg';
        file_put_contents($testFile1, 'fake image 1');

        // Shipment 1: 1 September 2026 (day 1, month SEPTEMBER)
        $shipment1 = Shipment::create([
            'user_id' => $user->id,
            'karyawan_id' => $karyawan->id,
            'packing_list_no' => 'PL-SEPT-001',
            'nomor_container_atau_plat' => 'TGHU1111111',
            'plat_nomor' => 'B1001AA',
            'jenis_produk' => 'fiber',
            'jenis_pengiriman' => 'export',
            'warehouse_lokasi' => 'atas',
            'cuaca' => 'kering',
            'waktu' => 'siang',
            'tanggal_staging' => '2026-09-01',
            'status' => 'submitted',
        ]);

        EvidenceItem::create([
            'shipment_id' => $shipment1->id,
            'sop_photo_point_id' => $point->id,
            'file_path' => 'shipments/test_batch_zip/photo_sept1.jpg',
            'file_name' => 'photo_sept1.jpg',
            'tipe_item' => 'foto',
        ]);

        // Download Tahunan 2026
        $response = $this->actingAs($user)->get(route('shipments.download-batch-zip', [
            'mode' => 'tahunan',
            'year' => 2026,
            'quality' => 'compressed',
        ]));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/zip');
        $this->assertStringContainsString('SPV_Evidence_Tahunan_2026.zip', (string) $response->headers->get('content-disposition'));

        // Inspect master zip contents
        $masterZipContent = $response->streamedContent();
        $tempMaster = tempnam(sys_get_temp_dir(), 'test_master_');
        file_put_contents($tempMaster, $masterZipContent);

        $zip = new ZipArchive;
        $this->assertTrue($zip->open($tempMaster));

        // Harus ada folder: FIBER/2026/SEPTEMBER/1/PL-SEPT-001.zip
        $foundEntry = false;
        $innerZipName = 'FIBER/2026/SEPTEMBER/1/PL-SEPT-001.zip';

        for ($i = 0; $i < $zip->numFiles; $i++) {
            if ($zip->getNameIndex($i) === $innerZipName) {
                $foundEntry = true;
                break;
            }
        }

        $this->assertTrue($foundEntry, "Entry [{$innerZipName}] tidak ditemukan di dalam Master ZIP.");

        // Ekstrak inner ZIP dan verifikasi file di dalamnya
        $innerZipData = $zip->getFromName($innerZipName);
        $tempInner = tempnam(sys_get_temp_dir(), 'test_inner_');
        file_put_contents($tempInner, $innerZipData);

        $innerZip = new ZipArchive;
        $this->assertTrue($innerZip->open($tempInner));
        $this->assertGreaterThan(0, $innerZip->numFiles);
        $this->assertStringStartsWith('TGHU1111111_Titik_01_Container_Depan', $innerZip->getNameIndex(0));

        $innerZip->close();
        $zip->close();
        @unlink($tempInner);
        @unlink($tempMaster);
    }

    public function test_batch_zip_download_filters_by_product_fiber_and_sodium(): void
    {
        $user = User::factory()->create();
        $karyawan = Karyawan::create(['nomor_induk' => 'KRY011', 'nama' => 'Petugas Produk', 'status' => 'aktif']);

        $point = SopPhotoPoint::create([
            'jenis_pengiriman' => 'both',
            'urutan' => 1,
            'nama_titik' => 'Loading Area',
            'tipe_item' => 'foto',
            'wajib' => true,
        ]);

        $storageDir = storage_path('app/public/shipments/test_batch_zip');
        File::ensureDirectoryExists($storageDir);
        $testFile = $storageDir.'/photo_prod.jpg';
        file_put_contents($testFile, 'fake image content');

        // Fiber
        $fiberShipment = Shipment::create([
            'user_id' => $user->id,
            'karyawan_id' => $karyawan->id,
            'packing_list_no' => 'PL-FIBER-01',
            'nomor_container_atau_plat' => 'CONT-FIBER',
            'plat_nomor' => 'B2001AA',
            'jenis_produk' => 'fiber',
            'jenis_pengiriman' => 'export',
            'warehouse_lokasi' => 'atas',
            'cuaca' => 'kering',
            'waktu' => 'siang',
            'tanggal_staging' => '2026-09-02',
            'status' => 'submitted',
        ]);
        EvidenceItem::create([
            'shipment_id' => $fiberShipment->id,
            'sop_photo_point_id' => $point->id,
            'file_path' => 'shipments/test_batch_zip/photo_prod.jpg',
            'file_name' => 'photo_prod.jpg',
            'tipe_item' => 'foto',
        ]);

        // Sodium
        $sodiumShipment = Shipment::create([
            'user_id' => $user->id,
            'karyawan_id' => $karyawan->id,
            'packing_list_no' => 'PL-SODIUM-01',
            'nomor_container_atau_plat' => 'CONT-SODIUM',
            'plat_nomor' => 'B2002AA',
            'jenis_produk' => 'sodium',
            'jenis_pengiriman' => 'lokal',
            'warehouse_lokasi' => 'tengah',
            'cuaca' => 'kering',
            'waktu' => 'siang',
            'tanggal_staging' => '2026-09-02',
            'status' => 'submitted',
        ]);
        EvidenceItem::create([
            'shipment_id' => $sodiumShipment->id,
            'sop_photo_point_id' => $point->id,
            'file_path' => 'shipments/test_batch_zip/photo_prod.jpg',
            'file_name' => 'photo_prod.jpg',
            'tipe_item' => 'foto',
        ]);

        // Download hanya fiber
        $response = $this->actingAs($user)->get(route('shipments.download-batch-zip', [
            'mode' => 'harian',
            'date' => '2026-09-02',
            'jenis_produk' => 'fiber',
        ]));

        $response->assertStatus(200);
        $this->assertStringContainsString('Fiber.zip', (string) $response->headers->get('content-disposition'));

        $tempMaster = tempnam(sys_get_temp_dir(), 'test_master_fiber_');
        file_put_contents($tempMaster, $response->streamedContent());

        $zip = new ZipArchive;
        $this->assertTrue($zip->open($tempMaster));
        $this->assertEquals(1, $zip->numFiles);
        $this->assertStringStartsWith('FIBER/', $zip->getNameIndex(0));
        $this->assertStringContainsString('PL-FIBER-01.zip', $zip->getNameIndex(0));
        $zip->close();
        @unlink($tempMaster);
    }

    public function test_batch_zip_preview_returns_correct_stats_and_sample_path(): void
    {
        $user = User::factory()->create();
        $karyawan = Karyawan::create(['nomor_induk' => 'KRY012', 'nama' => 'Petugas Preview', 'status' => 'aktif']);

        $point = SopPhotoPoint::create([
            'jenis_pengiriman' => 'both',
            'urutan' => 1,
            'nama_titik' => 'Loading Area',
            'tipe_item' => 'foto',
            'wajib' => true,
        ]);

        $storageDir = storage_path('app/public/shipments/test_batch_zip');
        File::ensureDirectoryExists($storageDir);
        $testFile = $storageDir.'/photo_preview.jpg';
        file_put_contents($testFile, 'fake image content');

        $shipment = Shipment::create([
            'user_id' => $user->id,
            'karyawan_id' => $karyawan->id,
            'packing_list_no' => 'PL-PREVIEW-99',
            'nomor_container_atau_plat' => 'TGHU5555555',
            'plat_nomor' => 'B5555AA',
            'jenis_produk' => 'fiber',
            'jenis_pengiriman' => 'export',
            'warehouse_lokasi' => 'atas',
            'cuaca' => 'kering',
            'waktu' => 'siang',
            'tanggal_staging' => '2026-09-01',
            'status' => 'submitted',
        ]);

        EvidenceItem::create([
            'shipment_id' => $shipment->id,
            'sop_photo_point_id' => $point->id,
            'file_path' => 'shipments/test_batch_zip/photo_preview.jpg',
            'file_name' => 'photo_preview.jpg',
            'tipe_item' => 'foto',
        ]);

        $response = $this->actingAs($user)->get(route('shipments.batch-zip-preview', [
            'mode' => 'bulanan',
            'year' => 2026,
            'month' => 9,
            'jenis_produk' => 'all',
        ]));

        $response->assertStatus(200);
        $response->assertJson([
            'count' => 1,
            'sample_path' => 'FIBER/2026/SEPTEMBER/1/PL-PREVIEW-99.zip',
        ]);
        $this->assertNotEmpty($response->json('estimated_size'));
        $this->assertNotEmpty($response->json('filename'));
    }

    public function test_batch_zip_download_redirects_with_error_when_no_shipments_found(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('shipments.download-batch-zip', [
            'mode' => 'tahunan',
            'year' => 2019,
        ]));

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    public function test_batch_zip_download_daily_and_range(): void
    {
        $user = User::factory()->create();
        $karyawan = Karyawan::create(['nomor_induk' => 'KRY013', 'nama' => 'Petugas Daily Range', 'status' => 'aktif']);

        $point = SopPhotoPoint::create([
            'jenis_pengiriman' => 'both',
            'urutan' => 1,
            'nama_titik' => 'Pintu Kontainer',
            'tipe_item' => 'foto',
            'wajib' => true,
        ]);

        $storageDir = storage_path('app/public/shipments/test_batch_zip');
        File::ensureDirectoryExists($storageDir);
        $testFile = $storageDir.'/photo_dr.jpg';
        file_put_contents($testFile, 'fake daily range photo');

        $shipment = Shipment::create([
            'user_id' => $user->id,
            'karyawan_id' => $karyawan->id,
            'packing_list_no' => 'PL-DAILY-01',
            'nomor_container_atau_plat' => 'TGHU7777777',
            'plat_nomor' => 'B7777AA',
            'jenis_produk' => 'sodium',
            'jenis_pengiriman' => 'lokal',
            'warehouse_lokasi' => 'tengah',
            'cuaca' => 'kering',
            'waktu' => 'siang',
            'tanggal_staging' => '2026-09-05',
            'status' => 'submitted',
        ]);

        EvidenceItem::create([
            'shipment_id' => $shipment->id,
            'sop_photo_point_id' => $point->id,
            'file_path' => 'shipments/test_batch_zip/photo_dr.jpg',
            'file_name' => 'photo_dr.jpg',
            'tipe_item' => 'foto',
        ]);

        // 1. Test Daily
        $dailyRes = $this->actingAs($user)->get(route('shipments.download-batch-zip', [
            'mode' => 'harian',
            'date' => '2026-09-05',
        ]));
        $dailyRes->assertStatus(200);
        $this->assertStringContainsString('SPV_Evidence_Harian_2026_SEPTEMBER_5.zip', (string) $dailyRes->headers->get('content-disposition'));

        // 2. Test Range
        $rangeRes = $this->actingAs($user)->get(route('shipments.download-batch-zip', [
            'mode' => 'range',
            'start_date' => '2026-09-01',
            'end_date' => '2026-09-05',
        ]));
        $rangeRes->assertStatus(200);
        $this->assertStringContainsString('SPV_Evidence_Range_20260901_sd_20260905.zip', (string) $rangeRes->headers->get('content-disposition'));
    }

    public function test_shipments_index_renders_batch_zip_button_and_modal(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('shipments.index'));

        $response->assertStatus(200);
        $response->assertSee('Download Batch ZIP');
        $response->assertSee('Download Arsip Evidence (Batch ZIP)');
        $response->assertSee('Pilih Periode Download');
        $response->assertSee('Struktur Folder di Dalam File ZIP:');
        $response->assertSee('Harian');
        $response->assertSee('Rentang');
        $response->assertSee('Bulanan');
        $response->assertSee('Tahunan');
        $response->assertSee('Fiber');
        $response->assertSee('Sodium');
    }
}
