<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Pembayaran;
use Carbon\Carbon;

class ChartController extends Controller
{
    /**
     * GET /api/chart/pendapatan — data grafik pendapatan 7 hari terakhir.
     */
    public function pendapatan()
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

        return response()->json([
            'labels' => $labels,
            'data' => $data,
        ]);
    }

    /**
     * GET /api/chart/booking-status — distribusi status booking (opsional).
     */
    public function statusBooking()
    {
        $statuses = ['PENDING', 'CONFIRMED', 'CHECKIN', 'SELESAI', 'BATAL'];

        $data = [];
        foreach ($statuses as $s) {
            $data[$s] = Booking::where('status', $s)->count();
        }

        return response()->json($data);
    }
}
