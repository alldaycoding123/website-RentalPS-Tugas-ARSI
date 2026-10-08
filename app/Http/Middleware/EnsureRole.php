<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    /**
     * Pastikan staff yang login memiliki role yang diizinkan.
     * Contoh pemakaian: ->middleware('role:KASIR,ADMIN,MANAJER')
     */
    public function handle(Request $request, Closure $next, string ...$allowedRoles): Response
    {
        $staff = $request->user('staff');

        if (! $staff) {
            return redirect()->route('staff.login')->with('error', 'Silakan login terlebih dahulu.');
        }

        if (! $staff->aktif) {
            auth('staff')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('staff.login')->with('error', 'Akun Anda dinonaktifkan. Hubungi manajer.');
        }

        // Role tidak sesuai: arahkan ke dashboard role-nya sendiri dengan pesan
        if (! in_array($staff->role, $allowedRoles, true)) {
            return redirect()
                ->route($this->dashboardRoute($staff->role))
                ->with('error', 'Anda tidak punya akses ke halaman tersebut.');
        }

        return $next($request);
    }

    /**
     * Route dashboard default sesuai role staff.
     */
    private function dashboardRoute(string $role): string
    {
        return match ($role) {
            'KASIR' => 'kasir.dashboard',
            'MANAJER' => 'manajer.dashboard',
            default => 'admin.dashboard',
        };
    }
}
