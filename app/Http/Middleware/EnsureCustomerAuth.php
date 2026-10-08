<?php

namespace App\Http\Middleware;

use App\Models\Pelanggan;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCustomerAuth
{
    /**
     * Pastikan session pelanggan_id ada dan pelanggannya masih valid.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $pelangganId = $request->session()->get('pelanggan_id');

        if (! $pelangganId) {
            return redirect()->route('customer.login')
                ->with('error', 'Silakan login dengan No. HP Anda terlebih dahulu.');
        }

        $pelanggan = Pelanggan::find($pelangganId);

        if (! $pelanggan) {
            $request->session()->forget('pelanggan_id');

            return redirect()->route('customer.login')
                ->with('error', 'Sesi berakhir, silakan login ulang.');
        }

        // Sisipkan ke request agar controller mudah mengaksesnya
        $request->attributes->set('pelanggan', $pelanggan);

        return $next($request);
    }
}
