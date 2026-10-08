@extends('layouts.staff')

@section('title', 'Data PS')
@section('subtitle', 'Kelola unit PlayStation, harga, dan status')

@section('content')
    <div class="mb-4 flex items-center justify-between gap-3">
        <p class="text-sm text-slate-400">{{ $units->count() }} unit terdaftar</p>
        <x-button href="{{ route('admin.units.create') }}">+ Tambah Unit</x-button>
    </div>

    {{-- Grid otomatis menyesuaikan jumlah unit --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4">
        @forelse($units as $unit)
            <div class="rounded-2xl border border-slate-800 bg-[#111827] p-5">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-cyan-400">{{ $unit->kode_unit }}</p>
                        <h2 class="mt-1 font-bold text-white">{{ $unit->nama }}</h2>
                        <p class="text-xs text-slate-500">{{ $unit->tipe }} · {{ $unit->ruang }}</p>
                    </div>
                    <x-badge :status="$unit->status" />
                </div>

                <p class="mt-3 text-xs text-slate-400">{{ $unit->tv }}</p>

                <div class="mt-4 flex items-center justify-between border-t border-slate-800 pt-4">
                    <p class="font-extrabold text-white">{{ rupiah($unit->harga_jam) }}<span class="text-xs font-medium text-slate-400"> /jam</span></p>
                    <div class="flex gap-2">
                        <x-button href="{{ route('admin.units.edit', $unit) }}" variant="secondary" class="px-3 py-1.5 text-xs">Ubah</x-button>

                        <form method="POST" action="{{ route('admin.units.destroy', $unit) }}"
                              onsubmit="return confirm('Hapus unit {{ $unit->kode_unit }}?');">
                            @csrf
                            @method('DELETE')
                            <x-button type="submit" variant="danger" class="px-3 py-1.5 text-xs">Hapus</x-button>
                        </form>
                    </div>
                </div>

                {{-- Ubah status cepat --}}
                <form method="POST" action="{{ route('admin.units.status', $unit) }}" class="mt-3 flex gap-2">
                    @csrf
                    @method('PATCH')
                    <select name="status" class="flex-1 rounded-lg border border-slate-700 bg-ink-800 px-2 py-1.5 text-xs text-white focus:border-cyan-400 focus:outline-none">
                        @foreach(['TERSEDIA', 'MAINTENANCE', 'DIGUNAKAN'] as $s)
                            <option value="{{ $s }}" {{ $unit->status === $s ? 'selected' : '' }}>{{ $s }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="rounded-lg border border-slate-700 px-3 py-1.5 text-xs font-semibold text-slate-300 hover:bg-slate-800">Simpan</button>
                </form>
            </div>
        @empty
            <div class="col-span-full rounded-2xl border border-dashed border-slate-700 p-12 text-center text-slate-400">
                Belum ada unit. Klik "Tambah Unit" untuk menambahkan.
            </div>
        @endforelse
    </div>
@endsection
