@extends('layouts.staff')

@section('title', 'Jadwal')
@section('subtitle', 'Matriks unit × jam pada tanggal terpilih')

@section('content')
    <x-card title="Filter Tanggal">
        <form method="GET" action="{{ route('admin.jadwal.index') }}" class="flex flex-col gap-3 sm:flex-row sm:items-end">
            <div>
                <label class="mb-1.5 block text-sm font-medium text-slate-300">Tanggal</label>
                <input type="date" name="tanggal" value="{{ $tanggal }}" min="{{ now('Asia/Makassar')->toDateString() }}"
                       class="rounded-xl border border-slate-700 bg-ink-800 px-3 py-2.5 text-sm text-white focus:border-cyan-400 focus:outline-none focus:ring-1 focus:ring-cyan-400">
            </div>
            <x-button type="submit">Tampilkan</x-button>
        </form>
    </x-card>

    <x-card class="mt-5" title="Matriks Jadwal" subtitle="Sel terisi = ada booking (klik tidak perlu, lihat kode booking di tooltip)">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-slate-800 text-slate-500">
                        <th class="sticky left-0 bg-[#111827] py-2 pr-3">Unit \ Jam</th>
                        @foreach($slotJam as $jam)
                            <th class="py-2 px-1 text-center">{{ substr($jam, 0, 2) }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @forelse($units as $unit)
                        <tr class="border-b border-slate-800/60">
                            <td class="sticky left-0 bg-[#111827] py-2 pr-3">
                                <span class="font-semibold text-white">{{ $unit->kode_unit }}</span>
                                <span class="block text-[10px] text-slate-500">{{ $unit->ruang }}</span>
                            </td>

                            @foreach($slotJam as $jam)
                                @php($b = $peta[$unit->id_unit][$jam] ?? null)
                                <td class="p-1">
                                    @if($b)
                                        <div title="{{ $b->kode }} · {{ $b->pelanggan->nama ?? '-' }}"
                                             class="grid h-7 place-items-center rounded-md border border-indigo-500/40 bg-indigo-500/20 font-mono text-[10px] text-indigo-200">
                                            ✓
                                        </div>
                                    @elseif($unit->status === 'MAINTENANCE')
                                        <div class="grid h-7 place-items-center rounded-md border border-slate-700 bg-slate-800/50 text-[10px] text-slate-500">M</div>
                                    @else
                                        <div class="grid h-7 place-items-center rounded-md border border-slate-800 bg-transparent text-[10px] text-slate-700">·</div>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @empty
                        <tr><td colspan="{{ count($slotJam) + 1 }}" class="py-8 text-center text-slate-500">Belum ada unit.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4 flex flex-wrap gap-4 text-xs text-slate-400">
            <span class="flex items-center gap-2"><span class="h-3 w-3 rounded border border-indigo-500/40 bg-indigo-500/20"></span> Terisi (booking)</span>
            <span class="flex items-center gap-2"><span class="h-3 w-3 rounded border border-slate-700 bg-slate-800/50"></span> Maintenance</span>
            <span class="flex items-center gap-2"><span class="h-3 w-3 rounded border border-slate-800"></span> Kosong</span>
        </div>
    </x-card>
@endsection
