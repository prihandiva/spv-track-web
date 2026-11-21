<?php

namespace App\Http\Controllers;

use App\Models\ExternalWarehouse;
use App\Models\Role;
use App\Models\SopPhotoPoint;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\View\View;

class SettingController extends Controller
{
    /**
     * Display settings hub overview.
     */
    public function index(): View
    {
        $totalUsers = User::count();
        $totalSuperadmin = User::where('role', 'superadmin')->count();
        $totalAdmin = User::where('role', 'admin')->count();
        $totalOperator = User::where('role', 'operator')->count();

        $totalSop = SopPhotoPoint::count();
        $totalSopWajib = SopPhotoPoint::where('wajib', true)->count();
        $totalSopFoto = SopPhotoPoint::where('tipe_item', 'foto')->count();
        $totalSopVideo = SopPhotoPoint::where('tipe_item', 'video')->count();

        $totalExternalWarehouses = ExternalWarehouse::count();
        $totalExternalWarehousesActive = ExternalWarehouse::where('status', 'aktif')->count();

        $settings = SystemSetting::all();

        return view('settings.index', compact(
            'totalUsers',
            'totalSuperadmin',
            'totalAdmin',
            'totalOperator',
            'totalSop',
            'totalSopWajib',
            'totalSopFoto',
            'totalSopVideo',
            'totalExternalWarehouses',
            'totalExternalWarehousesActive',
            'settings'
        ));
    }
}
