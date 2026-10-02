<?php

namespace App\Http\Controllers;

use App\Models\Karyawan;
use App\Models\Shipment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class KaryawanController extends Controller
{
    /**
     * Display a listing of the petugas.
     */
    public function index(Request $request): View
    {
        $query = Karyawan::withCount('shipments')->latest();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                    ->orWhere('nomor_induk', 'like', "%{$search}%");
            });
        }

        if ($status = $request->input('status')) {
            if (in_array($status, ['aktif', 'nonaktif'], true)) {
                $query->where('status', $status);
            }
        }

        $karyawans = $query->paginate(10)->withQueryString();

        $totalPetugas = Karyawan::count();
        $totalAktif = Karyawan::where('status', 'aktif')->count();
        $totalNonaktif = Karyawan::where('status', 'nonaktif')->count();
        $totalShipmentsHandled = Shipment::whereNotNull('karyawan_id')->count();

        return view('karyawan.index', compact(
            'karyawans',
            'totalPetugas',
            'totalAktif',
            'totalNonaktif',
            'totalShipmentsHandled'
        ));
    }

    /**
     * Show the form for creating a new petugas.
     */
    public function create(): View
    {
        return view('karyawan.create');
    }

    /**
     * Store a newly created petugas in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'nomor_induk' => ['required', 'string', 'max:50', 'unique:karyawans,nomor_induk'],
            'status' => ['required', 'in:aktif,nonaktif'],
        ], [
            'nama.required' => 'Nama petugas wajib diisi.',
            'nomor_induk.required' => 'Nomor induk / NIK wajib diisi.',
            'nomor_induk.unique' => 'Nomor induk / NIK ini sudah terdaftar.',
            'status.required' => 'Status petugas wajib dipilih.',
        ]);

        $karyawan = Karyawan::create($validated);

        return redirect()->route('karyawan.index')
            ->with('success', "Petugas {$karyawan->nama} ({$karyawan->nomor_induk}) berhasil ditambahkan.");
    }

    /**
     * Display the specified petugas and their shipments.
     */
    public function show(Karyawan $karyawan): View
    {
        $karyawan->loadCount('shipments');
        $shipments = $karyawan->shipments()
            ->with(['evidenceItems'])
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $totalSubmitted = $karyawan->shipments()->where('status', 'submitted')->count();
        $totalDraft = $karyawan->shipments()->where('status', 'draft')->count();

        return view('karyawan.show', compact('karyawan', 'shipments', 'totalSubmitted', 'totalDraft'));
    }

    /**
     * Show the form for editing the specified petugas.
     */
    public function edit(Karyawan $karyawan): View
    {
        return view('karyawan.edit', compact('karyawan'));
    }

    /**
     * Update the specified petugas in storage.
     */
    public function update(Request $request, Karyawan $karyawan): RedirectResponse
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'nomor_induk' => [
                'required',
                'string',
                'max:50',
                Rule::unique('karyawans', 'nomor_induk')->ignore($karyawan->id),
            ],
            'status' => ['required', 'in:aktif,nonaktif'],
        ], [
            'nama.required' => 'Nama petugas wajib diisi.',
            'nomor_induk.required' => 'Nomor induk / NIK wajib diisi.',
            'nomor_induk.unique' => 'Nomor induk / NIK ini sudah digunakan oleh petugas lain.',
            'status.required' => 'Status petugas wajib dipilih.',
        ]);

        $karyawan->update($validated);

        return redirect()->route('karyawan.index')
            ->with('success', "Data petugas {$karyawan->nama} berhasil diperbarui.");
    }

    /**
     * Remove the specified petugas from storage.
     */
    public function destroy(Karyawan $karyawan): RedirectResponse
    {
        if ($karyawan->shipments()->exists()) {
            return back()->with('error', "Petugas {$karyawan->nama} tidak dapat dihapus karena memiliki riwayat staging/shipment. Silakan nonaktifkan status petugas jika sudah tidak bertugas.");
        }

        $nama = $karyawan->nama;
        $karyawan->delete();

        return redirect()->route('karyawan.index')
            ->with('success', "Petugas {$nama} berhasil dihapus.");
    }

    /**
     * Toggle active/inactive status for the specified petugas.
     */
    public function toggleStatus(Karyawan $karyawan): RedirectResponse
    {
        $newStatus = $karyawan->status === 'aktif' ? 'nonaktif' : 'aktif';
        $karyawan->update(['status' => $newStatus]);

        $statusLabel = $newStatus === 'aktif' ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "Status petugas {$karyawan->nama} berhasil {$statusLabel}.");
    }
}
