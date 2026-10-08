<?php

namespace App\Services;

use App\Exceptions\SlotBentrokException;
use App\Models\Booking;
use App\Models\Pembayaran;
use App\Models\UnitPs;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BookingService
{
    /**
     * Membuat booking + pembayaran dalam satu transaksi,
     * dengan penguncian baris untuk mencegah double booking.
     *
     * @param  array{unit: UnitPs, pelanggan_id: int, tgl_main: string, mulai: string,
     *               selesai: string, durasi_jam: int, metode: string, staff_id?: int|null,
     *               status?: string}  $data
     * @return Booking
     *
     * @throws SlotBentrokException
     */
    public function buat(array $data): Booking
    {
        return DB::transaction(function () use ($data) {
            $unit = $data['unit'];
            $tglMain = $data['tgl_main'];
            $mulaiBaru = $data['mulai'];
            $selesaiBaru = $data['selesai'];

            // Validasi server-side: unit tidak boleh maintenance
            if ($unit->status === 'MAINTENANCE') {
                throw new SlotBentrokException("Unit {$unit->kode_unit} sedang dalam maintenance dan tidak bisa dibooking.");
            }

            // ANTI DOUBLE BOOKING: kunci baris booking aktif pada unit+tanggal yang rentangnya beririsan
            $bentrok = Booking::where('id_unit', $unit->id_unit)
                ->where('tgl_main', $tglMain)
                ->where('status', '!=', 'BATAL')
                ->where('mulai', '<', $selesaiBaru)
                ->where('selesai', '>', $mulaiBaru)
                ->lockForUpdate()
                ->exists();

            if ($bentrok) {
                throw new SlotBentrokException(
                    "Jadwal {$mulaiBaru}–{$selesaiBaru} pada {$tglMain} untuk unit {$unit->kode_unit} sudah dibooking orang lain. Silakan pilih jam lain."
                );
            }

            // Total dihitung di SERVER berdasarkan harga unit, abaikan angka dari browser
            $total = ((float) $unit->harga_jam) * (int) $data['durasi_jam'];

            $booking = Booking::create([
                'kode' => $this->kodeUnik(),
                'id_pelanggan' => $data['pelanggan_id'],
                'id_unit' => $unit->id_unit,
                'id_staff' => $data['staff_id'] ?? null,
                'tgl_main' => $tglMain,
                'mulai' => $mulaiBaru,
                'selesai' => $selesaiBaru,
                'durasi_jam' => (int) $data['durasi_jam'],
                'total' => $total,
                'status' => $data['status'] ?? 'PENDING',
            ]);

            // Booking selalu dibuat bersama satu record pembayaran
            Pembayaran::create([
                'id_booking' => $booking->id_booking,
                'id_staff' => null,
                'metode' => $data['metode'],
                'nominal' => $total,
                'status' => 'BELUM_BAYAR',
                'waktu' => null,
            ]);

            return $booking;
        });
    }

    /**
     * Kode booking "BK-" + 6 karakter [A-Z0-9], diulang bila kembar.
     */
    private function kodeUnik(): string
    {
        do {
            $kode = 'BK-'.Str::upper(Str::random(6));
        } while (Booking::where('kode', $kode)->exists());

        return $kode;
    }

    /**
     * Cek apakah dua rentang waktu beririsan (untuk validasi UI/walk-in).
     */
    public function bentrok(string $idUnit, string $tglMain, string $mulai, string $selesai, ?int $kecualiBooking = null): bool
    {
        return Booking::where('id_unit', $idUnit)
            ->where('tgl_main', $tglMain)
            ->where('status', '!=', 'BATAL')
            ->when($kecualiBooking, fn ($q) => $q->where('id_booking', '!=', $kecualiBooking))
            ->where('mulai', '<', $selesai)
            ->where('selesai', '>', $mulai)
            ->exists();
    }

    /**
     * Jam slot: 09:00 s.d. 21:00 (21:00 = 21:00–22:00).
     *
     * @return array<int, string>
     */
    public function daftarSlotMulai(): array
    {
        $jam = [];
        for ($h = 9; $h < 22; $h++) {
            $jam[] = sprintf('%02d:00', $h);
        }

        return $jam;
    }

    /**
     * Status tiap slot untuk satu unit & tanggal.
     *
     * @return array<int, array{jam: string, status: string, lewat: bool}>
     */
    public function slotPerHari(UnitPs $unit, string $tglMain): array
    {
        $sekarang = Carbon::now('Asia/Makassar');
        $tanggalDipilih = Carbon::parse($tglMain)->startOfDay();
        $iniHariIni = $tanggalDipilih->isToday();

        $bookingAktif = Booking::where('id_unit', $unit->id_unit)
            ->where('tgl_main', $tglMain)
            ->whereIn('status', Booking::STATUS_PAKAI)
            ->get(['mulai', 'selesai']);

        $hasil = [];

        foreach ($this->daftarSlotMulai() as $mulai) {
            $selesai = sprintf('%02d:00', ((int) substr($mulai, 0, 2)) + 1);

            $terpakai = false;
            foreach ($bookingAktif as $b) {
                // Normalisasi ke H:i karena MySQL TIME keluar sebagai H:i:s
                $bMulai = substr((string) $b->mulai, 0, 5);
                $bSelesai = substr((string) $b->selesai, 0, 5);

                // overlay: mulai baru < selesai booking & selesai baru > mulai booking
                if ($mulai < $bSelesai && $selesai > $bMulai) {
                    $terpakai = true;
                    break;
                }
            }

            // Slot yang sudah lewat (untuk hari ini) otomatis tidak bisa dipilih
            $lewat = $iniHariIni && Carbon::parse($tglMain.' '.$mulai)->lt($sekarang);

            $hasil[] = [
                'jam' => $mulai,
                'status' => $terpakai ? 'terpakai' : ($lewat ? 'lewat' : 'tersedia'),
                'lewat' => $lewat,
            ];
        }

        return $hasil;
    }
}
