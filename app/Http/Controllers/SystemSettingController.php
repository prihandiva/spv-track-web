<?php

namespace App\Http\Controllers;

use App\Models\SystemSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SystemSettingController extends Controller
{
    /**
     * Display the system and warehouse parameter settings page.
     */
    public function index(): View
    {
        $settings = SystemSetting::all()->keyBy('key');

        return view('settings.system.index', compact('settings'));
    }

    /**
     * Update system and warehouse settings in database.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'app_name' => ['required', 'string', 'max:255'],
            'company_name' => ['required', 'string', 'max:255'],
            'warehouse_default_name' => ['required', 'string', 'max:255'],
            'warehouse_locations' => ['required', 'string'],
            'product_types' => ['required', 'string'],
            'zip_naming_format' => ['required', 'string', 'max:100'],
        ], [
            'app_name.required' => 'Nama aplikasi wajib diisi.',
            'company_name.required' => 'Nama perusahaan wajib diisi.',
            'warehouse_default_name.required' => 'Nama warehouse utama wajib diisi.',
            'warehouse_locations.required' => 'Daftar lokasi staging warehouse wajib diisi.',
            'product_types.required' => 'Daftar jenis produk wajib diisi.',
            'zip_naming_format.required' => 'Pola penamaan berkas ZIP wajib diisi.',
        ]);

        // General settings
        SystemSetting::set('app_name', $validated['app_name']);
        SystemSetting::set('company_name', $validated['company_name']);
        SystemSetting::set('warehouse_default_name', $validated['warehouse_default_name']);

        // Process locations (split comma)
        $locations = array_values(array_filter(array_map('trim', explode(',', $validated['warehouse_locations']))));
        SystemSetting::set('warehouse_locations', $locations);

        // Process product types (split comma)
        $products = array_values(array_filter(array_map('trim', explode(',', $validated['product_types']))));
        SystemSetting::set('product_types', $products);

        // AI & OCR toggles
        SystemSetting::set('ocr_auto_validation', $request->has('ocr_auto_validation') ? '1' : '0');
        SystemSetting::set('ai_person_detection', $request->has('ai_person_detection') ? '1' : '0');
        SystemSetting::set('ai_barcode_detection', $request->has('ai_barcode_detection') ? '1' : '0');

        // Evidence & Watermark
        SystemSetting::set('watermark_evidence', $request->has('watermark_evidence') ? '1' : '0');

        // Export settings
        SystemSetting::set('zip_naming_format', $validated['zip_naming_format']);

        return redirect()->route('settings.system.index')
            ->with('success', 'Seluruh konfigurasi parameter sistem dan warehouse berhasil disimpan ke database.');
    }
}
