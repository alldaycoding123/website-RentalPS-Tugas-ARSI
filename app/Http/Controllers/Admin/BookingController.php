<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\SlotBentrokException;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreWalkinBookingRequest;
use App\Models\Booking;
use App\Models\Pelanggan;
use App\Models\UnitPs;
use App\Services\BookingService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function __construct(private readonly BookingService $bookingService)
    {
    }

    /**
     * Daftar booking + filter tanggal/status/unit + pagination.
     */
    public function index(Request $request)
    {
        $filters = $request->only(['tanggal', 'status', 'unit']);

        $bookings = Booking::with(['pelanggan', 'unit', 'pembayaran', 'staff'])
            ->when($filters['tanggal'] ?? '', fn ($q, $t) => $q->whereDate('tgl_main', $t))
            ->when($filters['status'] ?? '', fn ($q, $s) => $q->where('status', $s))
            ->when($filters['unit'] ?? '', fn ($q, $u) => $q->where('id_unit', $u))
            ->orderByDesc('tgl_main')
            ->orderByDesc('mulai')
            ->paginate(10)
            ->withQueryString();

        return view('admin.bookings.index', [
            'bookings' => $bookings,
            'filters' => $filters,
            'units' => UnitPs::orderBy('kode_unit')->get(),
            'statuses' => ['PENDING', 'CONFIRMED', 'CHECKIN', 'SELESAI', 'BATAL'],
        ]);
    }

    /**
     * Form tambah booking walk-in manual.
     */
    public function create()
    {
        return view('admin.bookings.create', [
            'units' => UnitPs::orderBy('kode_unit')->get(),
            'pelanggans' => Pelanggan::orderBy('nama')->get(),
        ]);
    }

    /**
     * Simpan booking walk-in — tetap lewat validasi anti double booking.
     */
    public function store(StoreWalkinBookingRequest $request)
    {
        $unit = UnitPs::findOrFail($request->validated('id_unit'));
        $durasi = (int) $request->validated('durasi_jam');
        $mulai = $request->validated('mulai');
        $selesai = sprintf('%02d:00', ((int) substr($mulai, 0, 2)) + $durasi);

        // Validasi batas atas rentang jam 09:00–22:00
        if (((int) substr($mulai, 0, 2)) + $durasi > 22) {
            return back()->withInput()
                ->with('error', 'Jam selesai melewati batas operasional 22:00. Kurangi durasi.');
        }

        try {
            $booking = $this->bookingService->buat([
                'unit' => $unit,
                'pelanggan_id' => (int) $request->validated('id_pelanggan'),
                'tgl_main' => $request->validated('tgl_main'),
                'mulai' => $mulai,
                'selesai' => $selesai,
                'durasi_jam' => $durasi,
                'metode' => $request->validated('metode'),
                'staff_id' => $request->user('staff')->id_staff,
                'status' => 'PENDING',
            ]);
        } catch (SlotBentrokException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('admin.bookings.index')
            ->with('success', "Booking walk-in {$booking->kode} berhasil dibuat.");
    }

    /**
     * Detail booking (untuk ubah status / batalkan).
     */
    public function edit(Booking $booking)
    {
        $booking->load(['pelanggan', 'unit', 'pembayaran', 'staff']);

        return view('admin.bookings.edit', ['booking' => $booking]);
    }

    /**
     * Ubah status booking (validasi transisi sederhana).
     */
    public function update(Request $request, Booking $booking)
    {
        $data = $request->validate([
            'status' => ['required', 'in:PENDING,CONFIRMED,CHECKIN,SELESAI,BATAL'],
        ], [
            'status.in' => 'Status tidak dikenali.',
        ]);

        $dipilih = $data['status'];

        // Booking CHECKIN/SELESAI diubah manual: sesuaikan status unit
        if ($dipilih === 'CHECKIN' && $booking->status !== 'CHECKIN') {
            $booking->unit->update(['status' => 'DIGUNAKAN']);
        }

        if ($booking->status === 'CHECKIN' && in_array($dipilih, ['SELESAI', 'BATAL'], true)) {
            $masihDipakai = Booking::where('id_unit', $booking->id_unit)
                ->where('status', 'CHECKIN')
                ->where('id_booking', '!=', $booking->id_booking)
                ->exists();

            if (! $masihDipakai && $booking->unit->status !== 'MAINTENANCE') {
                $booking->unit->update(['status' => 'TERSEDIA']);
            }
        }

        $booking->update(['status' => $dipilih]);

        return back()->with('success', "Status booking {$booking->kode} diubah menjadi {$dipilih}.");
    }

    /**
     * Batalkan booking (shortcut).
     */
    public function destroy(Booking $booking)
    {
        if (in_array($booking->status, ['CHECKIN', 'SELESAI'], true)) {
            return back()->with('error', 'Booking yang sudah check-in tidak bisa dihapus, ubah status saja.');
        }

        $booking->update(['status' => 'BATAL']);

        return redirect()->route('admin.bookings.index')
            ->with('success', "Booking {$booking->kode} dibatalkan.");
    }
}
