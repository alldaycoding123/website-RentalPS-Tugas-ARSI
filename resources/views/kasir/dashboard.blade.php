@extends('layouts.staff')

@section('title', 'Dashboard Kasir')
@section('subtitle', 'Ringkasan operasional hari ini · ' . tanggal_id($hariIni))

@section('content')
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <x-stat label="Booking Hari Ini" :value="$bookingHariIni" icon="📋" />
        <x-stat label="Belum Check-in" :value="$belumCheckin" icon="⏳" />
        <x-stat label="Unit Sedang Aktif" :value="$unitAktif" icon="🎮" />
    </div>

    <div class="mt-6 grid grid-cols-1 gap-5 lg:grid-cols-2">
        <x-card title="Booking Hari Ini" subtitle="Urut berdasarkan jam mulai">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="text-xs uppercase text-slate-500">
                        <tr class="border-b border-slate-800">
                            <th class="py-2 pr-3">Kode</th>
                            <th class="py-2 pr-3">Pelanggan</th>
                            <th class="py-2 pr-3">Unit</th>
                            <th class="py-2 pr-3">Jam</th>
                            <th class="py-2">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($terbaru as $b)
                            <tr class="border-b border-slate-800/60">
                                <td class="py-2.5 pr-3 font-mono text-cyan-300">{{ $b->kode }}</td>
                                <td class="py-2.5 pr-3 text-white">{{ $b->pelanggan->nama }}</td>
                                <td class="py-2.5 pr-3 text-slate-300">{{ $b->unit->kode_unit }}</td>
                                <td class="py-2.5 pr-3 font-mono text-slate-300">{{ jam($b->mulai) }}–{{ jam($b->selesai) }}</td>
                                <td class="py-2.5"><x-badge :status="$b->status" /></td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="py-8 text-center text-slate-500">Belum ada booking hari ini.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-card>

        <div class="space-y-5">
            <x-card title="Aksi Cepat">
                <div class="grid grid-cols-1 gap-3">
                    <x-button href="{{ route('kasir.checkin') }}" class="w-full">🎟️ Proses Check-in</x-button>
                    <x-button href="{{ route('kasir.checkout') }}" variant="secondary" class="w-full">🚪 Daftar Check-out</x-button>
                    <x-button href="{{ route('kasir.pembayaran') }}" variant="secondary" class="w-full">💳 Proses Pembayaran</x-button>
                </div>
            </x-card>

            <x-card title="Status Pembayaran Hari Ini">
                @php
                    $lunas = \App\Models\Pembayaran::where('status', 'LUNAS')
                        ->whereHas('booking', fn ($q) => $q->where('tgl_main', $hariIni))
                        ->count();
                    $belum = \App\Models\Pembayaran::where('status', 'BELUM_BAYAR')
                        ->whereHas('booking', fn ($q) => $q->where('tgl_main', $hariIni))
                        ->count();
                @endphp
                <div class="flex items-center justify-around text-center">
                    <div>
                        <p class="text-2xl font-extrabold text-emerald-300">{{ $lunas }}</p>
                        <p class="text-xs text-slate-400">Lunas</p>
                    </div>
                    <div class="h-10 w-px bg-slate-700"></div>
                    <div>
                        <p class="text-2xl font-extrabold text-rose-300">{{ $belum }}</p>
                        <p class="text-xs text-slate-400">Belum Bayar</p>
                    </div>
                </div>
            </x-card>
        </div>
    </div>
@endsection
