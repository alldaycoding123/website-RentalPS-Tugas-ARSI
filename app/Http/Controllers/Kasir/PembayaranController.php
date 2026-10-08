<?php

namespace App\Http\Controllers\Kasir;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;

class PembayaranController extends Controller
{
    /**
     * Daftar booking yang belum lunas.
     */
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        $bookings = Booking::with(['pelanggan', 'unit', 'pembayaran'])
            ->whereHas('pembayaran', fn ($q2) => $q2->where('status', 'BELUM_BAYAR'))
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($w) use ($q) {
                    $w->where('kode', 'like', "%{$q}%")
                        ->orWhereHas('pelanggan', fn ($p) => $p->where('no_hp', 'like', "%{$q}%"));
                });
            })
            ->orderByDesc('tgl_main')
            ->orderBy('mulai')
            ->paginate(10)
            ->withQueryString();

        return view('kasir.pembayaran', [
            'bookings' => $bookings,
            'q' => $q,
        ]);
    }

    /**
     * Konfirmasi pembayaran (LUNAS) + konfirmasi booking.
     */
    public function proses(Request $request, Booking $booking)
    {
        $data = $request->validate([
            'metode' => ['required', 'in:TRANSFER_BANK,E_WALLET,BAYAR_DI_TEMPAT'],
            'nominal' => ['required', 'numeric', 'min:0'],
        ], [
            'metode.required' => 'Metode pembayaran wajib dipilih.',
            'nominal.numeric' => 'Nominal harus berupa angka.',
        ]);

        $staff = $request->user('staff');
        $pembayaran = $booking->pembayaran;

        if (! $pembayaran) {
            return back()->with('error', 'Data pembayaran booking ini tidak ditemukan.');
        }

        if ($pembayaran->status === 'LUNAS') {
            return back()->with('error', 'Booking ini sudah lunas.');
        }

        $pembayaran->update([
            'metode' => $data['metode'],
            'nominal' => $data['nominal'],
            'status' => 'LUNAS',
            'id_staff' => $staff->id_staff,
            'waktu' => now(),
        ]);

        // Booking PENDING otomatis menjadi CONFIRMED setelah bayar
        if ($booking->status === 'PENDING') {
            $booking->update(['status' => 'CONFIRMED']);
        }

        return back()->with('success', "Pembayaran booking {$booking->kode} berhasil (LUNAS).");
    }
}
