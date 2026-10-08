@extends('layouts.staff')

@section('title', 'Laporan Pendapatan')
@section('subtitle', 'Rekap pendapatan harian / bulanan')

@section('content')
    <x-card title="Filter">
        <form method="GET" action="{{ route('manajer.laporan.pendapatan') }}" class="flex flex-col gap-3 sm:flex-row sm:items-end">
            <div>
                <label class="mb-1.5 block text-xs text-slate-400">Granulitas</label>
                <select name="granulitas" class="rounded-xl border border-slate-700 bg-ink-800 px-3 py-2.5 text-sm text-white focus:border-cyan-400 focus:outline-none">
                    <option value="harian" {{ $granulitas === 'harian' ? 'selected' : '' }}>Harian</option>
                    <option value="bulanan" {{ $granulitas === 'bulanan' ? 'selected' : '' }}>Bulanan</option>
                </select>
            </div>
            <div>
                <label class="mb-1.5 block text-xs text-slate-400">Bulan Acuan</label>
                <input type="month" name="bulan" value="{{ $bulan }}"
                       class="rounded-xl border border-slate-700 bg-ink-800 px-3 py-2.5 text-sm text-white focus:border-cyan-400 focus:outline-none">
            </div>
            <x-button type="submit">Terapkan</x-button>
            <x-button href="{{ route('manajer.dashboard') }}" variant="ghost">Dashboard</x-button>
        </form>
    </x-card>

    <div class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-2">
        <x-stat label="Total Pendapatan (Periode)" :value="rupiah($totalPendapatan)" icon="💰" />
        <x-stat label="Jumlah Transaksi Lunas" :value="$totalTransaksi" icon="🧾" />
    </div>

    <x-card class="mt-5" title="Detail Pendapatan">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="text-xs uppercase text-slate-500">
                    <tr class="border-b border-slate-800">
                        <th class="py-2 pr-3">Periode</th>
                        <th class="py-2 pr-3">Jumlah Transaksi</th>
                        <th class="py-2 text-right">Pendapatan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($baris as $b)
                        <tr class="border-b border-slate-800/60">
                            <td class="py-2.5 pr-3 font-semibold text-white">{{ $b['periode'] }}</td>
                            <td class="py-2.5 pr-3 text-slate-300">{{ $b['jumlah'] }} transaksi</td>
                            <td class="py-2.5 text-right font-semibold text-cyan-300">{{ rupiah($b['total']) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="py-10 text-center text-slate-500">Belum ada data.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-card>
@endsection
