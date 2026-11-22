<?php

use App\Http\Controllers\MobileApiController;
use Carbon\Carbon;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes for SPV-Track Mobile (Flutter) & External Integrations
|--------------------------------------------------------------------------
*/

// 1. Waktu Server WIB
Route::get('/server-time', function () {
    $now = Carbon::now('Asia/Jakarta');

    return response()->json([
        'timestamp' => $now->getTimestampMs(),
        'date' => $now->locale('id')->isoFormat('dddd, DD MMMM YYYY'),
        'time' => $now->format('H:i:s'),
        'timezone' => 'WIB',
        'formatted' => $now->format('d/m/Y H:i:s').' WIB',
    ]);
})->name('api.server-time');

// 2. Endpoint Terintegrasi untuk Aplikasi Mobile SPV-Track (Flutter)
Route::get('/dashboard', [MobileApiController::class, 'dashboard'])->name('api.mobile.dashboard');
Route::get('/shipments', [MobileApiController::class, 'shipments'])->name('api.mobile.shipments');
Route::get('/shipments/{shipment}', [MobileApiController::class, 'shipmentDetail'])->name('api.mobile.shipment-detail');
Route::post('/shipments', [MobileApiController::class, 'storeShipment'])->name('api.mobile.shipments.store');
Route::post('/shipments/{shipment}/evidence', [MobileApiController::class, 'uploadEvidence'])->name('api.mobile.shipments.evidence');
Route::post('/shipments/{shipment}/finalize', [MobileApiController::class, 'finalizeShipment'])->name('api.mobile.shipments.finalize');
Route::get('/karyawans', [MobileApiController::class, 'getKaryawans'])->name('api.mobile.karyawans');
Route::get('/sop-points', [MobileApiController::class, 'getSopPoints'])->name('api.mobile.sop-points');
Route::post('/ocr/shipment-order', [MobileApiController::class, 'ocrShipmentOrder'])->name('api.mobile.ocr.shipment-order');
Route::get('/evidence/{evidence}/file', [MobileApiController::class, 'serveEvidenceFile'])->name('api.mobile.evidence.file');


