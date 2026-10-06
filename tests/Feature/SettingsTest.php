<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\SopPhotoPoint;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Roles are already seeded in migration, ensure we have references
        Role::firstOrCreate(
            ['name' => 'superadmin'],
            [
                'display_name' => 'Super Administrator',
                'description' => 'Akses penuh',
                'permissions' => ['users.view', 'roles.view', 'settings.view', 'sop.view'],
                'is_system' => true,
            ]
        );

        Role::firstOrCreate(
            ['name' => 'admin'],
            [
                'display_name' => 'Admin Warehouse',
                'description' => 'Akses operasional',
                'permissions' => ['shipment.view', 'report.view'],
                'is_system' => true,
            ]
        );

        Role::firstOrCreate(
            ['name' => 'operator'],
            [
                'display_name' => 'Operator Lapangan',
                'description' => 'Akses lapangan',
                'permissions' => ['field_app.access', 'sop.view'],
                'is_system' => true,
            ]
        );

        User::create([
            'nama_warehouse' => 'Warehouse A',
            'email' => 'admin@spvtrack.com',
            'password' => bcrypt('password'),
            'role' => 'superadmin',
        ]);

        SopPhotoPoint::create([
            'urutan' => 1,
            'nama_titik' => 'Plat Nomor',
            'deskripsi' => 'Foto plat nomor truck/container',
            'tipe_item' => 'foto',
            'wajib' => true,
            'perlu_ocr_container' => false,
            'perlu_deteksi_orang' => false,
            'perlu_deteksi_barcode' => false,
            'jenis_pengiriman' => 'both',
        ]);

        SopPhotoPoint::create([
            'urutan' => 2,
            'nama_titik' => 'Foto di Loading Area',
            'deskripsi' => 'Photo staging area',
            'tipe_item' => 'foto',
            'wajib' => true,
            'perlu_ocr_container' => false,
            'perlu_deteksi_orang' => false,
            'perlu_deteksi_barcode' => false,
            'jenis_pengiriman' => 'both',
        ]);
    }

    public function test_settings_hub_page_is_accessible(): void
    {
        $response = $this->get(route('settings.index'));

        $response->assertStatus(200);
        $response->assertSee('Pusat Pengaturan &amp; Master Data', false);
        $response->assertSee('Manajemen Pengguna');
        $response->assertSee('Master 27 Titik SOP');
    }

    public function test_users_page_lists_users(): void
    {
        $response = $this->get(route('settings.users.index'));

        $response->assertStatus(200);
        $response->assertSee('Warehouse A');
        $response->assertSee('admin@spvtrack.com');
    }

    public function test_can_create_new_user(): void
    {
        $response = $this->post(route('settings.users.store'), [
            'nama_warehouse' => 'Operator Staging B',
            'email' => 'operator.b@spvtrack.com',
            'password' => 'secret123',
            'role' => 'operator',
        ]);

        $response->assertRedirect(route('settings.users.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'email' => 'operator.b@spvtrack.com',
            'nama_warehouse' => 'Operator Staging B',
            'role' => 'operator',
        ]);
    }

    public function test_can_update_user(): void
    {
        $user = User::create([
            'nama_warehouse' => 'Staff Lama',
            'email' => 'staff@spvtrack.com',
            'password' => bcrypt('password'),
            'role' => 'operator',
        ]);

        $response = $this->put(route('settings.users.update', $user), [
            'nama_warehouse' => 'Staff Diperbarui',
            'email' => 'staff.baru@spvtrack.com',
            'role' => 'admin',
        ]);

        $response->assertRedirect(route('settings.users.index'));
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'nama_warehouse' => 'Staff Diperbarui',
            'email' => 'staff.baru@spvtrack.com',
            'role' => 'admin',
        ]);
    }

    public function test_cannot_delete_last_superadmin(): void
    {
        $superadmin = User::where('role', 'superadmin')->first();

        $response = $this->delete(route('settings.users.destroy', $superadmin));

        $response->assertRedirect(route('settings.users.index'));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('users', ['id' => $superadmin->id]);
    }

    public function test_can_delete_regular_user(): void
    {
        $user = User::create([
            'nama_warehouse' => 'Operator Buang',
            'email' => 'hapus@spvtrack.com',
            'password' => bcrypt('password'),
            'role' => 'operator',
        ]);

        $response = $this->delete(route('settings.users.destroy', $user));

        $response->assertRedirect(route('settings.users.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_roles_page_is_accessible_and_can_update_permissions(): void
    {
        $role = Role::where('name', 'admin')->first();

        $response = $this->get(route('settings.roles.index', ['role_id' => $role->id]));
        $response->assertStatus(200);
        $response->assertSee('Admin Warehouse');

        $updateResponse = $this->put(route('settings.roles.update', $role), [
            'display_name' => 'Admin Warehouse Senior',
            'description' => 'Akses penuh laporan dan audit',
            'permissions' => ['shipment.view', 'report.view', 'report.download_pdf'],
        ]);

        $updateResponse->assertRedirect(route('settings.roles.index', ['role_id' => $role->id]));
        $this->assertDatabaseHas('roles', [
            'id' => $role->id,
            'display_name' => 'Admin Warehouse Senior',
        ]);

        $role->refresh();
        $this->assertTrue($role->hasPermission('report.download_pdf'));
    }

    public function test_can_create_custom_role(): void
    {
        $response = $this->post(route('settings.roles.store'), [
            'display_name' => 'Auditor ISO',
            'description' => 'Role untuk auditor eksternal',
            'permissions' => ['report.view', 'shipment.view'],
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('roles', [
            'display_name' => 'Auditor ISO',
            'is_system' => false,
        ]);
    }

    public function test_sop_points_page_lists_points_and_can_toggle(): void
    {
        $response = $this->get(route('settings.sop.index'));
        $response->assertStatus(200);
        $response->assertSee('Plat Nomor');

        $point = SopPhotoPoint::first();
        $this->assertFalse($point->perlu_ocr_container);

        $toggleResponse = $this->patch(route('settings.sop.toggle', $point), [
            'field' => 'perlu_ocr_container',
        ]);

        $toggleResponse->assertSessionHas('success');
        $point->refresh();
        $this->assertTrue($point->perlu_ocr_container);
    }

    public function test_can_create_and_update_sop_point(): void
    {
        $response = $this->post(route('settings.sop.store'), [
            'urutan' => 28,
            'nama_titik' => 'Foto Segel Tambahan',
            'deskripsi' => 'Foto segel nomor 2',
            'jenis_pengiriman' => 'export',
            'tipe_item' => 'foto',
            'wajib' => 1,
            'perlu_ocr_container' => 1,
        ]);

        $response->assertRedirect(route('settings.sop.index'));
        $this->assertDatabaseHas('sop_photo_points', [
            'urutan' => 28,
            'nama_titik' => 'Foto Segel Tambahan',
            'jenis_pengiriman' => 'export',
            'perlu_ocr_container' => true,
        ]);

        $newPoint = SopPhotoPoint::where('urutan', 28)->first();

        $updateResponse = $this->put(route('settings.sop.update', $newPoint), [
            'urutan' => 28,
            'nama_titik' => 'Foto Segel Revisi',
            'deskripsi' => 'Deskripsi baru',
            'jenis_pengiriman' => 'both',
            'tipe_item' => 'foto',
        ]);

        $updateResponse->assertRedirect(route('settings.sop.index'));
        $this->assertDatabaseHas('sop_photo_points', [
            'id' => $newPoint->id,
            'nama_titik' => 'Foto Segel Revisi',
            'jenis_pengiriman' => 'both',
        ]);
    }

    public function test_can_reset_sop_points_to_defaults(): void
    {
        $response = $this->post(route('settings.sop.reset-defaults'));

        $response->assertRedirect(route('settings.sop.index'));
        $response->assertSessionHas('success');

        $this->assertGreaterThanOrEqual(27, SopPhotoPoint::count());
    }

    public function test_system_settings_page_is_accessible_and_can_be_updated(): void
    {
        SystemSetting::set('app_name', 'Old Name');

        $response = $this->get(route('settings.system.index'));
        $response->assertStatus(200);

        $updateResponse = $this->put(route('settings.system.update'), [
            'app_name' => 'SPV-Track Pro 2026',
            'company_name' => 'South Pacific Viscose PT',
            'warehouse_default_name' => 'Warehouse Sentral',
            'warehouse_locations' => 'Atas, Tengah, Bawah, Sayap Timur',
            'product_types' => 'Fiber, Sodium, Chemical',
            'zip_naming_format' => '{NOMOR_CONTAINER}_FINAL',
            'ocr_auto_validation' => '1',
            'ai_person_detection' => '1',
            'watermark_evidence' => '1',
        ]);

        $updateResponse->assertRedirect(route('settings.system.index'));
        $updateResponse->assertSessionHas('success');

        $this->assertEquals('SPV-Track Pro 2026', SystemSetting::get('app_name'));
        $this->assertEquals('South Pacific Viscose PT', SystemSetting::get('company_name'));
        $locations = SystemSetting::get('warehouse_locations');
        $this->assertContains('Sayap Timur', $locations);
    }
}
