<?php

namespace App\Http\Controllers;

use App\Models\SopPhotoPoint;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SopSettingController extends Controller
{
    /**
     * Display a listing of Master SOP photo points.
     */
    public function index(Request $request): View
    {
        $query = SopPhotoPoint::withCount('evidenceItems')->ordered();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_titik', 'like', "%{$search}%")
                    ->orWhere('deskripsi', 'like', "%{$search}%");
            });
        }

        if ($pengiriman = $request->input('jenis_pengiriman')) {
            if (in_array($pengiriman, ['both', 'export', 'lokal'], true)) {
                $query->where('jenis_pengiriman', $pengiriman);
            }
        }

        if ($tipe = $request->input('tipe_item')) {
            if (in_array($tipe, ['foto', 'dokumen', 'video'], true)) {
                $query->where('tipe_item', $tipe);
            }
        }

        if ($request->has('wajib') && $request->input('wajib') !== '') {
            $query->where('wajib', (bool) $request->input('wajib'));
        }

        $points = $query->get();

        // Statistics
        $totalPoints = SopPhotoPoint::count();
        $totalWajib = SopPhotoPoint::where('wajib', true)->count();
        $totalOpsional = SopPhotoPoint::where('wajib', false)->count();
        $totalOcr = SopPhotoPoint::where('perlu_ocr_container', true)->count();
        $totalAiPerson = SopPhotoPoint::where('perlu_deteksi_orang', true)->count();
        $totalAiBarcode = SopPhotoPoint::where('perlu_deteksi_barcode', true)->count();

        $maxUrutan = (int) SopPhotoPoint::max('urutan');

        return view('settings.sop.index', compact(
            'points',
            'totalPoints',
            'totalWajib',
            'totalOpsional',
            'totalOcr',
            'totalAiPerson',
            'totalAiBarcode',
            'maxUrutan'
        ));
    }

    /**
     * Store a newly created SOP point in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama_titik' => ['required', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string', 'max:1000'],
            'urutan' => ['required', 'integer', 'min:1'],
            'jenis_pengiriman' => ['required', 'in:both,export,lokal'],
            'tipe_item' => ['required', 'in:foto,dokumen,video'],
            'wajib' => ['nullable', 'boolean'],
            'perlu_ocr_container' => ['nullable', 'boolean'],
            'perlu_deteksi_orang' => ['nullable', 'boolean'],
            'perlu_deteksi_barcode' => ['nullable', 'boolean'],
        ], [
            'nama_titik.required' => 'Nama titik SOP wajib diisi.',
            'urutan.required' => 'Nomor urutan titik wajib ditentukan.',
            'jenis_pengiriman.required' => 'Target jenis pengiriman wajib dipilih.',
            'tipe_item.required' => 'Tipe item evidence wajib dipilih.',
        ]);

        $validated['wajib'] = $request->boolean('wajib');
        $validated['perlu_ocr_container'] = $request->boolean('perlu_ocr_container');
        $validated['perlu_deteksi_orang'] = $request->boolean('perlu_deteksi_orang');
        $validated['perlu_deteksi_barcode'] = $request->boolean('perlu_deteksi_barcode');

        $point = SopPhotoPoint::create($validated);

        return redirect()->route('settings.sop.index')
            ->with('success', "Titik SOP #{$point->urutan} ({$point->nama_titik}) berhasil ditambahkan ke database.");
    }

    /**
     * Update the specified SOP point in storage.
     */
    public function update(Request $request, SopPhotoPoint $sopPhotoPoint): RedirectResponse
    {
        $validated = $request->validate([
            'nama_titik' => ['required', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string', 'max:1000'],
            'urutan' => ['required', 'integer', 'min:1'],
            'jenis_pengiriman' => ['required', 'in:both,export,lokal'],
            'tipe_item' => ['required', 'in:foto,dokumen,video'],
            'wajib' => ['nullable', 'boolean'],
            'perlu_ocr_container' => ['nullable', 'boolean'],
            'perlu_deteksi_orang' => ['nullable', 'boolean'],
            'perlu_deteksi_barcode' => ['nullable', 'boolean'],
        ], [
            'nama_titik.required' => 'Nama titik SOP wajib diisi.',
            'urutan.required' => 'Nomor urutan titik wajib ditentukan.',
            'jenis_pengiriman.required' => 'Target jenis pengiriman wajib dipilih.',
            'tipe_item.required' => 'Tipe item evidence wajib dipilih.',
        ]);

        $validated['wajib'] = $request->boolean('wajib');
        $validated['perlu_ocr_container'] = $request->boolean('perlu_ocr_container');
        $validated['perlu_deteksi_orang'] = $request->boolean('perlu_deteksi_orang');
        $validated['perlu_deteksi_barcode'] = $request->boolean('perlu_deteksi_barcode');

        $sopPhotoPoint->update($validated);

        return redirect()->route('settings.sop.index')
            ->with('success', "Perubahan titik SOP #{$sopPhotoPoint->urutan} ({$sopPhotoPoint->nama_titik}) berhasil disimpan.");
    }

    /**
     * Quick toggle for SOP boolean fields (wajib, AI flags).
     */
    public function toggle(Request $request, SopPhotoPoint $sopPhotoPoint): RedirectResponse|JsonResponse
    {
        $field = $request->input('field');

        $allowedFields = ['wajib', 'perlu_ocr_container', 'perlu_deteksi_orang', 'perlu_deteksi_barcode'];

        if (! in_array($field, $allowedFields, true)) {
            return back()->with('error', 'Bidang pengaturan tidak valid.');
        }

        $sopPhotoPoint->$field = ! $sopPhotoPoint->$field;
        $sopPhotoPoint->save();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'field' => $field,
                'value' => $sopPhotoPoint->$field,
            ]);
        }

        $fieldName = match ($field) {
            'wajib' => 'Kewajiban titik',
            'perlu_ocr_container' => 'Validasi OCR Container',
            'perlu_deteksi_orang' => 'Deteksi Orang AI',
            'perlu_deteksi_barcode' => 'Deteksi Barcode AI',
            default => $field,
        };

        $statusText = $sopPhotoPoint->$field ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "{$fieldName} untuk Titik #{$sopPhotoPoint->urutan} ({$sopPhotoPoint->nama_titik}) {$statusText}.");
    }

    /**
     * Swap sequence up or down.
     */
    public function move(Request $request, SopPhotoPoint $sopPhotoPoint): RedirectResponse
    {
        $direction = $request->input('direction'); // 'up' or 'down'

        if ($direction === 'up') {
            $previous = SopPhotoPoint::where('urutan', '<', $sopPhotoPoint->urutan)
                ->orderBy('urutan', 'desc')
                ->first();

            if ($previous) {
                $temp = $sopPhotoPoint->urutan;
                $sopPhotoPoint->urutan = $previous->urutan;
                $previous->urutan = $temp;
                $sopPhotoPoint->save();
                $previous->save();
            }
        } elseif ($direction === 'down') {
            $next = SopPhotoPoint::where('urutan', '>', $sopPhotoPoint->urutan)
                ->orderBy('urutan', 'asc')
                ->first();

            if ($next) {
                $temp = $sopPhotoPoint->urutan;
                $sopPhotoPoint->urutan = $next->urutan;
                $next->urutan = $temp;
                $sopPhotoPoint->save();
                $next->save();
            }
        }

        return redirect()->route('settings.sop.index')
            ->with('success', "Urutan Titik SOP #{$sopPhotoPoint->urutan} berhasil diperbarui.");
    }

    /**
     * Remove the specified SOP point.
     */
    public function destroy(SopPhotoPoint $sopPhotoPoint): RedirectResponse
    {
        $evidenceCount = $sopPhotoPoint->evidenceItems()->count();

        if ($evidenceCount > 0) {
            return redirect()->route('settings.sop.index')
                ->with('error', "Titik SOP ini tidak dapat dihapus karena sudah memiliki {$evidenceCount} bukti foto staging tersimpan. Anda dapat mengubah statusnya menjadi tidak wajib jika tidak lagi diperlukan.");
        }

        $nama = $sopPhotoPoint->nama_titik;
        $urutan = $sopPhotoPoint->urutan;
        $sopPhotoPoint->delete();

        return redirect()->route('settings.sop.index')
            ->with('success', "Titik SOP #{$urutan} ({$nama}) berhasil dihapus dari database.");
    }

    /**
     * Restore default 27 standard SOP points.
     */
    public function resetDefaults(): RedirectResponse
    {
        $defaultPoints = SopPhotoPoint::defaultPoints();

        foreach ($defaultPoints as $item) {
            SopPhotoPoint::updateOrCreate(
                ['urutan' => $item['urutan']],
                $item
            );
        }

        return redirect()->route('settings.sop.index')
            ->with('success', 'Master Data 27 Titik SOP standar berhasil diselaraskan dan diperbarui ke database.');
    }
}
