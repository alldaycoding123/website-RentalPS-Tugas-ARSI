@extends('layouts.staff')

@section('title', 'Data Booking')
@section('subtitle', 'Daftar booking dengan filter tanggal, status, dan unit')

@section('content')
    <x-card title="Filter">
        <form method="GET" action="{{ route('admin.bookings.index') }}" class="grid grid-cols-1 gap-3 sm:grid-cols-4">
            <div>
                <label class="mb-1.5 block text-xs text-slate-400">Tanggal</label>
                <input type="date" name="tanggal" value="{{ $filters['tanggal'] ?? '' }}"
                       class="w-full rounded-xl border border-slate-700 bg-ink-800 px-3 py-2 text-sm text-white focus:border-cyan-400 focus:outline-none">
            </div>
            <div>
                <label class="mb-1.5 block text-xs text-slate-400">Status</label>
                <select name="status" class="w-full rounded-xl border border-slate-700 bg-ink-800 px-3 py-2 text-sm text-white focus:border-cyan-400 focus:outline-none">
                    <option value="">Semua</option>
                    @foreach($statuses as $s)
                        <option value="{{ $s }}" {{ ($filters['status'] ?? '') === $s ? 'selected' : '' }}>{{ $s }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1.5 block text-xs text-slate-400">Unit</label>
                <select name="unit" class="w-full rounded-xl border border-slate-700 bg-ink-800 px-3 py-2 text-sm text-white focus:border-cyan-400 focus:outline-none">
                    <option value="">Semua</option>
                    @foreach($units as $u)
                        <option value="{{ $u->id_unit }}" {{ ($filters['unit'] ?? '') == $u->id_unit ? 'selected' : '' }}>{{ $u->kode_unit }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end gap-2">
                <x-button type="submit" class="flex-1">Terapkan</x-button>
                <x-button href="{{ route('admin.bookings.index') }}" variant="ghost">Reset</x-button>
            </div>
        </form>
    </x-card>

    <div class="mt-5 mb-4 flex items-center justify-between">
        <p class="text-sm text-slate-400">{{ $bookings->total() }} booking ditemukan</p>
        <x-button href="{{ route('admin.bookings.create') }}">+ Booking Walk-in</x-button>
    </div>

    <x-card>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="text-xs uppercase text-slate-500">
                    <tr class="border-b border-slate-800">
                        <th class="py-2 pr-3">Kode</th>
                        <th class="py-2 pr-3">Tanggal</th>
                        <th class="py-2 pr-3">Jam</th>
                        <th class="py-2 pr-3">Unit</th>
                        <th class="py-2 pr-3">Pelanggan</th>
                        <th class="py-2 pr-3">Total</th>
                        <th class="py-2 pr-3">Booking</th>
                        <th class="py-2 pr-3">Bayar</th>
                        <th class="py-2 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($bookings as $b)
                        <tr class="border-b border-slate-800/60">
                            <td class="py-3 pr-3 font-mono text-cyan-300">{{ $b->kode }}</td>
                            <td class="py-3 pr-3 text-slate-300">{{ $b->tgl_main->format('d/m/Y') }}</td>
                            <td class="py-3 pr-3 font-mono text-slate-300">{{ jam($b->mulai) }}–{{ jam($b->selesai) }}</td>
                            <td class="py-3 pr-3 text-slate-300">{{ $b->unit->kode_unit }}</td>
                            <td class="py-3 pr-3 text-white">{{ $b->pelanggan->nama }}</td>
                            <td class="py-3 pr-3 text-slate-300">{{ rupiah($b->total) }}</td>
                            <td class="py-3 pr-3"><x-badge :status="$b->status" /></td>
                            <td class="py-3 pr-3"><x-badge :status="$b->pembayaran?->status ?? 'BELUM_BAYAR'" /></td>
                            <td class="py-3 text-right">
                                <div class="inline-flex gap-2">
                                    <x-button href="{{ route('admin.bookings.edit', $b) }}" variant="secondary" class="px-3 py-1.5 text-xs">Detail</x-button>

                                    @if(!in_array($b->status, ['SELESAI', 'BATAL'], true))
                                        <form method="POST" action="{{ route('admin.bookings.destroy', $b) }}"
                                              onsubmit="return confirm('Batalkan booking {{ $b->kode }}?');">
                                            @csrf
                                            @method('DELETE')
                                            <x-button type="submit" variant="danger" class="px-3 py-1.5 text-xs">Batal</x-button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="py-10 text-center text-slate-500">
                                Belum ada booking dengan filter ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-5">
            {{ $bookings->links() }}
        </div>
    </x-card>
@endsection
