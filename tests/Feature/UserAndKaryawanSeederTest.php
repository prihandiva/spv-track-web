<?php

namespace Tests\Feature;

use App\Models\Karyawan;
use App\Models\User;
use Database\Seeders\KaryawanSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserAndKaryawanSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_seeder_creates_expected_users(): void
    {
        $this->seed(UserSeeder::class);

        // Verify Superadmin
        $superadmin = User::where('email', 'dadan@spvtrack.com')->first();
        $this->assertNotNull($superadmin);
        $this->assertEquals('DADAN NURHIDAYAT', $superadmin->nama_warehouse);
        $this->assertEquals('dadan', $superadmin->username);
        $this->assertEquals('superadmin', $superadmin->role);
        $this->assertTrue(Hash::check('password', $superadmin->password));

        // Verify Admin
        $admin = User::where('email', 'hendri@spvtrack.com')->first();
        $this->assertNotNull($admin);
        $this->assertEquals('HENDRI PURNOMO', $admin->nama_warehouse);
        $this->assertEquals('hendri', $admin->username);
        $this->assertEquals('admin', $admin->role);
        $this->assertTrue(Hash::check('password', $admin->password));

        // Verify System / Demo Accounts
        $this->assertDatabaseHas('users', ['email' => 'superadmin@spvtrack.com', 'role' => 'superadmin']);
        $this->assertDatabaseHas('users', ['email' => 'admin@spvtrack.com', 'role' => 'admin']);
        $this->assertDatabaseHas('users', ['email' => 'operator@spvtrack.com', 'role' => 'operator']);

        // Verify 29 unique operators
        $sampleOperators = [
            'Agus Rohiman' => 'agus.rohiman',
            'Dede Jaenudin' => 'dede.jaenudin',
            'Muhammad Ikhsan' => 'muhammad.ikhsan',
            'Slamet Liehardjo' => 'slamet.liehardjo',
            'Surono' => 'surono',
            'Ujang Jamaludin' => 'ujang.jamaludin',
            'Wahyudin' => 'wahyudin',
        ];

        foreach ($sampleOperators as $name => $username) {
            $user = User::where('email', "{$username}@spvtrack.com")->first();
            $this->assertNotNull($user, "User for {$name} should exist.");
            $this->assertEquals($name, $user->nama_warehouse);
            $this->assertEquals('operator', $user->role);
        }

        // Total operator users should be 29 + 1 (demo operator) = 30
        $this->assertEquals(30, User::where('role', 'operator')->count());
    }

    public function test_karyawan_seeder_creates_29_unique_operators(): void
    {
        $this->seed(KaryawanSeeder::class);

        // Verify 29 operators
        $this->assertEquals(29, Karyawan::where('position', 'operator')->count());

        $sampleNames = [
            'AGUS ROHIMAN',
            'DEDE JAENUDIN',
            'MUHAMMAD IKHSAN',
            'SLAMET LIEHARDJO',
            'SURONO',
            'UJANG JAMALUDIN',
            'WAHYUDIN',
        ];

        foreach ($sampleNames as $name) {
            $karyawan = Karyawan::where('nama', $name)->first();
            $this->assertNotNull($karyawan, "Karyawan for {$name} should exist.");
            $this->assertEquals('aktif', $karyawan->status);
            $this->assertEquals('operator', $karyawan->position);
            $this->assertStringStartsWith('OPR', $karyawan->nomor_induk);
        }
    }
}
