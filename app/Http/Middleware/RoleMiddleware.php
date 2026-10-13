<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        // Superadmin has unrestricted access to everything
        if ($user->role === 'superadmin') {
            return $next($request);
        }

        // Check if user's role is in the allowed roles list
        if (in_array($user->role, $roles)) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'error' => 'Akses ditolak: Anda tidak memiliki hak akses untuk tindakan ini.',
            ], 403);
        }

        return redirect()->route('dashboard')->with('error', 'Akses ditolak: Halaman atau tindakan tersebut hanya dapat diakses oleh peran tertentu (' . implode(', ', $roles) . ').');
    }
}
