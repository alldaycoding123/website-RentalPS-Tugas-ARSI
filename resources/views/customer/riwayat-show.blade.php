@extends('layouts.customer')

@section('title', 'Detail Booking')

@section('content')
    <div class="mx-auto max-w-xl">
        <a href="{{ route('customer.riwayat') }}" class="text-sm text-slate-400 hover:text-cyan-300">← Kembali ke Booking Saya</a>

        <x-card class="mt-4">
            <div class="text-center">
                <p class="font-mono text-2xl font-bold tracking-widest text-cyan-300">{{ $booking->kode }}</p>
                <div class="mt-3 flex justify-center gap-2">
                    <x-badge :status="$booking->status" />
                    @if($booking->pembayaran)
                        <x-badge :status="$booking->pembayaran->status" />
                    @endif
                </div>

                <div id="qrcode-booking" class="mx-auto mt-5 grid h-44 w-44 place-items-center rounded-xl bg-white p-3"></div>
            </div>

            <dl class="mt-6 space-y-2 text-sm">
                <div class="flex justify-between gap-4">
                    <dt class="text-slate-400">Nama</dt>
                    <dd class="text-right font-semibold text-white">{{ $booking->pelanggan->nama }}</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-slate-400">No. HP</dt>
                    <dd class="text-right font-semibold text-white">{{ $booking->pelanggan->no_hp }}</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-slate-400">Unit</dt>
                    <dd class="text-right font-semibold text-white">{{ $booking->unit->nama }} ({{ $booking->unit->kode_unit }})</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-slate-400">Tanggal</dt>
                    <dd class="text-right font-semibold text-white">{{ $booking->tgl_main->translatedFormat('d M Y') }}</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-slate-400">Jam</dt>
                    <dd class="text-right font-mono font-semibold text-cyan-300">{{ jam($booking->mulai) }} - {{ jam($booking->selesai) }}</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-slate-400">Metode</dt>
                    <dd class="text-right font-semibold text-white">{{ str_replace('_', ' ', $booking->pembayaran?->metode ?? '-') }}</dd>
                </div>
                <div class="flex justify-between gap-4 border-t border-slate-800 pt-3">
                    <dt class="text-base font-semibold text-white">Total</dt>
                    <dd class="text-xl font-extrabold text-cyan-300">{{ rupiah($booking->total) }}</dd>
                </div>
            </dl>

            <div class="mt-5 rounded-xl border border-amber-500/40 bg-amber-500/10 px-4 py-3 text-center text-sm text-amber-200">
                🎟️ Tunjukkan kode booking ini kepada kasir saat tiba.
            </div>

            @if(in_array($booking->status, ['PENDING', 'CONFIRMED'], true))
                <form method="POST" action="{{ route('customer.riwayat.batal', $booking) }}" class="mt-4"
                      onsubmit="return confirm('Batalkan booking {{ $booking->kode }}?');">
                    @csrf
                    <x-button type="submit" variant="danger" class="w-full">Batalkan Booking</x-button>
                </form>
            @endif
        </x-card>
    </div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
<script>
    (function () {
        const el = document.getElementById('qrcode-booking');
        if (window.QRCode) {
            new QRCode(el, {
                text: @json($booking->kode),
                width: 170,
                height: 170,
                colorDark: '#0a0e1a',
                colorLight: '#ffffff',
            });
        }
    })();
</script>
@endpush
