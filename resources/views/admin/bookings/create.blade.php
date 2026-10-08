@extends('layouts.staff')

@section('title', 'Booking Walk-in')
@section('subtitle', 'Tambah booking manual dari kasir/admin')

@section('content')
    <x-card class="max-w-3xl" title="Form Booking Walk-in" subtitle="Validasi anti double booking tetap diberlakukan.">
        <form method="POST" action="{{ route('admin.bookings.store') }}" id="form-walkin">
            @csrf

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-300">Unit PS</label>
                    <select name="id_unit" id="w-unit" required class="w-full rounded-xl border border-slate-700 bg-ink-800 px-3 py-2.5 text-sm text-white focus:border-cyan-400 focus:outline-none">
                        @foreach($units as $u)
                            <option value="{{ $u->id_unit }}"
                                    data-harga="{{ $u->harga_jam }}"
                                    {{ old('id_unit') == $u->id_unit ? 'selected' : '' }}
                                    {{ $u->status === 'MAINTENANCE' ? 'disabled' : '' }}>
                                {{ $u->kode_unit }} — {{ $u->nama }} ({{ rupiah($u->harga_jam) }}/jam){{ $u->status === 'MAINTENANCE' ? ' [MAINTENANCE]' : '' }}
                            </option>
                        @endforeach
                    </select>
                    @error('id_unit') <p class="mt-1 text-xs text-rose-400">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-300">Pelanggan</label>
                    <select name="id_pelanggan" required class="w-full rounded-xl border border-slate-700 bg-ink-800 px-3 py-2.5 text-sm text-white focus:border-cyan-400 focus:outline-none">
                        <option value="">— pilih pelanggan —</option>
                        @foreach($pelanggans as $p)
                            <option value="{{ $p->id_pelanggan }}" {{ old('id_pelanggan') == $p->id_pelanggan ? 'selected' : '' }}>
                                {{ $p->nama }} ({{ $p->no_hp }})
                            </option>
                        @endforeach
                    </select>
                    @error('id_pelanggan') <p class="mt-1 text-xs text-rose-400">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-300">Tanggal Main</label>
                    <input type="date" name="tgl_main" value="{{ old('tgl_main', now('Asia/Makassar')->toDateString()) }}"
                           min="{{ now('Asia/Makassar')->toDateString() }}" required
                           class="w-full rounded-xl border border-slate-700 bg-ink-800 px-3 py-2.5 text-sm text-white focus:border-cyan-400 focus:outline-none">
                    @error('tgl_main') <p class="mt-1 text-xs text-rose-400">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-300">Jam Mulai</label>
                        <select name="mulai" id="w-mulai" required class="w-full rounded-xl border border-slate-700 bg-ink-800 px-3 py-2.5 text-sm text-white focus:border-cyan-400 focus:outline-none">
                            @for($h = 9; $h <= 21; $h++)
                                <option value="{{ sprintf('%02d:00', $h) }}" {{ old('mulai') === sprintf('%02d:00', $h) ? 'selected' : '' }}>
                                    {{ sprintf('%02d:00', $h) }}
                                </option>
                            @endfor
                        </select>
                        @error('mulai') <p class="mt-1 text-xs text-rose-400">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-300">Durasi (jam)</label>
                        <select name="durasi_jam" id="w-durasi" required class="w-full rounded-xl border border-slate-700 bg-ink-800 px-3 py-2.5 text-sm text-white focus:border-cyan-400 focus:outline-none">
                            @for($d = 1; $d <= 6; $d++)
                                <option value="{{ $d }}" {{ old('durasi_jam', 1) == $d ? 'selected' : '' }}>{{ $d }} jam</option>
                            @endfor
                        </select>
                        @error('durasi_jam') <p class="mt-1 text-xs text-rose-400">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="sm:col-span-2">
                    <label class="mb-1.5 block text-sm font-medium text-slate-300">Metode Pembayaran</label>
                    <div class="grid grid-cols-1 gap-2 sm:grid-cols-3">
                        @foreach(['TRANSFER_BANK' => 'Transfer Bank', 'E_WALLET' => 'E-Wallet', 'BAYAR_DI_TEMPAT' => 'Bayar di Tempat'] as $key => $label)
                            <label class="flex cursor-pointer items-center gap-2 rounded-xl border border-slate-700 bg-ink-800 px-3 py-2.5 text-sm text-slate-300 hover:border-slate-600">
                                <input type="radio" name="metode" value="{{ $key }}" class="accent-cyan-400"
                                       {{ old('metode', 'BAYAR_DI_TEMPAT') === $key ? 'checked' : '' }} required>
                                {{ $label }}
                            </label>
                        @endforeach
                    </div>
                    @error('metode') <p class="mt-1 text-xs text-rose-400">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- Perkiraan total (server tetap menghitung ulang) --}}
            <div class="mt-5 flex items-center justify-between rounded-2xl border border-slate-800 bg-ink-800 p-4">
                <span class="text-sm text-slate-400">Perkiraan total (harga × durasi)</span>
                <span class="text-lg font-extrabold text-cyan-300" id="w-total">Rp 0</span>
            </div>

            <div class="mt-5 flex gap-3">
                <x-button type="submit">Simpan Booking</x-button>
                <x-button href="{{ route('admin.bookings.index') }}" variant="ghost">Batal</x-button>
            </div>
        </form>
    </x-card>
@endsection

@push('scripts')
<script>
    function hitungTotal() {
        const unit = document.getElementById('w-unit');
        const durasi = parseInt(document.getElementById('w-durasi').value, 10) || 0;
        const harga = parseFloat(unit.options[unit.selectedIndex]?.dataset.harga || 0);
        const total = harga * durasi;
        document.getElementById('w-total').textContent =
            'Rp ' + total.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }
    document.getElementById('w-unit').addEventListener('change', hitungTotal);
    document.getElementById('w-durasi').addEventListener('change', hitungTotal);
    hitungTotal();
</script>
@endpush
