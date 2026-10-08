@extends('layouts.staff')

@section('title', 'Proses Pembayaran')
@section('subtitle', 'Konfirmasi pembayaran booking yang belum lunas')

@section('content')
    <x-card title="Cari Booking Belum Lunas">
        <form method="GET" action="{{ route('kasir.pembayaran') }}" class="flex flex-col gap-3 sm:flex-row">
            <input type="text" name="q" value="{{ $q }}" placeholder="Kode booking atau No. HP"
                   class="flex-1 rounded-xl border border-slate-700 bg-ink-800 px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:border-cyan-400 focus:outline-none focus:ring-1 focus:ring-cyan-400">
            <x-button type="submit" class="sm:px-6">Cari</x-button>
        </form>
    </x-card>

    <div class="mt-5 space-y-4">
        @forelse($bookings as $b)
            <x-card>
                <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
                    <div>
                        <div class="flex items-center gap-3">
                            <span class="font-mono text-lg font-bold text-cyan-300">{{ $b->kode }}</span>
                            <x-badge :status="$b->status" />
                        </div>

                        <dl class="mt-3 space-y-1.5 text-sm">
                            <div class="flex justify-between gap-4">
                                <dt class="text-slate-400">Pelanggan</dt>
                                <dd class="font-semibold text-white">{{ $b->pelanggan->nama }} ({{ $b->pelanggan->no_hp }})</dd>
                            </div>
                            <div class="flex justify-between gap-4">
                                <dt class="text-slate-400">Unit</dt>
                                <dd class="font-semibold text-white">{{ $b->unit->nama }} · {{ $b->unit->kode_unit }}</dd>
                            </div>
                            <div class="flex justify-between gap-4">
                                <dt class="text-slate-400">Tanggal & Jam</dt>
                                <dd class="font-semibold text-white">{{ tanggal_id($b->tgl_main) }} · <span class="font-mono">{{ jam($b->mulai) }}–{{ jam($b->selesai) }}</span></dd>
                            </div>
                            <div class="flex justify-between gap-4">
                                <dt class="text-slate-400">Tagihan</dt>
                                <dd class="text-lg font-extrabold text-cyan-300">{{ rupiah($b->total) }}</dd>
                            </div>
                        </dl>
                    </div>

                    <form method="POST" action="{{ route('kasir.pembayaran.proses', $b) }}" class="rounded-2xl border border-slate-800 bg-ink-800 p-4">
                        @csrf
                        <p class="mb-3 text-sm font-semibold text-white">Konfirmasi Pembayaran</p>

                        <label class="mb-2 block text-xs text-slate-400">Metode</label>
                        <div class="mb-3 grid grid-cols-1 gap-2 sm:grid-cols-3">
                            @foreach(['TRANSFER_BANK' => 'Transfer', 'E_WALLET' => 'E-Wallet', 'BAYAR_DI_TEMPAT' => 'Tempat'] as $key => $label)
                                <label class="flex cursor-pointer items-center gap-2 rounded-xl border border-slate-700 px-3 py-2 text-sm text-slate-300 hover:border-slate-600">
                                    <input type="radio" name="metode" value="{{ $key }}" class="accent-cyan-400"
                                           {{ old('metode', $b->pembayaran?->metode) === $key ? 'checked' : '' }} required>
                                    {{ $label }}
                                </label>
                            @endforeach
                        </div>
                        @error('metode') <p class="mb-2 text-xs text-rose-400">{{ $message }}</p> @enderror

                        <label class="mb-1 block text-xs text-slate-400">Nominal</label>
                        <input type="number" name="nominal" value="{{ old('nominal', $b->total) }}" min="0" step="1000" required
                               class="mb-3 w-full rounded-xl border border-slate-700 bg-ink-900 px-3 py-2.5 text-sm text-white focus:border-cyan-400 focus:outline-none focus:ring-1 focus:ring-cyan-400">
                        @error('nominal') <p class="mb-2 text-xs text-rose-400">{{ $message }}</p> @enderror

                        <x-button type="submit" variant="success" class="w-full">💳 Tandai LUNAS</x-button>
                        <p class="mt-2 text-xs text-slate-500">Booking PENDING otomatis menjadi CONFIRMED setelah lunas.</p>
                    </form>
                </div>
            </x-card>
        @empty
            <div class="rounded-2xl border border-dashed border-slate-700 p-12 text-center">
                <p class="text-4xl">✅</p>
                <p class="mt-3 font-semibold text-white">Semua pembayaran sudah lunas</p>
                <p class="mt-1 text-sm text-slate-400">Tidak ada booking menunggu pembayaran.</p>
            </div>
        @endforelse
    </div>

    <div class="mt-6">
        {{ $bookings->links() }}
    </div>
@endsection
