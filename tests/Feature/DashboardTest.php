<?php

namespace Tests\Feature;

use App\Models\Karyawan;
use App\Models\Shipment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_renders_successfully_with_kpi_and_charts(): void
    {
        $user = User::factory()->create([
            'nama_warehouse' => 'Warehouse Alpha',
            'role' => 'admin',
        ]);

        $karyawan = Karyawan::factory()->create([
            'nama' => 'Budi Santoso',
            'status' => 'aktif',
        ]);

        // Create sample shipments
        Shipment::create([
            'user_id' => $user->id,
            'karyawan_id' => $karyawan->id,
            'jenis_produk' => 'fiber',
            'jenis_pengiriman' => 'export',
            'nomor_container_atau_plat' => 'CONT-9912',
            'cuaca' => 'kering',
            'waktu' => 'siang',
            'tanggal_staging' => Carbon::today(),
            'warehouse_lokasi' => 'atas',
            'status' => 'submitted',
            'submitted_at' => Carbon::now(),
        ]);

        Shipment::create([
            'user_id' => $user->id,
            'karyawan_id' => $karyawan->id,
            'jenis_produk' => 'sodium',
            'jenis_pengiriman' => 'lokal',
            'nomor_container_atau_plat' => 'B 1234 SPV',
            'cuaca' => 'mendung',
            'waktu' => 'sore',
            'tanggal_staging' => Carbon::today(),
            'warehouse_lokasi' => 'tengah',
            'status' => 'draft',
        ]);

        $response = $this->actingAs($user)->get('/');

        $response->assertStatus(200);
        $response->assertViewIs('dashboard');
        $response->assertViewHas([
            'currentUser',
            'totalShipments',
            'totalSubmitted',
            'totalDraft',
            'totalPetugas',
            'successRate',
            'kpiData',
            'statusDistribution',
            'typeDistribution',
            'operationalDistribution',
            'topPetugas',
            'shipments',
        ]);

        $response->assertSee('Warehouse Alpha');
        $response->assertSee('Admin');
    }
}
