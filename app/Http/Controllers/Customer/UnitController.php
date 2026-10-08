<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\UnitPs;
use App\Services\BookingService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class UnitController extends Controller
{
    public function __construct(private readonly BookingService $bookingService)
    {
    }

    /**
     * Pilih unit: grid kartu + filter tipe (Semua / PS5 / PS4) via JS.
     */
    public function index(Request $request)
    {
        $units = UnitPs::orderBy('kode_unit')->get();

        // Hitung status tampilan per unit untuk hari ini (dipakai badge)
        $today = Carbon::now('Asia/Makassar')->toDateString();
        $statusHariIni = [];

        foreach ($units as $unit) {
            $statusHariIni[$unit->id_unit] = $this->statusTampilan($unit, $today);
        }

        return view('customer.unit.index', [
            'units' => $units,
            'statusHariIni' => $statusHariIni,
            'filter' => $request->query('tipe', 'SEMUA'),
        ]);
    }

    /**
     * Halaman pilih jadwal untuk satu unit.
     */
    public function jadwal(Request $request, UnitPs $unit)
    {
        $tanggal = $request->query('tanggal', Carbon::now('Asia/Makassar')->toDateString());

        // Tanggal tidak boleh lampau
        if (Carbon::parse($tanggal)->lt(Carbon::today('Asia/Makassar'))) {
            $tanggal = Carbon::now('Asia/Makassar')->toDateString();
        }

        $slots = $this->bookingService->slotPerHari($unit, $tanggal);

        $pelanggan = \App\Models\Pelanggan::find($request->session()->get('pelanggan_id'));

        return view('customer.unit.jadwal', [
            'unit' => $unit,
            'tanggal' => $tanggal,
            'slots' => $slots,
            'pelanggan' => $pelanggan,
        ]);
    }

    /**
     * Simpan pilihan slot ke session, lalu lanjut ke halaman pembayaran.
     */
    public function simpanPilihan(Request $request, UnitPs $unit)
    {
        $data = $request->validate([
            'tgl_main' => ['required', 'date', 'after_or_equal:today'],
            'slot_mulai' => ['required', 'array', 'min:1'],
            'slot_mulai.*' => ['required', 'date_format:H:i'],
            'nama' => ['required', 'string', 'max:100'],
            'no_hp' => ['required', 'string', 'regex:/^(08|628|\+628)[0-9]{8,11}$/'],
        ], [
            'tgl_main.after_or_equal' => 'Tanggal main tidak boleh sebelum hari ini.',
            'slot_mulai.required' => 'Pilih minimal satu slot jam terlebih dahulu.',
            'no_hp.regex' => 'Format No. HP tidak valid.',
        ]);

        // Hitung durasi & rentang (slot harus berurutan/contigu)
        $rentang = $this->hitungRentang($data['slot_mulai']);

        // Ambil ulang slot dari SERVER: pastikan tidak terpakai & bukan jam lampau
        $slots = collect($this->bookingService->slotPerHari($unit, $data['tgl_main']));
        foreach ($data['slot_mulai'] as $jam) {
            $slot = $slots->firstWhere('jam', $jam);
            if (! $slot || $slot['status'] !== 'tersedia') {
                return redirect()
                    ->route('customer.units.jadwal', ['unit' => $unit, 'tanggal' => $data['tgl_main']])
                    ->with('error', "Jam {$jam} sudah tidak tersedia. Silakan pilih jadwal lain.");
            }
        }

        if ($rentang['durasi_jam'] < 1 || $rentang['durasi_jam'] > 6) {
            return redirect()
                ->route('customer.units.jadwal', ['unit' => $unit, 'tanggal' => $data['tgl_main']])
                ->with('error', 'Durasi sewa harus antara 1 sampai 6 jam.');
        }

        // Simpan draft pemesanan di session (total tetap dihitung server saat submit)
        $request->session()->put('booking_draft', [
            'id_unit' => $unit->id_unit,
            'tgl_main' => $data['tgl_main'],
            'slots' => $data['slot_mulai'],
            'mulai' => $rentang['mulai'],
            'selesai' => $rentang['selesai'],
            'durasi_jam' => $rentang['durasi_jam'],
            'nama' => $data['nama'],
            'no_hp' => $data['no_hp'],
        ]);

        return redirect()->route('customer.pembayaran');
    }

    /**
     * Gabungkan slot terpilih menjadi satu rentang (harus kontigu).
     *
     * @param  array<int, string>  $slots
     * @return array{mulai: string, selesai: string, durasi_jam: int}
     */
    private function hitungRentang(array $slots): array
    {
        $jam = array_map(fn ($s) => (int) substr($s, 0, 2), $slots);
        sort($jam);

        $durasi = count($jam);
        $mulai = sprintf('%02d:00', $jam[0]);
        $selesai = sprintf('%02d:00', $jam[$durasi - 1] + 1);

        return ['mulai' => $mulai, 'selesai' => $selesai, 'durasi_jam' => $durasi];
    }

    /**
     * Badge status tampilan: TERSEDIA / SUDAH_DIBOOKING / MAINTENANCE.
     */
    private function statusTampilan(UnitPs $unit, string $tanggal): string
    {
        if ($unit->status === 'MAINTENANCE') {
            return 'MAINTENANCE';
        }

        if ($unit->status === 'DIGUNAKAN') {
            return 'SUDAH_DIBOOKING';
        }

        // Semua slot hari ini terisi → dianggap sudah dibooking
        $slots = $this->bookingService->slotPerHari($unit, $tanggal);
        $tersedia = collect($slots)->contains('status', 'tersedia');

        return $tersedia ? 'TERSEDIA' : 'SUDAH_DIBOOKING';
    }
}
