@extends('layouts.staff')

@section('title', 'Performa Unit')
@section('subtitle', 'Jumlah booking, total jam, dan pendapatan per unit')

@section('content')
    <x-card title="Performa Unit" subtitle="Booking valid (selain BATAL) dan pembayaran LUNAS">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="text-xs uppercase text-slate-500">
                    <tr class="border-b border-slate-800">
                        <th class="py-2 pr-3">Unit</th>
                        <th class="py-2 pr-3">Ruang</th>
                        <th class="py-2 pr-3">Harga/Jam</th>
                        <th class="py-2 pr-3">Jumlah Booking</th>
                        <th class="py-2 pr-3">Total Jam</th>
                        <th class="py-2 text-right">Pendapatan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($units as $row)
                        <tr class="border-b border-slate-800/60">
                            <td class="py-3 pr-3">
                                <p class="font-semibold text-white">{{ $row['unit']->nama }}</p>
                                <p class="text-xs text-cyan-400">{{ $row['unit']->kode_unit }}</p>
                            </td>
                            <td class="py-3 pr-3 text-slate-300">{{ $row['unit']->ruang }}</td>
                            <td class="py-3 pr-3 text-slate-300">{{ rupiah($row['unit']->harga_jam) }}</td>
                            <td class="py-3 pr-3 text-slate-300">{{ $row['total_booking'] }}</td>
                            <td class="py-3 pr-3 text-slate-300">{{ $row['total_jam'] }} jam</td>
                            <td class="py-3 text-right font-semibold text-cyan-300">{{ rupiah($row['pendapatan']) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-10 text-center text-slate-500">Belum ada data unit.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-card>
@endsection
