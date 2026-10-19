<?php

namespace Tests\Feature;

use App\Models\Karyawan;
use App\Models\Shipment;
use App\Models\SopPhotoPoint;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FieldAppFlowTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Karyawan $karyawan;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::firstOrCreate(
            ['id' => 1],
            [
                'email' => 'admin@test.com',
                'nama_warehouse' => 'Main Warehouse',
                'password' => bcrypt('secret'),
                'role' => 'superadmin',
            ]
        );

        $this->actingAs($this->user);

        $this->karyawan = Karyawan::create([
            'nama' => 'Budi Santoso',
            'nomor_induk' => 'NIK-888',
            'position' => 'operator',
            'status' => 'aktif',
        ]);
    }

    public function test_field_app_create_page_renders_successfully(): void
    {
        $response = $this->get(route('field-app.create'));

        $response->assertStatus(200);
        $response->assertSee('Dokumen Shipment Order');
        $response->assertSee('Fix 9 Digit Angka');
        $response->assertSee('packing_list_no');
        $response->assertSee('shipment_group');
        $response->assertSee('shipment_no');
    }

    public function test_field_app_store_validates_exact_9_digits(): void
    {
        // Test with 8 digits (should fail)
        $response = $this->post(route('field-app.store'), [
            'jenis_produk' => 'fiber',
            'jenis_pengiriman' => 'export',
            'packing_list_no' => '12345678', // 8 digits
            'shipment_group' => '869026487', // 9 digits
            'shipment_no' => '860110918',   // 9 digits
            'plat_nomor' => 'B 1234 ABC',
            'nomor_container_atau_plat' => 'MSKU9988776',
            'cuaca' => 'kering',
            'waktu' => 'siang',
            'tanggal_staging' => '2026-10-05',
            'warehouse_lokasi' => 'atas',
            'karyawan_id' => $this->karyawan->id,
        ]);

        $response->assertSessionHasErrors(['packing_list_no']);

        // Test with 10 digits (should fail)
        $response = $this->post(route('field-app.store'), [
            'jenis_produk' => 'fiber',
            'jenis_pengiriman' => 'export',
            'packing_list_no' => '800123456',
            'shipment_group' => '1234567890', // 10 digits
            'shipment_no' => '860110918',
            'plat_nomor' => 'B 1234 ABC',
            'nomor_container_atau_plat' => 'MSKU9988776',
            'cuaca' => 'kering',
            'waktu' => 'siang',
            'tanggal_staging' => '2026-10-05',
            'warehouse_lokasi' => 'atas',
            'karyawan_id' => $this->karyawan->id,
        ]);

        $response->assertSessionHasErrors(['shipment_group']);
    }

    public function test_field_app_store_succeeds_with_valid_9_digits(): void
    {
        $response = $this->post(route('field-app.store'), [
            'jenis_produk' => 'fiber',
            'jenis_pengiriman' => 'export',
            'packing_list_no' => '800123456', // 9 digits
            'shipment_group' => '869026487', // 9 digits
            'shipment_no' => '860110918',   // 9 digits
            'plat_nomor' => 'B 1234 ABC',
            'nama_sopir' => 'Ahmad',
            'nomor_container_atau_plat' => 'MSKU9988776',
            'cuaca' => 'kering',
            'waktu' => 'siang',
            'tanggal_staging' => '2026-10-05',
            'warehouse_lokasi' => 'atas',
            'karyawan_id' => $this->karyawan->id,
        ]);

        $this->assertDatabaseHas('shipments', [
            'packing_list_no' => '800123456',
            'shipment_group' => '869026487',
            'shipment_no' => '860110918',
            'plat_nomor' => 'B 1234 ABC',
            'nomor_container_atau_plat' => 'MSKU9988776',
        ]);

        $shipment = Shipment::first();
        $response->assertRedirect(route('field-app.timeline', $shipment->id));
    }

    public function test_field_app_timeline_page_renders_successfully(): void
    {
        $shipment = Shipment::create([
            'user_id' => $this->user->id,
            'karyawan_id' => $this->karyawan->id,
            'jenis_produk' => 'fiber',
            'jenis_pengiriman' => 'export',
            'packing_list_no' => '800123456',
            'shipment_group' => '869026487',
            'shipment_no' => '860110918',
            'plat_nomor' => 'B 1234 ABC',
            'nama_sopir' => 'Ahmad',
            'nomor_container_atau_plat' => 'MSKU9988776',
            'cuaca' => 'kering',
            'waktu' => 'siang',
            'tanggal_staging' => '2026-10-05',
            'warehouse_lokasi' => 'atas',
            'status' => 'draft',
        ]);

        $response = $this->get(route('field-app.timeline', $shipment->id));

        $response->assertStatus(200);
        $response->assertSee('Waktu Pelaksanaan Staging Container');
        $response->assertSee('800123456');
    }

    public function test_field_app_submit_only_requires_start_and_end_time(): void
    {
        $shipment = Shipment::create([
            'user_id' => $this->user->id,
            'karyawan_id' => $this->karyawan->id,
            'jenis_produk' => 'fiber',
            'jenis_pengiriman' => 'export',
            'packing_list_no' => '800123456',
            'shipment_group' => '869026487',
            'shipment_no' => '860110918',
            'plat_nomor' => 'B 1234 ABC',
            'nomor_container_atau_plat' => 'MSKU9988776',
            'cuaca' => 'kering',
            'waktu' => 'siang',
            'tanggal_staging' => '2026-10-05',
            'warehouse_lokasi' => 'atas',
            'status' => 'draft',
        ]);

        // Mock upload of all mandatory points (assume 0 mandatory points for test or attach evidence items)
        $mandatoryPoints = SopPhotoPoint::where('wajib', true)->get();
        foreach ($mandatoryPoints as $point) {
            $shipment->evidenceItems()->create([
                'sop_photo_point_id' => $point->id,
                'file_path' => 'dummy.jpg',
                'file_name' => 'dummy.jpg',
                'tipe_item' => 'foto',
                'captured_at' => now(),
            ]);
        }

        $response = $this->post(route('field-app.submit', $shipment->id), [
            'waktu_kedatangan_container' => '2026-10-05T08:00',
            'waktu_keberangkatan_container' => '2026-10-05T12:00',
        ]);

        $response->assertRedirect('/');
        $shipment->refresh();
        $this->assertEquals('submitted', $shipment->status);
        $this->assertNotNull($shipment->waktu_kedatangan_container);
        $this->assertNotNull($shipment->waktu_keberangkatan_container);
    }

    public function test_field_app_store_supports_multiple_karyawan_and_displays_on_show_page(): void
    {
        $karyawan2 = Karyawan::create([
            'nama' => 'Siti Aminah',
            'nomor_induk' => 'NIK-999',
            'status' => 'aktif',
        ]);

        $response = $this->post(route('field-app.store'), [
            'jenis_produk' => 'sodium',
            'jenis_pengiriman' => 'lokal',
            'packing_list_no' => '800555666',
            'shipment_group' => '869111222',
            'shipment_no' => '860333444',
            'plat_nomor' => 'D 9999 XYZ',
            'nama_sopir' => 'Bambang',
            'nomor_container_atau_plat' => 'TGHU1234567',
            'cuaca' => 'kering',
            'waktu' => 'siang',
            'tanggal_staging' => '2026-10-05',
            'warehouse_lokasi' => 'tengah',
            'karyawan_ids' => [$this->karyawan->id, $karyawan2->id],
        ]);

        $shipment = Shipment::where('packing_list_no', '800555666')->first();
        $this->assertNotNull($shipment);
        $response->assertRedirect(route('field-app.timeline', $shipment->id));

        $this->assertDatabaseHas('karyawan_shipment', [
            'shipment_id' => $shipment->id,
            'karyawan_id' => $this->karyawan->id,
        ]);
        $this->assertDatabaseHas('karyawan_shipment', [
            'shipment_id' => $shipment->id,
            'karyawan_id' => $karyawan2->id,
        ]);

        // Check show page renders both officers
        $showResponse = $this->get(route('shipments.show', $shipment->id));
        $showResponse->assertStatus(200);
        $showResponse->assertSee('Budi Santoso');
        $showResponse->assertSee('NIK-888');
        $showResponse->assertSee('Siti Aminah');
        $showResponse->assertSee('NIK-999');
    }

    public function test_field_app_store_supports_sliver_and_yarn(): void
    {
        $response = $this->post(route('field-app.store'), [
            'jenis_produk' => 'sliver',
            'jenis_pengiriman' => 'export',
            'packing_list_no' => '800111222',
            'shipment_group' => '869111222',
            'shipment_no' => '860111222',
            'plat_nomor' => 'B 1111 AAA',
            'nama_sopir' => 'Anto',
            'nomor_container_atau_plat' => 'MSKU1111222',
            'cuaca' => 'kering',
            'waktu' => 'siang',
            'tanggal_staging' => '2026-10-05',
            'warehouse_lokasi' => 'atas',
            'karyawan_id' => $this->karyawan->id,
        ]);

        $shipment = Shipment::where('packing_list_no', '800111222')->first();
        $this->assertNotNull($shipment);
        $this->assertEquals('sliver', $shipment->jenis_produk);

        $showResponse = $this->get(route('shipments.show', $shipment->id));
        $showResponse->assertStatus(200);
        $showResponse->assertSee('SLIVER');
    }

    public function test_field_app_store_supports_others_with_custom_product_input(): void
    {
        $response = $this->post(route('field-app.store'), [
            'jenis_produk' => 'others',
            'jenis_produk_custom' => 'POLYESTER CHIPS',
            'jenis_pengiriman' => 'lokal',
            'packing_list_no' => '800333444',
            'shipment_group' => '869333444',
            'shipment_no' => '860333444',
            'plat_nomor' => 'B 2222 BBB',
            'nama_sopir' => 'Joko',
            'nomor_container_atau_plat' => 'TGHU3334445',
            'cuaca' => 'kering',
            'waktu' => 'siang',
            'tanggal_staging' => '2026-10-05',
            'warehouse_lokasi' => 'bawah',
            'karyawan_id' => $this->karyawan->id,
        ]);

        $shipment = Shipment::where('packing_list_no', '800333444')->first();
        $this->assertNotNull($shipment);
        $this->assertEquals('POLYESTER CHIPS', $shipment->jenis_produk);

        $showResponse = $this->get(route('shipments.show', $shipment->id));
        $showResponse->assertStatus(200);
        $showResponse->assertSee('POLYESTER CHIPS');

        $indexResponse = $this->get(route('shipments.index'));
        $indexResponse->assertStatus(200);
        $indexResponse->assertSee('POLYESTER CHIPS');
    }

    public function test_field_app_store_supports_nomor_seal_and_tracks_in_views(): void
    {
        $response = $this->post(route('field-app.store'), [
            'jenis_produk' => 'fiber',
            'jenis_pengiriman' => 'export',
            'packing_list_no' => '800777888',
            'shipment_group' => '869777888',
            'shipment_no' => '860777888',
            'plat_nomor' => 'D 1234 KLM',
            'nama_sopir' => 'Sopir Agus',
            'nomor_seal' => 'SEAL-SPV-999',
            'nomor_container_atau_plat' => 'TGHU9998887',
            'cuaca' => 'kering',
            'waktu' => 'siang',
            'tanggal_staging' => '2026-10-05',
            'warehouse_lokasi' => 'atas',
            'karyawan_id' => $this->karyawan->id,
        ]);

        $shipment = Shipment::where('packing_list_no', '800777888')->first();
        $this->assertNotNull($shipment);
        $this->assertEquals('SEAL-SPV-999', $shipment->nomor_seal);

        $showResponse = $this->get(route('shipments.show', $shipment->id));
        $showResponse->assertStatus(200);
        $showResponse->assertSee('SEAL-SPV-999');

        $indexResponse = $this->get(route('shipments.index'));
        $indexResponse->assertStatus(200);
        $indexResponse->assertSee('SEAL-SPV-999');

        $pdfResponse = $this->actingAs(User::factory()->create(['role' => 'admin']))->get(route('shipments.report', $shipment->id));
        $pdfResponse->assertStatus(200);
        $pdfResponse->assertSee('SEAL-SPV-999');
    }

    public function test_timeline_supports_clickable_preview_and_retake_action_bar(): void
    {
        $shipment = Shipment::create([
            'user_id' => $this->user->id,
            'karyawan_id' => $this->karyawan->id,
            'jenis_produk' => 'fiber',
            'jenis_pengiriman' => 'export',
            'packing_list_no' => '800999000',
            'shipment_group' => '869999000',
            'shipment_no' => '860999000',
            'plat_nomor' => 'B 9999 XYZ',
            'nama_sopir' => 'Budi Santoso',
            'nomor_seal' => 'SEAL-888-JKT',
            'nomor_container_atau_plat' => 'TGHU1112223',
            'cuaca' => 'kering',
            'waktu' => 'siang',
            'tanggal_staging' => '2026-10-05',
            'warehouse_lokasi' => 'atas',
            'status' => 'draft',
        ]);

        $point = SopPhotoPoint::create([
            'jenis_pengiriman' => 'both',
            'tipe_item' => 'foto',
            'urutan' => 1,
            'nama_titik' => 'Foto Kontainer Kosong Tampak Depan',
            'wajib' => true,
        ]);

        $response = $this->get(route('field-app.timeline', $shipment->id));
        $response->assertStatus(200);
        $response->assertSee('openPointPreview');
        $response->assertSee('Klik untuk Periksa');
        $response->assertSee('action-bar-' . $point->id);
        $response->assertSee('camera-input-' . $point->id);
        $response->assertSee('Stempel Digital SPV-Track');
        $response->assertSee('Pastikan foto/video jelas dan stempel waktu terbaca');

        // Test uploading photo 1
        $file1 = \Illuminate\Http\UploadedFile::fake()->image('point1_first.jpg', 640, 480);
        $uploadResp1 = $this->post(route('field-app.upload-photo', $shipment->id), [
            'sop_photo_point_id' => $point->id,
            'file' => $file1,
            'captured_at' => '2026-10-19 09:30:00',
        ]);
        $uploadResp1->assertStatus(200);
        $uploadResp1->assertJson(['success' => true]);

        $photo1 = \App\Models\EvidenceItem::where('shipment_id', $shipment->id)
            ->where('sop_photo_point_id', $point->id)
            ->first();
        $this->assertNotNull($photo1);

        // Test retake (re-uploading photo for the same point replaces/updates existing)
        $file2 = \Illuminate\Http\UploadedFile::fake()->image('point1_retake.jpg', 640, 480);
        $uploadResp2 = $this->post(route('field-app.upload-photo', $shipment->id), [
            'sop_photo_point_id' => $point->id,
            'file' => $file2,
            'captured_at' => '2026-10-19 09:35:00',
        ]);
        $uploadResp2->assertStatus(200);
        $uploadResp2->assertJson(['success' => true]);

        $photo1->refresh();
        $this->assertEquals('2026-10-19 09:35:00', $photo1->captured_at->format('Y-m-d H:i:s'));
        $this->assertStringContainsString('_p' . $point->id . '_2.jpg', $photo1->file_path);
    }
}

