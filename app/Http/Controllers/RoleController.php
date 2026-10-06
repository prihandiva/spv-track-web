<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class RoleController extends Controller
{
    /**
     * Display a listing of roles and permission matrix.
     */
    public function index(Request $request): View
    {
        $roles = Role::withCount('users')->get();
        $availableModules = Role::availablePermissions();

        $totalRoles = $roles->count();
        $totalUsers = User::count();

        // Selected role for detail/matrix editor (default to superadmin or query param)
        $selectedRoleId = $request->input('role_id', $roles->first()?->id);
        $selectedRole = $roles->firstWhere('id', (int) $selectedRoleId) ?? $roles->first();

        return view('settings.roles.index', compact(
            'roles',
            'availableModules',
            'totalRoles',
            'totalUsers',
            'selectedRole'
        ));
    }

    /**
     * Store a newly created custom role.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'display_name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
            'permissions' => ['nullable', 'array'],
        ], [
            'display_name.required' => 'Nama tampilan role wajib diisi.',
        ]);

        $name = Str::slug($validated['display_name'], '_');

        // Check if name already exists
        if (Role::where('name', $name)->exists()) {
            $name .= '_'.rand(10, 99);
        }

        $role = Role::create([
            'name' => $name,
            'display_name' => $validated['display_name'],
            'description' => $validated['description'] ?? null,
            'permissions' => $validated['permissions'] ?? [],
            'is_system' => false,
        ]);

        return redirect()->route('settings.roles.index', ['role_id' => $role->id])
            ->with('success', "Role {$role->display_name} berhasil ditambahkan ke database.");
    }

    /**
     * Update permissions and details of the specified role.
     */
    public function update(Request $request, Role $role): RedirectResponse
    {
        $validated = $request->validate([
            'display_name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
            'permissions' => ['nullable', 'array'],
        ], [
            'display_name.required' => 'Nama tampilan role wajib diisi.',
        ]);

        $permissions = $validated['permissions'] ?? [];

        // If superadmin, ensure core permissions are not accidentally stripped completely
        if ($role->name === 'superadmin') {
            $mandatory = ['users.view', 'roles.view', 'settings.view', 'sop.view'];
            $permissions = array_values(array_unique(array_merge($permissions, $mandatory)));
        }

        $role->update([
            'display_name' => $validated['display_name'],
            'description' => $validated['description'] ?? null,
            'permissions' => $permissions,
        ]);

        return redirect()->route('settings.roles.index', ['role_id' => $role->id])
            ->with('success', "Hak akses dan pengaturan role {$role->display_name} berhasil disimpan ke database.");
    }

    /**
     * Remove the specified custom role.
     */
    public function destroy(Role $role): RedirectResponse
    {
        if ($role->is_system) {
            return redirect()->route('settings.roles.index')
                ->with('error', 'Role bawaan sistem tidak dapat dihapus.');
        }

        if ($role->users()->count() > 0) {
            return redirect()->route('settings.roles.index', ['role_id' => $role->id])
                ->with('error', "Role {$role->display_name} masih digunakan oleh {$role->users()->count()} akun pengguna. Pindahkan pengguna ke role lain terlebih dahulu.");
        }

        $displayName = $role->display_name;
        $role->delete();

        return redirect()->route('settings.roles.index')
            ->with('success', "Role {$displayName} berhasil dihapus.");
    }
}
