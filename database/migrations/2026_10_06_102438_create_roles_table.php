<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('display_name');
            $table->text('description')->nullable();
            $table->json('permissions')->nullable();
            $table->boolean('is_system')->default(false);
            $table->timestamps();
        });

        $defaultRoles = [
            [
                'name' => 'superadmin',
                'display_name' => 'Super Administrator',
                'description' => 'Akses penuh ke seluruh sistem: kelola akun pengguna, role & hak akses, master SOP 27 titik, konfigurasi sistem, dan seluruh fitur laporan.',
                'permissions' => json_encode([
                    'shipment.view', 'shipment.create', 'shipment.edit', 'shipment.delete',
                    'report.view', 'report.download_pdf', 'report.download_excel', 'report.download_zip',
                    'petugas.view', 'petugas.create', 'petugas.edit', 'petugas.delete',
                    'sop.view', 'sop.create', 'sop.edit', 'sop.delete',
                    'users.view', 'users.create', 'users.edit', 'users.delete',
                    'roles.view', 'roles.edit',
                    'settings.view', 'settings.edit',
                ]),
                'is_system' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'admin',
                'display_name' => 'Admin Warehouse',
                'description' => 'Akses monitoring dan verifikasi: download laporan PDF/ZIP/Excel, koreksi data, audit staging, dan kelola petugas lapangan.',
                'permissions' => json_encode([
                    'shipment.view', 'shipment.edit',
                    'report.view', 'report.download_pdf', 'report.download_excel', 'report.download_zip',
                    'petugas.view', 'petugas.create', 'petugas.edit',
                    'sop.view',
                    'users.view',
                    'settings.view',
                ]),
                'is_system' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'operator',
                'display_name' => 'Operator Lapangan',
                'description' => 'Akses khusus Field App: input data shipment, upload foto/dokumen 27 titik SOP, dan submit evidence staging.',
                'permissions' => json_encode([
                    'shipment.view', 'shipment.create',
                    'field_app.access', 'field_app.upload', 'field_app.submit',
                    'sop.view',
                ]),
                'is_system' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('roles')->insert($defaultRoles);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
