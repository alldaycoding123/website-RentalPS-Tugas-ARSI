@extends('layouts.customer')

@section('title', 'Konfirmasi Pembayaran')

@section('content')
    <h1 class="text-2xl font-extrabold text-white">Konfirmasi Pembayaran</h1>
    <p class="mt-1 text-sm text-slate-400">Periksa rincian booking Anda sebelum melanjutkan.</p>

    <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-2">

        {{-- Kolom kiri: ringkasan booking --}}
        <x-card title="Ringkasan Booking">
            <dl class="space-y-3 text-sm">
                <div class="flex justify-between gap-4">
                    <dt class="text-slate-400">Nama pemesan</dt>
                    <dd class="text-right font-semibold text-white">{{ $draft['nama'] }}</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-slate-400">No. HP</dt>
                    <dd class="text-right font-semibold text-white">{{ $draft['no_hp'] }}</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-slate-400">Unit</dt>
                    <dd class="text-right font-semibold text-white">{{ $unit->nama }} ({{ $unit->kode_unit }})</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-slate-400">Ruang</dt>
                    <dd class="text-right font-semibold text-white">{{ $unit->ruang }}</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-slate-400">Tanggal</dt>
                    <dd class="text-right font-semibold text-white">{{ tanggal_id($draft['tgl_main']) }}</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-slate-400">Jam main</dt>
                    <dd class="text-right font-mono font-semibold text-cyan-300">{{ jam($draft['mulai']) }} - {{ jam($draft['selesai']) }}</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-slate-400">Durasi</dt>
                    <dd class="text-right font-semibold text-white">{{ $draft['durasi_jam'] }} jam</dd>
                </div>
                <div class="flex justify-between gap-4 border-t border-slate-800 pt-3">
                    <dt class="text-base font-semibold text-white">Total</dt>
                    <dd class="text-xl font-extrabold text-cyan-300">{{ rupiah($total) }}</dd>
                </div>
            </dl>

            <p class="mt-4 rounded-xl border border-slate-800 bg-ink-800 p-3 text-xs text-slate-400">
                Total dihitung dari harga unit × durasi sewa ({{ rupiah($unit->harga_jam) }} × {{ $draft['durasi_jam'] }} jam).
            </p>
        </x-card>

        {{-- Kolom kanan: metode pembayaran --}}
        <x-card title="Metode Pembayaran">
            <form method="POST" action="{{ route('customer.pembayaran.store') }}">
                @csrf

                <div class="space-y-3">
                    @php
                        $metode = [
                            'TRANSFER_BANK' => ['label' => 'Transfer Bank', 'icon' => '🏦', 'desc' => 'Transfer manual ke rekening PSRental (simulasi).'],
                            'E_WALLET' => ['label' => 'E-Wallet', 'icon' => '📱', 'desc' => 'GoPay / OVO / DANA (simulasi).'],
                            'BAYAR_DI_TEMPAT' => ['label' => 'Bayar di Tempat', 'icon' => '💵', 'desc' => 'Bayar tunai saat tiba di rental.'],
                        ];
                    @endphp

                    @foreach($metode as $key => $m)
                        <label class="flex cursor-pointer items-start gap-3 rounded-2xl border p-4 transition
                            {{ old('metode', 'TRANSFER_BANK') === $key ? 'border-cyan-400/60 bg-cyan-500/10' : 'border-slate-700 bg-ink-800 hover:border-slate-600' }}">
                            <input type="radio" name="metode" value="{{ $key }}"
                                   class="mt-1 accent-cyan-400"
                                   {{ old('metode', 'TRANSFER_BANK') === $key ? 'checked' : '' }}
                                   onchange="pilihMetode(this)">
                            <span class="flex-1">
                                <span class="flex items-center gap-2 text-sm font-semibold text-white">
                                    {{ $m['icon'] }} {{ $m['label'] }}
                                </span>
                                <span class="mt-0.5 block text-xs text-slate-400">{{ $m['desc'] }}</span>

                                @if(isset($metodeRekening[$key]))
                                    <span class="mt-2 block rounded-lg border border-slate-700 bg-ink-900 px-3 py-2 font-mono text-xs text-cyan-300" data-rek="{{ $key }}">
                                        {{ $metodeRekening[$key] }}
                                    </span>
                                @endif
                            </span>
                        </label>
                    @endforeach
                </div>

                @error('metode') <p class="mt-2 text-xs text-rose-400">{{ $message }}</p> @enderror

                <div class="mt-5 rounded-2xl border border-slate-800 bg-ink-800 p-4">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-slate-400">Total bayar</span>
                        <span class="text-xl font-extrabold text-cyan-300">{{ rupiah($total) }}</span>
                    </div>
                </div>

                <x-button type="submit" class="mt-4 w-full">Konfirmasi Booking ✓</x-button>

                <a href="{{ route('customer.units.jadwal', $unit) }}"
                   class="mt-3 block text-center text-sm text-slate-400 hover:text-cyan-300">← Ubah jadwal</a>
            </form>
        </x-card>
    </div>
@endsection

@push('scripts')
<script>
    // Tampilkan/sembunyikan contoh nomor rekening sesuai metode
    function pilihMetode(input) {
        document.querySelectorAll('input[name="metode"]').forEach((r) => {
            const label = r.closest('label');
            label.classList.remove('border-cyan-400/60', 'bg-cyan-500/10');
            label.classList.add('border-slate-700', 'bg-ink-800');
        });
        const label = input.closest('label');
        label.classList.add('border-cyan-400/60', 'bg-cyan-500/10');
        label.classList.remove('border-slate-700', 'bg-ink-800');
    }
</script>
@endpush
