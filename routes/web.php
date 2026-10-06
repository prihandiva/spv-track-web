<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FieldAppController;
use App\Http\Controllers\KaryawanController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\ShipmentController;
use App\Http\Controllers\ShipmentPhotoController;
use App\Http\Controllers\SopSettingController;
use App\Http\Controllers\SystemSettingController;
use App\Http\Controllers\UserController;
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

// Pengaturan & Master Data Routes
Route::prefix('pengaturan')->name('settings.')->group(function () {
    Route::get('/', [SettingController::class, 'index'])->name('index');

    // Pengguna (User Management)
    Route::get('/pengguna', [UserController::class, 'index'])->name('users.index');
    Route::post('/pengguna', [UserController::class, 'store'])->name('users.store');
    Route::put('/pengguna/{user}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/pengguna/{user}', [UserController::class, 'destroy'])->name('users.destroy');

    // Role & Hak Akses
    Route::get('/role', [RoleController::class, 'index'])->name('roles.index');
    Route::post('/role', [RoleController::class, 'store'])->name('roles.store');
    Route::put('/role/{role}', [RoleController::class, 'update'])->name('roles.update');
    Route::delete('/role/{role}', [RoleController::class, 'destroy'])->name('roles.destroy');

    // Master SOP 27 Titik
    Route::get('/sop', [SopSettingController::class, 'index'])->name('sop.index');
    Route::post('/sop', [SopSettingController::class, 'store'])->name('sop.store');
    Route::put('/sop/{sopPhotoPoint}', [SopSettingController::class, 'update'])->name('sop.update');
    Route::patch('/sop/{sopPhotoPoint}/toggle', [SopSettingController::class, 'toggle'])->name('sop.toggle');
    Route::patch('/sop/{sopPhotoPoint}/move', [SopSettingController::class, 'move'])->name('sop.move');
    Route::delete('/sop/{sopPhotoPoint}', [SopSettingController::class, 'destroy'])->name('sop.destroy');
    Route::post('/sop/reset-defaults', [SopSettingController::class, 'resetDefaults'])->name('sop.reset-defaults');

    // Parameter Sistem & Warehouse
    Route::get('/sistem', [SystemSettingController::class, 'index'])->name('system.index');
    Route::put('/sistem', [SystemSettingController::class, 'update'])->name('system.update');
});

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
