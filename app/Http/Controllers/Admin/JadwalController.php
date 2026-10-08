<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\UnitPs;
use App\Services\BookingService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class JadwalController extends Controller
{
    public function __construct(private readonly BookingService $bookingService)
    {
    }

    /**
     * Matriks unit × jam pada tanggal tertentu (slot terisi = ada booking).
     */
    public function index(Request $request)
    {
        $tanggal = $request->query('tanggal', Carbon::now('Asia/Makassar')->toDateString());

        if (Carbon::parse($tanggal)->lt(Carbon::today('Asia/Makassar'))) {
            $tanggal = Carbon::now('Asia/Makassar')->toDateString();
        }

        $units = UnitPs::orderBy('kode_unit')->get();
        $slotJam = $this->bookingService->daftarSlotMulai();

        // Ambil semua booking aktif pada tanggal tersebut
        $bookings = Booking::with(['pelanggan', 'pembayaran'])
            ->where('tgl_main', $tanggal)
            ->whereIn('status', Booking::STATUS_PAKAI)
            ->get();

        // Peta [id_unit][jam] = booking
        $peta = [];
        foreach ($bookings as $b) {
            $mulai = (int) substr((string) $b->mulai, 0, 2);
            $selesai = (int) substr((string) $b->selesai, 0, 2);
            for ($h = $mulai; $h < $selesai; $h++) {
                $peta[$b->id_unit][sprintf('%02d:00', $h)] = $b;
            }
        }

        return view('admin.jadwal', [
            'units' => $units,
            'slotJam' => $slotJam,
            'peta' => $peta,
            'tanggal' => $tanggal,
        ]);
    }
}
