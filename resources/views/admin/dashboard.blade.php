@extends('layouts.staff')

@section('title', 'Dashboard Admin')
@section('subtitle', 'Ringkasan realtime · ' . tanggal_id($hariIni))

@section('content')
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-stat label="Booking Hari Ini" :value="$bookingHariIni" icon="📋" />
        <x-stat label="Unit Aktif" :value="$unitAktif" icon="🎮" />
        <x-stat label="Pendapatan Hari Ini" :value="rupiah($pendapatanHariIni)" icon="💰" />
        <x-stat label="Pelanggan Terdaftar" :value="$totalPelanggan" icon="👥" />
    </div>

    <div class="mt-6 grid grid-cols-1 gap-5 xl:grid-cols-3">
        {{-- Tabel booking terbaru --}}
        <x-card class="xl:col-span-2" title="Booking Terbaru" subtitle="Status konfirmasi & pembayaran">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="text-xs uppercase text-slate-500">
                        <tr class="border-b border-slate-800">
                            <th class="py-2 pr-3">Kode</th>
                            <th class="py-2 pr-3">Tanggal</th>
                            <th class="py-2 pr-3">Unit</th>
                            <th class="py-2 pr-3">Pelanggan</th>
                            <th class="py-2 pr-3">Booking</th>
                            <th class="py-2">Bayar</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($terbaru as $b)
                            <tr class="border-b border-slate-800/60">
                                <td class="py-2.5 pr-3 font-mono text-cyan-300">{{ $b->kode }}</td>
                                <td class="py-2.5 pr-3 text-slate-300">{{ $b->tgl_main->format('d/m/Y') }}</td>
                                <td class="py-2.5 pr-3 text-slate-300">{{ $b->unit->kode_unit }}</td>
                                <td class="py-2.5 pr-3 text-white">{{ $b->pelanggan->nama }}</td>
                                <td class="py-2.5 pr-3"><x-badge :status="$b->status" /></td>
                                <td class="py-2.5"><x-badge :status="$b->pembayaran?->status ?? 'BELUM_BAYAR'" /></td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="py-8 text-center text-slate-500">Belum ada booking.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-card>

        {{-- Grid status unit --}}
        <x-card title="Status Unit" subtitle="Auto-refresh tiap 15 detik">
            <div id="grid-unit" class="grid grid-cols-2 gap-3">
                @foreach($units as $unit)
                    <div class="rounded-xl border border-slate-800 bg-ink-800 p-3" data-unit="{{ $unit->id_unit }}">
                        <p class="text-xs font-semibold text-slate-400">{{ $unit->kode_unit }}</p>
                        <p class="truncate text-sm font-semibold text-white">{{ $unit->nama }}</p>
                        <div class="mt-2 badge-slot">
                            <x-badge :status="$unit->status" />
                        </div>
                    </div>
                @endforeach
            </div>
        </x-card>
    </div>

    <div class="mt-6 flex flex-wrap gap-3">
        <x-button href="{{ route('admin.bookings.create') }}">+ Booking Walk-in</x-button>
        <x-button href="{{ route('admin.units.create') }}" variant="secondary">+ Unit PS</x-button>
        <x-button href="{{ route('admin.laporan.index') }}" variant="secondary">Buka Laporan</x-button>
    </div>
@endsection

@push('scripts')
<script>
    // Auto-refresh grid status unit
    setInterval(async () => {
        try {
            const res = await fetch('{{ route('api.units.status') }}', { headers: { 'Accept': 'application/json' } });
            if (!res.ok) return;
            const data = await res.json();

            const warna = {
                TERSEDIA: 'border-emerald-500/40 bg-emerald-500/10 text-emerald-300',
                SUDAH_DIBOOKING: 'border-orange-500/40 bg-orange-500/10 text-orange-300',
                MAINTENANCE: 'border-slate-500/40 bg-slate-500/10 text-slate-300',
                DIGUNAKAN: 'border-orange-500/40 bg-orange-500/10 text-orange-300',
            };
            const teks = {
                TERSEDIA: 'Tersedia', SUDAH_DIBOOKING: 'Dibooking',
                MAINTENANCE: 'Maintenance', DIGUNAKAN: 'Digunakan',
            };

            data.forEach((u) => {
                const el = document.querySelector('[data-unit="' + u.id_unit + '"] .badge-slot');
                if (!el) return;
                el.innerHTML = '<span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold ' +
                    (warna[u.badge] || warna.TERSEDIA) + '">' + (teks[u.badge] || u.badge) + '</span>';
            });
        } catch (e) {}
    }, 15000);
</script>
@endpush
