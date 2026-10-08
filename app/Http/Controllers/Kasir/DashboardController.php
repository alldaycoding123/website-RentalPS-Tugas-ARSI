<?php

namespace App\Http\Controllers\Kasir;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Pelanggan;
use App\Models\UnitPs;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Dashboard kasir: booking hari ini, belum check-in, unit aktif.
     */
    public function index()
    {
        $hariIni = Carbon::now('Asia/Makassar')->toDateString();

        $bookingHariIni = Booking::where('tgl_main', $hariIni)
            ->where('status', '!=', 'BATAL')
            ->count();

        $belumCheckin = Booking::where('tgl_main', $hariIni)
            ->whereIn('status', ['PENDING', 'CONFIRMED'])
            ->count();

        $unitAktif = UnitPs::where('status', 'DIGUNAKAN')->count();

        $terbaru = Booking::with(['pelanggan', 'unit', 'pembayaran'])
            ->where('tgl_main', $hariIni)
            ->orderBy('mulai')
            ->limit(10)
            ->get();

        return view('kasir.dashboard', [
            'bookingHariIni' => $bookingHariIni,
            'belumCheckin' => $belumCheckin,
            'unitAktif' => $unitAktif,
            'terbaru' => $terbaru,
            'hariIni' => $hariIni,
        ]);
    }
}
