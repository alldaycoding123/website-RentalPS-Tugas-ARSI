@extends('layouts.customer')

@section('title', 'Masuk Pelanggan')

@section('content')
    <div class="mx-auto max-w-5xl pt-2">
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">

            {{-- Kolom kiri: QR sesi + Session ID --}}
            <x-card title="Scan QR / No. HP" subtitle="Gunakan kamera untuk memindai, atau masukkan No. HP secara manual.">
                <div class="flex flex-col items-center gap-4 rounded-2xl border border-slate-800 bg-ink-800 p-6 text-center">
                    {{-- QR berisi token sesi --}}
                    <div id="qrcode-sesi" class="grid h-48 w-48 place-items-center rounded-xl bg-white p-3"></div>

                    <div class="w-full">
                        <p class="text-xs uppercase tracking-wider text-slate-500">Session ID</p>
                        <p class="mt-1 break-all font-mono text-sm text-cyan-300">{{ $sessionId }}</p>
                    </div>

                    <p class="text-xs text-slate-500">QR ini berisi token sesi <span class="font-mono text-slate-400">PSR:{{ $sessionId }}</span></p>

                    <form method="POST" action="{{ route('customer.demo') }}">
                        @csrf
                        <x-button type="submit" variant="secondary" class="w-full">👤 Akun Demo (081234567890)</x-button>
                    </form>
                </div>
            </x-card>

            {{-- Kolom kanan: form No. HP + kamera --}}
            <x-card title="Masuk dengan No. HP" subtitle="Belum punya akun? No. HP baru otomatis terdaftar.">
                <form method="POST" action="{{ route('customer.login.proses') }}" class="space-y-4">
                    @csrf

                    <div>
                        <label for="no_hp" class="mb-1.5 block text-sm font-medium text-slate-300">No. HP Indonesia</label>
                        <input id="no_hp" type="text" name="no_hp" value="{{ old('no_hp') }}" required autofocus
                               placeholder="081234567890"
                               class="w-full rounded-xl border border-slate-700 bg-ink-800 px-3 py-2.5 text-sm text-white placeholder-slate-500 focus:border-cyan-400 focus:outline-none focus:ring-1 focus:ring-cyan-400">
                        <p class="mt-1 text-xs text-slate-500">Format: 08xxxxxxxxxx atau +628xxxxxxxxxx (10–14 digit).</p>
                        @error('no_hp') <p class="mt-1 text-xs text-rose-400">{{ $message }}</p> @enderror
                    </div>

                    <x-button type="submit" class="w-full">Masuk / Daftar</x-button>
                </form>

                {{-- Pindai QR dengan kamera (html5-qrcode) --}}
                <div class="mt-5 border-t border-slate-800 pt-4">
                    <x-button id="btn-scan" variant="ghost" class="w-full">📷 Buka Kamera & Pindai QR</x-button>
                    <div id="reader" class="mt-3 overflow-hidden rounded-xl"></div>
                    <p id="scan-info" class="mt-2 text-xs text-slate-500"></p>
                </div>

                <a href="{{ route('beranda') }}" class="mt-5 block text-center text-sm text-slate-400 hover:text-cyan-300">← Kembali ke pilih akses</a>
            </x-card>
        </div>
    </div>
@endsection

@push('scripts')
{{-- qrcode.js untuk menampilkan QR sesi --}}
<script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
{{-- html5-qrcode untuk kamera --}}
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
    // Render QR sesi
    (function () {
        const el = document.getElementById('qrcode-sesi');
        if (window.QRCode) {
            new QRCode(el, {
                text: @json($qrPayload),
                width: 180,
                height: 180,
                colorDark: '#0a0e1a',
                colorLight: '#ffffff',
            });
        } else {
            el.innerHTML = '<span class="text-xs text-slate-500">QR tidak dapat dimuat (offline?)</span>';
        }
    })();

    // Scan QR via kamera
    (function () {
        const btn = document.getElementById('btn-scan');
        const info = document.getElementById('scan-info');
        let scanner = null;
        let aktif = false;

        btn.addEventListener('click', async () => {
            if (aktif) {
                try { await scanner.stop(); } catch (e) {}
                scanner.clear();
                scanner = null;
                aktif = false;
                btn.textContent = '📷 Buka Kamera & Pindai QR';
                return;
            }

            if (typeof Html5Qrcode === 'undefined') {
                info.textContent = 'Library kamera belum termuat. Periksa koneksi internet.';
                return;
            }

            try {
                scanner = new Html5Qrcode('reader');
                await scanner.start(
                    { facingMode: 'environment' },
                    { fps: 10, qrbox: { width: 220, height: 220 } },
                    (decodedText) => {
                        // Hasil scan diarahkan ke form No. HP lalu disubmit
                        const input = document.getElementById('no_hp');
                        input.value = decodedText.trim();
                        info.textContent = 'Hasil scan: ' + decodedText;
                        scanner.stop().then(() => {
                            aktif = false;
                            btn.textContent = '📷 Buka Kamera & Pindai QR';
                            input.form.submit();
                        }).catch(() => input.form.submit());
                    },
                    () => {}
                );
                aktif = true;
                btn.textContent = '⏹ Berhenti Kamera';
                info.textContent = 'Arahkan kamera ke QR (kamera hanya jalan di HTTPS / localhost / 127.0.0.1).';
            } catch (err) {
                info.textContent = 'Gagal membuka kamera: ' + err;
            }
        });
    })();
</script>
@endpush
