@extends('layouts.customer')

@section('title', 'Pilih Jadwal')

@section('content')
    {{-- Header ringkas unit --}}
    <div class="flex flex-wrap items-center justify-between gap-4 rounded-2xl border border-slate-800 bg-[#111827] p-5">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wider text-cyan-400">{{ $unit->kode_unit }}</p>
            <h1 class="mt-1 text-xl font-extrabold text-white">{{ $unit->nama }}</h1>
            <p class="mt-1 text-sm text-slate-400">{{ $unit->ruang }} · {{ $unit->tv }}</p>
        </div>
        <div class="text-right">
            <p class="text-lg font-extrabold text-white">{{ rupiah($unit->harga_jam) }}<span class="text-xs font-medium text-slate-400"> /jam</span></p>
            <p class="text-xs text-slate-500">Slot 09:00 – 22:00</p>
        </div>
    </div>

    <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-2">

        {{-- Kolom kiri: tanggal + grid slot --}}
        <x-card title="Pilih Tanggal & Jam" subtitle="Pilih slot berurutan (kontigu), minimal 1 jam, maksimal 6 jam.">
            <div class="mb-4">
                <label for="tanggal" class="mb-1.5 block text-sm font-medium text-slate-300">Tanggal main</label>
                <input type="date" id="tanggal" name="tanggal" value="{{ $tanggal }}"
                       min="{{ now('Asia/Makassar')->toDateString() }}"
                       class="w-full rounded-xl border border-slate-700 bg-ink-800 px-3 py-2.5 text-sm text-white focus:border-cyan-400 focus:outline-none focus:ring-1 focus:ring-cyan-400">
            </div>

            <div id="grid-slot" class="grid grid-cols-3 gap-2 sm:grid-cols-4">
                {{-- Diisi oleh JS dari endpoint JSON --}}
                <div class="col-span-full py-6 text-center text-sm text-slate-500">Memuat slot…</div>
            </div>

            <p id="peringatan-slot" class="mt-3 hidden rounded-xl border border-amber-500/40 bg-amber-500/10 px-3 py-2 text-sm text-amber-200"></p>

            {{-- Legenda --}}
            <div class="mt-4 flex flex-wrap gap-4 text-xs text-slate-400">
                <span class="flex items-center gap-2"><span class="h-3 w-3 rounded border border-slate-600 bg-transparent"></span> Tersedia</span>
                <span class="flex items-center gap-2"><span class="h-3 w-3 rounded border border-cyan-400 bg-cyan-500/30"></span> Dipilih</span>
                <span class="flex items-center gap-2"><span class="h-3 w-3 rounded border border-rose-500 bg-rose-500/20"></span> Terpakai / Lewat</span>
            </div>
        </x-card>

        {{-- Kolom kanan: data pemesan + rincian biaya --}}
        <div class="space-y-6">
            <x-card title="Data Pemesan">
                <form id="form-jadwal" method="POST" action="{{ route('customer.units.jadwal.simpan', $unit) }}">
                    @csrf
                    <input type="hidden" name="tgl_main" id="input-tanggal" value="{{ $tanggal }}">
                    <div id="input-slot-wrap"></div>

                    <div class="space-y-4">
                        <div>
                            <label for="nama" class="mb-1.5 block text-sm font-medium text-slate-300">Nama Lengkap</label>
                            <input id="nama" type="text" name="nama" value="{{ old('nama', $pelanggan->nama ?? '') }}" required maxlength="100"
                                   class="w-full rounded-xl border border-slate-700 bg-ink-800 px-3 py-2.5 text-sm text-white focus:border-cyan-400 focus:outline-none focus:ring-1 focus:ring-cyan-400">
                            @error('nama') <p class="mt-1 text-xs text-rose-400">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="no_hp" class="mb-1.5 block text-sm font-medium text-slate-300">No. HP</label>
                            <input id="no_hp" type="text" name="no_hp" value="{{ old('no_hp', $pelanggan->no_hp ?? '') }}" required
                                   class="w-full rounded-xl border border-slate-700 bg-ink-800 px-3 py-2.5 text-sm text-white focus:border-cyan-400 focus:outline-none focus:ring-1 focus:ring-cyan-400">
                            @error('no_hp') <p class="mt-1 text-xs text-rose-400">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </form>
            </x-card>

            {{-- Rincian biaya (dihitung realtime di sisi klien, hanya tampilan) --}}
            <x-card title="Rincian Biaya">
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-slate-400">Durasi sewa</dt>
                        <dd class="font-semibold text-white" id="rincian-durasi">0 jam</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-slate-400">Harga per jam</dt>
                        <dd class="font-semibold text-white">{{ rupiah($unit->harga_jam) }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-slate-400">Jam sewa</dt>
                        <dd class="font-semibold text-white" id="rincian-jam">—</dd>
                    </div>
                    <div class="mt-3 flex items-center justify-between border-t border-slate-800 pt-3">
                        <dt class="text-base font-semibold text-white">Total</dt>
                        <dd class="text-xl font-extrabold text-cyan-300" id="rincian-total">Rp 0</dd>
                    </div>
                </dl>
            </x-card>
        </div>
    </div>

    {{-- Footer sticky: total + tombol lanjut --}}
    <div class="fixed inset-x-0 bottom-0 z-30 border-t border-slate-800 bg-ink-900/95 backdrop-blur">
        <div class="mx-auto flex max-w-5xl items-center justify-between gap-4 px-4 py-3 sm:px-6">
            <div>
                <p class="text-xs text-slate-400">Total pembayaran</p>
                <p class="text-lg font-extrabold text-white" id="footer-total">Rp 0</p>
            </div>
            <x-button type="submit" form="form-jadwal" id="btn-lanjut" class="px-6" disabled>Lanjut Bayar →</x-button>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    const HARGA = {{ (float) $unit->harga_jam }};
    const UNIT_ID = {{ $unit->id_unit }};
    const URL_SLOT = @json(route('api.units.slots', $unit));
    const MAX_DURASI = 6;
    let slots = [];      // data dari server
    let terpilih = [];   // jam terpilih (urut)

    const grid = document.getElementById('grid-slot');
    const peringatan = document.getElementById('peringatan-slot');
    const btnLanjut = document.getElementById('btn-lanjut');

    function formatRp(n) {
        return 'Rp ' + n.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }

    function jamSelesai(jam) {
        return String(parseInt(jam.slice(0, 2), 10) + 1).padStart(2, '0') + ':00';
    }

    // Cek slot terpilih harus berurutan (kontigu)
    function kontigu(daftar) {
        const jam = daftar.map((j) => parseInt(j.slice(0, 2), 10)).sort((a, b) => a - b);
        for (let i = 1; i < jam.length; i++) {
            if (jam[i] !== jam[i - 1] + 1) return false;
        }
        return true;
    }

    function render() {
        grid.innerHTML = '';

        if (!slots.length) {
            grid.innerHTML = '<div class="col-span-full py-6 text-center text-sm text-slate-500">Slot tidak tersedia.</div>';
            return;
        }

        slots.forEach((s) => {
            const dipilih = terpilih.includes(s.jam);
            const nonaktif = s.status !== 'tersedia'; // terpakai atau lewat

            const btn = document.createElement('button');
            btn.type = 'button';
            btn.textContent = s.jam;
            btn.dataset.jam = s.jam;

            let kelas = 'rounded-xl border px-2 py-3 text-sm font-mono font-semibold transition ';

            if (nonaktif) {
                // Terpakai (merah, disabled) atau sudah lewat
                kelas += s.status === 'terpakai'
                    ? 'border-rose-500 bg-rose-500/15 text-rose-300 cursor-not-allowed line-through'
                    : 'border-slate-700/60 bg-slate-800/40 text-slate-600 cursor-not-allowed';
                btn.disabled = true;
            } else if (dipilih) {
                kelas += 'border-cyan-400 bg-cyan-500/30 text-cyan-200 shadow-lg shadow-cyan-500/20';
            } else {
                kelas += 'border-slate-600 bg-transparent text-slate-300 hover:border-slate-400 hover:bg-slate-800';
            }

            btn.className = kelas;

            if (!nonaktif) {
                btn.addEventListener('click', () => toggle(s.jam));
            }

            grid.appendChild(btn);
        });

        updateRincian();
    }

    function toggle(jam) {
        if (terpilih.includes(jam)) {
            terpilih = terpilih.filter((j) => j !== jam);
        } else {
            terpilih.push(jam);
        }

        terpilih.sort((a, b) => a.localeCompare(b));

        if (terpilih.length && !kontigu(terpilih)) {
            showPeringatan('Slot yang dipilih harus BERURUTAN (contigu). Pilih jam yang bersebelahan.');
        } else if (terpilih.length > MAX_DURASI) {
            showPeringatan('Durasi maksimal 6 jam.');
        } else {
            hidePeringatan();
        }

        render();
    }

    function showPeringatan(pesan) {
        peringatan.textContent = '⚠️ ' + pesan;
        peringatan.classList.remove('hidden');
    }

    function hidePeringatan() {
        peringatan.classList.add('hidden');
    }

    function updateRincian() {
        const durasi = terpilih.length;
        const total = durasi * HARGA;

        const valid = durasi > 0 && kontigu(terpilih) && durasi <= MAX_DURASI;

        document.getElementById('rincian-durasi').textContent = durasi + ' jam';
        document.getElementById('rincian-jam').textContent = durasi
            ? terpilih[0] + ' - ' + jamSelesai(terpilih[durasi - 1])
            : '—';
        document.getElementById('rincian-total').textContent = formatRp(total);
        document.getElementById('footer-total').textContent = formatRp(total);

        if (valid) {
            btnLanjut.disabled = false;
            btnLanjut.classList.remove('opacity-50', 'cursor-not-allowed');
            hidePeringatan();
        } else {
            btnLanjut.disabled = true;
            btnLanjut.classList.add('opacity-50', 'cursor-not-allowed');
        }

        // Slot terpilih dikirim sebagai input tersembunyi
        const wrap = document.getElementById('input-slot-wrap');
        wrap.innerHTML = '';
        terpilih.forEach((jam) => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'slot_mulai[]';
            input.value = jam;
            wrap.appendChild(input);
        });
    }

    async function muatSlot() {
        const tanggal = document.getElementById('tanggal').value;
        document.getElementById('input-tanggal').value = tanggal;
        terpilih = [];

        grid.innerHTML = '<div class="col-span-full py-6 text-center text-sm text-slate-500">Memuat slot…</div>';

        try {
            const res = await fetch(URL_SLOT + '?tanggal=' + encodeURIComponent(tanggal), {
                headers: { 'Accept': 'application/json' },
            });
            const data = await res.json();

            if (!res.ok) {
                grid.innerHTML = '<div class="col-span-full py-6 text-center text-sm text-rose-400">' + (data.error || 'Gagal memuat slot.') + '</div>';
                slots = [];
                updateRincian();
                return;
            }

            slots = data.slots || [];
            render();
        } catch (e) {
            grid.innerHTML = '<div class="col-span-full py-6 text-center text-sm text-rose-400">Gagal terhubung ke server.</div>';
        }
    }

    document.getElementById('tanggal').addEventListener('change', muatSlot);

    // Validasi sebelum submit
    document.getElementById('form-jadwal').addEventListener('submit', (e) => {
        if (!terpilih.length || !kontigu(terpilih) || terpilih.length > MAX_DURASI) {
            e.preventDefault();
            showPeringatan('Pilih slot jam yang berurutan (maksimal 6 jam) terlebih dahulu.');
        }
    });

    muatSlot();
</script>
@endpush
