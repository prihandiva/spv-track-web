<?php

use App\Http\Controllers\FieldAppController;
use App\Http\Controllers\ShipmentController;
use App\Models\Karyawan;
use App\Models\Shipment;
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
Route::get('/shipments/{shipment}', [ShipmentController::class, 'show'])->name('shipments.show');
Route::get('/karyawan', function () {
    return "<x-layout><div class='p-8'><h1 class='text-2xl font-bold'>Daftar Petugas</h1><p class='mt-4'>(Mock Page)</p></div></x-layout>";
})->name('karyawan.index');
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
