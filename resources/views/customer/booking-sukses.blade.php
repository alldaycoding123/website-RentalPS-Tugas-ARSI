@extends('layouts.customer')

@section('title', 'Booking Berhasil')

@section('content')
    <div class="mx-auto max-w-xl">
        <div class="rounded-2xl border border-emerald-500/40 bg-[#111827] p-6 text-center shadow-xl shadow-black/30">
            <div class="mx-auto grid h-16 w-16 place-items-center rounded-full bg-emerald-500/15 text-3xl ring-1 ring-emerald-500/40">✓</div>

            <h1 class="mt-4 text-2xl font-extrabold text-white">Booking Berhasil!</h1>
            <p class="mt-1 text-sm text-slate-400">Simpan kode booking Anda.</p>

            {{-- Kode booking besar + QR --}}
            <p class="mt-5 font-mono text-3xl font-bold tracking-widest text-cyan-300">{{ $booking->kode }}</p>
            <div id="qrcode-booking" class="mx-auto mt-4 grid h-48 w-48 place-items-center rounded-xl bg-white p-3"></div>

            {{-- Rincian --}}
            <dl class="mt-6 space-y-2 text-left text-sm">
                <div class="flex justify-between gap-4">
                    <dt class="text-slate-400">Nama</dt>
                    <dd class="text-right font-semibold text-white">{{ $booking->pelanggan->nama }}</dd>
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
                    <dt class="text-slate-400">Durasi</dt>
                    <dd class="text-right font-semibold text-white">{{ $booking->durasi_jam }} jam</dd>
                </div>
                <div class="flex justify-between gap-4 border-t border-slate-800 pt-3">
                    <dt class="text-base font-semibold text-white">Total</dt>
                    <dd class="text-xl font-extrabold text-cyan-300">{{ rupiah($booking->total) }}</dd>
                </div>
            </dl>

            <div class="mt-5 rounded-xl border border-amber-500/40 bg-amber-500/10 px-4 py-3 text-sm text-amber-200">
                🎟️ Tunjukkan kode booking ini kepada kasir saat tiba.
            </div>

            <div class="mt-5 grid grid-cols-1 gap-3 sm:grid-cols-2">
                <x-button href="{{ route('customer.units') }}" variant="secondary" class="w-full">Kembali ke Beranda</x-button>
                <x-button href="{{ route('customer.riwayat') }}" class="w-full">Lihat Booking Saya</x-button>
            </div>
        </div>
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
                width: 180,
                height: 180,
                colorDark: '#0a0e1a',
                colorLight: '#ffffff',
            });
        } else {
            el.innerHTML = '<span class="text-xs text-slate-500">QR tidak dapat dimuat.</span>';
        }
    })();
</script>
@endpush
