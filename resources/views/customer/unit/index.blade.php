@extends('layouts.customer')

@section('title', 'Pilih Unit PS')

@section('content')
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-2xl font-extrabold text-white">Pilih Unit PlayStation</h1>
            <p class="mt-1 text-sm text-slate-400">Status diperbarui otomatis setiap 15 detik.</p>
        </div>

        {{-- Filter tipe tanpa reload --}}
        <div class="flex gap-2" id="filter-tipe">
            @foreach(['SEMUA' => 'Semua', 'PS5' => 'PS5', 'PS4' => 'PS4'] as $tipe => $label)
                <button type="button" data-tipe="{{ $tipe }}"
                        class="filter-btn rounded-xl border px-3.5 py-2 text-sm font-semibold transition
                        {{ ($filter ?? 'SEMUA') === $tipe ? 'border-cyan-400/50 bg-cyan-500/15 text-cyan-300' : 'border-slate-700 text-slate-400 hover:text-white' }}">
                    {{ $label }}
                </button>
            @endforeach
        </div>
    </div>

    {{-- Grid unit: 1 kolom mobile, 3 kolom desktop --}}
    <div class="mt-6 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
        @forelse($units as $unit)
            @php $badge = $statusHariIni[$unit->id_unit] ?? $unit->status; @endphp
            <div class="unit-card flex flex-col rounded-2xl border border-slate-800 bg-[#111827] p-5 transition hover:border-slate-700"
                 data-tipe="{{ $unit->tipe }}" data-id="{{ $unit->id_unit }}">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-cyan-400">{{ $unit->kode_unit }}</p>
                        <h2 class="mt-1 text-lg font-bold text-white">{{ $unit->nama }}</h2>
                    </div>
                    <span class="badge-unit">
                        <x-badge :status="$badge" />
                    </span>
                </div>

                <dl class="mt-4 space-y-1.5 text-sm text-slate-400">
                    <div class="flex justify-between gap-3">
                        <dt>Ruang</dt>
                        <dd class="text-right text-slate-200">{{ $unit->ruang }}</dd>
                    </div>
                    <div class="flex justify-between gap-3">
                        <dt>TV / Spek</dt>
                        <dd class="text-right text-slate-200">{{ $unit->tv }}</dd>
                    </div>
                    <div class="flex justify-between gap-3">
                        <dt>Tipe</dt>
                        <dd class="text-right text-slate-200">{{ $unit->tipe }}</dd>
                    </div>
                </dl>

                <div class="mt-4 flex items-center justify-between border-t border-slate-800 pt-4">
                    <p class="text-lg font-extrabold text-white">{{ rupiah($unit->harga_jam) }}<span class="text-xs font-medium text-slate-400"> /jam</span></p>

                    <a href="{{ route('customer.units.jadwal', $unit) }}"
                       class="btn-book rounded-xl bg-gradient-to-r from-cyan-500 to-indigo-500 px-4 py-2 text-sm font-semibold text-white shadow-lg shadow-cyan-500/20 transition hover:from-cyan-400 hover:to-indigo-400 {{ $badge !== 'TERSEDIA' ? 'opacity-50 pointer-events-none' : '' }}"
                       data-state="{{ $badge }}">
                        Booking
                    </a>
                </div>
            </div>
        @empty
            <div class="col-span-full rounded-2xl border border-dashed border-slate-700 p-10 text-center text-slate-400">
                Belum ada unit PS. Hubungi admin.
            </div>
        @endforelse
    </div>

    {{-- Legenda --}}
    <div class="mt-6 flex flex-wrap gap-4 text-xs text-slate-400">
        <span class="flex items-center gap-2"><span class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span> Tersedia</span>
        <span class="flex items-center gap-2"><span class="h-2.5 w-2.5 rounded-full bg-orange-500"></span> Sudah Dibooking</span>
        <span class="flex items-center gap-2"><span class="h-2.5 w-2.5 rounded-full bg-slate-500"></span> Maintenance</span>
    </div>
@endsection

@push('scripts')
<script>
    // Filter tipe unit tanpa reload
    (function () {
        const tombol = document.querySelectorAll('.filter-btn');
        const kartu = document.querySelectorAll('.unit-card');

        tombol.forEach((btn) => {
            btn.addEventListener('click', () => {
                const tipe = btn.dataset.tipe;
                tombol.forEach((b) => {
                    b.className = 'filter-btn rounded-xl border px-3.5 py-2 text-sm font-semibold transition border-slate-700 text-slate-400 hover:text-white';
                });
                btn.className = 'filter-btn rounded-xl border px-3.5 py-2 text-sm font-semibold transition border-cyan-400/50 bg-cyan-500/15 text-cyan-300';

                kartu.forEach((k) => {
                    k.style.display = (tipe === 'SEMUA' || k.dataset.tipe === tipe) ? '' : 'none';
                });
            });
        });
    })();

    // Auto-refresh status tiap 15 detik
    (function () {
        const warna = {
            TERSEDIA: ['border-emerald-500/40 bg-emerald-500/15 text-emerald-300', 'Tersedia'],
            SUDAH_DIBOOKING: ['border-orange-500/40 bg-orange-500/15 text-orange-300', 'Sudah Dibooking'],
            MAINTENANCE: ['border-slate-500/40 bg-slate-500/15 text-slate-300', 'Maintenance'],
        };

        async function refresh() {
            try {
                const res = await fetch('{{ route('api.units.status') }}', {
                    headers: { 'Accept': 'application/json' },
                });
                if (!res.ok) return;
                const data = await res.json();

                data.forEach((u) => {
                    const kartu = document.querySelector('.unit-card[data-id="' + u.id_unit + '"]');
                    if (!kartu) return;

                    const badge = kartu.querySelector('.badge-unit');
                    const style = warna[u.badge] || warna.TERSEDIA;
                    badge.innerHTML = '<span class="inline-flex items-center gap-1 rounded-full border px-2.5 py-0.5 text-xs font-semibold ' + style[0] + '">' + style[1] + '</span>';

                    // Tombol booking hanya aktif bila Tersedia
                    const tombol = kartu.querySelector('.btn-book');
                    if (u.badge === 'TERSEDIA') {
                        tombol.classList.remove('opacity-50', 'pointer-events-none');
                        tombol.removeAttribute('aria-disabled');
                    } else {
                        tombol.classList.add('opacity-50', 'pointer-events-none');
                        tombol.setAttribute('aria-disabled', 'true');
                    }
                });
            } catch (e) { /* abaikan gangguan jaringan */ }
        }

        refresh();
        setInterval(refresh, 15000);
    })();
</script>
@endpush
