<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FieldAppController;
use App\Http\Controllers\KaryawanController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\ShipmentController;
use App\Http\Controllers\ShipmentPhotoController;
use App\Models\Karyawan;
use Carbon\Carbon;
use Illuminate\Support\Facades\Route;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

Route::get('/shipments', [ShipmentController::class, 'index'])->name('shipments.index');
Route::get('/shipments/download-batch-zip', [ShipmentController::class, 'downloadBatchZip'])->name('shipments.download-batch-zip');
Route::get('/shipments/batch-zip-preview', [ShipmentController::class, 'previewBatchZip'])->name('shipments.batch-zip-preview');
Route::get('/shipments/{shipment}', [ShipmentController::class, 'show'])->name('shipments.show');
Route::get('/shipments/{shipment}/download-evidence', [ShipmentController::class, 'downloadZip'])->name('shipments.download-evidence');
Route::get('/shipments/{shipment}/report', [ShipmentController::class, 'printReport'])->name('shipments.report');
Route::get('/shipments/{shipment}/download-pdf', [ShipmentController::class, 'downloadPdf'])->name('shipments.download-pdf');

// Karyawan (Petugas) CRUD Routes
Route::patch('/karyawan/{karyawan}/toggle-status', [KaryawanController::class, 'toggleStatus'])->name('karyawan.toggle-status');
Route::resource('karyawan', KaryawanController::class);

// Laporan & Audit Staging Routes
Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan.index');
Route::get('/laporan/export-excel', [LaporanController::class, 'exportExcel'])->name('laporan.export-excel');
Route::get('/laporan/print-rekap', [LaporanController::class, 'printRekap'])->name('laporan.print-rekap');
Route::get('/laporan/download-rekap-pdf', [LaporanController::class, 'downloadRekapPdf'])->name('laporan.download-rekap-pdf');

Route::get('/pengaturan', function () {
    return "<x-layout><div class='p-8'><h1 class='text-2xl font-bold'>Pengaturan</h1><p class='mt-4'>(Mock Page)</p></div></x-layout>";
})->name('settings.index');

// Field App Routes
Route::get('/field-app/create', [FieldAppController::class, 'create'])->name('field-app.create');
Route::post('/field-app', [FieldAppController::class, 'store'])->name('field-app.store');
Route::get('/field-app/{shipment}/timeline', [FieldAppController::class, 'timeline'])->name('field-app.timeline');
Route::post('/field-app/{shipment}/upload-photo', [FieldAppController::class, 'uploadPhoto'])->name('field-app.upload-photo');
Route::post('/field-app/{shipment}/submit', [FieldAppController::class, 'submit'])->name('field-app.submit');

// OCR Routes
Route::post('/field-app/ocr/shipment-order', [FieldAppController::class, 'ocrShipmentOrder'])->name('field-app.ocr.shipment-order');
Route::post('/field-app/ocr/surat-jalan', [FieldAppController::class, 'ocrSuratJalan'])->name('field-app.ocr.surat-jalan');

// Server Time API
Route::get('/api/server-time', function () {
    $now = Carbon::now('Asia/Jakarta');

    return response()->json([
        'timestamp' => $now->getTimestampMs(),
        'date' => $now->locale('id')->isoFormat('dddd, DD MMMM YYYY'),
        'time' => $now->format('H:i:s'),
        'timezone' => 'WIB',
    ]);
})->name('api.server-time');

// Photo Serving & Web Previews
Route::get('/photos/{photo}/file', [ShipmentPhotoController::class, 'servePermanentPhoto'])->name('photos.file');
Route::get('/photos/temp/{tempId}/file', [ShipmentPhotoController::class, 'previewTemp'])->name('photos.temp.file');
Route::post('/photos/temp', [ShipmentPhotoController::class, 'uploadTemp'])->name('photos.temp.upload');
Route::post('/shipments/{shipment}/photos/submit', [ShipmentPhotoController::class, 'submit'])->name('photos.submit');
