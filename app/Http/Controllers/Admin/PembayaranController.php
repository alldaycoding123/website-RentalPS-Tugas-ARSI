<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;

class PembayaranController extends Controller
{
    /**
     * Daftar transaksi + filter status/metode.
     */
    public function index(Request $request)
    {
        $filters = $request->only(['status', 'metode']);

        $pembayarans = \App\Models\Pembayaran::with(['booking.pelanggan', 'booking.unit', 'staff'])
            ->when($filters['status'] ?? '', fn ($q, $s) => $q->where('status', $s))
            ->when($filters['metode'] ?? '', fn ($q, $m) => $q->where('metode', $m))
            ->orderByDesc('updated_at')
            ->paginate(10)
            ->withQueryString();

        $totalLunas = (float) \App\Models\Pembayaran::where('status', 'LUNAS')->sum('nominal');

        return view('admin.pembayarans.index', [
            'pembayarans' => $pembayarans,
            'filters' => $filters,
            'totalLunas' => $totalLunas,
        ]);
    }
}
