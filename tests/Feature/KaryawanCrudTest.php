<?php

namespace Tests\Feature;

use App\Models\Karyawan;
use App\Models\Shipment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KaryawanCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_can_render_karyawan_index_page(): void
    {
        Karyawan::factory()->create([
            'nama' => 'Budi Santoso',
            'nomor_induk' => 'KRY001',
            'status' => 'aktif',
        ]);

        $response = $this->get(route('karyawan.index'));

        $response->assertStatus(200);
        $response->assertSee('Daftar Petugas Lapangan');
        $response->assertSee('Budi Santoso');
        $response->assertSee('KRY001');
    }

    public function test_it_can_filter_karyawan_by_search_query(): void
    {
        Karyawan::factory()->create([
            'nama' => 'Hendra Wijaya',
            'nomor_induk' => 'KRY777',
        ]);
        Karyawan::factory()->create([
            'nama' => 'Agus Supriyadi',
            'nomor_induk' => 'KRY888',
        ]);

        $response = $this->get(route('karyawan.index', ['search' => 'Hendra']));

        $response->assertStatus(200);
        $response->assertSee('Hendra Wijaya');
        $response->assertDontSee('Agus Supriyadi');
    }

    public function test_it_can_filter_karyawan_by_status(): void
    {
        Karyawan::factory()->create([
            'nama' => 'Budi Sedang Bertugas',
            'nomor_induk' => 'KRY-ACT',
            'status' => 'aktif',
        ]);
        Karyawan::factory()->create([
            'nama' => 'Siti Sedang Cuti',
            'nomor_induk' => 'KRY-NON',
            'status' => 'nonaktif',
        ]);

        $response = $this->get(route('karyawan.index', ['status' => 'aktif']));
        $response->assertStatus(200);
        $response->assertSee('Budi Sedang Bertugas');
        $response->assertDontSee('Siti Sedang Cuti');

        $responseNon = $this->get(route('karyawan.index', ['status' => 'nonaktif']));
        $responseNon->assertStatus(200);
        $responseNon->assertSee('Siti Sedang Cuti');
        $responseNon->assertDontSee('Budi Sedang Bertugas');
    }

    public function test_it_can_render_create_page(): void
    {
        $response = $this->get(route('karyawan.create'));

        $response->assertStatus(200);
        $response->assertSee('Tambah Petugas Lapangan Baru');
    }

    public function test_it_can_store_a_new_karyawan(): void
    {
        $payload = [
            'nama' => 'Dedi Setiawan',
            'nomor_induk' => 'KRY010',
            'status' => 'aktif',
        ];

        $response = $this->post(route('karyawan.store'), $payload);

        $response->assertRedirect(route('karyawan.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('karyawans', [
            'nama' => 'Dedi Setiawan',
            'nomor_induk' => 'KRY010',
            'status' => 'aktif',
        ]);
    }

    public function test_it_validates_required_fields_when_storing(): void
    {
        $response = $this->post(route('karyawan.store'), [
            'nama' => '',
            'nomor_induk' => '',
            'status' => '',
        ]);

        $response->assertSessionHasErrors(['nama', 'nomor_induk', 'status']);
    }

    public function test_it_validates_unique_nomor_induk(): void
    {
        Karyawan::factory()->create([
            'nomor_induk' => 'KRY001',
        ]);

        $response = $this->post(route('karyawan.store'), [
            'nama' => 'Petugas Baru',
            'nomor_induk' => 'KRY001',
            'status' => 'aktif',
        ]);

        $response->assertSessionHasErrors(['nomor_induk']);
    }

    public function test_it_can_render_show_page_with_shipments(): void
    {
        $karyawan = Karyawan::factory()->create([
            'nama' => 'Rahmat Hidayat',
            'nomor_induk' => 'KRY015',
        ]);

        $user = User::factory()->create();

        Shipment::create([
            'user_id' => $user->id,
            'karyawan_id' => $karyawan->id,
            'jenis_produk' => 'fiber',
            'jenis_pengiriman' => 'export',
            'nomor_container_atau_plat' => 'CONT-9999',
            'cuaca' => 'kering',
            'waktu' => 'siang',
            'tanggal_staging' => now()->toDateString(),
            'warehouse_lokasi' => 'atas',
            'status' => 'submitted',
        ]);

        $response = $this->get(route('karyawan.show', $karyawan->id));

        $response->assertStatus(200);
        $response->assertSee('Rahmat Hidayat');
        $response->assertSee('KRY015');
        $response->assertSee('CONT-9999');
    }

    public function test_it_can_render_edit_page(): void
    {
        $karyawan = Karyawan::factory()->create([
            'nama' => 'Agus Priyono',
        ]);

        $response = $this->get(route('karyawan.edit', $karyawan->id));

        $response->assertStatus(200);
        $response->assertSee('Agus Priyono');
    }

    public function test_it_can_update_an_existing_karyawan(): void
    {
        $karyawan = Karyawan::factory()->create([
            'nama' => 'Nama Lama',
            'nomor_induk' => 'KRY-OLD',
            'status' => 'aktif',
        ]);

        $response = $this->put(route('karyawan.update', $karyawan->id), [
            'nama' => 'Nama Baru',
            'nomor_induk' => 'KRY-NEW',
            'status' => 'nonaktif',
        ]);

        $response->assertRedirect(route('karyawan.index'));
        $response->assertSessionHas('success');

        $karyawan->refresh();
        $this->assertSame('Nama Baru', $karyawan->nama);
        $this->assertSame('KRY-NEW', $karyawan->nomor_induk);
        $this->assertSame('nonaktif', $karyawan->status);
    }

    public function test_it_can_toggle_karyawan_status(): void
    {
        $karyawan = Karyawan::factory()->create([
            'status' => 'aktif',
        ]);

        $response = $this->patch(route('karyawan.toggle-status', $karyawan->id));

        $response->assertSessionHas('success');
        $this->assertSame('nonaktif', $karyawan->fresh()->status);

        $this->patch(route('karyawan.toggle-status', $karyawan->id));
        $this->assertSame('aktif', $karyawan->fresh()->status);
    }

    public function test_it_can_delete_karyawan_without_shipments(): void
    {
        $karyawan = Karyawan::factory()->create([
            'nama' => 'Petugas Siap Hapus',
        ]);

        $response = $this->delete(route('karyawan.destroy', $karyawan->id));

        $response->assertRedirect(route('karyawan.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('karyawans', ['id' => $karyawan->id]);
    }

    public function test_it_prevents_deleting_karyawan_with_shipments(): void
    {
        $karyawan = Karyawan::factory()->create();
        $user = User::factory()->create();

        Shipment::create([
            'user_id' => $user->id,
            'karyawan_id' => $karyawan->id,
            'jenis_produk' => 'fiber',
            'jenis_pengiriman' => 'export',
            'nomor_container_atau_plat' => 'CONT-KEEP',
            'cuaca' => 'kering',
            'waktu' => 'siang',
            'tanggal_staging' => now()->toDateString(),
            'warehouse_lokasi' => 'atas',
            'status' => 'submitted',
        ]);

        $response = $this->delete(route('karyawan.destroy', $karyawan->id));

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('karyawans', ['id' => $karyawan->id]);
    }
}
