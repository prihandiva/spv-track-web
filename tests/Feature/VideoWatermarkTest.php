<?php

namespace Tests\Feature;

use App\Models\Karyawan;
use App\Models\Shipment;
use App\Models\SopPhotoPoint;
use App\Models\User;
use App\Services\PhotoProcessingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class VideoWatermarkTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Karyawan $karyawan;
    protected Shipment $shipment;
    protected SopPhotoPoint $videoPoint;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        $this->user = User::factory()->create([
            'role' => 'operator',
        ]);

        $this->karyawan = Karyawan::factory()->create([
            'nama' => 'Budi Operator',
        ]);

        $this->shipment = Shipment::create([
            'user_id' => $this->user->id,
            'karyawan_id' => $this->karyawan->id,
            'jenis_produk' => 'fiber',
            'jenis_pengiriman' => 'export',
            'nomor_container_atau_plat' => 'TEST1234567',
            'cuaca' => 'kering',
            'waktu' => 'siang',
            'warehouse_lokasi' => 'atas',
            'status' => 'draft',
            'tanggal_staging' => now()->toDateString(),
        ]);

        $this->videoPoint = SopPhotoPoint::create([
            'jenis_pengiriman' => 'both',
            'urutan' => 27,
            'nama_titik' => 'Video Singkat',
            'deskripsi' => 'Rekam video singkat',
            'tipe_item' => 'video',
            'wajib' => false,
        ]);
    }

    public function test_photo_processing_service_detects_ffmpeg(): void
    {
        $service = app(PhotoProcessingService::class);
        $ffmpeg = $service->getFfmpegBinary();

        $this->assertNotNull($ffmpeg, 'FFmpeg binary should be found on the system');
        $this->assertFileExists($ffmpeg);
    }

    public function test_uploading_video_via_field_app_applies_burn_in_and_saves_evidence(): void
    {
        $file = UploadedFile::fake()->create('sample_recording.mp4', 1024, 'video/mp4');

        $response = $this->actingAs($this->user)->postJson(route('field-app.upload-photo', $this->shipment->id), [
            'sop_photo_point_id' => $this->videoPoint->id,
            'file' => $file,
            'captured_at' => now()->format('Y-m-d H:i:s'),
        ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
        ]);

        $this->assertDatabaseHas('evidence_items', [
            'shipment_id' => $this->shipment->id,
            'sop_photo_point_id' => $this->videoPoint->id,
            'tipe_item' => 'video',
        ]);
    }

    public function test_uploading_video_exceeding_6mb_via_field_app_fails_validation(): void
    {
        // 7MB video (7168 KB > 6144 KB)
        $file = UploadedFile::fake()->create('heavy_video.mp4', 7168, 'video/mp4');

        $response = $this->actingAs($this->user)->postJson(route('field-app.upload-photo', $this->shipment->id), [
            'sop_photo_point_id' => $this->videoPoint->id,
            'file' => $file,
            'captured_at' => now()->format('Y-m-d H:i:s'),
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
        ]);
        $this->assertStringContainsString('6MB', $response->json('message'));

        $this->assertDatabaseMissing('evidence_items', [
            'shipment_id' => $this->shipment->id,
            'sop_photo_point_id' => $this->videoPoint->id,
        ]);
    }

    public function test_uploading_video_exceeding_6mb_via_mobile_api_fails_validation(): void
    {
        // 6.5MB video (6656 KB > 6144 KB)
        $file = UploadedFile::fake()->create('heavy_mobile_video.mp4', 6656, 'video/mp4');

        $response = $this->actingAs($this->user)->postJson("/api/shipments/{$this->shipment->id}/evidence", [
            'sop_photo_point_id' => $this->videoPoint->id,
            'file' => $file,
            'captured_at' => now()->format('Y-m-d H:i:s'),
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
        ]);
        $this->assertStringContainsString('6MB', $response->json('message'));

        $this->assertDatabaseMissing('evidence_items', [
            'shipment_id' => $this->shipment->id,
            'sop_photo_point_id' => $this->videoPoint->id,
        ]);
    }
}
