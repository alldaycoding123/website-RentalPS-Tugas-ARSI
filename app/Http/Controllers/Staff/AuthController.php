<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginStaffRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Halaman login staff (Kasir, Admin, Manajer).
     */
    public function showLogin()
    {
        return view('auth.staff-login');
    }

    /**
     * Proses login staff: hanya akun aktif.
     */
    public function login(LoginStaffRequest $request)
    {
        $credentials = $request->validated();

        if (! Auth::guard('staff')->attempt($credentials, true)) {
            return back()->withInput()->with('error', 'Email atau password salah.');
        }

        $staff = Auth::guard('staff')->user();

        if (! $staff->aktif) {
            Auth::guard('staff')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()->with('error', 'Akun Anda dinonaktifkan. Hubungi manajer.');
        }

        // Regenerate session saat login (keamanan sesi)
        $request->session()->regenerate();

        return redirect()->route($staff->dashboardRoute())
            ->with('success', "Selamat datang, {$staff->nama}!");
    }

    /**
     * Logout staff: invalidate session.
     */
    public function logout(Request $request)
    {
        Auth::guard('staff')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('staff.login')->with('success', 'Anda telah keluar.');
    }
}
