@php($action = $action ?? '')

<div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
    <div>
        <label class="mb-1.5 block text-sm font-medium text-slate-300">Kode Unit</label>
        <input type="text" name="kode_unit" value="{{ old('kode_unit', $unit->kode_unit ?? '') }}" required maxlength="20"
               placeholder="PS5-01"
               class="w-full rounded-xl border border-slate-700 bg-ink-800 px-3 py-2.5 text-sm text-white placeholder-slate-500 focus:border-cyan-400 focus:outline-none focus:ring-1 focus:ring-cyan-400">
        @error('kode_unit') <p class="mt-1 text-xs text-rose-400">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="mb-1.5 block text-sm font-medium text-slate-300">Nama Unit</label>
        <input type="text" name="nama" value="{{ old('nama', $unit->nama ?? '') }}" required maxlength="100"
               placeholder="PlayStation 5"
               class="w-full rounded-xl border border-slate-700 bg-ink-800 px-3 py-2.5 text-sm text-white placeholder-slate-500 focus:border-cyan-400 focus:outline-none focus:ring-1 focus:ring-cyan-400">
        @error('nama') <p class="mt-1 text-xs text-rose-400">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="mb-1.5 block text-sm font-medium text-slate-300">Tipe</label>
        <select name="tipe" class="w-full rounded-xl border border-slate-700 bg-ink-800 px-3 py-2.5 text-sm text-white focus:border-cyan-400 focus:outline-none">
            @foreach(['PS4', 'PS5'] as $t)
                <option value="{{ $t }}" {{ old('tipe', $unit->tipe ?? 'PS5') === $t ? 'selected' : '' }}>{{ $t }}</option>
            @endforeach
        </select>
        @error('tipe') <p class="mt-1 text-xs text-rose-400">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="mb-1.5 block text-sm font-medium text-slate-300">Ruang</label>
        <input type="text" name="ruang" value="{{ old('ruang', $unit->ruang ?? '') }}" required maxlength="50"
               placeholder="Ruang A"
               class="w-full rounded-xl border border-slate-700 bg-ink-800 px-3 py-2.5 text-sm text-white placeholder-slate-500 focus:border-cyan-400 focus:outline-none focus:ring-1 focus:ring-cyan-400">
        @error('ruang') <p class="mt-1 text-xs text-rose-400">{{ $message }}</p> @enderror
    </div>

    <div class="sm:col-span-2">
        <label class="mb-1.5 block text-sm font-medium text-slate-300">TV / Kelengkapan</label>
        <input type="text" name="tv" value="{{ old('tv', $unit->tv ?? '') }}" required maxlength="150"
               placeholder='TV 65" 4K OLED, Headset Premium'
               class="w-full rounded-xl border border-slate-700 bg-ink-800 px-3 py-2.5 text-sm text-white placeholder-slate-500 focus:border-cyan-400 focus:outline-none focus:ring-1 focus:ring-cyan-400">
        @error('tv') <p class="mt-1 text-xs text-rose-400">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="mb-1.5 block text-sm font-medium text-slate-300">Harga per Jam (Rp)</label>
        <input type="number" name="harga_jam" value="{{ old('harga_jam', $unit->harga_jam ?? 10000) }}" required min="1000" step="500"
               class="w-full rounded-xl border border-slate-700 bg-ink-800 px-3 py-2.5 text-sm text-white focus:border-cyan-400 focus:outline-none focus:ring-1 focus:ring-cyan-400">
        @error('harga_jam') <p class="mt-1 text-xs text-rose-400">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="mb-1.5 block text-sm font-medium text-slate-300">Status</label>
        <select name="status" class="w-full rounded-xl border border-slate-700 bg-ink-800 px-3 py-2.5 text-sm text-white focus:border-cyan-400 focus:outline-none">
            @foreach(['TERSEDIA', 'MAINTENANCE', 'DIGUNAKAN'] as $s)
                <option value="{{ $s }}" {{ old('status', $unit->status ?? 'TERSEDIA') === $s ? 'selected' : '' }}>
                    {{ $s === 'TERSEDIA' ? 'Tersedia' : ($s === 'MAINTENANCE' ? 'Maintenance' : 'Digunakan') }}
                </option>
            @endforeach
        </select>
        @error('status') <p class="mt-1 text-xs text-rose-400">{{ $message }}</p> @enderror
    </div>
</div>
