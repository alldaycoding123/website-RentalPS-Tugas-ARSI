@extends('layouts.staff')

@section('title', 'Check-in Booking')
@section('subtitle', 'Masukkan kode booking, scan QR, atau cari No. HP')

@section('content')
    <x-card title="Cari Booking" subtitle="Kode booking (BK-XXXXXX), hasil scan QR, atau No. HP pelanggan.">
        <form method="GET" action="{{ route('kasir.checkin') }}" class="flex flex-col gap-3 sm:flex-row">
            <input type="text" name="q" value="{{ $q }}" placeholder="BK-XXXXXX atau 081234567890"
                   class="flex-1 rounded-xl border border-slate-700 bg-ink-800 px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:border-cyan-400 focus:outline-none focus:ring-1 focus:ring-cyan-400">
            <x-button type="submit" class="sm:px-6">Cari</x-button>
        </form>

        <div class="mt-4 border-t border-slate-800 pt-4">
            <x-button id="btn-scan-checkin" variant="ghost">📷 Scan QR Kode Booking</x-button>
            <div id="reader-checkin" class="mt-3 overflow-hidden rounded-xl"></div>
            <p id="info-scan" class="mt-2 text-xs text-slate-500"></p>
        </div>
    </x-card>

    @if($pesan)
        <div class="mt-5 rounded-xl border border-rose-500/40 bg-rose-500/10 px-4 py-3 text-sm text-rose-200">
            ⚠️ {{ $pesan }}
        </div>
    @endif

    @if($hasil)
        <x-card class="mt-5" title="Detail Booking">
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between gap-4">
                        <dt class="text-slate-400">Kode</dt>
                        <dd class="font-mono font-semibold text-cyan-300">{{ $hasil->kode }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-slate-400">Pelanggan</dt>
                        <dd class="font-semibold text-white">{{ $hasil->pelanggan->nama }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-slate-400">No. HP</dt>
                        <dd class="font-semibold text-white">{{ $hasil->pelanggan->no_hp }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-slate-400">Unit</dt>
                        <dd class="font-semibold text-white">{{ $hasil->unit->nama }} ({{ $hasil->unit->kode_unit }})</dd>
                    </div>
                </dl>

                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between gap-4">
                        <dt class="text-slate-400">Tanggal</dt>
                        <dd class="font-semibold text-white">{{ $hasil->tgl_main->translatedFormat('d M Y') }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-slate-400">Jam</dt>
                        <dd class="font-mono font-semibold text-white">{{ jam($hasil->mulai) }} - {{ jam($hasil->selesai) }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-slate-400">Total</dt>
                        <dd class="font-semibold text-cyan-300">{{ rupiah($hasil->total) }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-slate-400">Status</dt>
                        <dd class="flex gap-2">
                            <x-badge :status="$hasil->status" />
                            @if($hasil->pembayaran)
                                <x-badge :status="$hasil->pembayaran->status" />
                            @endif
                        </dd>
                    </div>
                </dl>
            </div>

            {{-- Tombol check-in aktif sesuai validasi server --}}
            @php
                $hariIni = now('Asia/Makassar')->toDateString();
                $bisaCheckin = $hasil->tgl_main->toDateString() === $hariIni
                    && ! in_array($hasil->status, ['BATAL', 'CHECKIN', 'SELESAI'], true);
            @endphp

            <div class="mt-5 border-t border-slate-800 pt-4">
                @if($bisaCheckin)
                    <form method="POST" action="{{ route('kasir.checkin.proses', $hasil) }}">
                        @csrf
                        <x-button type="submit" class="w-full sm:w-auto">✅ Check-in Sekarang</x-button>
                    </form>
                @else
                    <p class="text-sm text-slate-400">
                        @if($hasil->status === 'CHECKIN')
                            Booking ini sudah check-in.
                        @elseif($hasil->status === 'BATAL')
                            Booking ini dibatalkan.
                        @elseif($hasil->status === 'SELESAI')
                            Booking ini sudah selesai.
                        @else
                            Check-in hanya dapat diproses pada tanggal main ({{ tanggal_id($hasil->tgl_main) }}).
                        @endif
                    </p>
                @endif
            </div>
        </x-card>
    @endif
@endsection

@push('scripts')
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
    (function () {
        const btn = document.getElementById('btn-scan-checkin');
        const info = document.getElementById('info-scan');
        let scanner = null;
        let aktif = false;

        btn.addEventListener('click', async () => {
            if (aktif) {
                try { await scanner.stop(); } catch (e) {}
                scanner.clear(); scanner = null; aktif = false;
                btn.textContent = '📷 Scan QR Kode Booking';
                return;
            }
            if (typeof Html5Qrcode === 'undefined') { info.textContent = 'Library kamera belum termuat.'; return; }

            try {
                scanner = new Html5Qrcode('reader-checkin');
                await scanner.start(
                    { facingMode: 'environment' },
                    { fps: 10, qrbox: { width: 240, height: 240 } },
                    (text) => { window.location.href = @json(route('kasir.checkin')) + '?q=' + encodeURIComponent(text.trim()); },
                    () => {}
                );
                aktif = true;
                btn.textContent = '⏹ Berhenti Kamera';
                info.textContent = 'Arahkan kamera ke QR kode booking.';
            } catch (err) { info.textContent = 'Gagal membuka kamera: ' + err; }
        });
    })();
</script>
@endpush
