<?php

use App\Http\Controllers\FieldAppController;
use App\Http\Controllers\KaryawanController;
use App\Http\Controllers\ShipmentController;
use App\Http\Controllers\ShipmentPhotoController;
use App\Models\Karyawan;
use App\Models\Shipment;
use Carbon\Carbon;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $shipments = Shipment::with('karyawan')->latest()->take(6)->get();
    $totalShipments = Shipment::count();
    $totalDraft = Shipment::where('status', 'draft')->count();
    $totalSubmitted = Shipment::where('status', 'submitted')->count();
    $totalPetugas = Karyawan::where('status', 'aktif')->count();

    return view('dashboard', compact('shipments', 'totalShipments', 'totalDraft', 'totalSubmitted', 'totalPetugas'));
})->name('dashboard');

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

Route::get('/laporan', function () {
    return "<x-layout><div class='p-8'><h1 class='text-2xl font-bold'>Laporan</h1><p class='mt-4'>(Mock Page)</p></div></x-layout>";
})->name('laporan.index');
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
