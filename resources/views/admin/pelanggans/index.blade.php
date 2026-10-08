@extends('layouts.staff')

@section('title', 'Data Pelanggan')
@section('subtitle', 'Kelola data pelanggan dan pencarian')

@section('content')
    <x-card title="Pencarian">
        <form method="GET" action="{{ route('admin.pelanggans.index') }}" class="flex flex-col gap-3 sm:flex-row">
            <input type="text" name="q" value="{{ $q }}" placeholder="Cari nama atau No. HP…"
                   class="flex-1 rounded-xl border border-slate-700 bg-ink-800 px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:border-cyan-400 focus:outline-none focus:ring-1 focus:ring-cyan-400">
            <x-button type="submit" class="sm:px-6">Cari</x-button>
        </form>
    </x-card>

    <div class="mt-5 mb-4 flex items-center justify-between">
        <p class="text-sm text-slate-400">{{ $pelanggans->total() }} pelanggan</p>
        <x-button href="{{ route('admin.pelanggans.create') }}">+ Tambah Pelanggan</x-button>
    </div>

    <x-card>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="text-xs uppercase text-slate-500">
                    <tr class="border-b border-slate-800">
                        <th class="py-2 pr-3">Nama</th>
                        <th class="py-2 pr-3">No. HP</th>
                        <th class="py-2 pr-3">Jumlah Booking</th>
                        <th class="py-2 pr-3">Terdaftar</th>
                        <th class="py-2 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pelanggans as $p)
                        <tr class="border-b border-slate-800/60">
                            <td class="py-3 pr-3 font-semibold text-white">{{ $p->nama }}</td>
                            <td class="py-3 pr-3 font-mono text-slate-300">{{ $p->no_hp }}</td>
                            <td class="py-3 pr-3 text-slate-300">{{ $p->bookings_count }}</td>
                            <td class="py-3 pr-3 text-slate-400">{{ $p->created_at->format('d M Y') }}</td>
                            <td class="py-3 text-right">
                                <div class="inline-flex gap-2">
                                    <x-button href="{{ route('admin.pelanggans.edit', $p) }}" variant="secondary" class="px-3 py-1.5 text-xs">Ubah</x-button>
                                    <form method="POST" action="{{ route('admin.pelanggans.destroy', $p) }}"
                                          onsubmit="return confirm('Hapus pelanggan {{ $p->nama }}?');">
                                        @csrf
                                        @method('DELETE')
                                        <x-button type="submit" variant="danger" class="px-3 py-1.5 text-xs">Hapus</x-button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-10 text-center text-slate-500">
                                {{ $q ? 'Pelanggan tidak ditemukan.' : 'Belum ada pelanggan.' }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-5">{{ $pelanggans->links() }}</div>
    </x-card>
@endsection
