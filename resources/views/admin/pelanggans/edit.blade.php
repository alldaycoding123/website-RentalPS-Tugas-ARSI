@extends('layouts.staff')

@section('title', 'Ubah Pelanggan')
@section('subtitle', $pelanggan->nama)

@section('content')
    <x-card class="max-w-xl">
        <form method="POST" action="{{ route('admin.pelanggans.update', $pelanggan) }}">
            @csrf
            @method('PUT')

            <div class="space-y-4">
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-300">Nama Lengkap</label>
                    <input type="text" name="nama" value="{{ old('nama', $pelanggan->nama) }}" required maxlength="100"
                           class="w-full rounded-xl border border-slate-700 bg-ink-800 px-3 py-2.5 text-sm text-white focus:border-cyan-400 focus:outline-none focus:ring-1 focus:ring-cyan-400">
                    @error('nama') <p class="mt-1 text-xs text-rose-400">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-300">No. HP</label>
                    <input type="text" name="no_hp" value="{{ old('no_hp', $pelanggan->no_hp) }}" required
                           class="w-full rounded-xl border border-slate-700 bg-ink-800 px-3 py-2.5 text-sm text-white focus:border-cyan-400 focus:outline-none focus:ring-1 focus:ring-cyan-400">
                    @error('no_hp') <p class="mt-1 text-xs text-rose-400">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="mt-5 flex gap-3">
                <x-button type="submit">Simpan Perubahan</x-button>
                <x-button href="{{ route('admin.pelanggans.index') }}" variant="ghost">Batal</x-button>
            </div>
        </form>
    </x-card>
@endsection
