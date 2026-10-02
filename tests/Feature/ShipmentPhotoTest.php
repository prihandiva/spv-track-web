<?php

namespace Tests\Feature;

use App\Models\Karyawan;
use App\Models\Shipment;
use App\Models\ShipmentPhoto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Tests\TestCase;

class ShipmentPhotoTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Karyawan $karyawan;

    protected Shipment $shipment;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');

        $this->user = User::firstOrCreate(
            ['email' => 'operator@test.com'],
            [
                'nama_warehouse' => 'Warehouse Staging',
                'password' => bcrypt('secret'),
                'role' => 'operator',
            ]
        );

        $this->karyawan = Karyawan::firstOrCreate(
            ['nomor_induk' => 'KRY999'],
            [
                'nama' => 'Petugas Lapangan Test',
                'status' => 'aktif',
            ]
        );

        $this->shipment = Shipment::create([
            'user_id' => $this->user->id,
            'karyawan_id' => $this->karyawan->id,
            'jenis_produk' => 'fiber',
            'jenis_pengiriman' => 'export',
            'nomor_container_atau_plat' => 'TGHU1234567',
            'cuaca' => 'kering',
            'waktu' => 'siang',
            'tanggal_staging' => now()->toDateString(),
            'warehouse_lokasi' => 'atas',
            'status' => 'draft',
        ]);
    }

    public function test_can_upload_temporary_photo_and_receive_wib_stamped_metadata(): void
    {
        $fakeImage = UploadedFile::fake()->image('plat_nomor.jpg', 1600, 1200);

        $response = $this->postJson(route('api.photos.temp.upload'), [
            'file' => $fakeImage,
            'point_no' => 1,
            'point_label' => 'Plat Nomor',
            'is_extra' => false,
            'captured_at' => '2026-10-02 08:30:00',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'temp_id',
                    'point_no',
                    'point_label',
                    'is_extra',
                    'captured_at',
                    'stamped_at',
                    'size',
                    'sha256',
                    'preview_url',
                    'original_url',
                    'thumbnail_url',
                ],
            ]);

        $data = $response->json('data');
        $this->assertStringContainsString('WIB', $data['stamped_at']);
        $this->assertEquals(1, $data['point_no']);
        $this->assertEquals('Plat Nomor', $data['point_label']);

        // Pastikan file temp tersimpan di disk private
        $tempId = $data['temp_id'];
        Storage::disk('local')->assertExists("temp_photos/{$tempId}/original.jpg");
        Storage::disk('local')->assertExists("temp_photos/{$tempId}/stamped.jpg");
        Storage::disk('local')->assertExists("temp_photos/{$tempId}/thumbnail.jpg");
        Storage::disk('local')->assertExists("temp_photos/{$tempId}/metadata.json");
    }

    public function test_can_preview_temporary_photo(): void
    {
        $fakeImage = UploadedFile::fake()->image('bale.jpg', 1200, 900);

        $uploadResponse = $this->postJson(route('api.photos.temp.upload'), [
            'file' => $fakeImage,
            'point_no' => 2,
            'point_label' => 'Foto Bale',
            'is_extra' => false,
        ]);

        $tempId = $uploadResponse->json('data.temp_id');

        $previewResponse = $this->get(route('api.photos.temp.file', ['tempId' => $tempId, 'type' => 'stamped']));
        $previewResponse->assertStatus(200);
        $previewResponse->assertHeader('Content-Type', 'image/jpeg');
    }

    public function test_can_submit_photos_to_permanent_storage(): void
    {
        // 1. Upload titik 1
        $img1 = UploadedFile::fake()->image('point1.jpg', 1200, 900);
        $res1 = $this->postJson(route('api.photos.temp.upload'), [
            'file' => $img1,
            'point_no' => 1,
            'point_label' => 'Plat Nomor',
            'is_extra' => false,
        ]);
        $tempId1 = $res1->json('data.temp_id');

        // 2. Upload foto ekstra
        $imgExtra = UploadedFile::fake()->image('extra.jpg', 1200, 900);
        $resExtra = $this->postJson(route('api.photos.temp.upload'), [
            'file' => $imgExtra,
            'point_no' => null,
            'point_label' => 'Kerusakan Palet Ekstra',
            'is_extra' => true,
        ]);
        $tempIdExtra = $resExtra->json('data.temp_id');

        // 3. Submit final
        $submitResponse = $this->postJson(route('api.shipments.photos.submit', $this->shipment->id), [
            'photos' => [
                [
                    'temp_id' => $tempId1,
                    'point_no' => 1,
                    'point_label' => 'Plat Nomor',
                    'is_extra' => false,
                    'ocr_text' => 'B 1234 SPV',
                ],
                [
                    'temp_id' => $tempIdExtra,
                    'point_no' => null,
                    'point_label' => 'Kerusakan Palet Ekstra',
                    'is_extra' => true,
                ],
            ],
        ]);

        $submitResponse->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.total_photos', 2);

        // Verifikasi di database
        $this->assertDatabaseHas('shipment_photos', [
            'shipment_id' => $this->shipment->id,
            'point_no' => 1,
            'point_label' => 'Plat Nomor',
            'is_extra' => false,
            'ocr_text' => 'B 1234 SPV',
        ]);

        $this->assertDatabaseHas('shipment_photos', [
            'shipment_id' => $this->shipment->id,
            'point_no' => null,
            'point_label' => 'Kerusakan Palet Ekstra',
            'is_extra' => true,
        ]);

        $this->shipment->refresh();
        $this->assertEquals('submitted', $this->shipment->status);
        $this->assertNotNull($this->shipment->submitted_at);
        $this->assertEquals(2, $this->shipment->photos()->count());
    }

    public function test_permanent_photo_is_immutable(): void
    {
        $img = UploadedFile::fake()->image('point2.jpg', 1200, 900);
        $res = $this->postJson(route('api.photos.temp.upload'), [
            'file' => $img,
            'point_no' => 2,
            'point_label' => 'Foto Titik 2',
        ]);
        $tempId = $res->json('data.temp_id');

        $this->postJson(route('api.shipments.photos.submit', $this->shipment->id), [
            'photos' => [
                [
                    'temp_id' => $tempId,
                    'point_no' => 2,
                    'point_label' => 'Foto Titik 2',
                ],
            ],
        ]);

        $photo = ShipmentPhoto::where('shipment_id', $this->shipment->id)->firstOrFail();

        // 1. Coba update record permanen -> Harus throw RuntimeException
        $this->expectException(RuntimeException::class);
        $photo->update(['point_label' => 'Label Diubah Ilegal']);
    }

    public function test_permanent_photo_cannot_be_deleted(): void
    {
        $img = UploadedFile::fake()->image('point3.jpg', 1200, 900);
        $res = $this->postJson(route('api.photos.temp.upload'), [
            'file' => $img,
            'point_no' => 3,
            'point_label' => 'Foto Titik 3',
        ]);
        $tempId = $res->json('data.temp_id');

        $this->postJson(route('api.shipments.photos.submit', $this->shipment->id), [
            'photos' => [
                [
                    'temp_id' => $tempId,
                    'point_no' => 3,
                    'point_label' => 'Foto Titik 3',
                ],
            ],
        ]);

        $photo = ShipmentPhoto::where('shipment_id', $this->shipment->id)->firstOrFail();

        // 2. Coba delete record permanen -> Harus throw RuntimeException
        $this->expectException(RuntimeException::class);
        $photo->delete();
    }

    public function test_clean_temporary_photos_command_executes(): void
    {
        $exitCode = Artisan::call('photos:clean-temp', ['--hours' => 24]);
        $this->assertEquals(0, $exitCode);
    }
}
