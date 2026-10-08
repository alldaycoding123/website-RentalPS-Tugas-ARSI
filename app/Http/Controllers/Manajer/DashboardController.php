<?php

namespace App\Http\Controllers\Manajer;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Pembayaran;
use App\Models\UnitPs;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Dashboard manajer: ringkasan + grafik pendapatan 7 hari (Chart.js).
     */
    public function index()
    {
        $hariIni = Carbon::now('Asia/Makassar');

        $totalPendapatan = (float) Pembayaran::where('status', 'LUNAS')->sum('nominal');

        $totalBookingValid = Booking::where('status', '!=', 'BATAL')->count();

        $transaksiHariIni = Pembayaran::where('status', 'LUNAS')
            ->whereDate('waktu', $hariIni->toDateString())
            ->count();

        $unitBeroperasi = UnitPs::where('status', '!=', 'MAINTENANCE')->count();

        // Data grafik 7 hari terakhir
        $grafik = $this->pendapatan7Hari();

        return view('manajer.dashboard', [
            'totalPendapatan' => $totalPendapatan,
            'totalBookingValid' => $totalBookingValid,
            'transaksiHariIni' => $transaksiHariIni,
            'unitBeroperasi' => $unitBeroperasi,
            'grafik' => $grafik,
        ]);
    }

    /**
     * JSON data pendapatan lunas 7 hari terakhir (untuk Chart.js).
     */
    public function apiPendapatan()
    {
        return response()->json($this->pendapatan7Hari());
    }

    /**
     * @return array{labels: array<int, string>, data: array<int, float>}
     */
    private function pendapatan7Hari(): array
    {
        $labels = [];
        $data = [];

        for ($i = 6; $i >= 0; $i--) {
            $tanggal = Carbon::now('Asia/Makassar')->subDays($i);

            $labels[] = $tanggal->translatedFormat('D, d M');
            $data[] = (float) Pembayaran::where('status', 'LUNAS')
                ->whereDate('waktu', $tanggal->toDateString())
                ->sum('nominal');
        }

        return ['labels' => $labels, 'data' => $data];
    }
}
