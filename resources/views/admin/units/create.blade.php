@extends('layouts.staff')

@section('title', 'Tambah Unit PS')
@section('subtitle', 'Unit baru otomatis tampil di grid pelanggan')

@section('content')
    <x-card class="max-w-3xl">
        <form method="POST" action="{{ route('admin.units.store') }}">
            @csrf
            @include('admin.units._form')

            <div class="mt-5 flex gap-3">
                <x-button type="submit">Simpan Unit</x-button>
                <x-button href="{{ route('admin.units.index') }}" variant="ghost">Batal</x-button>
            </div>
        </form>
    </x-card>
@endsection
