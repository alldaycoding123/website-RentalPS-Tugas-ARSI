<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Pembayaran;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LaporanController extends Controller
{
    /**
     * Laporan transaksi: filter rentang tanggal, ringkasan, tabel detail.
     */
    public function index(Request $request)
    {
        $dari = $request->query('dari', Carbon::now('Asia/Makassar')->startOfMonth()->toDateString());
        $sampai = $request->query('sampai', Carbon::now('Asia/Makassar')->toDateString());

        $query = $this->queryTransaksi($dari, $sampai);

        $pembayarans = ($request->query('csv') === '1')
            ? $query->get()
            : $query->paginate(15)->withQueryString();

        return view('admin.laporan', [
            'pembayarans' => $pembayarans,
            'dari' => $dari,
            'sampai' => $sampai,
            'totalPendapatan' => (clone $query)->sum('pembayaran.nominal'),
            'jumlahTransaksi' => (clone $query)->count(),
            'jumlahLunas' => (clone $query)->where('pembayaran.status', 'LUNAS')->count(),
        ]);
    }

    /**
     * Ekspor CSV laporan (streamDownload, tanpa package tambahan).
     */
    public function csv(Request $request): StreamedResponse
    {
        $dari = $request->query('dari', Carbon::now('Asia/Makassar')->startOfMonth()->toDateString());
        $sampai = $request->query('sampai', Carbon::now('Asia/Makassar')->toDateString());

        $namaFile = "laporan-transaksi-{$dari}-{$sampai}.csv";

        return response()->streamDownload(function () use ($dari, $sampai) {
            $handle = fopen('php://output', 'w');

            // BOM agar karakter UTF-8 rapi di Excel
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                'Kode Booking', 'Tanggal', 'Pelanggan', 'No. HP', 'Unit',
                'Jam', 'Durasi (jam)', 'Metode', 'Nominal', 'Status Bayar', 'Status Booking',
            ], ';');

            foreach ($this->queryTransaksi($dari, $sampai)->get() as $p) {
                $b = $p->booking;
                fputcsv($handle, [
                    $b->kode,
                    $b->tgl_main->format('d-m-Y'),
                    $b->pelanggan->nama ?? '-',
                    $b->pelanggan->no_hp ?? '-',
                    $b->unit->kode_unit.' ('.$b->unit->nama.')',
                    substr((string) $b->mulai, 0, 5).'-'.substr((string) $b->selesai, 0, 5),
                    $b->durasi_jam,
                    $p->metode,
                    $p->nominal,
                    $p->status,
                    $b->status,
                ], ';');
            }

            fclose($handle);
        }, $namaFile, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * Query transaksi pada rentang tanggal booking (bukan waktu bayar).
     */
    private function queryTransaksi(string $dari, string $sampai)
    {
        return Pembayaran::query()
            ->join('booking', 'booking.id_booking', '=', 'pembayaran.id_booking')
            ->leftJoin('pelanggan', 'pelanggan.id_pelanggan', '=', 'booking.id_pelanggan')
            ->leftJoin('unit_ps', 'unit_ps.id_unit', '=', 'booking.id_unit')
            ->whereBetween('booking.tgl_main', [$dari, $sampai])
            ->select([
                'pembayaran.*',
                'booking.kode as kode_booking',
                'booking.tgl_main as tgl_main',
                'booking.mulai as mulai',
                'booking.selesai as selesai',
                'booking.durasi_jam as durasi_jam',
                'booking.status as status_booking',
                'pelanggan.nama as nama_pelanggan',
                'pelanggan.no_hp as no_hp_pelanggan',
                'unit_ps.kode_unit as kode_unit',
            ])
            ->orderByDesc('booking.tgl_main')
            ->orderByDesc('booking.mulai');
    }
}
