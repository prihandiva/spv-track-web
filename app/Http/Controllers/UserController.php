<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class UserController extends Controller
{
    /**
     * Display a listing of system users.
     */
    public function index(Request $request): View
    {
        $query = User::withCount('shipments')->latest();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_warehouse', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($role = $request->input('role')) {
            $query->where('role', $role);
        }

        $users = $query->paginate(10)->withQueryString();

        $totalUsers = User::count();
        $totalSuperadmin = User::where('role', 'superadmin')->count();
        $totalAdmin = User::where('role', 'admin')->count();
        $totalOperator = User::where('role', 'operator')->count();

        $roles = Role::all();

        return view('settings.users.index', compact(
            'users',
            'totalUsers',
            'totalSuperadmin',
            'totalAdmin',
            'totalOperator',
            'roles'
        ));
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama_warehouse' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
            'role' => ['required', 'in:superadmin,admin,operator'],
        ], [
            'nama_warehouse.required' => 'Nama warehouse / akun pengguna wajib diisi.',
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email ini sudah terdaftar oleh pengguna lain.',
            'password.required' => 'Kata sandi wajib diisi.',
            'password.min' => 'Kata sandi minimal 6 karakter.',
            'role.required' => 'Role hak akses wajib dipilih.',
            'role.in' => 'Pilihan role tidak valid.',
        ]);

        $validated['password'] = Hash::make($validated['password']);

        $user = User::create($validated);

        return redirect()->route('settings.users.index')
            ->with('success', "Pengguna {$user->nama_warehouse} ({$user->email}) berhasil didaftarkan dengan role {$user->role_display_name}.");
    }

    /**
     * Update the specified user in storage.
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'nama_warehouse' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'password' => ['nullable', 'string', 'min:6'],
            'role' => ['required', 'in:superadmin,admin,operator'],
        ], [
            'nama_warehouse.required' => 'Nama warehouse / akun pengguna wajib diisi.',
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email ini sudah digunakan oleh pengguna lain.',
            'password.min' => 'Kata sandi baru minimal 6 karakter.',
            'role.required' => 'Role hak akses wajib dipilih.',
            'role.in' => 'Pilihan role tidak valid.',
        ]);

        // Safety: Do not demote the last superadmin
        if ($user->role === 'superadmin' && $validated['role'] !== 'superadmin') {
            $otherSuperadminCount = User::where('role', 'superadmin')->where('id', '!=', $user->id)->count();
            if ($otherSuperadminCount === 0) {
                return redirect()->route('settings.users.index')
                    ->with('error', 'Tidak dapat mengubah role Superadmin terakhir di sistem.');
            }
        }

        $payload = [
            'nama_warehouse' => $validated['nama_warehouse'],
            'email' => $validated['email'],
            'role' => $validated['role'],
        ];

        if (! empty($validated['password'])) {
            $payload['password'] = Hash::make($validated['password']);
        }

        $user->update($payload);

        return redirect()->route('settings.users.index')
            ->with('success', "Data akun pengguna {$user->nama_warehouse} berhasil diperbarui.");
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(Request $request, User $user): RedirectResponse
    {
        $currentAuthId = auth()->id();
        if ($currentAuthId && $currentAuthId === $user->id) {
            return redirect()->route('settings.users.index')
                ->with('error', 'Anda tidak dapat menghapus akun Anda sendiri yang sedang aktif digunakan.');
        }

        if (User::count() <= 1) {
            return redirect()->route('settings.users.index')
                ->with('error', 'Tidak dapat menghapus satu-satunya akun pengguna yang tersisa di sistem.');
        }

        if ($user->role === 'superadmin') {
            $superadminCount = User::where('role', 'superadmin')->count();
            if ($superadminCount <= 1) {
                return redirect()->route('settings.users.index')
                    ->with('error', 'Tidak dapat menghapus akun Superadmin satu-satunya di sistem.');
            }
        }

        $nama = $user->nama_warehouse;
        $user->delete();

        return redirect()->route('settings.users.index')
            ->with('success', "Pengguna {$nama} berhasil dihapus dari sistem.");
    }
}
