@extends('layouts.staff')

@section('title', 'Tren & Analitik')
@section('subtitle', 'Hari paling ramai, jam tersibuk, dan tren booking')

@section('content')
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <x-stat label="Booking 30 Hari Terakhir" :value="$booking30Hari" icon="📅" />
        <x-stat label="30 Hari Sebelumnya" :value="$bookingSebelumnya" icon="📆" />
        <x-stat label="Perubahan" :value="($persen === null ? 'Baru' : ($persen > 0 ? '+' : '').$persen.'%')" icon="📈" />
    </div>

    <div class="mt-6 grid grid-cols-1 gap-5 lg:grid-cols-2">
        {{-- Hari paling ramai --}}
        <x-card title="Hari Paling Ramai" subtitle="Berdasarkan jumlah booking valid">
            @php($maks = max(1, (int) $petaHari->max()))
            <div class="space-y-3">
                @foreach($petaHari as $hari => $jumlah)
                    <div>
                        <div class="mb-1 flex items-center justify-between text-sm">
                            <span class="text-slate-300">{{ $hari }}</span>
                            <span class="font-semibold text-white">{{ $jumlah }} booking</span>
                        </div>
                        <div class="h-2.5 w-full overflow-hidden rounded-full bg-slate-800">
                            <div class="h-full rounded-full bg-gradient-to-r from-cyan-400 to-indigo-500"
                                 style="width: {{ round(($jumlah / $maks) * 100) }}%"></div>
                        </div>
                    </div>
                @endforeach

                @if($petaHari->isEmpty())
                    <p class="text-sm text-slate-500">Belum ada data booking.</p>
                @endif
            </div>

            @if($petaHari->isNotEmpty())
                <p class="mt-4 rounded-xl border border-cyan-500/30 bg-cyan-500/10 px-3 py-2 text-sm text-cyan-200">
                    🏆 Hari tersibuk: <span class="font-semibold">{{ $petaHari->keys()->first() }}</span>
                    ({{ $petaHari->first() }} booking)
                </p>
            @endif
        </x-card>

        {{-- Jam tersibuk --}}
        <x-card title="Jam Tersibuk" subtitle="Berdasarkan jam mulai booking">
            @php($maksJam = max(1, (int) $jamSibuk->max('jumlah')))
            <div class="flex h-56 items-end gap-1.5">
                @foreach($jamSibuk->sortBy('jam_num') as $j)
                    <div class="flex flex-1 flex-col items-center justify-end gap-1">
                        <span class="text-[10px] text-slate-400">{{ $j->jumlah }}</span>
                        <div class="w-full rounded-t-md bg-gradient-to-t from-indigo-600 to-cyan-400"
                             style="height: {{ max(6, round(($j->jumlah / $maksJam) * 150)) }}px"
                             title="{{ sprintf('%02d:00', $j->jam_num) }} — {{ $j->jumlah }} booking"></div>
                        <span class="text-[10px] text-slate-500">{{ sprintf('%02d', $j->jam_num) }}</span>
                    </div>
                @endforeach
            </div>

            @if($jamSibuk->isNotEmpty())
                <p class="mt-4 rounded-xl border border-indigo-500/30 bg-indigo-500/10 px-3 py-2 text-sm text-indigo-200">
                    ⏰ Jam tersibuk: <span class="font-mono font-semibold">{{ sprintf('%02d:00', $jamSibuk->first()->jam_num) }}</span>
                    ({{ $jamSibuk->first()->jumlah }} booking)
                </p>
            @endif
        </x-card>
    </div>

    <x-card class="mt-5" title="Ringkasan Analitik" subtitle="Insight untuk pengambilan keputusan">
        <ul class="list-disc space-y-2 pl-5 text-sm text-slate-300">
            <li>Total <span class="font-semibold text-white">{{ $totalBooking }}</span> booking valid tercatat pada seluruh periode.</li>
            @if($petaHari->isNotEmpty())
                <li>Permintaan tertinggi ada pada hari <span class="font-semibold text-white">{{ $petaHari->keys()->first() }}</span> — pertimbangkan menambah slot jam sibuk.</li>
            @endif
            @if($jamSibuk->isNotEmpty())
                <li>Jam <span class="font-mono font-semibold text-white">{{ sprintf('%02d:00', $jamSibuk->first()->jam_num) }}</span> adalah jam dengan booking terbanyak.</li>
            @endif
            <li>
                @if($persen === null)
                    Belum cukup data perbandingan 30 hari sebelumnya.
                @elseif($persen >= 0)
                    Tren booking 30 hari naik <span class="font-semibold text-emerald-300">{{ $persen }}%</span> dibanding periode sebelumnya.
                @else
                    Tren booking 30 hari turun <span class="font-semibold text-rose-300">{{ abs($persen) }}%</span> dibanding periode sebelumnya.
                @endif
            </li>
        </ul>
    </x-card>
@endsection
