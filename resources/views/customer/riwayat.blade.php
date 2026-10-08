@extends('layouts.customer')

@section('title', 'Booking Saya')

@section('content')
    <div class="flex items-end justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-white">Booking Saya</h1>
            <p class="mt-1 text-sm text-slate-400">Riwayat booking milik Anda (terbaru dulu).</p>
        </div>
        <x-button href="{{ route('customer.units') }}" variant="secondary">+ Booking Baru</x-button>
    </div>

    <div class="mt-6 space-y-4">
        @forelse($bookings as $booking)
            <div class="rounded-2xl border border-slate-800 bg-[#111827] p-5">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <p class="font-mono text-sm font-bold text-cyan-300">{{ $booking->kode }}</p>
                        <p class="mt-1 font-semibold text-white">{{ $booking->unit->nama }} · {{ $booking->unit->kode_unit }}</p>
                        <p class="text-sm text-slate-400">{{ $booking->unit->ruang }}</p>
                    </div>

                    <div class="flex flex-col items-end gap-1.5">
                        <x-badge :status="$booking->status" />
                        @if($booking->pembayaran)
                            <x-badge :status="$booking->pembayaran->status" />
                        @endif
                    </div>
                </div>

                <div class="mt-4 grid grid-cols-2 gap-3 text-sm sm:grid-cols-4">
                    <div>
                        <p class="text-xs text-slate-500">Tanggal</p>
                        <p class="font-semibold text-white">{{ $booking->tgl_main->translatedFormat('d M Y') }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-slate-500">Jam</p>
                        <p class="font-mono font-semibold text-white">{{ jam($booking->mulai) }} - {{ jam($booking->selesai) }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-slate-500">Durasi</p>
                        <p class="font-semibold text-white">{{ $booking->durasi_jam }} jam</p>
                    </div>
                    <div>
                        <p class="text-xs text-slate-500">Total</p>
                        <p class="font-semibold text-cyan-300">{{ rupiah($booking->total) }}</p>
                    </div>
                </div>

                <div class="mt-4 flex flex-wrap items-center gap-3 border-t border-slate-800 pt-4">
                    <x-button href="{{ route('customer.riwayat.show', $booking) }}" variant="secondary" class="text-xs">Lihat Detail & QR</x-button>

                    @if(in_array($booking->status, ['PENDING', 'CONFIRMED'], true))
                        <form method="POST" action="{{ route('customer.riwayat.batal', $booking) }}"
                              onsubmit="return confirm('Batalkan booking {{ $booking->kode }}?');">
                            @csrf
                            <x-button type="submit" variant="danger" class="text-xs">Batalkan</x-button>
                        </form>
                        <span class="text-xs text-slate-500">Pembatalan maks. 1 jam sebelum jadwal.</span>
                    @endif
                </div>
            </div>
        @empty
            <div class="rounded-2xl border border-dashed border-slate-700 p-12 text-center">
                <p class="text-4xl">🎮</p>
                <p class="mt-3 font-semibold text-white">Belum ada booking</p>
                <p class="mt-1 text-sm text-slate-400">Yuk, pilih unit dan jadwal mainmu sekarang.</p>
                <x-button href="{{ route('customer.units') }}" class="mt-4">Pilih Unit PS</x-button>
            </div>
        @endforelse
    </div>

    <div class="mt-6">
        {{ $bookings->links() }}
    </div>
@endsection
