@props(['href' => null, 'variant' => 'primary', 'type' => 'button', 'target' => null])

@php
    $varians = [
        'primary' => 'bg-gradient-to-r from-cyan-500 to-indigo-500 text-white hover:from-cyan-400 hover:to-indigo-400 shadow-lg shadow-cyan-500/20',
        'secondary' => 'bg-slate-800 text-slate-200 border border-slate-700 hover:bg-slate-700',
        'danger' => 'bg-rose-600 text-white hover:bg-rose-500',
        'success' => 'bg-emerald-600 text-white hover:bg-emerald-500',
        'ghost' => 'bg-transparent text-slate-300 border border-slate-700 hover:bg-slate-800',
    ];

    $kelas = 'inline-flex items-center justify-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold transition focus:outline-none focus:ring-2 focus:ring-cyan-400/50 disabled:cursor-not-allowed disabled:opacity-50 '.$varians[$variant] ?? $varians['primary'];
@endphp

@if($href)
    <a href="{{ $href }}" @if($target) target="{{ $target }}" @endif {{ $attributes->merge(['class' => $kelas]) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $kelas]) }}>{{ $slot }}</button>
@endif
