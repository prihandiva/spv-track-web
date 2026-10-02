<?php

use App\Http\Controllers\ShipmentPhotoController;
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

// 2. Tahap 1: Upload Foto Sementara (Burn-in Timestamp Server)
Route::post('/photos/temp', [ShipmentPhotoController::class, 'uploadTemp'])->name('api.photos.temp.upload');
Route::get('/photos/temp/{tempId}/file', [ShipmentPhotoController::class, 'previewTemp'])->name('api.photos.temp.file');

// 3. Tahap 2: Submit Final Foto Shipment (Penyimpanan Permanen & Immutable)
Route::post('/shipments/{shipment}/submit', [ShipmentPhotoController::class, 'submit'])->name('api.shipments.submit');
Route::post('/shipments/{shipment}/photos/submit', [ShipmentPhotoController::class, 'submit'])->name('api.shipments.photos.submit');

// 4. Akses File Foto Permanen & Listing Foto
Route::get('/photos/{photo}/file', [ShipmentPhotoController::class, 'servePermanentPhoto'])->name('api.photos.file');
Route::get('/shipments/{shipment}/photos', [ShipmentPhotoController::class, 'index'])->name('api.shipments.photos.index');
