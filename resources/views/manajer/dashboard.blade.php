@extends('layouts.staff')

@section('title', 'Dashboard Manajer')
@section('subtitle', 'Ringkasan kinerja & grafik pendapatan')

@section('content')
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-stat label="Total Pendapatan" :value="rupiah($totalPendapatan)" icon="💰" />
        <x-stat label="Total Booking Valid" :value="$totalBookingValid" icon="📋" />
        <x-stat label="Transaksi Hari Ini" :value="$transaksiHariIni" icon="🧾" />
        <x-stat label="Unit Beroperasi" :value="$unitBeroperasi" icon="🎮" />
    </div>

    <div class="mt-6 grid grid-cols-1 gap-5 xl:grid-cols-3">
        {{-- Grafik pendapatan 7 hari (Chart.js) --}}
        <x-card class="xl:col-span-2" title="Pendapatan 7 Hari Terakhir" subtitle="Data dari endpoint JSON">
            <div class="relative h-72">
                <canvas id="grafik-pendapatan"></canvas>
            </div>
        </x-card>

        <div class="space-y-5">
            <x-card title="Menu Laporan">
                <div class="grid grid-cols-1 gap-3">
                    <x-button href="{{ route('manajer.laporan.pendapatan') }}" variant="secondary" class="w-full">💰 Laporan Pendapatan</x-button>
                    <x-button href="{{ route('manajer.laporan.performa') }}" variant="secondary" class="w-full">🎮 Performa Unit</x-button>
                    <x-button href="{{ route('manajer.laporan.tren') }}" variant="secondary" class="w-full">📈 Tren & Analitik</x-button>
                </div>
            </x-card>

            <x-card title="Catatan" subtitle="Manajer hanya memiliki akses laporan & analitik.">
                <p class="text-sm text-slate-400">
                    Data dihitung dari pembayaran berstatus <span class="font-semibold text-emerald-300">LUNAS</span>
                    dan booking selain <span class="font-semibold text-rose-300">BATAL</span>.
                </p>
            </x-card>
        </div>
    </div>
@endsection

@push('scripts')
{{-- Chart.js untuk grafik pendapatan --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
    (function () {
        if (typeof Chart === 'undefined') return;

        const ctx = document.getElementById('grafik-pendapatan');
        // Data awal dari server, lalu disegarkan via endpoint JSON
        const labels = @json($grafik['labels']);
        const data = @json($grafik['data']);

        const gradient = ctx.getContext('2d').createLinearGradient(0, 0, 0, 280);
        gradient.addColorStop(0, 'rgba(34, 211, 238, 0.35)');
        gradient.addColorStop(1, 'rgba(34, 211, 238, 0)');

        const chart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Pendapatan (Rp)',
                    data: data,
                    borderColor: '#22d3ee',
                    backgroundColor: gradient,
                    fill: true,
                    tension: 0.35,
                    pointBackgroundColor: '#6366f1',
                    pointRadius: 4,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { labels: { color: '#cbd5e1' } },
                    tooltip: {
                        callbacks: {
                            label: (c) => 'Rp ' + c.parsed.y.toLocaleString('id-ID'),
                        },
                    },
                },
                scales: {
                    x: { ticks: { color: '#94a3b8' }, grid: { color: 'rgba(148,163,184,0.1)' } },
                    y: {
                        ticks: { color: '#94a3b8', callback: (v) => 'Rp ' + v.toLocaleString('id-ID') },
                        grid: { color: 'rgba(148,163,184,0.1)' },
                    },
                },
            },
        });

        // Refresh data tiap 60 detik
        setInterval(async () => {
            try {
                const res = await fetch('{{ route('api.chart.pendapatan') }}', { headers: { 'Accept': 'application/json' } });
                if (!res.ok) return;
                const json = await res.json();
                chart.data.labels = json.labels;
                chart.data.datasets[0].data = json.data;
                chart.update();
            } catch (e) {}
        }, 60000);
    })();
</script>
@endpush
