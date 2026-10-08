<?php

namespace App\Http\Controllers\Customer;

use App\Exceptions\SlotBentrokException;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBookingRequest;
use App\Models\Booking;
use App\Models\Pelanggan;
use App\Models\UnitPs;
use App\Services\BookingService;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function __construct(private readonly BookingService $bookingService)
    {
    }

    /**
     * Halaman konfirmasi pembayaran (ringkasan + metode).
     */
    public function pembayaran(Request $request)
    {
        $draft = $request->session()->get('booking_draft');

        if (! $draft) {
            return redirect()->route('customer.units')
                ->with('error', 'Pilih unit dan jadwal terlebih dahulu.');
        }

        $unit = UnitPs::find($draft['id_unit']);

        if (! $unit) {
            $request->session()->forget('booking_draft');

            return redirect()->route('customer.units')->with('error', 'Unit tidak ditemukan.');
        }

        // Total ditampilkan = harga unit × durasi (angka browser diabaikan)
        $total = ((float) $unit->harga_jam) * (int) $draft['durasi_jam'];

        return view('customer.pembayaran', [
            'draft' => $draft,
            'unit' => $unit,
            'total' => $total,
            'metodeRekening' => $this->metodeRekening(),
        ]);
    }

    /**
     * Submit booking: validasi ulang server-side lalu buat booking + pembayaran.
     */
    public function store(StoreBookingRequest $request)
    {
        $draft = $request->session()->get('booking_draft');

        if (! $draft) {
            return redirect()->route('customer.units')
                ->with('error', 'Sesi pemesanan berakhir. Silakan pilih unit & jadwal ulang.');
        }

        $unit = UnitPs::find($draft['id_unit']);
        $pelanggan = Pelanggan::find($request->session()->get('pelanggan_id'));

        if (! $unit || ! $pelanggan) {
            return redirect()->route('customer.units')->with('error', 'Data tidak ditemukan.');
        }

        // Perbarui data pemesan sesuai form
        $pelanggan->update([
            'nama' => $request->validated('nama'),
            'no_hp' => $request->validated('no_hp'),
        ]);

        try {
            $booking = $this->bookingService->buat([
                'unit' => $unit,
                'pelanggan_id' => $pelanggan->id_pelanggan,
                'tgl_main' => $draft['tgl_main'],
                'mulai' => $draft['mulai'],
                'selesai' => $draft['selesai'],
                'durasi_jam' => $draft['durasi_jam'],
                'metode' => $request->validated('metode'),
                'status' => 'PENDING',
            ]);
        } catch (SlotBentrokException $e) {
            // Slot sudah diambil orang lain → kembali ke pemilihan jadwal
            return redirect()
                ->route('customer.units.jadwal', $unit)
                ->with('error', $e->getMessage());
        }

        $request->session()->forget('booking_draft');

        return redirect()->route('customer.booking.sukses', $booking->kode)
            ->with('success', 'Booking berhasil dibuat!');
    }

    /**
     * Halaman sukses: kode booking + QR + rincian.
     */
    public function sukses(Request $request, string $kode)
    {
        $booking = Booking::with(['pelanggan', 'unit', 'pembayaran'])
            ->where('kode', $kode)
            ->firstOrFail();

        // Pelanggan hanya boleh melihat booking miliknya sendiri
        if ($booking->id_pelanggan !== (int) $request->session()->get('pelanggan_id')) {
            abort(404);
        }

        return view('customer.booking-sukses', [
            'booking' => $booking,
        ]);
    }

    /**
     * Contoh nomor rekening/e-wallet (simulasi, tanpa gateway asli).
     *
     * @return array<string, string>
     */
    private function metodeRekening(): array
    {
        return [
            'TRANSFER_BANK' => 'BCA 1234567890 a.n. PSRental (Simulasi)',
            'E_WALLET' => '0812-3456-7890 a.n. PSRental (GoPay/OVO/Dana – Simulasi)',
        ];
    }
}
