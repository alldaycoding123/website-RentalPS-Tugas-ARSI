<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Pelanggan;
use App\Models\UnitPs;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Dashboard admin realtime: ringkasan + booking terbaru + status unit.
     */
    public function index()
    {
        $hariIni = Carbon::now('Asia/Makassar')->toDateString();

        $bookingHariIni = Booking::where('tgl_main', $hariIni)
            ->where('status', '!=', 'BATAL')
            ->count();

        $unitAktif = UnitPs::where('status', 'DIGUNAKAN')->count();

        $pendapatanHariIni = (float) \App\Models\Pembayaran::where('status', 'LUNAS')
            ->whereDate('waktu', $hariIni)
            ->sum('nominal');

        $totalPelanggan = Pelanggan::count();

        $terbaru = Booking::with(['pelanggan', 'unit', 'pembayaran'])
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        $units = UnitPs::orderBy('kode_unit')->get();

        return view('admin.dashboard', [
            'bookingHariIni' => $bookingHariIni,
            'unitAktif' => $unitAktif,
            'pendapatanHariIni' => $pendapatanHariIni,
            'totalPelanggan' => $totalPelanggan,
            'terbaru' => $terbaru,
            'units' => $units,
            'hariIni' => $hariIni,
        ]);
    }
}
