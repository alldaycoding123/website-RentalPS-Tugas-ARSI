<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Pembayaran;
use App\Models\Pelanggan;
use App\Models\Staff;
use App\Models\UnitPs;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Mengisi ±25 booking + pembayaran tersebar 14 hari terakhir
 * dengan berbagai status, TANPA overlap per unit, supaya grafik,
 * laporan, dan dashboard langsung terisi.
 */
class BookingSeeder extends Seeder
{
    public function run(): void
    {
        // Bila sudah ada booking (mis. seeder dijalankan ulang), jangan duplikasi
        if (Booking::count() > 0) {
            return;
        }

        $units = UnitPs::where('status', '!=', 'MAINTENANCE')->get();
        $pelanggans = Pelanggan::all();
        $staffs = Staff::pluck('id_staff');

        if ($units->isEmpty() || $pelanggans->isEmpty()) {
            return;
        }

        $tanggalHariIni = Carbon::now('Asia/Makassar');
        $slotJam = [9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20];

        // Peta jam terpakai: [id_unit][tanggal][jam] => true (anti overlap)
        $terpakai = [];

        // Sebarkan booking pada 14 hari terakhir (1–3 booking per hari)
        for ($hari = 13; $hari >= 0; $hari--) {
            $tanggal = $tanggalHariIni->copy()->subDays($hari);
            $isHariIni = $tanggal->isToday();
            $kuotaHari = $isHariIni ? random_int(2, 4) : random_int(1, 3);
            $dibuatHari = 0;

            foreach ($units->shuffle() as $unit) {
                if ($dibuatHari >= $kuotaHari) {
                    break;
                }

                $durasi = random_int(1, 3);
                $mulaiJam = $slotJam[random_int(0, count($slotJam) - 1)];

                // Jam selesai tidak boleh melewati 22:00
                if ($mulaiJam + $durasi > 22) {
                    continue;
                }

                // Anti overlap: seluruh jam harus benar-benar kosong
                $bebas = true;
                for ($h = $mulaiJam; $h < $mulaiJam + $durasi; $h++) {
                    if (isset($terpakai[$unit->id_unit][$tanggal->toDateString()][$h])) {
                        $bebas = false;
                        break;
                    }
                }

                if (! $bebas) {
                    continue;
                }

                for ($h = $mulaiJam; $h < $mulaiJam + $durasi; $h++) {
                    $terpakai[$unit->id_unit][$tanggal->toDateString()][$h] = true;
                }

                $mulai = sprintf('%02d:00', $mulaiJam);
                $selesai = sprintf('%02d:00', $mulaiJam + $durasi);
                $total = (float) $unit->harga_jam * $durasi;

                // Status: hari lampau cenderung SELESAI, hari ini bervariasi
                if ($tanggal->isPast()) {
                    $status = random_int(1, 10) <= 9 ? 'SELESAI' : 'BATAL';
                } elseif ($isHariIni) {
                    $status = collect(['PENDING', 'CONFIRMED', 'CHECKIN', 'SELESAI'])->random();
                } else {
                    $status = random_int(1, 10) <= 6 ? 'CONFIRMED' : 'PENDING';
                }

                // Pelanggan acak; No. HP demo lebih sering muncul
                $pelanggan = ($pelanggans->count() > 1 && random_int(1, 10) <= 3)
                    ? ($pelanggans->firstWhere('no_hp', '081234567890') ?? $pelanggans->random())
                    : $pelanggans->random();

                $booking = Booking::create([
                    'kode' => $this->kodeUnik(),
                    'id_pelanggan' => $pelanggan->id_pelanggan,
                    'id_unit' => $unit->id_unit,
                    'id_staff' => in_array($status, ['CHECKIN', 'SELESAI'], true)
                        ? $staffs->random()
                        : null,
                    'tgl_main' => $tanggal->toDateString(),
                    'mulai' => $mulai,
                    'selesai' => $selesai,
                    'durasi_jam' => $durasi,
                    'total' => $total,
                    'status' => $status,
                ]);

                // Pembayaran: mayoritas LUNAS, sisanya BELUM_BAYAR
                $lunas = $status === 'BATAL'
                    ? false
                    : ($status === 'SELESAI' || random_int(1, 10) <= 7);

                Pembayaran::create([
                    'id_booking' => $booking->id_booking,
                    'id_staff' => $lunas ? $staffs->random() : null,
                    'metode' => collect(['TRANSFER_BANK', 'E_WALLET', 'BAYAR_DI_TEMPAT'])->random(),
                    'nominal' => $total,
                    'status' => $lunas ? 'LUNAS' : 'BELUM_BAYAR',
                    // Waktu bayar di jam mulai agar laporan harian ikut terisi
                    'waktu' => $lunas
                        ? $tanggal->copy()->setTime($mulaiJam, random_int(0, 59))
                        : null,
                ]);

                $dibuatHari++;
            }
        }

        // Jamin selalu ada booking PENDING/CONFIRMED hari ini (untuk demo check-in & dashboard)
        $hariIni = $tanggalHariIni->toDateString();
        $adaAktifHariIni = Booking::where('tgl_main', $hariIni)
            ->whereIn('status', ['PENDING', 'CONFIRMED'])
            ->exists();

        if (! $adaAktifHariIni) {
            Booking::where('tgl_main', $hariIni)
                ->where('status', '!=', 'BATAL')
                ->orderBy('mulai')
                ->first()
                ?->update(['status' => 'CONFIRMED']);
        }

        // Sinkronkan status unit yang sedang dipakai booking CHECKIN hari ini
        $idUnitCheckin = Booking::where('status', 'CHECKIN')
            ->where('tgl_main', $hariIni)
            ->pluck('id_unit');

        UnitPs::whereIn('id_unit', $idUnitCheckin)
            ->where('status', '!=', 'MAINTENANCE')
            ->update(['status' => 'DIGUNAKAN']);
    }

    /**
     * Kode booking unik "BK-XXXXXX".
     */
    private function kodeUnik(): string
    {
        do {
            $kode = 'BK-'.Str::upper(Str::random(6));
        } while (Booking::where('kode', $kode)->exists());

        return $kode;
    }
}
