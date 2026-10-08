@extends('layouts.staff')

@section('title', 'Pembayaran')
@section('subtitle', 'Daftar transaksi dengan filter status & metode')

@section('content')
    <x-card title="Filter">
        <form method="GET" action="{{ route('admin.pembayarans.index') }}" class="grid grid-cols-1 gap-3 sm:grid-cols-3">
            <div>
                <label class="mb-1.5 block text-xs text-slate-400">Status</label>
                <select name="status" class="w-full rounded-xl border border-slate-700 bg-ink-800 px-3 py-2 text-sm text-white focus:border-cyan-400 focus:outline-none">
                    <option value="">Semua</option>
                    <option value="LUNAS" {{ ($filters['status'] ?? '') === 'LUNAS' ? 'selected' : '' }}>LUNAS</option>
                    <option value="BELUM_BAYAR" {{ ($filters['status'] ?? '') === 'BELUM_BAYAR' ? 'selected' : '' }}>BELUM BAYAR</option>
                </select>
            </div>
            <div>
                <label class="mb-1.5 block text-xs text-slate-400">Metode</label>
                <select name="metode" class="w-full rounded-xl border border-slate-700 bg-ink-800 px-3 py-2 text-sm text-white focus:border-cyan-400 focus:outline-none">
                    <option value="">Semua</option>
                    @foreach(['TRANSFER_BANK', 'E_WALLET', 'BAYAR_DI_TEMPAT'] as $m)
                        <option value="{{ $m }}" {{ ($filters['metode'] ?? '') === $m ? 'selected' : '' }}>
                            {{ str_replace('_', ' ', $m) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end gap-2">
                <x-button type="submit" class="flex-1">Terapkan</x-button>
                <x-button href="{{ route('admin.pembayarans.index') }}" variant="ghost">Reset</x-button>
            </div>
        </form>
    </x-card>

    <div class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-3">
        <x-stat label="Total Pendapatan (LUNAS)" :value="rupiah($totalLunas)" icon="💰" />
        <x-stat label="Transaksi Ditampilkan" :value="$pembayarans->total()" icon="🧾" />
        <x-stat label="Metode Terfilter" :value="($filters['metode'] ?? '') !== '' ? str_replace('_', ' ', $filters['metode']) : 'Semua'" icon="💳" />
    </div>

    <x-card class="mt-5">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="text-xs uppercase text-slate-500">
                    <tr class="border-b border-slate-800">
                        <th class="py-2 pr-3">Kode</th>
                        <th class="py-2 pr-3">Pelanggan</th>
                        <th class="py-2 pr-3">Unit</th>
                        <th class="py-2 pr-3">Metode</th>
                        <th class="py-2 pr-3">Nominal</th>
                        <th class="py-2 pr-3">Status</th>
                        <th class="py-2">Waktu</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pembayarans as $p)
                        <tr class="border-b border-slate-800/60">
                            <td class="py-3 pr-3 font-mono text-cyan-300">{{ $p->booking->kode ?? '-' }}</td>
                            <td class="py-3 pr-3 text-white">{{ $p->booking->pelanggan->nama ?? '-' }}</td>
                            <td class="py-3 pr-3 text-slate-300">{{ $p->booking->unit->kode_unit ?? '-' }}</td>
                            <td class="py-3 pr-3 text-slate-300">{{ str_replace('_', ' ', $p->metode) }}</td>
                            <td class="py-3 pr-3 text-slate-300">{{ rupiah($p->nominal) }}</td>
                            <td class="py-3 pr-3"><x-badge :status="$p->status" /></td>
                            <td class="py-3 text-slate-400">{{ $p->waktu?->format('d M Y H:i') ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="py-10 text-center text-slate-500">Belum ada transaksi.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-5">{{ $pembayarans->links() }}</div>
    </x-card>
@endsection
