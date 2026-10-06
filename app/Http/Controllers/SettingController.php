<?php

namespace App\Http\Controllers;

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

        $roles = Role::withCount('users')->get();
        $totalRoles = $roles->count();

        $totalSop = SopPhotoPoint::count();
        $totalSopWajib = SopPhotoPoint::where('wajib', true)->count();
        $totalSopOcr = SopPhotoPoint::where('perlu_ocr_container', true)->count();
        $totalSopAi = SopPhotoPoint::where('perlu_deteksi_orang', true)->orWhere('perlu_deteksi_barcode', true)->count();

        $settings = SystemSetting::all();

        return view('settings.index', compact(
            'totalUsers',
            'totalSuperadmin',
            'totalAdmin',
            'totalOperator',
            'roles',
            'totalRoles',
            'totalSop',
            'totalSopWajib',
            'totalSopOcr',
            'totalSopAi',
            'settings'
        ));
    }
}
