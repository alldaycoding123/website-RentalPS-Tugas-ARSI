@extends('layouts.staff')

@section('title', 'Ubah Unit PS')
@section('subtitle', $unit->kode_unit . ' · ' . $unit->nama)

@section('content')
    <x-card class="max-w-3xl">
        <form method="POST" action="{{ route('admin.units.update', $unit) }}">
            @csrf
            @method('PUT')
            @include('admin.units._form', ['unit' => $unit])

            <div class="mt-5 flex gap-3">
                <x-button type="submit">Simpan Perubahan</x-button>
                <x-button href="{{ route('admin.units.index') }}" variant="ghost">Batal</x-button>
            </div>
        </form>
    </x-card>
@endsection
