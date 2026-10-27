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
            'leadTimeStats',
            'kpiData',
            'statusDistribution',
            'typeDistribution',
            'operationalDistribution',
            'topPetugas',
            'shipments',
        ]);

        $response->assertSee('Warehouse Alpha');
        $response->assertSee('Admin');
        $response->assertSee('Rata-rata Lead Time');
        $response->assertSee('Lead Time Staging');
    }

    public function test_shipment_lead_time_calculation_and_display(): void
    {
        $user = User::factory()->create([
            'nama_warehouse' => 'Supervisor Staging',
            'role' => 'admin',
        ]);

        $karyawan = Karyawan::factory()->create([
            'nama' => 'Agus Operator',
            'status' => 'aktif',
        ]);

        // Staging with 1 hour 45 minutes duration (105 minutes)
        $shipment = Shipment::create([
            'user_id' => $user->id,
            'karyawan_id' => $karyawan->id,
            'jenis_produk' => 'fiber',
            'jenis_pengiriman' => 'export',
            'nomor_container_atau_plat' => 'CONT-LEAD-01',
            'cuaca' => 'kering',
            'waktu' => 'siang',
            'waktu_kedatangan_container' => Carbon::parse('2026-10-27 08:00:00'),
            'waktu_keberangkatan_container' => Carbon::parse('2026-10-27 09:45:00'),
            'tanggal_staging' => Carbon::today(),
            'warehouse_lokasi' => 'atas',
            'status' => 'submitted',
            'submitted_at' => Carbon::parse('2026-10-27 09:46:00'),
        ]);

        $this->assertEquals(105, $shipment->lead_time_minutes);
        $this->assertEquals('1 jam 45 menit', $shipment->lead_time_formatted);

        $response = $this->actingAs($user)->get('/');
        $response->assertStatus(200);
        $response->assertSee('1 jam 45 menit');
        $response->assertSee('Avg: 1 jam 45 menit');
    }
}
