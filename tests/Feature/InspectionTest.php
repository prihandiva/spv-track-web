<?php

namespace Tests\Feature;

use App\Models\BaleInspection;
use App\Models\ContainerInspection;
use App\Models\Karyawan;
use App\Models\Role;
use App\Models\Shipment;
use App\Models\SopPhotoPoint;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InspectionTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Karyawan $karyawan;
    protected Shipment $shipment;

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

        $this->user = User::create([
            'nama_warehouse' => 'Warehouse Pusat',
            'email' => 'operator@spvtrack.com',
            'password' => bcrypt('password'),
            'role' => 'superadmin',
        ]);
        $this->actingAs($this->user);

        $this->karyawan = Karyawan::create([
            'nama' => 'Budi Santoso',
            'nomor_induk' => 'KRY-001',
            'posisi' => 'Operator Lapangan',
            'status' => 'aktif',
        ]);

        $this->shipment = Shipment::create([
            'user_id' => $this->user->id,
            'karyawan_id' => $this->karyawan->id,
            'shipment_group' => '100000001',
            'shipment_no' => '200000001',
            'plat_nomor' => 'B 1234 SPV',
            'nama_sopir' => 'Asep',
            'nomor_seal' => 'LENZ987654',
            'packing_list_no' => '900000001',
            'tujuan_pengiriman' => 'PT Maju Bersama',
            'agen_forwarding' => 'Maersk Line',
            'jenis_produk' => 'fiber',
            'jenis_pengiriman' => 'export',
            'nomor_container_atau_plat' => 'TGHU1234567',
            'cuaca' => 'kering',
            'waktu' => 'siang',
            'tanggal_staging' => now()->toDateString(),
            'warehouse_lokasi' => 'atas',
            'status' => 'draft',
        ]);
        $this->shipment->karyawans()->sync([$this->karyawan->id]);

        SopPhotoPoint::create([
            'jenis_pengiriman' => 'both',
            'urutan' => 1,
            'nama_titik' => 'Plat Nomor',
            'deskripsi' => 'Foto plat nomor',
            'tipe_item' => 'foto',
            'wajib' => true,
        ]);
    }

    public function test_can_view_container_inspection_form(): void
    {
        $response = $this->get(route('field-app.container-inspection', $this->shipment->id));
        $response->assertStatus(200);
        $response->assertSee('Form Inspeksi Kondisi Kontainer');
        $response->assertSee('TGHU1234567');
    }

    public function test_can_save_container_inspection(): void
    {
        $payload = [
            'loading_date' => now()->toDateString(),
            'loading_start_time' => '08:00',
            'loading_finish_time' => '10:30',
            'nomor_kontainer' => 'TGHU1234567',
            'nomor_seal' => 'LENZ987654',
            'nama_emkl_pelayaran' => 'Maersk Line',
            'nama_petugas_inspeksi' => 'Budi Santoso',
            'pintu_status' => 1,
            'pintu_catatan' => 'Pintu luar & dalam bersih, tidak bolong, swab test bersih',
            'dinding_kanan_status' => 1,
            'dinding_kanan_catatan' => 'Dinding kanan bersih, tidak berdebu, swab test bersih',
            'dinding_kiri_status' => 1,
            'dinding_kiri_catatan' => 'Dinding kiri bersih, tidak berdebu, swab test bersih',
            'dinding_depan_status' => 1,
            'dinding_depan_catatan' => 'Dinding depan bersih, tidak retak, swab test bersih',
            'atap_status' => 1,
            'atap_catatan' => 'Atap bersih, kering, tidak berkarat/bocor',
            'lantai_status' => 1,
            'lantai_catatan' => 'Lantai rata, bersih, bebas benda tajam/paku',
            'kesimpulan' => 'layak',
            'catatan' => 'Kontainer bersih dan siap digunakan',
            'nama_inspektor' => 'Budi Santoso',
            'tanggal_inspeksi' => now()->toDateString(),
            'next' => 'timeline',
        ];

        $response = $this->post(route('field-app.container-inspection.save', $this->shipment->id), $payload);
        $response->assertRedirect(route('field-app.timeline', $this->shipment->id));

        $this->assertDatabaseHas('container_inspections', [
            'shipment_id' => $this->shipment->id,
            'nomor_kontainer' => 'TGHU1234567',
            'kesimpulan' => 'layak',
            'pintu_catatan' => 'Pintu luar & dalam bersih, tidak bolong, swab test bersih',
            'lantai_catatan' => 'Lantai rata, bersih, bebas benda tajam/paku',
        ]);
    }

    public function test_container_inspection_requires_catatan(): void
    {
        $payload = [
            'nomor_kontainer' => 'TGHU1234567',
            'nomor_seal' => 'LENZ987654',
            'nama_emkl_pelayaran' => 'Maersk Line',
            'nama_petugas_inspeksi' => 'Budi Santoso',
            'kesimpulan' => 'layak',
            'catatan' => '', // Empty catatan
        ];

        $response = $this->post(route('field-app.container-inspection.save', $this->shipment->id), $payload);
        $response->assertSessionHasErrors(['catatan']);
    }

    public function test_container_inspection_requires_point_notes_even_when_status_is_yes(): void
    {
        $payload = [
            'nomor_kontainer' => 'TGHU1234567',
            'nama_petugas_inspeksi' => 'Budi Santoso',
            'pintu_status' => 1, // Ya (Lolos)
            'pintu_catatan' => '', // Kosong tidak diperbolehkan
            'dinding_kanan_status' => 1,
            'dinding_kanan_catatan' => '',
            'kesimpulan' => 'layak',
            'catatan' => 'Catatan umum',
            'nama_inspektor' => 'Budi Santoso',
            'tanggal_inspeksi' => now()->toDateString(),
        ];

        $response = $this->post(route('field-app.container-inspection.save', $this->shipment->id), $payload);
        $response->assertSessionHasErrors(['pintu_catatan', 'dinding_kanan_catatan']);
    }

    public function test_can_view_bale_inspection_form(): void
    {
        $response = $this->get(route('field-app.bale-inspection', $this->shipment->id));
        $response->assertStatus(200);
        $response->assertSee('Form Inspeksi Kondisi Bale (Loading)');
        $response->assertSee('Verifikasi 4-Eyes Principle');
    }

    public function test_can_save_bale_inspection_with_items(): void
    {
        $items = [];
        for ($i = 1; $i <= 8; $i++) {
            $items[$i] = [
                'ada' => 0,
                'jumlah' => null,
                'tindakan' => null,
                'tindakan_lainnya' => null,
                'bale_awal' => null,
                'bale_pengganti' => null,
            ];
        }

        // Simulasikan temuan di item 1 (sobek)
        $items[1]['ada'] = 1;
        $items[1]['jumlah'] = 2;
        $items[1]['tindakan'] = 'diganti';
        $items[1]['bale_awal'] = 'BALE-001, BALE-002';
        $items[1]['bale_pengganti'] = 'BALE-101, BALE-102';

        $payload = [
            'loading_date' => now()->toDateString(),
            'loading_start_time' => '08:00',
            'loading_finish_time' => '10:30',
            'nama_petugas_loading' => 'Budi Santoso',
            'nomor_surat_jalan' => '900000001',
            'nama_pelanggan' => 'PT Maju Bersama',
            'jumlah_bale' => 120,
            'nomor_kontainer_truk' => 'TGHU1234567',
            'jenis_bale' => ['tx_ecovero'],
            'jenis_label' => ['fsc', 'isi'],
            'first_eye_nama' => 'Budi Santoso',
            'first_eye_jabatan' => 'Operator Lapangan',
            'first_eye_tanggal' => now()->toDateString(),
            'second_eye_status' => 'disetujui',
            'second_eye_catatan' => 'Semua bale telah diperiksa dan disetujui untuk loading.',
            'second_eye_nama' => 'Agus SPV',
            'second_eye_jabatan' => 'Supervisor',
            'second_eye_tanggal' => now()->toDateString(),
            'sumber_bale' => 'inspeksi_join_checking',
            'stagging_2_rubber_cone' => 1,
            'lokasi_stagging' => 'Gudang Atas',
            'cuaca' => 'kering',
            'waktu' => 'siang',
            'kesimpulan_akhir' => 'layak',
            'items' => $items,
        ];

        $response = $this->post(route('field-app.bale-inspection.save', $this->shipment->id), $payload);
        $response->assertRedirect(route('field-app.timeline', $this->shipment->id));

        $this->assertDatabaseHas('bale_inspections', [
            'shipment_id' => $this->shipment->id,
            'nomor_surat_jalan' => '900000001',
            'second_eye_catatan' => 'Semua bale telah diperiksa dan disetujui untuk loading.',
            'kesimpulan_akhir' => 'layak',
        ]);

        $this->assertDatabaseHas('bale_inspection_items', [
            'item_index' => 1,
            'ada_ketidaksesuaian' => true,
            'jumlah_bale' => 2,
            'tindakan_perbaikan' => 'diganti',
        ]);
    }

    public function test_can_view_print_views_for_container_and_bale(): void
    {
        $this->test_can_save_container_inspection();
        $this->test_can_save_bale_inspection_with_items();

        // Print Container
        $resContainer = $this->get(route('shipments.container-inspection.print', $this->shipment->id));
        $resContainer->assertStatus(200);
        $resContainer->assertSee('FORM INSPEKSI KONDISI KONTAINER');

        // Print Bale
        $resBale = $this->get(route('shipments.bale-inspection.print', $this->shipment->id));
        $resBale->assertStatus(200);
        $resBale->assertSee('FORM INSPEKSI KONDISI BALE (LOADING)');
    }

    public function test_shipment_report_contains_ordered_4_sections(): void
    {
        $this->test_can_save_container_inspection();
        $this->test_can_save_bale_inspection_with_items();

        $response = $this->get(route('shipments.report', $this->shipment->id));
        $response->assertStatus(200);

        // Halaman 1: Data Informasi Pengiriman (Shipment)
        $response->assertSee('1. Data Informasi Pengiriman (Shipment)');
        // Halaman 2: Form Inspeksi Kondisi Kontainer
        $response->assertSee('FORM INSPEKSI KONDISI KONTAINER');
        // Halaman 3: Form Inspeksi Kondisi Bale (Loading)
        $response->assertSee('FORM INSPEKSI KONDISI BALE (LOADING)');
        // Halaman 4+: Bukti Foto Loading Evidence
        $response->assertSee('4. Bukti Foto Loading Evidence (27 Titik SOP Inspeksi)');
    }

    public function test_shipment_show_page_displays_inspection_cards(): void
    {
        $this->test_can_save_container_inspection();
        $this->test_can_save_bale_inspection_with_items();

        $response = $this->get(route('shipments.show', $this->shipment->id));
        $response->assertStatus(200);
        $response->assertSee('Formulir Inspeksi Fisik (Lenzing Standards)');
        $response->assertSee('Form Inspeksi Kondisi Kontainer');
        $response->assertSee('Form Inspeksi Kondisi Bale (Loading)');
    }
}
