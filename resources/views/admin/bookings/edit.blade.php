@extends('layouts.staff')

@section('title', 'Detail Booking')
@section('subtitle', $booking->kode)

@section('content')
    <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
        <x-card title="Rincian Booking">
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between gap-4">
                    <dt class="text-slate-400">Kode</dt>
                    <dd class="font-mono font-semibold text-cyan-300">{{ $booking->kode }}</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-slate-400">Pelanggan</dt>
                    <dd class="font-semibold text-white">{{ $booking->pelanggan->nama }} ({{ $booking->pelanggan->no_hp }})</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-slate-400">Unit</dt>
                    <dd class="font-semibold text-white">{{ $booking->unit->nama }} · {{ $booking->unit->kode_unit }} · {{ $booking->unit->ruang }}</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-slate-400">Tanggal</dt>
                    <dd class="font-semibold text-white">{{ $booking->tgl_main->translatedFormat('d M Y') }}</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-slate-400">Jam</dt>
                    <dd class="font-mono font-semibold text-white">{{ jam($booking->mulai) }} - {{ jam($booking->selesai) }}</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-slate-400">Durasi</dt>
                    <dd class="font-semibold text-white">{{ $booking->durasi_jam }} jam</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-slate-400">Total</dt>
                    <dd class="font-semibold text-cyan-300">{{ rupiah($booking->total) }}</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-slate-400">Dibuat</dt>
                    <dd class="text-slate-300">{{ $booking->created_at->format('d M Y H:i') }}</dd>
                </div>
                <div class="flex justify-between gap-4 border-t border-slate-800 pt-3">
                    <dt class="text-slate-400">Status</dt>
                    <dd><x-badge :status="$booking->status" /></dd>
                </div>
            </dl>
        </x-card>

        <div class="space-y-5">
            <x-card title="Ubah Status Booking">
                <form method="POST" action="{{ route('admin.bookings.update', $booking) }}">
                    @csrf
                    @method('PUT')

                    <div class="mb-3 grid grid-cols-2 gap-2 sm:grid-cols-3">
                        @foreach(['PENDING', 'CONFIRMED', 'CHECKIN', 'SELESAI', 'BATAL'] as $s)
                            <label class="flex cursor-pointer items-center gap-2 rounded-xl border px-3 py-2 text-sm
                                {{ $booking->status === $s ? 'border-cyan-400/60 bg-cyan-500/10 text-cyan-200' : 'border-slate-700 bg-ink-800 text-slate-300' }}">
                                <input type="radio" name="status" value="{{ $s }}"
                                       {{ $booking->status === $s ? 'checked' : '' }} class="accent-cyan-400" required>
                                {{ $s }}
                            </label>
                        @endforeach
                    </div>

                    @error('status') <p class="mb-2 text-xs text-rose-400">{{ $message }}</p> @enderror

                    <x-button type="submit">Simpan Status</x-button>
                </form>
            </x-card>

            <x-card title="Pembayaran">
                @if($booking->pembayaran)
                    <dl class="space-y-2 text-sm">
                        <div class="flex justify-between gap-4">
                            <dt class="text-slate-400">Metode</dt>
                            <dd class="font-semibold text-white">{{ str_replace('_', ' ', $booking->pembayaran->metode) }}</dd>
                        </div>
                        <div class="flex justify-between gap-4">
                            <dt class="text-slate-400">Nominal</dt>
                            <dd class="font-semibold text-white">{{ rupiah($booking->pembayaran->nominal) }}</dd>
                        </div>
                        <div class="flex justify-between gap-4">
                            <dt class="text-slate-400">Status</dt>
                            <dd><x-badge :status="$booking->pembayaran->status" /></dd>
                        </div>
                        <div class="flex justify-between gap-4">
                            <dt class="text-slate-400">Waktu Bayar</dt>
                            <dd class="text-slate-300">{{ $booking->pembayaran->waktu?->format('d M Y H:i') ?? '—' }}</dd>
                        </div>
                    </dl>
                @else
                    <p class="text-sm text-slate-500">Data pembayaran tidak ditemukan.</p>
                @endif
            </x-card>

            <x-button href="{{ route('admin.bookings.index') }}" variant="ghost">← Kembali ke daftar booking</x-button>
        </div>
    </div>
@endsection
