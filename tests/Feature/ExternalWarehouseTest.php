<?php

namespace Tests\Feature;

use App\Models\ExternalWarehouse;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExternalWarehouseTest extends TestCase
{
    use RefreshDatabase;

    protected User $superadmin;
    protected User $operator;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(
            ['name' => 'superadmin'],
            [
                'display_name' => 'Super Administrator',
                'description' => 'Akses penuh',
                'permissions' => ['*'],
                'is_system' => true,
            ]
        );

        Role::firstOrCreate(
            ['name' => 'operator'],
            [
                'display_name' => 'Operator Lapangan',
                'description' => 'Akses lapangan',
                'permissions' => ['field_app.create'],
                'is_system' => true,
            ]
        );

        $this->superadmin = User::create([
            'nama_warehouse' => 'Super Admin Test',
            'username' => 'superadmintest',
            'email' => 'superadmin_test@spv.com',
            'password' => bcrypt('password123'),
            'role' => 'superadmin',
        ]);

        $this->operator = User::create([
            'nama_warehouse' => 'Operator Test',
            'username' => 'operatortest',
            'email' => 'operator_test@spv.com',
            'password' => bcrypt('password123'),
            'role' => 'operator',
        ]);
    }

    public function test_non_superadmin_cannot_access_external_warehouses(): void
    {
        $response = $this->actingAs($this->operator)
            ->get(route('settings.external-warehouses.index'));

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('error');
    }

    public function test_superadmin_can_view_external_warehouses_index(): void
    {
        ExternalWarehouse::create([
            'nama' => 'PT Mitra Logistik Unggul',
            'kode' => 'MLU',
            'status' => 'aktif',
        ]);

        $response = $this->actingAs($this->superadmin)
            ->get(route('settings.external-warehouses.index'));

        $response->assertStatus(200);
        $response->assertSee('PT Mitra Logistik Unggul');
        $response->assertSee('MLU');
    }

    public function test_superadmin_can_store_external_warehouse(): void
    {
        $response = $this->actingAs($this->superadmin)
            ->post(route('settings.external-warehouses.store'), [
                'nama' => 'PT Surya Mega Logistik',
                'kode' => 'SML',
                'status' => 'aktif',
                'pic_nama' => 'Bpk. Surya',
                'pic_kontak' => '081234567890',
                'alamat' => 'Kawasan Industri Rungkut',
                'catatan' => 'Rekanan strategis 2026',
            ]);

        $response->assertRedirect(route('settings.external-warehouses.index'));
        $this->assertDatabaseHas('external_warehouses', [
            'nama' => 'PT Surya Mega Logistik',
            'kode' => 'SML',
            'status' => 'aktif',
        ]);
    }

    public function test_store_validates_unique_name(): void
    {
        ExternalWarehouse::create([
            'nama' => 'PT Kembar Logistics',
            'status' => 'aktif',
        ]);

        $response = $this->actingAs($this->superadmin)
            ->post(route('settings.external-warehouses.store'), [
                'nama' => 'PT Kembar Logistics',
                'status' => 'aktif',
            ]);

        $response->assertSessionHasErrors(['nama']);
    }

    public function test_superadmin_can_update_external_warehouse(): void
    {
        $wh = ExternalWarehouse::create([
            'nama' => 'PT Lama Logistics',
            'kode' => 'LAMA',
            'status' => 'aktif',
        ]);

        $response = $this->actingAs($this->superadmin)
            ->put(route('settings.external-warehouses.update', $wh->id), [
                'nama' => 'PT Baru Logistics',
                'kode' => 'BARU',
                'status' => 'aktif',
            ]);

        $response->assertRedirect(route('settings.external-warehouses.index'));
        $this->assertDatabaseHas('external_warehouses', [
            'id' => $wh->id,
            'nama' => 'PT Baru Logistics',
            'kode' => 'BARU',
        ]);
    }

    public function test_superadmin_can_toggle_warehouse_status(): void
    {
        $wh = ExternalWarehouse::create([
            'nama' => 'PT Toggle Logistics',
            'status' => 'aktif',
        ]);

        $response = $this->actingAs($this->superadmin)
            ->patch(route('settings.external-warehouses.toggle-status', $wh->id));

        $response->assertRedirect();
        $this->assertDatabaseHas('external_warehouses', [
            'id' => $wh->id,
            'status' => 'nonaktif',
        ]);
    }

    public function test_superadmin_can_delete_external_warehouse(): void
    {
        $wh = ExternalWarehouse::create([
            'nama' => 'PT Hapus Logistics',
            'status' => 'nonaktif',
        ]);

        $response = $this->actingAs($this->superadmin)
            ->delete(route('settings.external-warehouses.destroy', $wh->id));

        $response->assertRedirect(route('settings.external-warehouses.index'));
        $this->assertDatabaseMissing('external_warehouses', [
            'id' => $wh->id,
        ]);
    }

    public function test_field_app_loads_active_warehouses_from_database(): void
    {
        ExternalWarehouse::create([
            'nama' => 'PT Active Wh Lapangan',
            'status' => 'aktif',
        ]);

        ExternalWarehouse::create([
            'nama' => 'PT Inactive Wh Tersembunyi',
            'status' => 'nonaktif',
        ]);

        $response = $this->actingAs($this->operator)
            ->get(route('field-app.create'));

        $response->assertStatus(200);
        $response->assertSee('PT Active Wh Lapangan');
        $response->assertDontSee('PT Inactive Wh Tersembunyi');
    }
}
