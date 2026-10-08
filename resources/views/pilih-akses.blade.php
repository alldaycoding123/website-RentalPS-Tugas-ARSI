@extends('layouts.customer')

@section('title', 'Pilih Akses')

@section('content')
    <div class="mx-auto max-w-3xl pt-6 text-center">
        <p class="mb-2 text-sm font-semibold uppercase tracking-[0.2em] text-cyan-400">Sistem Informasi Booking</p>
        <h1 class="text-3xl font-extrabold text-white sm:text-4xl">Selamat datang di <span class="text-white">PS</span><span class="text-cyan-400">Rental</span></h1>
        <p class="mx-auto mt-3 max-w-xl text-sm text-slate-400 sm:text-base">
            Booking rental PlayStation online — pilih akses sesuai kebutuhan Anda.
        </p>
    </div>

    {{-- Dua kartu akses: mobile bertumpuk, desktop berdampingan --}}
    <div class="mx-auto mt-8 grid max-w-4xl grid-cols-1 gap-5 md:grid-cols-2">

        {{-- Kartu Pelanggan --}}
        <a href="{{ route('customer.login') }}"
           class="group rounded-2xl border border-slate-800 bg-[#111827] p-6 transition hover:-translate-y-1 hover:border-cyan-400/50 hover:shadow-xl hover:shadow-cyan-500/10">
            <div class="grid h-14 w-14 place-items-center rounded-2xl bg-gradient-to-br from-cyan-500/20 to-indigo-500/20 text-2xl ring-1 ring-cyan-400/30">📱</div>
            <h2 class="mt-4 text-lg font-bold text-white">Pelanggan</h2>
            <p class="mt-1 text-sm text-slate-400">Scan QR atau masukkan No. HP untuk mulai booking unit PS & pilih jadwal.</p>
            <span class="mt-4 inline-flex items-center gap-2 text-sm font-semibold text-cyan-400">
                Masuk sebagai pelanggan
                <span class="transition group-hover:translate-x-1">→</span>
            </span>
        </a>

        {{-- Kartu Staff --}}
        <a href="{{ route('staff.login') }}"
           class="group rounded-2xl border border-slate-800 bg-[#111827] p-6 transition hover:-translate-y-1 hover:border-indigo-400/50 hover:shadow-xl hover:shadow-indigo-500/10">
            <div class="grid h-14 w-14 place-items-center rounded-2xl bg-gradient-to-br from-indigo-500/20 to-cyan-500/20 text-2xl ring-1 ring-indigo-400/30">🛡️</div>
            <h2 class="mt-4 text-lg font-bold text-white">Staff / Admin</h2>
            <p class="mt-1 text-sm text-slate-400">Login dengan akun untuk Kasir, Admin, atau Manajer (check-in, pembayaran, laporan).</p>
            <span class="mt-4 inline-flex items-center gap-2 text-sm font-semibold text-indigo-300">
                Login dengan akun
                <span class="transition group-hover:translate-x-1">→</span>
            </span>
        </a>
    </div>

    <div class="mx-auto mt-8 max-w-4xl rounded-2xl border border-slate-800 bg-ink-800/60 p-4 text-center text-xs text-slate-500">
        Akun demo staff: <span class="font-mono text-slate-300">admin@psrental.test</span> · password <span class="font-mono text-slate-300">password123</span>
        &nbsp;|&nbsp; Pelanggan demo: <span class="font-mono text-slate-300">081234567890</span>
    </div>
@endsection
