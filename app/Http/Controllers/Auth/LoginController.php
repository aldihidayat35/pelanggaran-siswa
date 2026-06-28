<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::check()) {
            $user = Auth::user();
            if ($user->role === 'admin') {
                return redirect()->route('admin.dashboard');
            }

            if ($user->role === 'guru') {
                return redirect()->route('guru.attendance');
            }

            Auth::logout();
            return redirect()->route('login')->withErrors([
                'email' => 'Role akun Anda belum memiliki akses aplikasi.',
            ]);
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            $user = Auth::user();

            if (!$user->is_active) {
                Auth::logout();
                return back()->withErrors([
                    'email' => 'Akun Anda telah dinonaktifkan.',
                ]);
            }

            if ($user->role === 'admin') {
                return redirect()->intended(route('admin.dashboard'));
            }

            if ($user->role === 'guru') {
                return redirect()->intended(route('guru.attendance'));
            }

            Auth::logout();
            return back()->withErrors([
                'email' => 'Role akun Anda belum memiliki akses aplikasi.',
            ])->onlyInput('email');
        }

        return back()->withErrors([
            'email' => 'Email atau password yang Anda masukkan salah.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
