<?php

namespace App\Http\Controllers;

use App\Models\ExternalWarehouse;
use App\Models\Shipment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExternalWarehouseController extends Controller
{
    /**
     * Display a listing of external warehouses.
     */
    public function index(Request $request): View
    {
        $query = ExternalWarehouse::latest();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                    ->orWhere('kode', 'like', "%{$search}%")
                    ->orWhere('pic_nama', 'like', "%{$search}%")
                    ->orWhere('alamat', 'like', "%{$search}%");
            });
        }

        if ($status = $request->input('status')) {
            if (in_array($status, ['aktif', 'nonaktif'], true)) {
                $query->where('status', $status);
            }
        }

        $warehouses = $query->paginate(10)->withQueryString();

        $totalWarehouses = ExternalWarehouse::count();
        $totalAktif = ExternalWarehouse::where('status', 'aktif')->count();
        $totalNonaktif = ExternalWarehouse::where('status', 'nonaktif')->count();

        return view('settings.external-warehouses.index', compact(
            'warehouses',
            'totalWarehouses',
            'totalAktif',
            'totalNonaktif'
        ));
    }

    /**
     * Store a newly created external warehouse in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255', 'unique:external_warehouses,nama'],
            'kode' => ['nullable', 'string', 'max:50'],
            'alamat' => ['nullable', 'string'],
            'pic_nama' => ['nullable', 'string', 'max:150'],
            'pic_kontak' => ['nullable', 'string', 'max:100'],
            'status' => ['required', 'in:aktif,nonaktif'],
            'catatan' => ['nullable', 'string'],
        ], [
            'nama.required' => 'Nama PT / Warehouse Eksternal wajib diisi.',
            'nama.unique' => 'Nama PT / Warehouse Eksternal ini sudah terdaftar.',
            'status.required' => 'Status aktif/nonaktif wajib dipilih.',
            'status.in' => 'Pilihan status tidak valid.',
        ]);

        $warehouse = ExternalWarehouse::create($validated);

        return redirect()->route('settings.external-warehouses.index')
            ->with('success', "Warehouse Eksternal \"{$warehouse->nama}\" berhasil ditambahkan ke master data.");
    }

    /**
     * Update the specified external warehouse in storage.
     */
    public function update(Request $request, ExternalWarehouse $externalWarehouse): RedirectResponse
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255', 'unique:external_warehouses,nama,'.$externalWarehouse->id],
            'kode' => ['nullable', 'string', 'max:50'],
            'alamat' => ['nullable', 'string'],
            'pic_nama' => ['nullable', 'string', 'max:150'],
            'pic_kontak' => ['nullable', 'string', 'max:100'],
            'status' => ['required', 'in:aktif,nonaktif'],
            'catatan' => ['nullable', 'string'],
        ], [
            'nama.required' => 'Nama PT / Warehouse Eksternal wajib diisi.',
            'nama.unique' => 'Nama PT / Warehouse Eksternal ini sudah digunakan oleh data lain.',
            'status.required' => 'Status aktif/nonaktif wajib dipilih.',
            'status.in' => 'Pilihan status tidak valid.',
        ]);

        $oldName = $externalWarehouse->nama;
        $externalWarehouse->update($validated);

        // Jika nama PT diubah, sinkronkan lokasi pada shipments terkait jika perlu
        if ($oldName !== $externalWarehouse->nama) {
            Shipment::where('warehouse_type', 'external')
                ->where('warehouse_lokasi', $oldName)
                ->update(['warehouse_lokasi' => $externalWarehouse->nama]);
        }

        return redirect()->route('settings.external-warehouses.index')
            ->with('success', "Data Warehouse Eksternal \"{$externalWarehouse->nama}\" berhasil diperbarui.");
    }

    /**
     * Toggle the active status of an external warehouse.
     */
    public function toggleStatus(ExternalWarehouse $externalWarehouse): RedirectResponse
    {
        $newStatus = $externalWarehouse->status === 'aktif' ? 'nonaktif' : 'aktif';
        $externalWarehouse->update(['status' => $newStatus]);

        $statusLabel = $newStatus === 'aktif' ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()->back()
            ->with('success', "Warehouse Eksternal \"{$externalWarehouse->nama}\" berhasil {$statusLabel}.");
    }

    /**
     * Remove the specified external warehouse from storage.
     */
    public function destroy(ExternalWarehouse $externalWarehouse): RedirectResponse
    {
        $nama = $externalWarehouse->nama;

        // Cek apakah pernah digunakan pada shipment
        $usageCount = Shipment::where('warehouse_type', 'external')
            ->where('warehouse_lokasi', $nama)
            ->count();

        if ($usageCount > 0) {
            // Jika ada shipment terkait, nonaktifkan alih-alih menghapus fisik untuk menjaga integritas data audit
            $externalWarehouse->update(['status' => 'nonaktif']);

            return redirect()->route('settings.external-warehouses.index')
                ->with('success', "Warehouse Eksternal \"{$nama}\" sedang digunakan pada {$usageCount} riwayat shipment. Statusnya berhasil dialihkan menjadi NONAKTIF agar tidak mengganggu histori laporan.");
        }

        $externalWarehouse->delete();

        return redirect()->route('settings.external-warehouses.index')
            ->with('success', "Warehouse Eksternal \"{$nama}\" berhasil dihapus dari master data.");
    }
}
