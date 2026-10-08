@extends('layouts.staff')

@section('title', 'Check-out Booking')
@section('subtitle', 'Selesaikan booking yang sedang berjalan')

@section('content')
    <x-card title="Booking Sedang Check-in" subtitle="Tekan Check-out untuk menyelesaikan booking dan melepas unit.">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="text-xs uppercase text-slate-500">
                    <tr class="border-b border-slate-800">
                        <th class="py-2 pr-3">Kode</th>
                        <th class="py-2 pr-3">Pelanggan</th>
                        <th class="py-2 pr-3">Unit</th>
                        <th class="py-2 pr-3">Jam</th>
                        <th class="py-2 pr-3">Bayar</th>
                        <th class="py-2 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($bookings as $b)
                        <tr class="border-b border-slate-800/60">
                            <td class="py-3 pr-3 font-mono text-cyan-300">{{ $b->kode }}</td>
                            <td class="py-3 pr-3">
                                <p class="font-semibold text-white">{{ $b->pelanggan->nama }}</p>
                                <p class="text-xs text-slate-500">{{ $b->pelanggan->no_hp }}</p>
                            </td>
                            <td class="py-3 pr-3 text-slate-300">{{ $b->unit->kode_unit }}</td>
                            <td class="py-3 pr-3 font-mono text-slate-300">{{ jam($b->mulai) }}–{{ jam($b->selesai) }}</td>
                            <td class="py-3 pr-3">
                                <x-badge :status="$b->pembayaran?->status ?? 'BELUM_BAYAR'" />
                            </td>
                            <td class="py-3 text-right">
                                <form method="POST" action="{{ route('kasir.checkout.proses', $b) }}" class="inline">
                                    @csrf
                                    <x-button type="submit" variant="success" class="px-3 py-1.5 text-xs">Check-out</x-button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-10 text-center text-slate-500">
                                Belum ada booking yang check-in. 🎮
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-card>
@endsection
