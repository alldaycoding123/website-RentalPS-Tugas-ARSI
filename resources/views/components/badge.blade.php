@props(['status' => '', 'label' => null])

@php
    // Peta warna badge per status
    $peta = [
        // Booking
        'PENDING' => 'bg-amber-500/15 text-amber-300 border-amber-500/40',
        'CONFIRMED' => 'bg-sky-500/15 text-sky-300 border-sky-500/40',
        'CHECKIN' => 'bg-indigo-500/15 text-indigo-300 border-indigo-500/40',
        'SELESAI' => 'bg-emerald-500/15 text-emerald-300 border-emerald-500/40',
        'BATAL' => 'bg-rose-500/15 text-rose-300 border-rose-500/40',
        // Pembayaran
        'BELUM_BAYAR' => 'bg-rose-500/15 text-rose-300 border-rose-500/40',
        'LUNAS' => 'bg-emerald-500/15 text-emerald-300 border-emerald-500/40',
        // Unit
        'TERSEDIA' => 'bg-emerald-500/15 text-emerald-300 border-emerald-500/40',
        'SUDAH_DIBOOKING' => 'bg-orange-500/15 text-orange-300 border-orange-500/40',
        'DIGUNAKAN' => 'bg-orange-500/15 text-orange-300 border-orange-500/40',
        'MAINTENANCE' => 'bg-slate-500/15 text-slate-300 border-slate-500/40',
    ];

    $kelas = $peta[$status] ?? 'bg-slate-500/15 text-slate-300 border-slate-500/40';

    $teks = $label ?? str_replace('_', ' ', $status);
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center gap-1 rounded-full border px-2.5 py-0.5 text-xs font-semibold {$kelas}"]) }}>{{ $teks }}</span>
