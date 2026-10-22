<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    /**
     * Show the login form.
     */
    public function showLoginForm(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    /**
     * Handle an authentication attempt.
     */
    public function login(Request $request): RedirectResponse
    {
        $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
        ], [
            'login.required' => 'Username atau email wajib diisi.',
            'password.required' => 'Password wajib diisi.',
        ]);

        $loginInput = trim($request->input('login'));
        $password = $request->input('password');
        $remember = $request->boolean('remember');

        // Determine initial field type
        $fieldType = filter_var($loginInput, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        $credentials = [$fieldType => $loginInput, 'password' => $password];

        $authenticated = Auth::attempt($credentials, $remember);

        // Fallback attempt on alternate field
        if (! $authenticated) {
            $altField = ($fieldType === 'email') ? 'username' : 'email';
            $authenticated = Auth::attempt([$altField => $loginInput, 'password' => $password], $remember);
        }

        if ($authenticated) {
            $request->session()->regenerate();
            $user = Auth::user();

            return redirect()->intended(route('dashboard'))
                ->with('success', "Selamat datang kembali, {$user->nama_warehouse} ({$user->role_display_name})!");
        }

        return back()
            ->withInput($request->only('login', 'remember'))
            ->withErrors([
                'login' => 'Username atau password yang Anda masukkan tidak sesuai.',
            ]);
    }

    /**
     * Log the user out of the application.
     */
    public function logout(Request $request): RedirectResponse
    {
        if (Auth::check()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return redirect()->route('login', ['logged_out' => 1])
            ->with('success', 'Anda telah berhasil logout dari sistem.');
    }
}
