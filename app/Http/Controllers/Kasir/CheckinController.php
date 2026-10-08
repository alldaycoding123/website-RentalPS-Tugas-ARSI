<?php

namespace App\Http\Controllers\Kasir;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Pelanggan;
use App\Models\UnitPs;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CheckinController extends Controller
{
    /**
     * Halaman check-in: input kode booking / scan QR / cari No. HP.
     */
    public function index(Request $request)
    {
        $hasil = null;
        $pesan = null;
        $q = trim((string) $request->query('q', ''));

        if ($q !== '') {
            $hasil = $this->cariBooking($q);
            if (! $hasil) {
                $pesan = "Booking dengan kode/nomor \"{$q}\" tidak ditemukan.";
            }
        }

        return view('kasir.checkin', [
            'hasil' => $hasil,
            'pesan' => $pesan,
            'q' => $q,
        ]);
    }

    /**
     * Proses check-in: validasi lalu ubah status booking & unit.
     */
    public function proses(Request $request, Booking $booking)
    {
        $staff = $request->user('staff');
        $hariIni = Carbon::now('Asia/Makassar')->toDateString();

        // Validasi check-in
        if ($booking->status === 'BATAL') {
            return back()->with('error', 'Booking ini sudah dibatalkan.');
        }

        if ($booking->tgl_main->toDateString() !== $hariIni) {
            return back()->with('error', 'Check-in hanya untuk booking tanggal hari ini.');
        }

        if ($booking->status === 'CHECKIN') {
            return back()->with('error', 'Booking ini sudah melakukan check-in.');
        }

        if ($booking->status === 'SELESAI') {
            return back()->with('error', 'Booking ini sudah selesai.');
        }

        $booking->update([
            'status' => 'CHECKIN',
            'id_staff' => $staff->id_staff,
        ]);

        // Unit menjadi DIGUNAKAN
        $booking->unit()->update(['status' => 'DIGUNAKAN']);

        return back()->with('success', "Check-in booking {$booking->kode} berhasil. Unit {$booking->unit->kode_unit} digunakan.");
    }

    /**
     * Daftar booking CHECKIN untuk check-out.
     */
    public function checkoutIndex()
    {
        $bookings = Booking::with(['pelanggan', 'unit', 'pembayaran'])
            ->where('status', 'CHECKIN')
            ->orderBy('mulai')
            ->get();

        return view('kasir.checkout', [
            'bookings' => $bookings,
        ]);
    }

    /**
     * Proses check-out: booking SELESAI, unit kembali TERSEDIA.
     */
    public function checkout(Request $request, Booking $booking)
    {
        $staff = $request->user('staff');

        if ($booking->status !== 'CHECKIN') {
            return back()->with('error', 'Hanya booking berstatus Check-in yang bisa check-out.');
        }

        $booking->update([
            'status' => 'SELESAI',
            'id_staff' => $booking->id_staff ?? $staff->id_staff,
        ]);

        // Kembalikan unit ke TERSEDIA kecuali masih dipakai booking lain
        $unit = $booking->unit;
        $masihDipakai = Booking::where('id_unit', $unit->id_unit)
            ->where('status', 'CHECKIN')
            ->where('id_booking', '!=', $booking->id_booking)
            ->exists();

        if (! $masihDipakai && $unit->status !== 'MAINTENANCE') {
            $unit->update(['status' => 'TERSEDIA']);
        }

        return back()->with('success', "Check-out booking {$booking->kode} berhasil.");
    }

    /**
     * Cari booking lewat kode booking, atau No. HP pelanggan.
     */
    private function cariBooking(string $q): ?Booking
    {
        // Cari berdasarkan kode booking (atau hasil scan QR)
        $booking = Booking::with(['pelanggan', 'unit', 'pembayaran'])
            ->where('kode', $q)
            ->first();

        if ($booking) {
            return $booking;
        }

        // Cari lewat No. HP pelanggan → booking hari ini terbaru
        $pelanggan = Pelanggan::where('no_hp', $q)->first();

        if (! $pelanggan) {
            return null;
        }

        return Booking::with(['pelanggan', 'unit', 'pembayaran'])
            ->where('id_pelanggan', $pelanggan->id_pelanggan)
            ->orderBy('tgl_main')
            ->orderByDesc('mulai')
            ->first();
    }
}
