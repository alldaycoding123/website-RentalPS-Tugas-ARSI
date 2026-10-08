<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\UnitPs;
use App\Services\BookingService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class UnitApiController extends Controller
{
    public function __construct(private readonly BookingService $bookingService)
    {
    }

    /**
     * GET /api/units/status — status ringkas semua unit (auto-refresh tiap 15 detik).
     */
    public function status()
    {
        $hariIni = Carbon::now('Asia/Makassar')->toDateString();

        $units = UnitPs::orderBy('kode_unit')->get();

        return response()->json($units->map(function (UnitPs $unit) use ($hariIni) {
            $slots = $this->bookingService->slotPerHari($unit, $hariIni);
            $adaSlotKosong = collect($slots)->contains('status', 'tersedia');

            // Badge: MAINTENANCE / SUDAH_DIBOOKING / TERSEDIA
            $badge = 'TERSEDIA';
            if ($unit->status === 'MAINTENANCE') {
                $badge = 'MAINTENANCE';
            } elseif ($unit->status === 'DIGUNAKAN' || ! $adaSlotKosong) {
                $badge = 'SUDAH_DIBOOKING';
            }

            return [
                'id_unit' => $unit->id_unit,
                'kode_unit' => $unit->kode_unit,
                'status' => $unit->status,
                'badge' => $badge,
                'slot_kosong' => $adaSlotKosong,
            ];
        })->values());
    }

    /**
     * GET /api/units/{unit}/slots?tanggal=YYYY-MM-DD — grid slot 09:00–21:00.
     */
    public function slots(Request $request, UnitPs $unit)
    {
        $tanggal = $request->query('tanggal', Carbon::now('Asia/Makassar')->toDateString());

        try {
            $parsed = Carbon::parse($tanggal);
        } catch (\Throwable) {
            return response()->json(['error' => 'Format tanggal tidak valid.'], 422);
        }

        if ($parsed->lt(Carbon::today('Asia/Makassar'))) {
            return response()->json(['error' => 'Tanggal tidak boleh lampau.'], 422);
        }

        return response()->json([
            'id_unit' => $unit->id_unit,
            'kode_unit' => $unit->kode_unit,
            'tanggal' => $tanggal,
            'slots' => $this->bookingService->slotPerHari($unit, $tanggal),
        ]);
    }
}
