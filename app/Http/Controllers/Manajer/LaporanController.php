<?php

namespace App\Http\Controllers\Manajer;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Pembayaran;
use App\Models\UnitPs;
use Carbon\Carbon;
use Illuminate\Http\Request;

class LaporanController extends Controller
{
    /**
     * Laporan pendapatan (harian / bulanan) sesuai pilihan `granulitas`.
     */
    public function pendapatan(Request $request)
    {
        $granulitas = $request->query('granulitas', 'harian');
        $bulan = $request->query('bulan', Carbon::now('Asia/Makassar')->format('Y-m'));

        if ($granulitas === 'bulanan') {
            // Pendapatan per bulan selama 12 bulan terakhir
            $baris = collect(range(11, 0))->map(function ($i) use ($bulan) {
                $target = Carbon::parse($bulan.'-01')->subMonths($i);

                $total = (float) Pembayaran::where('status', 'LUNAS')
                    ->whereYear('waktu', $target->year)
                    ->whereMonth('waktu', $target->month)
                    ->sum('nominal');

                $jumlah = Pembayaran::where('status', 'LUNAS')
                    ->whereYear('waktu', $target->year)
                    ->whereMonth('waktu', $target->month)
                    ->count();

                return [
                    'periode' => $target->translatedFormat('F Y'),
                    'total' => $total,
                    'jumlah' => $jumlah,
                ];
            });
        } else {
            // Pendapatan harian pada bulan terpilih
            $awal = Carbon::parse($bulan.'-01');
            $akhir = $awal->copy()->endOfMonth();
            $baris = collect();

            for ($tgl = $awal->copy(); $tgl->lte($akhir); $tgl->addDay()) {
                $total = (float) Pembayaran::where('status', 'LUNAS')
                    ->whereDate('waktu', $tgl->toDateString())
                    ->sum('nominal');

                $jumlah = Pembayaran::where('status', 'LUNAS')
                    ->whereDate('waktu', $tgl->toDateString())
                    ->count();

                $baris->push([
                    'periode' => $tgl->translatedFormat('d M Y'),
                    'total' => $total,
                    'jumlah' => $jumlah,
                ]);
            }
        }

        return view('manajer.laporan-pendapatan', [
            'baris' => $baris,
            'granulitas' => $granulitas,
            'bulan' => $bulan,
            'totalPendapatan' => (float) $baris->sum('total'),
            'totalTransaksi' => (int) $baris->sum('jumlah'),
        ]);
    }

    /**
     * Performa unit: jumlah booking, total jam, pendapatan per unit.
     */
    public function performaUnit()
    {
        $units = UnitPs::withCount(['bookings as total_booking' => fn ($q) => $q->where('status', '!=', 'BATAL')])
            ->withSum('bookings as total_jam', 'durasi_jam')
            ->orderByDesc('total_booking')
            ->get()
            ->map(function (UnitPs $unit) {
                $pendapatan = (float) Pembayaran::where('status', 'LUNAS')
                    ->whereHas('booking', fn ($q) => $q->where('id_unit', $unit->id_unit))
                    ->sum('nominal');

                return [
                    'unit' => $unit,
                    'total_booking' => (int) $unit->total_booking,
                    'total_jam' => (int) ($unit->total_jam ?? 0),
                    'pendapatan' => $pendapatan,
                ];
            });

        return view('manajer.performa-unit', [
            'units' => $units,
        ]);
    }

    /**
     * Tren & analitik: hari paling ramai, jam tersibuk.
     */
    public function tren()
    {
        // Hari paling ramai (booking valid, sepanjang waktu)
        $hariRamai = Booking::where('status', '!=', 'BATAL')
            ->selectRaw('DAYOFWEEK(tgl_main) as hari_num, COUNT(*) as jumlah')
            ->groupBy('hari_num')
            ->orderByDesc('jumlah')
            ->get();

        $namaHari = [1 => 'Minggu', 2 => 'Senin', 3 => 'Selasa', 4 => 'Rabu', 5 => 'Kamis', 6 => 'Jumat', 7 => 'Sabtu'];
        $petaHari = $hariRamai->mapWithKeys(fn ($h) => [$namaHari[$h->hari_num] => (int) $h->jumlah]);

        // Jam tersibuk (berdasarkan jam mulai booking)
        $jamSibuk = Booking::where('status', '!=', 'BATAL')
            ->selectRaw('HOUR(mulai) as jam_num, COUNT(*) as jumlah')
            ->groupBy('jam_num')
            ->orderByDesc('jumlah')
            ->get();

        $totalBooking = max(1, (int) $petaHari->sum());

        // Ringkasan tren 30 hari vs 30 hari sebelumnya
        $tigaPuluhHari = Booking::where('status', '!=', 'BATAL')
            ->where('tgl_main', '>=', Carbon::now('Asia/Makassar')->subDays(29)->toDateString())
            ->count();
        $sebelumnya = Booking::where('status', '!=', 'BATAL')
            ->whereBetween('tgl_main', [
                Carbon::now('Asia/Makassar')->subDays(59)->toDateString(),
                Carbon::now('Asia/Makassar')->subDays(30)->toDateString(),
            ])
            ->count();

        $persen = $sebelumnya > 0
            ? round((($tigaPuluhHari - $sebelumnya) / $sebelumnya) * 100, 1)
            : null;

        return view('manajer.tren', [
            'petaHari' => $petaHari,
            'jamSibuk' => $jamSibuk,
            'totalBooking' => $totalBooking,
            'booking30Hari' => $tigaPuluhHari,
            'bookingSebelumnya' => $sebelumnya,
            'persen' => $persen,
        ]);
    }
}
