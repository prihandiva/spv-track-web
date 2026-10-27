<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExternalWarehouseController;
use App\Http\Controllers\FieldAppController;
use App\Http\Controllers\InspectionController;
use App\Http\Controllers\KaryawanController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\ShipmentController;
use App\Http\Controllers\ShipmentPhotoController;
use App\Http\Controllers\SopSettingController;
use App\Http\Controllers\SystemSettingController;
use App\Http\Controllers\UserController;
use Carbon\Carbon;
use Illuminate\Support\Facades\Route;

// Guest Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});

// Logout Route (POST & GET fallback)
Route::match(['get', 'post'], '/logout', [AuthController::class, 'logout'])->name('logout');

// Protected Routes (Session Required)
Route::middleware('auth')->group(function () {

    // 1. Dashboard (All roles: superadmin, admin, operator)
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // 2. Monitoring Shipments (Viewable by all roles)
    Route::get('/shipments', [ShipmentController::class, 'index'])->name('shipments.index');

    // 3. Downloads, Laporan & Petugas (Admin & Superadmin)
    Route::middleware('role:admin,superadmin')->group(function () {
        Route::get('/shipments/download-batch-zip', [ShipmentController::class, 'downloadBatchZip'])->name('shipments.download-batch-zip');
        Route::get('/shipments/batch-zip-preview', [ShipmentController::class, 'previewBatchZip'])->name('shipments.batch-zip-preview');
        Route::get('/shipments/{shipment}/download-evidence', [ShipmentController::class, 'downloadZip'])->name('shipments.download-evidence')->whereNumber('shipment');
        Route::get('/shipments/{shipment}/report', [ShipmentController::class, 'printReport'])->name('shipments.report')->whereNumber('shipment');
        Route::get('/shipments/{shipment}/download-pdf', [ShipmentController::class, 'downloadPdf'])->name('shipments.download-pdf')->whereNumber('shipment');

        // Laporan & Audit Staging
        Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan.index');
        Route::get('/laporan/export-excel', [LaporanController::class, 'exportExcel'])->name('laporan.export-excel');
        Route::get('/laporan/print-rekap', [LaporanController::class, 'printRekap'])->name('laporan.print-rekap');
        Route::get('/laporan/download-rekap-pdf', [LaporanController::class, 'downloadRekapPdf'])->name('laporan.download-rekap-pdf');

        // Petugas Lapangan (Karyawan)
        Route::patch('/karyawan/{karyawan}/toggle-status', [KaryawanController::class, 'toggleStatus'])->name('karyawan.toggle-status');
        Route::resource('karyawan', KaryawanController::class);
    });

    Route::get('/shipments/{shipment}', [ShipmentController::class, 'show'])->name('shipments.show')->whereNumber('shipment');
    Route::delete('/shipments/{shipment}', [ShipmentController::class, 'destroy'])->name('shipments.destroy')->whereNumber('shipment')->middleware('role:superadmin');

    // 4. Create & Edit Shipment (Form Staging Input)
    Route::middleware('role:operator,superadmin')->group(function () {
        Route::get('/field-app/create', [FieldAppController::class, 'create'])->name('field-app.create');
        Route::post('/field-app', [FieldAppController::class, 'store'])->name('field-app.store');
    });

    // Edit & Update Shipment (Operator untuk draft; Admin & Superadmin untuk submitted & draft)
    Route::get('/field-app/{shipment}/edit', [FieldAppController::class, 'edit'])->name('field-app.edit');
    Route::put('/field-app/{shipment}', [FieldAppController::class, 'update'])->name('field-app.update');

    // 5. Field App Execution, Uploads & Timeline
    Route::get('/field-app/{shipment}/timeline', [FieldAppController::class, 'timeline'])->name('field-app.timeline');
    Route::post('/field-app/{shipment}/upload-photo', [FieldAppController::class, 'uploadPhoto'])->name('field-app.upload-photo');
    Route::post('/field-app/{shipment}/submit', [FieldAppController::class, 'submit'])->name('field-app.submit');
    Route::post('/field-app/ocr/shipment-order', [FieldAppController::class, 'ocrShipmentOrder'])->name('field-app.ocr.shipment-order');
    Route::post('/field-app/ocr/surat-jalan', [FieldAppController::class, 'ocrSuratJalan'])->name('field-app.ocr.surat-jalan');

    // 5b. Form Tambahan: Inspeksi Kontainer & Inspeksi Bale
    Route::get('/field-app/{shipment}/container-inspection', [InspectionController::class, 'editContainer'])->name('field-app.container-inspection');
    Route::post('/field-app/{shipment}/container-inspection', [InspectionController::class, 'saveContainer'])->name('field-app.container-inspection.save');
    Route::get('/field-app/{shipment}/bale-inspection', [InspectionController::class, 'editBale'])->name('field-app.bale-inspection');
    Route::post('/field-app/{shipment}/bale-inspection', [InspectionController::class, 'saveBale'])->name('field-app.bale-inspection.save');

    // Cetak & Download PDF Form Satuan (Container & Bale)
    Route::get('/shipments/{shipment}/container-inspection/print', [InspectionController::class, 'printContainer'])->name('shipments.container-inspection.print');
    Route::get('/shipments/{shipment}/container-inspection/pdf', [InspectionController::class, 'downloadContainerPdf'])->name('shipments.container-inspection.pdf');
    Route::get('/shipments/{shipment}/bale-inspection/print', [InspectionController::class, 'printBale'])->name('shipments.bale-inspection.print');
    Route::get('/shipments/{shipment}/bale-inspection/pdf', [InspectionController::class, 'downloadBalePdf'])->name('shipments.bale-inspection.pdf');

    // 6. Pengaturan & Master Data (HANYA SUPERADMIN)
    Route::prefix('pengaturan')->name('settings.')->middleware('role:superadmin')->group(function () {
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

        // Master Warehouse Eksternal
        Route::patch('/warehouse-eksternal/{externalWarehouse}/toggle-status', [ExternalWarehouseController::class, 'toggleStatus'])->name('external-warehouses.toggle-status');
        Route::resource('warehouse-eksternal', ExternalWarehouseController::class)->names('external-warehouses')->parameters(['warehouse-eksternal' => 'externalWarehouse']);

        // Parameter Sistem & Warehouse
        Route::get('/sistem', [SystemSettingController::class, 'index'])->name('system.index');
        Route::put('/sistem', [SystemSettingController::class, 'update'])->name('system.update');
    });

    // 7. Photo Serving & Temp Previews
    Route::get('/photos/{photo}/file', [ShipmentPhotoController::class, 'servePermanentPhoto'])->name('photos.file');
    Route::get('/photos/temp/{tempId}/file', [ShipmentPhotoController::class, 'previewTemp'])->name('photos.temp.file');
    Route::post('/photos/temp', [ShipmentPhotoController::class, 'uploadTemp'])->name('photos.temp.upload');
    Route::post('/shipments/{shipment}/photos/submit', [ShipmentPhotoController::class, 'submit'])->name('photos.submit');
});

// Server Time API (Public / Utility)
Route::get('/api/server-time', function () {
    $now = Carbon::now('Asia/Jakarta');

    return response()->json([
        'timestamp' => $now->getTimestampMs(),
        'date' => $now->locale('id')->isoFormat('dddd, DD MMMM YYYY'),
        'time' => $now->format('H:i:s'),
        'timezone' => 'WIB',
    ]);
})->name('api.server-time');
