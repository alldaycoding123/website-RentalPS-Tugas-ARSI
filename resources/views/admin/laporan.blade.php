@extends('layouts.staff')

@section('title', 'Laporan Transaksi')
@section('subtitle', 'Rekap pendapatan per rentang tanggal')

@section('content')
    <div class="no-print">
        <x-card title="Filter Rentang Tanggal">
            <form method="GET" action="{{ route('admin.laporan.index') }}" class="flex flex-col gap-3 sm:flex-row sm:items-end">
                <div>
                    <label class="mb-1.5 block text-xs text-slate-400">Dari</label>
                    <input type="date" name="dari" value="{{ $dari }}" required
                           class="rounded-xl border border-slate-700 bg-ink-800 px-3 py-2.5 text-sm text-white focus:border-cyan-400 focus:outline-none">
                </div>
                <div>
                    <label class="mb-1.5 block text-xs text-slate-400">Sampai</label>
                    <input type="date" name="sampai" value="{{ $sampai }}" required
                           class="rounded-xl border border-slate-700 bg-ink-800 px-3 py-2.5 text-sm text-white focus:border-cyan-400 focus:outline-none">
                </div>
                <x-button type="submit">Terapkan</x-button>

                <a href="{{ route('admin.laporan.csv') }}?dari={{ $dari }}&sampai={{ $sampai }}"
                   class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-700 px-4 py-2.5 text-sm font-semibold text-slate-200 hover:bg-slate-800">
                    ⬇ Ekspor CSV
                </a>
                <x-button variant="secondary" onclick="window.print()">🖨 Cetak</x-button>
            </form>
        </x-card>

        {{-- Ringkasan --}}
        <div class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-3">
            <x-stat label="Total Pendapatan" :value="rupiah($totalPendapatan)" icon="💰" />
            <x-stat label="Jumlah Transaksi" :value="$jumlahTransaksi" icon="🧾" />
            <x-stat label="Sudah Lunas" :value="$jumlahLunas" icon="✅" />
        </div>
    </div>

    {{-- Kop laporan (muncul saat dicetak) --}}
    <div class="mt-6 hidden print-header">
        <h1 class="text-xl font-bold">PSRental — Laporan Transaksi</h1>
        <p class="text-sm">Periode {{ tanggal_id($dari) }} s.d. {{ tanggal_id($sampai) }}</p>
    </div>

    <x-card class="mt-5" title="Detail Transaksi" subtitle="Booking pada rentang tanggal terpilih">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="text-xs uppercase text-slate-500">
                    <tr class="border-b border-slate-800">
                        <th class="py-2 pr-3">No</th>
                        <th class="py-2 pr-3">Kode</th>
                        <th class="py-2 pr-3">Tanggal</th>
                        <th class="py-2 pr-3">Pelanggan</th>
                        <th class="py-2 pr-3">Unit</th>
                        <th class="py-2 pr-3">Jam</th>
                        <th class="py-2 pr-3">Durasi</th>
                        <th class="py-2 pr-3">Metode</th>
                        <th class="py-2 pr-3">Nominal</th>
                        <th class="py-2">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pembayarans as $i => $p)
                        <tr class="border-b border-slate-800/60">
                            <td class="py-2.5 pr-3 text-slate-500">{{ $pembayarans instanceof \Illuminate\Pagination\LengthAwarePaginator ? $pembayarans->firstItem() + $i : $i + 1 }}</td>
                            <td class="py-2.5 pr-3 font-mono text-cyan-300">{{ $p->booking->kode ?? '-' }}</td>
                            <td class="py-2.5 pr-3 text-slate-300">{{ $p->booking?->tgl_main?->format('d/m/Y') ?? '-' }}</td>
                            <td class="py-2.5 pr-3 text-white">{{ $p->booking->pelanggan->nama ?? '-' }}</td>
                            <td class="py-2.5 pr-3 text-slate-300">{{ $p->booking->unit->kode_unit ?? '-' }}</td>
                            <td class="py-2.5 pr-3 font-mono text-slate-300">
                                {{ $p->booking ? jam($p->booking->mulai).'-'.jam($p->booking->selesai) : '-' }}
                            </td>
                            <td class="py-2.5 pr-3 text-slate-300">{{ $p->booking?->durasi_jam ?? '-' }} jam</td>
                            <td class="py-2.5 pr-3 text-slate-300">{{ str_replace('_', ' ', $p->metode) }}</td>
                            <td class="py-2.5 pr-3 font-semibold text-slate-200">{{ rupiah($p->nominal) }}</td>
                            <td class="py-2.5">
                                <x-badge :status="$p->status" />
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="py-10 text-center text-slate-500">
                                Tidak ada transaksi pada rentang tanggal ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="border-t-2 border-slate-700">
                        <td colspan="8" class="py-3 text-right font-semibold text-white">Total Pendapatan</td>
                        <td colspan="2" class="py-3 font-extrabold text-cyan-300">{{ rupiah($totalPendapatan) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        @if($pembayarans instanceof \Illuminate\Pagination\LengthAwarePaginator)
            <div class="mt-5 no-print">{{ $pembayarans->links() }}</div>
        @endif
    </x-card>
@endsection

@push('head')
<style>
    @media print {
        /* Sembunyikan sidebar, topbar, dan tombol saat mencetak */
        aside, header, .no-print, [data-flash], footer { display: none !important; }
        body { background: #fff !important; color: #000 !important; }
        main { padding: 0 !important; }
        .print-header { display: block !important; }
        .rounded-2xl, table { border-color: #cbd5e1 !important; box-shadow: none !important; }
        * { color: #000 !important; background: transparent !important; }
        a[href]:after { content: ''; }
    }
</style>
@endpush
