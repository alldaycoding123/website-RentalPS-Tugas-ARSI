<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Http\Request;

class RiwayatController extends Controller
{
    /**
     * Daftar booking milik pelanggan login (terbaru dulu).
     */
    public function index(Request $request)
    {
        $pelangganId = (int) $request->session()->get('pelanggan_id');

        $bookings = Booking::with(['unit', 'pembayaran'])
            ->where('id_pelanggan', $pelangganId)
            ->orderByDesc('tgl_main')
            ->orderByDesc('mulai')
            ->paginate(10);

        return view('customer.riwayat', [
            'bookings' => $bookings,
        ]);
    }

    /**
     * Detail booking (hanya milik sendiri) lengkap dengan QR.
     */
    public function show(Request $request, Booking $booking)
    {
        $this->milikSendiri($request, $booking);

        $booking->load(['unit', 'pembayaran', 'pelanggan']);

        return view('customer.riwayat-show', [
            'booking' => $booking,
        ]);
    }

    /**
     * Batalkan booking PENDING/CONFIRMED paling lambat 1 jam sebelum mulai.
     */
    public function batal(Request $request, Booking $booking)
    {
        $this->milikSendiri($request, $booking);

        if (! in_array($booking->status, ['PENDING', 'CONFIRMED'], true)) {
            return back()->with('error', 'Hanya booking Pending atau Confirmed yang bisa dibatalkan.');
        }

        // Batas pembatalan: 1 jam sebelum jam mulai
        $batas = Carbon::parse($booking->tgl_main->toDateString().' '.$booking->mulai)
            ->subHour();

        if (Carbon::now('Asia/Makassar')->gt($batas)) {
            return back()->with('error', 'Pembatalan hanya bisa dilakukan paling lambat 1 jam sebelum jadwal main.');
        }

        $booking->update(['status' => 'BATAL']);

        return back()->with('success', 'Booking '.$booking->kode.' berhasil dibatalkan.');
    }

    /**
     * Cek kepemilikan booking, jika bukan milik sendiri → 404.
     */
    private function milikSendiri(Request $request, Booking $booking): void
    {
        if ($booking->id_pelanggan !== (int) $request->session()->get('pelanggan_id')) {
            abort(404);
        }
    }
}
